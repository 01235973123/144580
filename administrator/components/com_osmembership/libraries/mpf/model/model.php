<?php
/**
 * @package     MPF
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * Base class for a Joomla Model
 *
 * @package       MPF
 * @subpackage    Model
 * @since         2.0
 */
class MPFModel
{
	/**
	 * The model name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Model state
	 *
	 * @var MPFModelState
	 */
	protected $state;

	/**
	 * The database driver.
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	/**
	 * Model configuration data
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * The name of the database table
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The associated menu item parameters
	 *
	 * @var \Joomla\Registry\Registry
	 */
	protected $params;

	/**
	 * Ignore request or not. If set to Yes, model states won't be set when it is created
	 *
	 * @var bool
	 */
	public $ignoreRequest = false;

	/**
	 * Remember model states value in session
	 *
	 * @var bool
	 */
	public $rememberStates = false;

	/**
	 * Store list of states uses Choices-JS  script
	 *
	 * @var array
	 */
	protected $choicesStates = [];

	/**
	 * @param   string  $name    The name of model to instantiate
	 *
	 * @param   string  $prefix  Prefix for the model class name, ComponentnameModel
	 *
	 * @param   array   $config  Configuration array for model
	 *
	 * @return MPFModel A model object
	 */
	public static function getInstance($name, $prefix, $config = [])
	{
		$name          = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);
		$class         = ucfirst($prefix) . ucfirst($name);
		$overrideClass = ucfirst($prefix) . 'Override' . ucfirst($name);

		if (class_exists($overrideClass))
		{
			$class = $overrideClass;
		}

		if (!class_exists($class))
		{
			if (isset($config['default_model_class']))
			{
				$class = $config['default_model_class'];
			}
			else
			{
				$class = 'MPFModel';
			}
		}

		return new $class($config);
	}

	/**
	 * Method to get temp instance of model (do not populate or remember model states)
	 *
	 * @param   string  $name    The name of model to instantiate
	 *
	 * @param   string  $prefix  Prefix for the model class name, ComponentnameModel
	 *
	 * @param   array   $config  Configuration array for model
	 *
	 * @return MPFModel A model object
	 */
	public static function getTempInstance($name, $prefix, $config = [])
	{
		$config['ignore_request']  = true;
		$config['remember_states'] = false;

		return static::getInstance($name, $prefix, $config);
	}

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options
	 *
	 * @throws Exception
	 */
	public function __construct($config = [])
	{
		// Set the model name
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

		// Set the model state
		$this->state = $config['state'] ?? new MPFModelState();
		$this->db    = $config['db'] ?? Factory::getContainer()->get('db');

		// Build default model configuration if it is not set
		if (empty($config['option']))
		{
			$className = get_class($this);
			$pos       = strpos($className, 'Model');

			if ($pos !== false)
			{
				$config['option'] = 'com_' . substr($className, 0, $pos);
			}
			else
			{
				throw new Exception(Text::_('Could not detect the component for model'), 500);
			}
		}

		if (empty($config['table_prefix']))
		{
			$component              = substr($config['option'], 4);
			$config['table_prefix'] = '#__' . strtolower($component) . '_';
		}

		if (empty($config['class_prefix']))
		{
			$component              = substr($config['option'], 4);
			$config['class_prefix'] = ucfirst($component);
		}

		if (empty($config['language_prefix']))
		{
			$component                 = substr($config['option'], 4);
			$config['language_prefix'] = strtoupper($component);
		}

		$this->table = $config['table'] ?? $config['table_prefix'] . strtolower(MPFInflector::pluralize($this->name));

		if (isset($config['ignore_request']))
		{
			$this->ignoreRequest = $config['ignore_request'];
		}

		if (isset($config['remember_states']))
		{
			$this->rememberStates = $config['remember_states'];
		}

		if (isset($config['params']) && $config['params'] instanceof Registry)
		{
			$this->params = $config['params'];
		}
		else
		{
			$this->params = new Registry();
		}

		$this->config = $config;

		//Add include path to find table class
		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $config['option'] . '/table');
	}

	/**
	 * Get JTable object for the model
	 *
	 * @param   string  $name
	 *
	 * @return \Joomla\CMS\Table\Table
	 */
	public function getTable($name = '')
	{
		if (!$name)
		{
			$name = MPFInflector::singularize($this->name);
		}

		$className = $this->config['class_prefix'] . 'Table' . ucfirst($name);

		return new  $className($this->getDbo());
	}

	/**
	 * Set the model state properties
	 *
	 * @param   string|array The   name of the property, an array
	 *
	 * @param   mixed The value of the property
	 *
	 * @return MPFModel
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
	 *
	 * @param   string  $default   The default value
	 *
	 * @return mixed <string, MPFModelState>
	 */
	public function get($property = null, $default = null)
	{
		$result = $default;

		if (is_null($property))
		{
			$result = $this->state;
		}
		elseif (isset($this->state->$property))
		{
			$result = $this->state->$property;
		}

		return $result;
	}

	/**
	 * Reset all cached data and reset the model state to it's default
	 *
	 * @param   bool If TRUE use defaults when resetting. Default is TRUE
	 *
	 * @return MPFModel
	 */
	public function reset($default = true)
	{
		$this->data  = null;
		$this->total = null;
		$this->state->reset($default);
		$this->query = $this->db->getQuery(true);

		return $this;
	}

	/**
	 * Method to get state object
	 *
	 * @return MPFModelState The state object
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Get the dbo
	 *
	 * @return \Joomla\Database\DatabaseDriver
	 */
	public function getDbo()
	{
		return $this->db;
	}

	/**
	 * Set menu parameters for model
	 *
	 * @param   \Joomla\Registry\Registry  $params
	 */
	public function setParams($params)
	{
		if ($params instanceof Registry)
		{
			$this->params = $params;
		}
	}

	/**
	 * Get menu item param associated with with model
	 *
	 * @return Registry
	 */
	public function getParams()
	{
		return $this->params ?: new Registry();
	}

	/**
	 * Get listt of states use Choices-JS script
	 *
	 * @return array
	 */
	public function getChoicesJSStates()
	{
		return $this->choicesStates;
	}

	/**
	 * Clean the cache
	 *
	 * @param   string  $group     The cache group
	 * @param   int     $clientId  The ID of the client
	 *
	 * @return  void
	 */
	protected function cleanCache($group = null, $clientId = 0)
	{
		$options = [
			'defaultgroup' => $group ?: $this->config['option'],
			'cachebase'    => ($clientId) ? JPATH_ADMINISTRATOR . '/cache' : Factory::getApplication()->get('cache_path', JPATH_SITE . '/cache'),
		];

		$cache = Cache::getInstance('callback', $options);
		$cache->clean();

		// Trigger the onContentCleanCache event.
		if (!empty($this->eventCleanCache))
		{
			Factory::getApplication()->triggerEvent($this->event_clean_cache, $options);
		}
	}

	/**
	 * Get a model state by name
	 *
	 * @param   string  $key  The key name.
	 *
	 * @return string The corresponding value.
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Set a model state by name
	 *
	 * @param   string  $key    The key name.
	 *
	 * @param   mixed   $value  The value for the key
	 *
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Supports a simple form Fluent Interfaces.
	 * Allows you to set states by
	 * using the state name as the method name.
	 *
	 * For example : $model->filter_order('name')->filter_order_Dir('DESC')->limit(10)->getData();
	 *
	 * @param   string  $method  Method name
	 *
	 * @param   array   $args    Array containing all the arguments for the original call
	 *
	 * @return MPFModel
	 */
	public function __call($method, $args)
	{
		if (isset($this->state->$method))
		{
			return $this->set($method, $args[0]);
		}

		return;
	}
}
