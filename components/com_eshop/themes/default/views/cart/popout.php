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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$bootstrapHelper        = $this->bootstrapHelper;
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$inputAppendClass       = $bootstrapHelper->getClassMapping('input-append');
$inputPrependClass      = $bootstrapHelper->getClassMapping('input-prepend');
$imgPolaroid            = $bootstrapHelper->getClassMapping('img-polaroid');
$btnClass				= $bootstrapHelper->getClassMapping('btn');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

$rootUri = Uri::root(true);
?>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/colorbox/jquery.colorbox.js" type="text/javascript"></script>
<h1>
	<?php echo Text::_('ESHOP_SHOPPING_CART'); ?>
	<?php
	if ($this->weight)
	{
		echo '&nbsp;(' . $this->weight . ')';
	}
	?>
</h1>
<?php
if (isset($this->success))
{
	?>
	<div class="success"><?php echo $this->success; ?></div>
	<?php
}
if (isset($this->warning))
{
	?>
	<div class="warning"><?php echo $this->warning; ?></div>
	<?php
}
?>
<?php
if (!count($this->cartData))
{
	?>
	<div class="no-content"><?php echo Text::_('ESHOP_CART_EMPTY'); ?></div>
	<?php
}
else
{
    $productFieldsDisplay       = EShopHelper::getConfigValue('product_fields_display', '');
    $productFieldsDisplayArr    = array();
    
    if ($productFieldsDisplay != '')
    {
        $productFieldsDisplayArr = explode(',', $productFieldsDisplay);
    }
	?>
	<div class="cart-info">
		<?php
		$countProducts = 0;
		?>
		<table class="table table-responsive table-bordered table-striped">
			<thead>
				<tr>
					<th style="text-align: center;"><?php echo Text::_('ESHOP_REMOVE'); ?></th>
					<th><?php echo Text::_('ESHOP_PRODUCT_NAME'); ?></th>
					<?php
					if (in_array('product_image', $productFieldsDisplayArr))
					{
					    ?>
					    <th style="text-align: center;"><?php echo Text::_('ESHOP_IMAGE'); ?></th>
					    <?php
					}
					
					if (in_array('product_sku', $productFieldsDisplayArr))
					{
					    ?>
					    <th><?php echo Text::_('ESHOP_MODEL'); ?></th>
					    <?php
					}
					
					if (in_array('product_quantity', $productFieldsDisplayArr))
					{
					    ?>
					    <th><?php echo Text::_('ESHOP_QUANTITY'); ?></th>
					    <?php
					}
					?>
					<th nowrap="nowrap"><?php echo Text::_('ESHOP_UNIT_PRICE'); ?></th>
					<th><?php echo Text::_('ESHOP_TOTAL'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($this->cartData as $key => $product)
				{
					$countProducts++;
					$optionData = $product['option_data'];
					$viewProductUrl = Route::_(EShopRoute::getProductRoute($product['product_id'], EShopHelper::getProductCategory($product['product_id'])));
					?>
					<tr>
						<td class="eshop-center-text" style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_REMOVE'); ?>">
							<a class="eshop-remove-item-cart" id="<?php echo $key; ?>" style="cursor: pointer;">
								<img alt="<?php echo Text::_('ESHOP_REMOVE'); ?>" title="<?php echo Text::_('ESHOP_REMOVE'); ?>" src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/remove.png" />
							</a>
						</td>
						<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>">
							<a href="<?php echo $viewProductUrl; ?>">
								<?php echo $product['product_name']; ?>
							</a>
							<?php
							if ($product['product_stock_warning'] && !$product['stock'])
							{
								?>
								<span class="stock">***</span>
								<?php
							}
							?>
							<br />	
							<?php
							for ($i = 0; $n = count($optionData), $i < $n; $i++)
							{
								echo '- ' . $optionData[$i]['option_name'] . ': ' . htmlentities($optionData[$i]['option_value']) . (isset($optionData[$i]['sku']) && $optionData[$i]['sku'] != '' ? ' (' . $optionData[$i]['sku'] . ')' : '') . '<br />';
							}
							?>
						</td>
						<?php
						if (in_array('product_image', $productFieldsDisplayArr))
						{
						    ?>
						    <td class="muted eshop-center-text" style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_IMAGE'); ?>">
    							<a href="<?php echo $viewProductUrl; ?>">
    								<img class="<?php echo $imgPolaroid; ?>" src="<?php echo $product['image']; ?>" />
    							</a>
    						</td>
						    <?php
						}
						
						if (in_array('product_sku', $productFieldsDisplayArr))
						{
						    ?>
						    <td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_MODEL'); ?>"><?php echo $product['product_sku']; ?></td>
						    <?php
						}
						
						if (in_array('product_quantity', $productFieldsDisplayArr))
						{
						    ?>
						    <td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_QUANTITY'); ?>">
    							<div class="<?php echo $inputAppendClass; ?> <?php echo $inputPrependClass; ?>">
    								<span class="eshop-quantity">
    									<input type="hidden" name="key[]" value="<?php echo $key; ?>" />
    									<a onclick="quantityUpdate('+', 'quantity_cart_<?php echo $countProducts; ?>', <?php echo EShopHelper::getConfigValue('quantity_step', '1'); ?>);<?php echo EShopHelper::getConfigValue('update_cart_function', 'update_button') == 'quantity_button' ? 'updateCart();' : ''; ?>" class="<?php echo $btnClass; ?> button-plus" id="cart_<?php echo $countProducts; ?>">+</a>
    										<input type="text" class="eshop-quantity-value" value="<?php echo EShopHelper::escape($product['quantity']); ?>" name="quantity[]" id="quantity_cart_<?php echo $countProducts; ?>" />
    									<a onclick="quantityUpdate('-', 'quantity_cart_<?php echo $countProducts; ?>', <?php echo EShopHelper::getConfigValue('quantity_step', '1'); ?>);<?php echo EShopHelper::getConfigValue('update_cart_function', 'update_button') == 'quantity_button' ? 'updateCart();' : ''; ?>" class="<?php echo $btnClass; ?> button-minus" id="cart_<?php echo $countProducts; ?>">-</a>
    								</span>
    							</div>
    						</td>
						    <?php
						}
						?>
						<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_UNIT_PRICE'); ?>">
							<?php
							if (EShopHelper::showPrice())
							{
								if (EShopHelper::getConfigValue('include_tax_anywhere', '0'))
								{
									echo $this->currency->format($this->tax->calculate($product['price'], $product['product_taxclass_id'], EShopHelper::getConfigValue('tax')));
								}
								else
								{
									echo $this->currency->format($product['price']);
								}
							}
							?>
						</td>
						<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_TOTAL'); ?>">
							<?php
							if (EShopHelper::showPrice())
							{
								if (EShopHelper::getConfigValue('include_tax_anywhere', '0'))
								{
									echo $this->currency->format($this->tax->calculate($product['total_price'], $product['product_taxclass_id'], EShopHelper::getConfigValue('tax')));
								}
								else
								{
									echo $this->currency->format($product['total_price']);
								}
							}
							?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
		if (EShopHelper::showPrice())
		{
			?>
			<div class="totals text-center" style="text-align: center">
			<?php
				foreach ($this->totalData as $data)
				{
					?>
						<div>
							<?php echo $data['title']; ?>:
							<?php echo $data['text']; ?>
						</div>
					<?php	
				} ?>
			</div>
			<?php
		}
	    ?>
    </div>
    <div class="<?php echo $controlGroupClass; ?>" style="text-align: center;">
		<div class="controls">
			<a class="<?php echo $btnClass; ?> btn-danger" href="<?php echo Route::_(EShopHelper::getContinueShopingUrl()); ?>"><?php echo Text::_('ESHOP_CONTINUE_SHOPPING'); ?></a>
			<?php
			if (EShopHelper::getConfigValue('update_cart_function', 'update_button') == 'update_button')
			{
			    ?>
			    <button type="button" class="<?php echo $btnClass; ?> btn-info" onclick="updateCart();" id="update-cart"><?php echo Text::_('ESHOP_UPDATE_CART'); ?></button>
			    <?php
			}
			?>
			<a class="<?php echo $btnClass; ?> btn-success" href="<?php echo Route::_(EShopRoute::getViewRoute('cart')); ?>"><?php echo Text::_('ESHOP_SHOPPING_CART'); ?></a>
			<?php
			if (EShopHelper::getConfigValue('active_https'))
			{
				$checkoutUrl = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
			}
			else
			{
				$checkoutUrl = Route::_(EShopRoute::getViewRoute('checkout'));
			}
			?>
			<a class="<?php echo $btnBtnPrimaryClass; ?>" href="<?php echo $checkoutUrl; ?>"><?php echo Text::_('ESHOP_CHECKOUT'); ?></a>
		</div>
	</div>
	
	<script type="text/javascript">
		//Function to update cart
		function updateCart()
		{
			Eshop.jQuery(function($){
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type: 'POST',
					url: siteUrl + 'index.php?option=com_eshop&task=cart.updates<?php echo EShopHelper::getAttachedLangLink(); ?>',
					data: $('.cart-info input[type=\'text\'], .cart-info input[type=\'hidden\']'),
					beforeSend: function() {
						$('#update-cart').attr('disabled', true);
						$('#update-cart').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
					},
					complete: function() {
						$('#update-cart').attr('disabled', false);
						$('.wait').remove();
					},
					success: function() {
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=cart&layout=popout&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>',
							dataType: 'html',
							success: function(html) {
								$.colorbox({
									overlayClose: true,
									opacity: 0.5,
									width: '90%',
									maxWidth: '800px',
									href: false,
									html: html
								});
								$.ajax({
									url: siteUrl + 'index.php?option=com_eshop&view=cart&layout=mini&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>',
									dataType: 'html',
									success: function(html) {
										$('#eshop-cart').html(html);
										$('.eshop-content').hide();
									},
									error: function(xhr, ajaxOptions, thrownError) {
										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
									}
								});
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
				  	}
				});
			})
		}

		Eshop.jQuery(function($) {
			//Ajax remove cart item
			$('.eshop-remove-item-cart').bind('click', function() {
				var aTag = $(this);
				var id = aTag.attr('id');
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type :'POST',
					url: siteUrl + 'index.php?option=com_eshop&task=cart.remove&key=' +  id + '&redirect=1<?php echo EShopHelper::getAttachedLangLink(); ?>',
					beforeSend: function() {
						aTag.attr('disabled', true);
						aTag.after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
					},
					complete: function() {
						aTag.attr('disabled', false);
						$('.wait').remove();
					},
					success : function() {
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=cart&layout=popout&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>',
							dataType: 'html',
							success: function(html) {
								$.colorbox({
									overlayClose: true,
									opacity: 0.5,
									width: '90%',
									maxWidth: '800px',
									href: false,
									html: html
								});
								$.ajax({
									url: siteUrl + 'index.php?option=com_eshop&view=cart&layout=mini&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>',
									dataType: 'html',
									success: function(html) {
										$('#eshop-cart').html(html);
										$('.eshop-content').hide();
									},
									error: function(xhr, ajaxOptions, thrownError) {
										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
									}
								});
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			});
		});
	</script>
	<?php
}