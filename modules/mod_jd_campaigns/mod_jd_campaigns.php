<?php

/**
 * @version         5.4.10
 * @package         Joomla
 * @subpackage      Joom Donation
 * @author          Tuan Pham Ngoc
 * @copyright       Copyright (C) 2009 - 2024 Ossolution Team
 * @license         GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR);
require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/loader.php';

//$styleUrl           = JUri::root() . 'media/com_jdonation/assets/css/style.css';
//JFactory::getDocument()->addStylesheet($styleUrl, 'text/css', null, null);
global $loadMedia;
DonationHelper::loadComponentCssForModules();
DonationHelper::loadLanguage();
$showTitle          = $params->get('show_title', 1);
$showGoal           = $params->get('show_goal', 1);
$showDonatedAmount  = $params->get('show_donated_amount', 1);
$showPercentDonated = $params->get('show_percent_donated', 1);
$showNumberDonors   = $params->get('show_number_donors', 1);
$showDaysLeft       = $params->get('show_days_left', 1);
$showButton         = $params->get('show_donate_button', 1);
$showCampaignDate	= $params->get('show_campaign_date',1);
$cids               = $params->get('cids', '');
$number_columns		= $params->get('number_columns',1);
$category_id		= $params->get('category_id',0);
$config             = DonationHelper::getConfig();
if ($config->load_twitter_bootstrap)
{
	DonationHelper::loadBootstrap(false);
}
//$bootstrapHelper    = new DonationHelperBootstrap($config->twitter_bootstrap_version);
$bootstrapHelper    = DonationHelperBootstrap::getInstance();
$rowFluidClass   	= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class		= $bootstrapHelper->getClassMapping('span12');
$span3Class		    = $bootstrapHelper->getClassMapping('span3');
$span6Class		    = $bootstrapHelper->getClassMapping('span6');
$span4Class		    = $bootstrapHelper->getClassMapping('span4');

$Itemid             = $params->get('item_id',Factory::getApplication()->input->getInt('Itemid',0));
$model = OSFModel::getInstance('Campaigns', 'DonationModel', array('option' => 'com_jdonation' , 'ignore_request' => true, 'remember_states' => false, 'table_prefix' => '#__jd_', 'class_prefix' => 'Donation'));
if ($cids)
{
    $model->cids($cids);
}
if($category_id > 0)
{
	$model->category_id ($category_id);
}
$rows = $model->getData();
$itemId = (int)$params->get('item_id');
if (!$itemId)
{
    $itemId = DonationHelper::getItemid();
}
require(ModuleHelper::getLayoutPath('mod_jd_campaigns', 'default'));
?>
