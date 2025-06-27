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
use Joomla\CMS\Uri\Uri;

// Include the helper functions only once
require_once __DIR__ . '/helper.php';

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

$categories = modEShopCategoryHelper::getCategories($params);
$document   = Factory::getApplication()->getDocument();
$template   = Factory::getApplication()->getTemplate();
$rootUri	= Uri::root(true);

//Load CSS of component
EShopHelper::loadComponentCssForModules();

if (is_file(JPATH_SITE . '/templates/' . $template . '/css/' . $module->module . '.css'))
{
	$document->addStyleSheet($rootUri . '/templates/' . $template . '/css/' . $module->module . '.css');
}
else
{
	$document->addStyleSheet($rootUri . '/modules/' . $module->module . '/css/style.css');
}

$showChildren       = $params->get('show_children');
$showNumberProducts = $params->get('show_number_products') && EShopHelper::getConfigValue('product_count');
$categoriesPerRow   = $params->get('categories_per_row', 3);
$bootstrapHelper    = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

$input = Factory::getApplication()->input;
$view  = $input->getString('view');

if ($view == 'category')
{
	$categoryId = $input->getInt('id');
}
else
{
	$categoryId = 0;
}

if ($categoryId == 0)
{
	$parentCategoryId = 0;
	$childCategoryId  = 0;
}
else
{
	$parentCategoryId = modEShopCategoryHelper::getParentCategoryId($categoryId);

	if ($parentCategoryId == $categoryId)
	{
		$childCategoryId = 0;
	}
	else
	{
		$childCategoryId = $categoryId;
	}
}

require ModuleHelper::getLayoutPath('mod_eshop_category', $params->get('layout', 'default'));
