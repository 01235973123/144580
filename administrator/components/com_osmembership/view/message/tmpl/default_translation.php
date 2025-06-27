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
use Joomla\CMS\Uri\Uri;

echo HTMLHelper::_( 'uitab.startTabSet', 'message-translation', ['active' => 'translation-page-' . $this->languages[0]->sef, 'recall' => true]);

$rootUri = Uri::root(true);

$keys = [
	'admin_email_subject',
	'admin_email_body',
	'user_email_subject',
	'user_email_body',
	'user_email_body_offline',
	'subscription_approved_email_subject',
	'subscription_approved_email_subject',
	'subscription_approved_email_body',
	'subscription_form_msg',
	'thanks_message',
	'thanks_message_offline',
	'cancel_message',
	'failure_message',
	'profile_update_email_subject',
	'profile_update_email_body',
	'subscription_renew_form_msg',
	'admin_renw_email_subject',
	'admin_renew_email_body',
	'user_renew_email_subject',
	'user_renew_email_body',
	'user_renew_email_body_offline',
	'renew_thanks_message',
	'renew_thanks_message_offline',
	'subscription_upgrade_form_msg',
	'admin_upgrade_email_subject',
	'admin_upgrade_email_body',
	'user_upgrade_email_subject',
	'user_upgrade_email_body',
	'user_upgrade_email_body_offline',
	'upgrade_thanks_message',
	'first_reminder_email_subject',
	'first_reminder_email_body',
	'second_reminder_email_subject',
	'second_reminder_email_body',
	'third_reminder_email_subject',
	'third_reminder_email_body',
	'new_group_member_email_subject',
	'new_group_member_email_body',
	'content_restricted_message',
	'recurring_subscription_cancel_message',
	'user_recurring_subscription_cancel_subject',
	'user_recurring_subscription_cancel_body',
	'subscription_payment_form_message',
	'subscription_payment_admin_email_subject',
	'subscription_payment_admin_email_body',
	'subscription_payment_user_email_subject',
	'subscription_payment_user_email_body',
	'subscription_payment_thanks_message',
	'request_payment_email_subject',
	'request_payment_email_body',
	'subscription_end_email_subject',
	'subscription_end_email_body',
];

foreach ($this->languages as $language)
{
	$sef = $language->sef;
	echo HTMLHelper::_( 'uitab.addTab', 'message-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/mod_languages/images/' . $language->image . '.gif" />');

	// Populate messages from default language to make it easier for translating data
	foreach ($keys as $key)
	{
		if (!isset($this->item->{$key . '_' . $sef}) || !trim($this->item->{$key . '_' . $sef}))
		{
			$this->item->{$key . '_' . $sef} = $this->item->{$key};
		}
	}
	?>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_ADMIN_EMAIL_SUBJECT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="admin_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'admin_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_ADMIN_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('admin_email_body_' . $sef, $this->item->{'admin_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('User Email Subject'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="user_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'user_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_USER_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('user_email_body_' . $sef, $this->item->{'user_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_USER_EMAIL_BODY_OFFLINE_PAYMENT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('user_email_body_offline_' . $sef, $this->item->{'user_email_body_offline_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_SUBSCRIPTION_APPROVED_EMAIL_SUBJECT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="subscription_approved_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'subscription_approved_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_SUBSCRIPTION_APPROVED_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong>Available Tags :[PAYMENT_DETAIL], [FORM_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('subscription_approved_email_body_' . $sef, $this->item->{'subscription_approved_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_form_msg_' . $sef, Text::_('OSM_SUBSCRIPTION_FORM_MESSAGE'), Text::_('OSM_SUBSCRIPTION_FORM_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('subscription_form_msg_' . $sef, $this->item->{'subscription_form_msg_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('thanks_message_' . $sef, Text::_('OSM_THANK_MESSAGE'), Text::_('OSM_THANK_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('thanks_message_' . $sef, $this->item->{'thanks_message_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('thanks_message_offline_' . $sef, Text::_('OSM_THANK_MESSAGE_OFFLINE'), Text::_('OSM_THANK_MESSAGE_OFFLINE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('thanks_message_offline_' . $sef, $this->item->{'thanks_message_offline_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('cancel_message_' . $sef, Text::_('OSM_PAYMENT_CANCEL_MESSAGE'), Text::_('OSM_PAYMENT_CANCEL_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('cancel_message_' . $sef, $this->item->{'cancel_message_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('failure_message_' . $sef, Text::_('OSM_PAYMENT_FAILURE_MESSAGE'), Text::_('OSM_PAYMENT_FAILURE_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('failure_message_' . $sef, $this->item->{'failure_message_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('profile_update_email_subject_' . $sef, Text::_('OSM_PROFILE_UPDATE_EMAIL_SUBJECT')); ?>
        </div>
        <div class="controls">
            <input type="text" name="profile_update_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'profile_update_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('profile_update_email_body_' . $sef, Text::_('OSM_PROFILE_UPDATE_EMAIL_BODY')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('profile_update_email_body_' . $sef, $this->item->{'profile_update_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_renew_form_msg_' . $sef, Text::_('OSM_SUBSCRIPTION_RENEW_FORM_MESSAGE'), Text::_('OSM_SUBSCRIPTION_RENEW_FORM_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('subscription_renew_form_msg_' . $sef, $this->item->{'subscription_renew_form_msg_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_RENEW_ADMIN_EMAIL_SUBJECT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="admin_renw_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'admin_renw_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_RENEW_ADMIN_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('admin_renew_email_body_' . $sef, $this->item->{'admin_renew_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_RENEW_USER_EMAIL_SUBJECT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="user_renew_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'user_renew_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_RENEW_USER_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('user_renew_email_body_' . $sef, $this->item->{'user_renew_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_RENEW_USER_EMAIL_BODY_OFFLINE'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('user_renew_email_body_offline_' . $sef, $this->item->{'user_renew_email_body_offline_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('renew_thanks_message_' . $sef, Text::_('OSM_RENEW_THANK_MESSAGE'), Text::_('OSM_RENEW_THANK_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('renew_thanks_message_' . $sef, $this->item->{'renew_thanks_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
        <div class="controls">
            <?php echo Text::_('OSM_RENEW_THANK_MESSAGE_EXPLAIN'); ?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('renew_thanks_message_offline_' . $sef, Text::_('OSM_RENEW_THANK_MESSAGE_OFFLINE'), Text::_('OSM_RENEW_THANK_MESSAGE_OFFLINE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('renew_thanks_message_offline_' . $sef, $this->item->{'renew_thanks_message_offline_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_upgrade_form_msg_' . $sef, Text::_('OSM_SUBSCRIPTION_UPGRADE_FORM_MESSAGE'), Text::_('OSM_SUBSCRIPTION_UPGRADE_FORM_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('subscription_upgrade_form_msg_' . $sef, $this->item->{'subscription_upgrade_form_msg_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_UPGRADE_ADMIN_EMAIL_SUBJECT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE], [TO_PLAN_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="admin_upgrade_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'admin_upgrade_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_UPGRADE_ADMIN_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('admin_upgrade_email_body_' . $sef, $this->item->{'admin_upgrade_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_UPGRADE_USER_EMAIL_SUBJECT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE], [TO_PLAN_TITLE]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="user_upgrade_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'user_upgrade_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_UPGRADE_USER_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('user_upgrade_email_body_' . $sef, $this->item->{'user_upgrade_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_UPGRADE_USER_EMAIL_BODY_OFFLINE'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('user_upgrade_email_body_offline_' . $sef, $this->item->{'user_upgrade_email_body_offline_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('upgrade_thanks_message_' . $sef, Text::_('OSM_UPGRADE_THANK_MESSAGE'), Text::_('OSM_UPGRADE_THANK_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('upgrade_thanks_message_' . $sef, $this->item->{'upgrade_thanks_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_FIRST_REMINDER_EMAIL_SUBJECT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE], [NUMBER_DAYS]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="first_reminder_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'first_reminder_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_FIRST_REMINDER_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [NUMBER_DAYS], [EXPIRE_DATE]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('first_reminder_email_body_' . $sef, $this->item->{'first_reminder_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_SECOND_REMINDER_EMAIL_SUBJECT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE], [NUMBER_DAYS]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="second_reminder_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'second_reminder_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_SECOND_REMINDER_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [NUMBER_DAYS], [EXPIRE_DATE]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('second_reminder_email_body_' . $sef, $this->item->{'second_reminder_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_THIRD_REMINDER_EMAIL_SUBJECT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE], [NUMBER_DAYS]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="third_reminder_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'third_reminder_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_THIRD_REMINDER_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [NUMBER_DAYS], [EXPIRE_DATE]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('third_reminder_email_body_' . $sef, $this->item->{'third_reminder_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('new_group_member_email_subject_' . $sef, Text::_('OSM_NEW_GROUP_MEMBER_EMAIL_SUBJECT'), Text::_('OSM_NEW_GROUP_MEMBER_EMAIL_SUBJECT_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <input type="text" name="new_group_member_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'new_group_member_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_NEW_GROUP_MEMBER_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('new_group_member_email_body_' . $sef, $this->item->{'new_group_member_email_body_' . $sef}, '100%', '250', '75', '8') ;?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('content_restricted_message_' . $sef, Text::_('OSM_CONTENT_RESTRICTED_MESSAGE'), Text::_('OSM_CONTENT_RESTRICTED_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('content_restricted_message_' . $sef, $this->item->{'content_restricted_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('recurring_subscription_cancel_message_' . $sef, Text::_('OSM_RECURRING_SUBSCRIPTION_CANCEL_MESSAGE'), Text::_('OSM_RECURRING_SUBSCRIPTION_CANCEL_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('recurring_subscription_cancel_message_' . $sef, $this->item->{'recurring_subscription_cancel_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('user_recurring_subscription_cancel_subject_' . $sef, Text::_('OSM_USER_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_SUBJECT'), Text::_('OSM_USER_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_SUBJECT_EXPLAIN')); ?>
            <?php echo Text::_('OSM_USER_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_SUBJECT'); ?>
        </div>
        <div class="controls">
            <input type="text" name="user_recurring_subscription_cancel_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'user_recurring_subscription_cancel_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('user_recurring_subscription_cancel_body_' . $sef, Text::_('OSM_USER_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_BODY'), Text::_('OSM_USER_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_BODY_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('user_recurring_subscription_cancel_body_' . $sef, $this->item->{'user_recurring_subscription_cancel_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_form_message_' . $sef, Text::_('OSM_SUBSCRIPTION_PAYMENT_FORM_MESSAGE'), Text::_('OSM_SUBSCRIPTION_PAYMENT_FORM_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('subscription_payment_form_message_' . $sef, $this->item->{'subscription_payment_form_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_admin_email_subject_' . $sef, Text::_('OSM_SUBSCRIPTION_PAYMENT_ADMIN_EMAIL_SUBJECT')); ?>
        </div>
        <div class="controls">
            <input type="text" name="subscription_payment_admin_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'subscription_payment_admin_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_admin_email_body_' . $sef, Text::_('OSM_SUBSCRIPTION_PAYMENT_ADMIN_EMAIL_BODY')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('subscription_payment_admin_email_body_' . $sef, $this->item->{'subscription_payment_admin_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_user_email_subject_' . $sef, Text::_('OSM_THANK_MESSAGE_OFFLINE'), Text::_('OSM_SUBSCRIPTION_PAYMENT_USER_EMAIL_SUBJECT')); ?>
        </div>
        <div class="controls">
            <input type="text" name="subscription_payment_user_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'subscription_payment_user_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_user_email_body_' . $sef, Text::_('OSM_SUBSCRIPTION_PAYMENT_USER_EMAIL_BODY')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('subscription_payment_user_email_body_' . $sef, $this->item->{'subscription_payment_user_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_thanks_message_' . $sef, Text::_('OSM_SUBSCRIPTION_PAYMENT_THANK_MESSAGE'), Text::_('OSM_SUBSCRIPTION_PAYMENT_THANK_MESSAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('subscription_payment_thanks_message_' . $sef, $this->item->{'subscription_payment_thanks_message_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('request_payment_email_subject_' . $sef, Text::_('OSM_REQUEST_PAYMENT_EMAIL_SUBJECT')); ?>
        </div>
        <div class="controls">
            <input type="text" name="request_payment_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'request_payment_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('request_payment_email_body_' . $sef, Text::_('OSM_REQUEST_PAYMENT_EMAIL_BODY')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('request_payment_email_body_' . $sef, $this->item->{'request_payment_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_SUBSCRIPTION_END_EMAIL_SUBJECT'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE], [NUMBER_DAYS]</strong>
            </p>
        </div>
        <div class="controls">
            <input type="text" name="subscription_end_email_subject_<?php echo $sef; ?>" class="form-control" value="<?php echo $this->item->{'subscription_end_email_subject_' . $sef}; ?>" size="40" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_SUBSCRIPTION_END_EMAIL_BODY'); ?>
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [NUMBER_DAYS], [EXPIRE_DATE]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('subscription_end_email_body_' . $sef, $this->item->{'subscription_end_email_body_' . $sef}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <?php
	foreach ($this->extraOfflinePlugins as $offlinePaymentPlugin)
	{
		$name   = $offlinePaymentPlugin->name;
		$title  = $offlinePaymentPlugin->title;
		$prefix = str_replace('os_offline', '', $name);
		?>
        <div class="control-group">
            <div class="control-label">
                <?php echo Text::_('OSM_USER_EMAIL_BODY_OFFLINE_PAYMENT'); ?>(<?php echo $title; ?>)
                <p class="osm-available-tags">
                    <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
                </p>
            </div>
            <div class="controls">
                <?php echo $editor->display('user_email_body_offline' . $prefix . '_' . $sef, $this->item->{'user_email_body_offline' . $prefix . '_' . $sef}, '100%', '250', '75', '8'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <?php echo OSMembershipHelperHtml::getFieldLabel('thanks_message_offline' . $prefix . '_' . $sef, Text::_('OSM_THANK_MESSAGE_OFFLINE') . '(' . $title . ')', Text::_('OSM_THANK_MESSAGE_OFFLINE_EXPLAIN')); ?>
            </div>
            <div class="controls">
                <?php echo $editor->display('thanks_message_offline' . $prefix . '_' . $sef, $this->item->{'thanks_message_offline' . $prefix . '_' . $sef}, '100%', '250', '75', '8'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <?php echo Text::_('OSM_RENEW_USER_EMAIL_BODY_OFFLINE'); ?>(<?php echo $title; ?>)
                <p class="osm-available-tags">
                    <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
                </p>
            </div>
            <div class="controls">
                <?php echo $editor->display('user_renew_email_body_offline' . $prefix . '_' . $sef, $this->item->{'user_renew_email_body_offline' . $prefix . '_' . $sef}, '100%', '250', '75', '8'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <?php echo OSMembershipHelperHtml::getFieldLabel('renew_thanks_message_offline' . $prefix . '_' . $sef, Text::_('OSM_RENEW_THANK_MESSAGE_OFFLINE') . '(' . $title . ')', Text::_('OSM_RENEW_THANK_MESSAGE_OFFLINE_EXPLAIN')); ?>
            </div>
            <div class="controls">
                <?php echo $editor->display('renew_thanks_message_offline' . $prefix . '_' . $sef, $this->item->{'renew_thanks_message_offline' . $prefix . '_' . $sef}, '100%', '250', '75', '8'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <?php echo Text::_('OSM_UPGRADE_USER_EMAIL_BODY_OFFLINE'); ?>(<?php echo $title; ?>)
                <p class="osm-available-tags">
                    <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
                </p>
            </div>
            <div class="controls">
                <?php echo $editor->display('user_upgrade_email_body_offline' . $prefix . '_' . $sef, $this->item->{'user_upgrade_email_body_offline' . $prefix . '_' . $sef}, '100%', '250', '75', '8'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <?php echo OSMembershipHelperHtml::getFieldLabel('upgrade_thanks_message_offline' . $prefix . '_' . $sef, Text::_('OSM_UPGRADE_THANK_MESSAGE_OFFLINE') . '(' . $title . ')', Text::_('OSM_UPGRADE_THANK_MESSAGE_OFFLINE_EXPLAIN')); ?>
            </div>
            <div class="controls">
                <?php echo $editor->display('upgrade_thanks_message_offline' . $prefix . '_' . $sef, $this->item->{'upgrade_thanks_message_offline' . $prefix . '_' . $sef}, '100%', '250', '75', '8'); ?>
            </div>
        </div>
		<?php
		}
	echo HTMLHelper::_( 'uitab.endTab');
}

echo HTMLHelper::_( 'uitab.endTabSet');
