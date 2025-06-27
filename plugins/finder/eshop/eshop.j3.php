<?php
/**
 * @package            Joomla
 * @subpackage         EShop
 * @author             Giang Dinh Truong
 * @copyright          Copyright (C) 2010 - 2022 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

// Load the base adapter.
JLoader::register('FinderIndexerAdapter', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php');

/**
 * Finder adapter for Joomla Contacts.
 *
 * @package     Joomla.Plugin
 * @subpackage  Finder.EShop
 * @since       3.1.0
 */
class plgFinderEshop extends FinderIndexerAdapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'Eshop';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension = 'com_eshop';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout = 'product';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title = 'Eshop';

	/**
	 * The table name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $table = '#__eshop_products';

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
	 * Method to update the item link information when the item category is
	 * changed. This is fired when the item category is published or unpublished
	 * from the list view.
	 *
	 * @param   string   $extension  The extension whose category has been updated.
	 * @param   array    $pks        A list of primary key ids of the content that has changed state.
	 * @param   integer  $value      The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderCategoryChangeState($extension, $pks, $value)
	{
		// Make sure we're handling com_contact categories
		if ($extension == 'com_eshop')
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
	 * @param   Table  $table    A JTable object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception on database error.
	 * @since   2.5
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_eshop.products')
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
	 * @param   Table  $row      A JTable object
	 * @param   boolean                  $isNew    If the content has just been created
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception on database error.
	 * @since   2.5
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		// We only want to handle contacts here
		if ($context == 'com_eshop.events')
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
	 * @param   Table  $row      A JTable object
	 * @param   boolean                  $isNew    If the content is just about to be created
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception on database error.
	 * @since   2.5
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		// We only want to handle contacts here
		// Query the database for the old access level if the item isn't new
		if ($context == 'com_eshop.products' && !$isNew)
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
	 * @param   string   $context  The context for the content passed to the plugin.
	 * @param   array    $pks      A list of primary key ids of the content that has changed state.
	 * @param   integer  $value    The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle contacts here
		if ($context == 'com_eshop.products')
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
	 * @since   2.5
	 */
	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		// Check if the extension is enabled
		if (ComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		// Initialize the item parameters.
		$registry = new Registry;
		$registry->loadString($item->params);
		$item->params = $registry;

		// Build the necessary route and path information.
		$item->url   = $this->getURL($item->id, $this->extension, $this->layout);
		$item->route = EShopRoute::getProductRoute($item->id, $item->category_id);
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
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'start_date');

		$item->state = $this->translateState($item->state, $item->cat_state);

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Eshop');

		// Add the category taxonomy data.
		$item->addTaxonomy('Category', $item->category_name, $item->cat_state, $item->cat_state);

		// Add the language taxonomy data.
		$item->addTaxonomy('Language', $item->language);

		// Get content extras.
		FinderIndexerHelper::getContentExtras($item);

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
		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/defines.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/inflector.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/autoload.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/rad/bootstrap.php';

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $query  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 * @since   2.5
	 */
	protected function getListQuery($query = null)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/defines.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/inflector.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/autoload.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/rad/bootstrap.php';

		$db     = Factory::getDbo();
		$config = EShopHelper::getConfig();
		$query  = $query instanceof JDatabaseQuery ? $query : $db->getQuery(true);

		$query->select('a.id, CONCAT(a.product_sku, " - ", b.product_name) AS title, b.language AS language, b.product_short_desc AS summary, b.product_desc AS body')
			->select(
				'a.published AS state, a.published AS access, b.meta_key AS meta_keywords, b.meta_desc AS meta_description, a.ordering, a.created_date AS start_date'
			)
			->select('c.id AS category_id, cd.category_name, c.published AS cat_state')
			->select('u.name AS author')
			->from('#__eshop_products AS a')
			->leftJoin('#__eshop_productdetails AS b ON a.id = b.product_id')
			->leftJoin('#__eshop_productcategories AS pc ON a.id = pc.product_id')
			->leftJoin('#__eshop_categories AS c ON c.id = pc.category_id')
			->leftJoin('#__eshop_categorydetails AS cd ON c.id = cd.category_id')
			->leftJoin('#__users as u ON a.created_by = u.id')
			->where('main_category = 1');

		return $query;
	}
}
