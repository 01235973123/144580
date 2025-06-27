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

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
EShopHelper::chosen();
?>
<script type="text/javascript">	
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'taxclass.cancel') {
			Joomla.submitform(pressbutton, form);
			return;				
		} else {
			//Validate the entered data before submitting
			if (form.taxclass_name.value == '') {
				alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
				form.taxclass_name.focus();
				return;
			}
			Joomla.submitform(pressbutton, form);
		}
	}
	var rowIndex = <?php echo isset($this->taxrateIds) ? count($this->taxrateIds) : 0; ?>;			
	function addTaxGroup(){
		html = '<tr id="taxrate' + rowIndex + '">>';
		html += '<td><select name="tax_id[]" class="input-xlarge form-select">';				
		html += taxrateOptions;		   
		html += '</select></td>';
		html += '<td><select name="based_on[]" class="input-xlarge form-select">';				
		html += BaseonOptions;		   
		html += '</select></td>';
		html += '<td><input type="text" name="priority[]" value="0" class="input-mini form-control"></td>'
		html += '<td><input type="button" onclick="jQuery(\'#taxrate' + rowIndex + '\').remove();" class="btn btn-small btn-primary" value="<?php echo Text::_('ESHOP_BTN_REMOVE'); ?>"></td>';
		html += '</tr>';
		jQuery('#taxRate_list').append(html);
		rowIndex++;
 	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('ESHOP_TAXCLASS_DETAILS'); ?></legend>
		<div class="control-group">
			<div class="control-label">
				<span class="required">*</span>
				<?php echo  Text::_('ESHOP_NAME'); ?>
			</div>
			<div class="controls">
				<input class="form-control" type="text" name="taxclass_name" id="taxclass_name" size="40" maxlength="250" value="<?php echo $this->item->taxclass_name; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_DESCRIPTION'); ?>
			</div>
			<div class="controls">
				<textarea class="form-control" rows="5" cols="40" name="taxclass_desc"><?php echo $this->item->taxclass_desc; ?></textarea>
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
	</fieldset>
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('ESHOP_TAX_RULES'); ?></legend>
		<table class="adminlist table table-bordered" style="text-align: center;">
			<thead>
				<tr>
					<th class="title" width="30%"><?php echo Text::_('ESHOP_TAX_RATE') ?></th>
					<th class="title" width="30%"><?php echo Text::_('ESHOP_BASED_ON') ?></th>
					<th class="title" width="30%"><?php echo Text::_('ESHOP_PRIORITY') ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody id="taxRate_list">
			<?php
				$rowIndex = 0;
				if(isset($this->taxrateIds) && count($this->taxrateIds))
				{
					foreach ($this->taxrateIds as $taxrateId)
					{
					?>							
						<tr id="taxrate<?php echo $rowIndex?>">
							<td>
								<?php
									echo HTMLHelper::_('select.genericlist', $this->taxrates, 'tax_id[]', 'class="input-xlarge form-select"', 'id', 'name', $taxrateId->tax_id); 
								?>
							</td>
							<td>
								<?php
									echo HTMLHelper::_('select.genericlist', $this->baseonOptions, 'based_on[]', 'class="input-xlarge form-select"', 'value', 'text', $taxrateId->based_on);
								?>
							</td>
							<td>
								<input type="text" name="priority[]" value="<?php echo $taxrateId->priority ?>" class="input-mini form-control">
							</td>
							<td>
								<input type="button" onclick="jQuery('#taxrate<?php echo $rowIndex?>').remove();" class="btn btn-small btn-primary" value="<?php echo Text::_('ESHOP_BTN_REMOVE'); ?>">
							</td>
						</tr>							
					<?php
						$rowIndex ++;
					}
				} 
			?>
			</tbody>
			<tfoot>
				<tr>
	              <td colspan="4">
	              	<input class="btn btn-small btn-primary" type="button" name="add" value="<?php echo Text::_('ESHOP_BTN_ADD')?>" onclick="addTaxGroup();" >
	              </td>
	            </tr>
		    </tfoot>
		</table>	
	</fieldset>
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('ESHOP_ASSIGN_TO_PRODUCTS'); ?></legend>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_PRODUCTS_NO_TAX_LIST'); ?>
				<small class="help"><?php echo Text::_('ESHOP_PRODUCTS_NO_TAX_LIST_HELP'); ?></small>
			</div>
			<div class="controls">
				<?php echo $this->lists['products_no_tax']; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_PRODUCTS_LIST'); ?>
				<small class="help"><?php echo Text::_('ESHOP_PRODUCTS_LIST_HELP'); ?></small>
			</div>
			<div class="controls">
				<?php echo $this->lists['products_list']; ?>
			</div>
		</div>
	</fieldset>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<input type="hidden" name="task" value="" />
</form>