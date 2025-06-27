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
$bootstrapHelper        = $this->bootstrapHelper;
$rowFluidClass          = $bootstrapHelper->getClassMapping('row-fluid');
$span4Class             = $bootstrapHelper->getClassMapping('span4');
$span8Class             = $bootstrapHelper->getClassMapping('span8');
?>
<div class="page-header">
	<h1 class="page-title eshop-title"><?php echo $this->manufacturer->manufacturer_page_heading != '' ? $this->manufacturer->manufacturer_page_heading : $this->manufacturer->manufacturer_name; ?></h1>
</div>	
<div class="<?php echo $rowFluidClass; ?>">
	<div class="<?php echo $span4Class; ?>">
		<img src="<?php echo $this->manufacturer->image; ?>" title="<?php echo $this->manufacturer->manufacturer_page_title != '' ? $this->manufacturer->manufacturer_page_title : $this->manufacturer->manufacturer_name; ?>" alt="<?php echo $this->manufacturer->manufacturer_alt_image != '' ? $this->manufacturer->manufacturer_alt_image : $this->manufacturer->manufacturer_name; ?>" />
	</div>
	<div class="<?php echo $span8Class; ?>"><?php echo $this->manufacturer->manufacturer_desc; ?></div>		
</div>
<?php
if (count($this->products))
{
	?>
	<div class="eshop-products-list">
		<?php
		echo EShopHtmlHelper::loadCommonLayout ('common/products.php', array (
			'products' => $this->products,
			'pagination' => $this->pagination,
			'sort_options' => $this->sort_options,
			'tax' => $this->tax,
			'currency' => $this->currency,
			'productsPerRow' => $this->productsPerRow,
			'catId' => 0,
			'actionUrl' => $this->actionUrl,
			'showSortOptions' => true,
		    'bootstrapHelper' => $bootstrapHelper
		));
		?>
	</div>
	<?php
}