<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

if ($params->get('collapse_at_start', '0'))
{
    $collapseClass = 'collapsed';
    $collapseStyle = ' style="display: none;"';
}
else 
{
    $collapseClass = '';
    $collapseStyle = '';
}

$productsFilterVisibleItems = EShopHelper::getConfigValue('products_filter_visible_items', 0);
?>
<form action="<?php echo Route::_('index.php?option=com_eshop&task=search&Itemid='.$itemId); ?>" method="post" name="advancedSearchForm" id="eshop_products_filter_form">
	<div class="eshop_products_filter_<?php echo $params->get( 'classname' ) ?> row-fluid panel-group" id="accordion">
		<div class="eshop-filter panel panel-primary">
			<div class="eshop-filter-reset-all">
				<a class="filter-button-reset" onclick="reset_all();">
					<i class="filter-reset-icon"></i>
					<?php echo Text::_('ESHOP_RESET_ALL'); ?>
				</a>
			</div>
			<?php
			if ($params->get('filter_by_keyword', 1))
			{
				?>
				<div class="eshop-filter-keyword eshop-filter panel panel-primary">
					<a data="#eshop-keyword" data-parent="#accordion" class="<?php echo $collapseClass; ?>">
						<span class="filter-heading">
							<?php echo Text::_('ESHOP_SEARCH')?>
							<i class="filter-head-icon"></i>
						</span>
					</a>
					<div id="eshop-keyword" class="panel-collapse"<?php echo $collapseStyle; ?>>
						<div class="panel-body">
							<div class="input-group">
								<input class="form-control span12 inputbox" type="text" name="keyword" id="keyword" value="<?php echo EShopHelper::escape($input->getString('keyword')); ?>" onchange="eshop_ajax_products_filter();" />
								<span class="input-group-addon"><i class="icon-search"></i></span>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			
			if ($filterByPrice)
			{
				?>
				<div class="eshop-filter-price eshop-filter panel panel-primary">
					<a data="#eshop-price" data-parent="#accordion"  class="<?php echo $collapseClass; ?>">
						<span class="filter-heading">
						<?php echo Text::_('ESHOP_FILTER_PRICE')?>
						<i class="filter-head-icon"></i>
					</a>
					<div id="eshop-price" class="panel-collapse"<?php echo $collapseStyle; ?>>
						<div class="panel-body">
							<?php echo Text::_('ESHOP_FILTER_PRICE_FROM')?>
							<input type="text" class="form-control" value="" id="min_price" name="min_price" class="span5" />
							<?php echo Text::_('ESHOP_FILTER_PRICE_TO')?>
							<input type="text" class="form-control" value="" id="max_price" name="max_price" class="span5" />
							<input type="hidden" value="1" name="filter_by_price" />
							<input type="hidden" value="<?php echo $params->get( 'min_price', 0); ?>" name="min_price_default" />
							<input type="hidden" value="<?php echo $params->get( 'max_price'); ?>" name="max_price_default" />
						</div><br/>
						<div class="wap-nouislider">
							<div id="price-behaviour"></div>
						</div>
						<br />
					</div>
				</div>
			<?php
			}
			?>
			<div id="eshop-filter-categories-container" class="eshop-filter panel panel-primary">
				<a data="#eshop-categories" data-parent="#accordion" class="<?php echo $collapseClass; ?>">
					<span class="filter-heading">
						<?php echo Text::_('ESHOP_FILTER_CATEGORIES'); ?>
						<i class="filter-head-icon"></i>
					</span>
				</a>
				<div id="eshop-categories" class="panel-collapse"<?php echo $collapseStyle; ?>>
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
			</div>
		</div>

		<?php
		if ($params->get('filter_by_manufacturers', 1))
		{
			if (count($manufacturers))
			{
				$display = '';
			}
			else
			{
				$display = ' style="display: none;"';
			}
            ?>
			<div id="eshop-filter-manufacturers-container" class="eshop-filter panel panel-primary"<?php echo $display; ?>>
				<a data="#eshop-manufacturers" data-parent="#accordion" class="<?php echo $collapseClass; ?>">
					<span class="filter-heading">
					<?php echo Text::_('ESHOP_FILTER_MANUFACTURERS'); ?>
					<i class="filter-head-icon"></i>
				</span>
				</a>
				<div id="eshop-manufacturers" class="panel-collapse"<?php echo $collapseStyle; ?>>
					<div class="panel-body">
    					<?php
    					if (!empty($filterData['manufacturer_ids']))
    					{
    					    $manufacturerIds = $filterData['manufacturer_ids'];
    					}
    					else
    					{
    					    $manufacturerIds = array();
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
    					    	    ?>
    								<li>
    									<label class="checkbox">
    										<input class="form-check-input manufacturer" onclick="eshop_ajax_products_filter('manufacturer');" type="checkbox" name="manufacturer_ids[]" value="<?php echo $manufacturer->manufacturer_id; ?>" <?php if (in_array($manufacturer->manufacturer_id, $manufacturerIds)) echo 'checked="checked"'; ?>>
    										<?php echo $manufacturer->manufacturer_name; ?><span class="badge badge-info"><?php echo $manufacturer->number_products;?></span>
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
        						    ?>
    								<li>
    									<label class="checkbox">
    										<input class="form-check-input manufacturer" onclick="eshop_ajax_products_filter('manufacturer');" type="checkbox" name="manufacturer_ids[]" value="<?php echo $manufacturer->manufacturer_id; ?>" <?php if (in_array($manufacturer->manufacturer_id, $manufacturerIds)) echo 'checked="checked"'; ?>>
    										<?php echo $manufacturer->manufacturer_name; ?><span class="badge badge-info"><?php echo $manufacturer->number_products;?></span>
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
    					    	    ?>
    								<li>
    									<label class="checkbox">
    										<input class="form-check-input manufacturer" onclick="eshop_ajax_products_filter('manufacturer');" type="checkbox" name="manufacturer_ids[]" value="<?php echo $manufacturer->manufacturer_id; ?>" <?php if (in_array($manufacturer->manufacturer_id, $manufacturerIds)) echo 'checked="checked"'; ?>>
    										<?php echo $manufacturer->manufacturer_name; ?><span class="badge badge-info"><?php echo $manufacturer->number_products;?></span>
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
			</div>
		<?php
		}

		if ($params->get('filter_by_attributes', 1))
		{
			foreach ($attributes as $attribute)
			{
				if (count($attribute->attributeValues))
				{
					$display = '';
				}
				else
				{
					$display = ' style="display: none;"';
				}

				if (!empty($filterData['attribute_'.$attribute->id]))
				{
					$attributeValues = $filterData['attribute_'.$attribute->id];
				}
				else
				{
					$attributeValues = array();
				}
				?>
				<div id="eshop-filter-attribute-<?php echo $attribute->id; ?>-container" class="eshop-filter panel panel-primary"<?php echo $display; ?>>
					<a data="#eshop-attribute-<?php echo $attribute->id;  ?>" data-parent="#accordion" class="<?php echo $collapseClass; ?>">
						<span class="filter-heading">
						<?php echo $attribute->attribute_name; ?>
						<i class="filter-head-icon"></i>
					</a>
					<div id="eshop-attribute-<?php echo $attribute->id;  ?>" class="panel-collapse"<?php echo $collapseStyle; ?>>
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
    											<?php echo $attributeValue->value; ?><span class="badge badge-info"><?php echo $attributeValue->number_products;?></span>
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
    											<?php echo $attributeValue->value; ?><span class="badge badge-info"><?php echo $attributeValue->number_products;?></span>
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
    											<?php echo $attributeValue->value; ?><span class="badge badge-info"><?php echo $attributeValue->number_products;?></span>
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
				</div>
				<?php
			}
		}

		if ($params->get('filter_by_options', 1))
		{
			foreach ($options as $option)
			{
				if (count($option->optionValues))
				{
					$display = '';
				}
				else
				{
					$display = ' style="display: none;"';
				}
				if (!empty($filterData['option_'.$option->id]))
				{
					$optionValues = $filterData['option_'.$option->id];
				}
				else
				{
					$optionValues = array();
				}
				?>
				<div id="eshop-filter-option-<?php echo $option->id; ?>-container" class="eshop-filter panel panel-primary"<?php echo $display; ?>>
					<a data="#eshop-option-<?php echo $option->id; ?>" data-parent="#accordion" class="<?php echo $collapseClass; ?>">
						<span class="filter-heading">
						<?php echo $option->option_name; ?>
						<i class="filter-head-icon"></i>
					</a>
					<div id="eshop-option-<?php echo $option->id; ?>" class="panel-collapse"<?php echo $collapseStyle; ?>>
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
				</div>
				<?php
			}
		}

		if ($filterByWeight)
		{
		?>
			<div class="eshop-filter-weight eshop-filter panel panel-primary">
				<a data="#eshop-weight" data-parent="#accordion" class="<?php echo $collapseClass; ?>">
					<span class="filter-heading">
					<?php echo Text::_('ESHOP_FILTER_WEIGHT')?>
					<i class="filter-head-icon"></i>
				</a>
				<div id="eshop-weight" class="panel-collapse"<?php echo $collapseStyle; ?>>
					<div class="panel-body">
						<?php echo Text::_('ESHOP_FILTER_WEIGHT_FROM')?>
						<input type="text" class="form-control" value="" id="min_weight" name="min_weight" class="span5" onchange="eshop_ajax_products_filter('weight');" />
						<?php echo Text::_('ESHOP_FILTER_WEIGHT_TO')?>
						<input type="text" class="form-control" value="" id="max_weight" name="max_weight" class="span5" onchange="eshop_ajax_products_filter('weight');" />
						<input type="hidden" value="1" name="filter_by_weight" />
						<input type="hidden" value="<?php echo $params->get( 'min_weight', 0); ?>" name="min_weight_default" />
						<input type="hidden" value="<?php echo $params->get( 'max_weight'); ?>" name="max_weight_default" />
						<input type="hidden" value="<?php echo $params->get('same_weight_unit', 1); ?>" name="same_weight_unit" />
					</div><br/>
					<div class="wap-nouislider">
						<div id="weight-behaviour"></div>
					</div>
					<br />
				</div>
			</div>
		<?php
		}

		if ($filterByLength)
		{
		?>
			<div class="eshop-filter-length eshop-filter panel panel-primary">
				<a data="#eshop-length" data-parent="#accordion"  class="<?php echo $collapseClass; ?>">
					<span class="filter-heading">
					<?php echo Text::_('ESHOP_FILTER_LENGTH')?>
					<i class="filter-head-icon"></i>
				</a>
				<div id="eshop-length" class="panel-collapse"<?php echo $collapseStyle; ?>>
					<div class="panel-body">
						<?php echo Text::_('ESHOP_FILTER_LENGTH_FROM')?>
						<input type="text" class="form-control" value="" id="min_length" name="min_length" class="span5" onchange="eshop_ajax_products_filter('length');" />
						<?php echo Text::_('ESHOP_FILTER_LENGTH_TO')?>
						<input type="text" class="form-control" value="" id="max_length" name="max_length" class="span5" onchange="eshop_ajax_products_filter('length');" />
						<input type="hidden" value="<?php echo $params->get( 'min_length', 0); ?>" name="min_length_default" />
						<input type="hidden" value="<?php echo $params->get( 'max_length'); ?>" name="max_length_default" />
						<input type="hidden" value="1" name="filter_by_length" />
					</div><br/>
					<div class="wap-nouislider">
						<div id="length-behaviour"></div>
					</div>
					<br />
				</div>
			</div>
		<?php
		}

		if ($filterByWidth)
		{
		?>
			<div class="eshop-filter-width eshop-filter panel panel-primary">
				<a data="#eshop-width" data-parent="#accordion"  class="<?php echo $collapseClass; ?>">
					<span class="filter-heading">
					<?php echo Text::_('ESHOP_FILTER_WIDTH')?>
					<i class="filter-head-icon"></i>
				</a>
				<div id="eshop-width" class="panel-collapse"<?php echo $collapseStyle; ?>>
					<div class="panel-body">
						<?php echo Text::_('ESHOP_FILTER_WIDTH_FROM')?>
						<input type="text" class="form-control" value="" id="min_width" name="min_width" class="span5" onchange="eshop_ajax_products_filter('width');" />
						<?php echo Text::_('ESHOP_FILTER_WIDTH_TO')?>
						<input type="text" class="form-control" value="" id="max_width" name="max_width" class="span5" onchange="eshop_ajax_products_filter('width');" />
						<input type="hidden" value="<?php echo $params->get( 'min_width', 0); ?>" name="min_width_default" />
						<input type="hidden" value="<?php echo $params->get( 'max_width'); ?>" name="max_width_default" />
						<input type="hidden" value="1" name="filter_by_width" />
					</div><br/>
					<div class="wap-nouislider">
						<div id="width-behaviour"></div>
					</div>
					<br />
				</div>
			</div>
		<?php
		}

		if ($filterByHeight)
		{
		?>
			<div class="eshop-filter-height eshop-filter panel panel-primary">
				<a data="#eshop-height" data-parent="#accordion"  class="<?php echo $collapseClass; ?>">
					<span class="filter-heading">
					<?php echo Text::_('ESHOP_FILTER_HEIGHT')?>
					<i class="filter-head-icon"></i>
				</a>
				<div id="eshop-height" class="panel-collapse"<?php echo $collapseStyle; ?>>
					<div class="panel-body">
						<?php echo Text::_('ESHOP_FILTER_HEIGHT_FROM')?>
						<input type="text" class="form-control" value="" id="min_height" name="min_height" class="span5" onchange="eshop_ajax_products_filter('height');" />
						<?php echo Text::_('ESHOP_FILTER_HEIGHT_TO')?>
						<input type="text" class="form-control" value="" id="max_height" name="max_height" class="span5" onchange="eshop_ajax_products_filter('height');" />
						<input type="hidden" value="<?php echo $params->get( 'min_height', 0); ?>" name="min_height_default" />
						<input type="hidden" value="<?php echo $params->get( 'max_height'); ?>" name="max_height_default" />
						<input type="hidden" value="1" name="filter_by_height" />
					</div><br/>
					<div class="wap-nouislider">
						<div id="height-behaviour"></div>
					</div>
					<br />
				</div>
			</div>
		<?php
		}

		if ($params->get('filter_by_stock', 1))
		{
		?>
			<div class="eshop-filter-stock eshop-filter panel panel-primary">
				<a data="#eshop-stock" data-parent="#accordion" class="<?php echo $collapseClass; ?>">
					<span class="filter-heading">
					<?php echo Text::_('ESHOP_FILTER_STOCK')?>
					<i class="filter-head-icon"></i>
				</a>
				<div id="eshop-stock" class="panel-collapse"<?php echo $collapseStyle; ?>>
					<div class="panel-body">
						<select name="product_in_stock" id="product_in_stock" class="inputbox" style="width: 180px;" onchange="eshop_ajax_products_filter();">
							<option value="0" <?php if ($input->getInt('product_in_stock', 0, 'int') == '0') echo 'selected = "selected"'; ?>><?php echo Text::_('ESHOP_BOTH'); ?></option>
							<option value="1" <?php if ($input->getInt('product_in_stock', 0, 'int') == '1') echo 'selected = "selected"'; ?>><?php echo Text::_('ESHOP_IN_STOCK'); ?></option>
							<option value="-1" <?php if ($input->getInt('product_in_stock', 0, 'int') == '-1') echo 'selected = "selected"'; ?>><?php echo Text::_('ESHOP_OUT_OF_STOCK'); ?></option>
						</select>
					</div>
				</div>
			</div>
		<?php
		}
		?>
		<div class="eshop-filter-hidden">
			<?php
			if ($filterByLength || $filterByWidth || $filterByHeight)
			{
			?>
				<input type="hidden" value="<?php echo $params->get('same_length_unit', 1); ?>" name="same_length_unit" />
			<?php
			}
			?>
	        <input type="hidden" name="change_filter" id="change_filter_id" value="" />
			<input type="hidden" name="category_id" id="filter_category_id" value="<?php echo empty($filterData['category_id']) ? 0 : $filterData['category_id']; ?>" />
		</div>
	</div>
</form>
<script type="text/javascript">
	Eshop.jQuery(function($){
		toggleItems = function(itemType, toggleType)
		{
			if (toggleType == 'more')
			{
				$('#more-' + itemType).css('display', '');
    			$('#toggle-' + itemType).html('<a onclick="toggleItems(\'' + itemType + '\', \'less\');"><?php echo Text::_('ESHOP_SHOW_LESS'); ?></a>');
			}
			else
			{
				$('#more-' + itemType).css('display', 'none');
				$('#toggle-' + itemType).html('<a onclick="toggleItems(\'' + itemType + '\', \'more\');"><?php echo Text::_('ESHOP_SHOW_MORE'); ?></a>');
			}
		}
		
		$(document).ready(function(){
			$('.eshop-filter a').click(function(){
				$(this).toggleClass('collapsed');
				var id = $(this).attr('data');
				$(id).slideToggle('slow');
			})
		})
		eshop_filter_by_category = function(categoryId)
		{
			$('#filter_category_id').val(categoryId);
			eshop_ajax_products_filter('category');
		};
		reset_all = function()
		{
			<?php
			if ($filterByPrice)
			{
				?>
				$("#price-behaviour").val([<?php echo $params->get( 'min_price', 0); ?>, <?php echo $params->get( 'max_price'); ?>]);
				$('input[name^=min_price]').val('<?php echo $symbol . $params->get( 'min_price', 0); ?>');
				$('input[name^=max_price]').val('<?php echo $symbol . $params->get( 'max_price'); ?>');
				<?php
			}
			if ($filterByWeight)
			{
				?>
				$("#weight-behaviour").val([<?php echo $params->get( 'min_weight', 0); ?>, <?php echo $params->get( 'max_weight'); ?>]);
				$('input[name^=min_weight]').val('<?php echo $params->get( 'min_weight', 0) . $weightUnit; ?>');
				$('input[name^=max_weight]').val('<?php echo $params->get( 'max_weight') . $weightUnit; ?>');
				<?php
			}
			if ($filterByLength)
			{
				?>
				$("#length-behaviour").val([<?php echo $params->get( 'min_length', 0); ?>, <?php echo $params->get( 'max_length'); ?>]);
				$('input[name^=min_length]').val('<?php echo $params->get( 'min_length', 0) . $lengthUnit; ?>');
				$('input[name^=max_length]').val('<?php echo $params->get( 'max_length') . $lengthUnit; ?>');
				<?php
			}
			if ($filterByWidth)
			{
				?>
				$("#width-behaviour").val([<?php echo $params->get( 'min_width', 0); ?>, <?php echo $params->get( 'max_width'); ?>]);
				$('input[name^=min_width]').val('<?php echo $params->get( 'min_width', 0) . $lengthUnit; ?>');
				$('input[name^=max_width]').val('<?php echo $params->get( 'max_width') . $lengthUnit; ?>');
				<?php
			}
			if ($filterByWeight)
			{
				?>
				$("#height-behaviour").val([<?php echo $params->get( 'min_height', 0); ?>, <?php echo $params->get( 'max_height'); ?>]);
				$('input[name^=min_height]').val('<?php echo $params->get( 'min_height', 0) . $lengthUnit; ?>');
				$('input[name^=max_height]').val('<?php echo $params->get( 'max_height') . $lengthUnit; ?>');
				<?php
			}
			if ($params->get('filter_by_stock',1))
			{
				?>
				$('#product_in_stock').val('0');
				<?php
			}
			if ($params->get('filter_by_manufacturers', 1))
			{
				?>
				$('input[name^=manufacturer_ids]').prop("checked", false);			
				<?php
			}
			if ($params->get('filter_by_attributes', 1))
			{
				foreach ($attributes as $attribute)
				{
					?>
					$('input[name^=attribute_<?php echo $attribute->id; ?>]').prop("checked", false);		
					<?php
				}
			}
			if ($params->get('filter_by_options', 1))
			{
				foreach ($options as $option)
				{
					?>
					$('input[name^=option_<?php echo $option->id; ?>]').prop("checked", false);	
					<?php
				}
			}
			if ($params->get('filter_by_keyword', 1))
			{
				?>
				$('input[name^=keyword]').val('');
				<?php
			}
			?>
			eshop_filter_by_category(0);
		}
		//Ajax request
		eshop_ajax_products_filter = function(changeFilter)
		{
			if (changeFilter === undefined || typeof changeFilter == 'object') {
				changeFilter = '';
			}

			$('#change_filter_id').val(changeFilter);

			$("body").append('<img id="eshop-loadding" src="<?php echo Uri::root().'modules/mod_eshop_products_filter/assets/images/loading.gif'?>" />');
			jQuery('.alert-success').remove();
			$('#eshop-loadding').css({"position":"fixed","left":$( window ).width()/2-240+"px","top":$( window ).height()/2-160+"px","z-index":999});
			$("body").css({"opacity": "0.4"});
			$.ajax({
				type: "POST",
				url:  "<?php echo Uri::root(); ?>index.php?option=com_eshop&view=filter&Itemid=<?php echo $itemId; ?>&format=raw&<?php echo EShopHelper::getAttachedLangLink(); ?>&change_filter=" + changeFilter,
				data: $('#eshop_products_filter_form input[type=\'text\'], #eshop_products_filter_form input[type=\'checkbox\']:checked, #eshop_products_filter_form select, #eshop_products_filter_form input[type=\'hidden\']'),
				cache: false,
				success: function(html){
					$('#eshop-main-container').html(html);
					$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
					$('#change_filter_id').val('');
					if ($('#eshop-filter-categories-result').length)
					{
						$('#eshop-filter-categories-container').show();
						$('#eshop-categories').html($('#eshop-filter-categories-result').html());
						$('#eshop-filter-categories-result').html('');
					}
					else
					{
						$('#eshop-filter-categories-container').hide();
					}
					<?php
					if ($params->get('filter_by_manufacturers', 1))
					{
					?>
						if ($('#eshop-filter-manufacturers-result').length)
						{
							$('#eshop-filter-manufacturers-container').show();
							$('#eshop-manufacturers').html($('#eshop-filter-manufacturers-result').html());
							$('#eshop-filter-manufacturers-result').html('');
						}
						else
						{
							$('#eshop-filter-manufacturers-container').hide();
						}
					<?php
					}

					if ($params->get('filter_by_attributes', 1))
					{
						foreach ($attributes as $attribute)
						{
							?>
							if ($('#eshop-filter-attribute-<?php echo $attribute->id; ?>-result').length)
							{
								$('#eshop-filter-attribute-<?php echo $attribute->id; ?>-container').show();
								$('#eshop-attribute-<?php echo $attribute->id; ?>').html($('#eshop-filter-attribute-<?php echo $attribute->id; ?>-result').html());
								$('#eshop-filter-attribute-<?php echo $attribute->id; ?>-result').html('');
							}
							else
							{
								$('#eshop-filter-attribute-<?php echo $attribute->id; ?>-container').hide();
							}
							<?php
						}
					}
					
					if ($params->get('filter_by_options', 1))
					{
						foreach ($options as $option)
						{
							?>
							if ($('#eshop-filter-option-<?php echo $option->id; ?>-result').length)
							{
								$('#eshop-filter-option-<?php echo $option->id; ?>-container').show();
								$('#eshop-option-<?php echo $option->id;  ?>').html($('#eshop-filter-option-<?php echo $option->id; ?>-result').html());
								$('#eshop-filter-option-<?php echo $option->id; ?>-result').html('');
							}
							else
							{
								$('#eshop-filter-option-<?php echo $option->id; ?>-container').hide();
							}
						<?php
						}
					}
					?>
					$("body").css({"opacity": "1"});
					$('#eshop-loadding').remove();
				}
			});
		}
		<?php
		
		if ($filterByPrice)
		{
			?>
			$("#price-behaviour").noUiSlider({
				start: [ <?php echo $input->get('min_price', 0, 'float') ?: $params->get( 'min_price', 0); ?>, <?php echo $input->get('max_price', 0, 'float') ?: $params->get('max_price'); ?> ],
				range: {
					'min': <?php echo $params->get( 'min_price', 0); ?>,
					'max': <?php echo $params->get( 'max_price'); ?>
				},
				connect: true,
				serialization: {
					lower: [
						$.Link({
							target: $("#min_price"),
							format: {
								prefix: '<?php echo $symbol; ?>',
								decimals: 0,
							}
						})
					],
					upper: [
						$.Link({
							target: $("#max_price"),
							format: {
								prefix: '<?php echo $symbol; ?>',
								decimals: 0,
							}
						})
					],
				}
			}).on('change', eshop_ajax_products_filter);
			<?php
		}
		if ($filterByWeight)
		{
			?>
			$("#weight-behaviour").noUiSlider({
				start: [ <?php echo $input->get('min_weight', 0, 'float') ?: $params->get( 'min_weight', 0); ?>, <?php echo $input->get('max_weight', 0, 'float') ?: $params->get( 'max_weight'); ?> ],
				range: {
					'min': <?php echo $params->get( 'min_weight', 0); ?>,
					'max': <?php echo $params->get( 'max_weight'); ?>
				},
				connect: true,
				serialization: {
					lower: [
						$.Link({
							target: $("#min_weight"),
							format: {
								postfix: '<?php echo $weightUnit; ?>',
								decimals: 0,
							}
						})
					],
					upper: [
						$.Link({
							target: $("#max_weight"),
							format: {
								postfix: '<?php echo $weightUnit; ?>',
								decimals: 0,
							}
						})
					],
				}
			}).on('change', eshop_ajax_products_filter);
			<?php
		}
		if ($filterByLength)
		{
			?>
			$("#length-behaviour").noUiSlider({
				start: [ <?php echo $input->get('min_length', 0, 'float') ?: $params->get( 'min_length', 0); ?>, <?php echo $input->get('max_length', 0, 'float') ?: $params->get( 'max_length'); ?> ],
				range: {
					'min': <?php echo $params->get( 'min_length', 0); ?>,
					'max': <?php echo $params->get( 'max_length'); ?>
				},
				connect: true,
				serialization: {
					lower: [
						$.Link({
							target: $("#min_length"),
							format: {
								postfix: '<?php echo $lengthUnit; ?>',
								decimals: 0,
							}
						})
					],
					upper: [
						$.Link({
							target: $("#max_length"),
							format: {
								postfix: '<?php echo $lengthUnit; ?>',
								decimals: 0,
							}
						})
					],
				}
			}).on('change', eshop_ajax_products_filter);
			<?php
		}
		if ($filterByWidth)
		{
			?>
			$("#width-behaviour").noUiSlider({
				start: [ <?php echo $input->get('min_width', 0, 'float') ?: $params->get( 'min_width', 0); ?>, <?php echo $input->get('max_width', 0, 'float') ?: $params->get( 'max_width'); ?> ],
				range: {
					'min': <?php echo $params->get( 'min_width', 0); ?>,
					'max': <?php echo $params->get( 'max_width'); ?>
				},
				connect: true,
				serialization: {
					lower: [
						$.Link({
							target: $("#min_width"),
							format: {
								postfix: '<?php echo $lengthUnit; ?>',
								decimals: 0,
							}
						})
					],
					upper: [
						$.Link({
							target: $("#max_width"),
							format: {
								postfix: '<?php echo $lengthUnit; ?>',
								decimals: 0,
							}
						})
					],
				}
			}).on('change', eshop_ajax_products_filter);
			<?php
		}
		if ($filterByHeight)
		{
			?>
			$("#height-behaviour").noUiSlider({
				start: [ <?php echo $input->get('min_height', 0, 'float') ?: $params->get( 'min_height', 0); ?>, <?php echo $input->get('max_height', 0, 'float') ?: $params->get( 'max_height'); ?> ],
				range: {
					'min': <?php echo $params->get( 'min_height', 0); ?>,
					'max': <?php echo $params->get( 'max_height'); ?>
				},
				connect: true,
				serialization: {
					lower: [
						$.Link({
							target: $("#min_height"),
							format: {
								postfix: '<?php echo $lengthUnit; ?>',
								decimals: 0,
							}
						})
					],
					upper: [
						$.Link({
							target: $("#max_height"),
							format: {
								postfix: '<?php echo $lengthUnit; ?>',
								decimals: 0,
							}
						})
					],
				}
			}).on('change', eshop_ajax_products_filter);
			<?php
		}
		?>
	})
</script>