<?php
use Joomla\CMS\Factory;
/**
 * @version        3.5
 * @package        Joomla
 * @subpackage     Payment Form
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
/**
 * Reregister prefix and classes for auto-loading
 */
if (Factory::getApplication()->isClient('administrator'))
{
	$jdConfig = array(
		'default_controller_class' => 'DonationController',
		'default_view' => 'dashboard',
		'class_prefix' => 'Donation',
		'language_prefix' => 'JD',
		'table_prefix' => '#__jd_');
}
else
{
	$jdConfig = array(
		'default_controller_class' => 'DonationController',
		'default_view' => 'donation',
		'class_prefix' => 'Donation',
		'language_prefix' => 'JD',
		'table_prefix' => '#__jd_');
}

