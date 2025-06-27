<?php

/**
 * Register the prefix so that the classes in EshopRAD library can be auto-load
 */

use Joomla\Database\DatabaseQuery;

defined('_JEXEC') or die;

JLoader::registerPrefix('EshopRAD', __DIR__);

if (EShopHelper::isJoomla5())
{
	JLoader::registerAlias('JDatabaseQuery', DatabaseQuery::class);

	// Force autoload class to make it available for using
	class_exists('JDatabaseQuery');
}

if (EShopHelper::getConfigValue('debug_mode', 0))
{
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
}
else
{
	error_reporting(0);
}