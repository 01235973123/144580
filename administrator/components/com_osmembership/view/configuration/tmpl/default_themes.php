<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 *
 * @var MPFConfig $config
 */
?>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('OSM_THEME_SETTINGS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('twitter_bootstrap_version', Text::_('OSM_TWITTER_BOOTSTRAP_VERSION'), Text::_('OSM_TWITTER_BOOTSTRAP_VERSION_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['twitter_bootstrap_version'];?>
		</div>
	</div>
    <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['twitter_bootstrap_version' => ['2', '5']]); ?>'>
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('load_twitter_bootstrap_in_frontend', Text::_('OSM_LOAD_BOOTSTRAP_CSS_IN_FRONTEND'), Text::_('OSM_LOAD_BOOTSTRAP_CSS_IN_FRONTEND_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('load_twitter_bootstrap_in_frontend', $config->get('load_twitter_bootstrap_in_frontend', '0')); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_form_layout', Text::_('OSM_SUBSCRIPTION_FORM_LAYOUT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['subscription_form_layout']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('form_format', Text::_('OSM_FORM_FORMAT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['form_format']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('number_fields_per_row', Text::_('OSM_NUMBER_FIELDS_PER_ROW'), Text::_('OSM_NUMBER_FIELDS_PER_ROW_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['number_fields_per_row']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('show_login_box_on_subscribe_page', Text::_('OSM_SHOW_LOGIN_BOX'), Text::_('OSM_SHOW_LOGIN_BOX')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('show_login_box_on_subscribe_page', $config->show_login_box_on_subscribe_page); ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['show_login_box_on_subscribe_page' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('show_extra_login_buttons', Text::_('OSM_SHOW_EXTRA_LOGIN_BUTTONS'), Text::_('OSM_SHOW_EXTRA_LOGIN_BUTTONS_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('show_extra_login_buttons', $config->show_extra_login_buttons); ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['show_login_box_on_subscribe_page' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('show_forgot_username_password', Text::_('OSM_SHOW_FORGOT_USERNAME_PASSWORD'), Text::_('OSM_SHOW_FORGOT_USERNAME_PASSWORD_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('show_forgot_username_password', $config->show_forgot_username_password); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('hide_active_plans', Text::_('OSM_HIDE_ACTIVE_PLANS'), Text::_('OSM_HIDE_ACTIVE_PLANS_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('hide_active_plans', $config->get('hide_active_plans', 0)); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('setup_price_including_tax', Text::_('OSM_SETUP_PRICE_INCLUDING_TAX'), Text::_('OSM_SETUP_PRICE_INCLUDING_TAX_EXPLAIN')); ?>
		</div>
		<div class="controls">
            <?php echo OSMembershipHelperHtml::getBooleanInput('setup_price_including_tax', $config->get('setup_price_including_tax', 0)); ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['setup_price_including_tax' => ['0']]); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('show_price_including_tax', Text::_('OSM_SHOW_PRICE_INCLUDING_TAX'), ''); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('show_price_including_tax', $config->show_price_including_tax); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('hide_details_button', Text::_('OSM_HIDE_DETAILS_BUTTON'), Text::_('OSM_HIDE_DETAILS_BUTTON_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('hide_details_button', $config->hide_details_button); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('show_upgrade_button', Text::_('OSM_SHOW_UPGRADE_BUTTON'), Text::_('OSM_SHOW_UPGRADE_BUTTON_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('show_upgrade_button', $config->get('show_upgrade_button', 1)); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('hide_signup_button_if_upgrade_available', Text::_('OSM_HIDE_SIGN_UP_IF_UPGRADE_AVAILABLE'), Text::_('OSM_HIDE_SIGN_UP_IF_UPGRADE_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('hide_signup_button_if_upgrade_available', $config->hide_signup_button_if_upgrade_available); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('display_recurring_payment_amounts', Text::_('OSM_DISPLAY_RECURRING_PAYMENT_AMOUNTS'), Text::_('OSM_DISPLAY_RECURRING_PAYMENT_AMOUNTS_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('display_recurring_payment_amounts', $config->get('display_recurring_payment_amounts', 1)); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('show_renew_options_on_plan_details', Text::_('OSM_SHOW_RENEW_OPTION_ON_PLAN_DETAIL'), Text::_('OSM_SHOW_RENEW_OPTION_ON_PLAN_DETAIL_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('show_renew_options_on_plan_details', $config->show_renew_options_on_plan_details); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('show_upgrade_options_on_plan_details', Text::_('OSM_SHOW_UPGRADE_OPTION_ON_PLAN_DETAIL'), Text::_('OSM_SHOW_UPGRADE_OPTION_ON_PLAN_DETAIL_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('show_upgrade_options_on_plan_details', $config->show_upgrade_options_on_plan_details); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('date_format', Text::_('OSM_DATE_FORMAT'), ''); ?>
		</div>
		<div class="controls">
			<input type="text" name="date_format" class="form-control" value="<?php echo $config->date_format; ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('date_field_format', Text::_('OSM_DATE_FIELD_FORMAT'), Text::_('OSM_DATE_FIELD_FORMAT_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['date_field_format']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('currency_code', Text::_('OSM_CURRENCY')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['currency_code']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('currency_symbol', Text::_('OSM_CURRENCY_SYMBOL'), ''); ?>
		</div>
		<div class="controls">
			<input type="text" name="currency_symbol" class="form-control" value="<?php echo $config->currency_symbol; ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('decimals', Text::_('OSM_DECIMALS'), Text::_('OSM_DECIMALS_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="number" name="decimals" class="form-control" value="<?php echo $config->get('decimals', 2); ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('dec_point', Text::_('OSM_DECIMAL_POINT'), Text::_('OSM_DECIMAL_POINT_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="dec_point" class="form-control" value="<?php echo $config->get('dec_point', '.'); ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('thousands_sep', Text::_('OSM_THOUSANDS_SEP'), Text::_('OSM_THOUSANDS_SEP_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="thousands_sep" class="form-control" value="<?php echo $config->get('thousands_sep', ','); ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('currency_position', Text::_('OSM_CURRENCY_POSITION'), ''); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['currency_position']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('number_columns', Text::_('OSM_NUMBER_COLUMNS_IN_COLUMNS_LAYOUT'), Text::_('OSM_NUMBER_COLUMNS_IN_COLUMNS_LAYOUT_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="number" name="number_columns" class="form-control" value="<?php echo $config->number_columns ?: 3 ; ?>" size="10" />
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('max_errors_per_field', Text::_('OSM_MAX_ERRORS_PER_FIELD'), Text::_('OSM_MAX_ERRORS_PER_FIELD_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <input type="number" min="0" step="1" name="max_errors_per_field" class="form-control" value="<?php echo (int) $config->max_errors_per_field ; ?>" size="10" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('display_field_description', Text::_('OSM_DISPLAY_FIELD_DESCRIPTION'), Text::_('OSM_DISPLAY_FIELD_DESCRIPTION_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $this->lists['display_field_description']; ?>
        </div>
    </div>
</fieldset>
