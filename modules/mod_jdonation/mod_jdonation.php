<?php
/**
 * @version        5.11.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

error_reporting(E_ERROR | E_PARSE);
require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/loader.php';
require_once JPATH_ROOT .'/components/com_jdonation/helper/route.php'; 
$db						= Factory::getDbo();
DonationHelper::loadComponentCssForModules();

Factory::getDocument()->addStylesheet(Uri::base(true) . '/modules/mod_jdonation/assets/style.css', 'text/css', null, null);
Factory::getApplication()->getDocument()->getWebAssetManager()->registerAndUseScript('noconflict', Uri::root() .'media/com_jdonation/assets/js/noconflict.js');
Factory::getApplication()->getDocument()->getWebAssetManager()->registerAndUseScript('jdonationscript', Uri::root() .'media/com_jdonation/assets/js/jdonation.js');
DonationHelper::loadLanguage();
$config					= DonationHelper::getConfig();
$background_color		= $params->get('background_color','#4b3381');
$text_color				= $params->get('text_color','#FFFFFF');
$highlight_text			= $params->get('highlight_text','#3283b6');
$highlight_bgcolor		= $params->get('highlight_bgcolor','#3283b6');
$campaign_id			= $params->get('campaign_id','');
$box_width				= $params->get('box_width','120');
$show_campaign			= $params->get('show_campaign',0);
$show_raised			= $params->get('show_raised',0);
$query                  = $db->getQuery(true);
if($show_campaign == 1)
{
	$fieldSuffix        = DonationHelper::getFieldSuffix();
	$options = [] ;
	$options[] = HTMLHelper::_('select.option', '' , Text::_('JD_SELECT_CAMPAIGN') , 'id', 'title') ;
	$query->clear();
	$query->select('*')
		->from('#__jd_campaigns')
		->where('published = 1')
		//->where('(start_date = '.$db->quote($nullDate).' OR DATE(start_date) <= CURDATE())')
		//->where('(end_date = '.$db->quote($nullDate).' OR DATE(end_date) >= CURDATE())')
		->order('ordering');

	if ($fieldSuffix)
	{
		$fields = array(
			'title',
			'description',
			'amounts_explanation',
			'donation_form_msg',
			'thanks_message'
		);
		DonationHelper::getMultilingualFields($query, $fields, $fieldSuffix);
	}
	$db->setQuery($query);
	$rowCampaigns = $db->loadObjectList();
	$options = array_merge($options, $rowCampaigns) ;
	$lists['campaign_id'] = HTMLHelper::_('select.genericlist', $options, 'campaign_id', ' class="form-select" onchange="processChangeCampaign();" ', 'id', 'title') ;
}

$amounts				= $config->donation_amounts;
if ($amounts != '')
{
    $amounts			= explode("\r\n", $amounts);
}


if ((int)$campaign_id > 0) 
{
    $db->setQuery("SELECT * FROM #__jd_campaigns WHERE id = " . $db->quote($campaign_id));
    $campaign = $db->loadObject();

    if ($campaign !== null) {
        $campaign_amounts = $campaign->amounts;

        $query = $db->getQuery(true);
        $query->select('SUM(amount)')
            ->from('#__jd_donors')
            ->where('campaign_id = ' . $db->quote($campaign_id))
            ->where('published = 1');
        $db->setQuery($query);

        $campaign->donated_amount = floatval($db->loadResult());

        if ($campaign_amounts != "") {
            $amounts = explode("\r\n", $campaign_amounts);
        }

        if ($campaign->show_amounts == 0) {
            $amounts = '';
        }
    } else {
        
        $campaign_amounts = '';
        $amounts = '';
        $campaign = new stdClass(); 
    }
}
//Get list of payment methods
//$model					= OSFModel::getInstance('Plugins', 'DonationModel', array('option' => 'com_jdonation', 'ignore_request' => true, 'remember_states' => false, 'table_prefix' => '#__jd_', 'class_prefix' => 'Donation'));
//$paymentPlugins			= $model->filter_state('P')->getData();

$paymentPlugins   = (array)os_jdpayments::getPaymentMethods((int)$campaign_id);



$itemId					= (int)$params->get('item_id');
if (!$itemId)
{
    $itemId				= DonationHelper::getItemid();
}
$currencySymbol			= $config->currency_symbol;
$minimumAmount			= (int)$config->minimum_donation_amount;
$maximumAmount			= (int)$config->maximum_donation_amount;
$donationType			= $params->get('donation_type', 2);
require ModuleHelper::getLayoutPath('mod_jdonation', $params->get('layout', 'default'));