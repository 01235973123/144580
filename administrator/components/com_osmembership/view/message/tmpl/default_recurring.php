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
        <?php echo OSMembershipHelperHtml::getFieldLabel('recurring_subscription_cancel_message', Text::_('OSM_RECURRING_SUBSCRIPTION_CANCEL_MESSAGE'), Text::_('OSM_RECURRING_SUBSCRIPTION_CANCEL_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('recurring_subscription_cancel_message', $this->item->recurring_subscription_cancel_message, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('user_recurring_subscription_cancel_subject', Text::_('OSM_USER_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_SUBJECT'), Text::_('OSM_USER_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_SUBJECT_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <input type="text" name="user_recurring_subscription_cancel_subject" class="form-control" value="<?php echo $this->item->user_recurring_subscription_cancel_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('user_recurring_subscription_cancel_body', Text::_('OSM_USER_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_BODY'), Text::_('OSM_USER_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_BODY_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('user_recurring_subscription_cancel_body', $this->item->user_recurring_subscription_cancel_body, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('admin_recurring_subscription_cancel_subject', Text::_('OSM_ADMIN_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_SUBJECT'), Text::_('OSM_ADMIN_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_SUBJECT_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <input type="text" name="admin_recurring_subscription_cancel_subject" class="form-control" value="<?php echo $this->item->admin_recurring_subscription_cancel_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('admin_recurring_subscription_cancel_body', Text::_('OSM_ADMIN_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_BODY'), Text::_('OSM_ADMIN_RECURRING_SUBSCRIPTION_CANCEL_EMAIL_BODY_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('admin_recurring_subscription_cancel_body', $this->item->admin_recurring_subscription_cancel_body, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_OFFLINE_RECURRING_RENEWAL_EMAIL_SUBJECT'); ?>
        <p class="osm-available-tags">
            <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE]</strong>
        </p>
    </div>
    <div class="controls">
        <input type="text" name="offline_recurring_email_subject" class="form-control" value="<?php echo $this->item->offline_recurring_email_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_OFFLINE_RECURRING_RENEWAL_EMAIL_BODY'); ?>
        <p class="osm-available-tags">
            <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
        </p>
    </div>
    <div class="controls">
        <?php echo $editor->display('offline_recurring_email_body', $this->item->offline_recurring_email_body, '100%', '250', '75', '8') ;?>
    </div>
</div>
