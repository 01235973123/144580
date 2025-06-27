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

?>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_ADMIN_EMAIL_SUBJECT'); ?>
        <p class="osm-available-tags">
            <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE]</strong>
        </p>
    </div>
    <div class="controls">
        <input type="text" name="admin_email_subject" class="form-control" value="<?php echo $this->item->admin_email_subject; ?>" size="40" />
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
        <?php echo $editor->display('admin_email_body', $this->item->admin_email_body, '100%', '250', '75', '8') ;?>
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
        <input type="text" name="user_email_subject" class="form-control" value="<?php echo $this->item->user_email_subject; ?>" size="40" />
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
        <?php echo $editor->display('user_email_body', $this->item->user_email_body, '100%', '250', '75', '8') ;?>
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
        <?php echo $editor->display('user_email_body_offline', $this->item->user_email_body_offline, '100%', '250', '75', '8') ;?>
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
        <input type="text" name="subscription_approved_email_subject" class="form-control" value="<?php echo $this->item->subscription_approved_email_subject; ?>" size="40" />
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
        <?php echo $editor->display('subscription_approved_email_body', $this->item->subscription_approved_email_body, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo Text::_('OSM_ADMIN_SUBSCRIPTION_APPROVED_EMAIL_SUBJECT'); ?>
        <p class="osm-available-tags">
            <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE]</strong>
        </p>
    </div>
    <div class="controls">
        <input type="text" name="admin_subscription_approved_email_subject" class="form-control" value="<?php echo $this->item->admin_subscription_approved_email_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo Text::_('OSM_ADMIN_SUBSCRIPTION_APPROVED_EMAIL_BODY'); ?>
        <p class="osm-available-tags">
            <strong>Available Tags :[PAYMENT_DETAIL], [FORM_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
        </p>
    </div>
    <div class="controls">
		<?php echo $editor->display('admin_subscription_approved_email_body', $this->item->admin_subscription_approved_email_body, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_form_msg', Text::_('OSM_SUBSCRIPTION_FORM_MESSAGE'), Text::_('OSM_SUBSCRIPTION_FORM_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('subscription_form_msg', $this->item->subscription_form_msg, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('thanks_message', Text::_('OSM_THANK_MESSAGE'), Text::_('OSM_THANK_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('thanks_message', $this->item->thanks_message, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('thanks_message_offline', Text::_('OSM_THANK_MESSAGE_OFFLINE'), Text::_('OSM_THANK_MESSAGE_OFFLINE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('thanks_message_offline', $this->item->thanks_message_offline, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('cancel_message', Text::_('OSM_PAYMENT_CANCEL_MESSAGE'), Text::_('OSM_PAYMENT_CANCEL_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('cancel_message', $this->item->cancel_message, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('failure_message', Text::_('OSM_PAYMENT_FAILURE_MESSAGE'), Text::_('OSM_PAYMENT_FAILURE_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('failure_message', $this->item->failure_message, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('profile_update_email_subject', Text::_('OSM_PROFILE_UPDATE_EMAIL_SUBJECT'), Text::_('OSM_PROFILE_UPDATE_EMAIL_SUBJECT_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <input type="text" name="profile_update_email_subject" class="form-control" value="<?php echo $this->item->profile_update_email_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_PROFILE_UPDATE_EMAIL_BODY'); ?>
        <p class="osm-available-tags">
            <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[PROFILE_LINK], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT]</strong>
        </p>
    </div>
    <div class="controls">
        <?php echo $editor->display('profile_update_email_body', $this->item->profile_update_email_body, '100%', '250', '75', '8') ;?>
    </div>
</div>

<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('content_restricted_message', Text::_('OSM_CONTENT_RESTRICTED_MESSAGE'), Text::_('OSM_CONTENT_RESTRICTED_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('content_restricted_message', $this->item->content_restricted_message, '100%', '250', '75', '8') ;?>
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
            <?php echo $editor->display('user_email_body_offline' . $prefix, $this->item->{'user_email_body_offline' . $prefix}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('thanks_message_offline' . $prefix, Text::_('OSM_THANK_MESSAGE_OFFLINE') . '(' . $title . ')', Text::_('OSM_THANK_MESSAGE_OFFLINE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('thanks_message_offline' . $prefix, $this->item->{'thanks_message_offline' . $prefix}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <?php
}
?>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('mass_mail_template', Text::_('OSM_MASS_MAIL_TEMPLATE')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('mass_mail_template', $this->item->mass_mail_template, '100%', '250', '75', '8') ;?>
    </div>
</div>