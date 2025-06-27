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

$options = array();
$options[] = HTMLHelper::_('select.option', '1', Text::_('ESHOP_YES'));
$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_NO'));

$baseUri = Uri::base();
$rootUri = Uri::root();

if (EShopHelper::isJoomla4())
{
    $tabApiPrefix = 'uitab.';
}
else
{
    HTMLHelper::_('behavior.tabstate');

    $tabApiPrefix = 'bootstrap.';
}
?>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_ASSIGN_OPTIONS_TO_PRODUCT'); ?></legend>
    <?php
    for ($i = 0; $n = count($this->options), $i < $n; $i++)
	{
		echo $this->lists['option_values_'.$this->options[$i]->id];
	}
	
	echo $this->lists['price_sign'];
	echo $this->lists['price_type'];
	echo $this->lists['weight_sign'];
	echo $this->lists['shipping'];
	echo $this->lists['option_value_published'];
	
	if (count($this->options))
	{
        echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'product-options', array('active' => 'product-option-' . $this->options[0]->id, 'orientation' => 'horizontal'));
        
        for ($i = 0; $n = count($this->productOptions), $i < $n; $i++)
        {
            $productOption = $this->productOptions[$i];
            
            echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product-options', 'product-option-' . $productOption->id, $productOption->option_name);
            ?>
    		<div class="control-group">
    			<div class="control-label">
    				<?php echo Text::_('ESHOP_ASSIGN_TO_PRODUCT'); ?>
    			</div>
    			<div class="controls">
    				<?php echo HTMLHelper::_('select.genericlist', $options, 'assign_' . $productOption->id, 'class="input-medium form-select"', 'value', 'text', 1); ?>
    				<input type="hidden" name="option_ids[]" value="<?php echo $productOption->id; ?>" />
    				<input type="hidden" name="product_option_ids[]" value="<?php echo $productOption->product_option_id; ?>" />
    				<?php
    				if ($productOption->option_type == 'Select' || $productOption->option_type == 'Radio' || $productOption->option_type == 'Checkbox' || $productOption->option_type == 'Text' || $productOption->option_type == 'Textarea')
    				{
    				    ?>
    				    <input type="hidden" name="option_types[]" value="1" />
    				    <?php
    				}
    				else 
    				{
    				    ?>
    				    <input type="hidden" name="option_types[]" value="2" />
    				    <?php
    				}
    				?>
    			</div>
    		</div>
    		<div class="control-group">
    			<div class="control-label">
    				<?php echo Text::_('ESHOP_REQUIRED'); ?>
    			</div>
    			<div class="controls">
    				<?php echo HTMLHelper::_('select.genericlist', $options, 'required_' . $productOption->id, 'class="input-medium form-select"', 'value', 'text', $productOption->required); ?>
    			</div>
    		</div>
    		<?php
    		if ($productOption->option_type == 'Select' || $productOption->option_type == 'Radio' || $productOption->option_type == 'Checkbox')
    		{
    			?>
    			<table class="adminlist table table-bordered" style="text-align: center;">
    				<thead>
    				<tr>
    					<th class="title" width=""><?php echo Text::_('ESHOP_OPTION_VALUE'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_SKU'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_QUANTITY'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_PRICE'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_WEIGHT'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_SHIPPING'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_PUBLISHED'); ?></th>
    					<th class="title" width="" nowrap="nowrap"><?php echo Text::_('ESHOP_IMAGE'); ?></th>
    					<th class="title" width="">&nbsp;</th>
    				</tr>
    				</thead>
    				<tbody id="product_option_<?php echo $productOption->id; ?>_values_area">
    				<?php
    				$options = array();
    				$options[] = HTMLHelper::_('select.option', '1', Text::_('ESHOP_YES'));
    				$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_NO'));
    				
    				for ($j = 0; $m = count($this->productOptionValues[$i]), $j < $m; $j++)
    				{
    					$productOptionValue = $this->productOptionValues[$i][$j];
    					?>
    					<tr id="product_option_<?php echo $productOption->id; ?>_value_<?php echo $j; ?>">
    						<td style="text-align: center;">
    							<?php echo $this->lists['option_value_'.$productOptionValue->id]; ?>
    							<input type="hidden" name="product_option_value_<?php echo $productOption->id; ?>_ids[]" value="<?php echo $productOptionValue->id; ?>" />
    						</td>
    						<td style="text-align: center;">
    							<input class="input-small form-control" type="text" name="product_option_value_<?php echo $productOption->id; ?>_sku[]" size="10" maxlength="255" value="<?php echo $productOptionValue->sku; ?>" />
    						</td>
    						<td style="text-align: center;">
    							<input class="input-small form-control" type="text" name="product_option_value_<?php echo $productOption->id; ?>_quantity[]" size="10" maxlength="255" value="<?php echo $productOptionValue->quantity; ?>" />
    						</td>
    						<td style="text-align: center;">
    							<?php echo $this->lists['price_sign_'.$productOptionValue->id]; ?>
    							<input class="input-small form-control" type="text" name="product_option_value_<?php echo $productOption->id; ?>_price[]" size="10" maxlength="255" value="<?php echo $productOptionValue->price; ?>" />
    							<?php echo $this->lists['price_type_'.$productOptionValue->id]; ?>
    						</td>
    						<td style="text-align: center;">
    							<?php echo $this->lists['weight_sign_'.$productOptionValue->id]; ?>
    							<input class="input-small form-control" type="text" name="product_option_value_<?php echo $productOption->id; ?>_weight[]" size="10" maxlength="255" value="<?php echo $productOptionValue->weight; ?>" />
    						</td>
    						<td style="text-align: center;">
    							<?php echo $this->lists['shipping_'.$productOptionValue->id]; ?>
    						</td>
    						<td style="text-align: center;">
    							<?php echo $this->lists['option_value_published_'.$productOptionValue->id]; ?>
    						</td>
    						<td style="text-align: center;" nowrap="nowrap">
    							<?php
    							if (is_file(JPATH_ROOT.'/media/com_eshop/options/'.$productOptionValue->image))
    							{
    								$viewImage = File::stripExt($productOptionValue->image).'-100x100.'.EShopHelper::getFileExt($productOptionValue->image);
    								if (is_file(JPATH_ROOT.'/media/com_eshop/options/resized/'.$viewImage))
    								{
    									?>
    									<img class="img-polaroid" width="50" src="<?php echo $rootUri . 'media/com_eshop/options/resized/' . $viewImage; ?>" /><br />
    									<?php
    								}
    								?>
    								<div class="form-check">
                            			<input type="checkbox" class="form-check-input" name="remove_image_<?php echo $productOptionValue->id; ?>" id="remove_image" value="1" />
                            			<label class="form-check-label" for="remove_image">
                            				<?php echo Text::_('ESHOP_REMOVE_IMAGE'); ?>
                            			</label>
                        			</div>
    								<?php
    							}
    							?>
    							<input class="input-small form-control" type="file" name="product_option_value_<?php echo $productOption->id; ?>_image[]" accept="image/*" />
    							<input type="hidden" name="product_option_value_<?php echo $productOption->id; ?>_imageold[]" value="<?php echo $productOptionValue->image; ?>" />
    						</td>
    						<td style="text-align: center;">
    							<input type="button" class="btn btn-small btn-primary" name="btnRemove" value="<?php echo Text::_('ESHOP_BTN_REMOVE'); ?>" onclick="removeProductOptionValue(<?php echo $productOption->id; ?>, <?php echo $j; ?>);" />
    						</td>
    					</tr>
    					<?php
    				}
    				?>
    				</tbody>
    				<tfoot>
    				<tr>
    					<td colspan="8">
    						<input type="button" class="btn btn-small btn-primary" name="btnAdd" value="<?php echo Text::_('ESHOP_BTN_ADD'); ?>" onclick="addProductOptionValue(<?php echo $productOption->id; ?>);" />
    						<?php echo $this->lists['option_values_' . $productOption->id]; ?>
    					</td>
    				</tr>
    				</tfoot>
    			</table>
    			<?php
    		}
    		if ($productOption->option_type == 'Text' || $productOption->option_type == 'Textarea')
    		{
    			$productOptionValue = $this->productOptionValues[$i][0];
    			?>
    			<div class="control-group">
        			<div class="control-label">
        				<?php echo Text::_('ESHOP_PRODUCT_PRICE_PER_CHAR'); ?>
        			</div>
        			<div class="controls">
        				<input type="hidden" name="option_value_<?php echo $productOption->id; ?>_id[]" id="product_option_value_<?php echo $productOption->id; ?>_id" value="0" />
        				<input type="hidden" name="product_option_value_<?php echo $productOption->id; ?>_ids[]" value="<?php echo $productOptionValue->id; ?>" />
        				<input type="hidden" name="product_option_value_<?php echo $productOption->id; ?>_sku[]" value="" />
        				<input type="hidden" name="product_option_value_<?php echo $productOption->id; ?>_quantity[]" value="0" />
    					<?php echo $this->lists['price_sign_t_' . $productOption->id]; ?>
    					<input class="input-medium form-control" type="text" name="product_option_value_<?php echo $productOption->id; ?>_price[]" size="10" maxlength="255" value="<?php echo $productOptionValue->price; ?>" />
    					<?php echo $this->lists['price_type_t_' . $productOption->id]; ?>
    					<input type="hidden" name="product_option_value_<?php echo $productOption->id; ?>_weight[]" value="0" />
        				<input type="hidden" name="product_option_value_<?php echo $productOption->id; ?>_weight_sign[]" value="+" />
        				<input type="hidden" name="product_option_value_<?php echo $productOption->id; ?>_shipping[]" value="1" />
        			</div>
        		</div>
    			<?php
    		}
    		
            echo HTMLHelper::_($tabApiPrefix . 'endTab');
        }
        
        for ($i = 0; $n = count($this->notProductOptions), $i < $n; $i++)
        {
            $notProductOption = $this->notProductOptions[$i];
        
            echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product-options', 'product-option-' . $notProductOption->id, $notProductOption->option_name);
            ?>
    		<div class="control-group">
    			<div class="control-label">
    				<?php echo Text::_('ESHOP_ASSIGN_TO_PRODUCT'); ?>
    			</div>
    			<div class="controls">
    				<?php echo HTMLHelper::_('select.genericlist', $options, 'assign_' . $notProductOption->id, 'class="input-medium form-select"', 'value', 'text', 0); ?>
    				<input type="hidden" name="option_ids[]" value="<?php echo $notProductOption->id; ?>" />
    				<input type="hidden" name="product_option_ids[]" value="0" />
    				<?php
    				if ($notProductOption->option_type == 'Select' || $notProductOption->option_type == 'Radio' || $notProductOption->option_type == 'Checkbox' || $notProductOption->option_type == 'Text' || $notProductOption->option_type == 'Textarea')
    				{
    				    ?>
    				    <input type="hidden" name="option_types[]" value="1" />
    				    <?php
    				}
    				else 
    				{
    				    ?>
    				    <input type="hidden" name="option_types[]" value="2" />
    				    <?php
    				}
    				?>
    			</div>
    		</div>
    		<div class="control-group">
    			<div class="control-label">
    				<?php echo Text::_('ESHOP_REQUIRED'); ?>
    			</div>
    			<div class="controls">
    				<?php echo HTMLHelper::_('select.genericlist', $options, 'required_' . $notProductOption->id, 'class="input-medium form-select"', 'value', 'text', 1); ?>
    			</div>
    		</div>
    		<?php
    		if ($notProductOption->option_type == 'Select' || $notProductOption->option_type == 'Radio' || $notProductOption->option_type == 'Checkbox')
    		{
    			?>
    			<table class="adminlist table table-bordered" style="text-align: center;">
    				<thead>
    				<tr>
    					<th class="title" width=""><?php echo Text::_('ESHOP_OPTION_VALUE'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_SKU'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_QUANTITY'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_PRICE'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_WEIGHT'); ?></th>
    					<th class="title" width=""><?php echo Text::_('ESHOP_SHIPPING'); ?></th>
    					<th class="title" width="" nowrap="nowrap"><?php echo Text::_('ESHOP_IMAGE'); ?></th>
    					<th class="title" width="">&nbsp;</th>
    				</tr>
    				</thead>
    				<tbody id="product_option_<?php echo $notProductOption->id; ?>_values_area"></tbody>
    				<tfoot>
    				<tr>
    					<td colspan="8">
    						<input type="button" class="btn btn-small btn-primary" name="btnAdd" value="<?php echo Text::_('ESHOP_BTN_ADD'); ?>" onclick="addProductOptionValue(<?php echo $notProductOption->id; ?>);" />
    						<?php echo $this->lists['option_values_' . $notProductOption->id]; ?>
    					</td>
    				</tr>
    				</tfoot>
    			</table>
    			<?php
    		}
    		if ($notProductOption->option_type == 'Text' || $notProductOption->option_type == 'Textarea')
    		{
    			?>
    			<div class="control-group">
        			<div class="control-label">
        				<?php echo Text::_('ESHOP_PRODUCT_PRICE_PER_CHAR'); ?>
        			</div>
        			<div class="controls">
        				<input type="hidden" name="option_value_<?php echo $notProductOption->id; ?>_id[]" id="option_value_<?php echo $notProductOption->id; ?>_id" value="0" />
        				<input type="hidden" name="product_option_value_<?php echo $notProductOption->id; ?>_ids[]" value="0" />
        				<input type="hidden" name="product_option_value_<?php echo $notProductOption->id; ?>_sku[]" value="" />
        				<input type="hidden" name="product_option_value_<?php echo $notProductOption->id; ?>_quantity[]" value="0" />
    					<?php echo $this->lists['price_sign_visible']; ?>
    					<input class="input-medium form-control" type="text" name="product_option_value_<?php echo $notProductOption->id; ?>_price[]" size="10" maxlength="255" value="0" />
    					<?php echo $this->lists['price_type_visible']; ?>
    					<input type="hidden" name="product_option_value_<?php echo $notProductOption->id; ?>_weight[]" value="0" />
        				<input type="hidden" name="product_option_value_<?php echo $notProductOption->id; ?>_weight_sign[]" value="+" />
        				<input type="hidden" name="product_option_value_<?php echo $notProductOption->id; ?>_shipping[]" value="1" />
        			</div>
        		</div>
    			<?php
    		}
        		
            echo HTMLHelper::_($tabApiPrefix . 'endTab');
        }
        
        echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	}
    ?>
</fieldset>
<?php
if (EShopHelper::getConfigValue('assign_same_options'))
{
	?>
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('ESHOP_ASSIGN_SAME_OPTIONS_TO_OTHER_PRODUCTS'); ?></legend>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_ASSIGN_SAME_OPTIONS_TO_OTHER_PRODUCTS_HELP'); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['same_options_products']; ?>
			</div>
		</div>
	</fieldset>
	<?php
}