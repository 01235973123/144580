<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
?>
<form action="<?php echo Route::_('index.php?option=com_eshop&task=search&Itemid='.$itemId); ?>" method="post" name="advancedSearchForm" id="advancedSearchForm">
	<div class="eshop_advanced_search<?php echo $params->get( 'classname' ) ?> row-fluid panel-group" id="accordion">
		<?php
		if ($params->get('show_price',1))
		{
			?>
			<div class="eshop-filter panel panel-primary">
				<a data-toggle="collapse" data-parent="#accordion" href="#eshop-price" class="collapsed">
					<strong><?php echo Text::_('ESHOP_FILTER_PRICE')?></strong><br />
				</a>
				<div id="eshop-price" class="panel-collapse in collapse">
					<div class="panel-body">
						<strong><?php echo Text::_('ESHOP_FROM')?></strong>
						<input type="text" value="" id="min_price" name="min_price" class="span4" />
						<strong><?php echo Text::_('ESHOP_TO')?></strong>
						<input type="text" value="" id="max_price" name="max_price" class="span4" />
					</div><br/>
					<div class="wap-nouislider">
						<div id="price-behaviour"></div>
					</div>
					<br />
				</div>
			</div>
			<?php
		}
		if ($params->get('show_weight',1))
		{
			?>
			<div class="eshop-filter panel panel-primary">
				<a data-toggle="collapse" data-parent="#accordion" href="#eshop-weight" class="collapsed">
					<strong><?php echo Text::_('ESHOP_FILTER_WEIGHT')?></strong><br />
				</a>
				<div id="eshop-weight" class="panel-collapse in collapse">
					<div class="panel-body">
						<strong><?php echo Text::_('ESHOP_FROM')?></strong>
						<input type="text" value="" id="min_weight" name="min_weight" class="span4" />
						<strong><?php echo Text::_('ESHOP_TO')?></strong>
						<input type="text" value="" id="max_weight" name="max_weight" class="span4" />
						<input type="hidden" value="<?php echo $params->get('same_weight_unit', 1); ?>" name="same_weight_unit" />
					</div>
					<div class="wap-nouislider">
						<div id="weight-behaviour"></div>
					</div>
					<br />
				</div>
			</div>
			<?php
		}
		if ($params->get('show_length',1))
		{
			?>
			<div class="eshop-filter panel panel-primary">
				<a data-toggle="collapse" data-parent="#accordion" href="#eshop-length" class="collapsed">
					<strong><?php echo Text::_('ESHOP_FILTER_LENGTH')?></strong><br />
				</a>
				<div id="eshop-length" class="panel-collapse in collapse">
					<div class="panel-body">
						<strong><?php echo Text::_('ESHOP_FROM')?></strong>
						<input type="text" value="" id="min_length" name="min_length" class="span4" />
						<strong><?php echo Text::_('ESHOP_TO')?></strong>
						<input type="text" value="" id="max_length" name="max_length" class="span4" />
					</div>
					<div class="wap-nouislider">
						<div id="length-behaviour"></div>
					</div>
					<br />
				</div>
			</div>
			<?php
		}
		if ($params->get('show_width',1))
		{
			?>
			<div class="eshop-filter panel panel-primary">
				<a data-toggle="collapse" data-parent="#accordion" href="#eshop-width" class="collapsed">
					<strong><?php echo Text::_('ESHOP_FILTER_WIDTH')?></strong><br />
				</a>
				<div id="eshop-width" class="panel-collapse in collapse">
					<div class="panel-body">
						<strong><?php echo Text::_('ESHOP_FROM')?></strong>
						<input type="text" value="" id="min_width" name="min_width" class="span4" />
						<strong><?php echo Text::_('ESHOP_TO')?></strong>
						<input type="text" value="" id="max_width" name="max_width" class="span4" />
					</div>
					<div class="wap-nouislider">
						<div id="width-behaviour"></div>
					</div>
					<br />
				</div>
			</div>
			<?php
		}
		if ($params->get('show_height',1))
		{
			?>
			<div class="eshop-filter panel panel-primary">
				<a data-toggle="collapse" data-parent="#accordion" href="#eshop-height" class="collapsed">
					<strong><?php echo Text::_('ESHOP_FILTER_HEIGHT')?></strong><br />
				</a>
				<div id="eshop-height" class="panel-collapse in collapse">
					<div class="panel-body">
						<strong><?php echo Text::_('ESHOP_FROM')?></strong>
						<input type="text" value="" id="min_height" name="min_height" class="span4" />
						<strong><?php echo Text::_('ESHOP_TO')?></strong>
						<input type="text" value="" id="max_height" name="max_height" class="span4" />
					</div>
					<div class="wap-nouislider">
						<div id="height-behaviour"></div>
					</div>
					<br />
				</div>
			</div>
			<?php
		}
		if ($params->get('show_stock',1))
		{
			?>
			<div class="eshop-filter panel panel-primary">
				<a data-toggle="collapse" data-parent="#accordion" href="#eshop-stock" class="collapsed">
					<strong><?php echo Text::_('ESHOP_FILTER_STOCK')?></strong><br />
				</a>
				<div id="eshop-stock" class="panel-collapse in collapse">
					<div class="panel-body">
						<select name="product_in_stock" id="product_in_stock" class="inputbox" style="width: 180px;">
							<option value="0" <?php if ($productInStock == '0') echo 'selected = "selected"'; ?>><?php echo Text::_('ESHOP_BOTH'); ?></option>
							<option value="1" <?php if ($productInStock == '1') echo 'selected = "selected"'; ?>><?php echo Text::_('ESHOP_IN_STOCK'); ?></option>
							<option value="-1" <?php if ($productInStock == '-1') echo 'selected = "selected"'; ?>><?php echo Text::_('ESHOP_OUT_OF_STOCK'); ?></option>
						</select>
					</div>
				</div>
			</div>
			<?php
		}
		if ($params->get('show_categories', 1) && count($categories))
		{
			?>
			<div class="eshop-filter panel panel-primary">
				<a data-toggle="collapse" data-parent="#accordion" href="#eshop-categories" class="collapsed">
					<strong><?php echo Text::_('ESHOP_FILTER_CATEGORIES'); ?></strong><br />
				</a>
				<div id="eshop-categories" class="panel-collapse in collapse">
					<div class="panel-body">
						<ul>
						<?php
						for ($i = 0; $n = count($categories), $i < $n; $i++)
						{
							?>
							<li>
								<label class="checkbox" for="category_ids_<?php echo $categories[$i]->id; ?>">
									<input class="category_ids" type="checkbox" name="category_ids[]" id="category_ids_<?php echo $categories[$i]->id; ?>" value="<?php echo $categories[$i]->id; ?>" <?php if (in_array($categories[$i]->id, $categoryIds)) echo 'checked="checked"'; ?>>
									<?php echo $categories[$i]->treeElement; ?>
								</label>
							</li>	
							<?php
						}
						?>
						</ul>
					</div>
				</div>
			</div>
			<?php
		}
		if ($params->get('show_manufacturers', 1) && count($manufacturers))
		{
			?>
			<div class="eshop-filter panel panel-primary">
				<a data-toggle="collapse" data-parent="#accordion" href="#eshop-manufacturers" class="collapsed">
					<strong><?php echo Text::_('ESHOP_FILTER_MANUFACTURERS'); ?></strong><br />
				</a>
				<div id="eshop-manufacturers" class="panel-collapse in collapse">
					<div class="panel-body">
						<ul>
							<?php
							foreach ($manufacturers as $manufacturer)
							{
								?>
								<li>
									<label class="checkbox" for="manufacturer_ids_<?php echo $manufacturer->id; ?>">
										<input class="manufacturer" type="checkbox" name="manufacturer_ids[]" id="manufacturer_ids_<?php echo $manufacturer->id; ?>" value="<?php echo $manufacturer->manufacturer_id; ?>" <?php if (in_array($manufacturer->manufacturer_id, $manufacturerIds)) echo 'checked="checked"'; ?>>
										<?php echo $manufacturer->manufacturer_name; ?>
									</label>
								</li>	
								<?php
							}
							?>
						</ul>
					</div>
				</div>
			</div>
			<?php
		}
		if ($params->get('show_attributes', 1) && count($attributeGroups))
		{
			?>
			<div class="eshop-filter panel panel-primary">
				<a data-toggle="collapse" data-parent="#accordion" href="#eshop-attributes" class="collapsed">
					<strong><?php echo Text::_('ESHOP_FILTER_ATTRIBUTES'); ?></strong><br />
				</a>
				<div id="eshop-attributes" class="panel-collapse in collapse">
					<div class="panel-body">
						<ul>
							<?php
							foreach ($attributeGroups as $attributeGroup)
							{
								if (count($attributeGroup->attribute))
								{
									?>
									<li>
										<strong>
											<?php echo $attributeGroup->attributegroup_name; ?>
										</strong>
										<ul>
											<?php
											foreach ($attributeGroup->attribute as $attribute)
											{
												?>
												<li>
													<label class="checkbox" for="attribute_ids_<?php echo $attribute->id; ?>">
														<input class="eshop-attributes" type="checkbox" name="attribute_ids[]" id="attribute_ids_<?php echo $attribute->id; ?>" value="<?php echo $attribute->id; ?>" <?php if (in_array($attribute->id, $attributeIds)) echo 'checked="checked"'; ?>>
														<?php echo $attribute->attribute_name; ?>
													</label>
												</li>	
												<?php
											}
											?>
										</ul>
									</li>									
									<?php
								}
							}
							?>
						</ul>	
					</div>
				</div>
			</div>
			<?php
		}
		if ($params->get('show_options', 1) && count($options))
		{
			?>
			<div class="eshop-filter panel panel-primary">
				<a data-toggle="collapse" data-parent="#accordion" href="#eshop-options" class="collapsed">
					<strong><?php echo Text::_('ESHOP_FILTER_OPTIONS'); ?></strong><br />
				</a>
				<div id="eshop-options" class="panel-collapse in collapse">
					<div class="panel-body">
						<?php
						foreach ($options as $option)
						{
							if (count($option->optionValues))
							{
								?>
								<ul>
									<li>
										<strong>
											<?php echo $option->option_name; ?>
										</strong>
										<ul>
											<?php
											foreach ($option->optionValues as $optionValue)
											{
												?>
												<li>
													<label class="checkbox" for="optionvalue_ids_<?php echo $optionValue->id; ?>">
														<input class="eshop-options" type="checkbox" name="optionvalue_ids[]" id="optionvalue_ids_<?php echo $optionValue->id; ?>" value="<?php echo $optionValue->id; ?>" <?php if (in_array($optionValue->id, $optionValueIds)) echo 'checked="checked"'; ?>>
														<?php echo $optionValue->value; ?>
													</label>
												</li>
												<?php
											}
											?>
										</ul>	
									</li>
								</ul>	
								<?php
							}
						}
						?>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<div class="eshop-filter">
			<div class="input-prepend">
				<label for="keyword"><?php echo Text::_('ESHOP_FILTER_BY_KEYWORD'); ?>:</label>
				<input class="span12 inputbox product_advancedsearch" type="text" name="keyword" id="keyword" value="<?php echo $keyword; ?>">
			</div>
		</div>
		<div class="eshop-filter">
	        <div class="input-prepend">
				<button class="btn btn-primary" name="Submit" tabindex="0" type="submit">
					<em class="icon-search"></em>
					<?php echo Text::_('ESHOP_SEARCH')?>
				</button>
				<button class="btn btn-primary eshop-reset" name="Submit" tabindex="0" type="button">
					<em class="icon-refresh"></em>
					<?php echo Text::_('ESHOP_RESET_ALL')?>
				</button>
				<?php
				if ($params->get('show_length',1) || $params->get('show_width',1) || $params->get('show_height',1))
				{
					?>
					<input type="hidden" value="<?php echo $params->get('same_length_unit', 1); ?>" name="same_length_unit" />
					<?php
				}
				?>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
	Eshop.jQuery(function($){
		//reset button
		$('.eshop-reset').click(function(){
			<?php
			if ($params->get('show_price',1))
			{
				?>
				$("#price-behaviour").val([<?php echo $params->get( 'min_price', 0); ?>, <?php echo $params->get( 'max_price', 1000); ?>]);
				$('input[name^=min_price]').val('<?php echo $symbol . $params->get( 'min_price', 0); ?>');
				$('input[name^=max_price]').val('<?php echo $symbol . $params->get( 'max_price', 1000); ?>');
				<?php
			}
			if ($params->get('show_weight',1))
			{
				?>
				$("#weight-behaviour").val([<?php echo $params->get( 'min_weight', 0); ?>, <?php echo $params->get( 'max_weight', 100); ?>]);
				$('input[name^=min_weight]').val('<?php echo $params->get( 'min_weight', 100) . $weightUnit; ?>');
				$('input[name^=max_weight]').val('<?php echo $params->get( 'max_weight', 100) . $weightUnit; ?>');
				<?php
			}
			if ($params->get('show_length',1))
			{
				?>
				$("#length-behaviour").val([<?php echo $params->get( 'min_length', 0); ?>, <?php echo $params->get( 'max_length', 100); ?>]);
				$('input[name^=min_length]').val('<?php echo $params->get( 'min_length', 100) . $lengthUnit; ?>');
				$('input[name^=max_length]').val('<?php echo $params->get( 'max_length', 100) . $lengthUnit; ?>');
				<?php
			}
			if ($params->get('show_width',1))
			{
				?>
				$("#width-behaviour").val([<?php echo $params->get( 'min_width', 0); ?>, <?php echo $params->get( 'max_width', 100); ?>]);
				$('input[name^=min_width]').val('<?php echo $params->get( 'min_width', 100) . $lengthUnit; ?>');
				$('input[name^=max_width]').val('<?php echo $params->get( 'max_width', 100) . $lengthUnit; ?>');
				<?php
			}
			if ($params->get('show_height',1))
			{
				?>
				$("#height-behaviour").val([<?php echo $params->get( 'min_height', 0); ?>, <?php echo $params->get( 'max_height', 100); ?>]);
				$('input[name^=min_height]').val('<?php echo $params->get( 'min_height', 100) . $lengthUnit; ?>');
				$('input[name^=max_height]').val('<?php echo $params->get( 'max_height', 100) . $lengthUnit; ?>');
				<?php
			}
			if ($params->get('show_stock',1))
			{
				?>
				$('#product_in_stock').val('2');
				<?php
			}
			if ($params->get('show_categories', 1) && count($categories))
			{
				?>
				$('input[name^=category_ids]').prop("checked", false);
				<?php
			}
			if ($params->get('show_manufacturers', 1) && count($manufacturers))
			{
				?>
				$('input[name^=manufacturer_ids]').prop("checked", false);			
				<?php
			}
			if ($params->get('show_attributes', 1) && count($attributeGroups))
			{
				?>
				$('input[name^=attribute_ids]').prop("checked", false);		
				<?php
			}
			if ($params->get('show_options', 1) && count($options))
			{
				?>
				$('input[name^=optionvalue_ids]').prop("checked", false);	
				<?php
			}
			?>
			$('input[name^=keyword]').val('');
		})
		<?php
		if ($params->get('show_price',1))
		{
			?>
			$("#price-behaviour").noUiSlider({
				start: [ <?php echo $minPrice ?: $params->get( 'min_price', 0); ?>, <?php echo $maxPrice ?: $params->get( 'max_price', 1000); ?> ],
				range: {
					'min': <?php echo $params->get( 'min_price', 0); ?>,
					'max': <?php echo $params->get( 'max_price', 1000); ?>
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
							target: function( value, handleElement, slider ){
								$("#max_price").val( value );
							}
						}),
					],
					format: {
						prefix: '<?php echo $symbol; ?>',
						decimals: 0,
					}
				}
			});
			<?php
		}
		if ($params->get('show_weight',1))
		{
			?>
			$("#weight-behaviour").noUiSlider({
				start: [ <?php echo $minWeight ?: $params->get( 'min_weight', 0); ?>, <?php echo $maxWeight ?: $params->get( 'max_weight', 100); ?> ],
				range: {
					'min': <?php echo $params->get( 'min_weight', 0); ?>,
					'max': <?php echo $params->get( 'max_weight', 100); ?>
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
							target: function( value, handleElement, slider ){
								$("#max_weight").val( value );
							}
						}),
					],
					format: {
						postfix: '<?php echo $weightUnit; ?>',
						decimals: 0,
					}
				}
			});
			<?php
		}
		if ($params->get('show_length',1))
		{
			?>
			$("#length-behaviour").noUiSlider({
				start: [ <?php echo $minLength ?: $params->get( 'min_length', 0); ?>, <?php echo $maxLength ?: $params->get( 'max_length', 100); ?> ],
				range: {
					'min': <?php echo $params->get( 'min_length', 0); ?>,
					'max': <?php echo $params->get( 'max_length', 100); ?>
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
							target: function( value, handleElement, slider ){
								$("#max_length").val( value );
							}
						}),
					],
					format: {
						postfix: '<?php echo $lengthUnit; ?>',
						decimals: 0,
					}
				}
			});		
			<?php
		}
		if ($params->get('show_width',1))
		{
			?>
			$("#width-behaviour").noUiSlider({
				start: [ <?php echo $minWidth ?: $params->get( 'min_width', 0); ?>, <?php echo $maxWidth ?: $params->get( 'max_width', 100); ?> ],
				range: {
					'min': <?php echo $params->get( 'min_width', 0); ?>,
					'max': <?php echo $params->get( 'max_width', 100); ?>
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
							target: function( value, handleElement, slider ){
								$("#max_width").val( value );
							}
						}),
					],
					format: {
						postfix: '<?php echo $lengthUnit; ?>',
						decimals: 0,
					}
				}
			});					
			<?php
		}
		if ($params->get('show_height',1))
		{
			?>
			$("#height-behaviour").noUiSlider({
				start: [ <?php echo $minHeight ?: $params->get( 'min_height', 0); ?>, <?php echo $maxHeight ?: $params->get( 'max_height', 100); ?> ],
				range: {
					'min': <?php echo $params->get( 'min_height', 0); ?>,
					'max': <?php echo $params->get( 'max_height', 100); ?>
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
							target: function( value, handleElement, slider ){
								$("#max_height").val( value );
							}
						}),
					],
					format: {
						postfix: '<?php echo $lengthUnit; ?>',
						decimals: 0,
					}
				}
			});						
			<?php
		}
		?>
	})
</script>