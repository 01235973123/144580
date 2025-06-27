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
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('OSM_BATCH_COUPONS_TITLE'));
ToolbarHelper::custom('coupon.batch', 'upload', 'upload', 'Generate Coupons', false);
ToolbarHelper::cancel('coupon.cancel');

Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('showon');
?>
<form action="index.php?option=com_osmembership&view=coupon" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
    <div class="control-group">
        <div class="control-label">
            <?php echo  Text::_('OSM_NUMBER_COUPONS'); ?>
        </div>
        <div class="controls">
            <input class="form-control input-small" type="number" name="number_coupon" id="number_coupon" size="15" maxlength="250" value="" />
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
    <div class="control-group">
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
            <?php echo  Text::_('OSM_DISCOUNT'); ?>
        </div>
        <div class="controls">
            <input class="form-control input-small d-inline-block" type="number" name="discount" id="discount" size="10" maxlength="250" value="" />&nbsp;&nbsp;<?php echo $this->lists['coupon_type'] ; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo  Text::_('OSM_CHARACTERS_SET'); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="text" name="characters_set" id="characters_set" size="15" maxlength="250" value="" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo  Text::_('OSM_PREFIX'); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="text" name="prefix" id="prefix" size="15" maxlength="250" value="" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo  Text::_('OSM_COUPON_LENGTH'); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="number" name="length" id="length" size="15" maxlength="250" value="" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_VALID_FROM_DATE'); ?>
        </div>
        <div class="controls">
            <?php echo HTMLHelper::_('calendar', '', 'valid_from', 'valid_from', $this->datePickerFormat . ' %H:%M:%S') ; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_VALID_TO_DATE'); ?>
        </div>
        <div class="controls">
            <?php echo HTMLHelper::_('calendar', '', 'valid_to', 'valid_to', $this->datePickerFormat . ' %H:%M:%S') ; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_TIMES'); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="number" name="times" id="times" size="5" maxlength="250" value="<?php echo $this->item->times;?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_NOTE'); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="text" name="note" id="note" size="5" maxlength="250" value="<?php echo $this->item->note;?>" />
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
    <div class="clr"></div>
	<?php echo HTMLHelper::_('form.token'); ?>
	<input type="hidden" name="used" value="<?php echo $this->item->used;?>" />
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="" />
</form>