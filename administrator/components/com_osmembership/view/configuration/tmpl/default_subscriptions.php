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
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Layout variables
 *
 * @var MPFConfig $config
 */
?>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('OSM_SUBSCRIPTION_SETTINGS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('download_id', Text::_('OSM_DOWNLOAD_ID'), Text::_('OSM_DOWNLOAD_ID_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="download_id" class="form-control" value="<?php echo $config->download_id; ?>" size="60" />
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('editor', Text::_('OSM_EDITOR')); ?>
        </div>
        <div class="controls">
            <?php echo $this->lists['editor']; ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('enable_avatar', Text::_('OSM_ENABLE_AVATAR'), Text::_('OSM_ENABLE_AVATAR_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('enable_avatar', $config->enable_avatar); ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['enable_avatar' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('resize_avatar_method', Text::_('OSM_RESIZE_AVATAR_METHOD')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['resize_avatar_method']; ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['enable_avatar' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('avatar_width', Text::_('OSM_AVATAR_WIDTH')); ?>
		</div>
		<div class="controls">
			<input type="text" name="avatar_width" class="input-small form-control" value="<?php echo $this->config->avatar_width ?: 80; ?>" />
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['enable_avatar' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('avatar_width', Text::_('OSM_AVATAR_HEIGHT')); ?>
		</div>
		<div class="controls">
			<input type="text" name="avatar_height" class="input-small form-control" value="<?php echo $this->config->avatar_height ?: 80; ?>" />
		</div>
	</div>
    <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['enable_avatar' => '1']); ?>'>
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('avatar_max_file_size', Text::_('OSM_AVATAR_MAX_FILE_SIZE'), Text::_('OSM_AVATAR_MAX_FILE_SIZE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <input type="text" name="avatar_max_file_size" class="input-small form-control" value="<?php echo $this->config->avatar_max_file_size; ?>" /> MB
        </div>
    </div>
    <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['enable_avatar' => '1']); ?>'>
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('avatar_max_width', Text::_('OSM_AVATAR_MAX_WIDTH', Text::_('OSM_AVATAR_MAX_WIDTH_EXPLAIN'))); ?>
        </div>
        <div class="controls">
            <input type="text" name="avatar_max_width" class="input-small form-control" value="<?php echo $this->config->avatar_max_width; ?>" />
        </div>
    </div>
    <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['enable_avatar' => '1']); ?>'>
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('avatar_max_height', Text::_('OSM_AVATAR_MAX_HEIGHT', Text::_('OSM_AVATAR_MAX_HEIGHT_EXPLAIN'))); ?>
        </div>
        <div class="controls">
            <input type="text" name="avatar_max_height" class="input-small form-control" value="<?php echo $this->config->avatar_max_height; ?>" />
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('auto_login', Text::_('OSM_AUTO_LOGIN'), Text::_('OSM_AUTO_LOGIN_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('auto_login', $config->auto_login); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('synchronize_email', Text::_('OSM_SYNCHRONIZE_EMAIL'), Text::_('OSM_SYNCHRONIZE_EMAIL_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('synchronize_email', $config->get('synchronize_email', 0)); ?>
		</div>
	</div>
    <?php
		if (PluginHelper::isEnabled('osmembership', 'userprofile'))
		{
		?>
            <div class="control-group">
                <div class="control-label">
			        <?php echo OSMembershipHelperHtml::getFieldLabel('synchronize_profile_data_to_subscriptions', Text::_('OSM_SYNCHRONIZE_PROFILE_DATA_TO_SUBSCRIPTIONS'), Text::_('OSM_SYNCHRONIZE_PROFILE_DATA_TO_SUBSCRIPTIONS_EXPLAIN')); ?>
                </div>
                <div class="controls">
			        <?php echo OSMembershipHelperHtml::getBooleanInput('synchronize_profile_data_to_subscriptions', $config->get('synchronize_profile_data_to_subscriptions', 0)); ?>
                </div>
            </div>
        <?php
		}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('number_days_before_renewal', Text::_('OSM_ALLOW_RENEWAL'), Text::_('OSM_ALLOW_RENEWAL_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="number" name="number_days_before_renewal" class="input-small form-control" value="<?php echo (int) $this->config->number_days_before_renewal; ?>" size="10" />
			<?php echo Text::_('OSM_DAYS_BEFORE_SUBSCRIPTION_EXPIRED'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_renew_behavior', Text::_('OSM_SUBSCRIPTION_RENEW_BEHAVIOR'), Text::_('OSM_SUBSCRIPTION_RENEW_BEHAVIOR_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['subscription_renew_behavior']; ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('allow_upgrade_from_expired_subscriptions', Text::_('OSM_ALLOW_UPGRADE_FROM_EXPIRED_SUBSCRIPTIONS'), Text::_('OSM_ALLOW_UPGRADE_FROM_EXPIRED_SUBSCRIPTIONS_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('allow_upgrade_from_expired_subscriptions', $config->allow_upgrade_from_expired_subscriptions); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('enable_captcha', Text::_('OSM_ENABLE_CAPTCHA'), ''); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('enable_captcha', $config->enable_captcha); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('enable_coupon', Text::_('OSM_ENABLE_COUPON'), ''); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('enable_coupon', $config->enable_coupon); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('auto_generate_membership_id', Text::_('OSM_GENERATE_MEMBERSHIP_ID'), Text::_('OSM_GENERATE_MEMBERSHIP_ID_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('auto_generate_membership_id', $config->auto_generate_membership_id); ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['auto_generate_membership_id' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('membership_id_prefix', Text::_('OSM_MEMBERSHIP_ID_PREFIX'), Text::_('OSM_MEMBERSHIP_ID_PREFIX_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="membership_id_prefix" class="input-medium form-control" value="<?php echo $this->config->membership_id_prefix; ?>"/>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['auto_generate_membership_id' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('reset_membership_id', Text::_('OSM_RESET_MEMBERSHIP_ID'), Text::_('OSM_RESET_MEMBERSHIP_ID_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('reset_membership_id', $config->reset_membership_id); ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['auto_generate_membership_id' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('membership_id_start_number', Text::_('OSM_MEMBERSHIP_ID_START_NUMBER'), Text::_('OSM_MEMBERSHIP_ID_START_NUMBER_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="membership_id_start_number" class="form-control" value="<?php echo $config->membership_id_start_number ?: 1000; ?>" size="10" />
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['auto_generate_membership_id' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('membership_id_length', Text::_('OSM_MEMBERSHIP_ID_LENGTH'), Text::_('OSM_MEMBERSHIP_ID_LENGTH_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="membership_id_length" class="form-control" value="<?php echo $config->membership_id_length; ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('show_join_group_link', Text::_('OSM_SHOW_JOIN_GROUP_LINK'), Text::_('OSM_SHOW_JOIN_GROUP_LINK_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('show_join_group_link', $config->show_join_group_link); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('enable_select_existing_users', Text::_('OSM_ENABLE_SELECT_EXISTING_USER'), Text::_('OSM_ENABLE_SELECT_EXISTING_USER_EXPLAINS')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('enable_select_existing_users', $config->enable_select_existing_users); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('enable_subscription_payment', Text::_('OSM_ENABLE_SUBSCRIPTION_PAYMENT'), Text::_('OSM_ENABLE_SUBSCRIPTION_PAYMENT_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('enable_subscription_payment', $config->enable_subscription_payment); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('include_group_members_in_export', Text::_('OSM_INCLUDE_GROUP_MEMBERS_IN_EXPORT')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('include_group_members_in_export', $config->include_group_members_in_export); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('delete_subscriptions_when_account_deleted', Text::_('OSM_DELETE_SUBSCRIPTIONS_WHEN_ACCOUNT_DELETED'), Text::_('OSM_DELETE_SUBSCRIPTIONS_WHEN_ACCOUNT_DELETED_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('delete_subscriptions_when_account_deleted', $config->delete_subscriptions_when_account_deleted); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('force_select_plan', Text::_('OSM_FORCE_SELECT_PLAN'), Text::_('OSM_FORCE_SELECT_PLAN_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('force_select_plan', $config->force_select_plan); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('use_expired_date_as_start_date', Text::_('OSM_ALWAYS_USE_EXPIRED_DATE_AS_START_DATE_FOR_RENEWAL'), Text::_('OSM_ALWAYS_USE_EXPIRED_DATE_AS_START_DATE_FOR_RENEWAL_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('use_expired_date_as_start_date', $config->get('use_expired_date_as_start_date', 0)); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('enable_select_show_hide_members_list', Text::_('OSM_ENABLE_SELECT_SHOW_HIDE_ON_MEMBERS_LIST'), Text::_('OSM_ENABLE_SELECT_SHOW_HIDE_ON_MEMBERS_LIST_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('enable_select_show_hide_members_list', $config->get('enable_select_show_hide_members_list', 0)); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('enable_select_show_hide_members_list_on_signup', Text::_('OSM_ENABLE_SELECT_SHOW_HIDE_ON_MEMBERS_LIST_ON_SIGNUP'), Text::_('OSM_ENABLE_SELECT_SHOW_HIDE_ON_MEMBERS_LIST_ON_SIGNUP_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('enable_select_show_hide_members_list_on_signup', $config->get('enable_select_show_hide_members_list_on_signup', $config->get('enable_select_show_hide_members_list', 0))); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('enable_editing_recurring_payment_amounts', Text::_('OSM_ENABLE_EDITING_RECURRING_PAYMENT_AMOUNTS'), Text::_('OSM_ENABLE_EDITING_RECURRING_PAYMENT_AMOUNTS_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('enable_editing_recurring_payment_amounts', $config->get('enable_editing_recurring_payment_amounts', 0)); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('grace_period', Text::_('OSM_GRADE_PERIOD')); ?>
        </div>
        <div class="controls">
			<input type="number" min="0" name="grace_period" value="<?php echo $config->get('grace_period', 0); ?>" step="1" class="input-small form-control d-inline-block" /> <?php echo $this->lists['grace_period_unit']; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('export_exclude_status', Text::_('OSM_EXPORT_EXCLUDE_STATUS'), Text::_('OSM_EXPORT_EXCLUDE_STATUS_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo $this->lists['export_exclude_status']; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('enable_user_cancel_subscription', Text::_('OSM_ENABLE_CANCEL_SUBSCRIPTION'), Text::_('OSM_ENABLE_CANCEL_SUBSCRIPTION_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('enable_user_cancel_subscription', $config->get('enable_user_cancel_subscription', 1)); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('validate_backend_subscription_form_data', Text::_('OSM_VALIDATE_BACKEND_SUBSCRIPTION_FORM_DATA'), Text::_('OSM_VALIDATE_BACKEND_SUBSCRIPTION_FORM_DATA_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('validate_backend_subscription_form_data', $config->get('validate_backend_subscription_form_data', 1)); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('collect_ip_address', Text::_('OSM_COLLECT_IP_ADDRESS')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('collect_ip_address', $config->get('collect_ip_address', 1)); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('show_payment_method', Text::_('OSM_SHOW_PAYMENT_METHOD'), Text::_('OSM_SHOW_PAYMENT_METHOD_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('show_payment_method', $config->get('show_payment_method', 1)); ?>
		</div>
	</div>
</fieldset>
