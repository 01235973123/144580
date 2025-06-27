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

$rootUri = Uri::root(true);
?>
<div class="eshop-cart-items">
	<a>
		<i class="fa fa-shopping-cart">&nbsp;</i>
		<span id="eshop-cart-total">
			<?php
			   echo $this->countProducts;
			   if (EShopHelper::showPrice())
			   {
			       ?>
					&nbsp;-&nbsp;<?php echo $this->totalPrice; ?>
					<?php
				}
            ?>
		</span>
	</a>
</div>
<div class="eshop-cart-content">
<?php
	if ($this->countProducts == 0)
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
			foreach ($this->items as $key => $product)
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
							echo '<small>- ' . $optionData[$i]['option_name'] . ': ' . htmlentities($optionData[$i]['option_value']) . (isset($optionData[$i]['sku']) && $optionData[$i]['sku'] != '' ? ' (' . $optionData[$i]['sku'] . ')' : '') . '</small><br />';
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
							<?php echo $this->currency->format($this->tax->calculate($product['total_price'], $product['product_taxclass_id'], EShopHelper::getConfigValue('tax'))); ?>
						</td>
						<?php
					}
					?>
					<td class="eshop-remove">
						<a class="eshop-remove-cart-item" href="#" id="<?php echo $key; ?>">
							<img alt="<?php echo Text::_('ESHOP_REMOVE'); ?>" title="<?php echo Text::_('ESHOP_REMOVE'); ?>" src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/remove.png" />
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
				foreach ($this->totalData as $data)
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
		<a href="<?php echo $checkoutUrl; ?>"><?php echo Text::_('ESHOP_CHECKOUT'); ?></a>
	</div>
	<?php
	}
	?>
</div>
<script type="text/javascript">
	Eshop.jQuery(function($) {
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
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type :'POST',
					url: siteUrl + 'index.php?option=com_eshop&task=cart.remove&key=' +  id + '&redirect=0<?php echo EShopHelper::getAttachedLangLink(); ?>',
					beforeSend: function() {
						$('.wait').html('<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" />');
					},
					success : function() {
						var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=cart&layout=mini&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>',
							dataType: 'html',
							success: function(html) {
								$('#eshop-cart').html(html);
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
	});
</script>