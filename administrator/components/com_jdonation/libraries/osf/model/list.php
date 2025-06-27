<?php

/**
 * @package     OSF
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2024 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;

/**
 * Model class for handling lists of items.
 *
 * @package     OSF
 * @subpackage  Model
 * @since       1.0
 */
class OSFModelList extends OSFModel
{

	/**
	 * List total
	 *
	 * @var integer
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
	 * @var JPagination
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
	protected $searchFields = array();
			
	/**
	 * Remember model states, always set to true for model list
	 * @var boolean
	 */
	public $rememberStates = true;

	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 *        	  	
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

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
		$this->state->insert('limit', 'int', Factory::getConfig()->get('list_limit'))
			->insert('limitstart', 'int', 0)
			->insert('filter_order', 'cmd', $defaultOrdering)
			->insert('filter_order_Dir', 'word', 'asc')
			->insert('filter_search', 'string')
			->insert('filter_state', 'string')
			->insert('filter_access', 'int', 0)
			->insert('filter_language', 'string');
		
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
	}

	/**
	 * Get a list of items
	 *
	 * @return array
	 */
	public function getData()
	{
		if (empty($this->data))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$this->buildQueryColumns($query)
				->buildQueryFrom($query)
				->buildQueryJoins($query)
				->buildQueryWhere($query)
				->buildQueryGroup($query)
				->buildQueryHaving($query)
				->buildQueryOrder($query);
			//echo $query->__toString(); die();
			$db->setQuery($query, $this->state->limitstart, $this->state->limit);
			$this->data = $db->loadObjectList();
		}
		
		return $this->data;
	}

	/**
	 * Get total record
	 *
	 * @return integer Number of records
	 *        
	 */
	public function getTotal()
	{
		if (empty($this->total))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$this->buildQueryFrom($query)
				->buildQueryWhere($query);
			$db->setQuery($query);
			$this->total = (int) $db->loadResult();
		}
		
		return $this->total;
	}

	/**
	 * Get pagination object
	 *
	 * @return JPagination
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
            jimport('joomla.html.pagination');
			$this->pagination = new Pagination($this->getTotal(), $this->state->limitstart, $this->state->limit);
		}
		
		return $this->pagination;
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function buildQueryColumns($query)
	{
		$query->select(array('tbl.*'));
		
		return $this;
	}

	/**
	 * Builds FROM tables list for the query
	 */
	protected function buildQueryFrom($query)
	{
		$query->from($this->table . ' AS tbl');
		
		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function buildQueryJoins($query)
	{
		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function buildQueryWhere($query)
	{
		$user = Factory::getApplication()->getIdentity();
		$db = $this->getDbo();
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
                $query->where('tbl.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
            }
		}
		if ($state->filter_search)
		{
            //Remove blank space from searching
            $state->filter_search = trim($state->filter_search);
			if (stripos($state->search, 'id:') === 0)
			{
				$query->where('tbl.id = ' . (int) substr($state->filter_search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($state->filter_search, true) . '%', false);
				if (is_array($this->searchFields))
				{
					$whereOr = array();
					foreach ($this->searchFields as $searchField)
					{
						$whereOr[] = " LOWER($searchField) LIKE " . $search;
					}
					$query->where('(' . implode(' OR ', $whereOr) . ') ');
				}
			}
		}
		
		if ($state->filter_language && $state->filter_language != '*')
		{
			$query->where('tbl.language IN (' . $db->Quote($state->filter_language) . ',' . $db->Quote('*') . ', "")');
		}
		
		return $this;
	}

	/**
	 * Builds a GROUP BY clause for the query
	 */
	protected function buildQueryGroup($query)
	{
		return $this;
	}

	/**
	 * Builds a HAVING clause for the query
	 */
	protected function buildQueryHaving($query)
	{
		return $this;
	}

	/**
	 * Builds a generic ORDER BY clasue based on the model's state
	 */
	protected function buildQueryOrder($query)
	{
		$sort = $this->state->filter_order;
		$direction = strtoupper($this->state->filter_order_Dir);
		if ($sort)
		{
			$query->order($sort . ' ' . $direction);
		}
	}
}
