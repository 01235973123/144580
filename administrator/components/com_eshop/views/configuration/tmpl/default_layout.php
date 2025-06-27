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
use Joomla\CMS\Language\Text;

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
?>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_LAYOUT_GENERAL'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('theme', Text::_('ESHOP_CONFIG_THEME')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['theme']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('products_filter_layout', Text::_('ESHOP_CONFIG_PRODUCTS_FILTER_LAYOUT'), Text::_('ESHOP_CONFIG_PRODUCTS_FILTER_LAYOUT_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['products_filter_layout']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('products_filter_visible_items', Text::_('ESHOP_CONFIG_PRODUCTS_FILTER_VISIBLE_ITEMS'), Text::_('ESHOP_CONFIG_PRODUCTS_FILTER_VISIBLE_ITEMS_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="products_filter_visible_items" id="products_filter_visible_items"  value="<?php echo $this->config->products_filter_visible_items ?? '0'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('cart_popout', Text::_('ESHOP_CONFIG_CART_NOTIFY'), Text::_('ESHOP_CONFIG_CART_NOTIFY_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['cart_popout']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('update_cart_function', Text::_('ESHOP_CONFIG_UPDATE_CART_FUNCTION'), Text::_('ESHOP_CONFIG_UPDATE_CART_FUNCTION_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['update_cart_function']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('update_quote_function', Text::_('ESHOP_CONFIG_UPDATE_QUOTE_FUNCTION'), Text::_('ESHOP_CONFIG_UPDATE_QUOTE_FUNCTION_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['update_quote_function']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('load_jquery_framework', Text::_('ESHOP_CONFIG_LOAD_JQUERY_FRAMEWORK')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['load_jquery_framework']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('load_bootstrap_css', Text::_('ESHOP_CONFIG_LOAD_BOOTSTRAP_CSS')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['load_bootstrap_css']; ?>
		</div>
	</div>
	<div class="control-group">
			<div class="control-label">
				<?php echo EShopHtmlHelper::getFieldLabel('twitter_bootstrap_version', Text::_('ESHOP_TWITTER_BOOTSTRAP_VERSION'), Text::_('ESHOP_TWITTER_BOOTSTRAP_VERSION_EXPLAIN')); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['twitter_bootstrap_version'];?>
			</div>
		</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('date_format', Text::_('ESHOP_CONFIG_DATE_FORMAT'), Text::_('ESHOP_CONFIG_DATE_FORMAT_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-large form-control" type="text" name="date_format" id="date_format"  value="<?php echo $this->config->date_format ?? 'm-d-Y'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_categories_nav', Text::_('ESHOP_CONFIG_SHOW_CATEGORIES_NAVIGATION')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_categories_nav']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_products_nav', Text::_('ESHOP_CONFIG_SHOW_PRODUCTS_NAVIGATION')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_products_nav']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('shipping_address_format', Text::_('ESHOP_CONFIG_SHIPPING_ADDRESS_FORMAT'), Text::_('ESHOP_CONFIG_SHIPPING_ADDRESS_FORMAT_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'shipping_address_format', $this->config->shipping_address_format ?? '[SHIPPING_FIRSTNAME] [SHIPPING_LASTNAME]<br /> [SHIPPING_ADDRESS_1], [SHIPPING_ADDRESS_2]<br /> [SHIPPING_CITY], [SHIPPING_POSTCODE] [SHIPPING_ZONE_NAME]<br /> [SHIPPING_EMAIL]<br /> [SHIPPING_TELEPHONE]<br /> [SHIPPING_FAX]', '100%', '250', '75', '10' ); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('payment_address_format', Text::_('ESHOP_CONFIG_PAYMENT_ADDRESS_FORMAT'), Text::_('ESHOP_CONFIG_PAYMENT_ADDRESS_FORMAT_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'payment_address_format', $this->config->payment_address_format ?? '[PAYMENT_FIRSTNAME] [PAYMENT_LASTNAME]<br /> [PAYMENT_ADDRESS_1], [PAYMENT_ADDRESS_2]<br /> [PAYMENT_CITY], [PAYMENT_POSTCODE] [PAYMENT_ZONE_NAME]<br /> [PAYMENT_EMAIL]<br /> [PAYMENT_TELEPHONE]<br /> [PAYMENT_FAX]', '100%', '250', '75', '10' ); ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_PRODUCT_PAGE'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('grid_ratio_image_info', Text::_('ESHOP_CONFIG_GRID_RATIO_IMAGE_INFO'), Text::_('ESHOP_CONFIG_GRID_RATIO_IMAGE_INFO_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['grid_ratio_image_info']; ?>
		</div>
	</div>	
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_manufacturer', Text::_('ESHOP_CONFIG_SHOW_MANUFACTURER'), Text::_('ESHOP_CONFIG_SHOW_MANUFACTURER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_manufacturer']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_sku', Text::_('ESHOP_CONFIG_SHOW_SKU'), Text::_('ESHOP_CONFIG_SHOW_SKU_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_sku']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_availability', Text::_('ESHOP_CONFIG_SHOW_AVAILABILITY'), Text::_('ESHOP_CONFIG_SHOW_AVAILABILITY_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_availability']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_product_weight', Text::_('ESHOP_CONFIG_SHOW_PRODUCT_WEIGHT'), Text::_('ESHOP_CONFIG_SHOW_PRODUCT_WEIGHT_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_product_weight']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_product_dimensions', Text::_('ESHOP_CONFIG_SHOW_PRODUCT_DIMENSIONS'), Text::_('ESHOP_CONFIG_SHOW_PRODUCT_DIMENSIONS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_product_dimensions']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_product_tags', Text::_('ESHOP_CONFIG_SHOW_PRODUCT_TAGS'), Text::_('ESHOP_CONFIG_SHOW_PRODUCT_TAGS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_product_tags']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_product_attachments', Text::_('ESHOP_CONFIG_SHOW_PRODUCT_ATTACHMENTS'), Text::_('ESHOP_CONFIG_SHOW_PRODUCT_ATTACHMENTS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_product_attachments']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_specification', Text::_('ESHOP_CONFIG_SHOW_SPECIFICATION'), Text::_('ESHOP_CONFIG_SHOW_SPECIFICATION_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_specification']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_related_products', Text::_('ESHOP_CONFIG_SHOW_RELATED_PRODUCTS'), Text::_('ESHOP_CONFIG_SHOW_RELATED_PRODUCTS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_related_products']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_quantity_box_in_product_page', Text::_('ESHOP_CONFIG_SHOW_QUANTITY_BOX_IN_PRODUCT_PAGE'), Text::_('ESHOP_CONFIG_SHOW_QUANTITY_BOX_IN_PRODUCT_PAGE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_quantity_box_in_product_page']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_short_desc_in_product_page', Text::_('ESHOP_CONFIG_SHOW_SHORT_DESC_IN_PRODUCT_PAGE'), Text::_('ESHOP_CONFIG_SHOW_SHORT_DESC_IN_PRODUCT_PAGE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_short_desc_in_product_page']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_CATEGORY_PAGE'); ?></legend>	
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_category_image', Text::_('ESHOP_CONFIG_SHOW_CATEGORY_IMAGE'), Text::_('ESHOP_CONFIG_SHOW_CATEGORY_IMAGE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_category_image']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_category_desc', Text::_('ESHOP_CONFIG_SHOW_CATEGORY_DESC'), Text::_('ESHOP_CONFIG_SHOW_CATEGORY_DESC_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_category_desc']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_products_in_all_levels', Text::_('ESHOP_CONFIG_SHOW_PRODUCTS_IN_ALL_LEVELS'), Text::_('ESHOP_CONFIG_SHOW_PRODUCTS_IN_ALL_LEVELS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_products_in_all_levels']; ?>
		</div>
	</div>
	<b><?php echo Text::_('ESHOP_CONFIG_CATEGORY_DEFAULT_LAYOUT'); ?></b>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_sub_categories', Text::_('ESHOP_CONFIG_SHOW_SUB_CATEGORIES'), Text::_('ESHOP_CONFIG_SHOW_SUB_CATEGORIES_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_sub_categories']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('sub_categories_layout', Text::_('ESHOP_CONFIG_SUB_CATEGORIES_LAYOUT'), Text::_('ESHOP_CONFIG_SUB_CATEGORIES_LAYOUT_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['sub_categories_layout']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('default_products_layout', Text::_('ESHOP_CONFIG_DEFAULT_PRODUCTS_LAYOUT'), Text::_('ESHOP_CONFIG_DEFAULT_PRODUCTS_LAYOUT_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['default_products_layout']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_quantity_box', Text::_('ESHOP_CONFIG_SHOW_QUANTITY_BOX'), Text::_('ESHOP_CONFIG_SHOW_QUANTITY_BOX_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_quantity_box']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_product_attributes', Text::_('ESHOP_CONFIG_SHOW_PRODUCT_ATTRIBUTES'), Text::_('ESHOP_CONFIG_SHOW_PRODUCT_ATTRIBUTES_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_product_attributes']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('list_show_availability', Text::_('ESHOP_CONFIG_SHOW_LIST_AVAILABILITY'), Text::_('ESHOP_CONFIG_SHOW_LIST_AVAILABILITY_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['list_show_availability']; ?>
		</div>
	</div>
	<b><?php echo Text::_('ESHOP_CONFIG_CATEGORY_TABLE_LAYOUT'); ?></b>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('table_show_image', Text::_('ESHOP_CONFIG_TABLE_SHOW_IMAGE'), Text::_('ESHOP_CONFIG_TABLE_SHOW_IMAGE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['table_show_image']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('table_show_short_description', Text::_('ESHOP_CONFIG_TABLE_SHOW_SHORT_DESCRIPTION'), Text::_('ESHOP_CONFIG_TABLE_SHOW_SHORT_DESCRIPTION_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['table_show_short_description']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('table_show_category', Text::_('ESHOP_CONFIG_TABLE_SHOW_CATEGORY'), Text::_('ESHOP_CONFIG_TABLE_SHOW_CATEGORY_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['table_show_category']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('table_show_manufacturer', Text::_('ESHOP_CONFIG_TABLE_SHOW_MANUFACTURER'), Text::_('ESHOP_CONFIG_TABLE_SHOW_MANUFACTURER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['table_show_manufacturer']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('table_show_price', Text::_('ESHOP_CONFIG_TABLE_SHOW_PRICE'), Text::_('ESHOP_CONFIG_TABLE_SHOW_PRICE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['table_show_price']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('table_show_availability', Text::_('ESHOP_CONFIG_TABLE_SHOW_AVAILABILITY'), Text::_('ESHOP_CONFIG_TABLE_SHOW_AVAILABILITY_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['table_show_availability']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('table_show_product_attributes', Text::_('ESHOP_CONFIG_TABLE_SHOW_PRODUCT_ATTRIBUTES'), Text::_('ESHOP_CONFIG_TABLE_SHOW_PRODUCT_ATTRIBUTES_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['table_show_product_attributes']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('table_show_quantity_box', Text::_('ESHOP_CONFIG_TABLE_SHOW_QUANTITY_BOX'), Text::_('ESHOP_CONFIG_TABLE_SHOW_QUANTITY_BOX_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['table_show_quantity_box']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('table_show_actions', Text::_('ESHOP_CONFIG_TABLE_SHOW_ACTIONS'), Text::_('ESHOP_CONFIG_TABLE_SHOW_ACTIONS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['table_show_actions']; ?>
		</div>
	</div>
	<?php
	if (EShopHelper::getConfigValue('product_custom_fields'))
	{
	    $xml          = simplexml_load_file(JPATH_ROOT . '/components/com_eshop/fields.xml');
	    $fields       = $xml->fields->fieldset->children();
	    
	    foreach ($fields as $field)
	    {
	        $name = $field->attributes()->name;
	        $label = Text::_($field->attributes()->label);
	        ?>
            <div class="control-group">
                <div class="control-label">
                    <?php echo 'Show '. $label; ?>
                </div>
                <div class="controls">
                    <?php echo $this->lists['table_show_'. $name]; ?>
                </div>
            </div>
            <?php
        }
	}
	?>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_COMPARE_PAGE'); ?></legend>	
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_image', Text::_('ESHOP_CONFIG_COMPARE_IMAGE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_image']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_price', Text::_('ESHOP_CONFIG_COMPARE_PRICE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_price']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_sku', Text::_('ESHOP_CONFIG_COMPARE_SKU')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_sku']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_manufacturer', Text::_('ESHOP_CONFIG_COMPARE_MANUFACTURER')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_manufacturer']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_availability', Text::_('ESHOP_CONFIG_COMPARE_AVAILABILITY')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_availability']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_rating', Text::_('ESHOP_CONFIG_COMPARE_RATING')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_rating']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_short_desc', Text::_('ESHOP_CONFIG_COMPARE_SHORT_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_short_desc']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_desc', Text::_('ESHOP_CONFIG_COMPARE_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_desc']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_weight', Text::_('ESHOP_CONFIG_COMPARE_WEIGHT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_weight']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_dimensions', Text::_('ESHOP_CONFIG_COMPARE_DIMENSIONS')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_dimensions']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_attributes', Text::_('ESHOP_CONFIG_COMPARE_ATTRIBUTES')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['compare_attributes']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_CUSTOMER_PAGE'); ?></legend>	
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('customer_manage_account', Text::_('ESHOP_CONFIG_CUSTOMER_MANAGE_ACCOUNT')); ?>
		</div>
		<div class="controls">
		<?php echo $this->lists['customer_manage_account']; ?>
	</div>
    </div>
    <div class="control-group">
    	<div class="control-label">
    		<?php echo EShopHtmlHelper::getFieldLabel('customer_manage_order', Text::_('ESHOP_CONFIG_CUSTOMER_MANAGE_ORDER')); ?>
    	</div>
    	<div class="controls">
    		<?php echo $this->lists['customer_manage_order']; ?>
    	</div>
    </div>
    <div class="control-group">
    	<div class="control-label">
    		<?php echo EShopHtmlHelper::getFieldLabel('customer_manage_quote', Text::_('ESHOP_CONFIG_CUSTOMER_MANAGE_QUOTE')); ?>
    	</div>
    	<div class="controls">
    		<?php echo $this->lists['customer_manage_quote']; ?>
    	</div>
    </div>
    <div class="control-group">
    	<div class="control-label">
    		<?php echo EShopHtmlHelper::getFieldLabel('customer_manage_download', Text::_('ESHOP_CONFIG_CUSTOMER_MANAGE_DOWNLOAD')); ?>
    	</div>
    	<div class="controls">
    		<?php echo $this->lists['customer_manage_download']; ?>
    	</div>
    </div>
    <div class="control-group">
    	<div class="control-label">
    		<?php echo EShopHtmlHelper::getFieldLabel('customer_manage_address', Text::_('ESHOP_CONFIG_CUSTOMER_MANAGE_ADDRESS')); ?>
    	</div>
    	<div class="controls">
			<?php echo $this->lists['customer_manage_address']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_QUOTE_FORM_FIELDS'); ?></legend>	
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_name_published', Text::_('ESHOP_CONFIG_QUOTE_FORM_NAME_PUBLISHED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_name_published']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_name_required', Text::_('ESHOP_CONFIG_QUOTE_FORM_NAME_REQUIRED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_name_required']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_email_published', Text::_('ESHOP_CONFIG_QUOTE_FORM_EMAIL_PUBLISHED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_email_published']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_email_required', Text::_('ESHOP_CONFIG_QUOTE_FORM_EMAIL_REQUIRED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_email_required']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_company_published', Text::_('ESHOP_CONFIG_QUOTE_FORM_COMPANY_PUBLISHED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_company_published']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_company_required', Text::_('ESHOP_CONFIG_QUOTE_FORM_COMPANY_REQUIRED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_company_required']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_telephone_published', Text::_('ESHOP_CONFIG_QUOTE_FORM_TELEPHONE_PUBLISHED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_telephone_published']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_telephone_required', Text::_('ESHOP_CONFIG_QUOTE_FORM_TELEPHONE_REQUIRED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_telephone_required']; ?>
    	</div>
    </div> 
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_address_published', Text::_('ESHOP_CONFIG_QUOTE_FORM_ADDRESS_PUBLISHED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_address_published']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_address_required', Text::_('ESHOP_CONFIG_QUOTE_FORM_ADDRESS_REQUIRED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_address_required']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_city_published', Text::_('ESHOP_CONFIG_QUOTE_FORM_CITY_PUBLISHED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_city_published']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_city_required', Text::_('ESHOP_CONFIG_QUOTE_FORM_CITY_REQUIRED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_city_required']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_postcode_published', Text::_('ESHOP_CONFIG_QUOTE_FORM_POSTCODE_PUBLISHED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_postcode_published']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_postcode_required', Text::_('ESHOP_CONFIG_QUOTE_FORM_POSTCODE_REQUIRED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_postcode_required']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_country_published', Text::_('ESHOP_CONFIG_QUOTE_FORM_COUNTRY_PUBLISHED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_country_published']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_country_required', Text::_('ESHOP_CONFIG_QUOTE_FORM_COUNTRY_REQUIRED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_country_required']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_state_published', Text::_('ESHOP_CONFIG_QUOTE_FORM_STATE_PUBLISHED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_state_published']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_state_required', Text::_('ESHOP_CONFIG_QUOTE_FORM_STATE_REQUIRED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_state_required']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_message_published', Text::_('ESHOP_CONFIG_QUOTE_FORM_MESSAGE_PUBLISHED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_message_published']; ?>
    	</div>
    </div>
    <div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_form_message_required', Text::_('ESHOP_CONFIG_QUOTE_FORM_MESSAGE_REQUIRED')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['quote_form_message_required']; ?>
    	</div>
    </div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_BUTTONS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('use_button_icons', Text::_('ESHOP_CONFIG_USE_BUTTON_ICONS')); ?>
		</div>
		<div class="controls">
    		<?php echo $this->lists['use_button_icons']; ?>
    	</div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('notify_button_code', Text::_('ESHOP_CONFIG_NOTIFY_BUTTON_CODE')); ?>
		</div>
		<div class="controls">
			<textarea class="input-xxlarge form-control" rows="5" cols="40" name="notify_button_code" id="notify_button_code"><?php echo $this->config->notify_button_code; ?></textarea>					
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('wishlist_button_code', Text::_('ESHOP_CONFIG_WISHLIST_BUTTON_CODE')); ?>
		</div>
		<div class="controls">
			<textarea class="input-xxlarge form-control" rows="5" cols="40" name="wishlist_button_code" id="wishlist_button_code"><?php echo $this->config->wishlist_button_code; ?></textarea>					
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('compare_button_code', Text::_('ESHOP_CONFIG_COMPARE_BUTTON_CODE')); ?>
		</div>
		<div class="controls">
			<textarea class="input-xxlarge form-control" rows="5" cols="40" name="compare_button_code" id="compare_button_code"><?php echo $this->config->compare_button_code; ?></textarea>					
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('question_button_code', Text::_('ESHOP_CONFIG_QUESTION_BUTTON_CODE')); ?>
		</div>
		<div class="controls">
			<textarea class="input-xxlarge form-control" rows="5" cols="40" name="question_button_code" id="question_button_code"><?php echo $this->config->question_button_code; ?></textarea>					
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('match_button_code', Text::_('ESHOP_CONFIG_MATCH_BUTTON_CODE')); ?>
		</div>
		<div class="controls">
			<textarea class="input-xxlarge form-control" rows="5" cols="40" name="match_button_code" id="match_button_code"><?php echo $this->config->match_button_code; ?></textarea>					
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('email_button_code', Text::_('ESHOP_CONFIG_EMAIL_BUTTON_CODE')); ?>
		</div>
		<div class="controls">
			<textarea class="input-xxlarge form-control" rows="5" cols="40" name="email_button_code" id="email_button_code"><?php echo $this->config->email_button_code; ?></textarea>					
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('pdf_button_code', Text::_('ESHOP_CONFIG_PDF_BUTTON_CODE')); ?>
		</div>
		<div class="controls">
			<textarea class="input-xxlarge form-control" rows="5" cols="40" name="pdf_button_code" id="pdf_button_code"><?php echo $this->config->pdf_button_code; ?></textarea>					
		</div>
	</div>
</fieldset>