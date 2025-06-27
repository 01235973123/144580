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
<table width="100%">
	<tr>
		<td style="background-color: #CDDDDD; text-align: left">
			<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>
		</td>
		<?php
		if (in_array('product_image', $productFieldsDisplayArr))
		{
		    ?>
		    <td style="background-color: #CDDDDD; text-align: center;">
    			<?php echo Text::_('ESHOP_IMAGE'); ?>
    		</td>
		    <?php
		}
		
		if (in_array('product_sku', $productFieldsDisplayArr))
		{
		    ?>
		    <td style="background-color: #CDDDDD; text-align: left;">
    			<?php echo Text::_('ESHOP_MODEL'); ?>
    		</td>
		    <?php
		}
		
		if (in_array('product_quantity', $productFieldsDisplayArr))
		{
		    ?>
		    <td style="background-color: #CDDDDD; text-align: left;">
    			<?php echo Text::_('ESHOP_QUANTITY'); ?>
    		</td>
		    <?php
		}
		
		if (in_array('product_custom_message', $productFieldsDisplayArr))
		{
			?>
		    <td style="background-color: #CDDDDD; text-align: left;">
    			<?php echo Text::_('ESHOP_CUSTOM_MESSAGE'); ?>
    		</td>
		    <?php
		}
		?>
		<td style="background-color: #CDDDDD; text-align: left;">
			<?php echo Text::_('ESHOP_UNIT_PRICE'); ?>
		</td>
		<td style="background-color: #CDDDDD; text-align: left;">
			<?php echo Text::_('ESHOP_TOTAL'); ?>
		</td>
	</tr>
	<?php 
	foreach ($orderProducts as $product)
	{
		$options = $product->options;
		?>
		<tr>
			<td style="vertical-align: middle;">
				<?php
				echo '<b>' . $product->product_name . '</b>';
				for ($i = 0; $n = count($options), $i < $n; $i++)
				{
					echo '<br />- ' . $options[$i]->option_name . ': ' . $options[$i]->option_value . (isset($options[$i]->sku) && $options[$i]->sku != '' ? ' (' . $options[$i]->sku . ')' : '');
				}
				?>
			</td>
			<?php
    		if (in_array('product_image', $productFieldsDisplayArr))
    		{
    		    ?>
    		    <td style="text-align: center; vertical-align: middle;">
					<img src="<?php echo $product->image; ?>" />
				</td>
    		    <?php
    		}
    		
    		if (in_array('product_sku', $productFieldsDisplayArr))
    		{
    		    ?>
    		    <td style="vertical-align: middle;">
    				<?php echo $product->product_sku; ?>
    			</td>
    		    <?php
    		}
    		
    		if (in_array('product_quantity', $productFieldsDisplayArr))
    		{
    		    ?>
    		    <td style="vertical-align: middle;">
    				<?php echo $product->quantity; ?>
    			</td>
    		    <?php
    		}
    		
    		if (in_array('product_custom_message', $productFieldsDisplayArr))
    		{
    			?>
				<td style="vertical-align: middle;">
					<?php echo $product->product_custom_message; ?>
				</td>
				<?php
			}
    		?>
			<td style="vertical-align: middle;">
				<?php echo $product->price; ?>
			</td>
			<td style="vertical-align: middle;">
				<?php echo $product->total_price; ?>
			</td>
		</tr>
		<?php 
	}
	foreach ($orderTotals as $orderTotal)
	{
		?>
		<tr>
			<td colspan="<?php echo $colspan; ?>" style="text-align: right;">
				<?php echo Text::_($orderTotal->title); ?>:
			</td>
			<td>
				<strong><?php echo $orderTotal->text; ?></strong>
			</td>
		</tr>
		<?php
	}
	?>
</table>