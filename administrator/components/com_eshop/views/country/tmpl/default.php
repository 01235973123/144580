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
		if (pressbutton == 'country.cancel') {
			Joomla.submitform(pressbutton, form);
			return;
		} else {
			//Validate the entered data before submitting
			if (form.country_name.value == '') {
				alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
				form.country_name.focus();
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
			<input class="input-xlarge form-control" type="text" name="country_name" id="country_name" size="40" maxlength="250" value="<?php echo $this->item->country_name; ?>" />
		</div>
	</div>				
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_ISO_CODE2'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="iso_code_2" id="iso_code_2" maxlength="2" value="<?php echo $this->item->iso_code_2; ?>" />
		</div>
	</div>	
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_ISO_CODE3'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="iso_code_3" id="iso_code_3" maxlength="3" value="<?php echo $this->item->iso_code_3; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_POSTCODE_REQUIRED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['postcode_required']; ?>
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