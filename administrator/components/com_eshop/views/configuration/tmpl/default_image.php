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
use Joomla\CMS\Uri\Uri;

?>

<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('view_image', Text::_('ESHOP_CONFIG_VIEW_IMAGE'), Text::_('ESHOP_CONFIG_VIEW_IMAGE_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['view_image']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('zoom_scale', Text::_('ESHOP_CONFIG_ZOOM_SCALE'), Text::_('ESHOP_CONFIG_ZOOM_SCALE_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['zoom_scale']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_RESIZED_IMAGE_BACKGROUND'); ?>
	</div>
	<div class="controls">
		<input type="text" name="resized_image_background" class="input-small form-control color {required:false}" value="<?php echo $this->config->resized_image_background ?? 'FFFFFF'; ?>" size="5" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('product_image_rollover', Text::_('ESHOP_PRODUCT_IMAGE_ROLLOVER'), Text::_('ESHOP_PRODUCT_IMAGE_ROLLOVER_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['product_image_rollover']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('default_category_image', Text::_('ESHOP_DEFAULT_CATEGORY_IMAGE'), Text::_('ESHOP_DEFAULT_CATEGORY_IMAGE_HELP')); ?>
	</div>
	<div class="controls">
		<input type="text" name="default_category_image" class="input-xlarge form-control" value="<?php echo $this->config->default_category_image ?? 'no-image.png'; ?>" size="5" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('default_manufacturer_image', Text::_('ESHOP_DEFAULT_MANUFACTURER_IMAGE'), Text::_('ESHOP_DEFAULT_MANUFACTURER_IMAGE_HELP')); ?>
	</div>
	<div class="controls">
		<input type="text" name="default_manufacturer_image" class="input-xlarge form-control" value="<?php echo $this->config->default_manufacturer_image ?? 'no-image.png'; ?>" size="5" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('default_product_image', Text::_('ESHOP_DEFAULT_PRODUCT_IMAGE'), Text::_('ESHOP_DEFAULT_PRODUCT_IMAGE_HELP')); ?>
	</div>
	<div class="controls">
		<input type="text" name="default_product_image" class="input-xlarge form-control" value="<?php echo $this->config->default_product_image ?? 'no-image.png'; ?>" size="5" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('number_images_to_show', Text::_('ESHOP_NUMBER_IMAGES_TO_SHOW'), Text::_('ESHOP_NUMBER_IMAGES_TO_SHOW_HELP')); ?>
	</div>
	<div class="controls">
		<input type="text" name="number_images_to_show" class="input-xlarge form-control" value="<?php echo $this->config->number_images_to_show ?? '3'; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('number_images_to_scroll', Text::_('ESHOP_NUMBER_IMAGES_TO_SCROLL'), Text::_('ESHOP_NUMBER_IMAGES_TO_SCROLL_HELP')); ?>
	</div>
	<div class="controls">
		<input type="text" name="number_images_to_scroll" class="input-xlarge form-control" value="<?php echo $this->config->number_images_to_scroll ?? '1'; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('slide_speed', Text::_('ESHOP_SLIDE_SPEED'), Text::_('ESHOP_SLIDE_SPEED_HELP')); ?>
	</div>
	<div class="controls">
		<input type="text" name="slide_speed" class="input-xlarge form-control" value="<?php echo $this->config->slide_speed ?? '300'; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('autoplay', Text::_('ESHOP_AUTOPLAY'), Text::_('ESHOP_AUTOPLAY_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['autoplay']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('autoplay_speed', Text::_('ESHOP_AUTOPLAY_SPEED'), Text::_('ESHOP_AUTOPLAY_SPEED_HELP')); ?>
	</div>
	<div class="controls">
		<input type="text" name="autoplay_speed" class="input-xlarge form-control" value="<?php echo $this->config->autoplay_speed ?? '3000'; ?>" />
	</div>
</div>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_IMAGE_SIZE_FUNCTION'); ?></legend>
	<div class="control-group"><?php echo Text::_('ESHOP_CONFIG_IMAGE_SIZE_FUNCTION_HELP'); ?></div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_CATEGORY_IMAGE_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_category_width; ?>" name="image_category_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_category_height; ?>" name="image_category_height" class="input-mini form-control" />
			<?php echo $this->lists['category_image_size_function']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_MANUFACTURER_IMAGE_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_manufacturer_width; ?>" name="image_manufacturer_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_manufacturer_height; ?>" name="image_manufacturer_height" class="input-mini form-control" />
			<?php echo $this->lists['manufacturer_image_size_function']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_PRODUCT_IMAGE_THUMB_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_thumb_width; ?>" name="image_thumb_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_thumb_height; ?>" name="image_thumb_height" class="input-mini form-control" />
			<?php echo $this->lists['thumb_image_size_function']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_PRODUCT_IMAGE_POPUP_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_popup_width; ?>" name="image_popup_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_popup_height; ?>" name="image_popup_height" class="input-mini form-control" />
			<?php echo $this->lists['popup_image_size_function']; ?>
		</div>
	</div>	
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_PRODUCT_IMAGE_LIST_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_list_width; ?>" name="image_list_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_list_height; ?>" name="image_list_height" class="input-mini form-control" />
			<?php echo $this->lists['list_image_size_function']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_ADDITIONAL_PRODUCT_IMAGE_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_additional_width; ?>" name="image_additional_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_additional_height; ?>" name="image_additional_height" class="input-mini form-control" />
			<?php echo $this->lists['additional_image_size_function']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_RELATED_PRODUCT_IMAGE_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_related_width; ?>" name="image_related_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_related_height; ?>" name="image_related_height" class="input-mini form-control" />
			<?php echo $this->lists['related_image_size_function']; ?>
		</div>
	</div>	
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_COMPARE_IMAGE_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_compare_width; ?>" name="image_compare_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_compare_height; ?>" name="image_compare_height" class="input-mini form-control" />
			<?php echo $this->lists['compare_image_size_function']; ?>
		</div>
	</div>	
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_WISH_LIST_IMAGE_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_wishlist_width; ?>" name="image_wishlist_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_wishlist_height; ?>" name="image_wishlist_height" class="input-mini form-control" />
			<?php echo $this->lists['wishlist_image_size_function']; ?>
		</div>
	</div>	
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_CART_IMAGE_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_cart_width; ?>" name="image_cart_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_cart_height; ?>" name="image_cart_height" class="input-mini form-control" />
			<?php echo $this->lists['cart_image_size_function']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_LABEL_IMAGE_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_label_width; ?>" name="image_label_width" class="input-mini form-control" />
				x
			<input type="text" value="<?php echo $this->config->image_label_height; ?>" name="image_label_height" class="input-mini form-control" />
			<?php echo $this->lists['label_image_size_function']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo  Text::_('ESHOP_CONFIG_OPTION_IMAGE_SIZE'); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->image_option_width; ?>" name="image_option_width" class="input-mini form-control" />
				x	
			<input type="text" value="<?php echo $this->config->image_option_height; ?>" name="image_option_height" class="input-mini form-control" />
			<?php echo $this->lists['option_image_size_function']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('recreate_watermark_images', Text::_('ESHOP_RECREATE_WATERMARK_IMAGES'), Text::_('ESHOP_RECREATE_WATERMARK_IMAGES_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['recreate_watermark_images']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('product_use_image_watermarks', Text::_('ESHOP_PRODUCT_USE_WATERMARKS')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['product_use_image_watermarks']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('category_use_image_watermarks', Text::_('ESHOP_CATEGORY_USE_WATERMARKS')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['category_use_image_watermarks']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('manufacture_use_image_watermarks', Text::_('ESHOP_MANUFACTURE_USE_WATERMARKS')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['manufacture_use_image_watermarks']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('images_opacity', Text::_('ESHOP_CONFIG_WATERMARK_IMAGES_OPACITY')); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->images_opacity; ?>" name="images_opacity" id="images_opacity" class="input-mini form-control" /> %
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('watermark_position', Text::_('ESHOP_CONFIG_WATERMARK_POSITION')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['watermark_position']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('watermark_type', Text::_('ESHOP_CONFIG_WATERMARK_TYPE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['watermark_type']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('watermark_font', Text::_('ESHOP_CONFIG_WATERMARK_FONT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['watermark_font']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('watermark_fontsize', Text::_('ESHOP_CONFIG_WATERMARK_FONT_SIZE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['watermark_fontsize']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('watermark_color', Text::_('ESHOP_CONFIG_WATERMARK_COLOR')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['watermark_color']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('custom_text', Text::_('ESHOP_CONFIG_WATERMARK_CUSTOM_TEXT')); ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $this->config->custom_text; ?>" id="custom_text" name="custom_text" class="form-control" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('watermark_photo_file', Text::_('ESHOP_CONFIG_WATERMARK_PHOTO')); ?>
		</div>
		<div class="controls">
			<?php
			if (isset($this->config->watermark_photo) && $this->config->watermark_photo != "")
			{
				if (file_exists(JPATH_ROOT . "/images/" . $this->config->watermark_photo))
				{
					?>
					<img src="<?php echo Uri::root(); ?>images/<?php echo $this->config->watermark_photo; ?>" />
					<?php
				}
				?>
				<div style="clear:both;"></div>
				<input type="hidden" name="watermark_photo" id="watermark_photo" value="<?php echo $this->config->watermark_photo; ?>" />
				<?php
			}
			?>
			<input type="file" name="watermark_photo_file" id="watermark_photo_file" />
		</div>
	</div>
</fieldset>