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
        <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_form_message', Text::_('OSM_SUBSCRIPTION_PAYMENT_FORM_MESSAGE'), Text::_('OSM_SUBSCRIPTION_PAYMENT_FORM_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('subscription_payment_form_message', $this->item->subscription_payment_form_message, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_admin_email_subject', Text::_('OSM_SUBSCRIPTION_PAYMENT_ADMIN_EMAIL_SUBJECT')); ?>
    </div>
    <div class="controls">
        <input type="text" name="subscription_payment_admin_email_subject" class="form-control" value="<?php echo $this->item->subscription_payment_admin_email_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_admin_email_body', Text::_('OSM_SUBSCRIPTION_PAYMENT_ADMIN_EMAIL_BODY')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('subscription_payment_admin_email_body', $this->item->subscription_payment_admin_email_body, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_user_email_subject', Text::_('OSM_SUBSCRIPTION_PAYMENT_USER_EMAIL_SUBJECT')); ?>
    </div>
    <div class="controls">
        <input type="text" name="subscription_payment_user_email_subject" class="form-control" value="<?php echo $this->item->subscription_payment_user_email_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_user_email_body', Text::_('OSM_SUBSCRIPTION_PAYMENT_USER_EMAIL_BODY')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('subscription_payment_user_email_body', $this->item->subscription_payment_user_email_body, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_payment_thanks_message', Text::_('OSM_SUBSCRIPTION_PAYMENT_THANK_MESSAGE'), Text::_('OSM_SUBSCRIPTION_PAYMENT_THANK_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('subscription_payment_thanks_message', $this->item->subscription_payment_thanks_message, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('request_payment_email_subject', Text::_('OSM_REQUEST_PAYMENT_EMAIL_SUBJECT')); ?>
    </div>
    <div class="controls">
        <input type="text" name="request_payment_email_subject" class="form-control" value="<?php echo $this->item->request_payment_email_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('request_payment_email_body', Text::_('OSM_REQUEST_PAYMENT_EMAIL_BODY')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('request_payment_email_body', $this->item->request_payment_email_body, '100%', '250', '75', '8') ;?>
    </div>
</div>