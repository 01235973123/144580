<?php
/**
 * @package     Joom Donation
 * @subpackage  UI
 *
 * @copyright   Copyright (C) 2009 - 2018 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

abstract class OSFUiAbstract implements OSFUiInterface
{
	/**
	 * Css class map array
	 *
	 * @var array
	 */
	protected $classMaps;

	/**
	 * Method to add a new class to class mapping
	 *
	 * @param $class
	 * @param $mappedClass
	 *
	 * @return void
	 */
	public function addClassMapping($class, $mappedClass)
	{
		$class       = trim($class);
		$mappedClass = trim($mappedClass);

		$this->classMaps[$class] = $mappedClass;
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
		$class = trim($class);

		return isset($this->classMaps[$class]) ? $this->classMaps[$class] : null;
	}
}