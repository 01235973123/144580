<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseQuery;
use Joomla\Registry\Registry;

/**
 * Finder adapter for Membership Pro.
 *
 * @package     Joomla.Plugin
 * @subpackage  Finder.Membership Pro
 * @since       3.3.0
 */
class plgFinderMembershipPro extends Adapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'Plans';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension = 'com_osmembership';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout = 'plan';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title = 'Plan';

	/**
	 * The table name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $table = '#__osmembership_plans';

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
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();
	}

	/**
	 * Method to update the item link information when the item category is
	 * changed. This is fired when the item category is published or unpublished
	 * from the list view.
	 *
	 * @param   string  $extension  The extension whose category has been updated.
	 * @param   array   $pks        A list of primary key ids of the content that has changed state.
	 * @param   int     $value      The value of the state that the content has been changed to.
	 *
	 * @return  void
	 */
	public function onFinderCategoryChangeState($extension, $pks, $value)
	{
		// Make sure we're handling com_contact categories
		if ($extension == 'com_osmembership')
		{
			$this->categoryStateChange($pks, $value);
		}
	}

	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * This event will fire when contacts are deleted and when an indexed item is deleted.
	 *
	 * @param   string                   $context  The context of the action being performed.
	 * @param   \Joomla\CMS\Table\Table  $table    A JTable object containing the record to be deleted
	 *
	 * @return  bool  True on success.
	 *
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_osmembership.plans')
		{
			$id = $table->id;
		}
		elseif ($context == 'com_finder.index')
		{
			$id = $table->link_id;
		}
		else
		{
			return true;
		}

		// Remove the items.
		return $this->remove($id);
	}

	/**
	 * Method to determine if the access level of an item changed.
	 *
	 * @param   string                   $context  The context of the content passed to the plugin.
	 * @param   \Joomla\CMS\Table\Table  $row      A JTable object
	 * @param   bool                     $isNew    If the content has just been created
	 *
	 * @return  bool  True on success.
	 *
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		// We only want to handle contacts here
		if ($context == 'com_osmembership.plans')
		{
			// Check if the access levels are different
			if (!$isNew && $this->old_access != $row->access)
			{
				// Process the change.
				$this->itemAccessChange($row);
			}

			// Reindex the item
			$this->reindex($row->id);
		}

		return true;
	}

	/**
	 * Method to reindex the link information for an item that has been saved.
	 * This event is fired before the data is actually saved so we are going
	 * to queue the item to be indexed later.
	 *
	 * @param   string                   $context  The context of the content passed to the plugin.
	 * @param   \Joomla\CMS\Table\Table  $row      A JTable object
	 * @param   bool                     $isNew    If the content is just about to be created
	 *
	 * @return  bool  True on success.
	 *
	 * @throws  Exception on database error.
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		// We only want to handle contacts here
		// Query the database for the old access level if the item isn't new
		if ($context == 'com_osmembership.plans' && !$isNew)
		{
			$this->checkItemAccess($row);
		}

		return true;
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string  $context  The context for the content passed to the plugin.
	 * @param   array   $pks      A list of primary key ids of the content that has changed state.
	 * @param   int     $value    The value of the state that the content has been changed to.
	 *
	 * @return  void
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle contacts here
		if ($context == 'com_osmembership.plans')
		{
			$this->itemStateChange($pks, $value);
		}

		// Handle when the plugin is disabled
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult  $item    The item to index as an FinderIndexerResult object.
	 * @param   string               $format  The item format
	 *
	 * @return  void
	 *
	 * @throws  Exception on database error.
	 */
	protected function index(Result $item, $format = 'html')
	{
		// Check if the extension is enabled
		if (ComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		$itemId = OSMembershipHelper::getItemid();

		// Initialize the item parameters.
		$registry     = new Registry($item->params);
		$item->params = $registry;

		// Build the necessary route and path information.
		$item->url = $this->getURL($item->id, $this->extension, $this->layout);

		$planMenuId = OSMembershipHelperRoute::getPlanMenuId($item->id, $item->category_id, $itemId);

		$item->route = 'index.php?option=com_osmembership&view=plan&catid=' . $item->category_id . '&id=' . $item->id . '&Itemid=' . $planMenuId;
		$item->path  = $item->route;

		// Get the menu title if it exists.
		$title = $this->getItemMenuTitle($item->url);

		// Adjust the title if necessary.
		if (!empty($title) && $this->params->get('use_menu_title', true))
		{
			$item->title = $title;
		}

		// Handle the contact user name.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'title');

		// Add the meta-data processing instructions.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'meta_keywords');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'meta_description');

		$item->state = $this->translateState($item->state, $item->cat_state);

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Plans');

		// Add the category taxonomy data.
		if ($item->category_name)
		{
			$item->addTaxonomy('Category', $item->category_name, $item->cat_state, $item->cat_access);
		}

		// Get content extras.
		FinderIndexerHelper::getContentExtras($item);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  bool  True on success.
	 *
	 */
	protected function setup()
	{
		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $query  A JDatabaseQuery object or null.
	 *
	 * @return \Joomla\Database\DatabaseQuery A database object.
	 */
	protected function getListQuery($query = null)
	{
		$db    = $this->db;
		$query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true);

		$query->select('a.id, a.category_id, a.title, a.short_description AS summary, a.description AS body')
			->select('a.published AS state, a.access AS access')
			->select('a.meta_keywords, a.meta_description, a.ordering')
			->select('c.title AS category_name, IFNULL(c.published, a.published) AS cat_state, IFNULL(c.access, a.access) AS cat_access')
			->from('#__osmembership_plans AS a')
			->leftJoin('#__osmembership_categories AS c ON c.id = a.category_id');

		return $query;
	}
}
