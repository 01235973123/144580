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

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

$bootstrapHelper = EShopHtmlHelper::getAdminBootstrapHelper();
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$span7Class      = $bootstrapHelper->getClassMapping('span7');
$span5Class      = $bootstrapHelper->getClassMapping('span5');
?>
<script type="text/javascript">	
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'payment.cancel') {
			Joomla.submitform(pressbutton, form);
			return;				
		} else {
			//Validate the entered data before submitting													
			Joomla.submitform(pressbutton, form);
		}								
	}		
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<div class="<?php echo $rowFluidClass; ?>">
		<div class="<?php echo $span7Class; ?>">
			<fieldset class="form-horizontal options-form">
				<legend><?php echo Text::_('ESHOP_PAYMENT_PLUGIN_DETAILS'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('ESHOP_NAME'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->name; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('ESHOP_TITLE'); ?>
					</div>
					<div class="controls">
						<input class="form-control" type="text" name="title" id="title" size="40" maxlength="250" value="<?php echo $this->item->title;?>" />
					</div>
				</div>					
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('ESHOP_AUTHOR'); ?>
					</div>
					<div class="controls">
						<input class="form-control" type="text" name="author" id="author" size="40" maxlength="250" value="<?php echo $this->item->author;?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('ESHOP_CREATION_DATE'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->creation_date; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('ESHOP_COPYRIGHT'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->copyright; ?>
					</div>
				</div>	
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('ESHOP_LICENSE'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->license; ?>
					</div>
				</div>							
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('ESHOP_AUTHOR_EMAIL'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->author_email; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('ESHOP_AUTHOR_URL'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->author_url; ?>
					</div>
				</div>				
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('ESHOP_VERSION'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->version; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('ESHOP_DESCRIPTION'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->description; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('ESHOP_PUBLISHED'); ?>
					</div>
					<div class="controls">
						<?php					
							echo $this->lists['published'];					
						?>						
					</div>
				</div>
			</fieldset>				
		</div>						
		<div class="<?php echo $span5Class; ?>">
			<fieldset class="form-horizontal options-form">
				<legend><?php echo Text::_('ESHOP_PAYMENT_PLUGIN_PARAMETERS'); ?></legend>
				<?php
					foreach ($this->form->getFieldset('basic') as $field)
					{
    					echo $field->renderField();
					}
				?>
			</fieldset>
		</div>
	</div>
	<div class="clearfix"></div>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<input type="hidden" name="task" value="" />
</form>