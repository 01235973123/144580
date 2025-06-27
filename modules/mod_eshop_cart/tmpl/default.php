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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$rootUri = Uri::root(true);

if (!EShopHelper::getConfigValue('catalog_mode'))
{
	?>
	<div id="eshop-cart" class="eshop-cart<?php echo $params->get( 'moduleclass_sfx' ); ?>">
		<div class="eshop-cart-items">
			<a>
				<i class="fa fa-shopping-cart">&nbsp;</i>
				<span id="eshop-cart-total">
					<?php
					   echo $countProducts;
					   if (EShopHelper::showPrice())
					   {
					       ?>
	   						&nbsp;-&nbsp;<?php echo $totalPrice; ?>
	   						<?php
	   					}
                    ?>
				</span>
			</a>
		</div>
		<div class="eshop-cart-content" style="display: none;">
		<?php
			if ($countProducts == 0)
			{
				echo Text::_('ESHOP_CART_EMPTY');
			}
			else
			{
			?>
			<div class="eshop-mini-cart-info">
				<table cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td colspan="5" style="border: 0px;"><span class="wait"></span></td>
					</tr>
					<?php
					foreach ($items as $key => $product)
					{
						$optionData = $product['option_data'];
						$viewProductUrl = Route::_(EShopRoute::getProductRoute($product['product_id'], EShopHelper::getProductCategory($product['product_id'])));
						?>
						<tr>
							<td class="eshop-image">
								<a href="<?php echo $viewProductUrl; ?>">
									<img src="<?php echo $product['image']; ?>" />
								</a>
							</td>
							<td class="eshop-name">
								<a href="<?php echo $viewProductUrl; ?>">
									<?php echo $product['product_name']; ?>
								</a>
								<div>
								<?php
								for ($i = 0; $n = count($optionData), $i < $n; $i++)
								{
									echo '<small>- ' . $optionData[$i]['option_name'] . ': ' . $optionData[$i]['option_value'] . (isset($optionData[$i]['sku']) && $optionData[$i]['sku'] != '' ? ' (' . $optionData[$i]['sku'] . ')' : '') . '</small><br />';
								}
								?>
								</div>
							</td>
							<td class="eshop-quantity">
								x&nbsp;<?php echo $product['quantity']; ?>
							</td>
							<?php
							if (EShopHelper::showPrice())
							{
								?>
								<td class="eshop-total">
									<?php echo $currency->format($product['total_price']); ?>
								</td>
								<?php
							}
							?>
							<td class="eshop-remove">
								<a class="eshop-remove-cart-item" href="#" id="<?php echo $key; ?>">
									<img alt="<?php echo Text::_('ESHOP_REMOVE'); ?>" title="<?php echo Text::_('ESHOP_REMOVE'); ?>" src="<?php echo $rootUri; ?>/components/com_eshop/assets/images/remove.png" />
								</a>
							</td>
						</tr>
					<?php
					}
					?>
				</table>
			</div>
			<?php
			if (EShopHelper::showPrice())
			{
				?>
				<div class="mini-cart-total">
					<table cellpadding="0" cellspacing="0" width="100%">
						<?php
						foreach ($totalData as $data)
						{
							?>
							<tr>
								<td class="eshop-right"><strong><?php echo $data['title']; ?>:&nbsp;</strong></td>
								<td class="eshop-right"><?php echo $data['text']; ?></td>
							</tr>
							<?php
						}
						?>
					</table>
				</div>
				<?php
			}
			?>
			<div class="checkout">
				<a href="<?php echo Route::_(EShopRoute::getViewRoute('cart')); ?>"><?php echo Text::_('ESHOP_VIEW_CART'); ?></a>
				&nbsp;|&nbsp;
				<a href="<?php echo Route::_(EShopRoute::getViewRoute('checkout')); ?>"><?php echo Text::_('ESHOP_CHECKOUT'); ?></a>
			</div>
			<?php
			}
			?>
		</div>
	</div>
	<script type="text/javascript">
		(function($) {
			$(document).ready(function() {
				$('.eshop-cart-items a').click(function() {
					$('.eshop-cart-content').slideToggle('fast');
				});
				$('.eshop-cart-content').mouseleave(function() {
					$('.eshop-cart-content').hide();
				});
				//Ajax remove cart item
				$('.eshop-remove-cart-item').bind('click', function() {
					var id = $(this).attr('id');
					$.ajax({
						type :'POST',
						url  : '<?php echo EShopHelper::getSiteUrl(); ?>index.php?option=com_eshop&task=cart.remove&key=' +  id + '&redirect=<?php echo ($view == 'cart' || $view == 'checkout') ? '1' : '0'; ?>',
						beforeSend: function() {
							$('.wait').html('<img src="<?php echo $rootUri; ?>/components/com_eshop/assets/images/loading.gif" alt="" />');
						},
						success : function() {
							<?php
							if ($view == 'cart' || $view == 'checkout')
							{
								?>
								window.location.href = '<?php echo Route::_(EShopRoute::getViewRoute('cart')); ?>';
								<?php
							}
							else 
							{
								?>
								$.ajax({
									url: '<?php echo EShopHelper::getSiteUrl(); ?>index.php?option=com_eshop&view=cart&layout=mini&format=raw',
									dataType: 'html',
									success: function(html) {
										$('#eshop-cart').html(html);
										$('.eshop-cart-content').show();
									},
									error: function(xhr, ajaxOptions, thrownError) {
										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
									}
								});
								<?php
							}
							?>
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				});
			});
		})(jQuery)
	</script>
	<?php
}
