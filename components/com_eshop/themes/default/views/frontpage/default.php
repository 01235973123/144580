<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined ( '_JEXEC' ) or die ();

//Display Shop Instroduction
if (EShopHelper::getMessageValue('shop_introduction') != '' && EShopHelper::getConfigValue('introduction_display_on', 'front_page') == 'front_page')
{
	?>
	<div class="eshop-shop-introduction"><?php echo EShopHelper::getMessageValue('shop_introduction'); ?></div>
	<?php
}

//Display Page Heading
if ($this->params->get('show_page_heading'))
{
	?>
	<div class="page-header">
		<h1 class="page-title eshop-title"><?php echo $this->params->get('page_heading'); ?></h1>
	</div>	
	<?php
}

if (count($this->categories)) 
{
	?>
	<div class="eshop-categories-list">
		<?php echo EShopHtmlHelper::loadCommonLayout('common/categories.php', array ('categories' => $this->categories, 'categoriesPerRow' => $this->categoriesPerRow, 'bootstrapHelper' => $this->bootstrapHelper)); ?>
	</div>
	<hr />	
	<?php
}

if (count($this->products))
{
	?>
	<div class="eshop-products-list">
		<?php
		echo EShopHtmlHelper::loadCommonLayout('common/products.php', array(
			'products' => $this->products,
			'tax' => $this->tax,
			'currency' => $this->currency,
			'productsPerRow' => $this->productsPerRow,
			'catId' => 0,
			'showSortOptions' => false,
		    'bootstrapHelper' => $this->bootstrapHelper
		));
		?>
	</div>
	<?php
}