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

$uri = Uri::getInstance();
?>
<h2>
	<strong>
		<?php echo Text::_('ESHOP_PRICE'); ?>:
		<?php
		$productPriceArray = EShopHelper::getProductPriceArray($this->item->id, $this->item->product_price);
		if ($productPriceArray['salePrice'] >= 0)
		{
			?>
			<span class="eshop-base-price"><?php echo $this->currency->format($this->tax->calculate($productPriceArray['basePrice'] + $this->option_price, $this->item->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>&nbsp;
			<span class="eshop-sale-price"><?php echo $this->currency->format($this->tax->calculate($productPriceArray['salePrice'] + $this->option_price, $this->item->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>
			<?php
		}
		else
		{
			?>
			<span class="price"><?php echo $this->currency->format($this->tax->calculate($productPriceArray['basePrice'] + $this->option_price, $this->item->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>
			<?php
		}
		?>
	</strong><br />
	<?php
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
    			<span class="eshop-base-price"><?php echo $this->currency->format($productPriceArray['basePrice'] + $this->option_price); ?></span>&nbsp;
    			<span class="eshop-sale-price"><?php echo $this->currency->format($productPriceArray['salePrice'] + $this->option_price); ?></span>
    			<?php
		    }
		    else 
		    {
		        ?>
    			<span class="eshop-sale-price"><?php echo $this->currency->format($productPriceArray['salePrice'] + $this->option_price); ?></span>
    			<?php
		    }		
		}
		else
		{
			?>
			<span class="price"><?php echo $this->currency->format($productPriceArray['basePrice'] + $this->option_price); ?></span>
			<?php
		}
		?>
		</small>
		<?php
	}
	?>
</h2>