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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$translatable = $this->isMultilingualTranslable;
$requireNameInMultipleLanguages = EShopHelper::getConfigValue('require_name_in_multiple_languages', 1);
$defaultSiteLanguage =  ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		
		if (pressbutton == 'product.cancel')
		{
			Joomla.submitform(pressbutton, form);
			return;
		}
		else
		{
			//Validate the entered data before submitting
			<?php
			if ($translatable)
			{
    			foreach ($this->languages as $language)
    			{
        			$langId = $language->lang_id;
        			
        			if ($requireNameInMultipleLanguages)
        			{
        			    ?>
            			if (document.getElementById('product_name_<?php echo $langId; ?>').value == '')
                		{
            				alert("<?php echo addcslashes(Text::_('ESHOP_ENTER_NAME'), '"'); ?>");
            				document.getElementById('product_name_<?php echo $langId; ?>').focus();
            				return;
            			}
            			<?php
                    }
                    else 
                    {
                       if ($language->lang_code == $defaultSiteLanguage)
                       {
                            ?>
                			if (document.getElementById('product_name_<?php echo $langId; ?>').value == '')
                    		{
                				alert("<?php echo addcslashes(Text::_('ESHOP_ENTER_NAME'), '"'); ?>");
                				document.getElementById('product_name_<?php echo $langId; ?>').focus();
                				return;
                			}
                			<?php
                       }
                    }
    			}
			}
			else
			{
    			?>
    			if (form.product_name.value == '')
        		{
    				alert("<?php echo addcslashes(Text::_('ESHOP_ENTER_NAME'), '"'); ?>");
    				form.product_name.focus();
    				return;
    			}
    			<?php
			}
			?>
			if (form.product_sku.value == '')
			{
				alert("<?php echo addcslashes(Text::_('ESHOP_ENTER_PRODUCT_SKU'), '"'); ?>");
				form.product_sku.focus();
				return;
			}
			
			if (form.main_category_id.value == '0')
			{
				alert("<?php echo addcslashes(Text::_('ESHOP_SELECT_CATEGORY_PROMPT'), '"'); ?>");
				form.main_category_id.focus();
				return;
			}
			
			Joomla.submitform(pressbutton, form);
		}
	}
	
	//Add or Remove product images
	var countProductImages = '<?php echo count($this->productImages); ?>';
	
	function addProductImage()
	{
		var html = '<tr id="product_image_' + countProductImages + '" style="height: 100px;">';

		//Image column
		html += '<td style="text-align: center; vertical-align: middle;"><input type="file" class="input" size="20" accept="image/*" name="image[]" /></td>';

		//Ordering column
		html += '<td style="text-align: center; vertical-align: middle;"><input class="input-small form-control" type="text" name="image_ordering[]" maxlength="10" value="" /></td>';

		//Published column
		html += '<td style="text-align: center; vertical-align: middle;"><select class="input-medium form-select" name="image_published[]">';
		html += '<option selected="selected" value="1"><?php echo addcslashes(Text::_('ESHOP_YES'), "'"); ?></option>';
		html += '<option value="0"><?php echo addcslashes(Text::_('ESHOP_NO'), "'"); ?></option>';
		html += '</select></td>';

		// Remove button column
		html += '<td style="text-align: center; vertical-align: middle;"><input type="button" class="btn btn-small btn-primary" name="btnRemove" value="<?php echo addcslashes(Text::_('ESHOP_BTN_REMOVE'), "'"); ?>" onclick="removeProductImage('+countProductImages+');" /></td>';
		html += '</tr>';
		
		jQuery('#product_images_area').append(html);
		countProductImages++;
	}
	
	function removeProductImage(rowIndex)
	{
		jQuery('#product_image_' + rowIndex).remove();
	}
	
	//Add or Remove product attachments
	var countProductAttachments = '<?php echo count($this->productAttachments); ?>';
	
	function addProductAttachment()
	{
		var html = '<tr id="product_attachment_' + countProductAttachments + '" style="height: 100px;">';

		//Image column
		html += '<td style="text-align: center; vertical-align: middle;"><input type="file" class="input" size="20" name="attachment[]" /></td>';

		//Ordering column
		html += '<td style="text-align: center; vertical-align: middle;"><input class="input-small form-control" type="text" name="attachment_ordering[]" maxlength="10" value="" /></td>';

		//Published column
		html += '<td style="text-align: center; vertical-align: middle;"><select class="input-medium form-select" name="attachment_published[]">';
		html += '<option selected="selected" value="1"><?php echo addcslashes(Text::_('ESHOP_YES'), "'"); ?></option>';
		html += '<option value="0"><?php echo addcslashes(Text::_('ESHOP_NO'), "'"); ?></option>';
		html += '</select></td>';

		// Remove button column
		html += '<td style="text-align: center; vertical-align: middle;"><input type="button" class="btn btn-small btn-primary" name="btnRemove" value="<?php echo addcslashes(Text::_('ESHOP_BTN_REMOVE'), "'"); ?>" onclick="removeProductAttachment('+countProductAttachments+');" /></td>';
		html += '</tr>';
		
		jQuery('#product_attachments_area').append(html);
		countProductAttachments++;
	}
	
	function removeProductAttachment(rowIndex)
	{
		jQuery('#product_attachment_' + rowIndex).remove();
	}

	//Add or Remove product attributes
	var countProductAttributes = '<?php echo count($this->productAttributes); ?>';
	
	function addProductAttribute()
	{
		var html = '<tr id="product_attribute_' + countProductAttributes + '">';

		//Attribute column
		html += '<td style="text-align: center; vertical-align: middle;"><?php echo preg_replace(array('/\r/', '/\n/'), '', $this->lists['attributes']); ?></td>';

		//Value column
		html += '<td style="text-align: center;">';
		<?php
		if ($translatable)
		{
    		foreach ($this->languages as $language)
    		{
        		$langCode = $language->lang_code;
        		?>
        		html += '<input class="input-large form-control" type="text" name="attribute_value_<?php echo $langCode; ?>[]" maxlength="255" value="" />';
        		html += '<img src="<?php echo Uri::root(); ?>media/com_eshop/flags/<?php echo $this->languageData['flag'][$langCode]; ?>" /><br />';
        		<?php
    		}
		}
		else
		{
    		?>
    		html += '<input class="input-xlarge form-control" type="text" name="attribute_value[]" maxlength="255" value="" />';
    		<?php
		}
		?>
		html += '</td>';

		//Published column
		html += '<td style="text-align: center; vertical-align: middle;"><select class="input-medium form-select" name="attribute_published[]">';
		html += '<option selected="selected" value="1"><?php echo addcslashes(Text::_('ESHOP_YES'), "'"); ?></option>';
		html += '<option value="0"><?php echo addcslashes(Text::_('ESHOP_NO'), "'"); ?></option>';
		html += '</select></td>';

		// Remove button column
		html += '<td style="text-align: center; vertical-align: middle;"><input type="button" class="btn btn-small btn-primary" name="btnRemove" value="<?php echo addcslashes(Text::_('ESHOP_BTN_REMOVE'), "'"); ?>" onclick="removeProductAttribute('+countProductAttributes+');" /></td>';
		html += '</tr>';
		
		jQuery('#product_attributes_area').append(html);
		countProductAttributes++;
	}
	
	function removeProductAttribute(rowIndex)
	{
		jQuery('#product_attribute_' + rowIndex).remove();
	}
	
	//Option Values
	var countProductOptionValues = new Array();
	<?php
	for ($i = 0; $n = count($this->productOptions), $i < $n; $i++)
	{
    	$productOption = $this->productOptions[$i];
    	?>
    	countProductOptionValues['<?php echo $productOption->id ?>'] = '<?php echo count($this->productOptionValues[$i]); ?>';
    	<?php
	}
	
	for ($i = 0; $n = count($this->options), $i < $n; $i++)
	{
        ?>
        if (countProductOptionValues['<?php echo $this->options[$i]->id; ?>'] === undefined)
        {
        	countProductOptionValues['<?php echo $this->options[$i]->id; ?>'] = 0;
        }
        <?php
	}
	?>
	function addProductOptionValue(optionId)
	{
		var html = '<tr id="product_option_'+optionId+'_value_'+countProductOptionValues[optionId]+'">';

		//Option Value column
		html += '<td style="text-align: center;">';
		html += '<select class="input-large form-select" name="option_value_'+optionId+'_id[]">';
		html +=	jQuery('#option_values_'+optionId).html();
		html += '</select>';
		html += '<input type="hidden" name="product_option_value_'+optionId+'_ids[]" value="0" />';
		html += '</td>';

		//SKU column
		html += '<td style="text-align: center;">';
		html += '<input type="text" value="" maxlength="255" name="product_option_value_'+optionId+'_sku[]" class="input-small form-control">';
		html += '</td>';

		//Quantity column
		html += '<td style="text-align: center;">';
		html += '<input type="text" value="" maxlength="255" name="product_option_value_'+optionId+'_quantity[]" class="input-small form-control">';
		html += '</td>';

		//Price column
		html += '<td style="text-align: center;">';
		html += '<select class="input-mini form-select" name="product_option_value_'+optionId+'_price_sign[]">';
		html +=	jQuery('#price_sign').html();
		html += '</select>';
		html += '<input type="text" value="" maxlength="255" name="product_option_value_'+optionId+'_price[]" class="input-small form-control">';
		html += '<select class="input-medium form-select" name="product_option_value_'+optionId+'_price_type[]">';
		html +=	jQuery('#price_type').html();
		html += '</select>';
		html += '</td>';

		//Weight column
		html += '<td style="text-align: center;">';
		html += '<select class="input-mini form-select" name="product_option_value_'+optionId+'_weight_sign[]">';
		html +=	jQuery('#weight_sign').html();
		html += '</select>';
		html += '<input type="text" value="" maxlength="255" name="product_option_value_'+optionId+'_weight[]" class="input-small form-control">';
		html += '</td>';

		//Shipping column
		html += '<td style="text-align: center;">';
		html += '<select class="input-mini form-select" name="product_option_value_'+optionId+'_shipping[]">';
		html +=	jQuery('#shipping').html();
		html += '</select>';
		html += '</td>';

		//Published column
		html += '<td style="text-align: center;">';
		html += '<select class="input-mini form-select" name="product_option_value_'+optionId+'_published[]">';
		html +=	jQuery('#option_value_published').html();
		html += '</select>';
		html += '</td>';

		//Image column
		html += '<td style="text-align: center;">';
		html += '<input type="file" name="product_option_value_'+optionId+'_image[]" accept="image/*" class="input-small form-control">';
		html += '</td>';

		//Remove button column
		html += '<td style="text-align: center;">';
		html += '<input type="button" onclick="removeProductOptionValue('+optionId+', '+countProductOptionValues[optionId]+');" value="Remove" name="btnRemove" class="btn btn-small btn-primary">';
		html += '</td>';
		html += '</tr>';
		
		jQuery('#product_option_'+optionId+'_values_area').append(html);
		countProductOptionValues[optionId]++;
	}
	
	function removeProductOptionValue(optionId, rowIndex)
	{
		jQuery('#product_option_'+optionId+'_value_'+rowIndex).remove();
	}
	
	var countProductDiscounts = '<?php echo count($this->productDiscounts); ?>';
	
	function addProductDiscount()
	{
		var html = '<tr id="product_discount_' + countProductDiscounts + '">';

		//Customer group column
		html += '<td style="text-align: center;"><?php echo preg_replace(array('/\r/', '/\n/'), '', $this->lists['discount_customer_group']); ?></td>';

		//Quantity column
		html += '<td style="text-align: center;">';
		html += '<input type="text" value="" maxlength="10" name="discount_quantity[]" class="input-mini form-control" />';
		html += '</td>';

		//Priority column
		html += '<td style="text-align: center;">';
		html += '<input type="text" value="" maxlength="10" name="discount_priority[]" class="input-mini form-control" />';
		html += '</td>';

		//Price column
		html += '<td style="text-align: center;">';
		html += '<input type="text" value="" maxlength="10" name="discount_price[]" class="input-medium form-control" />';
		html += '</td>';

		//Start date column
		html += '<td style="text-align: center;">';
		<?php
		if (version_compare(JVERSION, '3.6.9', 'ge'))
		{
		?>
			var datePicker = jQuery('#date_html_container').html();
			datePicker = datePicker.replace(/tmp_date_picker_id/g, "discount_date_start_" + countProductDiscounts);
			datePicker = datePicker.replace(/tmp_date_picker_name/g, "discount_date_start[]");
			html += datePicker;
		<?php
		}
		else
		{
		?>
			html += '<input type="text" style="width: 100px;" class="input-medium hasTooltip" value="" id="discount_date_start_'+countProductDiscounts+'" name="discount_date_start[]">';
			html += '<button id="discount_date_start_'+countProductDiscounts+'_img" class="btn" type="button"><i class="icon-calendar"></i></button>';
		<?php
		}
		?>
		html += '</td>';

		//End date column
		html += '<td style="text-align: center;">';
    	<?php
		if (version_compare(JVERSION, '3.6.9', 'ge'))
		{
		?>
			var datePicker = jQuery('#date_html_container').html();
			datePicker = datePicker.replace(/tmp_date_picker_id/g, "discount_date_end_" + countProductDiscounts);
			datePicker = datePicker.replace(/tmp_date_picker_name/g, "discount_date_end[]");
			html += datePicker;
		<?php
		}
		else
		{
		?>
    		html += '<input type="text" style="width: 100px;" class="input-medium hasTooltip" value="" id="discount_date_end_'+countProductDiscounts+'" name="discount_date_end[]">';
    		html += '<button id="discount_date_end_'+countProductDiscounts+'_img" class="btn" type="button"><i class="icon-calendar"></i></button>';
		<?php
		}
    	?>
    	html += '</td>';
    	
		//Published column
		html += '<td style="text-align: center;"><select class="input-medium form-select" name="discount_published[]">';
		html += '<option selected="selected" value="1"><?php echo addcslashes(Text::_('ESHOP_YES'), "'"); ?></option>';
		html += '<option value="0"><?php echo addcslashes(Text::_('ESHOP_NO'), "'"); ?></option>';
		html += '</select></td>';

		// Remove button column
		html += '<td style="text-align: center;"><input type="button" class="btn btn-small btn-primary" name="btnRemove" value="<?php echo addcslashes(Text::_('ESHOP_BTN_REMOVE'), "'"); ?>" onclick="removeProductDiscount('+countProductDiscounts+');" /></td>';
		html += '</tr>';
		jQuery('#product_discounts_area').append(html);
		Calendar.setup({
			// Id of the input field
			inputField: "discount_date_start_"+countProductDiscounts,
			// Format of the input field
			ifFormat: "%Y-%m-%d %H:%M",
			showsTime: true,
			// Trigger for the calendar (button ID)
			button: "discount_date_start_"+countProductDiscounts+"_img",
			// Alignment (defaults to "Bl")
			align: "Tl",
			singleClick: true,
			firstDay: 0
		});
		Calendar.setup({
			// Id of the input field
			inputField: "discount_date_end_"+countProductDiscounts,
			// Format of the input field
			ifFormat: "%Y-%m-%d %H:%M",
			showsTime: true,
			// Trigger for the calendar (button ID)
			button: "discount_date_end_"+countProductDiscounts+"_img",
			// Alignment (defaults to "Bl")
			align: "Tl",
			singleClick: true,
			firstDay: 0
		});
		countProductDiscounts++;
	}
	
	function removeProductDiscount(rowIndex)
	{
		jQuery('#product_discount_' + rowIndex).remove();
	}
	
	var countProductSpecials = '<?php echo count($this->productSpecials); ?>';
	
	function addProductSpecial()
	{
		var html = '<tr id="product_special_' + countProductSpecials + '">';

		//Customer group column
		html += '<td style="text-align: center;"><?php echo preg_replace(array('/\r/', '/\n/'), '', $this->lists['special_customer_group']); ?></td>';

		//Priority column
		html += '<td style="text-align: center;">';
		html += '<input type="text" value="" maxlength="10" name="special_priority[]" class="input-mini form-control" />';
		html += '</td>';

		//Price column
		html += '<td style="text-align: center;">';
		html += '<input type="text" value="" maxlength="10" name="special_price[]" class="input-medium form-control" />';
		html += '</td>';

		//Start date column
		html += '<td style="text-align: center;">';
		<?php
		if (version_compare(JVERSION, '3.6.9', 'ge'))
		{
		?>
			var datePicker = jQuery('#date_html_container').html();
			datePicker = datePicker.replace(/tmp_date_picker_id/g, "special_date_start_" + countProductSpecials);
			datePicker = datePicker.replace(/tmp_date_picker_name/g, "special_date_start[]");
			html += datePicker;
		<?php
		}
		else
		{
		?>
			html += '<input type="text" style="width: 100px; " value="" id="special_date_start_'+countProductSpecials+'" name="special_date_start[]">';
			html += '<button id="special_date_start_'+countProductSpecials+'_img" class="btn" type="button"><i class="icon-calendar"></i></button>';
		<?php
		}
		?>
		html += '</td>';
		
		//End date column
		html += '<td style="text-align: center;">';
		<?php
		if (version_compare(JVERSION, '3.6.9', 'ge'))
		{
		?>
			var datePicker = jQuery('#date_html_container').html();
			datePicker = datePicker.replace(/tmp_date_picker_id/g, "special_date_end_" + countProductSpecials);
			datePicker = datePicker.replace(/tmp_date_picker_name/g, "special_date_end[]");
			html += datePicker;
		<?php
		}
		else
		{
		?>
			html += '<input type="text" style="width: 100px; " value="" id="special_date_end_'+countProductSpecials+'" name="special_date_end[]">';
			html += '<button id="special_date_end_'+countProductSpecials+'_img" class="btn" type="button"><i class="icon-calendar"></i></button>';
		<?php
		}
		?>
		html += '</td>';
		
		//Published column
		html += '<td style="text-align: center;"><select class="input-medium form-select" name="special_published[]">';
		html += '<option selected="selected" value="1"><?php echo addcslashes(Text::_('ESHOP_YES'), "'"); ?></option>';
		html += '<option value="0"><?php echo addcslashes(Text::_('ESHOP_NO'), "'"); ?></option>';
		html += '</select></td>';

		// Remove button column
		html += '<td style="text-align: center;"><input type="button" class="btn btn-small btn-primary" name="btnRemove" value="<?php echo addcslashes(Text::_('ESHOP_BTN_REMOVE'), "'"); ?>" onclick="removeProductSpecial('+countProductSpecials+');" /></td>';
		html += '</tr>';
		jQuery('#product_specials_area').append(html);
		Calendar.setup({
			// Id of the input field
			inputField: "special_date_start_"+countProductSpecials,
			// Format of the input field
			ifFormat: "%Y-%m-%d %H:%M",
			showsTime: true,
			// Trigger for the calendar (button ID)
			button: "special_date_start_"+countProductSpecials+"_img",
			// Alignment (defaults to "Bl")
			align: "Tl",
			singleClick: true,
			firstDay: 0
		});
		Calendar.setup({
			// Id of the input field
			inputField: "special_date_end_"+countProductSpecials,
			// Format of the input field
			ifFormat: "%Y-%m-%d %H:%M",
			showsTime: true,
			// Trigger for the calendar (button ID)
			button: "special_date_end_"+countProductSpecials+"_img",
			// Alignment (defaults to "Bl")
			align: "Tl",
			singleClick: true,
			firstDay: 0
		});
		countProductSpecials++;
	}
	
	function removeProductSpecial(rowIndex)
	{
		jQuery('#product_special_' + rowIndex).remove();
	}
</script>
