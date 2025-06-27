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

use Joomla\CMS\Language\Text;

if (count($this->products))
{
	
	if (EShopHelper::getConfigValue('products_filter_layout', 'default') == 'default')
	{
		$productsFilterLayout = '';
	}
	else
	{
		$productsFilterLayout = '_table';	
	}
	
	echo EShopHtmlHelper::loadCommonLayout('common/products_list' . $productsFilterLayout . '.php', array(
		'categories'      => $this->categories,
		'products'        => $this->products,
		'pagination'      => $this->pagination,
		'actionUrl'		  => $this->actionUrl,
		'sort_options'    => $this->sort_options,
		'tax'             => $this->tax,
		'currency'        => $this->currency,
		'category'        => $this->category,
		'productsPerRow'  => $this->productsPerRow,
		'catId'           => 0,
		'showSortOptions' => true,
		'manufacturers'   => $this->manufacturers,
		'attributes'      => $this->attributes,
		'options'         => $this->options,
		'filterData'      => $this->filterData,
	    'bootstrapHelper' => $this->bootstrapHelper,
	    'attributeGroups' => $this->attributeGroups,
	    'productAttributes' => $this->productAttributes
	));
}
else
{
?>
	<div class="eshop-empty-search-result"><?php echo Text::_('ESHOP_NO_PRODUCTS_FOUND'); ?></div>
<?php
}