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

use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$rootUri = Uri::root();
?>
<div class="control-group">
	<div class="control-label">
		<span class="required">*</span>
		<?php echo Text::_('ESHOP_PRODUCT_SKU'); ?>
	</div>
	<div class="controls">
		<input class="input-xlarge form-control" type="text" name="product_sku" id="product_sku" size="" maxlength="250" value="<?php echo $this->item->product_sku; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_MANUFACTURER'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['manufacturer']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('ESHOP_IMAGE'); ?>
	</div>
	<div class="controls">
		<input type="file" class="input-large form-control" accept="image/*" name="product_image" />
		<?php
		if (is_file(JPATH_ROOT.'/media/com_eshop/products/'.$this->item->product_image))
		{
			$viewImage = File::stripExt($this->item->product_image).'-100x100.'.EShopHelper::getFileExt($this->item->product_image);

			if (is_file(JPATH_ROOT.'/media/com_eshop/products/resized/'.$viewImage))
			{
			?>
				<img src="<?php echo $rootUri.'media/com_eshop/products/resized/'.$viewImage; ?>" />
			<?php
			}
			else
			{
			?>
				<img src="<?php echo $rootUri.'media/com_eshop/products/'.$this->item->product_image; ?>" width="100" height="100" />
			<?php
			}
			?>
			<div class="form-check">
    			<input type="checkbox" class="form-check-input" name="remove_image" id="remove_image" value="1" />
    			<label class="form-check-label" for="remove_image">
    				<?php echo Text::_('ESHOP_REMOVE_IMAGE'); ?>
    			</label>
			</div>
			<?php
		}
		?>
	</div>
</div>
<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_SELECT_AN_IMAGE'); ?>
			<span class="help"><?php echo Text::_('ESHOP_SELECT_AN_IMAGE_HELP'); ?></span>
		</div>
		<div class="controls">
			<?php echo $this->lists['existed_image']; ?>
		</div>
	</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_MAIN_CATEGORY'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['main_category_id']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_ADDITIONAL_CATEGORIES'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['category_id']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_cost', Text::_('ESHOP_PRODUCT_COST'), Text::_('ESHOP_PRODUCT_COST_HELP')); ?>
	</div>
	<div class="controls">
		<input class="input-medium form-control" type="text" name="product_cost" id="product_cost" size="" maxlength="250" value="<?php echo $this->item->product_cost ? $this->item->product_cost : 0; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_price', Text::_('ESHOP_PRODUCT_PRICE'), Text::_('ESHOP_PRODUCT_PRICE_HELP')); ?>
	</div>
	<div class="controls">
		<input class="input-medium form-control" type="text" name="product_price" id="product_price" size="" maxlength="250" value="<?php echo $this->item->product_price ? $this->item->product_price : 0; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_CALL_FOR_PRICE'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_call_for_price']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_LENGTH'); ?>
	</div>
	<div class="controls">
		<input class="input-medium form-control" type="text" name="product_length" id="product_length" size="" maxlength="250" value="<?php echo $this->item->product_length ? $this->item->product_length: 0; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_WIDTH'); ?>
	</div>
	<div class="controls">
		<input class="input-medium form-control" type="text" name="product_width" id="product_width" size="" maxlength="250" value="<?php echo $this->item->product_width ? $this->item->product_width : 0; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_HEIGHT'); ?>
	</div>
	<div class="controls">
		<input class="input-medium form-control" type="text" name="product_height" id="product_height" size="" maxlength="250" value="<?php echo $this->item->product_height ? $this->item->product_height : 0; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_LENGTH_UNIT'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_length_id']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_WEIGHT'); ?>
	</div>
	<div class="controls">
		<input class="input-medium form-control" type="text" name="product_weight" id="product_weight" size="" maxlength="250" value="<?php echo $this->item->product_weight ? $this->item->product_weight : 0; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_WEIGHT_UNIT'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_weight_id']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_TAX'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['taxclasses']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_MINIMUM_QUANTITY'); ?>
	</div>
	<div class="controls">
		<input class="input-medium form-control" type="text" name="product_minimum_quantity" id="product_minimum_quantity" size="" maxlength="250" value="<?php echo $this->item->product_minimum_quantity ? $this->item->product_minimum_quantity : 0; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_MAXIMUM_QUANTITY'); ?>
	</div>
	<div class="controls">
		<input class="input-medium form-control" type="text" name="product_maximum_quantity" id="product_maximum_quantity" size="" maxlength="250" value="<?php echo $this->item->product_maximum_quantity ? $this->item->product_maximum_quantity : 0; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_DOWNLOADS'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_downloads']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_REQUIRE_SHIPPING'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_shipping']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_SHIPPING_COST'); ?>
		<span class="help"><?php echo Text::_('ESHOP_PRODUCT_SHIPPING_COST_HELP'); ?></span>
	</div>
	<div class="controls">
		<input class="input-medium form-control" type="text" name="product_shipping_cost" id="product_shipping_cost" size="" maxlength="250" value="<?php echo $this->item->product_shipping_cost ? $this->item->product_shipping_cost : 0; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_SHIPPING_COST_GEOZONES'); ?>
		<span class="help"><?php echo Text::_('ESHOP_PRODUCT_SHIPPING_COST_GEOZONES_HELP'); ?></span>
	</div>
	<div class="controls">
		<input class="form-control" type="text" name="product_shipping_cost_geozones" id="product_shipping_cost_geozones" size="" maxlength="250" value="<?php echo $this->item->product_shipping_cost_geozones; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_RELATED_PRODUCTS'); ?>
		<span class="help"><?php echo Text::_('ESHOP_RELATED_PRODUCTS_HELP'); ?></span>
	</div>
	<div class="controls">
		<?php echo $this->lists['related_products']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_RELATE_PRODUCT_TO_CATEGORY'); ?>
		<span class="help"><?php echo Text::_('ESHOP_RELATE_PRODUCT_TO_CATEGORY_HELP'); ?></span>
	</div>
	<div class="controls">
		<?php echo $this->lists['relate_product_to_category']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_AVAILABLE_DATE'); ?>
	</div>
	<div class="controls">
		<?php echo HTMLHelper::_('calendar', $this->item->product_available_date, 'product_available_date', 'product_available_date', '%Y-%m-%d %H:%M', ['showTime' => true]); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_END_DATE'); ?>
	</div>
	<div class="controls">
		<?php echo HTMLHelper::_('calendar', $this->item->product_end_date, 'product_end_date', 'product_end_date', '%Y-%m-%d %H:%M', ['showTime' => true]); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_FEATURED'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['featured']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_CUSTOMERGROUPS'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_customergroups']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_CART_MODE'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_cart_mode']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_PRODUCT_QUOTE_MODE'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_quote_mode']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_languages', Text::_('ESHOP_PRODUCT_LANGUAGES'), Text::_('ESHOP_PRODUCT_LANGUAGES_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_languages']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('ESHOP_PRODUCT_TAGS'); ?>
	</div>
	<div class="controls">
		<input class="form-select" type="text" name="product_tags" id="product_tags" size="50" value="<?php echo $this->item->product_tags; ?>" />
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
