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
<fieldset class="form-horizontal options-form">
	<legend class="adminform"><?php echo Text::_('OSM_RECURRING_SETTINGS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_RECURRING_SUBSCRIPTION'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['recurring_subscription']; ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['recurring_subscription' => '1']); ?>'>
		<div class="control-label">
			<?php echo Text::_('OSM_TRIAL_AMOUNT'); ?>
		</div>
		<div class="controls">
			<input type="text" class="form-control" name="trial_amount" value="<?php echo $this->item->trial_amount; ?>" size="10" />
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['recurring_subscription' => '1']); ?>'>
		<div class="control-label">
			<?php echo Text::_('OSM_TRIAL_DURATION'); ?>
		</div>
		<div class="controls">
			<input type="text" class="input-mini form-control d-inline-block" name="trial_duration" value="<?php echo $this->item->trial_duration > 0 ? $this->item->trial_duration : ''; ?>"/>
			<?php echo $this->lists['trial_duration_unit']; ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['recurring_subscription' => '1']); ?>'>
		<div class="control-label">
			<?php echo Text::_('OSM_NUMBER_PAYMENTS'); ?>
		</div>
		<div class="controls">
			<input type="text" class="form-control" name="number_payments" value="<?php echo $this->item->number_payments; ?>" size="10" />
		</div>
	</div>

    <?php
		if ($this->item->number_payments > 0)
		{
		?>
            <div class="control-group">
                <div class="control-label">
                    <?php echo OSMembershipHelperHtml::getFieldLabel('last_payment_action', Text::_('OSM_AFTER_LAST_PAYMENT_ACTION'), Text::_('OSM_AFTER_LAST_PAYMENT_ACTION_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['last_payment_action']; ?>
                </div>
            </div>
            <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['last_payment_action' => '2']); ?>'>
                <div class="control-label">
			        <?php echo Text::_('OSM_EXTEND_DURATION'); ?>
                </div>
                <div class="controls">
                    <input type="text" class="input-mini" name="extend_duration" value="<?php echo $this->item->extend_duration > 0 ? $this->item->extend_duration : ''; ?>"/>
			        <?php echo $this->lists['extend_duration_unit']; ?>
                </div>
            </div>
        <?php
		}
	?>
</fieldset>