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
 * @var MPFConfig $config
 */
?>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('OSM_MAIL_SETTINGS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('from_name', Text::_('OSM_FROM_NAME'), Text::_('OSM_FROM_NAME_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="from_name" class="form-control" value="<?php echo $config->from_name; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('from_email', Text::_('OSM_FROM_EMAIL'), Text::_('OSM_FROM_EMAIL_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="from_email" class="form-control" value="<?php echo $config->from_email; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('reply_to_email', Text::_('OSM_REPLY_TO_EMAIL')); ?>
		</div>
		<div class="controls">
			<input type="email" name="reply_to_email" class="form-control" value="<?php echo $config->reply_to_email; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('notification_emails', Text::_('OSM_NOTIFICATION_EMAILS'), Text::_('OSM_NOTIFICATION_EMAILS_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="notification_emails" class="form-control" value="<?php echo $config->notification_emails; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('log_emails', Text::_('OSM_LOG_EMAILS'), Text::_('OSM_LOG_EMAILS_EXPLAIN')); ?>
		</div>
		<div class="controls">
            <?php
				if ($config->log_emails || !empty($config->log_email_types))
				{
					$logEmails = true;
				}
				else
				{
					$logEmails = false;
				}

				echo OSMembershipHelperHtml::getBooleanInput('log_emails', $logEmails);
			?>
		</div>
	</div>
</fieldset>
