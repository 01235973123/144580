<?php

/**
 * @package     OSF
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2009 - 2023 Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();
define('OS_INPUT_ALLOWRAW', 2);
define('OS_INPUT_ALLOWHTML', 4);

use Joomla\CMS\Input\Input;
use Joomla\CMS\Filter\InputFilter;
/**
 * Extends JInput class to allow getting raw data from Input object. This can be removed when we don't provide support for Joomla 2.5.x
 * 
 * @package     OSF
 * @subpackage	Input
 * @since		1.0
 */
class OSFInput extends Input
{

	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is $_REQUEST)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *	
	 */
	public function __construct($source = null, array $options = [])
	{
		if ($source instanceof Input)
		{
			$reflection = new ReflectionClass($source);
			$property   = $reflection->getProperty('data');
			$property->setAccessible(true);
			$source = $property->getValue($source);
		}
		if (!isset($options['filter']))
		{
			//Set default filter so that getHtml can be returned properly
			//$options['filter'] = JFilterInput::getInstance(null, null, 1, 1);
			if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
			{
				//Set default filter so that getHtml can be returned properly
				$options['filter'] = InputFilter::getInstance([], [], 1, 1);
			}
			else
			{
				$options['filter'] = InputFilter::getInstance(null, null, 1, 1);
			}
		}
		
		parent::__construct($source, $options);
		
		//if (get_magic_quotes_gpc())
		//{
			$this->data = self::stripSlashesRecursive($this->data);
		//}
	}

	/**
	 * Get the row data from input
	 * 
	 * @return array
	 */
	public function getData($mask = OS_INPUT_ALLOWHTML)
	{
		if ($mask & 2)
		{
			return $this->data;
		}

		return $this->filter->clean($this->data, '');
	}

	/**
	 * Check to see if a variable is avaialble in the input or not
	 * 
	 * @param string $name the variable name
	 * @return boolean
	 */
	public function has($name)
	{
		if (isset($this->data[$name]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Helper method to Un-quotes a quoted string
	 * @param string $value
	 * @return Ambigous <multitype:, string>
	 */
	protected static function stripSlashesRecursive($value)
	{
		$value = is_array($value) ? array_map(array('OSFInput', 'stripSlashesRecursive'), $value) : stripslashes($value);
		return $value;
	}
}
