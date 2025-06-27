<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;

class OSMembershipHelperBootstrap
{
	/**
	 * Bootstrap Helper instance
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected static $instance;

	/**
	 * Twitter bootstrap version, default 2
	 * @var string
	 */
	protected $bootstrapVersion;

	/**
	 * UI component
	 *
	 * @var MPFUiInterface
	 */
	protected $ui;

	/**
	 * The class mapping to map between the css class and custom class configured by administrator
	 * @var array
	 */
	protected static $classesMap = [];

	/**
	 * Get bootstrap helper object
	 *
	 * @return OSMembershipHelperBootstrap
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			if (Factory::getApplication()->isClient('administrator'))
			{
				self::$instance = new self('5');
			}
			else
			{
				$config = OSMembershipHelper::getConfig();

				self::$instance = new self($config->get('twitter_bootstrap_version', 5));

				if ($config->css_classes_map)
				{
					$classesMap = json_decode($config->css_classes_map, true);

					foreach ($classesMap as $classMap)
					{
						self::$classesMap[trim($classMap['class'])] = trim($classMap['mapped_class']);
					}
				}
			}
		}

		return static::$instance;
	}

	/**
	 * Constructor, initialize the classmaps array
	 *
	 * @param   string  $ui
	 * @param   array   $classMaps
	 *
	 * @throws Exception
	 */
	public function __construct($ui, $classMaps = [])
	{
		if (empty($ui))
		{
			$ui = 5;
		}

		$uiPath = JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries/mpf/ui';

		if (in_array($ui, [2, 3]) && !file_exists($uiPath . '/bootstrap.' . $ui . '.php'))
		{
			$ui = 5;
		}

		switch ($ui)
		{
			case 2:
			case 3:
			case 4:
			case 5:
				$uiClass = 'MPFUiBootstrap' . $ui;
				break;
			default:
				$uiClass = 'MPFUi' . ucfirst($ui);
				break;
		}

		$this->bootstrapVersion = $ui;

		if (class_exists($uiClass . 'override'))
		{
			$uiClass = $uiClass . 'override';
		}

		if (!class_exists($uiClass))
		{
			throw new Exception(sprintf('UI class %s not found', $uiClass));
		}

		$this->ui = new $uiClass($classMaps);
	}

	/**
	 * Get the mapping of a given class
	 *
	 * @param   string  $class  The input class
	 * @param   string  $mappedClass
	 *
	 * @return $this
	 */
	public function addClassMapping($class, $mappedClass)
	{
		$this->ui->addClassMapping($class, $mappedClass);

		return $this;
	}

	/**
	 * Get the mapping of a given class
	 *
	 * @param   string  $class  The input class
	 *
	 * @return string The mapped class
	 */
	public function getClassMapping($class)
	{
		$class = $this->ui->getClassMapping($class);

		// Early return in case there is no custom css class mapping
		if (empty(self::$classesMap))
		{
			return $class;
		}

		// Check to see if there is direct class mapping
		if (isset(self::$classesMap[$class]))
		{
			return self::$classesMap[$class];
		}

		// Early return if this is not multiple css classes separated by comma
		if (!str_contains($class, ' '))
		{
			return $class;
		}

		// If there is multiple class, find mapping of each class
		$classes       = explode(' ', $class);
		$mappedClasses = [];

		foreach ($classes as $cssClass)
		{
			if (isset(self::$classesMap[$cssClass]))
			{
				// Special case for btn class in class group such as btn btn-primary
				$mappedClasses[] = self::$classesMap[$cssClass];
			}
			else
			{
				$mappedClasses[] = $cssClass;
			}
		}

		return implode(' ', $mappedClasses);
	}

	/**
	 * Get twitter bootstrap version
	 *
	 * @return int|string
	 */
	public function getBootstrapVersion()
	{
		return $this->bootstrapVersion;
	}

	/**
	 * Method to get input with prepend add-on
	 *
	 * @param   string  $input
	 * @param   string  $addOn
	 *
	 * @return string
	 */
	public function getPrependAddon($input, $addOn)
	{
		return $this->ui->getPrependAddon($input, $addOn);
	}

	/**
	 * Method to get input with append add-on
	 *
	 * @param   string  $input
	 * @param   string  $addOn
	 *
	 * @return string
	 */
	public function getAppendAddon($input, $addOn)
	{
		return $this->ui->getAppendAddon($input, $addOn);
	}

	/**
	 * Get framework own css class
	 *
	 * @param   string  $class
	 * @param   int     $behavior
	 *
	 * @return string
	 */
	public function getFrameworkClass($class, $behavior = 0)
	{
		return $this->ui->getFrameworkClass($class, $behavior);
	}

	/**
	 * Return UI Object
	 *
	 * @return MPFUiInterface
	 */
	public function getUi()
	{
		return $this->ui;
	}
}
