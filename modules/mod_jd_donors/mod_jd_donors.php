<?php

/**
 * @version        5.4.10
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;


require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/loader.php';
require_once JPATH_ROOT .'/components/com_jdonation/helper/route.php';
$db					= Factory::getDbo();
$query				= $db->getQuery(true);
DonationHelper::loadComponentCssForModules();
$styleUrl			= Uri::base(true) . '/modules/mod_jd_donors/assets/css/style.css';
Factory::getDocument()->addStylesheet($styleUrl);
DonationHelper::loadLanguage();
$config				= DonationHelper::getConfig();
$donorType			= $params->get('donor_type', 0);
$numberDonors		= $params->get('number_donors', 6);
$integration		= $params->get('integration', 0);
$displayUsername	= $params->get('display_username', 1);
$campaignIds		= trim($params->get('campaign_ids'));
$display_currency	= $params->get('display_currency',1);
$show_honoreename	= $params->get('show_honoreename', 0);
$show_campaign		= $params->get('show_campaign',1);
$show_donation_amount = $params->get('show_donation_amount',1);
$show_donor_address	= $params->get('show_donor_address',0);
$show_donor_city	= $params->get('show_donor_city',0);
$show_donor_state	= $params->get('show_donor_state',0);
$show_donor_country	= $params->get('show_donor_country',0);
$show_comment		= $params->get('show_comment',0);

$bootstrapHelper	= new DonationHelperBootstrap($config->twitter_bootstrap_version);

$model				= OSFModel::getInstance('Donors', 'DonationModel', array('option' => 'com_jdonation', 'ignore_request' => true, 'remember_states' => false, 'table_prefix' => '#__jd_', 'class_prefix' => 'Donation'));
$model->filter_state('P');
if ($campaignIds)
{
	$model->filter_campaign_ids($campaignIds);
}
$model->filter_amount(1);
switch ($donorType)
{
	case 0:
		$model->filter_order('amount')
			->filter_order_Dir('DESC');
		break;
	case 1:
		$model->filter_order('created_date')
			->filter_order_Dir('DESC');
		break;
	case 2:
		$model->filter_order('rand()')
			->filder_order_Dir('');
		break;
}
$model->limitstart(0)
	->limit($numberDonors);
$rows = $model->getData();

if ($integration == 1)
{
	$query->select('id')
		->from('#__menu')
		->where("link LIKE '%index.php?option=com_comprofiler%'");
	$db->setQuery($query);
	$itemId = $db->loadResult();
}
elseif ($integration == 2)
{
	$query->select('id')
		->from('#__menu')
		->where("link LIKE '%index.php?option=com_community%'");
	$db->setQuery($query);
	$itemId = $db->loadResult();
}
require(ModuleHelper::getLayoutPath('mod_jd_donors', 'default'));
