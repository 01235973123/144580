<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

EShopHelper::chosen();
?>
<script type="text/javascript">	
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'discount.cancel') {
			Joomla.submitform(pressbutton, form);
			return;
		} else {
			//Validate the entered data before submitting
			if (form.discount_value.value == '') {
				alert("<?php echo Text::_('ESHOP_ENTER_DISCOUNT_VALUE'); ?>");
				form.discount_value.focus();
				return;
			}
			if (form.discount_start_date.value != '' && form.discount_end_date.value != '' && form.discount_start_date.value > form.discount_end_date.value) {
				alert("<?php echo Text::_('ESHOP_DATE_VALIDATE'); ?>");
				form.discount_start_date.focus();
				return;
			}
			Joomla.submitform(pressbutton, form);
		}
	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo  Text::_('ESHOP_DISCOUNT_VALUE'); ?>
		</div>
		<div class="controls">
			<input class="input-large form-control" type="text" name="discount_value" id="discount_value" maxlength="255" value="<?php echo $this->item->discount_value ? $this->item->discount_value : 0; ?>" />
			<small><?php echo Text::_('ESHOP_DISCOUNT_VALUE_HELP'); ?></small>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_DISCOUNT_TYPE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['discount_type']; ?>
			<small><?php echo Text::_('ESHOP_DISCOUNT_TYPE_HELP'); ?></small>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_CUSTOMERGROUPS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['discount_customergroups']; ?>
			<small><?php echo Text::_('ESHOP_CUSTOMERGROUPS_HELP'); ?></small>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_SELECT_PRODUCTS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['products']; ?>
			<small><?php echo Text::_('ESHOP_DISCOUNT_PRODUCTS_HELP'); ?></small>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_SELECT_MANUFACTURERS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['manufacturers']; ?>
			<small><?php echo Text::_('ESHOP_DISCOUNT_MANUFACTURERS_HELP'); ?></small>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_CATEGORIES'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['categories']; ?>
			<small><?php echo Text::_('ESHOP_DISCOUNT_CATEGORIES_HELP'); ?></small>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_START_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->discount_start_date, 'discount_start_date', 'discount_start_date', '%Y-%m-%d %H:%M', ['showTime' => true]); ?>
			<small><?php echo Text::_('ESHOP_DISCOUNT_START_DATE_HELP'); ?></small>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_END_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->discount_end_date, 'discount_end_date', 'discount_end_date', '%Y-%m-%d %H:%M', ['showTime' => true]); ?>
			<small><?php echo Text::_('ESHOP_DISCOUNT_END_DATE_HELP'); ?></small>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<input type="hidden" name="task" value="" />
</form>