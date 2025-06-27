<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Layout variables
 *
 * @var Joomla\CMS\Editor\Editor $editor
 */
?>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('OSM_PLAN_DETAIL');?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="title" id="title" size="40" maxlength="250" value="<?php echo $this->item->title;?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_ALIAS'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="alias" id="alias" size="40" maxlength="250" value="<?php echo $this->item->alias;?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_CATEGORY'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['category_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_PRICE'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="number" name="price" id="price" size="10" maxlength="250" value="<?php echo $this->item->price;?>" step="0.01" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_SUBSCRIPTION_LENGTH'); ?>
		</div>
		<div class="controls">
			<input class="form-control input-small d-inline-block" type="number" name="subscription_length" id="subscription_length" min="1" size="10" maxlength="250" value="<?php echo $this->item->subscription_length;?>" /><?php echo $this->lists['subscription_length_unit']; ?>
		</div>
	</div>
	<?php
	if ($this->item->recurring_subscription && $this->item->subscription_length == 1 && $this->item->subscription_length_unit == 'M')
	{
	?>
        <div class="control-group">
            <div class="control-label">
				<?php echo OSMembershipHelperHtml::getFieldLabel('payment_day', Text::_('OSM_PAYMENT_DAY'), Text::_('OSM_PAYMENT_DAY_EXPLAIN')) ?>
            </div>
            <div class="controls">
                <input type="number" max="31" min="0" step="1" name="payment_day" class="form-control" value="<?php echo $this->item->payment_day; ?>" />
            </div>
        </div>
	<?php
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('expired_date', Text::_('OSM_EXPIRED_DATE'), Text::_('OSM_EXPIRED_DATE_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->expired_date, 'expired_date', 'expired_date', $this->datePickerFormat) ; ?>
		</div>
	</div>
	<?php
	if ((int)$this->item->expired_date)
	{
	?>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('OSM_PRORATED_SIGNUP_COST');?>
			</div>
			<div class="controls">
				<?php echo $this->lists['prorated_signup_cost'];?>
			</div>
		</div>
        <div class="control-group">
            <div class="control-label">
                <?php echo OSMembershipHelperHtml::getFieldLabel('grace_period', Text::_('OSM_OVERLAP_PERIOD'), Text::_('OSM_OVERLAP_PERIOD_EXPLAIN')); ?>
            </div>
            <div class="controls">
                <input class="form-control input-small" type="number" name="grace_period" id="grace_period" size="10" maxlength="250" value="<?php echo $this->item->grace_period;?>" /><?php echo ' ' . Text::_('OSM_DAYS'); ?>
            </div>
        </div>
	<?php
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_LIFETIME_MEMBERSHIP');?>
		</div>
		<div class="controls">
			<?php echo $this->lists['lifetime_membership'];?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_THUMB'); ?>
		</div>
		<div class="controls">
			<input type="file" class="form-control" name="thumb_image" size="60" />
			<?php
			if ($this->item->thumb)
			{
			?>
				<img src="<?php echo Uri::root(true) . '/media/com_osmembership/' . $this->item->thumb; ?>" class="img_preview" />
				<input type="checkbox" name="del_thumb" value="1" /><?php echo Text::_('OSM_DELETE_CURRENT_THUMB'); ?>
			<?php
			}
			?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_ENABLE_RENEWAL'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_renewal']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('subscribe_access', Text::_('OSM_ACCESS', Text::_('OSM_ACCESS_EXPLAIN'))) ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['access']; ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('subscribe_access', Text::_('OSM_SUBSCRIBE_ACCESS', Text::_('OSM_SUBSCRIBE_ACCESS_EXPLAIN'))) ?>
        </div>
        <div class="controls">
			<?php echo $this->lists['subscribe_access']; ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('hidden', Text::_('OSM_HIDDEN', Text::_('OSM_HIDDEN_EXPLAIN'))) ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('hidden', $this->item->hidden); ?>
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
	<div class="control-group">
		<label class="mp-form-field-label" for="short_description">
			<?php echo Text::_('OSM_SHORT_DESCRIPTION'); ?>
		</label>
		<?php echo $editor->display('short_description', $this->item->short_description, '100%', '450', '75', '10') ; ?>
	</div>
	<div class="control-group">
		<label class="mp-form-field-label" for="description">
			<?php echo Text::_('OSM_DESCRIPTION'); ?>
		</label>
		<?php echo $editor->display('description', $this->item->description, '100%', '450', '75', '10') ; ?>
	</div>
</fieldset>
