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
        <?php echo OSMembershipHelperHtml::getFieldLabel('subscription_renew_form_msg', Text::_('OSM_SUBSCRIPTION_RENEW_FORM_MESSAGE'), Text::_('OSM_SUBSCRIPTION_RENEW_FORM_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('subscription_renew_form_msg', $this->item->subscription_renew_form_msg, '100%', '250', '75', '8') ;?>
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
        <input type="text" name="admin_renw_email_subject" class="form-control" value="<?php echo $this->item->admin_renw_email_subject; ?>" size="40" />
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
        <?php echo $editor->display('admin_renew_email_body', $this->item->admin_renew_email_body, '100%', '250', '75', '8') ;?>
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
        <input type="text" name="user_renew_email_subject" class="form-control" value="<?php echo $this->item->user_renew_email_subject; ?>" size="40" />
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
        <?php echo $editor->display('user_renew_email_body', $this->item->user_renew_email_body, '100%', '250', '75', '8') ;?>
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
        <?php echo $editor->display('user_renew_email_body_offline', $this->item->user_renew_email_body_offline, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('renew_thanks_message', Text::_('OSM_RENEW_THANK_MESSAGE'), Text::_('OSM_RENEW_THANK_MESSAGE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('renew_thanks_message', $this->item->renew_thanks_message, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('renew_thanks_message_offline', Text::_('OSM_RENEW_THANK_MESSAGE_OFFLINE'), Text::_('OSM_RENEW_THANK_MESSAGE_OFFLINE_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('renew_thanks_message_offline', $this->item->renew_thanks_message_offline, '100%', '250', '75', '8') ;?>
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
            <?php echo Text::_('OSM_RENEW_USER_EMAIL_BODY_OFFLINE'); ?>(<?php echo $title; ?>)
            <p class="osm-available-tags">
                <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT], [TRANSACTION_ID], [PAYMENT_METHOD]</strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('user_renew_email_body_offline' . $prefix, $this->item->{'user_renew_email_body_offline' . $prefix}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('renew_thanks_message_offline' . $prefix, Text::_('OSM_RENEW_THANK_MESSAGE_OFFLINE') . '(' . $title . ')', Text::_('OSM_RENEW_THANK_MESSAGE_OFFLINE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display('renew_thanks_message_offline' . $prefix, $this->item->{'renew_thanks_message_offline' . $prefix}, '100%', '250', '75', '8'); ?>
        </div>
    </div>
    <?php
}
