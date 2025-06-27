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

?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'currency.cancel') {
			Joomla.submitform(pressbutton, form);
			return;
		} else {
			//Validate the entered data before submitting
			if (form.currency_name.value == '') {
				alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
				form.currency_name.focus();
				return;
			}
			Joomla.submitform(pressbutton, form);
		}
	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo  Text::_('ESHOP_NAME'); ?>
		</div>
		<div class="controls">
			<input class="input-large form-control" type="text" name="currency_name" id="currency_name" size="40" maxlength="250" value="<?php echo $this->item->currency_name; ?>" />
		</div>
	</div>				
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_CURRENCY_CODE'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="currency_code" id="currency_code" value="<?php echo $this->item->currency_code; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_LEFT_SYMBOL'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="left_symbol" id="left_symbol" value="<?php echo $this->item->left_symbol; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_RIGHT_SYMBOL'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="right_symbol" id="right_symbol" value="<?php echo $this->item->right_symbol; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_DECIMAL_SYMBOL'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="decimal_symbol" id="decimal_symbol" value="<?php echo $this->item->decimal_symbol; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_DECIMAL_PLACE'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="decimal_place" id="decimal_place" value="<?php echo $this->item->decimal_place; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_THOUSANDS_SEPARATOR'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="thousands_separator" id="thousands_separator" value="<?php echo $this->item->thousands_separator; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_EXCHANGED_VALUE'); ?>
		</div>
		<div class="controls">
			<input class="input-large form-control" type="text" name="exchanged_value" id="exchanged_value" value="<?php echo $this->item->exchanged_value ? $this->item->exchanged_value : 1; ?>" />
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
	<div class="clearfix"></div>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<input type="hidden" name="task" value="" />
</form>