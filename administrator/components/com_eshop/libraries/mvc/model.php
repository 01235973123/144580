<?php
/**
 * @version        1.0
 * @package        OSFramework
 * @subpackage     EShopModel
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

/**
 * Basic Model class to implement Generic function
 * @author Giang Dinh Truong
 *
 */
class EShopModel extends ItemModel
{

	/**
	 * Entity ID
	 *
	 * @var int
	 */
	protected $_id = null;

	/**
	 * Entity data
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Table name where the object stored
	 * @var
	 */
	protected $_tableName = null;

	/**
	 * Name of component
	 * @var string
	 */
	protected $_component = null;

	/**
	 * This object can be translated into different language or not
	 * @var Boolean
	 */
	protected $translatable = false;

	/**
	 * List of fields which can be translated
	 * @var array
	 */
	protected $translatableFields = [];

	/**
	 * This model trigger events or not. By default, set it to No to improve performance
	 *
	 * @var boolean
	 */
	protected $triggerEvents = false;

	/**
	 * The event to trigger after deleting the data.
	 *
	 * @var string
	 */
	protected $eventAfterDelete = null;

	/**
	 * The event to trigger after saving the data.
	 *
	 * @var string
	 */
	protected $eventAfterSave = null;

	/**
	 * The event to trigger before deleting the data.
	 *
	 * @var string
	 */
	protected $eventBeforeDelete = null;

	/**
	 * The event to trigger before saving the data.
	 *
	 * @var string
	 */
	protected $eventBeforeSave = null;

	/**
	 * The event to trigger after changing the published state of the data.
	 *
	 * @var string
	 */
	protected $eventChangeState = null;

	/**
	 * Name of plugin group which will be loaded to process the triggered event.
	 * Default is component name
	 *
	 * @var string
	 */
	protected $pluginGroup = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct($config = [])
	{
		parent::__construct();

		$input = Factory::getApplication()->input;
		$db    = $this->getDbo();
		if (isset($config['table_name']))
		{
			$this->_tableName = $config['table_name'];
		}
		else
		{
			$this->_tableName = $db->getPrefix() . strtolower(ESHOP_TABLE_PREFIX . '_' . EShopInflector::pluralize($this->name));
		}

		$r = null;

		if (preg_match('/(.*)Model(.*)/i', get_class($this), $r))
		{
			$this->_component = strtolower($r[1]);
		}
		$array = $input->get('cid', [0], '', 'array');
		$edit  = $input->get('edit', true);
		if ($edit)
		{
			$this->setId((int) $array[0]);
		}

		#Adding support for translatable objects
		if (isset($config['translatable']))
		{
			$this->translatable = $config['translatable'];
		}
		else
		{
			$this->translatable = false;
		}
		if (isset($config['translatable_fields']))
		{
			$this->translatableFields = $config['translatable_fields'];
		}
		else
		{
			$this->translatableFields = [];
		}

		if ($this->triggerEvents)
		{
			$name = ucfirst($this->name);

			if (isset($config['plugin_group']))
			{
				$this->pluginGroup = $config['plugin_group'];
			}
			elseif (empty($this->pluginGroup))
			{
				//Plugin group should default to component name
				$this->pluginGroup = substr($this->option, 4);
			}

			//Initialize the events
			if (isset($config['event_after_delete']))
			{
				$this->eventAfterDelete = $config['event_after_delete'];
			}
			elseif (empty($this->eventAfterDelete))
			{
				$this->eventAfterDelete = 'on' . $name . 'AfterDelete';
			}

			if (isset($config['event_after_save']))
			{
				$this->eventAfterSave = $config['event_after_save'];
			}
			elseif (empty($this->eventAfterSave))
			{
				$this->eventAfterSave = 'on' . $name . 'AfterSave';
			}

			if (isset($config['event_before_delete']))
			{
				$this->eventBeforeDelete = $config['event_before_delete'];
			}
			elseif (empty($this->eventBeforeDelete))
			{
				$this->eventBeforeDelete = 'on' . $name . 'BeforeDelete';
			}

			if (isset($config['event_before_save']))
			{
				$this->eventBeforeSave = $config['event_before_save'];
			}
			elseif (empty($this->eventBeforeSave))
			{
				$this->eventBeforeSave = 'on' . $name . 'BeforeSave';
			}

			if (isset($config['event_change_state']))
			{
				$this->eventChangeState = $config['event_change_state'];
			}
			elseif (empty($this->eventChangeState))
			{
				$this->eventChangeState = 'on' . $name . 'ChangeState';
			}
		}
	}

	/**
	 *
	 * Function to get a specific model
	 *
	 * @param   string  $name
	 *
	 * @return model object
	 */
	public function getModel($name = '')
	{
		if ($name == '')
		{
			$name = $this->name;
		}

		JLoader::import($name, JPATH_SITE . '/components/com_eshop/models');
		$model = ItemModel::getInstance($name, 'EShopModel');

		return $model;
	}

	/**
	 * Method to set the item identifier
	 *
	 * @access    public
	 *
	 * @param   int item identifier
	 */
	public function setId($id)
	{
		// Set id and data
		$this->_id   = $id;
		$this->_data = null;
	}

	public function setTableName($table)
	{
		$this->_tableName = $table;
	}

	public function getTranslatable()
	{
		return $this->translatable;
	}

	/**
	 * Method to get an item data
	 *
	 * @since 1.5
	 */
	public function &getItem($pk = null)
	{
		if (empty($this->_data))
		{
			if ($this->_id)
			{
				$this->_loadData();
			}
			else
			{
				$this->_initData();
			}
		}

		return $this->_data;
	}

	/**
	 * Method to store an item
	 *
	 * @access    public
	 * @return boolean True on success
	 * @since     1.5
	 */
	public function store(&$data)
	{
		$db    = $this->getDbo();
		$user  = Factory::getUser();
		$row   = new EShopTable($this->_tableName, 'id', $db);
		$isNew = true;

		if ($data['id'])
		{
			$row->load($data['id']);
			$isNew = false;
		}

		if (!$row->id && property_exists($row, 'ordering'))
		{
			$row->ordering = $row->getNextOrder($this->getWhereNextOrdering());
		}
		if (property_exists($row, 'hits') && !$row->hits)
		{
			$row->hits = 0;
		}
		if (property_exists($row, 'created_date') && !$row->created_date)
		{
			$row->created_date = Factory::getDate()->toSql();
		}
		if (property_exists($row, 'created_by') && !$row->created_by)
		{
			$row->created_by = $user->get('id');
		}
		if (property_exists($row, 'modified_date'))
		{
			$row->modified_date = Factory::getDate()->toSql();
		}
		if (property_exists($row, 'modified_by'))
		{
			$row->modified_by = $user->get('id');
		}
		if (property_exists($row, 'checked_out'))
		{
			$row->checked_out = 0;
		}
		if (property_exists($row, 'checked_out_time'))
		{
			$row->checked_out_time = '0000-00-00 00:00:00';
		}
		if (isset($data[$this->name . '_alias']) && empty($data[$this->name . '_alias']))
		{
			$data[$this->name . '_alias'] = ApplicationHelper::stringUrlSafe($data[$this->name . '_name']);
		}
		if (!$row->bind($data))
		{
			throw new Exception($row->getError());
		}
		if (!$row->check())
		{
			throw new Exception($row->getError());
		}

		$this->beforeStore($row, $data, $isNew);

		if ($this->triggerEvents)
		{
			PluginHelper::importPlugin($this->pluginGroup);
			$result = Factory::getApplication()->triggerEvent($this->eventBeforeSave, [$row, $data, $isNew]);


			if (in_array(false, $result, true))
			{
				throw new Exception($row->getError());
			}
		}

		if (!$row->store())
		{
			throw new Exception($row->getError());
		}

		$data['id'] = $row->id;
		//Adding support for translable objects
		if ($this->translatable)
		{
			if (Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
			{
				$languages = EShopHelper::getLanguages();
				foreach ($languages as $language)
				{
					$langCode                          = $language->lang_code;
					$detailsRow                        = new EShopTable(EShopInflector::singularize($this->_tableName) . 'details', 'id', $db);
					$detailsRow->id                    = $data['details_id_' . $langCode];
					$detailsRow->{$this->name . '_id'} = $data['id'];
					foreach ($this->translatableFields as $field)
					{
						if ($field == $this->name . '_alias')
						{
							if (empty($data[$this->name . '_alias_' . $langCode]))
							{
								$detailsRow->{$field} = ApplicationHelper::stringUrlSafe($data[$this->name . '_name_' . $langCode]);
							}
							else
							{
								$detailsRow->{$field} = $data[$this->name . '_alias_' . $langCode];
							}
						}
						else
						{
							$detailsRow->{$field} = $data[$field . '_' . $langCode];
						}
					}
					$detailsRow->language = $langCode;
					$detailsRow->store();
				}
			}
			else
			{
				$detailsRow                        = Table::getInstance('Eshop', ucfirst($this->name . 'detail'));
				$detailsRow->id                    = $data['details_id'];
				$detailsRow->{$this->name . '_id'} = $data['id'];
				foreach ($this->translatableFields as $field)
				{
					if ($field == $this->name . '_alias')
					{
						if (empty($data[$this->name . '_alias']))
						{
							$detailsRow->{$field} = ApplicationHelper::stringUrlSafe($data[$this->name . '_name']);
						}
						else
						{
							$detailsRow->{$field} = $data[$this->name . '_alias'];
						}
					}
					else
					{
						$detailsRow->{$field} = $data[$field];
					}
				}
				$detailsRow->language = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

				$detailsRow->store();
			}
		}

		if ($this->triggerEvents)
		{
			Factory::getApplication()->triggerEvent($this->eventAfterSave, [$row, $data, $isNew]);
		}

		$this->afterStore($row, $data, $isNew);

		return true;
	}

	/**
	 * Method to remove items
	 *
	 * @access    public
	 * @return boolean True on success
	 * @since     1.5
	 */
	public function delete($cid = [])
	{
		if (count($cid))
		{
			$db    = $this->getDbo();
			$cids  = implode(',', $cid);
			$query = $db->getQuery(true);
			$query->delete($this->_tableName)
				->where('id IN (' . $cids . ')');
			$db->setQuery($query);
			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			// Delete details records
			if ($this->translatable)
			{
				$query->clear()
					->delete(EShopInflector::singularize($this->_tableName) . 'details')
					->where($this->name . '_id IN (' . $cids . ')');
				$db->setQuery($query);
				if (!$db->execute())
				{
					//Removed error
					return 0;
				}
			}

			if ($db->getAffectedRows() < count($cid))
			{
				//Removed warning
				return 2;
			}
		}

		//Removed success
		return 1;
	}

	/**
	 * Load the data
	 *
	 */
	public function _loadData()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($this->_tableName)
			->where('id = ' . intval($this->_id));
		$db->setQuery($query);

		$this->_data = $db->loadObject();
		if ($this->translatable)
		{
			if (Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
			{
				$query->clear();
				$query->select('*')
					->from(EShopInflector::singularize($this->_tableName) . 'details')
					->where($this->name . '_id = ' . $this->_id);
				$db->setQuery($query);
				$rows = $db->loadObjectList('language');
				if (count($rows))
				{
					foreach ($rows as $language => $row)
					{
						foreach ($this->translatableFields as $field)
						{
							if ($field == $this->name . '_name')
							{
								$this->_data->{$field . '_' . $language} = htmlspecialchars($row->{$field});
							}
							else
							{
								$this->_data->{$field . '_' . $language} = $row->{$field};
							}
						}
						$this->_data->{'details_id_' . $language} = $row->id;
					}
				}
			}
			else
			{
				$query->clear();
				$query->select('*')
					->from(EShopInflector::singularize($this->_tableName) . 'details')
					->where($this->name . '_id = ' . intval($this->_id))
					->where('language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
				$db->setQuery($query);
				$row = $db->loadObject();
				if (is_object($row))
				{
					foreach ($this->translatableFields as $field)
					{
						if ($field == $this->name . '_name')
						{
							$this->_data->{$field} = htmlspecialchars($row->{$field});
						}
						else
						{
							$this->_data->{$field} = $row->{$field};
						}
					}
					$this->_data->{'details_id'} = $row->id;
				}
			}
		}
	}

	/**
	 * Init data
	 *
	 */
	public function _initData()
	{
		$db = $this->getDbo();

		if (is_file(JPATH_ADMINISTRATOR . '/components/com_eshop/tables/' . $this->name . '.php'))
		{
			$row = $this->getTable($this->name, $this->_component . 'Table');
		}
		else
		{
			$row = new EShopTable($this->_tableName, 'id', $db);
		}

		$this->_data = $row;

		if ($this->translatable)
		{
			if (Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
			{
				$languages = EShopHelper::getLanguages();
				foreach ($languages as $language)
				{
					$langCode = $language->lang_code;
					foreach ($this->translatableFields as $field)
					{
						$this->_data->{$field . '_' . $langCode} = '';
					}
				}
				$this->_data->{'details_id_' . $langCode} = '';
			}
			else
			{
				foreach ($this->translatableFields as $field)
				{
					$this->_data->{$field} = '';
				}
				$this->_data->{'details_id'} = '';
			}
		}
	}

	/**
	 * Publish the selected items
	 *
	 * @param   array  $cid
	 *
	 * @return boolean
	 */
	public function publish($cid, $state)
	{
		if (count($cid))
		{
			$db   = $this->getDbo();
			$cids = implode(',', $cid);

			$query = $db->getQuery(true);
			$query->update($this->_tableName)
				->set('published = ' . $state)
				->where('id IN (' . $cids . ')');
			$db->setQuery($query);
			if (!$db->execute())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Save the order of entities
	 *
	 * @param   array  $cid
	 * @param   array  $order
	 */
	public function saveOrder($cid, $order)
	{
		$db = $this->getDbo();
		if (is_file(JPATH_ADMINISTRATOR . '/components/com_eshop/tables/' . $this->name . '.php'))
		{
			$row = $this->getTable($this->getName(), $this->getName());
		}
		else
		{
			$row = new EShopTable($this->_tableName, 'id', $db);
		}
		$groupings = [];
		// update ordering values
		for ($i = 0; $i < count($cid); $i++)
		{
			$row->load((int) $cid[$i]);
			// track parents
			if (property_exists($row, $this->name . '_parent_id'))
			{
				$groupings[] = $row->{$this->name . '_parent_id'};
			}
			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store())
				{
					throw new Exception($row->getError());
				}
			}
		}
		// execute updateOrder for each parent group
		$groupings = array_unique($groupings);
		foreach ($groupings as $group)
		{
			$row->reorder($this->name . '_parent_id = ' . (int) $group);
		}

		return true;
	}

	/**
	 * Change ordering of ann item
	 *
	 */
	public function move($direction)
	{
		$db = $this->getDbo();
		if (is_file(JPATH_ADMINISTRATOR . '/components/com_eshop/tables/' . $this->name . '.php'))
		{
			$row = $this->getTable($this->name, $this->_component . 'Table');
		}
		else
		{
			$row = new EShopTable($this->_tableName, 'id', $db);
		}
		$row->load($this->_id);
		if (!$row->move($direction))
		{
			throw new Exception($row->getError());
		}

		return true;
	}

	/**
	 * Copy an entity
	 *
	 */
	public function copy($id)
	{
		//Copy from the main table
		$db = $this->getDbo();
		if (is_file(JPATH_ADMINISTRATOR . '/components/com_eshop/tables/' . $this->name . '.php'))
		{
			$row    = $this->getTable($this->name, $this->_component . 'Table');
			$rowOld = $this->getTable($this->name, $this->_component . 'Table');
		}
		else
		{
			$row    = new EShopTable($this->_tableName, 'id', $db);
			$rowOld = new EShopTable($this->_tableName, 'id', $db);
		}
		$rowOld->load($id);
		$data       = ArrayHelper::fromObject($rowOld);
		$data['id'] = 0;
		if (isset($data[$this->name . '_name']))
		{
			$data[$this->name . '_name'] = $rowOld->{$this->name . '_name'} . ' ' . Text::_('ESHOP_COPY');
		}
		//Make a copy of image
		$imageField = $this->name . '_image';
		if (isset($rowOld->{$imageField}) && $rowOld->{$imageField} != '')
		{
			$oldImage = $rowOld->{$imageField};
			if (is_file(JPATH_ROOT . '/media/com_eshop/' . EShopInflector::pluralize($this->name) . '/' . $oldImage))
			{
				$newImage = File::stripExt($oldImage) . ' ' . Text::_('ESHOP_COPY') . '.' . EShopHelper::getFileExt($oldImage);
				if (File::copy(
					JPATH_ROOT . '/media/com_eshop/' . EShopInflector::pluralize($this->name) . '/' . $oldImage,
					JPATH_ROOT . '/media/com_eshop/' . EShopInflector::pluralize($this->name) . '/' . $newImage
				))
				{
					//resize copied image
					EShopHelper::resizeImage($newImage, JPATH_ROOT . '/media/com_eshop/' . EShopInflector::pluralize($this->name) . '/', 100, 100);
					$data[$this->name . '_image'] = $newImage;
				}
			}
		}
		$row->bind($data);
		$row->check();
		if (property_exists($row, 'ordering'))
		{
			$row->ordering = $row->getNextOrder($this->getWhereNextOrdering());
		}
		if (property_exists($row, 'hits'))
		{
			$row->hits = 0;
		}
		$row->published = 0;
		$row->store();
		//Copy from the details table
		if ($this->translatable)
		{
			$query = $db->getQuery(true);
			$query->select('id')
				->from(EShopInflector::singularize($this->_tableName) . 'details')
				->where($this->name . '_id = ' . $id);
			$db->setQuery($query);
			$detailIds = $db->loadColumn();
			foreach ($detailIds as $detailId)
			{
				$detailsRow    = Table::getInstance('Eshop', ucfirst($this->name . 'detail'));
				$detailsRowOld = Table::getInstance('Eshop', ucfirst($this->name . 'detail'));

				$detailsRowOld->load($detailId);
				$data                        = ArrayHelper::fromObject($detailsRowOld);
				$data['id']                  = 0;
				$data[$this->name . '_id']   = $row->id;
				$data[$this->name . '_name'] = $detailsRowOld->{$this->name . '_name'} . ' ' . Text::_('ESHOP_COPY');
				if (isset($data[$this->name . '_alias']))
				{
					$data[$this->name . '_alias'] = ApplicationHelper::stringUrlSafe($data[$this->name . '_name']);
				}
				$detailsRow->bind($data);
				$detailsRow->check();
				$detailsRow->store();
			}
		}

		return $row->id;
	}

	public function getWhereNextOrdering()
	{
		return '';
	}

	/**
	 * Give a chance for child class to pre-process the data
	 *
	 * @param $row
	 * @param $data
	 * @param $isNew bool
	 */
	protected function beforeStore($row, $data, $isNew)
	{
	}

	/**
	 * Give a chance for child class to post-process the data
	 *
	 * @param $row
	 * @param $data
	 * @param $isNew bool
	 */
	protected function afterStore($row, $data, $isNew)
	{
	}
}
