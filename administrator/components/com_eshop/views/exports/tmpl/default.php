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
use Joomla\CMS\Toolbar\ToolbarHelper;

EShopHelper::chosen();

ToolbarHelper::title(Text::_('ESHOP_EXPORTS'), 'generic.png');
ToolbarHelper::custom('exports.process', 'download', 'download', Text::_('ESHOP_PROCESS'), false);
ToolbarHelper::cancel('exports.cancel');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'exports.cancel') {
			Joomla.submitform(pressbutton, form);
			return;
		} else {
			if (form.export_type.value == '') {
				alert('<?php echo Text::_('ESHOP_EXPORT_TYPE_PROMPT'); ?>');
				form.export_type.focus();
				return;	
			}
			Joomla.submitform(pressbutton, form);
		}
	}
	
	function changeExportType()
	{
		var form = document.adminForm;
		var allAreaArr = ['order_status_area', 'order_id_area', 'list_order_area', 'image_separator_area', 'language_area', 'google_feed_area', 'pinterest_feed_area', 'export_format_area', 'category_ids', 'product_status', 'export_fields', 'export_orders_fields', 'start_record_area', 'total_records_area'];
		if (form.export_type.value == '' || form.export_type.value == 'customers') {
			var areaArr = [];
		} else if (form.export_type.value == 'products') {
			var areaArr = ['image_separator_area', 'language_area', 'export_format_area', 'category_ids', 'product_status', 'export_fields', 'start_record_area', 'total_records_area'];
		} else if (form.export_type.value == 'categories' || form.export_type.value == 'manufacturers') {
			var areaArr = ['language_area'];
		} else if (form.export_type.value == 'orders') {
			var areaArr = ['order_status_area', 'order_id_area', 'list_order_area', 'language_area', 'export_format_area', 'export_orders_fields'];
		} else if (form.export_type.value == 'google_feed') {
			var areaArr = ['language_area', 'product_status', 'google_feed_area' ,'export_format_area', 'start_record_area', 'total_records_area'];
		} else if (form.export_type.value == 'pinterest_feed') {
			var areaArr = ['language_area', 'product_status', 'pinterest_feed_area' ,'export_format_area', 'start_record_area', 'total_records_area'];
		}
		for (var i = 0; i < allAreaArr.length; i++) {
			if (areaArr.indexOf(allAreaArr[i]) >= 0) {
				if (allAreaArr[i] == 'google_feed_area' || allAreaArr[i] == 'pinterest_feed_area') {
					document.getElementById(allAreaArr[i]).style = 'display: block;';
				}
				else {
					document.getElementById(allAreaArr[i]).style = 'display: flex;';
				}
			}
			else {
				document.getElementById(allAreaArr[i]).style = 'display: none;';
			}
		}
	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<div class="exports-area">
    	<div class="control-group">
    		<div class="control-label">
    			<?php echo  Text::_('ESHOP_EXPORT_TYPE'); ?>
    		</div>
    		<div class="controls">
    			<?php echo $this->lists['export_type']; ?>
    		</div>
    	</div>
    	<div class="control-group" id="order_status_area" style="display: none;">
    		<div class="control-label">
    			<?php echo  Text::_('ESHOP_ORDER_STATUS'); ?>
    		</div>	
    		<div class="controls">
    			<?php echo $this->lists['order_status_id']; ?>
    		</div>
    	</div>
    	<div class="control-group" id="order_id_area" style="display: none;">
    		<div class="control-label">
    			<?php echo  Text::_('ESHOP_ORDER_ID'); ?>
    		</div>
    		<div class="controls">
    			<?php echo  Text::_('ESHOP_FROM'); ?>&nbsp;<input class="input-mini form-control" type="text" name="order_id_from" id="order_id_from" value="0" />&nbsp;<?php echo  Text::_('ESHOP_TO'); ?>&nbsp;<input class="input-mini form-control" type="text" name="order_id_to" id="order_id_to" value="0" />
    		</div>
    	</div>
    	<div class="control-group" id="list_order_area" style="display: none;">
    		<div class="control-label">
    			<?php echo  Text::_('ESHOP_LIST_ORDER_ID'); ?>
    		</div>
    		<div class="controls">
    			<input class="input-xxlarge form-control" type="text" name="list_order_id" id="list_order_id" value="" />
    		</div>
    		<span class="help"><?php echo  Text::_('ESHOP_LIST_ORDER_ID_HELP'); ?></span>
    	</div>
    	<div class="control-group" id="image_separator_area" style="display: none;">
    		<div class="control-label">
    			<?php echo  Text::_('ESHOP_IMAGE_SEPARATOR'); ?>
    		</div>
    		<div class="controls">
    			<input class="input-mini form-control" type="text" name="image_separator" id="image_separator" maxlength="1" value=";" />
    		</div>
    	</div>
    	<div class="control-group" id="language_area" style="display: none;">
    		<div class="control-label">
    			<?php echo  Text::_('ESHOP_LANGUAGE'); ?>
    		</div>
    		<div class="controls">
    			<?php echo $this->lists['language']; ?>
    		</div>
    	</div>
    	<div class="control-group" id="export_format_area" style="display: none;">
    		<div class="control-label">
    			<?php echo  Text::_('ESHOP_EXPORT_FORMAT'); ?>
    		</div>
    		<div class="controls">
    			<?php echo $this->lists['export_format']; ?>
    		</div>
    	</div>
    	<div class="control-group" id="category_ids" style="display: none;">
            <div class="control-label">
                <?php echo  Text::_('ESHOP_SELECT_CATEGORIES'); ?>
            </div>
            <div class="controls">
                <?php echo $this->lists['category_ids']; ?>
            </div>
        </div>
        <div class="control-group" id="product_status" style="display: none;">
            <div class="control-label">
                <?php echo  Text::_('ESHOP_SELECT_PRODUCT_STATUS'); ?>
            </div>
            <div class="controls">
                <?php echo $this->lists['product_status']; ?>
            </div>
        </div>
        <div class="control-group" id="export_fields" style="display: none;">
            <div class="control-label">
                <?php echo  Text::_('ESHOP_SELECT_FIELDS'); ?>
            </div>
            <div class="controls">
                <?php echo $this->lists['export_fields']; ?>
            </div>
        </div>
        <div class="control-group" id="export_orders_fields" style="display: none;">
            <div class="control-label">
                <?php echo  Text::_('ESHOP_SELECT_FIELDS'); ?>
            </div>
            <div class="controls">
                <?php echo $this->lists['export_orders_fields']; ?>
            </div>
        </div>
        <div class="control-group" id="start_record_area" style="display: none;">
            <div class="control-label">
                <?php echo  Text::_('ESHOP_START_RECORD'); ?>
            </div>
            <div class="controls">
                <input class="input-mini form-control" type="text" name="start_record" id="start_record" maxlength="6" value="" />
            </div>
        </div>
        <div class="control-group" id="total_records_area" style="display: none;">
            <div class="control-label">
                <?php echo  Text::_('ESHOP_TOTAL_RECORDS'); ?>
            </div>
            <div class="controls">
                <input class="input-mini form-control" type="text" name="total_records" id="total_records" maxlength="6" value="" />
            </div>
        </div>
    	<div id="google_feed_area" style="display: none;">
    		<div class="control-group" id="remove_zero_price_products">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_REMOVE_ZERO_PRICE_PRODUCTS'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['remove_zero_price_products']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="remove_out_of_stock_products">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_REMOVE_OUT_OF_STOCK_PRODUCTS'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['remove_out_of_stock_products']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_id">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_ID'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_id']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_title">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_TITLE'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_title']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_description">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_DESCRIPTION'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_description']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_product_type">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_PRODUCT_TYPE'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_product_type']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_link">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_LINK'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_link']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_mobile_link">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_MOBILE_LINK'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_mobile_link']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_image_link">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_IMAGE_LINK'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_image_link']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_additional_image_link">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_ADDITIONAL_IMAGE_LINK'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_additional_image_link']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_availability">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_AVAILABILITY'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_availability']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_price">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_PRICE'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_price']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_sale_price">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_SALE_PRICE'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_sale_price']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_mpn">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_MPN'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_mpn']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_brand">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_BRAND'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_brand']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_shipping_weight">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_SHIPPING_WEIGHT'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_shipping_weight']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="google_alias">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_GOOGLE_ALIAS'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['google_alias']; ?>
    			</div>
    		</div>
    	</div>
    	<div id="pinterest_feed_area" style="display: none;">
    		<div class="control-group" id="pinterest_">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_PINTEREST_ID'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['pinterest_id']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="pinterest_title">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_PINTEREST_TITLE'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['pinterest_title']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="pinterest_description">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_PINTEREST_DESCRIPTION'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['pinterest_description']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="pinterest_link">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_PINTEREST_LINK'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['pinterest_link']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="pinterest_image_link">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_PINTEREST_IMAGE_LINK'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['pinterest_image_link']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="pinterest_availability">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_PINTEREST_AVAILABILITY'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['pinterest_availability']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="pinterest_price">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_PINTEREST_PRICE'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['pinterest_price']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="pinterest_condition">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_PINTEREST_CONDITION'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['pinterest_condition']; ?>
    			</div>
    		</div>
    		<div class="control-group" id="pinterest_brand">
    			<div class="control-label">
    				<?php echo  Text::_('ESHOP_PINTEREST_BRAND'); ?>
    			</div>
    			<div class="controls">
    				<?php echo $this->lists['pinterest_brand']; ?>
    			</div>
    		</div>
    	</div>
    </div>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="task" value="" />
	<div class="clearfix"></div>
</form>