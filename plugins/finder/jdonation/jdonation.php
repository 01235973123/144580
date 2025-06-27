<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.Donation
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

//jimport('joomla.application.component.helper');

// Load the base adapter.

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseQuery;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;


/**
 * Finder adapter for Joomla Contacts.
 *
 * @package     Joomla.Plugin
 * @subpackage  Finder.Donation
 * @since       2.5
 */
class plgFinderJdonation extends Adapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'Jdonation';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension = 'com_jdonation';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout = 'donation';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title = 'Campaigns';

	/**
	 * The table name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $table = '#__jd_campaigns';

	/**
	 * The field the published state is stored in.
	 *
	 * @var    string
	 * @since  2.5
	 */

	protected $state_field = 'published';


	/**
	 * Constructor
	 *
	 * @param   object &$subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @since   2.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();
	}

	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * This event will fire when contacts are deleted and when an indexed item is deleted.
	 *
	 * @param   string $context The context of the action being performed.
	 * @param   JTable $table   A JTable object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterDelete($context, $table): void
	{
		if ($context === 'com_jdonation.donation')
		{
			$id = $table->id;
		}
		elseif ($context === 'com_finder.index')
		{
			$id = $table->link_id;
		}
		else
		{
			return;
		}

		// Remove item from the index.
		$this->remove($id);
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string  $context The context for the content passed to the plugin.
	 * @param   array   $pks     A list of primary key ids of the content that has changed state.
	 * @param   integer $value   The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// Handle when the plugin is disabled
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}
	}

	public function onFinderAfterSave($context, $row, $isNew): void
	{
		// We only want to handle articles here.
		if ($context === 'com_jdonation.donation')
		{
			// Reindex the item.
			$this->reindex($row->id);
		}

	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult $item   The item to index as an FinderIndexerResult object.
	 * @param   string              $format The item format
	 *
	 * @return  void
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function index(Result $item)
	{
		// Check if the extension is enabled
		if (ComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		$db = Factory::getDbo();

		// Default Itemid
		$itemId = DonationHelper::getItemid();

		// Initialize the item parameters.
		//$registry = new JRegistry;
		//$registry->loadString($item->params);
		//$item->params = $registry;

		//$registry = new Registry($item->params);
		//$item->params = $registry;

		// Build the necessary route and path information.
		$item->url   = $this->getURL($item->id, 'com_jdonation', 'campaigns');
		
		$item->route = DonationHelperRoute::getDonationFormRoute($item->id, $itemId);
		$item->path  = $item->route;

		// Get the menu title if it exists.
		$title = $this->getItemMenuTitle($item->url);

		// Adjust the title if necessary.
		if (!empty($title) && $this->params->get('use_menu_title', true))
		{
			$item->title = $title;
		}

		$item->addInstruction(Indexer::META_CONTEXT, 'title');

		$item->addInstruction(Indexer::META_CONTEXT, 'short_description');

		$item->addInstruction(Indexer::META_CONTEXT, 'description');

		// Add the meta-data processing instructions.
		$item->addInstruction(Indexer::META_CONTEXT, 'meta_keywords');
		$item->addInstruction(Indexer::META_CONTEXT, 'meta_description');

		$item->addInstruction(Indexer::META_CONTEXT, 'author');
		$item->addInstruction(Indexer::META_CONTEXT, 'start_date');

		$item->state = $this->translateState($item->state , $item->cat_state);

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Donation');

		// Add the category taxonomy data.
		$item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

		// Add the language taxonomy data.
		//$item->addTaxonomy('Language', '*');

		// Get content extras.
		Helper::getContentExtras($item);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	protected function setup()
	{
		// Load dependent classes.
		include_once JPATH_SITE . '/components/com_jdonation/helper/helper.php';
		include_once JPATH_SITE . '/components/com_jdonation/helper/route.php';

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed $query A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($query = null)
	{
		$db    = Factory::getDbo();
		$query = $query instanceof JDatabaseQuery ? $query : $db->getQuery(true);
		$query->select('a.id, a.title,  a.short_description AS summary, a.description AS body')
			->select('a.published AS state, a.access AS access, a.start_date AS start_date')
			->select('a.start_date AS publish_start_date, a.end_date AS publish_end_date')
			->select('a.meta_keywords, a.meta_description, a.ordering')
			->select('u.name AS author')
			->select('d.id as catid, d.title AS category, d.published as cat_state, d.access as cat_access')
			->from('#__jd_campaigns AS a')
			->join('left', '#__jd_categories AS d ON d.id = a.category_id')
			->join('left', '#__users AS u ON u.id=a.user_id');
		//DonationHelper::logData(JPATH_ROOT .'/components/com_jdonation/finder.txt', [], $query->__toString());
		return $query;
	}
}


