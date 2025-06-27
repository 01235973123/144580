<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2011 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class eshop_shipping
{

	/**
	 *
	 * Initial function
	 */
	public function __construct()
	{
		//Do nothing
	}

	/**
	 * Name of shipping plugin
	 *
	 * @var string
	 */
	var $_name = null;

	/**
	 * Method to get shipping name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Method to set shipping name
	 *
	 * @param   string  $value
	 */
	public function setName($value)
	{
		$this->_name = $value;
	}
}