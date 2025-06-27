<?php
/**
 * @package     RAD
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2015 - 2025 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

defined('_JEXEC') or die;

/**
 * Class to help query data from database
 *
 * @package       RAD
 * @subpackage    Entity
 * @since         2.0
 *
 * @method self innerJoin($table, $condition = null) Inner Join with other database table
 * @method self leftJoin($table, $condition = null) Left Join with other database table
 * @method self setLimit($limit = 0, $offset = 0) Set limit for the query
 * @method self having($conditions, $glue = 'AND') Add having clause for the query
 */
class RADEntityQuery
{
	/**
	 * Datbase driver object
	 *
	 * @var DatabaseDriver
	 */
	protected $db;

	/**
	 * The table
	 * @var string
	 */
	protected $table;

	/**
	 * The primrary key of the table
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * Database query object
	 *
	 * @var \Joomla\Database\DatabaseQuery
	 */
	protected $query;

	/**
	 * The methods that should be returned from query builder.
	 *
	 * @var array
	 */
	protected $passThrough = [
		'innerJoin',
		'leftJoin',
		'setLimit',
		'having',
	];

	/**
	 * Constructor
	 *
	 * @param   string           $table
	 * @param   ?DatabaseDriver  $db
	 * @param   string           $primaryKey
	 */
	public function __construct(string $table, ?DatabaseDriver $db = null, string $primaryKey = 'id')
	{
		$this->table      = $table;
		$this->db         = $db ?? Factory::getContainer()->get(DatabaseInterface::class);
		$this->primaryKey = $primaryKey;
		$this->query      = $db->getQuery(true);
	}

	/**
	 * Select a single column, or array of columns to return data
	 *
	 * @param   string|array  $columns
	 * @param   string|array  $as
	 *
	 * @return self
	 */
	public function columns($columns, $as = null)
	{
		if ($columns === '*')
		{
			$this->query->select($columns);
		}
		else
		{
			$this->query->select($this->db->quoteName($columns, $as));
		}

		return $this;
	}

	/**
	 * Filter return data base on data from a column
	 *
	 * $entity->where('published', 1)
	 * ->where('price', '>=', 100)
	 *
	 *
	 * @param ...$parameters
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException
	 */
	public function where(...$parameters)
	{
		[$field, $operator, $value] = $this->detachWhereMethodParams($parameters);

		$this->query->where($this->db->quoteName($field) . ' ' . $operator . ' ' . $this->db->quote($value));

		return $this;
	}

	/**
	 * Method to allow filter for records which field equal to certain value
	 *
	 * @param ...$parameters
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException
	 */
	public function equal(...$parameters)
	{
		[$field, $value] = $this->dettachWhereCompareMethodParams($parameters);

		$this->where($field, '=', $value);

		return $this;
	}

	/**
	 * Method to allow filter for records which field not equal to certain value
	 *
	 * @param ...$parameters
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException
	 */
	public function notEqual(...$parameters)
	{
		[$field, $value] = $this->dettachWhereCompareMethodParams($parameters);

		$this->where($field, '!=', $value);

		return $this;
	}

	/**
	 * Method to allow filter for records which field greater than certain value
	 *
	 * @param ...$parameters
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException
	 */
	public function greaterThan(...$parameters)
	{
		[$field, $value] = $this->dettachWhereCompareMethodParams($parameters);

		$this->where($field, '>', $value);

		return $this;
	}

	/**
	 * Method to allow filter for records which field greater than or equal certain value
	 *
	 * @param ...$parameters
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException
	 */
	public function greaterThanOrEqual(...$parameters)
	{
		[$field, $value] = $this->dettachWhereCompareMethodParams($parameters);

		$this->where($field, '>=', $value);

		return $this;
	}

	/**
	 * Method to allow filter for records which field smaller than certain value
	 *
	 * @param ...$parameters
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException
	 */
	public function smallerThan(...$parameters)
	{
		[$field, $value] = $this->dettachWhereCompareMethodParams($parameters);

		$this->where($field, '<', $value);

		return $this;
	}

	/**
	 * Method to allow filter for records which field smaller than or equal certain value
	 *
	 * @param ...$parameters
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException
	 */
	public function smallerThanOrEqual(...$parameters)
	{
		[$field, $value] = $this->dettachWhereCompareMethodParams($parameters);

		$this->where($field, '<=', $value);

		return $this;
	}

	/**
	 * Allow adding conditions to where clause of the query
	 *
	 * @param   string|string[]  $wheres
	 *
	 * @return self
	 */
	public function queryWhere($wheres)
	{
		$wheres = (array) $wheres;

		foreach ($wheres as $where)
		{
			if (!is_string($where))
			{
				throw new InvalidArgumentException('Parameter pass to method must be a string or array of string');
			}

			$this->query->where($where);
		}

		return $this;
	}

	/**
	 * Filter return data which value from the given column is in an array
	 *
	 * @param ...$parameters
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException
	 */
	public function in(...$parameters)
	{
		[$field, $values, $parameterType] = $this->detachWhereInMethodParams($parameters);

		$this->query->whereIn($this->db->quoteName($field), $values, $parameterType);

		return $this;
	}

	/**
	 * Filter return data which value from the given column is not in an array
	 *
	 * @param ...$parameters
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException
	 */
	public function notIn(...$parameters)
	{
		[$field, $values, $parameterType] = $this->detachWhereInMethodParams($parameters);

		$this->query->whereNotIn($this->db->quoteName($field), $values, $parameterType);

		return $this;
	}

	/**
	 * Order return data by a column in ASC directtion
	 *
	 * @param   string  $field
	 *
	 * @return self
	 */
	public function orderBy(string $field)
	{
		$this->query->order($this->db->quoteName($field));

		return $this;
	}

	/**
	 * Order return data by a column in DESC direction
	 *
	 * @param   string  $field
	 *
	 * @return self
	 */
	public function orderByDesc(string $field)
	{
		$this->query->order($this->db->quoteName($field . ' DESC'));

		return $this;
	}

	/**
	 * Return all the records match the conditions
	 *
	 * @return array
	 */
	public function getAll(): array
	{
		if ($this->query->select === null)
		{
			$this->query->select('*');
		}

		$this->query->from($this->db->quoteName($this->table));

		$items = $this->db->setQuery($this->query)
			->loadObjectList();

		$this->resetQuery();

		return $items;
	}

	/**
	 * Return all records match the conditions we need. Proxy for getAll method
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->getAll();
	}

	/**
	 * Return one record match the condition
	 *
	 * @return stdClass|null
	 */
	public function getOne()
	{
		if ($this->query->select === null)
		{
			$this->query->select('*');
		}

		$this->query->setLimit(1);

		$this->query->from($this->db->quoteName($this->table));

		$item = $this->db->setQuery($this->query)
			->loadObject();

		$this->resetQuery();

		return $item;
	}

	/**
	 * Proxy method for getOne
	 *
	 * @return stdClass|null
	 */
	public function one()
	{
		return $this->getOne();
	}

	/**
	 * Return data for a single column which we want to get data from
	 *
	 * @return array
	 */
	public function getColumn()
	{
		if ($this->query->select === null)
		{
			$this->query->select($this->db->quoteName($this->primaryKey));
		}

		$this->query->from($this->db->quoteName($this->table));

		$data = $this->db->setQuery($this->query)
			->loadColumn();

		$this->resetQuery();

		return $data;
	}

	/**
	 * Get only single field from first row of return data
	 *
	 * @return mixed|null
	 */
	public function getResult()
	{
		if ($this->query->select === null)
		{
			$this->query->select($this->db->quoteName($this->primaryKey));
		}

		$this->query->from($this->db->quoteName($this->table));

		$data = $this->db->setQuery($this->query)
			->loadResult();

		$this->resetQuery();

		return $data;
	}

	/**
	 * Count the number rows which satisfy the conditions
	 *
	 * @return  int
	 */
	public function count($field = '*')
	{
		$this->query->select('COUNT(' . $this->db->quoteName($field) . ')');

		return $this->getResult();
	}

	/**
	 * Get max of a field from records which satisfy the conditions
	 *
	 * @param   string  $field
	 *
	 * @return mixed
	 */
	public function max(string $field)
	{
		$this->query->select('MAX(' . $this->db->quoteName($field) . ')');

		return $this->getResult();
	}

	/**
	 * Get min of a field from records which satisfy the conditions
	 *
	 * @param   string  $field
	 *
	 * @return mixed
	 */
	public function min(string $field)
	{
		$this->query->select('MIN(' . $this->db->quoteName($field) . ')');

		return $this->getResult();
	}

	/**
	 * Get avg of a field from records which satisfy the conditions
	 *
	 * @param   string  $field
	 *
	 * @return mixed
	 */
	public function avg(string $field)
	{
		$this->query->select('AVG(' . $this->db->quoteName($field) . ')');

		return $this->getResult();
	}

	/**
	 * Get sum of a field from records which satisfy the conditions
	 *
	 * @param   string  $field
	 *
	 * @return mixed
	 */
	public function sum(string $field)
	{
		$this->query->select('SUM(' . $this->db->quoteName($field) . ')');

		return $this->getResult();
	}

	/**
	 * Find a record by its primary key.
	 *
	 * @param   int    $id       primary key
	 * @param   array  $columns  columns to be selected in query
	 *
	 * @return ?stdClass
	 */
	public function find(int $id, $columns = '*')
	{
		if ($columns !== '*')
		{
			$this->columns($columns);
		}

		$this->where($this->primaryKey, $id);

		return $this->getOne();
	}

	/**
	 * Function to check if row exist.
	 *
	 * @param   mixed  $id  primary key value
	 *
	 * @return  boolean
	 */
	public function exists(int $id): bool
	{
		return $this->find($id) !== null;
	}

	/**
	 * Find last inserted.
	 *
	 * @param   array  $columns  columns to be selected in query
	 *
	 * @return  stdClass|null
	 *
	 */
	public function last($columns = '*')
	{
		if ($columns !== '*')
		{
			$this->columns($columns);
		}

		$this->orderByDesc($this->primaryKey);

		return $this->getOne();
	}

	/**
	 * Execute the query and get the first result.
	 *
	 * @param   array  $columns  columns to be selected
	 *
	 * @return  stdClass|null
	 */
	public function first($columns = '*')
	{
		if ($columns !== '*')
		{
			$this->columns($columns);
		}

		$this->orderBy($this->primaryKey);

		return $this->getOne();
	}

	/**
	 * Function to reset the DatabaseQuery instance
	 * Needed in order to reuse the Query instances
	 *
	 * @return void
	 */
	protected function resetQuery()
	{
		$this->query->clear();
	}

	/**
	 * Dettach the parameters which is used for where method
	 *
	 * @param   array  $parameters
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException
	 */
	private function detachWhereMethodParams(array $parameters): array
	{
		$numberParams = count($parameters);

		if ($numberParams < 2 || $numberParams > 3)
		{
			throw new InvalidArgumentException(sprintf('The method only support 2 or 3 parameters, %d parameters is provided', $numberParams));
		}

		$field = $parameters[0];

		if ($numberParams === 2)
		{
			$operator = '=';
			$value    = $parameters[1];
		}
		else
		{
			$operator = $parameters[1];
			$value    = $parameters[2];
		}

		return [$field, $operator, $value];
	}

	/**
	 * Get parameter use for in and whereIn method
	 *
	 * @param   array  $parameters
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException
	 */
	private function detachWhereInMethodParams(array $parameters): array
	{
		$numberParams = count($parameters);

		if ($numberParams === 1)
		{
			$field         = $this->primaryKey;
			$values        = $parameters[0];
			$parameterType = ParameterType::INTEGER;
		}
		elseif ($numberParams === 2)
		{
			$field         = $parameters[0];
			$values        = $parameters[1];
			$parameterType = ParameterType::INTEGER;
		}
		elseif ($numberParams === 3)
		{
			$field         = $parameters[0];
			$values        = $parameters[1];
			$parameterType = $parameters[2];
		}
		else
		{
			throw new InvalidArgumentException(
				sprintf('Method %s only allows 1, 2 or 3 parameters, number %d parameters provided', __METHOD__, $numberParams)
			);
		}

		return [$field, $values, $parameterType];
	}

	/**
	 * Method to detect the parametes pass to where method such as whereEqual, whereNotEqual
	 *
	 * @param   array  $parameters
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException
	 */
	private function dettachWhereCompareMethodParams(array $parameters): array
	{
		$numberParams = count($parameters);

		if ($numberParams == 1)
		{
			$field = $this->primaryKey;
			$value = $parameters[0];
		}
		elseif ($numberParams == 2)
		{
			[$field, $value] = $parameters;
		}
		else
		{
			throw new InvalidArgumentException(
				sprintf('Method %s only support 1 or 2 parameters, %d parameters provided', __METHOD__, $numberParams)
			);
		}

		return [$field, $value];
	}

	/**
	 * Dynamically handle calls into the query instance.
	 *
	 * @param   string  $method      Method called dynamically
	 * @param   array   $parameters  Parameters to be passed to the dynamic called method
	 *
	 * @return $this
	 */
	public function __call($method, $parameters)
	{
		if (!in_array($method, $this->passThrough))
		{
			throw new BadMethodCallException(sprintf('Method %s does not exist or is not exposed from QueryInterface.', $method));
		}

		$this->query->{$method}(...$parameters);

		return $this;
	}
}