<?php
/**
 * Base Model Class
 *
 * @author        Ossolution Team
 * @package       Joomla
 * @subpackage    EshopRAD.Model
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class EshopRADModel
{

	/**
	 * Hold all instances of this model class
	 *
	 * @var Array
	 */
	protected static $instances;

	/**
	 * The model (base) name
	 *
	 * @var    string
	 */
	protected $name;

	/**
	 * Model state
	 *
	 * @var EshopRADModelState
	 */
	protected $state;

	/**
	 * The database driver.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * The prefix of the database table
	 *
	 * @var string
	 */

	protected $tablePrefix;
	/**
	 * The name of the database table
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 */
	protected $option = null;

	/**
	 * Returns a Model object, always creating it
	 *
	 * @param   string  $name    The name of model to instantiate
	 * @param   string  $prefix  Prefix for the model class name, ComponentnameModel
	 * @param   array   $config  Configuration array for model
	 *
	 * @return  mixed   A model object or false on failure
	 *
	 */
	public static function getInstance($name, $prefix, $config = [])
	{
		if (!isset(self::$instances[$prefix . $name]))
		{
			$name       = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);
			$modelClass = ucfirst($prefix) . ucfirst($name);
			if (!class_exists($modelClass))
			{
				if (isset($config['fallback_class']))
				{
					$modelClass = $config['fallback_class'];
				}
				else
				{
					$modelClass = 'EshopRADModel';
				}
			}

			self::$instances[$prefix . $name] = new $modelClass($config);
		}

		return self::$instances[$prefix . $name];
	}

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options
	 *
	 * @throws  Exception
	 */
	public function __construct($config = [])
	{
		if (isset($config['option']))
		{
			$this->option = $config['option'];
		}
		else
		{
			$className = get_class($this);
			$pos       = strpos($className, 'Model');
			if ($pos !== false)
			{
				$this->option = 'com_' . substr($className, 0, $pos);
			}
			else
			{
				throw new Exception(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_GET_NAME'), 500);
			}
		}

		if (isset($config['name']))
		{
			$this->name = $config['name'];
		}
		else
		{
			$className = get_class($this);
			$pos       = strpos($className, 'Model');
			if ($pos !== false)
			{
				$this->name = substr($className, $pos + 5);
			}
			else
			{
				throw new Exception(Text::_('JLIB_APPLICATION_ERROR_MODEL_GET_NAME'), 500);
			}
		}
		if (isset($config['db']))
		{
			$this->db = $config['db'];
		}
		else
		{
			$this->db = Factory::getDbo();
		}
		// Set the model state
		if (array_key_exists('state', $config))
		{
			$this->state = $config['state'];
		}
		else
		{
			$this->state = new EshopRADModelState();
		}

		if (isset($config['table_prefix']))
		{
			$this->tablePrefix = $config['table_prefix'];
		}
		else
		{
			$component         = substr($this->option, 4);
			$this->tablePrefix = '#__' . strtolower($component) . '_';
		}

		if (isset($config['table']))
		{
			$this->table = $config['table'];
		}
		else
		{
			$this->table = $this->tablePrefix . strtolower(EshopRADInflector::pluralize($this->name));
		}
	}

	/**
	 * Get name of the model
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get EshopRADTable object for the model
	 *
	 * @param   string  $name
	 *
	 * @return EshopRADTable
	 */
	public function getTable($name = '')
	{
		if (!$name)
		{
			$name = EshopRADInflector::singularize($this->name);
		}
		$component = substr($this->option, 4);
		$class     = ucfirst($component) . 'Table' . ucfirst($name);
		if (!class_exists($class))
		{
			$class = 'EshopRADTable';
		}
		$tableName = $this->tablePrefix . strtolower(EshopRADInflector::pluralize($name));

		return new $class($tableName, 'id', $this->db);
	}

	/**
	 * Set the model state properties
	 *
	 * @param   string|arrayThe name of the property, an array
	 * @param   mixed                The value of the property
	 *
	 * @return    EshopRADModel
	 */
	public function set($property, $value = null)
	{
		$changed = false;

		if (is_array($property))
		{
			foreach ($property as $key => $value)
			{
				if (isset($this->state->$key) && $this->state->$key != $value)
				{
					$changed = true;
					break;
				}
			}

			$this->state->setData($property);
		}
		else
		{
			if (isset($this->state->$property) && $this->state->$property != $value)
			{
				$changed = true;
			}

			$this->state->$property = $value;
		}

		if ($changed)
		{
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
			$this->data  = null;
			$this->total = null;
		}

		return $this;
	}

	/**
	 * Get the model state properties
	 *
	 * If no property name is given then the function will return an associative array of all properties.
	 *
	 * @param   string  $property  The name of the property
	 * @param   string  $default   The default value
	 *
	 * @return mixed <string, EshopRADModelState>
	 */
	public function get($property = null, $default = null)
	{
		$result = $default;

		if (is_null($property))
		{
			$result = $this->state;
		}
		else
		{
			if (isset($this->state->$property))
			{
				$result = $this->state->$property;
			}
		}

		return $result;
	}

	/**
	 * Reset all cached data and reset the model state to it's default
	 *
	 * @param   boolean If TRUE use defaults when resetting. Default is TRUE
	 *
	 * @return EshopRADModel
	 */
	public function reset($default = true)
	{
		$this->data  = null;
		$this->total = null;
		$this->state->reset($default);

		return $this;
	}

	/**
	 * Method to get state object
	 *
	 * @return  EshopRADModelState The state object
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Get the dbo
	 *
	 * @return JDatabase
	 */
	public function getDbo()
	{
		return $this->db;
	}

	/**
	 * Get a model state by name
	 *
	 * @param   string  The key name.
	 *
	 * @return  string  The corresponding value.
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Set a model state by name
	 *
	 * @param   string  The key name.
	 * @param   mixed   The value for the key
	 *
	 * @return  void
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Supports a simple form Fluent Interfaces. Allows you to set states by
	 * using the state name as the method name.
	 *
	 * For example : $model->sort('name')->limit(10)->getList();
	 *
	 * @param   string  Method name
	 * @param   array   Array containing all the arguments for the original call
	 *
	 * @return  EshopRADModel
	 */
	public function __call($method, $args)
	{
		if (isset($this->state->$method))
		{
			return $this->set($method, $args[0]);
		}

		return null;
	}
}
