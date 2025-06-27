<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

//Fix PayPal IPN sending to wrong URL
if (!empty($_POST['txn_type']) && empty($_REQUEST['task']) && empty($_REQUEST['view']))
{
	$_REQUEST['task']           = 'checkout.verifyPayment';
	$_REQUEST['payment_method'] = 'os_paypal';
}

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

$input = Factory::getApplication()->input;

$command = $input->getCmd('task', 'display');

// Check for a controller.task command.
if (strpos($command, '.') !== false)
{
	[$controller, $task] = explode('.', $command);
	$path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';

	if (file_exists($path))
	{
		require_once $path;

		$className  = 'EShopController' . ucfirst($controller);
		$controller = new $className();
	}
	else
	{
		//Fallback to default controller
		$controller = new EShopAdminController(['entity_name' => $controller, 'name' => 'EShop']);
	}

	$input->set('task', $task);
}
else
{
	require_once JPATH_COMPONENT . '/controller.php';

	$controller = new EShopController();
}

//Shop offline
if (EShopHelper::getConfigValue('shop_offline', 0))
{
	$input->set('task', '');
	$input->set('view', 'offline');
}

//Load JQuery Framework
if (EShopHelper::getConfigValue('load_jquery_framework', 1))
{
	HTMLHelper::_('jquery.framework');
}

// Load Bootstrap CSS
if (EShopHelper::getConfigValue('load_bootstrap_css', 1) && in_array(EShopHelper::getConfigValue('twitter_bootstrap_version', 2), [2, 5]))
{
	EShopHelper::loadBootstrapCss();
}

$document = Factory::getApplication()->getDocument();
$rootUri  = Uri::root(true);

$document->addScript($rootUri . '/media/com_eshop/assets/js/noconflict.js');
$document->addScript($rootUri . '/media/com_eshop/assets/js/eshop.js');

// Load CSS of corresponding theme
$theme = EShopHelper::getConfigValue('theme');

if (is_file(JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/css/style.css'))
{
	$document->addStyleSheet($rootUri . '/components/com_eshop/themes/' . $theme . '/css/style.css');
}
else
{
	$document->addStyleSheet($rootUri . '/components/com_eshop/themes/default/css/style.css');
}

// Load custom CSS file
if (is_file(JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/css/custom.css'))
{
	$document->addStyleSheet($rootUri . '/components/com_eshop/themes/' . $theme . '/css/custom.css');
}
elseif (is_file(JPATH_ROOT . '/components/com_eshop/themes/default/css/custom.css'))
{
	$document->addStyleSheet($rootUri . '/components/com_eshop/themes/default/css/custom.css');
}

// Perform the Request task
$controller->execute($input->getCmd('task', 'display'));
$controller->redirect();