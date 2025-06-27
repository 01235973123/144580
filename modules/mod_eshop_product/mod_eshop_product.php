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
use Joomla\Filesystem\File;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

// Get parameters
$headerText        = $params->get('header_text', '');
$footerText        = $params->get('footer_text', '');
$productsPerRow    = $params->get('products_per_row', 4);
$showShortDesc     = $params->get('show_short_desc', 0);
$shortDescLimited  = $params->get('short_desc_limited', 100);
$showPrice         = $params->get('show_price', 1);
$showAddcart       = $params->get('show_addtocart', 1);
$showAddquote      = $params->get('show_addtoquote', 1);
$showAddToWishlist = $params->get('show_add_to_wishlist', 1);
$showAddToCompare  = $params->get('show_add_to_compare', 1);
$showRating        = $params->get('show_rating', 1);
$layout            = $params->get('layout', 'default');
$thumbnailWidth    = $params->get('image_width', 100);
$thumbnailHeight   = $params->get('image_height', 100);

// Get products to display
$items = modEShopProductHelper::getItems($params);

$currency = new EShopCurrency();
$tax      = new EShopTax(EShopHelper::getConfig());
$document = Factory::getApplication()->getDocument();
$template = Factory::getApplication()->getTemplate();
$theme    = EShopHelper::getConfigValue('theme');
$rootUri  = Uri::root(true);

// Load Bootstrap CSS
if (EShopHelper::getConfigValue('load_bootstrap_css', 1) && in_array(EShopHelper::getConfigValue('twitter_bootstrap_version', 2), [2, 5]))
{
	EShopHelper::loadBootstrapCss();
}

// Load slick css
$document->addStyleSheet($rootUri . '/components/com_eshop/assets/slick/slick.css');

// Load CSS of corresponding theme
if (is_file((JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/css/style.css')))
{
	$document->addStyleSheet($rootUri . '/components/com_eshop/themes/' . $theme . '/css/style.css');
}
else
{
	$document->addStyleSheet($rootUri . '/components/com_eshop/themes/default/css/style.css');
}

// Load custom CSS file of component
if (is_file((JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/css/custom.css')))
{
	$document->addStyleSheet($rootUri . '/components/com_eshop/themes/' . $theme . '/css/custom.css');
}
else
{
	$document->addStyleSheet($rootUri . '/components/com_eshop/themes/default/css/custom.css');
}

// Load css file of module
if (is_file((JPATH_ROOT . '/templates/' . $template . '/css/' . $module->module . '.css')))
{
	$document->addStyleSheet($rootUri . '/templates/' . $template . '/css/' . $module->module . '.css');
}
else
{
	$document->addStyleSheet($rootUri . '/modules/' . $module->module . '/css/style.css');
}

//Load JQuery Framework
if (EShopHelper::getConfigValue('load_jquery_framework', 1))
{
	HTMLHelper::_('jquery.framework');
}

//Load javascript and css
$document->addScript($rootUri . '/components/com_eshop/assets/js/noconflict.js');
$document->addScript($rootUri . '/components/com_eshop/assets/js/eshop.js');
$document->addScript($rootUri . '/components/com_eshop/assets/slick/slick.js');
$document->addScript($rootUri . '/components/com_eshop/assets/colorbox/jquery.colorbox.js');
$document->addStyleSheet($rootUri . '/components/com_eshop/assets/colorbox/colorbox.css');
$document->addStyleSheet($rootUri . '/components/com_eshop/assets/css/labels.css');

require ModuleHelper::getLayoutPath('mod_eshop_product', $params->get('layout', 'default'));