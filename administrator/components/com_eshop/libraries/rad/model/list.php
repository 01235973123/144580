<?php
/**
 * @package     Joomla.EshopRAD
 * @subpackage  ModelList
 *
 * @author      Ossolution Team
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;

class EshopRADModelList extends EshopRADModel
{
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
	 * List of fields which will be used for searching data from database table
	 *
	 * @var array
	 */
	protected $searchFields = [];

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
	 * @var Pagination
	 */
	protected $pagination;

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  The configuration data for the model
	 *
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);
		$app     = Factory::getApplication();
		$context = $this->option . '.' . $this->name . '.';
		if (!isset($config['remember_states']))
		{
			$config['remember_states'] = true;
		}
		if ($config['remember_states'])
		{
			$this->state->insert('limit', 'int', $app->getUserStateFromRequest($context . 'limit', 'limit', $app->get('list_limit')))
				->insert('limitstart', 'int', $app->getUserStateFromRequest($context . 'limitstart', 'limitstart', 0))
				->insert('filter_order', 'cmd', $app->getUserStateFromRequest($context . 'filter_order', 'filter_order', 'a.ordering'))
				->insert('filter_order_Dir', 'word', $app->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', 'asc'))
				->insert('filter_search', 'string', $app->getUserStateFromRequest($context . 'filter_search', 'filter_search'))
				->insert('filter_access', 'int', $app->getUserStateFromRequest($context . 'filter_access', 'filter_access'));
		}
		else
		{
			$this->state->insert('limit', 'int', $app->get('list_limit'))
				->insert('limitstart', 'int', 0)
				->insert('filter_order', 'cmd', 'a.ordering')
				->insert('filter_order_Dir', 'word', 'asc')
				->insert('filter_search', 'string')
				->insert('filter_access', 'int', 0);
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
		if (isset($config['search_fields']))
		{
			$this->searchFields = (array) $config['search_fields'];
		}
		else
		{
			//Build the search field array automatically, basically, we should search based on name, title, description if these fields are available
			if ($this->translatable)
			{
				$table  = new EshopRADTable(EshopRADInflector::singularize($this->table) . 'details', 'id', $this->db);
				$fields = array_keys($table->getFields());
			}
			else
			{
				$table  = new EshopRADTable($this->table, 'id', $this->db);
				$fields = array_keys($table->getFields());
			}
			$fieldPrefix = strtolower(EshopRADInflector::singularize($this->name)) . '_';
			if (in_array($fieldPrefix . 'name', $fields))
			{
				$this->searchFields[] = 'a.' . $fieldPrefix . 'name';
			}
			if (in_array($fieldPrefix . 'title', $fields))
			{
				$this->searchFields[] = 'a.' . $fieldPrefix . 'title';
			}
			if (in_array($fieldPrefix . 'description', $fields))
			{
				$this->searchFields[] = 'a.' . $fieldPrefix . 'description';
			}
		}
	}

	/**
	 * Get a list of items
	 *
	 * @return  array
	 */
	public function getData()
	{
		if (empty($this->data))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$this->_buildQueryColumns($query)
				->_buildQueryFrom($query)
				->_buildQueryJoins($query)
				->_buildQueryWhere($query)
				->_buildQueryGroup($query)
				->_buildQueryHaving($query)
				->_buildQueryOrder($query);
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
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$this->_buildQueryFrom($query)
				->_buildQueryJoins($query)
				->_buildQueryWhere($query);
			$db->setQuery($query);
			$this->total = (int) $db->loadResult();
		}

		return $this->total;
	}

	/**
	 * Get pagination object
	 *
	 * @return Pagination
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			$this->pagination = new Pagination($this->getTotal(), $this->state->limitstart, $this->state->limit);
			$this->pagination->setAdditionalUrlParam('keyword', $this->state->keyword);
		}

		return $this->pagination;
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select(['a.*']);

		if ($this->translatable)
		{
			$query->select($this->translatableFields);
		}

		return $this;
	}

	/**
	 * Builds FROM tables list for the query
	 */
	protected function _buildQueryFrom(JDatabaseQuery $query)
	{
		$query->from($this->table . ' AS a');

		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		if ($this->translatable)
		{
			$query->innerJoin(
				EshopRADInflector::singularize($this->table) . 'details AS b ON a.id = b.' . strtolower(
					EshopRADInflector::singularize($this->name)
				) . '_id'
			);
		}

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$user  = Factory::getUser();
		$db    = $this->getDbo();
		$state = $this->state;
		$query->where('a.published = 1');
		if ($state->filter_access)
		{
			$query->where('a.access = ' . (int) $state->filter_access);
			if (!$user->authorise('core.admin'))
			{
				$query->where('a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
			}
		}

		if ($state->filter_search)
		{
			if (stripos($state->filter_search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($state->filter_search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($state->filter_search, true) . '%', false);
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
		if ($this->translatable)
		{
			$query->where('b.language=' . $this->db->quote(Factory::getLanguage()->getTag()));
		}

		return $this;
	}

	/**
	 * Builds a GROUP BY clause for the query
	 */
	protected function _buildQueryGroup(JDatabaseQuery $query)
	{
		return $this;
	}

	/**
	 * Builds a HAVING clause for the query
	 */
	protected function _buildQueryHaving(JDatabaseQuery $query)
	{
		return $this;
	}

	/**
	 * Builds a generic ORDER BY clasue based on the model's state
	 */
	protected function _buildQueryOrder(JDatabaseQuery $query)
	{
		$sort      = $this->state->filter_order;
		$direction = strtoupper($this->state->filter_order_Dir);
		if ($sort)
		{
			$query->order($sort . ' ' . $direction);
		}
	}
}
