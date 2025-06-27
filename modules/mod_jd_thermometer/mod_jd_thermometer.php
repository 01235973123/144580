<?php
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
/**
 * @version        4.2
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2016 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR);
if (!defined('PATH_IMAGES'))
{
	define("PATH_IMAGES", Uri::root() . 'media/com_jdonation/assets/images');
}
$document		= Factory::getDocument();
require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/loader.php';

DonationHelper::loadComponentCssForModules();
DonationHelper::loadLanguage();
$donorType		= $params->get('donor_type', 0);
$cids			= $params->get('cids', '');
$showTitle		= $params->get('show_title', 'none');
$color			= $params->get('color', 'therm.jpg');
$showButton		= $params->get('show_donate_button', 1);
$model			= OSFModel::getInstance('Campaigns', 'DonationModel', array('option' => 'com_jdonation', 'ignore_request' => true, 'remember_states' => false, 'table_prefix' => '#__jd_', 'class_prefix' => 'Donation'));
if ($cids)
{
    $model->cids($cids);
}
$rows			= $model->getData();
$itemId			= (int) $params->get('item_id');
if (!$itemId)
{
	$itemId		= DonationHelper::getItemid();
}
$config			= DonationHelper::getConfig();
$thousands_sep = $config->thousands_sep;
if($thousands_sep == "")
{
	$thousands_sep = ",";
}
if ($config->load_twitter_bootstrap)
{
	DonationHelper::loadBootstrap();
}
HTMLHelper::_('script', DonationHelper::getSiteUrl() . '/media/com_jdonation/assets/js/noconflict.js', false, false);

require (ModuleHelper::getLayoutPath('mod_jd_thermometer', 'default'));
?>
