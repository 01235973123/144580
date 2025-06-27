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

?>
<div class="page-header">
	<h1 class="page-title eshop-title"><?php echo Text::_('ESHOP_SEARCH_RESULT'); ?></h1>
</div>
<?php
if (count($this->products))
{
	?>
	<div class="eshop-products-list">
		<?php
		echo EShopHtmlHelper::loadCommonLayout('common/products.php', array (
			'products' => $this->products,
			'pagination' => $this->pagination,
			'sort_options' => $this->sort_options,
			'tax' => $this->tax,
			'currency' => $this->currency,
			'productsPerRow' => $this->productsPerRow,
			'catId' => 0,
			'actionUrl' => $this->actionUrl,
			'showSortOptions' => true,
		    'bootstrapHelper' => $this->bootstrapHelper
		));
		?>
	</div>
	<?php
}
else
{
	?>
	<div class="eshop-empty-search-result"><?php echo Text::_('ESHOP_NO_PRODUCTS_FOUND'); ?></div>
	<?php
}