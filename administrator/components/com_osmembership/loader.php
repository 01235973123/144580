<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

// define('MP_DEBUG', true);
define('OSM_DEFAULT_RENEW_OPTION_ID', 999);

$autoLoader = include JPATH_LIBRARIES . '/vendor/autoload.php';

if ($autoLoader)
{
	$autoLoader->setPsr4('OSSolution\\MembershipPro\\Admin\\Event\\', JPATH_ADMINISTRATOR . '/components/com_osmembership/Event');
}

/**
 * Re-register auto loader for MPF MVC library
 */
JLoader::registerPrefix('MPF', JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries/mpf');

$app = Factory::getApplication();

// Register table classes autoloader
if (!$app->isClient('administrator'))
{
	JLoader::registerPrefix('OSMembershipTable', JPATH_ADMINISTRATOR . '/components/com_osmembership/table');
}

// This should not be needed, but we leave it here for backward compatible purpose
Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_osmembership/table');

if (!$app->isClient('site'))
{
	// Register autoloader for helper classes
	JLoader::register('OSMembershipHelper', JPATH_ROOT . '/components/com_osmembership/helper/helper.php');
	JLoader::registerPrefix('OSMembershipHelper', JPATH_ROOT . '/components/com_osmembership/helper');

	// Register auto-loader for other shared classes
	JLoader::register('OSMembershipModelSubscriptiontrait', JPATH_ROOT . '/components/com_osmembership/model/subscriptiontrait.php');
	JLoader::register('OSMembershipModelValidationtrait', JPATH_ROOT . '/components/com_osmembership/model/validationtrait.php');
	JLoader::register('OSMembershipModelApi', JPATH_ROOT . '/components/com_osmembership/model/api.php');
	JLoader::register('OSMembershipModelOverrideApi', JPATH_ROOT . '/components/com_osmembership/model/override/api.php');

	JLoader::register('OSMembershipViewPlan', JPATH_ROOT . '/components/com_osmembership/view/plan.php');
}

if ($app->isClient('api'))
{
	JLoader::registerPrefix('OSMembership', JPATH_ADMINISTRATOR . '/components/com_osmembership');
}
else
{
	JLoader::registerPrefix('OSMembership', JPATH_BASE . '/components/com_osmembership');
}

// We do not use these two classes, but it needs to be here for backward compatible purpose
JLoader::register('os_payments', JPATH_ROOT . '/components/com_osmembership/plugins/os_payments.php');
JLoader::register('os_payment', JPATH_ROOT . '/components/com_osmembership/plugins/os_payment.php');

require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries/vendor/autoload.php';

if (OSMembershipHelper::isJoomla5())
{
	JLoader::registerAlias('JDatabaseQuery', \Joomla\Database\DatabaseQuery::class);

	// Force autoload class to make it available for using
	class_exists('JDatabaseQuery');
}

// Disable STRICT_TRANS_TABLES mode required in Joomla 4

/* @var \Joomla\Database\DatabaseDriver $db */
$db = Factory::getContainer()->get('db');
$db->setQuery("SET sql_mode=(SELECT REPLACE(@@sql_mode,'STRICT_TRANS_TABLES',''));");
$db->execute();

// Workaround for buggy PHP installation
if (!defined('CURL_SSLVERSION_TLSv1_2'))
{
	define('CURL_SSLVERSION_TLSv1_2', 6);
}