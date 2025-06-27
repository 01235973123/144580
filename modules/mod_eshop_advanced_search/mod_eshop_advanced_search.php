<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

$categories      = modEShopAdvancedSearchHelper::getCategories($params->get('child_categories_level', 9999));
$manufacturers   = modEShopAdvancedSearchHelper::getManufacturers();
$attributeGroups = modEShopAdvancedSearchHelper::getAttributeGroups();
$options         = modEShopAdvancedSearchHelper::getOptions();
$template        = Factory::getApplication()->getTemplate();

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

//Get weight unit
$weight     = EShopWeight::getInstance();
$weightId   = EShopHelper::getConfigValue('weight_id');
$weightUnit = $weight->getUnit($weightId);

//Get length unit
$length     = EShopLength::getInstance();
$lengthId   = EShopHelper::getConfigValue('length_id');
$lengthUnit = $length->getUnit($lengthId);

$input = Factory::getApplication()->input;

//Get submitted values
$minPrice       = str_replace($symbol, '', htmlspecialchars($input->get('min_price'), ENT_COMPAT, 'UTF-8'));
$maxPrice       = str_replace($symbol, '', htmlspecialchars($input->get('max_price'), ENT_COMPAT, 'UTF-8'));
$minWeight      = str_replace($weightUnit, '', htmlspecialchars($input->get('min_weight'), ENT_COMPAT, 'UTF-8'));
$maxWeight      = str_replace($weightUnit, '', htmlspecialchars($input->get('max_weight'), ENT_COMPAT, 'UTF-8'));
$minLength      = str_replace($lengthUnit, '', htmlspecialchars($input->get('min_length'), ENT_COMPAT, 'UTF-8'));
$maxLength      = str_replace($lengthUnit, '', htmlspecialchars($input->get('max_length'), ENT_COMPAT, 'UTF-8'));
$minWidth       = str_replace($lengthUnit, '', htmlspecialchars($input->get('min_width'), ENT_COMPAT, 'UTF-8'));
$maxWidth       = str_replace($lengthUnit, '', htmlspecialchars($input->get('max_width'), ENT_COMPAT, 'UTF-8'));
$minHeight      = str_replace($lengthUnit, '', htmlspecialchars($input->get('min_height'), ENT_COMPAT, 'UTF-8'));
$maxHeight      = str_replace($lengthUnit, '', htmlspecialchars($input->get('max_height'), ENT_COMPAT, 'UTF-8'));
$productInStock = $input->get('product_in_stock');
$categoryIds    = $input->get('category_ids');

if (!$categoryIds)
{
	$categoryIds = [];
}
else
{
	$categoryIds = explode(',', $categoryIds);
}

$manufacturerIds = $input->get('manufacturer_ids');

if (!$manufacturerIds)
{
	$manufacturerIds = [];
}
else
{
	$manufacturerIds = explode(',', $manufacturerIds);
}

$attributeIds = $input->get('attribute_ids');

if (!$attributeIds)
{
	$attributeIds = [];
}
else
{
	$attributeIds = explode(',', $attributeIds);
}

$optionValueIds = $input->get('optionvalue_ids');

if (!$optionValueIds)
{
	$optionValueIds = [];
}
else
{
	$optionValueIds = explode(',', $optionValueIds);
}

$keyword = $input->getString('keyword');

if (!empty($keyword))
{
	$keyword = htmlspecialchars($keyword, ENT_COMPAT, 'UTF-8');
}

$itemId = $params->get('item_id');

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
$rootUri  = Uri::root(true);
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

$document->addStyleSheet(EShopHelper::getSiteUrl() . 'modules/mod_eshop_advanced_search/assets/css/jquery.nouislider.css');
$document->addScript(EShopHelper::getSiteUrl() . 'modules/mod_eshop_advanced_search/assets/js/jquery.nouislider.min.js');

require(ModuleHelper::getLayoutPath('mod_eshop_advanced_search'));
