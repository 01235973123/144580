<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Little command to allow viewing subscription data easier without having to edit code during support
if ($this->input->getInt('debug'))
{
	print_r($this->item);
}

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core')
	->useScript('showon')
	->registerAndUseScript('com_osmembership.admin-coupon-default', 'media/com_osmembership/js/admin-coupon-default.min.js');

$keys = [
	'OSM_ENTER_COUPON',
	'OSM_ENTER_DISCOUNT_AMOUNT',
];
OSMembershipHelperHtml::addJSStrings($keys);
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<?php
		if (!empty($this->subscriptions))
		{
			echo HTMLHelper::_( 'uitab.startTabSet', 'coupon', ['active' => 'coupon-page', 'recall' => true]);
			echo HTMLHelper::_( 'uitab.addTab', 'coupon', 'coupon-page', Text::_('OSM_BASIC_INFORMATION'));
		}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_CODE'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="code" id="code" size="15" maxlength="250"
			       value="<?php echo $this->item->code; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_DISCOUNT'); ?>
		</div>
		<div class="controls">
			<input class="form-control input-small d-inline-block" type="number" name="discount" id="discount" size="10" maxlength="250"
			       value="<?php echo $this->item->discount; ?>"/>&nbsp;&nbsp;<?php echo $this->lists['coupon_type']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_COUPON_ASSIGNMENT'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['assignment']; ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['assignment' => ['1', '-1']]); ?>'>
		<div class="control-label">
			<?php echo Text::_('OSM_PLANS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['plan_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_type', Text::_('OSM_SUBSCRIPTION_TYPE'), Text::_('OSM_SUBSCRIPTION_TYPE_EXPLAIN'));?>
		</div>
		<div class="controls">
			<?php echo $this->lists['subscription_type']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('apply_for', Text::_('OSM_APPLY_FOR'), Text::_('OSM_APPLY_FOR_EXPLAIN')) ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['apply_for']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_TIMES'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="number" name="times" id="times" size="5" maxlength="250"
			       value="<?php echo $this->item->times; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_TIME_USED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->item->used; ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_MAX_USAGE_PER_USER'); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="number" name="max_usage_per_user" id="max_usage_per_user" size="5" maxlength="250"
                   value="<?php echo $this->item->max_usage_per_user; ?>"/>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_VALID_FROM_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->valid_from, 'valid_from', 'valid_from', $this->datePickerFormat . ' %H:%M:%S'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_VALID_TO_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->valid_to, 'valid_to', 'valid_to', $this->datePickerFormat . ' %H:%M:%S'); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_NOTE'); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="text" name="note" id="note" size="5" maxlength="250"
                   value="<?php echo $this->item->note; ?>"/>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo  Text::_('OSM_USER'); ?>
        </div>
        <div class="controls">
            <?php // Note that 100 parameter is used to prevent on change trigger for the input ?>
	        <?php echo OSMembershipHelper::getUserInput($this->item->user_id, 100) ; ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_ACCESS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['access']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>

	<?php
	if (!empty($this->subscriptions))
	{
		echo HTMLHelper::_( 'uitab.endTab');
		echo HTMLHelper::_( 'uitab.addTab', 'coupon', 'subscriptions-page', Text::_('OSM_COUPON_USAGE'));
		echo $this->loadTemplate('subscriptions');
		echo HTMLHelper::_( 'uitab.endTab');
		echo HTMLHelper::_( 'uitab.endTabSet');
	}
	?>
	<div class="clearfix"></div>
	<?php echo HTMLHelper::_('form.token'); ?>
	<input type="hidden" name="used" value="<?php echo $this->item->used; ?>"/>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value=""/>
</form>