<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!$this->config->multiple_booking && $this->config->activate_invoice_feature)
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('enable_invoice', Text::_('EB_ENABLE_INVOICE'), Text::_('EB_ENABLE_INVOICE_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_invoice']; ?>
		</div>
	</div>
<?php
}

if (!$this->config->multiple_booking && $this->config->get('bes_user_registration', 1))
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('user_registration', Text::_('EB_USER_REGISTRATION_INTEGRATION'), Text::_('EB_USER_REGISTRATION_INTEGRATION_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['user_registration']; ?>
		</div>
	</div>
<?php
}

if ($this->config->get('bes_show_enable_coupon', 1))
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_ENABLE_COUPON'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_coupon']; ?>
		</div>
	</div>
<?php
}

if ($this->config->activate_deposit_feature
	&& !$this->config->multiple_booking)
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('deposit_amount_apply_for', Text::_('EB_DEPOSIT_AMOUNT_APPLY_FOR')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['deposit_amount_apply_for']; ?>
		</div>
	</div>
<?php
}

if ($this->config->get('bes_show_activate_waiting_list', 1))
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_ENABLE_WAITING_LIST'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['activate_waiting_list']; ?>
		</div>
	</div>
<?php
}

if ($this->config->get('bes_show_collect_member_information', 1))
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_COLLECT_MEMBER_INFORMATION'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['collect_member_information']; ?>
		</div>
	</div>
<?php
}

if ($this->config->get('bes_show_prevent_duplicate_registration', 1))
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_PREVENT_DUPLICATE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['prevent_duplicate_registration']; ?>
		</div>
	</div>
<?php
}

if ($this->config->get('bes_show_send_emails', 1))
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_SEND_NOTIFICATION_EMAILS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['send_emails']; ?>
		</div>
	</div>
<?php
}

if ($this->config->get('bes_show_enable_terms_and_conditions', 1))
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_ENABLE_TERMS_CONDITIONS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_terms_and_conditions']; ?>
		</div>
	</div>
<?php
}

if ($this->config->get('bes_show_article_id', 1))
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_TERMS_CONDITIONS'); ?>
		</div>
		<div class="controls">
			<?php echo EventbookingHelperHtml::getArticleInput($this->item->article_id); ?>
		</div>
	</div>
<?php
}
?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('send_ics_file', Text::_('EB_SEND_ICS_FILE')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['send_ics_file']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('simply_registration_process', Text::_('EB_SIMPLY_REGISTRATION_PROCESS')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['simply_registration_process']; ?>
	</div>
</div>

