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

if ($this->option_value != '')
{
	if (EShopHelper::getConfigValue('show_sku'))
	{
	    ?>
	    <div class="product-sku">
	    	<strong><?php echo Text::_('ESHOP_PRODUCT_CODE'); ?>:</strong>
	    	<span><?php echo ($this->option_sku != '' ? $this->option_sku : $this->item->product_sku); ?></span>
	    </div>
	    <?php
	}
	
	$productInventory = EShopHelper::getProductInventory($this->item->id);
	
	if ($productInventory['product_manage_stock'] && $productInventory['product_show_availability'])
	{
	    ?>
	    <div class="product-availability">
	    	<strong><?php echo Text::_('ESHOP_AVAILABILITY'); ?>:</strong>
	    	<span><?php echo ($this->option_quantity != '' ? $this->option_quantity : $this->item->availability); ?></span>
	    </div>
	    <?php
	}
	
	if (EShopHelper::getConfigValue('show_product_weight'))
	{
	    ?>
	    <div class="product-weight">
	    	<strong><?php echo Text::_('ESHOP_PRODUCT_WEIGHT'); ?>:</strong>
	    	<span><?php echo number_format($this->option_weight != '' ? $this->option_weight + $this->item->product_weight : $this->item->product_weight, 2).EShopHelper::getWeightUnit($this->item->product_weight_id, Factory::getLanguage()->getTag()); ?></span>
	    </div>
	    <?php   
	}
}
else
{
	if (EShopHelper::getConfigValue('show_sku'))
	{
	?>
	    <div class="product-sku">
	    	<strong><?php echo Text::_('ESHOP_PRODUCT_CODE'); ?>:</strong>
	    	<span><?php echo $this->item->product_sku; ?></span>
	    </div>
	    <?php
	}
	
	$productInventory = EShopHelper::getProductInventory($this->item->id);
	
	if ($productInventory['product_manage_stock'] && $productInventory['product_show_availability'])
	{
	    ?>
	    <div class="product-availability">
	    	<strong><?php echo Text::_('ESHOP_AVAILABILITY'); ?>:</strong>
	    	<span><?php echo $this->item->availability; ?></span>
	    </div>
	    <?php
	}
	
	if (EShopHelper::getConfigValue('show_product_weight'))
	{
	    ?>
	    <div class="product-weight">
	    	<strong><?php echo Text::_('ESHOP_PRODUCT_WEIGHT'); ?>:</strong>
	    	<span><?php echo number_format($this->item->product_weight, 2).EShopHelper::getWeightUnit($this->item->product_weight_id, Factory::getLanguage()->getTag()); ?></span>
	    </div>
	    <?php   
	}
}