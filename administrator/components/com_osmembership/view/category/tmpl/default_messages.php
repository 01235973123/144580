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
 * Layout variable
 *
 * @var \Joomla\CMS\Editor\Editor $editor
 */
?>
<div class="control-group">
	<p class="text-error" style="font-size:16px;"><?php echo Text::_('OSM_CATEGORY_MESSAGES_EXPLAIN'); ?></p>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_SUBSCRIPTION_FORM_MESSAGE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('subscription_form_message', $this->item->subscription_form_message, '100%', '350', '75', '10'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_USER_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('user_email_body', $this->item->user_email_body, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_USER_EMAIL_BODY_OFFLINE_PAYMENT'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('user_email_body_offline', $this->item->user_email_body_offline, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_ADMIN_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('admin_email_body', $this->item->admin_email_body, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_THANK_MESSAGE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('thanks_message', $this->item->thanks_message, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_THANK_MESSAGE_OFFLINE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('thanks_message_offline', $this->item->thanks_message_offline, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_SUBSCRIPTION_RENEW_FORM_MESSAGE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('subscription_renew_form_msg', $this->item->subscription_renew_form_msg, '100%', '350', '75', '10'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_RENEW_USER_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('user_renew_email_body', $this->item->user_renew_email_body, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_RENEW_USER_EMAIL_BODY_OFFLINE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('user_renew_email_body_offline', $this->item->user_renew_email_body_offline, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_RENEW_ADMIN_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('admin_renew_email_body', $this->item->admin_renew_email_body, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_RENEW_THANK_MESSAGE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('renew_thanks_message', $this->item->renew_thanks_message, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_RENEW_THANK_MESSAGE_OFFLINE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('renew_thanks_message_offline', $this->item->renew_thanks_message_offline, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_SUBSCRIPTION_UPGRADE_FORM_MESSAGE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('subscription_upgrade_form_msg', $this->item->subscription_upgrade_form_msg, '100%', '350', '75', '10'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_UPGRADE_USER_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('user_upgrade_email_body', $this->item->user_upgrade_email_body, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_UPGRADE_USER_EMAIL_BODY_OFFLINE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('user_upgrade_email_body_offline', $this->item->user_upgrade_email_body_offline, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_UPGRADE_ADMIN_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('admin_upgrade_email_body', $this->item->admin_upgrade_email_body, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_UPGRADE_THANK_MESSAGE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('upgrade_thanks_message', $this->item->upgrade_thanks_message, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_UPGRADE_THANK_MESSAGE_OFFLINE'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('upgrade_thanks_message_offline', $this->item->upgrade_thanks_message_offline, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_SUBSCRIPTION_APPROVED_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('subscription_approved_email_body', $this->item->subscription_approved_email_body, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_FIRST_REMINDER_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('first_reminder_email_body', $this->item->first_reminder_email_body, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_SECOND_REMINDER_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('second_reminder_email_body', $this->item->second_reminder_email_body, '100%', '350', '75', '8'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_THIRD_REMINDER_EMAIL_BODY'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('third_reminder_email_body', $this->item->third_reminder_email_body, '100%', '350', '75', '8'); ?>
	</div>
</div>

<?php
if (property_exists($this->item, 'fourth_reminder_email_body'))
{
	$this->editor('fourth_reminder_email_body', 'OSM_FOURTH_REMINDER_EMAIL_BODY');
}

if (property_exists($this->item, 'fifth_reminder_email_body'))
{
	$this->editor('fifth_reminder_email_body', 'OSM_FIFTH_REMINDER_EMAIL_BODY');
}

if (property_exists($this->item, 'sixth_reminder_email_body'))
{
	$this->editor('sixth_reminder_email_body', 'OSM_SIXTH_REMINDER_EMAIL_BODY');
}
