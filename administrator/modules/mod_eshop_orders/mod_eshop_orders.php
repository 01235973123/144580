<?php
/**
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

/* @var Joomla\Registry\Registry $params */
$count = (int) $params->get('count', 10);

$db		= Factory::getDbo();
$query	= $db->getQuery(true);

$query->clear()
	->select('*')
	->from('#__eshop_orders')
	->order('id DESC');
$db->setQuery($query, 0, $count);
$rows = $db->loadObjectList();

if (count($rows))
{
	$currency	= EShopCurrency::getInstance();
	$nullDate	= $db->getNullDate();
	Factory::getApplication()->getLanguage()->load('com_eshop', JPATH_ADMINISTRATOR);

	require ModuleHelper::getLayoutPath('mod_eshop_orders');
}