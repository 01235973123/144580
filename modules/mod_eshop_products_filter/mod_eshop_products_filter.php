<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2016 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;

$input = Factory::getApplication()->input;

$option = $input->getCmd('option');
$view   = $input->getCmd('view');

if ($option != 'com_eshop')
{
	return;
}

$showOnProductPage = $params->get('show_on_product_page', 1);

if (!$showOnProductPage && $option == 'com_eshop' && $view == 'product')
{
	return;
}

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';


require_once JPATH_ROOT . '/components/com_eshop/helpers/filter.php';

$keyword = $input->getString('keyword');

if (!empty($keyword))
{
	$keyword = EShopHelper::escape($keyword);
}

$filterData = EShopFilter::getFilterData();

$categories = EShopFilter::getCategories($filterData);

if ($params->get('filter_by_manufacturers', 1))
{
	$manufacturers = EShopFilter::getManufacturers($filterData);
}

if ($params->get('filter_by_attributes', 1))
{
	$attributes = EShopFilter::getAttributes($filterData, true);
}

if ($params->get('filter_by_options', 1))
{
	$options = EShopFilter::getOptions($filterData, true);
}

//Get currency symbol
$currency     = EShopCurrency::getInstance();
$currencyCode = $currency->getCurrencyCode();
$db           = Factory::getDbo();
$query        = $db->getQuery(true);
$query->select('left_symbol, right_symbol')
	->from('#__eshop_currencies')
	->where('currency_code = ' . $db->quote($currencyCode));
$db->setQuery($query);
$row = $db->loadObject();
($row->left_symbol) ? $symbol = $row->left_symbol : $symbol = $row->right_symbol;

if (!empty($filterData['category_id']))
{
	$query->clear()
		->select(' a.id, a.category_parent_id, b.category_name')
		->from('#__eshop_categories AS a')
		->innerJoin('#__eshop_categorydetails AS b ON a.id = b.category_id')
		->where('a.id = ' . $filterData['category_id']);

	if (Multilanguage::isEnabled())
	{
		$query->where('b.language = ' . $db->quote(Factory::getLanguage()->getTag()));
	}

	$db->setQuery($query);
	$category = $db->loadObject();
}

//Get weight unit
$weight     = EShopWeight::getInstance();
$weightId   = EShopHelper::getConfigValue('weight_id');
$weightUnit = $weight->getUnit($weightId);

//Get length unit
$length     = EShopLength::getInstance();
$lengthId   = EShopHelper::getConfigValue('length_id');
$lengthUnit = $length->getUnit($lengthId);

$itemId = $params->get('item_id');

if ($params->get('filter_by_price', 1) && $params->get('max_price') > 0)
{
	$filterByPrice = true;
}
else
{
	$filterByPrice = false;
}

if ($params->get('filter_by_weight', 1) && $params->get('max_weight') > 0)
{
	$filterByWeight = true;
}
else
{
	$filterByWeight = false;
}

if ($params->get('filter_by_weight', 1) && $params->get('max_weight') > 0)
{
	$filterByWeight = true;
}
else
{
	$filterByWeight = false;
}

if ($params->get('filter_by_length', 1) && $params->get('max_length') > 0)
{
	$filterByLength = true;
}
else
{
	$filterByLength = false;
}

if ($params->get('filter_by_width', 1) && $params->get('max_width') > 0)
{
	$filterByWidth = true;
}
else
{
	$filterByWidth = false;
}

if ($params->get('filter_by_height', 1) && $params->get('max_height') > 0)
{
	$filterByHeight = true;
}
else
{
	$filterByHeight = false;
}

if (!$itemId)
{
	$itemId = EShopRoute::getDefaultItemId();
}

//Load JQuery Framework
if (EShopHelper::getConfigValue('load_jquery_framework', 1))
{
	HTMLHelper::_('jquery.framework');
}

$document = Factory::getApplication()->getDocument();
$template = Factory::getApplication()->getTemplate();
$rootUri  = Uri::base(true);

$document->addScript($rootUri . '/components/com_eshop/assets/js/noconflict.js');

//Load CSS of component
EShopHelper::loadComponentCssForModules();

if (is_file((JPATH_ROOT . '/templates/' . $template . '/css/' . $module->module . '.css')))
{
	$document->addStyleSheet($rootUri . '/templates/' . $template . '/css/' . $module->module . '.css');
}
else
{
	$document->addStyleSheet($rootUri . '/modules/' . $module->module . '/assets/css/style.css');
}

$document->addStyleSheet($rootUri . '/modules/mod_eshop_products_filter/assets/css/jquery.nouislider.css');
$document->addScript($rootUri . '/modules/mod_eshop_products_filter/assets/js/jquery.nouislider.min.js');

require ModuleHelper::getLayoutPath('mod_eshop_products_filter');
