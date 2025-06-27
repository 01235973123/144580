<?php
/**
 * @version        1.0
 * @package        Joomla
 * @subpackage     OSFramework
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;

class EShopModelList extends ListModel
{

	/**
	 * Context, using for store permanent information
	 *
	 * @var string
	 */
	protected $context = null;

	/**
	 * search fields using for searching
	 *
	 * @var string
	 */
	protected $searchFields = null;

	/**
	 *
	 * @var main database table which we will query data from
	 */
	protected $mainTable = null;

	/**
	 * This object can be translated into different language or not
	 *
	 * @var Boolean
	 */
	protected $translatable = false;

	/**
	 * List of fields which can be translated
	 * @var array
	 */
	protected $translatableFields = [];

	/**
	 * Total records
	 *
	 * @var int
	 */
	protected $_total = 0;

	/**
	 * Entitires data array
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination = null;

	/**
	 * Constructor.
	 *
	 * @param
	 *            array An optional associative array of configuration settings.
	 *
	 * @see   JController
	 * @since 1.6
	 */
	public function __construct($config = [])
	{
		parent::__construct();

		$input         = Factory::getApplication()->input;
		$mainframe     = Factory::getApplication();
		$baseStateVars = [
			'search'           => ['', 'string', 1],
			'filter_order'     => ['a.id', 'cmd', 1],
			'filter_order_Dir' => ['', 'cmd', 1],
			'filter_state'     => ['', 'cmd', 1],
		];

		if (isset($config['state_vars']))
		{
			$config['state_vars'] = array_merge($baseStateVars, $config['state_vars']);
		}
		else
		{
			$config['state_vars'] = $baseStateVars;
		}

		if (isset($config['search_fields']))
		{
			$this->searchFields = $config['search_fields'];
		}
		else
		{
			$this->searchFields = 'a.' . EShopInflector::singularize($this->name) . '_name';
		}

		if (isset($config['main_table']))
		{
			$this->mainTable = $config['main_table'];
		}
		else
		{
			$this->getMainTable();
		}
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
		if (isset($config['context']))
		{
			$this->context = $config['context'];
		}
		else
		{
			$this->getContext();
		}

		if (isset($config['state_vars']))
		{
			foreach ($config['state_vars'] as $name => $values)
			{
				$storeInSession = $values[2] ?? 0;
				$type           = $values[1] ?? null;
				$default        = $values[0] ?? null;
				if ($storeInSession)
				{
					$value = $mainframe->getUserStateFromRequest($this->context . '.' . $name, $name, $default, $type);
				}
				else
				{
					$value = $input->get($name, $default, 'default', $type);
				}

				$this->setState($name, $value);
			}
		}

		$fullOrdering = $mainframe->getUserStateFromRequest($this->context . '.' . 'filter_full_ordering', 'filter_full_ordering', '');

		if ($fullOrdering)
		{
			$parts     = explode(' ', $fullOrdering);
			$sort      = $parts[0];
			$direction = $parts[1] ?? '';

			$this->setState('filter_order', $sort);
			$this->setState('filter_order_Dir', $direction);
		}

		// Get the pagination request variables
		$limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->get('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to get categories data
	 *
	 * @access public
	 * @return array
	 */
	public function getItems()
	{
		// Lets load the content if it doesn't already exist


		if (empty($this->_data))
		{
			// Adjust the limitStart state property
			$limit = $this->getState('limit');
			
			if ($limit)
			{
				$offset = $this->getState('limitstart');
				$total  = $this->getTotal();
			
				//If the offset is higher than the total recalculate the offset
				if ($offset !== 0 && $total !== 0 && $offset >= $total)
				{
					$offset                  = floor(($total - 1) / $limit) * $limit;
					$this->setState('limitstart', $offset);
				}
			}
			
			$query       = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	/**
	 * Get total entities
	 *
	 * @return int
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$db    = $this->getDbo();
			$where = $this->_buildContentWhereArray();
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			if ($this->translatable)
			{
				$query->from($this->mainTable . ' AS a ')
					->innerJoin(
						EShopInflector::singularize($this->mainTable) . 'details AS b ON (a.id = b.' . EShopInflector::singularize(
							$this->name
						) . '_id)'
					);
			}
			else
			{
				$query->from($this->mainTable . ' AS a ');
			}
			if (count($where))
			{
				$query->where($where);
			}

			$db->setQuery($query);
			$this->_total = $db->loadResult();
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			$this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Basic build Query function.
	 * The child class must override it if it is necessary
	 *
	 * @return string
	 */
	public function _buildQuery()
	{
		$db    = $this->getDbo();
		$state = $this->getState();
		$query = $db->getQuery(true);
		if ($this->translatable)
		{
			$query->select('a.*, ' . implode(', ', $this->translatableFields))
				->from($this->mainTable . ' AS a ')
				->innerJoin(
					EShopInflector::singularize($this->mainTable) . 'details AS b ON (a.id = b.' . EShopInflector::singularize($this->name) . '_id)'
				);
		}
		else
		{
			$query->select('a.*')
				->from($this->mainTable . ' AS a ');
		}
		$where = $this->_buildContentWhereArray();
		if (count($where))
		{
			$query->where($where);
		}
		$orderby = $this->_buildContentOrderBy();
		if ($orderby != '')
		{
			$query->order($orderby);
		}

		return $query;
	}

	/**
	 *
	 * Build order by clause for the select command
	 * @return string order by clause
	 */
	public function _buildContentOrderBy()
	{
		$state   = $this->getState();
		$orderby = '';

		if ($state->filter_order != '')
		{
			$orderby = $state->filter_order . ' ' . $state->filter_order_Dir;
		}

		return $orderby;
	}

	/**
	 * Build an where clause array
	 *
	 * @return array
	 */
	public function _buildContentWhereArray()
	{
		$db    = $this->getDbo();
		$state = $this->getState();
		$where = [];
		if ($state->filter_state == 'P')
		{
			$where[] = ' a.published=1 ';
		}
		elseif ($state->filter_state == 'U')
		{
			$where[] = ' a.published = 0';
		}

		if ($state->search)
		{
			$search = $db->quote('%' . $db->escape(strtolower($state->search), true) . '%', false);
			if (is_array($this->searchFields))
			{
				$whereOr = [];
				foreach ($this->searchFields as $titleField)
				{
					$whereOr[] = " LOWER($titleField) LIKE " . $search;
				}
				$where[] = ' (' . implode(' OR ', $whereOr) . ') ';
			}
			else
			{
				$where[] = 'LOWER(' . $this->searchFields . ') LIKE ' . $db->quote('%' . $db->escape(strtolower($state->search), true) . '%', false);
			}
		}

		if ($this->translatable)
		{
			$where[] = 'b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"';
		}

		return $where;
	}

	public function getContext()
	{
		if (empty($this->context))
		{
			$r = null;
			if (preg_match('/(.*)Model/i', get_class($this), $r))
			{
				$component     = $r[1];
				$this->context = $component . '.' . $this->getName();
			}
		}

		return $this->context;
	}

	/**
	 * Get name of database table use for query
	 *
	 * @return string The main database table
	 */
	public function getMainTable()
	{
		$db = $this->getDbo();
		if (empty($this->mainTable))
		{
			$this->mainTable = $db->getPrefix() . strtolower(ESHOP_TABLE_PREFIX . '_' . $this->getName());
		}

		return $this->mainTable;
	}
}
