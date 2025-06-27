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
 * @var \Joomla\CMS\Editor\Editor $editor
 */
?>
<div class="control-group">
	<p class="text-error" style="font-size:16px;"><?php echo Text::_('OSM_PLAN_MESSAGES_EXPLAIN'); ?></p>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('OSM_FIRST_REMINDER_EMAIL_SUBJECT'); ?>
	</div>
	<div class="controls">
		<input type="text" name="first_reminder_email_subject" class="form-control"
		       value="<?php echo $this->item->first_reminder_email_subject; ?>" size="40"/>
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
		<?php echo Text::_('OSM_SECOND_REMINDER_EMAIL_SUBJECT'); ?>
	</div>
	<div class="controls">
		<input type="text" name="second_reminder_email_subject" class="form-control"
		       value="<?php echo $this->item->second_reminder_email_subject; ?>" size="40"/>
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
		<?php echo Text::_('OSM_THIRD_REMINDER_EMAIL_SUBJECT'); ?>
	</div>
	<div class="controls">
		<input type="text" name="third_reminder_email_subject" class="form-control"
		       value="<?php echo $this->item->third_reminder_email_subject; ?>" size="40"/>
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
if (property_exists($this->item, 'fourth_reminder_email_subject'))
{
    $this->text('fourth_reminder_email_subject', 'OSM_FOURTH_REMINDER_EMAIL_SUBJECT');
}

if (property_exists($this->item, 'fourth_reminder_email_body'))
{
    $this->editor('fourth_reminder_email_body', 'OSM_FOURTH_REMINDER_EMAIL_BODY');
}

if (property_exists($this->item, 'fifth_reminder_email_subject'))
{
	$this->text('fifth_reminder_email_subject', 'OSM_FIFTH_REMINDER_EMAIL_SUBJECT');
}

if (property_exists($this->item, 'fifth_reminder_email_body'))
{
	$this->editor('fifth_reminder_email_body', 'OSM_FIFTH_REMINDER_EMAIL_BODY');
}

if (property_exists($this->item, 'sixth_reminder_email_subject'))
{
	$this->text('sixth_reminder_email_subject', 'OSM_SIXTH_REMINDER_EMAIL_SUBJECT');
}

if (property_exists($this->item, 'sixth_reminder_email_body'))
{
	$this->editor('sixth_reminder_email_body', 'OSM_SIXTH_REMINDER_EMAIL_BODY');
}