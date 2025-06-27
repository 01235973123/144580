<?php
/**
 * @version        4.2
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2022 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
error_reporting(0);
include JPATH_ADMINISTRATOR . '/components/com_jdonation/config.php';
require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/loader.php';
DonationHelper::loadLanguage();
$campaignId = $params->get('campaign_id');
$request    = array('view' => 'donation', 'campaign_id' => $campaignId, 'content_plugin' => 1, 'Itemid' => DonationHelper::getItemid());
$input      = new OSFInput($request);
//Execute the controller
return OSFController::getInstance('com_jdonation', $input, $jdConfig)->execute();
