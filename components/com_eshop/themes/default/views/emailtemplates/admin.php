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

$productFieldsDisplay       = EShopHelper::getConfigValue('product_fields_display', '');
$productFieldsDisplayArr    = array();

if ($productFieldsDisplay != '')
{
    $productFieldsDisplayArr = explode(',', $productFieldsDisplay);
}

$colspan = 2 + count($productFieldsDisplayArr);
?>
<table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
	<thead>
		<tr>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
				<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>
			</td>
			<?php
			if (in_array('product_image', $productFieldsDisplayArr))
			{
			    ?>
			    <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: center; padding: 7px; color: #222222;">
    				<?php echo Text::_('ESHOP_IMAGE'); ?>
    			</td>
			    <?php
			}
			
			if (in_array('product_sku', $productFieldsDisplayArr))
			{
			    ?>
			    <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
    				<?php echo Text::_('ESHOP_MODEL'); ?>
    			</td>
			    <?php
			}
			
			if (in_array('product_quantity', $productFieldsDisplayArr))
			{
			    ?>
			    <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
    				<?php echo Text::_('ESHOP_QUANTITY'); ?>
    			</td>
			    <?php
			}
			
			if (in_array('product_custom_message', $productFieldsDisplayArr))
			{
				?>
			    <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
    				<?php echo Text::_('ESHOP_CUSTOM_MESSAGE'); ?>
    			</td>
			    <?php
			}
			?>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
				<?php echo Text::_('ESHOP_UNIT_PRICE'); ?>
			</td>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
				<?php echo Text::_('ESHOP_TOTAL'); ?>
			</td>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($orderProducts as $product)
		{
			?>
			<tr>
				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
				<?php echo $product->product_name; ?>
				<?php
				foreach ($product->orderOptions as $option)
				{
					if ($option->option_type == 'File' && $option->option_value != '')
					{
						echo '<br />- ' . $option->option_name . ': <a href="' . EShopHelper::getSiteUrl() . 'index.php?option=com_eshop&task=downloadOptionFile&id=' . $option->id . '">' . $option->option_value . '</a>';
					}
					else
					{
						echo '<br />- ' . $option->option_name . ': ' . $option->option_value . (isset($option->sku) && $option->sku != '' ? ' (' . $option->sku . ')' : '');
					}
				}
				?>
			</td>
			<?php
			if (in_array('product_image', $productFieldsDisplayArr))
			{
			    ?>
			    <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: center; padding: 7px;">
    				<img src="<?php echo $product->image; ?>" />
    			</td>
			    <?php
			}
			
			if (in_array('product_sku', $productFieldsDisplayArr))
			{
			    ?>
			    <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
    				<?php echo $product->product_sku; ?>
    			</td>
			    <?php
			}
			
			if (in_array('product_quantity', $productFieldsDisplayArr))
			{
			    ?>
			    <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
    				<?php echo $product->quantity; ?>
    			</td>
			    <?php
			}
			
			if (in_array('product_custom_message', $productFieldsDisplayArr))
			{
				?>
			    <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
    				<?php echo $product->product_custom_message; ?>
    			</td>
			    <?php
			}
			?>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
				<?php echo $product->price; ?>
			</td>
			<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
				<?php echo $product->total_price; ?>
			</td>
		</tr>
		<?php
		}
		?>
	</tbody>
	<tfoot>
		<?php
		foreach ($orderTotals as $total)
		{
			?>
			<tr>
				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;" colspan="<?php echo $colspan; ?>">
					<b><?php echo Text::_($total->title); ?></b>
				</td>
				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;">
					<?php echo $total->text; ?>
				</td>
			</tr>
		<?php
		}
		?>
	</tfoot>
</table>