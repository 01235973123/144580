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

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

//Load JQuery Framework
if (EShopHelper::getConfigValue('load_jquery_framework', 1))
{
	HTMLHelper::_('jquery.framework');
}

$document = Factory::getApplication()->getDocument();
$template = Factory::getApplication()->getTemplate();

//Load CSS of component
EShopHelper::loadComponentCssForModules();

if (is_file(JPATH_SITE . '/templates/' . $template . '/css/' . $module->module . '.css'))
{
	$document->addStyleSheet(Uri::base() . 'templates/' . $template . '/css/' . $module->module . '.css');
}
else
{
	$document->addStyleSheet(Uri::base() . 'modules/' . $module->module . '/assets/css/style.css');
}
require ModuleHelper::getLayoutPath('mod_eshop_search', $params->get('layout', 'default'));