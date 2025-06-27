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

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('invoice_enable', Text::_('ESHOP_CONFIG_INVOICE_ENABLE'), Text::_('ESHOP_CONFIG_INVOICE_ENABLE_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['invoice_enable']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('invoice_status_ids', Text::_('ESHOP_CONFIG_INVOICE_STATUS_IDS'), Text::_('ESHOP_CONFIG_INVOICE_STATUS_IDS_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['invoice_status_ids']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('always_generate_invoice', Text::_('ESHOP_CONFIG_ALWAYS_GENERATE_INVOICE'), Text::_('ESHOP_CONFIG_ALWAYS_GENERATE_INVOICE_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['always_generate_invoice']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('invoice_start_number', Text::_('ESHOP_CONFIG_INVOICE_START_NUMBER'), Text::_('ESHOP_CONFIG_INVOICE_START_NUMBER_HELP')); ?>
	</div>
	<div class="controls">
		<input class="input-xlarge form-control" type="text" name="invoice_start_number" id="invoice_start_number" size="15" value="<?php echo $this->config->invoice_start_number ?? '1'; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('reset_invoice_number', Text::_('ESHOP_CONFIG_RESET_INVOICE_NUMBER'), Text::_('ESHOP_CONFIG_RESET_INVOICE_NUMBER_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['reset_invoice_number']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('invoice_prefix', Text::_('ESHOP_CONFIG_INVOICE_PREFIX'), Text::_('ESHOP_CONFIG_INVOICE_PREFIX_HELP')); ?>
	</div>
	<div class="controls">
		<input class="input-xlarge form-control" type="text" name="invoice_prefix" id="invoice_prefix" size="15" value="<?php echo $this->config->invoice_prefix ?? 'INV[YEAR]'; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('invoice_number_length', Text::_('ESHOP_CONFIG_INVOICE_NUMBER_LENGTH'), Text::_('ESHOP_CONFIG_INVOICE_NUMBER_LENGTH_HELP')); ?>
	</div>
	<div class="controls">
		<input class="input-xlarge form-control" type="text" name="invoice_number_length" id="invoice_number_length" size="15" value="<?php echo $this->config->invoice_number_length ?? '5'; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('send_invoice_to_customer', Text::_('ESHOP_CONFIG_INVOICE_SEND_TO_CUSTOMER'), Text::_('ESHOP_CONFIG_INVOICE_SEND_TO_CUSTOMER_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['send_invoice_to_customer']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('send_invoice_to_admin', Text::_('ESHOP_CONFIG_INVOICE_SEND_TO_ADMIN'), Text::_('ESHOP_CONFIG_INVOICE_SEND_TO_ADMIN_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['send_invoice_to_admin']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('pdf_font', Text::_('ESHOP_CONFIG_INVOICE_PDF_FONT'), Text::_('ESHOP_CONFIG_INVOICE_PDF_FONT_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['pdf_font']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('pdf_image_path', Text::_('ESHOP_CONFIG_PDF_IMAGE_PATH'), Text::_('ESHOP_CONFIG_PDF_IMAGE_PATH_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['pdf_image_path']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('pdf_font_size', Text::_('ESHOP_CONFIG_INVOICE_PDF_FONT_SIZE'), Text::_('ESHOP_CONFIG_INVOICE_PDF_FONT_SIZE_HELP')); ?>
	</div>
	<div class="controls">
		<input class="input-xlarge form-control" type="text" name="pdf_font_size" id="pdf_font_size" size="15" value="<?php echo $this->config->pdf_font_size ?? '8'; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_fields_display', Text::_('ESHOP_CONFIG_PRODUCT_FIELDS_DISPLAY'), Text::_('ESHOP_CONFIG_PRODUCT_FIELDS_DISPLAY_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_fields_display']; ?>
	</div>
</div>
	