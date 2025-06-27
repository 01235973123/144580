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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$span = intval(12 / $productsPerRow);
$rowFluidClass          		= $bootstrapHelper->getClassMapping('row-fluid');
$spanClass              		= $bootstrapHelper->getClassMapping('span' . $span);
$span3Class             		= $bootstrapHelper->getClassMapping('span3');
$span9Class             		= $bootstrapHelper->getClassMapping('span9');
$hiddenPhoneClass       		= $bootstrapHelper->getClassMapping('hidden-phone');
$inputAppendClass       		= $bootstrapHelper->getClassMapping('input-append');
$inputPrependClass      		= $bootstrapHelper->getClassMapping('input-prepend');
$imgPolaroid            		= $bootstrapHelper->getClassMapping('img-polaroid');
$btnClass						= $bootstrapHelper->getClassMapping('btn');
$btnBtnSecondaryClass			= $bootstrapHelper->getClassMapping('btn btn-secondary');
$btnBtnPrimaryClass				= $bootstrapHelper->getClassMapping('btn btn-primary');
$productsFilterVisibleItems     = EShopHelper::getConfigValue('products_filter_visible_items', 0);

$rootUri = Uri::root(true);
?>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/colorbox/jquery.colorbox.js" type="text/javascript"></script>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/js/jquery.cookie.js" type="text/javascript"></script>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/js/layout.js" type="text/javascript"></script>
<script>
	Eshop.jQuery(function($){
		$(document).ready(function() {	
			changeLayout('<?php echo EShopHelper::getConfigValue('default_products_layout', 'list'); ?>');
		});
	});
</script>
<div id="products-list-container" class="products-list-container block <?php echo EShopHelper::getConfigValue('default_products_layout', 'list'); ?>">
<div class="sortPagiBar <?php echo $rowFluidClass; ?> clearfix">
	<div class="<?php echo $span3Class; ?>">
		<div class="btn-group <?php echo $hiddenPhoneClass; ?>">
			<?php
			if (EShopHelper::getConfigValue('default_products_layout') == 'grid')
			{
				?>
				<a rel="grid" href="#" class="<?php echo $btnBtnSecondaryClass; ?>"><i class="icon-th-large"></i></a>
				<a rel="list" href="#" class="<?php echo $btnBtnSecondaryClass; ?>"><i class="icon-th-list"></i></a>
				<?php
			}
			else 
			{
				?>
				<a rel="list" href="#" class="<?php echo $btnBtnSecondaryClass; ?>"><i class="icon-th-list"></i></a>
				<a rel="grid" href="#" class="<?php echo $btnBtnSecondaryClass; ?>"><i class="icon-th-large"></i></a>
				<?php
			}
			?>
		</div>
	</div>
	<?php
	if ($showSortOptions)
	{
		?>
		<div class="<?php echo $span9Class; ?>">
			<form method="post" name="adminForm" id="adminForm" action="<?php echo $actionUrl; ?>">
				<div class="clearfix">
					<div class="eshop-product-show">
						<b><?php echo Text::_('ESHOP_SHOW'); ?>: </b>
						<?php echo $pagination->getLimitBox(); ?>
					</div>
					<?php
					if ($sort_options)
					{
						?>
						<div class="eshop-product-sorting">
							<b><?php echo Text::_('ESHOP_SORTING_BY'); ?>: </b>
							<?php echo $sort_options; ?>
						</div>
						<?php
					}
                    ?>
				</div>
			</form> 
		</div>
	<?php					
	}
	?>
</div>
<div class="<?php echo $rowFluidClass; ?> clearfix">
	<?php
	foreach ($products as $product)
	{
		if (!empty($product->product_main_category_id))
		{
			$mainCategoryId = $product->product_main_category_id;
		}
		else
		{
			// This else case is just for backward compatible purpose
			$mainCategoryId = EShopHelper::getProductCategory($product->id);
		}

		$productUrl = Route::_(EShopRoute::getProductRoute($product->id, $mainCategoryId));
		?>
		<div class="<?php echo $spanClass; ?> ajax-block-product spanbox clearfix">
			<div class="eshop-image-block">
				<div class="image <?php echo $imgPolaroid; ?>">
					<a href="<?php echo $productUrl; ?>">
						<?php
						if (count($product->labels))
						{
							for ($i = 0; $n = count($product->labels), $i < $n; $i++)
							{
								$label = $product->labels[$i];
								if ($label->label_style == 'rotated' && !($label->enable_image && $label->label_image))
								{
									?>
									<div class="cut-rotated">
									<?php
								}
								if ($label->enable_image && $label->label_image)
								{
									$imageWidth = $label->label_image_width > 0 ? $label->label_image_width : EShopHelper::getConfigValue('label_image_width');
									if (!$imageWidth)
										$imageWidth = 50;
									$imageHeight = $label->label_image_height > 0 ? $label->label_image_height : EShopHelper::getConfigValue('label_image_height');
									if (!$imageHeight)
										$imageHeight = 50;
									?>
									<span class="horizontal <?php echo $label->label_position; ?> small-db"
										  style="opacity: <?php echo $label->label_opacity; ?>;<?php echo 'background-image: url(' . $label->label_image . ')'; ?>; background-repeat: no-repeat; width: <?php echo $imageWidth; ?>px; height: <?php echo $imageHeight; ?>px; box-shadow: none;"></span>
									<?php
								}
								else
								{
									?>
									<span
										class="<?php echo $label->label_style; ?> <?php echo $label->label_position; ?> small-db"
										style="background-color: <?php echo '#' . $label->label_background_color; ?>; color: <?php echo '#' . $label->label_foreground_color; ?>; opacity: <?php echo $label->label_opacity; ?>;<?php if ($label->label_bold) echo 'font-weight: bold;'; ?>">
														<?php echo $label->label_name; ?>
													</span>
									<?php
								}
								if ($label->label_style == 'rotated' && !($label->enable_image && $label->label_image))
								{
									?>
									</div>
									<?php
								}
							}
						}
						?>
						<span class="product-image">
							<img src="<?php echo $product->image; ?>" title="<?php echo $product->product_page_title != '' ? EShopHelper::escape($product->product_page_title) : EShopHelper::escape($product->product_name); ?>" alt="<?php echo $product->product_alt_image != '' ? EShopHelper::escape($product->product_alt_image) : EShopHelper::escape($product->product_name); ?>" />
						</span>
						<?php 
						if (isset($product->additional_image) && EShopHelper::getConfigValue('product_image_rollover', 0))
						{
							?>
							<span class="additional-image">
								<?php echo "<img src='".$product->additional_image."' />"; ?>
							</span>
							<?php
						}
						?>
					</a>
				</div>
			</div>
			<div class="eshop-info-block">
				<h5><a href="<?php echo $productUrl; ?>" title="<?php echo EShopHelper::escape($product->product_name); ?>"><?php echo $product->product_name; ?></a></h5>
				<div class="eshop-product-desc"><?php echo $product->product_short_desc; ?></div>
				<?php
				$productId = $product->id;
				
				if (EShopHelper::getConfigValue('show_product_attributes', 0) && isset($productAttributes[$productId]) && count($productAttributes[$productId]) > 0)
				{
				    ?>
				    <table class="table table-bordered">
        			<?php
        			for ($i = 0; $n = count($attributeGroups), $i < $n; $i++)
        			{
        				if (count($productAttributes[$productId][$i]))
        				{
        					?>
        					<thead>
        						<tr>
        							<th colspan="2"><?php echo $attributeGroups[$i]->attributegroup_name; ?></th>
        						</tr>
        					</thead>
        					<tbody>
        						<?php
        						for ($j = 0; $m = count($productAttributes[$productId][$i]), $j < $m; $j++)
        						{
        							?>
        							<tr>
        								<td width="30%"><?php echo $productAttributes[$productId][$i][$j]->attribute_name; ?></td>
        								<td width="70%"><?php echo $productAttributes[$productId][$i][$j]->value; ?></td>
        							</tr>
        							<?php
        						}
        						?>
        					</tbody>
        					<?php
        				}
            		}
            		?>
            		</table>
				    <?php
				}
				?>
				<div class="eshop-product-price">
					<?php
					if ($product->product_price_text != '')
					{
						echo $product->product_price_text;
					}
					else
					{
						if (EShopHelper::showPrice() && !$product->product_call_for_price)
						{	
							$productPriceArray = EShopHelper::getProductPriceArray($product->id, $product->product_price);
							if ($productPriceArray['salePrice'] >= 0)
							{
								?>
								<span
									class="eshop-base-price"><?php echo $currency->format($tax->calculate($productPriceArray['basePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>&nbsp;
								<span
									class="eshop-sale-price"><?php echo $currency->format($tax->calculate($productPriceArray['salePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>
								<?php
							}
							else
							{
								?>
								<span
									class="price"><?php echo $currency->format($tax->calculate($productPriceArray['basePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>
								<?php
							}
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
											<span class="eshop-base-price"><?php echo $currency->format($productPriceArray['basePrice']); ?></span>&nbsp;
											<span class="eshop-sale-price"><?php echo $currency->format($productPriceArray['salePrice']); ?></span>
											<?php
										}
										else 
										{
											?>
											<span class="eshop-sale-price"><?php echo $currency->format($productPriceArray['salePrice']); ?></span>
											<?php
										}
									}
									else
									{
										?>
										<span class="product-price"><?php echo $currency->format($productPriceArray['basePrice']); ?></span>
										<?php
									}
									?>
								</small>
								<?php
							}
						}
						if ($product->product_call_for_price)
						{
							echo Text::_('ESHOP_CALL_FOR_PRICE'); ?>: <?php echo EShopHelper::getConfigValue('telephone');
						}
					}
					?>
				</div>
			</div>
			<div class="eshop-buttons">
				<?php
				if (property_exists($product, 'has_required_otpion'))
				{
					$productHasRequiredOption = $product->has_required_otpion;
				}
				else
				{
					$productHasRequiredOption = EShopHelper::isRequiredOptionProduct($product->id);
				}

				if (!$productHasRequiredOption)
				{
					//$isCartMode  = EShopHelper::isCartMode($product);
					$isCartMode  = $product->is_product_cart_mode;
					$isQuoteMode = EShopHelper::isQuoteMode($product);

					if ($isCartMode || $isQuoteMode)
					{
						?>
						<div class="eshop-cart-area">
							<?php
							if (EShopHelper::showProductQuantityBox($product->id, 'show_quantity_box'))
							{
								?>
								<div class="<?php echo $inputAppendClass; ?> <?php echo $inputPrependClass; ?>">
									<span class="eshop-quantity">
										<a onclick="quantityUpdate('-', 'quantity_<?php echo $product->id; ?>', <?php echo EShopHelper::getConfigValue('quantity_step', '1'); ?>)" class="<?php echo $btnBtnSecondaryClass; ?> button-minus" id="<?php echo $product->id; ?>">-</a>
											<input type="text" class="input-small form-control eshop-quantity-value" id="quantity_<?php echo $product->id; ?>" name="quantity[]" value="<?php echo EShopHelper::getConfigValue('start_quantity_number', '1'); ?>" />
											<?php
											if (EShopHelper::getConfigValue('one_add_to_cart_button', '0'))
											{
												?>
												<input type="hidden" name="product_id[]" value="<?php echo $product->id; ?>" />
												<?php
											}
											?>
										<a onclick="quantityUpdate('+', 'quantity_<?php echo $product->id; ?>', <?php echo EShopHelper::getConfigValue('quantity_step', '1'); ?>)" class="<?php echo $btnBtnSecondaryClass; ?> button-plus" id="<?php echo $product->id; ?>">+</a>
									</span>
								</div>
								<?php
							}
							if ($isCartMode && !EShopHelper::getConfigValue('one_add_to_cart_button', '0'))
							{
								$message = EShopHelper::getAddToCartSuccessMessage($product, $productUrl);
								?>
								<input id="add-to-cart-<?php echo $product->id; ?>" type="button" class="<?php echo $btnBtnPrimaryClass; ?>"
									   onclick="addToCart(<?php echo $product->id; ?>, 1, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>', '<?php echo EShopHelper::getConfigValue('cart_popout')?>', '<?php echo Route::_(EShopRoute::getViewRoute('cart')); ?>', '<?php echo $message; ?>');"
									   value="<?php echo Text::_('ESHOP_ADD_TO_CART'); ?>"/>
								<?php
							}

							if ($isQuoteMode)
							{
								?>
								<input id="add-to-quote-<?php echo $product->id; ?>" type="button" class="<?php echo $btnBtnPrimaryClass; ?>"
									   onclick="addToQuote(<?php echo $product->id; ?>, 1, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>');"
									   value="<?php echo Text::_('ESHOP_ADD_TO_QUOTE'); ?>"/>
								<?php
							}
							?>
						</div>
						<?php
					}
				}
				else 
				{
					?>
					<div class="eshop-cart-area">
						<a class="<?php echo $btnBtnPrimaryClass; ?>" href="<?php echo $productUrl; ?>" title="<?php echo EShopHelper::escape($product->product_name); ?>"><?php echo Text::_('ESHOP_PRODUCT_VIEW_DETAILS'); ?></a>
					</div>	
					<?php
				}
				
				if (($productInventory['product_manage_stock'] && $product->product_quantity <= 0 && EShopHelper::getConfigValue('allow_notify')) || EShopHelper::getConfigValue('allow_wishlist') || EShopHelper::getConfigValue('allow_compare'))
				{
					if (EShopHelper::getConfigValue('use_button_icons', 0))
					{
						?>
						<div class="box-action-icons-list">
							<?php
							if ($productInventory['product_manage_stock'] && $product->product_quantity <= 0 && EShopHelper::getConfigValue('allow_notify'))
							{
								?>
								<a class="<?php echo $btnBtnSecondaryClass; ?>"
								   onclick="makeNotify(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>')"><?php echo EShopHelper::getConfigValue('notify_button_code'); ?></a>
								<?php
							}
							if (EShopHelper::getConfigValue('allow_wishlist'))
							{
								?>
								<a class="<?php echo $btnBtnSecondaryClass; ?>" style="cursor: pointer;"
								   onclick="addToWishList(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>')"
								   title="<?php echo Text::_('ESHOP_ADD_TO_WISH_LIST'); ?>"><?php echo EShopHelper::getConfigValue('wishlist_button_code'); ?></a>
								<?php
							}
							if (EShopHelper::getConfigValue('allow_compare'))
							{
								?>
								<a class="<?php echo $btnBtnSecondaryClass; ?>" style="cursor: pointer;"
								   onclick="addToCompare(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>')"
								   title="<?php echo Text::_('ESHOP_ADD_TO_COMPARE'); ?>"><?php echo EShopHelper::getConfigValue('compare_button_code'); ?></a>
								<?php
							}
							?>
						</div>
						<?php
					}
					else 
					{
						?>
						<div class="box-action-text-list">
							<?php
							if ($productInventory['product_manage_stock'] && $product->product_quantity <= 0 && EShopHelper::getConfigValue('allow_notify'))
							{
								?>
								<a class="<?php echo $btnBtnSecondaryClass; ?>"
								   onclick="makeNotify(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>')"><?php echo Text::_('ESHOP_PRODUCT_NOTIFY'); ?></a>
								<?php
							}
							if (EShopHelper::getConfigValue('allow_wishlist'))
							{
								?>
								<a class="<?php echo $btnBtnSecondaryClass; ?>" style="cursor: pointer;"
								   onclick="addToWishList(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>')"
								   title="<?php echo Text::_('ESHOP_ADD_TO_WISH_LIST'); ?>"><?php echo Text::_('ESHOP_ADD_TO_WISH_LIST'); ?></a>
								<?php
							}
							if (EShopHelper::getConfigValue('allow_compare'))
							{
								?>
								<a class="<?php echo $btnBtnSecondaryClass; ?>" style="cursor: pointer;"
								   onclick="addToCompare(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>')"
								   title="<?php echo Text::_('ESHOP_ADD_TO_COMPARE'); ?>"><?php echo Text::_('ESHOP_ADD_TO_COMPARE'); ?></a>
								<?php
							}
							?>
						</div>
						<?php
					}
				}
				?>
			</div>
		</div>
	<?php
	}
	?>
</div>
<?php
if (EShopHelper::getConfigValue('show_quantity_box') && EShopHelper::getConfigValue('one_add_to_cart_button', '0'))
{
	?>
	<div class="<?php echo $rowFluidClass; ?>">
		<input id="multiple-products-add-to-cart" type="button" class="<?php echo $btnBtnPrimaryClass; ?>" onclick="multipleProductsAddToCart('<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>', '<?php echo EShopHelper::getConfigValue('cart_popout')?>', '<?php echo Route::_(EShopRoute::getViewRoute('cart')); ?>');" value="<?php echo Text::_('ESHOP_MULTIPLE_PRODUCTS_ADD_TO_CART'); ?>" />
	</div>
	<?php
}
		
if (isset($pagination) && ($pagination->total > $pagination->limit))
{
	?>
	<div class="<?php echo $rowFluidClass; ?>">
		<div class="pagination">
			<?php echo $pagination->getPagesLinks(); ?>
		</div>
	</div>
	<?php
}

?>
<div id="eshop-filter-categories-result" style="display: none;">
	<div class="panel-body">
		<?php
		$visibleCategories = array();
		
		for ($i = 0; $i < count($categories); $i++)
		{
			if ($categories[$i]->number_products > 0)
			{
				$visibleCategories[] = $categories[$i];
			}
		}
		
		$countCategories = count($visibleCategories);
		
		if ($productsFilterVisibleItems > 0 && $countCategories > $productsFilterVisibleItems)
		{
			?>
			<ul>
				<?php
				if (!empty($category))
				{
					?>
					<li>
						<a class="eshop-filter-selected-category" href="javascript:eshop_filter_by_category(<?php echo $category->category_parent_id; ?>);"><strong><?php echo $category->category_name; ?></strong></a>
					</li>
					<?php
				}
		
				for ($i = 0; $i < $productsFilterVisibleItems; $i++)
				{
					$category = $visibleCategories[$i];
					?>
					<li>
						<a href="javascript:eshop_filter_by_category(<?php echo $category->id; ?>);"><?php echo $category->category_name; ?><span class="badge badge-info"><?php echo $category->number_products;?></span></a>
					</li>
					<?php
				}
				?>
			</ul>
			<ul id="more-categories" style="display: none;">
				<?php
				for ($i = $productsFilterVisibleItems; $i < count($visibleCategories); $i++)
				{
					$category = $visibleCategories[$i];
					?>
					<li>
						<a href="javascript:eshop_filter_by_category(<?php echo $category->id; ?>);"><?php echo $category->category_name; ?><span class="badge badge-info"><?php echo $category->number_products;?></span></a>
					</li>
					<?php
				}
				?>
			</ul>
			<span id="toggle-categories" class="eshop-toggle-link"><a onclick="toggleItems('categories', 'more');"><?php echo Text::_('ESHOP_SHOW_MORE'); ?></a></span>
			<?php
		}
		else 
		{
			?>
			<ul>
				<?php
				if (!empty($category))
				{
					?>
					<li>
						<a class="eshop-filter-selected-category" href="javascript:eshop_filter_by_category(<?php echo $category->category_parent_id; ?>);"><strong><?php echo $category->category_name; ?></strong></a>
					</li>
					<?php
				}
		
				for ($i = 0; $n = count($visibleCategories), $i < $n; $i++)
				{
					$category = $visibleCategories[$i];
					?>
					<li>
						<a href="javascript:eshop_filter_by_category(<?php echo $category->id; ?>);"><?php echo $category->category_name; ?><span class="badge badge-info"><?php echo $category->number_products;?></span></a>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		}		
		?>
	</div>
</div>
<?php

if (count($manufacturers) > 0)
{
?>
	<div id="eshop-filter-manufacturers-result" style="display: none;">
		<div class="panel-body">
			<?php
			if (!empty($filterData['manufacturer_ids']))
			{
			    $manufacturerIds = $filterData['manufacturer_ids'];
			    $hasSelected = true;
			}
			else
			{
			    $manufacturerIds = array();
			    $hasSelected = false;
			}
			
			$countManufacturers = count($manufacturers);
			
			if ($productsFilterVisibleItems > 0 && $countManufacturers > $productsFilterVisibleItems)
			{
			    ?>
			    <ul>
    				<?php
    				for ($i = 0; $i < $productsFilterVisibleItems; $i++)
    				{
    				    $manufacturer = $manufacturers[$i];
    					$checked = in_array($manufacturer->id, $manufacturerIds);
    				    ?>
    					<li>
    						<label class="checkbox">
    							<input class="manufacturer" onclick="eshop_ajax_products_filter('manufacturer');" type="checkbox" name="manufacturer_ids[]" value="<?php echo $manufacturer->manufacturer_id; ?>" <?php if ($checked) echo 'checked="checked"'; ?>>
    							<?php echo $manufacturer->manufacturer_name; ?>
    							<span class="badge badge-info">
    								<?php
    									if ($hasSelected && !$checked)
    									{
    										echo '+';
    									}
    
    									echo $manufacturer->number_products;
    								?>
    							</span>
    						</label>
    					</li>
    					<?php
    				}
    				?>
    			</ul>
    			<ul id="more-manufacturers" style="display: none;">
    				<?php
    				for ($i = $productsFilterVisibleItems; $i < $countManufacturers; $i++)
    				{
    				    $manufacturer = $manufacturers[$i];
    					$checked = in_array($manufacturer->id, $manufacturerIds);
    				    ?>
    					<li>
    						<label class="checkbox">
    							<input class="manufacturer" onclick="eshop_ajax_products_filter('manufacturer');" type="checkbox" name="manufacturer_ids[]" value="<?php echo $manufacturer->manufacturer_id; ?>" <?php if ($checked) echo 'checked="checked"'; ?>>
    							<?php echo $manufacturer->manufacturer_name; ?>
    							<span class="badge badge-info">
    								<?php
    									if ($hasSelected && !$checked)
    									{
    										echo '+';
    									}
    
    									echo $manufacturer->number_products;
    								?>
    							</span>
    						</label>
    					</li>
    					<?php
    				}
    				?>
    			</ul>
    			<span id="toggle-manufacturers" class="eshop-toggle-link"><a onclick="toggleItems('manufacturers', 'more');"><?php echo Text::_('ESHOP_SHOW_MORE'); ?></a></span>
			    <?php
			}
			else 
			{
			    ?>
			    <ul>
    				<?php
    				foreach ($manufacturers as $manufacturer)
    				{
    					$checked = in_array($manufacturer->id, $manufacturerIds);
    				?>
    					<li>
    						<label class="checkbox">
    							<input class="manufacturer" onclick="eshop_ajax_products_filter('manufacturer');" type="checkbox" name="manufacturer_ids[]" value="<?php echo $manufacturer->manufacturer_id; ?>" <?php if ($checked) echo 'checked="checked"'; ?>>
    							<?php echo $manufacturer->manufacturer_name; ?>
    							<span class="badge badge-info">
    								<?php
    									if ($hasSelected && !$checked)
    									{
    										echo '+';
    									}
    
    									echo $manufacturer->number_products;
    								?>
    							</span>
    						</label>
    					</li>
    				<?php
    				}
    				?>
    			</ul>
			    <?php
			}
			?>
		</div>
	</div>
<?php
}

foreach ($attributes as $attribute)
{
	if (count($attribute->attributeValues))
	{

		if (!empty($filterData['attribute_' . $attribute->id]))
		{
			$attributeValues = $filterData['attribute_' . $attribute->id];
			$hasSelected = true;
		}
		else
		{
			$attributeValues = array();
			$hasSelected = false;
		}
		?>
		<div id="eshop-filter-attribute-<?php echo $attribute->id; ?>-result" style="display: none;">
			<div class="panel-body">
				<?php
				$countAttributes = count($attribute->attributeValues);
				
				if ($productsFilterVisibleItems > 0 && $countAttributes > $productsFilterVisibleItems)
				{
					?>
					<ul>
						<?php
						for ($i = 0; $i < $productsFilterVisibleItems; $i++)
						{
							$attributeValue = $attribute->attributeValues[$i];
						?>
							<li>
								<label class="checkbox">
									<input class="form-check-input eshop-attributes" type="checkbox" name="attribute_<?php echo $attribute->id;?>[]" onclick="eshop_ajax_products_filter('attribute_<?php echo $attribute->id;?>');" value="<?php echo EshopHelper::escape($attributeValue->value); ?>" <?php if (in_array($attributeValue->value, $attributeValues)) echo 'checked="checked"'; ?> />
									<?php echo $attributeValue->value; ?>
									<span class="badge badge-info">
										<?php
											if ($hasSelected && !$checked)
											{
												echo '+';
											}
											echo $attributeValue->number_products;
										?>
									</span>
								</label>
							</li>
						<?php
						}
						?>
					</ul>
					<ul id="more-attributes-<?php echo $attribute->id;?>" style="display: none;">
						<?php
						for ($i = $productsFilterVisibleItems; $i < $countAttributes; $i++)
						{
							$attributeValue = $attribute->attributeValues[$i];
						?>
							<li>
								<label class="checkbox">
									<input class="form-check-input eshop-attributes" type="checkbox" name="attribute_<?php echo $attribute->id;?>[]" onclick="eshop_ajax_products_filter('attribute_<?php echo $attribute->id;?>');" value="<?php echo EshopHelper::escape($attributeValue->value); ?>" <?php if (in_array($attributeValue->value, $attributeValues)) echo 'checked="checked"'; ?> />
									<?php echo $attributeValue->value; ?>
									<span class="badge badge-info">
										<?php
											if ($hasSelected && !$checked)
											{
												echo '+';
											}
											echo $attributeValue->number_products;
										?>
									</span>
								</label>
							</li>
						<?php
						}
						?>
					</ul>
					<span id="toggle-attributes-<?php echo $attribute->id;?>" class="eshop-toggle-link"><a onclick="toggleItems('attributes-<?php echo $attribute->id;?>', 'more');"><?php echo Text::_('ESHOP_SHOW_MORE'); ?></a></span>
					<?php
				}
				else 
				{
					?>
					<ul>
						<?php
						foreach ($attribute->attributeValues as $attributeValue)
						{
						?>
							<li>
								<label class="checkbox">
									<input class="form-check-input eshop-attributes" type="checkbox" name="attribute_<?php echo $attribute->id;?>[]" onclick="eshop_ajax_products_filter('attribute_<?php echo $attribute->id;?>');" value="<?php echo EshopHelper::escape($attributeValue->value); ?>" <?php if (in_array($attributeValue->value, $attributeValues)) echo 'checked="checked"'; ?> />
									<?php echo $attributeValue->value; ?>
									<span class="badge badge-info">
										<?php
											if ($hasSelected && !$checked)
											{
												echo '+';
											}
											echo $attributeValue->number_products;
										?>
									</span>
								</label>
							</li>
						<?php
						}
						?>
					</ul>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}
}

foreach ($options as $option)
{
	if (!empty($filterData['option_' . $option->id]))
	{
		$optionValues = $filterData['option_' . $option->id];
	}
	else
	{
		$optionValues = array();
	}
	if (count($option->optionValues))
	{
	?>
		<div id="eshop-filter-option-<?php echo $option->id; ?>-result" style="display: none;">
			<div class="panel-body">
				<?php
				$countOptions = count($option->optionValues);

				if ($productsFilterVisibleItems > 0 && $countOptions > $productsFilterVisibleItems)
				{
					?>
					<ul>
						<?php
						for ($i = 0; $i < $productsFilterVisibleItems; $i++)
						{
							$optionValue = $option->optionValues[$i];
							?>
							<li>
								<label class="checkbox">
									<input class="form-check-input eshop-options" type="checkbox" onclick="eshop_ajax_products_filter('option_<?php echo $option->id; ?>')" name="option_<?php echo $option->id; ?>[]" value="<?php echo $optionValue->id; ?>" />
									<?php echo $optionValue->value; ?><span class="badge badge-info"><?php echo $optionValue->number_products;?></span>
								</label>
							</li>
							<?php
						}
						?>
					</ul>
					<ul id="more-options-<?php echo $option->id; ?>" style="display: none;">
						<?php
						for ($i = $productsFilterVisibleItems; $i < $countOptions; $i++)
						{
							$optionValue = $option->optionValues[$i];
							?>
							<li>
								<label class="checkbox">
									<input class="form-check-input eshop-options" type="checkbox" onclick="eshop_ajax_products_filter('option_<?php echo $option->id; ?>')" name="option_<?php echo $option->id; ?>[]" value="<?php echo $optionValue->id; ?>" />
									<?php echo $optionValue->value; ?><span class="badge badge-info"><?php echo $optionValue->number_products;?></span>
								</label>
							</li>
							<?php
						}
						?>
					</ul>
					<span id="toggle-options-<?php echo $option->id; ?>" class="eshop-toggle-link"><a onclick="toggleItems('options-<?php echo $option->id; ?>', 'more');"><?php echo Text::_('ESHOP_SHOW_MORE'); ?></a></span>
					<?php
				}
				else 
				{
					?>
					<ul>
						<?php
						foreach ($option->optionValues as $optionValue)
						{
						?>
							<li>
								<label class="checkbox">
									<input class="form-check-input eshop-options" type="checkbox" onclick="eshop_ajax_products_filter('option_<?php echo $option->id; ?>')" name="option_<?php echo $option->id; ?>[]" value="<?php echo $optionValue->id; ?>" />
									<?php echo $optionValue->value; ?><span class="badge badge-info"><?php echo $optionValue->number_products;?></span>
								</label>
							</li>
						<?php
						}
						?>
					</ul>
					<?php
				}
				?>
			</div>
		</div>
	<?php
	}
}
?>
</div>