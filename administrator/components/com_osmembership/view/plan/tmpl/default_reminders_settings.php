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
use Joomla\CMS\Plugin\PluginHelper;

?>
<fieldset class="form-horizontal options-form">
	<legend class="adminform"><?php echo Text::_('OSM_REMINDERS_SETTINGS'); ?></legend>
    <div class="control-group">
        <div class="control-label">
			<?php echo  Text::_('OSM_SEND_FIRST_REMINDER'); ?>
        </div>
        <div class="controls">
            <input type="number" class="input-small form-control d-inline-block" name="send_first_reminder" value="<?php echo $this->item->send_first_reminder; ?>" size="5" /><span><?php echo ' ' . Text::_('OSM_DAYS') . ' ' . $this->lists['send_first_reminder_time']; ?></span><?php echo Text::_('OSM_SUBSCRIPTION_EXPIRED'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo  Text::_('OSM_SEND_SECOND_REMINDER'); ?>
        </div>
        <div class="controls">
            <input type="number" class="input-small form-control d-inline-block" name="send_second_reminder" value="<?php echo $this->item->send_second_reminder; ?>" size="5" /><span><?php echo ' ' . Text::_('OSM_DAYS') . ' ' . $this->lists['send_second_reminder_time']; ?></span><?php echo Text::_('OSM_SUBSCRIPTION_EXPIRED'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo  Text::_('OSM_SEND_THIRD_REMINDER'); ?>
        </div>
        <div class="controls">
            <input type="number" class="input-small form-control d-inline-block" name="send_third_reminder" value="<?php echo $this->item->send_third_reminder; ?>" size="5" /><span><?php echo ' ' . Text::_('OSM_DAYS') . ' ' . $this->lists['send_third_reminder_time']; ?></span><?php echo Text::_('OSM_SUBSCRIPTION_EXPIRED'); ?>
        </div>
    </div>
    <?php
        if (property_exists($this->item, 'send_fourth_reminder'))
        {
        ?>
            <div class="control-group">
                <div class="control-label">
			        <?php echo  Text::_('OSM_SEND_FOURTH_REMINDER'); ?>
                </div>
                <div class="controls">
                    <input type="number" class="input-small form-control d-inline-block" name="send_fourth_reminder" value="<?php echo $this->item->send_fourth_reminder; ?>" size="5" /><span><?php echo ' ' . Text::_('OSM_DAYS') . ' ' . $this->lists['send_fourth_reminder_time']; ?></span><?php echo Text::_('OSM_SUBSCRIPTION_EXPIRED'); ?>
                </div>
            </div>
        <?php
        }

        if (property_exists($this->item, 'send_fifth_reminder'))
        {
        ?>
            <div class="control-group">
                <div class="control-label">
                    <?php echo  Text::_('OSM_SEND_FIFTH_REMINDER'); ?>
                </div>
                <div class="controls">
                    <input type="number" class="input-small form-control d-inline-block" name="send_fifth_reminder" value="<?php echo $this->item->send_fifth_reminder; ?>" size="5" /><span><?php echo ' ' . Text::_('OSM_DAYS') . ' ' . $this->lists['send_fifth_reminder_time']; ?></span><?php echo Text::_('OSM_SUBSCRIPTION_EXPIRED'); ?>
                </div>
            </div>
        <?php
        }

        if (property_exists($this->item, 'send_sixth_reminder'))
        {
        ?>
            <div class="control-group">
                <div class="control-label">
                    <?php echo  Text::_('OSM_SEND_SIXTH_REMINDER'); ?>
                </div>
                <div class="controls">
                    <input type="number" class="input-small form-control d-inline-block" name="send_sixth_reminder" value="<?php echo $this->item->send_sixth_reminder; ?>" size="5" /><span><?php echo ' ' . Text::_('OSM_DAYS') . ' ' . $this->lists['send_sixth_reminder_time']; ?></span><?php echo Text::_('OSM_SUBSCRIPTION_EXPIRED'); ?>
                </div>
            </div>
        <?php
        }

        if ($this->item->number_payments > 0)
		{
		?>
            <div class="control-group">
                <div class="control-label">
			        <?php echo  Text::_('OSM_SEND_SUBSCRIPTION_END'); ?>
                </div>
                <div class="controls">
                    <input type="number" class="input-mini form-control d-inline-block" name="send_subscription_end" value="<?php echo $this->item->send_subscription_end; ?>" size="5" /><span><?php echo ' ' . Text::_('OSM_DAYS') . ' ' . $this->lists['send_subscription_end_time']; ?></span><?php echo Text::_('OSM_SUBSCRIPTION_EXPIRED'); ?>
                </div>
            </div>
        <?php
		}

		if (PluginHelper::isEnabled('system', 'membershipprosms'))
		{
		?>
            <div class="control-group">
                <div class="control-label">
                    <?php echo Text::_('OSM_ENABLE_SMS'); ?>
                </div>
                <div class="controls">
                    <?php echo OSMembershipHelperHtml::getBooleanInput('enable_sms_reminder', $this->item->enable_sms_reminder); ?>
                </div>
            </div>
        <?php
		}
	?>
</fieldset>
