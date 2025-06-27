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

$rowFluidClass          = $bootstrapHelper->getClassMapping('row-fluid');
$span3Class             = $bootstrapHelper->getClassMapping('span3');
$imgPolaroid            = $bootstrapHelper->getClassMapping('img-polaroid');
?>
<div class="plugin_products <?php echo $rowFluidClass; ?>">
    <?php
    for ($i = 0; $n = count($products), $i < $n; $i++)
    {
    	$product = $products[$i];
    	?>
    	<div class="<?php echo $span3Class; ?>">
    		<div class="image <?php echo $imgPolaroid; ?>">
    			<a href="<?php echo Route::_(EShopRoute::getProductRoute($product->id, EShopHelper::getProductCategory($product->id))); ?>">
    				<img src="<?php echo $product->thumb_image; ?>" title="<?php echo $product->product_page_title != '' ? $product->product_page_title : $product->product_name; ?>" alt="<?php echo $product->product_alt_image != '' ? $product->product_alt_image : $product->product_name; ?>" />
        			</a>
              	</div>
                <div class="name">
                    <a href="<?php echo Route::_(EShopRoute::getProductRoute($product->id, EShopHelper::getProductCategory($product->id))); ?>">
                        <h5><?php echo $product->product_name; ?></h5>
                    </a>
                    <?php
                    if (EShopHelper::showPrice() && !$product->product_call_for_price)
                    {
                        echo Text::_('ESHOP_PRICE'); ?>:
                        <?php
                        $productPriceArray = EShopHelper::getProductPriceArray($product->id, $product->product_price);
                        if ($productPriceArray['salePrice'] >= 0)
                        {
                            ?>
                            <span class="eshop-base-price"><?php echo $currency->format($tax->calculate($productPriceArray['basePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>&nbsp;
                            <span class="eshop-sale-price"><?php echo $currency->format($tax->calculate($productPriceArray['salePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>
                            <?php
                        }
                        else
                        {
                            ?>
                            <span class="price"><?php echo $currency->format($tax->calculate($productPriceArray['basePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>
                            <?php
                        }
                    }
                    if ($product->product_call_for_price)
                    {
                    	?>
    					<span class="call-for-price">
    						<?php echo Text::_('ESHOP_CALL_FOR_PRICE'); ?>: <?php echo EShopHelper::getConfigValue('telephone'); ?>
    					</span>
    					<?php
                    }
                    ?>
                </div>
    		</div>
    		<?php
    	if ($i > 0 && ($i + 1) % 4 == 0)
    	{
    		?>
    		</div><div class="plugin_products <?php echo $rowFluidClass; ?>">
    		<?php
    	}
    }
    ?>
</div>