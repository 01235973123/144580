<?php

/**
* @package        Joomla
* @subpackage     Joom Donation
* @author         Tuan Pham Ngoc
* @copyright      Copyright (C) 2009 - 2023 Ossolution Team
* @license        GNU/GPL, see LICENSE.php
*/

use Joomla\CMS\Factory;


class DonationHelperBootstrap
{
	/**
	 * Bootstrap Helper instance
	 *
	 * @var EventbookingHelperBootstrap
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
	 * @var JDUiInterface
	 */
	protected $ui;

	/**
	 * The class mapping to map between twitter bootstrap 2 and twitter bootstrap 3
	 * @var string
	 */
	protected static $classMaps;


	public static function getInstance()
	{
		if (self::$instance === null)
		{
			if (Factory::getApplication()->isClient('administrator'))
			{
				if (DonationHelper::isJoomla4())
				{
					self::$instance = new self('4');
				}
				else
				{
					self::$instance = new self('2');
				}
			}
			else
			{
				$config         = DonationHelper::getConfig();
				self::$instance = new self($config->twitter_bootstrap_version);
			}
		}

		return static::$instance;
	}
	/**
	 * Constructor, initialize the classmaps array
	 *
	 * @param string $ui
	 * @param array  $classMaps
	 *
	 * @throws Exception
	 */
	public function __construct($ui, $classMaps = [])
	{
		if (empty($ui))
		{
			$ui = 2;
		}
		if(!DonationHelper::isJoomla4() && Factory::getApplication()->isClient('administrator'))
		{
			$ui = 2;
		}
		elseif(DonationHelper::isJoomla4() && Factory::getApplication()->isClient('administrator'))
		{
			$ui = 4;
		}
		switch ($ui)
		{
			case 2:
			case 3:
			case 4:
			case 5:
				$uiClass = 'OSFUiBootstrap' . $ui;
				break;
			case 6:
				$uiClass = 'OSFUiUikit';
				break;
			default:
				$uiClass = 'OSFUI' . ucfirst($ui);
				break;
		}
		$this->bootstrapVersion = $ui;
		if (!class_exists($uiClass))
		{
			
			throw new Exception(sprintf('UI class %s not found', $uiClass));
			
		}
		$this->ui = new $uiClass($classMaps);
	}

	/**
	 * Get the mapping of a given class
	 *
	 * @param string $class The input class
	 *
	 * @return string The mapped class
	 */
	public function getClassMapping($class)
	{
		return $this->ui->getClassMapping($class);
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
	 * @param string $input
	 * @param string $addOn
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
	 * @param string $input
	 * @param string $addOn
	 *
	 * @return string
	 */
	public function getAppendAddon($input, $addOn)
	{
		return $this->ui->getAppendAddon($input, $addOn);
	}
}
