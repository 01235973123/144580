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
use Joomla\CMS\Uri\Uri;

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

$input   = Factory::getApplication()->input;
$command = $input->get('task', 'display');

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
	$path = JPATH_COMPONENT . '/controller.php';
	require_once $path;
	$controller = new EShopController();
}

Factory::getApplication()->getDocument()->addStyleSheet(Uri::base(true) . '/components/com_eshop/assets/css/style.css');

// Perform the Request task
$controller->execute($input->get('task'));
$controller->redirect();