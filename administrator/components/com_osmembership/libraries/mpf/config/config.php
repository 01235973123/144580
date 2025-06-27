<?php

/**
 * @package     MPF
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Store component config data
 *
 * @package        MPF
 * @subpackage     Config
 * @since          2.0
 */
class MPFConfig
{
	/**
	 * The config data container
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * @param   string  $table  The database table which stores config data
	 * @param   string  $keyField
	 * @param   string  $valueField
	 */
	public function __construct($table, $keyField = 'config_key', $valueField = 'config_value')
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select($db->quoteName([$keyField, $valueField]))
			->from($table);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$key   = $row->{$keyField};
			$value = $row->{$valueField};
			// For HTML Editing: $value = preg_replace('/&([a-z0-9]+|#[0-9]{1,6}|#x[0-9a-f]{1,6});/i', '&$1;', $value);
			$this->data[$key] = $value;
		}
	}

	/**
	 * Retrieve data for a config option
	 *
	 * @param   string  $key      The key of the config option
	 *
	 * @param   mixed   $default  Default value if no data has been set for that config option
	 *
	 * @return mixed The config option value
	 */
	public function get($key, $default = null)
	{
		$result = $default;

		if (isset($this->data[$key]))
		{
			$result = $this->data[$key];
		}

		return $result;
	}

	/**
	 * Set data for a config option
	 *
	 * @param   string  $name  The name of config option
	 * @param   mixed   $value
	 *
	 * @return $this
	 */
	public function set($name, $value)
	{
		$this->data[$name] = $value;

		return $this;
	}

	/**
	 * Magic method to get a config option value
	 *
	 * @param   string
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * Set config option value
	 *
	 * @param   string  $name   The user-specified config option
	 *
	 * @param   mixed   $value  The user-specified config option value.
	 *
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	/**
	 * Test existence of a config variable
	 *
	 * @param   string
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}
}
