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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

?>
<table style="font-size: 14pt;">
    <tr>
        <td align="center" style="font-size: 24pt;">
            <?php echo $product->product_name; ?><br />
        </td>
    </tr>
    <tr>
        <td width="45%">
            <img src="<?php echo $product->thumb_image; ?>" />
        </td>
        <td width="55%" align="left">
        	<?php
            if (EShopHelper::getConfigValue('show_manufacturer'))
            {
            	?>
                <b><?php echo Text::_('ESHOP_BRAND'); ?>:</b>
                <?php echo $manufacturer->manufacturer_name ?? ''; ?><br />
            	<?php
            }
            if (EShopHelper::getConfigValue('show_sku'))
            {
            	?>
                <b><?php echo Text::_('ESHOP_PRODUCT_CODE'); ?>:</b>
                <?php echo $product->product_sku; ?><br />
            	<?php
            }
            if (EShopHelper::getConfigValue('show_availability'))
            {
            	?>
                <b><?php echo Text::_('ESHOP_AVAILABILITY'); ?>:</b>
                <?php
                echo $product->availability;
                if (isset($product_available_date))
                {
                	echo ' (' . Text::_('ESHOP_PRODUCT_AVAILABLE_DATE') . ': ' . $product_available_date . ')';
                }
                ?><br />
            	<?php
            }
            if (EShopHelper::getConfigValue('show_product_weight'))
            {
            	?>
                <b><?php echo Text::_('ESHOP_PRODUCT_WEIGHT'); ?>:</b>
                <?php echo number_format($product->product_weight, 2).EShopHelper::getWeightUnit($product->product_weight_id, Factory::getLanguage()->getTag()); ?><br />
            	<?php
            }
            if (EShopHelper::getConfigValue('show_product_dimensions'))
            {
            	?>
                <b><?php echo Text::_('ESHOP_PRODUCT_DIMENSIONS'); ?>:</b>
                <?php echo number_format($product->product_length, 2).EShopHelper::getLengthUnit($product->product_length_id, Factory::getLanguage()->getTag()) . ' x ' . number_format($product->product_width, 2).EShopHelper::getLengthUnit($product->product_length_id, Factory::getLanguage()->getTag()) . ' x ' . number_format($product->product_height, 2).EShopHelper::getLengthUnit($product->product_length_id, Factory::getLanguage()->getTag()); ?><br />
            	<?php
            }
            if (isset($product->paramData) && count($product->paramData))
            {
				foreach ($product->paramData as $param)
				{
					if ($param['value'])
					{
						?>
						<strong><?php echo $param['title']; ?>: </strong>
						<span><?php echo $param['value']; ?></span><br />
					<?php
					}
				}
			}
            //Product Price
			if (EShopHelper::showPrice() && !$product->product_call_for_price)
			{
				?>
				<h2>
					<b><?php echo Text::_('ESHOP_PRICE'); ?>:
					<?php
					$productPriceArray = EShopHelper::getProductPriceArray($product->id, $product->product_price);
					if ($productPriceArray['salePrice'] >= 0)
					{
						?>
						<span style="text-decoration: line-through; color: #FF0000;"><?php echo $currency->format($tax->calculate($productPriceArray['basePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>&nbsp;
						<span><?php echo $currency->format($tax->calculate($productPriceArray['salePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>
						<?php
					}
					else
					{
						?>
						<span class="price"><?php echo $currency->format($tax->calculate($productPriceArray['basePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>
						<?php
					}
					?></b><br /><?php
					if (EShopHelper::getConfigValue('tax') && EShopHelper::getConfigValue('display_ex_tax'))
					{
						?>
						<small>
							<?php echo Text::_('ESHOP_EX_TAX'); ?>:
                            <?php
                            if ($productPriceArray['salePrice'] >= 0)
                            {
                                if (EShopHelper::getConfigValue('display_ex_tax_base_price', 1))
                                {
                                    ?>
    								<span style="text-decoration: line-through; color: #FF0000;"><?php echo $currency->format($productPriceArray['basePrice']); ?></span>&nbsp;
    								<span><?php echo $currency->format($productPriceArray['salePrice']); ?></span>
    								<?php
                                }
                                else 
                                {
                                    ?>
    								<span><?php echo $currency->format($productPriceArray['salePrice']); ?></span>
    								<?php
                                }
                            }
                            else
                            {
								?>
								<span class="price"><?php echo $currency->format($productPriceArray['basePrice']); ?></span>
								<?php
                            }
                            ?>
						</small>
						<?php
					}
					?>
				</h2>
				<?php
				if (count($discountPrices))
				{
					for ($i = 0; $n = count($discountPrices), $i < $n; $i++)
					{
						$discountPrice = $discountPrices[$i];
						echo $discountPrice->quantity.' '.Text::_('ESHOP_OR_MORE').' '.$currency->format($tax->calculate($discountPrice->price, $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))).'<br />';
					}
				}
			}
			if ($product->product_call_for_price)
			{
				echo "<b>" . Text::_('ESHOP_CALL_FOR_PRICE') . "</b>: " . EShopHelper::getConfigValue('telephone');
			}
            ?>
        </td>
    </tr>
    <?php
    if ($product->product_short_desc != '')
	{
		?>
		<tr><td><b><?php echo Text::_('ESHOP_SHORT_DESCRIPTION');?></b></td></tr>
		<tr>
			<td width="100%">
				<?php echo $product->product_short_desc; ?><br />
			</td>
		</tr>
		<?php
	}
	if ($product->product_desc != '')
	{
		?>
		<tr><td><b><?php echo Text::_('ESHOP_DESCRIPTION');?></b></td></tr>
		<tr>
			<td width="100%">
				<?php echo $product->product_desc; ?><br />
			</td>
		</tr>
		<?php
	}
	if ($product->tab1_title != '' && $product->tab1_content != '')
	{
		?>
		<tr><td><b><?php echo $product->tab1_title; ?></b></td></tr>
		<tr>
			<td width="100%">
				<?php echo $product->tab1_content; ?><br />
			</td>
		</tr>
		<?php
	}
	if ($product->tab2_title != '' && $product->tab2_content != '')
	{
		?>
		<tr><td><b><?php echo $product->tab2_title; ?></b></td></tr>
		<tr>
			<td width="100%">
				<?php echo $product->tab2_content; ?><br />
			</td>
		</tr>
		<?php
	}
	if ($product->tab3_title != '' && $product->tab3_content != '')
	{
		?>
		<tr><td><b><?php echo $product->tab3_title; ?></b></td></tr>
		<tr>
			<td width="100%">
				<?php echo $product->tab3_content; ?><br />
			</td>
		</tr>
		<?php
	}
	if ($product->tab4_title != '' && $product->tab4_content != '')
	{
		?>
		<tr><td><b><?php echo $product->tab4_title; ?></b></td></tr>
		<tr>
			<td width="100%">
				<?php echo $product->tab4_content; ?><br />
			</td>
		</tr>
		<?php
	}
	if ($product->tab5_title != '' && $product->tab5_content != '')
	{
		?>
		<tr><td><b><?php echo $product->tab5_title; ?></b></td></tr>
		<tr>
			<td width="100%">
				<?php echo $product->tab5_content; ?><br />
			</td>
		</tr>
		<?php
	}
	if (EShopHelper::getConfigValue('show_specification') && $hasSpecification)
	{
	    ?>
	    <tr><td><b><?php echo Text::_('ESHOP_SPECIFICATION');?></b></td></tr>
	    <tr>
	    	<td width="100%">
				<table style="border: 1px solid #dddddd;" width="90%">
				    <?php
				    for ($i = 0; $n = count($attributeGroups), $i < $n; $i++)
				    {
				        if (count($productAttributes[$i]))
				        {
				        	?>
							<tr style="background-color: #cddddd">
								<td colspan="2" style="border: 1px solid #dddddd;"><?php echo $attributeGroups[$i]->attributegroup_name; ?></td>
							</tr>
							<?php
							for ($j = 0; $m = count($productAttributes[$i]), $j < $m; $j++)
							{
								?>
								<tr>
									<td width="35%" style="border: 1px solid #dddddd;"><?php echo $productAttributes[$i][$j]->attribute_name; ?></td>
									<td width="65%" style="border: 1px solid #dddddd;"><?php echo $productAttributes[$i][$j]->value; ?></td>
								</tr>
								<?php
							}
						}
					}
					?>
				</table>
			</td>
		</tr>
	<?php
	}
	if (count($productImages))
	{
		?>
		<tr><td><b><?php echo Text::_('ESHOP_PRODUCT_GALLERY');?></b></td></tr>
		<tr>
			<?php
			for ($i = 0; $n = count($productImages), $i < $n; $i++)
			{
				?>
				<td width="50%">
					<img src="<?php echo $productImages[$i]->thumb_image; ?>" />
				</td>
				<?php
				if (($i + 1) % 2 == 0)
				{
				?>
					</tr><tr>
					<?php
				}
	        }
			?>
		</tr>
		<?php
	}
	?>
</table>