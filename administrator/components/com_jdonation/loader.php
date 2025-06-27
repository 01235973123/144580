<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
/**
 * Reregister prefix and classes for auto-loading
 */

JLoader::registerPrefix('OSF', JPATH_ADMINISTRATOR . '/components/com_jdonation/libraries/osf', false, true);

if (!Factory::getApplication()->isClient('administrator'))
{
	JLoader::registerPrefix('DonationTable', JPATH_ADMINISTRATOR . '/components/com_jdonation/table');
}

if (Factory::getApplication()->isClient('api'))
{
	JLoader::registerPrefix('Donation', JPATH_ADMINISTRATOR . '/components/com_jdonation');
}
else
{
	JLoader::registerPrefix('Donation', JPATH_BASE . '/components/com_jdonation',false, true);
}


JLoader::register('os_jdpayments', JPATH_SITE . '/components/com_jdonation/payments/os_payments.php');
JLoader::register('os_payment', JPATH_SITE . '/components/com_jdonation/payments/os_payment.php');

if (!Factory::getApplication()->isClient('site'))
{
	JLoader::register('DonationHelper', JPATH_SITE . '/components/com_jdonation/helper/helper.php');
	JLoader::register('DonationHelperHtml', JPATH_SITE . '/components/com_jdonation/helper/html.php');
	JLoader::register('DonationHelperBootstrap', JPATH_SITE . '/components/com_jdonation/helper/bootstrap.php');
	JLoader::register('DonationHelperCryptor', JPATH_SITE . '/components/com_jdonation/helper/cryptor.php');

	// Register override classes
	$possibleOverrides = [
		'DonationHelperOverrideHelper'       => 'helper.php',
	];

	foreach ($possibleOverrides as $className => $filename)
	{
		JLoader::register($className, JPATH_SITE . '/components/com_jdonation/helper/override/' . $filename);
	}
}
else
{
	//Front-end, we will re-use some controllers and models from back-end
	JLoader::register('DonationControllerDonor', JPATH_ADMINISTRATOR . '/components/com_jdonation/controller/donor.php');
	JLoader::register('DonationModelCampaigns', JPATH_ADMINISTRATOR . '/components/com_jdonation/model/campaigns.php');
	JLoader::register('DonationModelDonors', JPATH_ADMINISTRATOR . '/components/com_jdonation/model/donors.php');
	JLoader::register('DonationModelPlugins', JPATH_ADMINISTRATOR . '/components/com_jdonation/model/plugins.php');
}


if (DonationHelper::isJoomla5())
{
	JLoader::registerAlias('JDatabaseQuery', \Joomla\Database\DatabaseQuery::class);

	// Force autoload class to make it available for using
	class_exists('JDatabaseQuery');
}

if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
{
	$db = Factory::getContainer()->get('db');
	$db->setQuery("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
	$db->execute();
}


if (!defined('CURL_SSLVERSION_TLSv1_2'))
{
	define('CURL_SSLVERSION_TLSv1_2', 6);
}

class JoomdonationHelper extends DonationHelper
{

}

