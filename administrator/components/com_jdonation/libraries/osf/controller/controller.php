<?php
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Session\Session;
/**
 * @package     OSF
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2009 - 2023 Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();

/**
 * Base class for a Joomla Controller
 *
 * Controller (Controllers are where you put all the actual code.) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 *
 * @package		OSF
 * @subpackage	Controller
 * @since		1.0
 */
class OSFController
{

	/**
	 * Array which hold all the controller objects has been created
	 *
	 * @var Array
	 */
	protected static $instances = array();

	/**
	 * The application object.
	 *
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * The input object.
	 *
	 * @var OSFInput
	 */
	protected $input;

	/**
	 * Full name of the component being dispatched com_foobar
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * Name of the component
	 *
	 * @var string
	 */
	protected $component;

	/**
	 * Name of the controller
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * Database table prefix
	 * 
	 * @var string
	 */
	
	protected $tablePrefix = null;

	/**
	 * Class prefix used as prefix for all classes in the component: Controllers, Models, Views, Tables, Helpers
	 *
	 * @var string
	 */
	protected $classPrefix = null;

	/**
	 * Language prefix, used for language strings in component
	 *
	 * @var string
	 */
	protected $languagePrefix;

	/**
	 * The default view which will be rendered in case there is no view specified. Default will be component name
	 *
	 * @var string
	 */
	protected $defaultView;

	/**
	 * Array of class methods
	 *
	 * @var array
	 */
	protected $methods;

	/**
	 * Array which map a task with the method will be called
	 *
	 * @var array
	 */
	protected $taskMap = array();

	/**
	 * Current or most recently performed task.
	 *
	 * @var string
	 */
	protected $task;

	/**
	 * Redirect message.
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Redirect message type.
	 *
	 * @var string
	 */
	protected $messageType;

	/**
	 * URL for redirection.
	 *
	 * @var string
	 */
	protected $redirect;

	/**
	 * Method to get instance of a controller
	 *
	 * @param string $option 
	 * @param JInput $input        	
	 * @param array $config        	
	 *
	 * @return OSFController
	 */
	public static function getInstance($option, OSFInput $input = null, array $config = array())
	{
		//Make sure the component is passed to the method		
		if (empty($option) || !ComponentHelper::isEnabled($option))
		{
			throw new Exception(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}
		$input = $input ? $input : new OSFInput();

		$view = $input->getCmd('view');
		$task = $input->get('task', '');
		$pos = strpos($task, '.');
		if ($pos !== false)
		{
			//In case task has dot in it, task need to have the format controllername.task
			$view = substr($task, 0, $pos);
			$task = substr($task, $pos + 1);
			$input->set('view', $view);
			$input->set('task', $task);
		}
		$component = substr($option, 4);
		//Controller name
		if (isset($config['name']))
		{
			$name = $config['name'];
		}
		else
		{
			$name = OSFInflector::singularize($input->get('view'));
			if (!$name)
			{
				$name = 'controller';
			}
			$config['name'] = $name;
		}
		if (!isset(self::$instances[$component . $name]))
		{
			if (isset($config['class_prefix']))
			{
				$prefix = ucfirst($config['class_prefix']);
			}
			else
			{
				$prefix = ucfirst($component);
				$config['class_prefix'] = $prefix;
			}
			if ($view)
			{
				$class = $prefix . 'Controller' . ucfirst(OSFInflector::singularize($view));
			}
			else
			{
				$class = $prefix . 'Controller';
			}
			if (!class_exists($class))
			{
				if (isset($config['default_controller_class']))
				{
					$class = $config['default_controller_class'];
				}
				else
				{
					$class = 'OSFController';
				}
			}
			$input->set('option', $option);
			self::$instances[$option . $name] = new $class($input, $config);
		}
		return self::$instances[$option . $name];
	}

	/**
	 * Constructor.
	 *
	 * @param array $config An optional associative array of configuration settings.
	 *        	
	 */
	public function __construct(OSFInput $input = null, array $config = array())
	{
		$this->app = Factory::getApplication();
		$this->input = $input;
		$this->option = $input->get('option');
		$this->component = substr($this->option, 4);
		$this->name = $config['name'];
		$this->classPrefix = $config['class_prefix'];
		if (isset($config['language_prefix']))
		{
			$this->languagePrefix = $config['language_prefix'];
		}
		else
		{
			$this->languagePrefix = strtoupper($this->option);
		}
		if (isset($config['default_view']))
		{
			$this->defaultView = $config['default_view'];
		}
		else
		{
			$this->defaultView = $this->component;
		}
		if (isset($config['table_prefix']))
		{
			$this->tablePrefix = $config['table_prefix'];
		}	
		else 
		{
			$this->tablePrefix = '#__' . strtolower($this->component) . '_';
		}
		// Build the default taskMap based on the class methods
		$xMethods = get_class_methods('OSFController');
		$r = new ReflectionClass($this);
		$rMethods = $r->getMethods(ReflectionMethod::IS_PUBLIC);
		foreach ($rMethods as $rMethod)
		{
			$mName = $rMethod->getName();
			if (!in_array($mName, $xMethods) || $mName == 'display')
			{
				$this->taskMap[strtolower($mName)] = $mName;
				$this->methods[] = strtolower($mName);
			}
		}
		$this->task = $input->get('task', 'display');
		if (isset($config['default_task']))
		{
			$this->registerTask('__default', $config['default_task']);
		}
		else
		{
			$this->registerTask('__default', 'display');
		}

        //Add tables path
        Table::addIncludePath(JPATH_ADMINISTRATOR.'/components/'.$this->option.'/table');
	}

	/**
	 * Excute the given task
	 *
	 * @return OSFController return itself to support changing
	 */
	public function execute()
	{
		$task = strtolower($this->task);
		if (isset($this->taskMap[$task]))
		{
			$doTask = $this->taskMap[$task];
		}
		elseif (isset($this->taskMap['__default']))
		{
			$doTask = $this->taskMap['__default'];
		}
		else
		{
			throw new Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TASK_NOT_FOUND', $task), 404);
		}
		$this->$doTask();
		
		return $this;
	}

	/**
	 * Method to display a view
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param boolean $cachable If true, the view output will be cached
	 *        	
	 * @param array $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *        		        	
	 * @return OSFController A OSFController object to support chaining.
	 */
	public function display($cachable = false, array $urlparams = array())
	{
		// Create the view object
		$viewType = $this->input->get('type', 'html');
		$viewName = $this->input->get('view', $this->defaultView);
		$viewLayout = $this->input->get('layout', 'default');
		$view = $this->getView($viewName, $viewType, $viewLayout);
		
		// If view has model, create the model, and assign it to the view
		if ($view->hasModel)
		{
			$model = $this->getModel($viewName);
			$view->setModel($model);
		}
		
		// Display the view		
		if ($cachable && $viewType != 'feed' && Factory::getConfig()->get('caching') >= 1)
		{
			$cache = Factory::getCache($this->option, 'view');
			if (is_array($urlparams))
			{
				if (!empty($this->app->registeredurlparams))
				{
					$registeredurlparams = $this->app->registeredurlparams;
				}
				else
				{
					$registeredurlparams = new stdClass();
				}
				
				foreach ($urlparams as $key => $value)
				{
					// Add your safe url parameters with variable type as value {@see JFilterInput::clean()}.
					$registeredurlparams->$key = $value;
				}
				
				$this->app->registeredurlparams = $registeredurlparams;
			}
			$cache->get($view, 'display');
		}
		else
		{
			$view->display();
		}
		return $this;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param string $name The model name. Optional. Default will be the controller name
	 *        		
	 * @param array $config Configuration array for model. Optional.
	 *        	        	
	 * @return OSFModel The model.
	 *        
	 */
	public function getModel($name = '', array $config = array())
	{
		// If name is not given, the model will has same name with controller
		if (empty($name))
		{
			$name = $this->name;
		}
		
		// Merge config array with default config values
		$config += array('name' => $name, 'option' => $this->option, 'class_prefix' => $this->classPrefix, 'language_prefix' => $this->languagePrefix, 'table_prefix' => $this->tablePrefix);
										
		// Set default model class in case it is not existed
		if (!isset($config['default_model_class']))
		{
			if (OSFInflector::isPlural($name))
			{
				$config['default_model_class'] = 'OSFModelList';
			}
			else
			{
				if ($this->app->isClient('administrator'))
				{
					$config['default_model_class'] = 'OSFModelAdmin';
				}
				else
				{
					$config['default_model_class'] = 'OSFModel';
				}
			}
		}
		
		//Create model and auto populate model states if required
		$model = OSFModel::getInstance($name, $this->classPrefix . 'Model', $config);
		if (!$model->ignoreRequest)
		{
			$this->populateModelStates($model);
		}
		
		return $model;
	}

	/**
	 * Method to get instance of a view
	 *
	 * @param string $name The view name
	 *        	
	 * @param array $config Configuration array for view. Optional.
	 *        	
	 * @return OSFView Reference to the view
	 *        
	 */
	public function getView($name, $type = 'html', $layout = 'default', array $config = array())
	{
		// Merge config array with default config parameters
		$config += array('name' => $name, 'layout' => $layout, 'option' => $this->option, 'class_prefix' => $this->classPrefix, 'language_prefix' => $this->languagePrefix);
		
		// Set the default paths for finding the layout if it is not specified in the $config array		
		if (!isset($config['paths']))
		{
			$paths = array();
			$paths[]         = JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/' . $this->option . '/' . $name;
			if ($this->app->isClient('administrator'))
			{
				$paths[] = JPATH_ADMINISTRATOR . '/components/' . $this->option . '/view/' . $name . '/tmpl';
			}
			else
			{
				$paths[] = JPATH_ROOT . '/components/' . $this->option . '/view/' . $name . '/tmpl';
			}			
			$config['paths'] = $paths;
		}
		
		//Set default view class if class is not existed
		if (!isset($config['default_view_class']))
		{
			if (OSFInflector::isPlural($name))
			{
				$config['default_view_class'] = 'OSFViewList';
			}
			else
			{
				$config['default_view_class'] = 'OSFViewItem';
			}
		}
		if ($this->app->isClient('administrator'))
		{
			$config['is_admin_view'] = true;
		}
		$config['Itemid'] = $this->input->getInt('Itemid');
        if (!isset($config['input']))
        {
            $config['input'] = $this->input;
        }

		return OSFView::getInstance($name, $type, $this->classPrefix . 'View', $config);
	}

	/**
	 * Populate model states from controller input
	 * 
	 * @param OSFModel $model
	 */
	public function populateModelStates($model)
	{
		$data = $this->input->getData();
		if ($model->rememberStates)
		{
			$states = array_keys($model->getState()->getData());
			if (count($states))
			{
				$context = $this->option . '.' . $this->input->get('view', $this->defaultView) . '.';
				foreach ($states as $state)
				{
					$newState = $this->getUserStateFromRequest($context . $state, $state);
					if ($newState != null)
					{
						$data[$state] = $newState;
					}
				}
			}
		}
		$model->set($data);
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @param   string  $key      The key of the user state variable.
	 * @param   string  $request  The name of the variable passed in a request.
	 * @param   string  $default  The default value for the variable if not found. Optional.
	 * @param   string  $type     Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 *
	 * @return  object  The request user state.
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none')
	{
		$currentState = $this->app->getUserState($key, $default);
		$newState = $this->input->get($request, null, $type);
		// Save the new value only if it was set in this request.
		if ($newState !== null)
		{
			$this->app->setUserState($key, $newState);
		}
		else
		{
			$newState = $currentState;
		}
		
		return $newState;
	}

	/**
	 * Sets the internal message that is passed with a redirect
	 *
	 * @param string $text Message to display on redirect.
	 *        	
	 * @param string $type Message type. Optional, defaults to 'message'.
	 *        	
	 * @return string Previous message
	 *        
	 */
	public function setMessage($text, $type = 'message')
	{
		$previous = $this->message;
		$this->message = $text;
		$this->messageType = $type;
		
		return $previous;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param string $url URL to redirect to.
	 *        	
	 * @param string $msg Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 *        	
	 * @param string $type Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 *        	
	 * @return OSFController This object to support chaining.
	 *        
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
		$this->redirect = $url;
		if ($msg !== null)
		{
			// Controller may have set this directly
			$this->message = $msg;
		}
		
		// Ensure the type is not overwritten by a previous call to setMessage.
		if (empty($type))
		{
			if (empty($this->messageType))
			{
				$this->messageType = 'message';
			}
		}
		// If the type is explicitly set, set it.
		else
		{
			$this->messageType = $type;
		}
		
		return $this;
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return boolean False if no redirect exists.
	 *        
	 */
	public function redirect()
	{
		if ($this->redirect)
		{
			$this->app->enqueueMessage($this->message, $this->messageType);
			$this->app->redirect($this->redirect);
		}
		
		return false;
	}

	/**
	 * Get the last task that is being performed or was most recently performed.
	 *
	 * @return string The task that is being performed or was most recently performed.
	 *        
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @param string $task The task name
	 *        	
	 * @param string $method The name of the method in the derived class to perform for this task.
	 *        	
	 * @return OSFController A OSFController object to support chaining.
	 *        
	 */
	public function registerTask($task, $method)
	{
		if (in_array(strtolower($method), $this->methods))
		{
			$this->taskMap[strtolower($task)] = $method;
		}
		
		return $this;
	}

	/**
	 * Get the application object.
	 *
	 * @return JApplicationBase The application object.
	 *        
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Get the input object.
	 *
	 * @return OSFInput The input object.
	 *        
	 */
	public function getInput()
	{
		return $this->input;
	}

    /**
     * Check token to prevent CSRF attack
     */
    protected function csrfProtection()
    {
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
    }
}
