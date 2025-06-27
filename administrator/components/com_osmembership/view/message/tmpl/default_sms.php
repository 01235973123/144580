<?php
/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2020 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/* @var OSMembershipViewMessageHtml $this */
?>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('new_subscription_admin_sms', Text::_('OSM_NEW_SUBSCRIPTION_ADMIN_SMS')); ?>
    </div>
    <div class="controls">
        <textarea name="new_subscription_admin_sms" class="form-control" rows="10"><?php echo $this->item->get('new_subscription_admin_sms'); ?></textarea>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('new_subscription_renewal_admin_sms', Text::_('OSM_NEW_SUBSCRIPTION_RENEWAL_ADMIN_SMS')); ?>
    </div>
    <div class="controls">
        <textarea name="new_subscription_renewal_admin_sms" class="form-control" rows="10"><?php echo $this->item->get('new_subscription_renewal_admin_sms'); ?></textarea>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('new_subscription_upgrade_admin_sms', Text::_('OSM_NEW_SUBSCRIPTION_UPGRADE_ADMIN_SMS')); ?>
    </div>
    <div class="controls">
        <textarea name="new_subscription_upgrade_admin_sms" class="form-control" rows="10"><?php echo $this->item->get('new_subscription_upgrade_admin_sms'); ?></textarea>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('first_reminder_sms', Text::_('OSM_FIRST_REMINDER_SMS')); ?>
    </div>
    <div class="controls">
        <textarea name="first_reminder_sms" class="form-control" rows="10"><?php echo $this->item->get('first_reminder_sms'); ?></textarea>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('second_reminder_sms', Text::_('OSM_SECOND_REMINDER_SMS')); ?>
    </div>
    <div class="controls">
        <textarea name="second_reminder_sms" class="form-control" rows="10"><?php echo $this->item->get('second_reminder_sms'); ?></textarea>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('third_reminder_sms', Text::_('OSM_THIRD_REMINDER_SMS')); ?>
    </div>
    <div class="controls">
        <textarea name="third_reminder_sms" class="form-control" rows="10"><?php echo $this->item->get('third_reminder_sms'); ?></textarea>
    </div>
</div>