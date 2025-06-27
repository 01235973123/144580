<?php
/**
 * @package     OSF
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2009 - 2023 Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();

/**
 * Joomla CMS Base View Class
 *
 * @package		OSF
 * @subpackage	View
 * @since       1.0
 */
abstract class OSFView
{

	/**
	 * Name of the view
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The model object.
	 *
	 * @var OSFModel
	 */
	protected $model;

	/**
	 * Full name of the component
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * Determine the view has a model associated with it or not.
	 * If set to No, no model will be created and assigned to the view method when the view is being displayed
	 * 
	 * @var boolean
	 */
	public $hasModel = true;

	/**
	 * Prefix of class related to the view
	 * 
	 * @var string
	 */
	protected $classPrefix = null;

	/**
	 * Returns a View object, always creating it
	 *
	 * @param string $name The name of view to instantiate     	
	 * @param string $type The type of view to instantiate
	 * @param string $prefix Prefix for the view class name, ComponentnameView
	 * @param array $config Configuration array for view
	 *        	       	
	 * @return OSFView A view object
	 *        
	 */
	public static function getInstance($name, $type, $prefix, array $config = array())
	{
		$class = ucfirst($prefix) . ucfirst($name) . ucfirst($type);	
		if (!class_exists($class))
		{
			if (isset($config['default_view_class']))
			{
				$class = $config['default_view_class'];
			}
			else
			{
				$class = 'OSFView' . ucfirst($type);
			}
		}
		return new $class($config);
	}

	/**
	 * Constructor
	 *
	 * @param array $config A named configuration array for object construction.
	 *        	
	 */
	public function __construct(array $config = array())
	{
		if (isset($config['name']))
		{
			$this->name = $config['name'];
		}
		else
		{
			$className = get_class($this);
			$viewPos = strpos('View', $className);
			if ($viewPos !== false)
			{
				$this->name = substr($className, $viewPos + 4);
			}
		}
		if (isset($config['option']))
		{
			$this->option = $config['option'];
		}
		else
		{
			$className = get_class($this);
			$viewPos = strpos('View', $className);
			if ($viewPos !== false)
			{
				$this->option = substr($className, 0, $viewPos);
			}
		}
		if (isset($config['has_model']))
		{
			$this->hasModel = $config['has_model'];
		}
		
		if (isset($config['class_prefix']))
		{
			$this->classPrefix = $config['class_prefix'];
		}
		else
		{
			$component = substr($this->option, 4);
			$this->classPrefix = ucfirst($component);
		}
	}

	/**
	 * Get name of the current view
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the model object
	 *
	 * @param OSFModel $model        	
	 */
	public function setModel(OSFModel $model)
	{
		$this->model = $model;
	}

	/**
	 * Get the model object
	 *
	 * @return OSFModel
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Method to escape output.
	 *
	 * @param string $output The output to escape.
	 *        	
	 * @return string The escaped output.
	 *        
	 */
	public function escape($output)
	{
		return $output;
	}
}
