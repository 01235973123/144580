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
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

//Load com_eshop language file
$language = Factory::getLanguage();
$template = Factory::getApplication()->getTemplate();

$tag = $language->getTag();

if (!$tag)
{
	$tag = 'en-GB';
}

$language->load('com_eshop', JPATH_ROOT, $tag);

//Load css module eshop quote
$document = Factory::getApplication()->getDocument();
$rootUri = Uri::root(true);

//Load CSS of component
EShopHelper::loadComponentCssForModules();

//Add extra css for selected type
if (is_file(JPATH_SITE . '/templates/' . $template . '/css/' . $module->module . '.css'))
{
	$document->addStyleSheet($rootUri . '/templates/' . $template . '/css/' . $module->module . '.css');
}
else
{
	$document->addStyleSheet($rootUri . '/modules/' . $module->module . '/asset/css/style.css');
}

//Load JQuery Framework
if (EShopHelper::getConfigValue('load_jquery_framework', 1))
{
	HTMLHelper::_('jquery.framework');
}

//Load javascript
$document->addScript($rootUri . '/components/com_eshop/assets/js/noconflict.js');

//Get quote data
$quote         = new EShopQuote();
$items         = $quote->getQuoteData();
$countProducts = $quote->countProducts();

$input = Factory::getApplication()->input;
$view  = $input->getString('view');

require ModuleHelper::getLayoutPath('mod_eshop_quote', $params->get('layout', 'default'));