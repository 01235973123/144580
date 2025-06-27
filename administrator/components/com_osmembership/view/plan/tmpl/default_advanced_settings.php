<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<fieldset class="form-horizontal options-form">
	<legend class="adminform"><?php echo Text::_('OSM_GROUP_MEMBERSHIP'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('number_members_type', Text::_('OSM_NUMBER_MEMBER_TYPES'), Text::_('OSM_NUMBER_MEMBER_TYPES_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['number_members_type']; ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['number_members_type' => '0']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('number_group_members', Text::_('PLG_GRM_MAX_NUMBER_MEMBERS'), Text::_('PLG_GRM_MAX_NUMBER_MEMBERS_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="number" class="form-control input-small" name="number_group_members" id="number_group_members" value="<?php echo $this->item->number_group_members; ?>" />
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['number_members_type' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('number_members_field', Text::_('OSM_NUMBER_MEMBERS_FIELD'), Text::_('OSM_NUMBER_MEMBERS_FIELD_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['number_members_field']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend class="adminform"><?php echo Text::_('OSM_SUBSCRIPTION_FORM_SETTINGS'); ?></legend>
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
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend class="adminform"><?php echo Text::_('OSM_ADVANCED_SETTINGS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('setup_fee', Text::_('OSM_SETUP_FEE'), Text::_('OSM_SETUP_FEE_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="number" class="form-control input-small" name="setup_fee" id="setup_fee" value="<?php echo $this->item->setup_fee; ?>" step="0.01" />
		</div>
	</div>
	<?php
		if ($this->item->id && !$this->item->recurring_subscription)
		{
		?>
			<div class="control-group">
				<div class="control-label">
					<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_start_date_option', Text::_('OSM_SUBSCRIPTION_START_DATE_OPTION'), Text::_('OSM_SUBSCRIPTION_START_DATE_OPTION_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['subscription_start_date_option'];?>
				</div>
			</div>
			<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['subscription_start_date_option' => '1']); ?>'>
				<div class="control-label">
					<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_start_date', Text::_('OSM_PLAN_SUBSCRIPTION_START_DATE'), Text::_('OSM_PLAN_SUBSCRIPTION_START_DATE_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo HTMLHelper::_('calendar', $this->planParams->get('subscription_start_date'), 'subscription_start_date', 'subscription_start_date', '%Y-%m-%d %H:%M:%S') ; ?>
				</div>
			</div>
			<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['subscription_start_date_option' => '2']); ?>'>
				<div class="control-label">
					<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_start_date_field', Text::_('OSM_SUBSCRIPTION_START_DATE_FIELD'), Text::_('OSM_SUBSCRIPTION_START_DATE_FIELD_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['subscription_start_date_field'];?>
				</div>
			</div>
		<?php
		}
	?>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('free_plan_subscription_status', Text::_('OSM_FREE_PLAN_STATUS'), Text::_('OSM_FREE_PLAN_STATUS_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo $this->lists['free_plan_subscription_status'];?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('login_redirect_menu_id', Text::_('OSM_LOGIN_REDIRECT'), Text::_('OSM_LOGIN_REDIRECT_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['login_redirect_menu_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('payment_methods', Text::_('OSM_PAYMENT_METHODS'), Text::_('OSM_PAYMENT_METHODS_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['payment_methods'];?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('currency_code', Text::_('OSM_CURRENCY'), Text::_('OSM_CURRENCY_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['currency'];?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('currency_symbol', Text::_('OSM_CURRENCY_SYMBOL'), Text::_('OSM_CURRENCY_SYMBOL_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" class="form-control input-small" name="currency_symbol" id="currency_symbol" value="<?php echo $this->item->currency_symbol; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_SUBSCRIPTION_COMPLETE_URL'); ?>
		</div>
		<div class="controls">
			<input type="url" class="form-control" name="subscription_complete_url" value="<?php echo $this->item->subscription_complete_url; ?>" size="40" />
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo  Text::_('OSM_OFFLINE_PAYMENT_SUBSCRIPTION_COMPLETE_URL'); ?>
        </div>
        <div class="controls">
            <input type="url" class="form-control" name="offline_payment_subscription_complete_url" value="<?php echo $this->item->offline_payment_subscription_complete_url; ?>" size="40" />
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('notification_emails', Text::_('OSM_NOTIFICATION_EMAILS'), Text::_('OSM_NOTIFICATION_EMAILS_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" class="form-control" name="notification_emails" value="<?php echo $this->item->notification_emails; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('paypal_email', Text::_('OSM_PAYPAL_EMAIL'), Text::_('OSM_PAYPAL_EMAIL_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="email" class="form-control" name="paypal_email" value="<?php echo $this->item->paypal_email; ?>" />
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_PUBLISH_UP'); ?>
        </div>
        <div class="controls">
	        <?php echo HTMLHelper::_('calendar', $this->item->publish_up, 'publish_up', 'publish_up', $this->datePickerFormat . ' %H:%M:%S', ['class' => 'input-medium']); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_PUBLISH_DOWN'); ?>
        </div>
        <div class="controls">
	        <?php echo HTMLHelper::_('calendar', $this->item->publish_down, 'publish_down', 'publish_down', $this->datePickerFormat . ' %H:%M:%S', ['class' => 'input-medium']); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo OSMembershipHelperHtml::getFieldLabel('require_coupon', Text::_('OSM_REQUIRE_COUPON', Text::_('OSM_REQUIRE_COUPON_EXPLAIN'))) ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('require_coupon', $this->item->require_coupon); ?>
        </div>
    </div>
    <div class="control-group">
		<div class="control-label">
	        <?php echo OSMembershipHelperHtml::getFieldLabel('subscriptions_manage_user_id', Text::_('OSM_SUBSCRIPTIONS_MANAGE_USER'), Text::_('OSM_SUBSCRIPTIONS_MANAGE_USER_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getUserInput($this->item->subscriptions_manage_user_id, 'subscriptions_manage_user_id'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('created_by', Text::_('OSM_CREATED_BY')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getUserInput($this->item->created_by, 'created_by'); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_TERMS_AND_CONDITIONS_ARTICLE') ; ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getArticleInput($this->item->terms_and_conditions_article_id, 'terms_and_conditions_article_id'); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('conversion_tracking_code', Text::_('OSM_CONVERSION_TRACKING_CODE'), Text::_('OSM_CONVERSION_TRACKING_CODE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <textarea name="conversion_tracking_code" class="form-control" rows="8"><?php echo $this->item->conversion_tracking_code;?></textarea>
        </div>
    </div>
</fieldset>
