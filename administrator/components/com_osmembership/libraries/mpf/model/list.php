<?php
/**
 * @package     MPF
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

/**
 * Model class for handling lists of items.
 *
 * @package     MPF
 * @subpackage  Model
 * @since       2.0
 */
class MPFModelList extends MPFModel
{
	/**
	 * The query object of the model
	 *
	 * @var \Joomla\Database\DatabaseQuery
	 */
	protected $query;
	/**
	 * List total
	 *
	 * @var int
	 */
	protected $total;

	/**
	 * Model list data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Pagination object
	 *
	 * @var \Joomla\CMS\Pagination\Pagination
	 */
	protected $pagination;

	/**
	 * Name of state field name, usually be tbl.state or tbl.published
	 *
	 * @var string
	 */
	protected $stateField;

	/**
	 * List of fields which will be used for searching data from database table
	 *
	 * @var array
	 */
	protected $searchFields = [];

	/**
	 * Remember model states, always set to true for model list
	 * @var bool
	 */
	public $rememberStates = true;

	/**
	 * Clear join clause for getTotal method
	 *
	 * @var bool
	 */
	protected $clearJoin = true;

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->query = $this->db->getQuery(true);

		$fields = array_keys($this->db->getTableColumns($this->table));

		if (in_array('ordering', $fields))
		{
			$defaultOrdering = 'tbl.ordering';
		}
		else
		{
			$defaultOrdering = 'tbl.id';
		}

		if (in_array('published', $fields))
		{
			$this->stateField = 'tbl.published';
		}
		else
		{
			$this->stateField = 'tbl.state';
		}

		$listLimit = Factory::getApplication()->get('list_limit');

		if ($listLimit > 100)
		{
			$listLimit = 100;
		}

		$this->state->insert('limit', 'int', $listLimit)
			->insert('limitstart', 'int', 0)
			->insert('filter_order', 'cmd', $defaultOrdering)
			->insert('filter_order_Dir', 'word', 'asc')
			->insert('filter_search', 'string', '')
			->insert('filter_state', 'string')
			->insert('filter_access', 'int', 0)
			->insert('filter_language', 'string')
			->insert('filter_full_ordering', 'string');

		if (isset($config['search_fields']))
		{
			$this->searchFields = (array) $config['search_fields'];
		}
		else
		{
			// Build the search field array automatically, basically, we should search based on name, title, description if these fields are available
			if (in_array('name', $fields))
			{
				$this->searchFields[] = 'tbl.name';
			}

			if (in_array('title', $fields))
			{
				$this->searchFields[] = 'tbl.title';
			}

			if (in_array('alias', $fields))
			{
				$this->searchFields[] = 'tbl.alias';
			}
		}

		if (isset($config['clear_join']))
		{
			$this->clearJoin = $config['clear_join'];
		}
	}

	/**
	 * Get a list of items
	 *
	 * @param   bool  $returnIterator
	 *
	 * @return array
	 */
	public function getData($returnIterator = false)
	{
		if (empty($this->data))
		{
			$db = $this->getDbo();

			$query = $this->buildListQuery();

			$this->beforeQueryData($query);

			// Adjust the limitStart state property
			$limit = $this->state->limit;

			if ($limit)
			{
				$offset = $this->state->limitstart;
				$total  = $this->getTotal();

				//If the offset is higher than the total recalculate the offset
				if ($offset !== 0 && $total !== 0 && $offset >= $total)
				{
					$offset                  = floor(($total - 1) / $limit) * $limit;
					$this->state->limitstart = $offset;
				}
			}

			$db->setQuery($query, $this->state->limitstart, $this->state->limit);

			if ($returnIterator)
			{
				$this->data = $db->getIterator();
			}
			else
			{
				$this->data = $db->loadObjectList();
			}

			$this->beforeReturnData($this->data);

			// Store the query so that it can be used in getTotal method if needed
			$this->query = $query;
		}

		return $this->data;
	}

	/**
	 * Get total record. Child class should override this method if needed
	 *
	 * @return int Number of records
	 */
	public function getTotal()
	{
		if (empty($this->total))
		{
			$db    = $this->getDbo();
			$query = $this->buildTotalQuery();
			$this->beforeQueryTotal($query);
			$db->setQuery($query);
			$this->total = (int) $db->loadResult();
		}

		return $this->total;
	}

	/**
	 * Get pagination object
	 *
	 * @return \Joomla\CMS\Pagination\Pagination
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			$this->pagination = new Pagination($this->getTotal(), $this->state->limitstart, $this->state->limit);
		}

		return $this->pagination;
	}

	/**
	 * Return the query, allow external code a change to manipulate the query when required
	 *
	 * @return \Joomla\Database\DatabaseQuery
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * Build the query object which is used to get list of records from database
	 *
	 * @return \Joomla\Database\DatabaseQuery
	 */
	protected function buildListQuery()
	{
		$query = $this->query;

		$this->buildQueryColumns($query)
			->buildQueryFrom($query)
			->buildQueryJoins($query)
			->buildQueryWhere($query)
			->buildQueryGroup($query)
			->buildQueryHaving($query)
			->buildQueryOrder($query);

		return $query;
	}

	/**
	 * Build query object use to get total records from database
	 *
	 * @return \Joomla\Database\DatabaseQuery
	 */
	protected function buildTotalQuery()
	{
		$query = clone $this->query;

		$query->clear('select')
			->clear('group')
			->clear('having')
			->clear('order')
			->clear('limit')
			->select('COUNT(*)');

		// Clear join clause if needed
		if ($this->clearJoin)
		{
			$query->clear('join');
		}

		return $query;
	}

	/**
	 * Builds SELECT columns list for the query
	 *
	 * @param   \Joomla\Database\DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryColumns(DatabaseQuery $query)
	{
		$query->select(['tbl.*']);

		return $this;
	}

	/**
	 * Builds FROM tables list for the query
	 *
	 * @param   \Joomla\Database\DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryFrom(DatabaseQuery $query)
	{
		$query->from($this->table . ' AS tbl');

		return $this;
	}

	/**
	 * Builds JOINS clauses for the query
	 *
	 * @param   \Joomla\Database\DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryJoins(DatabaseQuery $query)
	{
		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param   \Joomla\Database\DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(DatabaseQuery $query)
	{
		$user  = Factory::getApplication()->getIdentity();
		$db    = $this->getDbo();
		$state = $this->state;

		if ($state->filter_state == 'P')
		{
			$query->where($this->stateField . ' = 1');
		}
		elseif ($state->filter_state == 'U')
		{
			$query->where($this->stateField . ' = 0');
		}

		if ($state->filter_access)
		{
			$query->where('tbl.access = ' . (int) $state->filter_access);

			if (!$user->authorise('core.admin'))
			{
				$query->whereIn('tbl.access', $user->getAuthorisedViewLevels());
			}
		}

		$state->filter_search = trim($state->filter_search);

		if ($state->filter_search)
		{
			$this->applySearchFilter($query);
		}

		if ($state->filter_language && $state->filter_language != '*')
		{
			$query->whereIn('tbl.language', [$state->filter_language, '*'], ParameterType::STRING);
		}

		return $this;
	}

	/**
	 * Apply search filter
	 *
	 * @param   \Joomla\Database\DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function applySearchFilter(DatabaseQuery $query)
	{
		$db    = $this->getDbo();
		$state = $this->state;

		//Remove blank space from searching
		if (stripos($state->filter_search, 'id:') === 0)
		{
			$query->where('tbl.id = ' . (int) substr($state->filter_search, 3));
		}
		else
		{
			$search = $db->quote('%' . $db->escape($state->filter_search, true) . '%', false);

			if (is_array($this->searchFields))
			{
				$whereOr = [];

				foreach ($this->searchFields as $searchField)
				{
					$whereOr[] = " LOWER($searchField) LIKE " . $search;
				}

				$query->where('(' . implode(' OR ', $whereOr) . ') ');
			}
		}
	}

	/**
	 * Builds a GROUP BY clause for the query
	 *
	 * @param   \Joomla\Database\DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryGroup(DatabaseQuery $query)
	{
		return $this;
	}

	/**
	 * Builds a HAVING clause for the query
	 *
	 * @param   \Joomla\Database\DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryHaving(DatabaseQuery $query)
	{
		return $this;
	}

	/**
	 * Builds a generic ORDER BY clasue based on the model's state
	 *
	 * @param   \Joomla\Database\DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryOrder(DatabaseQuery $query)
	{
		$sort      = $this->state->filter_order;
		$direction = strtoupper($this->state->filter_order_Dir);
		if ($sort)
		{
			$query->order($sort . ' ' . $direction);
		}

		return $this;
	}

	/**
	 * This method give child class a chance to adjust the query before it is run to return list of records
	 *
	 * @param   \Joomla\Database\DatabaseQuery  $query
	 */
	protected function beforeQueryData(DatabaseQuery $query)
	{
	}

	/**
	 * This method give child class a chance to adjust the query object before it is run to return total records
	 *
	 * @param   \Joomla\Database\DatabaseQuery  $query
	 */
	protected function beforeQueryTotal(DatabaseQuery $query)
	{
	}

	/**
	 * This method give child class to adjust the return data in getData method without having to override the
	 * whole method
	 *
	 * @param   array  $rows
	 */
	protected function beforeReturnData($rows)
	{
	}
}
