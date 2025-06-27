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
echo $this->loadTemplate('category');

if (count ($this->subCategories) && EShopHelper::getConfigValue('show_sub_categories'))
{
?>
	<div class="eshop-sub-categories-list"><?php echo EShopHtmlHelper::loadCommonLayout('common/sub_categories.php', array ('subCategories' => $this->subCategories, 'subCategoriesPerRow' => $this->subCategoriesPerRow, 'bootstrapHelper' => $this->bootstrapHelper)); ?></div>
	<?php
}

if (!count($this->products))
{
	return;
}

if ($this->category->category_layout == 'table')
{
	$productContainerClass = 'eshop-products-table';
	$productsLayout        = 'common/products_table.php';
}
else
{
	$productContainerClass = 'eshop-products-list';
	$productsLayout        = 'common/products.php';
}
?>
<div class="<?php echo $productContainerClass; ?>">
	<?php
	echo EShopHtmlHelper::loadCommonLayout($productsLayout, [
		'products'          => $this->products,
		'pagination'        => $this->pagination,
		'sort_options'      => $this->sort_options,
		'tax'               => $this->tax,
		'currency'          => $this->currency,
		'productsPerRow'    => $this->productsPerRow,
		'catId'             => $this->category->id,
		'actionUrl'         => $this->actionUrl,
		'showSortOptions'   => true,
		'bootstrapHelper'   => $this->bootstrapHelper,
		'attributeGroups'   => $this->attributeGroups,
		'productAttributes' => $this->productAttributes,
		'category'          => $this->category,
	]);
	?>
</div>