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

if (EShopHelper::isJoomla4())
{
    $tabApiPrefix = 'uitab.';
}
else
{
   HTMLHelper::_('behavior.tabstate');

   $tabApiPrefix = 'bootstrap.';
}

$options = array();
$options[] = HTMLHelper::_('select.option', '1', Text::_('ESHOP_YES'));
$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_NO'));
?>
<script type="text/javascript">	
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		
		if (pressbutton == 'customer.cancelNewAddress') {
			<?php
			if (EShopHelper::isJoomla4())
			{
			    ?>
			    document.getElementById('newAddressFormModal').close();
			    <?php
			}
			else 
			{
			    ?>
			    jQuery( '#newAddressFormModal' ).modal('hide');
			    <?php
			}
			?>
		} else if (pressbutton == 'customer.saveNewAddress') {
			Joomla.submitform(pressbutton, form);
			return;
		} else if (pressbutton == 'customer.cancel') {
			Joomla.submitform(pressbutton, form);
			return;
		} else {
			//Validate the entered data before submitting
			if (form.firstname.value == '') {
				alert("<?php echo Text::_('ESHOP_ENTER_FIRSTNAME'); ?>");
				form.firstname.focus();
				return;
			}
			if (form.lastname.value == '') {
				alert("<?php echo Text::_('ESHOP_ENTER_LASTNAME'); ?>");
				form.lastname.focus();
				return;
			}
			if (form.email.value == '') {
				alert("<?php echo Text::_('ESHOP_ENTER_EMAIL'); ?>");
				form.email.focus();
				return;
			}
			if (form.telephone.value == '') {
				alert("<?php echo Text::_('ESHOP_ENTER_TELEPHONE'); ?>");
				form.telephone.focus();
				return;
			}
			<?php
			if (!$this->item->id)
			{
				?>
				if (form.username.value == '') {
					alert("<?php echo Text::_('ESHOP_ENTER_USERNAME'); ?>");
					form.username.focus();
					return;
				}
				if (form.password.value == '') {
					alert("<?php echo Text::_('ESHOP_ENTER_PASSWORD'); ?>");
					form.password.focus();
					return;
				}
				<?php
			}
			?>
			Joomla.submitform(pressbutton, form);
		}
	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'customer', array('active' => 'general-page'));
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'customer', 'general-page', Text::_('ESHOP_GENERAL', true));
	?>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo Text::_('ESHOP_FIRST_NAME'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="firstname" id="firstname" maxlength="32" value="<?php echo $this->item->firstname; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo Text::_('ESHOP_LAST_NAME'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="lastname" id="lastname" maxlength="32" value="<?php echo $this->item->lastname; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo Text::_('ESHOP_EMAIL'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="email" id="email" maxlength="96" value="<?php echo $this->item->email; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo Text::_('ESHOP_TELEPHONE'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="telephone" id="telephone" maxlength="32" value="<?php echo $this->item->telephone; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_FAX'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="fax" id="fax" maxlength="32" value="<?php echo $this->item->fax; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_CUSTOMERGROUP'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['customergroup_id']; ?>
		</div>
	</div>
	<?php 
	if (!$this->item->id)
	{
	?>
		<div class="control-group">
			<div class="control-label">
				<span class="required">*</span>
				<?php echo Text::_('ESHOP_USERNAME'); ?>
			</div>
			<div class="controls">
				<input class="input-xlarge form-control" type="text" name="username" maxlength="150" value="" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<span class="required">*</span>
				<?php echo Text::_('ESHOP_PASSWORD'); ?>
			</div>
			<div class="controls">
				<input class="input-xlarge form-control" type="password" name="password" maxlength="100" value="" />
			</div>
		</div>
	<?php	
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'customer', 'address-page', Text::_('ESHOP_ADDRESS', true));
	
	if ($this->item->id > 0)
	{
	    if (EShopHelper::isJoomla4())
	    {
	        $onClick = "document.getElementById('newAddressFormModal').open();";
	    }
	    else
	    {
	        $onClick = "jQuery( '#newAddressFormModal' ).modal('show');";
	    }
	    ?>
	    <div class="new-adddress-btn">
    		<input class="btn btn-small btn-primary" type="button" name="new_address" value="<?php echo Text::_('ESHOP_BTN_NEW_ADDRESS')?>" onclick="<?php echo $onClick; ?>">
    	</div>
	    <?php
	}
	?>
    	
	<?php
	if (count($this->addresses) > 0)
	{
	    $forms = $this->forms;
	    
	    echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'address', array('active' => 'general-page'));
	    
	    for ($i = 0; $n = count($this->addresses), $i < $n; $i++)
	    {
	        $address   = $this->addresses[$i];
	        $form      = $forms[$i];
	        echo HTMLHelper::_($tabApiPrefix . 'addTab', 'address', 'address-' . ($i + 1), Text::_('ESHOP_ADDRESS') . ' ' . ($i + 1));
	        ?>
	        <input type="hidden" name="addresses[]" value="<?php echo $address->id; ?>" />
	        <div class="form-group form-row">
				<label class="col-md-3 form-control-label"><?php echo Text::_('ESHOP_REMOVE_ADDRESS') . ' ' . ($i + 1); ?></label>
				<div class="col-md-9 docs-input-sizes">
					<?php echo HTMLHelper::_('select.genericlist', $options, 'remove_address[]', 'class="input-medium form-select"', 'value', 'text', 0); ?>
				</div>
			</div>
	        <?php
    		echo $form->render();
    		
	        echo HTMLHelper::_($tabApiPrefix . 'endTab');
	    }
	    
	    echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	}
	
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
	echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	?>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<input type="hidden" name="customer_id" value="<?php echo $this->item->customer_id; ?>" />
	<input type="hidden" name="task" value="" />
	<?php
	// Load the batch processing form
	echo HTMLHelper::_(
		'bootstrap.renderModal',
		'newAddressFormModal',
		array(
			'title' => Text::_('ESHOP_TITLE_NEW_ADDRESS'),
			'footer' => $this->loadTemplate('new_address_footer')
		),
		$this->loadTemplate('new_address_body')
	);
	?>
</form>