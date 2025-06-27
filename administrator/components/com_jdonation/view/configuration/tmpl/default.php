<?php
/**
 * @version        5.6.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;

if (!DonationHelper::isJoomla4())
{
	HTMLHelper::_('behavior.tooltip');
}
else
{
	HTMLHelper::_('bootstrap.tooltip');
}
Factory::getApplication()->getDocument()->getWebAssetManager()->registerAndUseScript('com_jdonation.jcolor',Uri::root() . 'components/com_jdonation/assets/js/colorpicker/jscolor.js');
// Set toolbar items for the page
ToolbarHelper::title(Text::_('JD_CONFIGURATION'), 'equalizer.png');
ToolbarHelper::save('save');
ToolbarHelper::apply('apply');
ToolbarHelper::cancel();
$bootstrapHelper		= $this->bootstrapHelper;
$rowFluidClass			= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class			= $bootstrapHelper->getClassMapping('span12');
$span7Class				= $bootstrapHelper->getClassMapping('span7');
$span5Class				= $bootstrapHelper->getClassMapping('span5');
$controlGroupClass		= $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass		= $bootstrapHelper->getClassMapping('control-label');
$controlsClass			= $bootstrapHelper->getClassMapping('controls');
$editor					= Editor::getInstance(Factory::getApplication()->getConfig()->get('editor'));
$languages				= DonationHelper::getLanguages();
$translatable			= Multilanguage::isEnabled() && count($languages);
$editorPlugin			= null;
if (PluginHelper::isEnabled('editors', 'codemirror'))
{
	$editorPlugin = 'codemirror';
}
elseif(PluginHelper::isEnabled('editor', 'none'))
{
	$editorPlugin = 'none';
}
if ($editorPlugin)
{
	$showCustomCss = 1;
}else{
	$showCustomCss = 0;
}
$config = $this->config;
if (DonationHelper::isJoomla4())
{
	$tabApiPrefix = 'uitab.';

	Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('showon');
}
else
{
	$tabApiPrefix = 'bootstrap.';

	HTMLHelper::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
}

$db                 = Factory::getContainer()->get('db');
?>
<form action="index.php?option=com_jdonation&view=configuration" method="post" name="adminForm" id="adminForm" class="form-horizontal">
    <div id="j-main-container">
        <div class="<?php echo $rowFluidClass; ?>">
            <?php
            echo HTMLHelper::_($tabApiPrefix.'startTabSet', 'configuration', array('active' => 'general-page'));
                echo HTMLHelper::_($tabApiPrefix.'addTab', 'configuration', 'general-page', Text::_('JD_GENERAL', true));
                ?>
                <fieldset class="form-horizontal options-form">
					<legend><?php echo Text::_('JD_MAIN_CONFIGURATION'); ?></legend>
                    <table class="admintable adminform" style="width:100%;">
                        <tr>
                            <td class="key" width="30%">
                                <?php echo Text::_('JD_FRONTEND_CSS_FRAMEWORK'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['twitter_bootstrap_version']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_FRONTEND_CSS_FRAMEWORK_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key" width="25%">
                                <?php echo Text::_('JD_LOAD_TWITTER_BOOTSTRAP_CSS'); ?>
                            </td>
                            <td width="15%">
                                <?php echo $this->lists['load_twitter_bootstrap']; ?>
                            </td>
                            <td width="60%" class="field_explanation">
                                <?php echo Text::_('JD_LOAD_TWITTER_BOOTSTRAP_CSS_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td  class="key">
                                <?php echo Text::_('JD_INTEGRATION'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['cb_integration']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_INTEGRATION_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_USER_REGISTRATION'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['registration_integration']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_USER_REGISTRATION_EXPLAIN_NEW'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_SHOW_UPDATE_AVAILABLE'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['show_update_available_message_in_dashboard']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_SHOW_UPDATE_AVAILABLE_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_CREATE_ACCOUNT_WHEN_DONATION_ACTIVATED'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['create_account_when_donation_active']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_CREATE_ACCOUNT_WHEN_DONATION_ACTIVATED_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_SHOW_LOGIN_BOX'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['show_login_box']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_SHOW_LOGIN_BOX_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ACTIVATE_HTTPS'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['use_https']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ACTIVATE_HTTPS_EXPLAIN'); ?>
                            </td>
                        </tr>
					</table>
				</fieldset>
				<fieldset class="form-horizontal options-form">
					<legend><?php echo Text::_('JD_MAIN_CONFIGURATION'); ?></legend>
                    <table class="admintable adminform" style="width:100%;">
                        <tr>
                            <td class="key" width="30%">
                                <?php echo Text::_('JD_ACTIVATE_CAMPAIGN_FEATURE'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['use_campaign']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ACTIVATE_CAMPAIGN_FEATURE_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key" width="30%">
                                <?php echo Text::_('JD_SHOW_CAMPAIGN_DETAILS'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['show_campaign']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_SHOW_CAMPAIGN_DETAILS_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key" width="30%">
                                <?php echo Text::_('JD_SHOW_CAMPAIGN_PROGRESS'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['show_campaign_progress']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_SHOW_CAMPAIGN_PROGRESS_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" width="30%">
                                <?php echo Text::_('JD_SHOW_NO_PICTURE_AVAILABLE'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['show_campaign_picture']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_SHOW_NO_PICTURE_AVAILABLE_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key" width="30%">
                                <?php echo Text::_('JD_ENABLE_DONATION_WITH_EXPIRED_CAMPAIGNS'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['endable_donation_with_expired_campaigns']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ENABLE_DONATION_WITH_EXPIRED_CAMPAIGNS_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key" width="30%">
                                <?php echo Text::_('JD_ENABLE_DONATION_WITH_GOAL_ACHIEVED_CAMPAIGNS'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['endable_donation_with_goal_achieved_campaigns']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ENABLE_DONATION_WITH_GOAL_ACHIEVED_CAMPAIGNS_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key" width="30%">
                                <?php echo Text::_('JD_AUTO_APPROVAL_CAMPAIGN'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['auto_approval_campaign']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_AUTO_APPROVAL_CAMPAIGN_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" width="30%">
                                <?php echo Text::_('JD_ENABLE_SELECTING_CURRENCY_IN_CAMPAIGN'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['activate_campaign_currency']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ENABLE_SELECTING_CURRENCY_IN_CAMPAIGN_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" width="30%">
                                <?php echo Text::_('JD_ACTIVE_SIMPLE_MULTILINGUAL'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['simple_language']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ACTIVATE_SIMPLE_MULTILINGUAL_EXPLAIN'); ?>
                            </td>
                        </tr>
					</table>
				</fieldset>
				<fieldset class="form-horizontal options-form">
					<legend><?php echo Text::_('JD_DONATION_SETTING'); ?></legend>
                    <table class="admintable adminform" style="width:100%;">
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ENABLE_RECURRING_DONATION'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['enable_recurring']; ?>
                            </td>
                            <td >
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ALLOW_DONORS_TO_CANCEL_RECURRING_DONATION'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['enable_cancel_recurring']; ?>
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_ALLOW_DONORS_TO_CANCEL_RECURRING_DONATION_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_RECURRING_FREQUENCIES'); ?>
                            </td>
                            <td>
                                <?php echo DonationHelper::getChoicesJsSelect($this->lists['recurring_frequencies']); ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_RECURRING_FREQUENCIES_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ENABLE_CURRENCY_SELECTION'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['currency_selection']; ?>
                            </td>
                            <td class="field_explanation"> 
                                <?php echo Text::_('JD_ENABLE_CURRENCY_SELECTION_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ACTIVE_CURRENCIES'); ?>
                            </td>
                            <td>
                                <?php echo DonationHelper::getChoicesJsSelect($this->lists['active_currencies']); ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ACTIVE_CURRENCIES_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_CONVERT_CURRENCY_BEFORE_DONATION'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['convert_currency_before_donation'] ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_CONVERT_CURRENCY_BEFORE_DONATION_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_SHOW_PENDING_DONATION_RECORDS'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['show_pending_records']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_SHOW_PENDING_DONATION_RECORDS_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ENABLE_CAPTCHA'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['enable_captcha']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ENABLE_CAPTCHA_EXPLAIN'); ?>
                            </td>
                        </tr>
						<?php
						$jdcaptcha = $this->lists['jdcaptcha'];
						if($jdcaptcha->extension_id > 0)
						{
						?>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_USE_JD_CAPTCHA'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['use_jd_captcha']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_USE_JD_CAPTCHA_EXPLAIN'); ?>
                            </td>
                        </tr>
						<?php } ?>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ENABLE_CAPTCHA_WITH_PUBLIC'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['enable_captcha_with_public_user']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ENABLE_CAPTCHA_WITH_PUBLIC_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_BLOCK_IP_ADDRESSES'); ?>
                            </td>
                            <td>
								<textarea rows="5" cols="40" name="block_ip_addresses" class="form-control"><?php echo $this->config->block_ip_addresses;?></textarea> 
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_BLOCK_IP_ADDRESSES_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_SHOW_NUMBER_OCCURRENCES'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['show_r_times'] ; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_SHOW_NUMBER_OCCURRENCES_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_PAYMENT_GATEWAY_FEE'); ?>
                            </td>
                            <td>
                                <input type="text" name="convenience_fee" class="input-small form-control" value="<?php echo $this->config->convenience_fee; ?>" size="5" /> (%)
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_INCLUDE_PAYMENT_FEE'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['include_payment_fee'];?>
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_INCLUDE_PAYMENT_FEE_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_DONOR_CAN_CHOOSE_PAY_FEE') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['pay_payment_gateway_fee'] ; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_DONOR_CAN_CHOOSE_PAY_FEE_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_CUSTOM_FIELD_BY_CAMPAIGN'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['field_campaign'] ; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_CUSTOM_FIELD_BY_CAMPAIGN_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_AMOUNTS_BY_CAMPAIGN'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['amount_by_campaign'] ; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_AMOUNTS_BY_CAMPAIGN_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ALLOW_ANONYMOUS_DONATION'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['enable_hide_donor']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ALLOW_ANONYMOUS_DONATION_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_PRE_DEFINED_AMOUNT_FORMAT') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['amounts_format'] ; ?>
                            </td>
                            <td >
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_MINIMUM_DONATION_AMOUNT') ; ?>
                            </td>
                            <td>
                                <input type="number" name="minimum_donation_amount" class="input-mini form-control" value="<?php echo $this->config->minimum_donation_amount; ?>" size="10" />
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_MAXIMUM_DONATION_AMOUNT') ; ?>
                            </td>
                            <td>
                                <input type="number" name="maximum_donation_amount" class="input-mini form-control" value="<?php echo $this->config->maximum_donation_amount; ?>" size="10" />
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_CURRENCY'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['currency']; ?>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_CURRENCY_SYMBOL'); ?>
                            </td>
                            <td>
                                <input type="text" name="currency_symbol" class="input-mini form-control" value="<?php echo $this->config->currency_symbol; ?>" size="10" />
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>

                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_NUMBER_DECIMALS'); ?>
                            </td>
                            <td>
                                <input type="number" name="decimals" class="input-mini form-control" value="<?php echo isset($this->config->decimals) ? $this->config->decimals : '2'; ?>" size="10" />
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_NUMBER_DECIMALS_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_DECIMAL_POINT'); ?>
                            </td>
                            <td>
                                <input type="text" name="dec_point" class="input-mini form-control" value="<?php echo isset($this->config->dec_point) ? $this->config->dec_point : '.'; ?>" size="10" />
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_DECIMAL_POINT_EXPLAIN'); ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_THOUSAND_SEPARATOR'); ?>
                            </td>
                            <td>
                                <input type="text" name="thousands_sep" class="input-mini form-control" value="<?php echo isset($this->config->thousands_sep) ? $this->config->thousands_sep : ','; ?>" size="10" />
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_THOUSAND_SEPARATOR_EXPLAIN'); ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_CURRENCY_POSITION'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['currency_position']; ?>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_LEAVE_SPACE_BETWEEN_CURRENCY_AND_AMOUNT'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['currency_space']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_LEAVE_SPACE_BETWEEN_CURRENCY_AND_AMOUNT_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_DEFAULT_COUNTRY'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['country_list']; ?>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_PRE_DEFINED_AMOUNT'); ?>
                            </td>
                            <td>
                                <textarea rows="5" cols="40" name="donation_amounts" class="form-control"><?php echo $this->config->donation_amounts; ?></textarea>
                            </td>
                            <td class="field_explanation">
                                <strong>Each item in one line</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_PRE_DEFINED_AMOUNT_EXPLANATION'); ?>
                            </td>
                            <td>
                                <textarea rows="5" cols="40" name="donation_amounts_explanation" class="form-control"><?php echo $this->config->donation_amounts_explanation; ?></textarea>
                            </td>
                            <td class="field_explanation">
                                <strong>
                                    Each item in one line
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_USING_BRACKETS_IN_AMOUNT_EXPLANATION'); ?>
                            </td>
                            <td>
                                <?php
                                echo $this->lists['show_brackets'];
                                ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_USING_BRACKETS_IN_AMOUNT_EXPLANATION_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_DISPLAY_AMOUNT_TEXTBOX') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['display_amount_textbox']; ?>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_POPULATE_DONATION_DATA_FROM_PREVIOUS_DONATION') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['populate_from_previous_donation']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_POPULATE_DONATION_DATA_FROM_PREVIOUS_DONATION_EXPLAIN') ; ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_DONATION_COMPLETE_URL') ?>
                            </td>
                            <td>
                                <input type="text" name="complete_url" class="input-large form-control ilarge" value="<?php echo $this->config->complete_url; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_DONATION_COMPLETE_URL_EXPLAIN') ?>
                            </td>
                        </tr>
					</table>
				</fieldset>
				<fieldset class="form-horizontal options-form">
					<legend><?php echo Text::_('JD_TRIBUTES_SETTING'); ?></legend>
                    <table class="admintable adminform" style="width:100%;">
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ACTIVATE_TRIBUTES') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['activate_tributes']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ACTIVATE_TRIBUTES_DESC') ; ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_TRIBUTE_TYPES') ; ?>
                            </td>
                            <td>
                                <?php echo DonationHelper::getChoicesJsSelect($this->lists['dedicate_type']); ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_TRIBUTE_TYPES_DESC') ; ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_ADD_HONOREE_INFORMATION_IN_CSV') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['add_honoree_in_csv']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ADD_HONOREE_INFORMATION_IN_CSV_DESC') ; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_SEND_EMAIL_TO_HONOREE') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['send_email_to_honoree']; ?>
                            </td>
                            <td>
                            </td>
                        </tr>
					</table>
				</fieldset>
				<fieldset class="form-horizontal options-form">
					<legend><?php echo Text::_('JD_OTHER_SETTING'); ?></legend>
                    <table class="admintable adminform" style="width:100%;">
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ACTIVATE_EXPORT_DONORS_FEATURE') ?>
                            </td>
                            <td>
                                <?php
                                echo $this->lists['export_donors'];
                                ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ACTIVATE_EXPORT_DONORS_FEATURE_EXPLAIN') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_SHOW_TERMS_AND_CONDITIONS') ?>
                            </td>
                            <td>
                                <?php
                                echo $this->lists['active_term'];
                                ?>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_TERM_AND_CONDITION_ARTICLE') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['article_id']; ?>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_SHOW_NEWSLETTER_SUBSCRIPTION') ?>
                            </td>
                            <td>
                                <?php
                                echo $this->lists['show_newsletter_subscription'];
                                ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_SHOW_NEWSLETTER_SUBSCRIPTION_EXPLAIN') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_DATE_FORMAT') ; ?>
                            </td>
                            <td>
                                <input type="text" name="date_format" class="input-small form-control" value="<?php echo $this->config->date_format; ?>" size="10" />
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_ENABLE_GIFT_AID') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['enable_gift_aid']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ENABLE_GIFT_AID_EXPLAIN') ; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ALLOWED_FILE_TYPES'); ?>
                            </td>
                            <td>
                                <input type="text" name="allowed_extensions" class="input-large form-control" size="50" value="<?php echo $this->config->allowed_extensions ? $this->config->allowed_extensions: 'doc, docx, ppt, pptx, pdf, zip, rar, jpg, jepg, png, zip'; ?>" />
                            </td>
                            <td class="field_explanation">
                                List of allowed file types, comma seperated
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_MAX_UPLOAD_FILE_SIZE'); ?>
                            </td>
                            <td>
                                <input type="number" name="upload_max_file_size" class="input-mini form-control imini" size="50" value="<?php echo $this->config->upload_max_file_size ? $this->config->upload_max_file_size: '2'; ?>" />MB
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_MAX_UPLOAD_FILE_SIZE_EXPLAIN'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('Send Attachment to Admin Emails'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['send_attachment_to_admin_email']; ?>
                            </td>
                            <td class="field_explanation">
                                If set to Yes, all the attachments which users uploaded when they make payment will be sent to administrator emails
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_NUMBER_SEGMENTS') ; ?>
                            </td>
                            <td>
                                <input class="input-mini form-control imini" type="number" name="number_segmenets" value="<?php echo isset($this->config->number_segmenets) ? $this->config->number_segmenets : 5; ?>" />
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_SELECT_TEMPLATE_STYLE') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['layout_type']; ?>
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_SELECT_TEMPLATE_STYLE_EXPLAIN') ; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_DEFAULT_DONATION_LAYOUT') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['default_layout']; ?>
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_DEFAULT_DONATION_LAYOUT_EXPLAIN') ; ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_BACKGROUND_CAMPAIGN_DETAILS') ; ?>
                            </td>
                            <td>
                                <input type="text" name="campaign_color" class="input-small form-control color" value="<?php echo ($this->config->campaign_color != '') ? $this->config->campaign_color : '7bb4e0' ; ?>" />
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_BACKGROUND_CAMPAIGN_DETAILS_EXPLAIN') ; ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('CSV Delimiter') ; ?>
                            </td>
                            <td>
                                <input type="text" name="csv_delimiter" class="input-mini imini form-control" value="<?php echo ($this->config->csv_delimiter != '') ? $this->config->csv_delimiter : ',' ; ?>" />
                            </td>
                            <td class="field_explanation">
                                &nbsp;
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_ACTIVATE_CAMPAIGN_SHARING') ?>
                            </td>
                            <td>
                                <?php
                                echo $this->lists['activate_campaign_sharing'];
                                ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ACTIVATE_CAMPAIGN_SHARING_EXPLAIN') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ACTIVE_SHARING') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['social_sharing']; ?>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_SHARING_TYPE') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['social_sharing_type']; ?>
                            </td>
                            <td class="field_explanation">
                                For Native Sharing, there is no need to enter publisher id
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('JD_ADDTHIS_PUBLISHER_ID'); ?>
                            </td>
                            <td>
                                <input type="text" name="addthis_publisher" class="input-small form-control" value="<?php echo $this->config->addthis_publisher; ?>" />
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_ACTIVATE_QRCODE'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists ['qr_code']; ?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_ACTIVATE_QRCODE_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td colspan="3" class="config_heading">
                                <h3><?php echo Text::_('JD_SIMPLE_LAYOUT_SETTING') ; ?></h3>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_BACKGROUND_COLOR_SIMPLE_LAYOUT') ; ?>
                            </td>
                            <td>
                                <input type="text" name="color" class="input-small form-control color" value="<?php echo ($this->config->color != '') ? $this->config->color : 'b250d2' ; ?>" />
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_BACKGROUND_COLOR_SIMPLE_LAYOUT_EXPLAIN') ; ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_FIELD_DESCRIPTION') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['display_field_description']; ?>
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_FIELD_DESCRIPTION_EXPLAIN') ; ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_PAYMENT_METHODS_SHOWING') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['show_payment_method']; ?>
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_PAYMENT_METHODS_SHOWING_EXPLAIN') ; ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_ACTIVATE_FORM_FLOATING') ; ?>
                            </td>
                            <td>
                                <?php echo $this->lists['activate_form_floating']; ?>
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_ACTIVATE_FORM_FLOATING_EXPLAIN') ; ?>
                            </td>
                        </tr>
						<tr>
                            <td colspan="3" class="config_heading">
                                <h3><?php echo Text::_('JD_ANTI_SPAM_SETTING') ; ?></h3>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_MIN_FORM_TIME') ; ?>
                            </td>
                            <td>
                                <input type="text" name="min_form_time" class="input-mini imini form-control" value="<?php echo $this->config->min_form_time; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_MINIMUM_FORM_TIME_EXPLAIN') ; ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_MAX_FORM_SUBMISSION') ; ?>
                            </td>
                            <td>
                                <input type="text" name="max_form_submission" class="input-mini imini form-control" value="<?php echo $this->config->max_form_submission; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                &nbsp;<?php echo Text::_('JD_MAXIMUM_SUBMIT_PER_SESSION_EXPLAIN') ; ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <?php
                echo HTMLHelper::_($tabApiPrefix.'endTab');
                echo HTMLHelper::_($tabApiPrefix.'addTab', 'configuration', 'message-page', Text::_('JD_EMAIL_MESSAGES', true));
                ?>
					<table class="admintable adminform" style="width:100%;">
                        <tr>
                            <td class="key" width="20%">
                                <?php echo Text::_('JD_FROM_NAME'); ?>
                            </td>
                            <td width="50%">
                                <input type="text" name="from_name" class="input-large form-control ilarge" value="<?php echo $this->config->from_name; ?>" size="50" />
                            </td>
                            <td width="30%" class="field_explanation">
                                <?php echo Text::_('JD_FROM_NAME_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_FROM_EMAIL'); ?>
                            </td>
                            <td>
                                <input type="text" name="from_email" class="input-large form-control ilarge" value="<?php echo $this->config->from_email; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_FROM_EMAIL_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_REPLY_EMAIL'); ?>
                            </td>
                            <td>
                                <input type="text" name="reply_email" class="input-large form-control ilarge" value="<?php echo $this->config->reply_email; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_REPLY_EMAIL_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_NOTIFICATION_EMAILS'); ?>
                            </td>
                            <td>
                                <input type="text" name="notification_emails" class="input-large form-control ilarge" value="<?php echo $this->config->notification_emails; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_NOTIFICATION_EMAILS_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_LOG_EMAILS'); ?>
                            </td>
                            <td>
                                <?php echo $this->lists['log_emails'];?>
                            </td>
                            <td class="field_explanation">
                                <?php echo Text::_('JD_LOG_EMAIL_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_PAYPAL_REDIRECT_MESSAGE'); ?>
                            </td>
                            <td >
                                <?php echo $editor->display( 'paypal_redirect_message',  $this->config->paypal_redirect_message , '100%', '250', '75', '8') ; ?>
                            </td>
                            <td class="field_explanation">
                                
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_ADMIN_EMAIL_SUBJECT'); ?>
                            </td>
                            <td>
                                <input type="text" name="admin_email_subject" class="input-large form-control" value="<?php echo $this->config->admin_email_subject; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_ADMIN_EMAIL_BODY'); ?>
                            </td>
                            <td >
                                <?php echo $editor->display( 'admin_email_body',  $this->config->admin_email_body , '100%', '250', '75', '8' ); ?>
                                <div style="display:none;">
                                    <textarea id="admin_email_body_value"><?php echo  $this->config->admin_email_body; ?></textarea>
                                </div>
                                <button type="button"
                                        class="btn btn-warning btn-sm"
                                        onclick="showTemplateCompareModal('admin_email_body')">
                                    <?php echo Text::_('JD_RESET_TO_DEFAULT');?>
                                </button>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('Available Tags :[DONATION_DETAIL], [CAMPAIGN], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_USER_EMAIL_SUBJECT'); ?>
                            </td>
                            <td >
                                <input type="text" name="user_email_subject" class="input-large form-control" value="<?php echo $this->config->user_email_subject; ?>" size="50" />
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_USER_EMAIL_BODY'); ?>
                            </td>
                            <td >
                                <?php echo $editor->display( 'user_email_body',  $this->config->user_email_body , '100%', '250', '75', '8' ); ?>
                                <div style="display:none;">
                                    <textarea id="user_email_body_value"><?php echo  $this->config->user_email_body; ?></textarea>
                                </div>
                                <button type="button"
                                        class="btn btn-warning btn-sm"
                                        onclick="showTemplateCompareModal('user_email_body')">
                                    <?php echo Text::_('JD_RESET_TO_DEFAULT');?>
                                </button>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('Available Tags :[DONATION_DETAIL], [CAMPAIGN], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_USER_EMAIL_BODY_OFFLINE'); ?>
                            </td>
                            <td >
                                <?php echo $editor->display( 'user_email_body_offline',  $this->config->user_email_body_offline , '100%', '250', '75', '8' ); ?>
                                <div style="display:none;">
                                    <textarea id="user_email_body_offline_value"><?php echo  $this->config->user_email_body_offline; ?></textarea>
                                </div>
                                <button type="button"
                                        class="btn btn-warning btn-sm"
                                        onclick="showTemplateCompareModal('user_email_body_offline')">
                                    <?php echo Text::_('JD_RESET_TO_DEFAULT');?>
                                </button>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('Available Tags :[DONATION_DETAIL], [CAMPAIGN], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>>
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_USER_EMAIL_BODY_OFFLINE_RECEIVED'); ?>
                            </td>
                            <td >
                                <?php echo $editor->display( 'user_email_body_offline_received',  $this->config->user_email_body_offline_received , '100%', '250', '75', '8' ); ?>
                                <div style="display:none;">
                                    <textarea id="user_email_body_offline_received_value"><?php echo  $this->config->user_email_body_offline_received; ?></textarea>
                                </div>
                                <button type="button"
                                        class="btn btn-warning btn-sm"
                                        onclick="showTemplateCompareModal('user_email_body_offline_received')">
                                    <?php echo Text::_('JD_RESET_TO_DEFAULT');?>
                                </button>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('Available Tags :[DONATION_DETAIL], [CAMPAIGN], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_RECURRING_EMAIL_SUBJECT'); ?>
                            </td>
                            <td >
                                <input type="text" name="recurring_email_subject" class="input-large form-control" value="<?php echo $this->config->recurring_email_subject; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_RECURRING_EMAIL_BODY'); ?>
                            </td>
                            <td >
                                 <?php echo $editor->display( 'recurring_email_body',  $this->config->recurring_email_body , '100%', '250', '75', '8' ); ?>
                                 <div style="display:none;">
                                    <textarea id="recurring_email_body_value"><?php echo  $this->config->recurring_email_body; ?></textarea>
                                </div>
                                <button type="button"
                                        class="btn btn-warning btn-sm"
                                        onclick="showTemplateCompareModal('recurring_email_body')">
                                    <?php echo Text::_('JD_RESET_TO_DEFAULT');?>
                                </button>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('Available Tags :[DONATION_DETAIL], [CAMPAIGN], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_CANCEL_RECURRING_EMAIL_SUBJECT'); ?>
                            </td>
                            <td>
                                 <input type="text" name="cancel_recurring_email_subject" class="input-large form-control" value="<?php echo $this->config->cancel_recurring_email_subject; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_CANCEL_RECURRING_EMAIL_BODY'); ?>
                            </td>
                            <td >
                                 <?php echo $editor->display( 'cancel_recurring_email_body',  $this->config->cancel_recurring_email_body , '100%', '250', '75', '8' ); ?>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('Available Tags :[DONATION_DETAIL], [CAMPAIGN], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" >
                                <?php echo Text::_('JD_CANCEL_RECURRING_ADMIN_EMAIL_SUBJECT'); ?>
                            </td>
                            <td >
                                  <input type="text" name="cancel_recurring_admin_email_subject" class="input-large form-control" value="<?php echo $this->config->cancel_recurring_admin_email_subject; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_CANCEL_RECURRING_ADMIN_EMAIL_BODY'); ?>
                            </td>
                            <td>
                                 <?php echo $editor->display( 'cancel_recurring_admin_email_body',  $this->config->cancel_recurring_admin_email_body , '100%', '250', '75', '8' ); ?>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('Available Tags :[DONATION_DETAIL], [CAMPAIGN], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_DONATION_PAGE_MESSAGE'); ?>
                            </td>
                            <td>
                                  <?php echo $editor->display( 'donation_form_msg',  $this->config->donation_form_msg , '100%', '250', '75', '8' ); ?>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_DONATION_PAGE_MESSAGE_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_THANKYOU_MESSAGE'); ?>
                            </td>
                            <td>
                                  <?php echo $editor->display( 'thanks_message',  $this->config->thanks_message , '100%', '250', '75', '8' ); ?>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_THANKYOU_MESSAGE_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_THANKYOU_MESSAGE_OFFLINE'); ?>
                            </td>
                            <td>
                                  <?php echo $editor->display( 'thanks_message_offline',  $this->config->thanks_message_offline , '100%', '250', '75', '8' ); ?>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_THANKYOU_MESSAGE_OFFLINE_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_CANCEL_MESSAGE'); ?>
                            </td>
                            <td >
                                  <?php echo $editor->display( 'cancel_message',  $this->config->cancel_message , '100%', '250', '75', '8' ); ?>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_CANCEL_MESSAGE_EXPLAIN'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_HONOREE_EMAIL_SUBJECT'); ?>
                            </td>
                            <td>
                                  <input type="text" name="honoree_email_subject" class="input-large form-control" value="<?php echo $this->config->honoree_email_subject; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                                
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_HONOREE_EMAIL_BODY'); ?>
                            </td>
                            <td>
                                  <?php echo $editor->display( 'honoree_email_body',  $this->config->honoree_email_body , '100%', '250', '75', '8' ); ?>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('Available Tags :[DONATION_DETAIL], [CAMPAIGN], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [HONOREE_NAME], [DEDICATE_TYPE]'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_NEW_CAMPAIGN_EMAIL_SUBJECT'); ?>
                            </td>
                            <td>
                                  <input type="text" name="new_campaign_email_subject" class="input-large form-control" value="<?php echo $this->config->new_campaign_email_subject; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                               
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_NEW_CAMPAIGN_EMAIL_BODY'); ?>
                            </td>
                            <td>
                                <?php echo $editor->display( 'new_campaign_email_body',  $this->config->new_campaign_email_body , '100%', '250', '75', '8' ); ?>
                                <div style="display:none;">
                                    <textarea id="new_campaign_email_body_value"><?php echo  $this->config->new_campaign_email_body; ?></textarea>
                                </div>
                                <button type="button"
                                        class="btn btn-warning btn-sm"
                                        onclick="showTemplateCompareModal('new_campaign_email_body')">
                                    <?php echo Text::_('JD_RESET_TO_DEFAULT');?>
                                </button>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                               <?php echo Text::_('Available Tags :[CAMPAIGN_TITLE], [OWNER], [CAMPAIGN_LINK]'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_CAMPAIGN_OWNER_NOTIFICATION'); ?>
                            </td>
                            <td >
                                <input type="text" name="campaign_owner_notification" class="input-large form-control" value="<?php echo $this->config->campaign_owner_notification; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                               
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_CAMPAIGN_OWNER_NOTIFICATION_BODY'); ?>
                            </td>
                            <td>
                                <?php echo $editor->display( 'campaign_owner_notification_body',  $this->config->campaign_owner_notification_body , '100%', '250', '75', '8' ); ?>
                                <div style="display:none;">
                                    <textarea id="campaign_owner_notification_body_value"><?php echo  $this->config->campaign_owner_notification_body; ?></textarea>
                                </div>
                                <button type="button"
                                        class="btn btn-warning btn-sm"
                                        onclick="showTemplateCompareModal('campaign_owner_notification_body')">
                                    <?php echo Text::_('JD_RESET_TO_DEFAULT');?>
                                </button>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                               <?php echo Text::_('Available Tags :[CAMPAIGN_TITLE], [OWNER], [CAMPAIGN_LINK]'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_PAYMENT_REQUEST_SUBJECT'); ?>
                            </td>
                            <td>
                                 <input type="text" name="payment_request_sbj" class="input-large form-control" value="<?php echo $this->config->payment_request_sbj; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                              
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_PAYMENT_REQUEST_BODY'); ?>
                            </td>
                            <td>
                                 <?php echo $editor->display( 'payment_request_body',  $this->config->payment_request_body , '100%', '250', '75', '8' ); ?>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                               <?php echo Text::_('Available Tags :[PAYMENT_LINK], [FIRST_NAME], [LAST_NAME], [AMOUNT]'); ?>
                            </td>
                        </tr>
						<tr>
                            <td class="key">
                                <?php echo Text::_('JD_SHARE_CAMPAIGN_SUBJECT'); ?>
                            </td>
                            <td >
                                 <input type="text" name="share_campaign_sbj" class="input-large form-control" value="<?php echo $this->config->share_campaign_sbj; ?>" size="50" />
                            </td>
                            <td class="field_explanation">
                              
                            </td>
                        </tr>
						<tr>
                            <td class="key" style="vertical-align:top;padding-top:10px;">
                                <?php echo Text::_('JD_SHARE_CAMPAIGN_BODY'); ?>
                            </td>
                            <td>
                                 <?php echo $editor->display( 'share_campaign_body',  $this->config->share_campaign_body , '100%', '250', '75', '8' ); ?>
                            </td>
                            <td class="field_explanation" style="vertical-align:top;padding-top:10px;">
                               <?php echo Text::_('Available Tags :[CAMPAIGN_TITLE], [CAMPAIGN_LINK], [CAMPAIGN_QR], [MY_NAME], [MY_EMAIL], [FRIEND_NAME], [FRIEND_EMAIL], [CAMPAIGN_GOAL]'); ?>
                            </td>
                        </tr>
					</table>
                <?php
                echo HTMLHelper::_($tabApiPrefix.'endTab');
                ?>
                    <?php
                    if ($translatable)
                    {
                        echo HTMLHelper::_($tabApiPrefix.'addTab', 'configuration', 'translation-page', Text::_('JD_EMAIL_MESSAGES_TRANSLATION', true));
                        ?>
                        <div class="tab-pane" id="translation-page">
                            <div class="tab-content">
                                <?php
                                $i = 0;
                                echo HTMLHelper::_($tabApiPrefix.'startTabSet', 'configuration-languages', array('active' => 'translation-page-0'));
                                foreach ($languages as $language)
                                {
                                    $sef = $language->sef;
                                    echo HTMLHelper::_($tabApiPrefix.'addTab', 'configuration-languages', 'translation-page-'.$i, $language->title . ' <img src="' . Uri::root() . 'media/com_jdonation/flags/' . $sef . '.png" />');
                                    ?>
                                    <div class="tab-pane<?php echo $i == 0 ? ' active' : ''; ?>">
                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                    <?php echo Text::_('JD_AMOUNTS_EXPLANTION'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                    <textarea name="amounts_explanation_<?php echo $sef; ?>" cols="40" rows="5" class="form-control"><?php echo $this->config->{'amounts_explanation_'.$sef};?></textarea>
                                            </div>
                                        </div>
                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                    <?php echo Text::_('JD_PAYPAL_REDIRECT_MESSAGE'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                    <?php echo $editor->display( 'paypal_redirect_message_'.$sef,  $this->config->{'paypal_redirect_message_'.$sef} , '100%', '250', '75', '10' ) ; ?>
                                            </div>
                                        </div>
                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo  Text::_('JD_ADMIN_EMAIL_SUBJECT'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <input class="input-xlarge form-control ilarge" type="text" name="admin_email_subject_<?php echo $sef; ?>" id="admin_email_subject_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->config->{'admin_email_subject_'.$sef}; ?>" />
                                            </div>
                                        </div>
                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_ADMIN_EMAIL_BODY'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <?php echo $editor->display( 'admin_email_body_'.$sef,  $this->config->{'admin_email_body_'.$sef} , '100%', '250', '75', '10' ) ; ?>
                                            </div>
                                        </div>

                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo  Text::_('JD_USER_EMAIL_SUBJECT'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <input class="input-xlarge form-control ilarge" type="text" name="user_email_subject_<?php echo $sef; ?>" id="user_email_subject_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->config->{'user_email_subject_'.$sef}; ?>" />
                                            </div>
                                        </div>
                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_USER_EMAIL_BODY'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <?php echo $editor->display( 'user_email_body_'.$sef,  $this->config->{'user_email_body_'.$sef} , '100%', '250', '75', '10' ) ; ?>
                                            </div>
                                        </div>

                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_USER_EMAIL_BODY_OFFLINE'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <?php echo $editor->display( 'user_email_body_offline_'.$sef,  $this->config->{'user_email_body_offline_'.$sef} , '100%', '250', '75', '10' ) ; ?>
                                            </div>
                                        </div>

										<div class="<?php echo $controlGroupClass; ?>">
											<label class="<?php echo $controlLabelClass; ?>">
												<?php echo Text::_('JD_USER_EMAIL_BODY_OFFLINE_RECEIVED'); ?>
												<br />
												<small><?php echo Text::_('Available Tags :[DONATION_DETAIL], [CAMPAIGN], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>></small>
											</label>
											<div class="<?php echo $controlsClass; ?>">
												<?php echo $editor->display( 'user_email_body_offline_received_'.$sef,  $this->config->{'user_email_body_offline_received_'.$sef} , '100%', '250', '75', '8' ); ?>
											</div>
										</div>

                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_RECURRING_PAYMENT_EMAIL_SUBJECT'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <input type="text" name="recurring_email_subject_<?php echo $sef; ?>" class="input-large form-control ilarge" value="<?php echo $this->config->{'recurring_email_subject_'.$sef}; ?>" size="50" />
                                            </div>
                                        </div>
                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_RECURRING_PAYMENT_EMAIL_BODY'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <?php echo $editor->display( 'recurring_email_body_'.$sef,  $this->config->{'recurring_email_body_'.$sef} , '100%', '250', '75', '8' ); ?>
                                            </div>
                                        </div>

                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_DONATION_PAGE_MESSAGE'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <?php echo $editor->display( 'donation_form_msg_'.$sef,  $this->config->{'donation_form_msg_'.$sef} , '100%', '250', '75', '10' ) ; ?>
                                            </div>
                                        </div>

                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_THANKYOU_MESSAGE'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <?php echo $editor->display( 'thanks_message_'.$sef,  $this->config->{'thanks_message_'.$sef} , '100%', '250', '75', '10' ) ; ?>
                                            </div>
                                        </div>

                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_THANKYOU_MESSAGE_OFFLINE'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <?php echo $editor->display( 'thanks_message_offline_'.$sef,  $this->config->{'thanks_message_offline_'.$sef} , '100%', '250', '75', '10' ) ; ?>
                                            </div>
                                        </div>
                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_CANCEL_MESSAGE'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <?php echo $editor->display( 'cancel_message_'.$sef,  $this->config->{'cancel_message_'.$sef} , '100%', '250', '75', '10' ) ; ?>
                                            </div>
                                        </div>
                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_HONOREE_EMAIL_SUBJECT'); ?>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <input type="text" name="honoree_email_subject_<?php echo $sef; ?>" class="input-large form-control ilarge" value="<?php echo $this->config->{'honoree_email_subject_'.$sef}; ?>" size="50" />
                                            </div>
                                        </div>
                                        <div class="<?php echo $controlGroupClass; ?>">
                                            <label class="<?php echo $controlLabelClass; ?>">
                                                <?php echo Text::_('JD_HONOREE_EMAIL_BODY'); ?>
                                                <br />
                                                <small><?php echo Text::_('Available Tags :[DONATION_DETAIL], [CAMPAIGN], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [HONOREE_NAME], [DEDICATE_TYPE]'); ?></small>
                                            </label>
                                            <div class="<?php echo $controlsClass; ?>">
                                                <?php echo $editor->display( 'honoree_email_body_'.$sef,  $this->config->{'honoree_email_body_'.$sef} , '100%', '250', '75', '8' ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    echo HTMLHelper::_($tabApiPrefix.'endTab');
                                    $i++;
                                }
                                echo HTMLHelper::_($tabApiPrefix.'endTabSet');
                                ?>
                            </div>
                        </div>
                        <?php
                        echo HTMLHelper::_($tabApiPrefix.'endTab');
                    }
                    echo HTMLHelper::_($tabApiPrefix.'addTab', 'configuration', 'donation-receipt-layout', Text::_('JD_DONATION_RECEIPT_LAYOUT', true));
                    ?>
                    <div class="tab-pane" id="donation-receipt-layout">
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_ACTIVATE_DONATION_RECEIPT'); ?>
                                <br />
                                <small><?php echo Text::_('JD_ACTIVATE_DONATION_RECEIPT_EXPLAIN'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <?php echo $this->lists['activate_donation_receipt_feature']; ?>
                            </div>
                        </div>
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_GENERATE_INVOICE_FOR_PAID_DONATION_ONLY'); ?>
                                <br />
                                <small><?php echo Text::_('JD_GENERATE_INVOICE_FOR_PAID_DONATION_ONLY_EXPLAIN'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <?php echo $this->lists['generated_invoice_for_paid_donation_only']; ?>
                            </div>
                        </div>
						<div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_MAKE_INVOICE_READONLY'); ?>
                                <br />
                                <small><?php echo Text::_('JD_MAKE_INVOICE_READONLY_EXPLAIN'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <?php echo $this->lists['invoice_readonly']; ?>
                            </div>
                        </div>
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_SEND_RECEIPT_VIA_EMAIL'); ?>
                                <br />
                                <small><?php echo Text::_('JD_SEND_RECEIPT_VIA_EMAIL_EXPLAIN'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <?php echo $this->lists['send_receipt_via_email']; ?>
                            </div>
                        </div>
						<div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_SEND_RECEIPT_TO_ADMINISTRATOR'); ?>
                                <br />
                                <small><?php echo Text::_('JD_SEND_RECEIPT_TO_ADMINISTRATOR_EXPLAIN'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <?php echo $this->lists['send_receipt_to_admin']; ?>
                            </div>
                        </div>
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_INVOICE_START_NUMBER'); ?>
                                <br />
                                <small><?php echo Text::_('JD_INVOICE_START_NUMBER_EXPLAIN'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <input type="text" name="invoice_start_number" class="input-mini form-control" value="<?php echo $config->invoice_start_number ? $config->invoice_start_number : 1; ?>" size="10" />
                            </div>
                        </div>
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_RESET_INVOICE_NUMBER_EVERY_YEAR'); ?>
                                <br />
                                <small><?php echo Text::_('JD_RESET_INVOICE_NUMBER_EVERY_YEAR_EXPLAIN'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <?php echo $this->lists['reset_invoice_number']; ?>
                            </div>
                        </div>
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_INVOICE_PREFIX'); ?>
                                <br />
                                <small><?php echo Text::_('JD_INVOICE_PREFIX_EXPLAIN'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <input type="text" name="invoice_prefix" class="input-mini form-control" value="<?php echo $config->invoice_prefix; ?>" size="10" />
                            </div>
                        </div>
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_INVOICE_NUMBER_LENGTH'); ?>
                                <br />
                                <small><?php echo Text::_('JD_INVOICE_NUMBER_LENGTH_EXPLAIN'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <input type="text" name="invoice_number_length" class="input-mini form-control" value="<?php echo $config->invoice_number_length ? $config->invoice_number_length : 5; ?>" size="10" />
                            </div>
                        </div>
						<div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_PDF_FONT'); ?>
                                <br />
                                <small><?php echo Text::_('JD_PDF_FONT_EXPLAIN'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <?php echo $this->lists['pdf_font']; ?>
                            </div>
                        </div>
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_DONATION_RECEIPT_LAYOUT'); ?>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <?php echo $editor->display( 'donation_receipt_layout',  $this->config->donation_receipt_layout , '100%', '550', '75', '8' );?>
                            </div>
                        </div>
                        <?php
                        foreach ($languages as $language)
                        {
                            $sef = $language->sef;
                            ?>
                            <div class="<?php echo $controlGroupClass; ?>">
                                <label class="<?php echo $controlLabelClass; ?>">
                                    <?php echo Text::_('JD_DONATION_RECEIPT_LAYOUT'); ?> <img src="<?php echo  Uri::root() . 'media/com_jdonation/flags/' . $sef . '.png'; ?>" />
                                </label>
                                <div class="<?php echo $controlsClass; ?>">
                                    <?php
                                    $donation_receipt_layout = $config->{'donation_receipt_layout_'.$sef};
                                    echo $editor->display( 'donation_receipt_layout_'.$sef,  $donation_receipt_layout , '100%', '550', '75', '8' );
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
					<?php
                    echo HTMLHelper::_($tabApiPrefix.'endTab');
					if($showCustomCss == 1){
                        echo HTMLHelper::_($tabApiPrefix.'addTab', 'configuration', 'donation-custom-css', Text::_('JD_CUSTOM_CSS', true));
                        ?>
                        <div class="tab-pane" id="donation-custom-css">
                            <table  width="100%">
                                <tr>
                                    <td>
                                        <?php
                                        $customCss = '';
                                        if (file_exists(JPATH_ROOT.'/media/com_jdonation/assets/css/custom.css'))
                                        {
                                            $customCss = file_get_contents(JPATH_ROOT.'/media/com_jdonation/assets/css/custom.css');
                                        }
                                        echo Editor::getInstance($editorPlugin)->display('custom_css', $customCss, '100%', '550', '75', '8', false, null, null, null, array('syntax' => 'css'));
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php
                        echo HTMLHelper::_($tabApiPrefix.'endTab');
					}
                    echo HTMLHelper::_($tabApiPrefix.'addTab', 'configuration', 'donation-privacy', Text::_('JD_PRIVACY_POLICY'));
                    ?>
                    <div class="tab-pane" id="donation-privacy">
                        <table class="admintable adminform" style="width:100%;">
                            <tr>
                                <td class="key" width="15%">
                                    <?php echo Text::_('JD_SHOW_PRIVACY_POLICY'); ?>
                                </td>
                                <td width="25%">
                                    <?php echo $this->lists['show_privacy']; ?>
                                </td>
                                <td width="60%">
                                    <?php echo Text::_('JD_SHOW_PRIVACY_POLICY_EXPLAIN'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="key" width="15%">
                                    <?php echo Text::_('JD_PRIVACY_ARTICLE'); ?>
                                </td>
                                <td width="25%">
                                    <?php echo DonationHelper::getArticleInput($this->config->privacy_policy_article_id, 'privacy_policy_article_id'); ?>
                                </td>
                                <td width="60%">
                                    <?php echo Text::_('JD_PRIVACY_ARTICLE_EXPLAIN'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="key" width="15%">
                                    <?php echo Text::_('JD_STORE_IP_ADDRESS'); ?>
                                </td>
                                <td width="25%">
                                    <?php echo $this->lists['store_ip_address']; ?>
                                </td>
                                <td width="60%">
                                    <?php echo Text::_('JD_STORE_IP_ADDRESS_EXPLAIN'); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php
                    echo HTMLHelper::_($tabApiPrefix.'endTab');
                    if(DonationHelper::isMailchimpPluginEnabled()){
                        echo HTMLHelper::_($tabApiPrefix.'addTab', 'configuration', 'mailchimp-list', Text::_('Mailchimp'));
                        ?>
                        <div class="tab-pane" id="mailchimp-list">
                            <table  width="100%">
                                <tr>
                                    <td  class="key" width="15%">
                                        <?php echo Text::_('JD_MAILCHIMP_LIST'); ?>
                                    </td>
                                    <td width="30%">
                                        <?php
                                        echo $this->lists['mailchimp_list'];
                                        ?>
                                    </td>
                                    <td width="55%">
                                        <?php echo Text::_('JD_MAILCHIMP_LIST_EXPLAIN'); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php
                        echo HTMLHelper::_($tabApiPrefix.'endTab');
                    }
                    echo HTMLHelper::_($tabApiPrefix.'addTab', 'configuration', 'donation-download-id', Text::_('Download ID'));
					?>
					<div class="tab-pane" id="donation-download-id">
						<table  width="100%">
							<tr>
                                <td  class="key" width="15%">
                                    <?php echo Text::_('JD_DOWNLOAD_ID'); ?>
                                </td>
                                <td width="30%">
                                    <input class="input-large form-control" type="text" name="download_id" id="download_id" value="<?php echo $this->config->{'download_id'}; ?>" />
                                </td>
                                <td width="55%">
                                    <?php echo Text::_('JD_DOWNLOAD_EXPLAIN'); ?>
                                </td>
                            </tr>
						</table>
					</div>
                    <?php
                    echo HTMLHelper::_($tabApiPrefix.'endTab');
            echo HTMLHelper::_($tabApiPrefix.'endTabSet');
            ?>
        </div>
    </div>
	<div class="clearfix"></div>
	<input type="hidden" name="task" value="" />	
</form>

<div class="modal fade" id="templateCompareModal" tabindex="-1" role="dialog" aria-labelledby="templateCompareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="templateCompareModalLabel">Compare Email Template</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#templateCompareModal').modal('hide');">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="templateCompareBody">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="$('#templateCompareModal').modal('hide');">Close</button>
        <button type="button" class="btn btn-success" id="btnApplyDefaultTemplate">Apply Default</button>
      </div>
    </div>
  </div>
</div>

<?php
$query = $db->getQuery(true)
    ->select($db->quoteName(['email_key', 'content']))
    ->from($db->quoteName('#__jd_email_templates'));
$db->setQuery($query);
$templates = $db->loadAssocList('email_key');

$defaultTemplates = [];
foreach ($templates as $key => $row) {
    $defaultTemplates[$key] = $row['content'];
}
?>
<!-- 2. Truyền sang JS -->
<script>
var defaultTemplates = <?php echo json_encode($defaultTemplates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
function showTemplateCompareModal(templateKey) {
    var current = document.querySelector('#'+templateKey+'_value').value;
    var def = defaultTemplates[templateKey];
    var html = `
      <div class="row">
        <div class="col-md-6">
          <h6>Current Template</h6>
          <pre style="background:#f8f9fa; border:1px solid #eee; padding:10px; height:300px; overflow:auto;">${current}</pre>
        </div>
        <div class="col-md-6">
          <h6>Default Template</h6>
          <pre style="background:#f1f8e9; border:1px solid #eee; padding:10px; height:300px; overflow:auto;">${def}</pre>
        </div>
      </div>
    `;
    document.getElementById('templateCompareBody').innerHTML = html;
    $('#templateCompareModal').modal('show');

    document.getElementById('btnApplyDefaultTemplate').onclick = function() {
        setEditorContent(templateKey, def);
        $('#templateCompareModal').modal('hide');
    }

}

function setEditorContent(templateKey, newContent) {
    if (typeof tinymce !== 'undefined' && tinymce.get(templateKey)) {
        tinymce.get(templateKey).setContent(newContent);
    } else if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[templateKey]) {
        CKEDITOR.instances[templateKey].setData(newContent);
    } else if (typeof jce !== 'undefined' && jce.getEditor) {
        var editor = jce.getEditor(templateKey);
        if (editor) editor.setContent(newContent);
    } else {
        // fallback cho textarea thường
        document.querySelector('#'+templateKey).value = newContent;
    }
}


function escapeHtml(text) {
    var map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>
