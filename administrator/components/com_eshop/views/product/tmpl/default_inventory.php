<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$rootUri = Uri::root();
?>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_manage_stock', Text::_('ESHOP_PRODUCT_MANAGE_STOCK'), Text::_('ESHOP_PRODUCT_MANAGE_STOCK_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_manage_stock']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_stock_display', Text::_('ESHOP_PRODUCT_DISPLAY_STOCK'), Text::_('ESHOP_PRODUCT_DISPLAY_STOCK_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_stock_display']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_show_availability', Text::_('ESHOP_PRODUCT_SHOW_AVAILABILITY'), Text::_('ESHOP_PRODUCT_SHOW_AVAILABILITY_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_show_availability']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_stock_warning', Text::_('ESHOP_PRODUCT_STOCK_WARNING'), Text::_('ESHOP_PRODUCT_STOCK_WARNING_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_stock_warning']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_quantity', Text::_('ESHOP_PRODUCT_QUANTITY')); ?>
	</div>
	<div class="controls">
		<input class="input-small form-control" type="text" name="product_quantity" id="product_quantity" size="" maxlength="250" value="<?php echo $this->item->product_quantity ? $this->item->product_quantity : 0 ; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_threshold', Text::_('ESHOP_PRODUCT_THRESHOLD'), Text::_('ESHOP_PRODUCT_THRESHOLD_HELP')); ?>
	</div>
	<div class="controls">
		<input class="input-small form-control" type="text" name="product_threshold" id="product_threshold" size="" maxlength="250" value="<?php echo $this->item->product_threshold ? $this->item->product_threshold : 0; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_stock_checkout', Text::_('ESHOP_PRODUCT_STOCK_CHECKOUT'), Text::_('ESHOP_PRODUCT_STOCK_CHECKOUT_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_stock_checkout']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_stock_status_id', Text::_('ESHOP_OUT_OF_STOCK_STATUS'), Text::_('ESHOP_OUT_OF_STOCK_STATUS_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_stock_status_id']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_inventory_global', Text::_('ESHOP_PRODUCT_INVENTORY_GLOBAL'), Text::_('ESHOP_PRODUCT_INVENTORY_GLOBAL_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_inventory_global']; ?>
	</div>
</div>