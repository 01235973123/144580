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
use Joomla\CMS\Router\Route;

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
			    <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">
    				<?php echo Text::_('ESHOP_CUSTOM_MESSAGE'); ?>
    			</td>
			    <?php
			}
			?>
			<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">
				<?php echo Text::_('ESHOP_UNIT_PRICE'); ?>
			</td>
			<?php
			if ($showDownloadLink)
			{
				?>
				<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: center; padding: 7px; color: #222222;">
					<?php echo Text::_('ESHOP_DOWNLOADS'); ?>
				</td>
				<?php
			}
			?>
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
			<?php
			if ($showDownloadLink)
			{
				$colspan = $colspan + 1;
				//Show list of download links for each product
				?>
				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
					<?php
					if (count($product->downloads))
					{
						for ($i = 0; $n = count($product->downloads), $i < $n; $i++)
						{
							$download = $product->downloads[$i];
							?>
							<?php echo ($i + 1) . '. '?><a href="<?php echo Route::_(EShopHelper::getSiteUrl() . 'index.php?option=com_eshop&task=customer.downloadFile&order_id='.intval($download->order_id).'&download_code='.$download->download_code); ?>"title="<?php echo Text::_('ESHOP_DOWNLOAD'); ?>"><?php echo $download->download_name; ?></a>
							<?php
							if ($i < ($n - 1))
							{
								echo '<br />';
							}
						}
					}
					?>
				</td>
				<?php
			}
			?>
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