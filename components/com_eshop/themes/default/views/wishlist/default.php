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
$imgPolaroid            = $bootstrapHelper->getClassMapping('img-polaroid');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');
?>
<script src="<?php echo Uri::root(true); ?>/media/com_eshop/assets/colorbox/jquery.colorbox.js" type="text/javascript"></script>
<?php
if (isset($this->success))
{
	?>
	<div class="success"><?php echo $this->success; ?></div>
	<?php
}
?>
<div class="page-header">
	<h1 class="page-title eshop-title"><?php echo Text::_('ESHOP_MY_WISHLIST'); ?></h1>
</div>
<?php
if (!count($this->products))
{
	?>
	<div class="no-content"><?php echo Text::_('ESHOP_WISHLIST_EMPTY'); ?></div>
	<?php
}
else
{
	?>
	<table class="table table-responsive table-bordered table-striped">
		<thead>
			<tr>
				<th><?php echo Text::_('ESHOP_IMAGE'); ?></th>
				<th><?php echo Text::_('ESHOP_PRODUCT_NAME'); ?></th>
				<th><?php echo Text::_('ESHOP_MODEL'); ?></th>
				<th><?php echo Text::_('ESHOP_AVAILABILITY'); ?></th>
				<th><?php echo Text::_('ESHOP_UNIT_PRICE'); ?></th>
				<th><?php echo Text::_('ESHOP_ACTION'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($this->products as $product)
			{
				$viewProductUrl = Route::_(EShopRoute::getProductRoute($product->id, EShopHelper::getProductCategory($product->id)));
				?>
				<tr>
					<td class="muted eshop-center-text" data-content="<?php echo Text::_('ESHOP_IMAGE'); ?>">
						<a href="<?php echo $viewProductUrl; ?>">
							<img class="<?php echo $imgPolaroid; ?>" src="<?php echo $product->image; ?>" />
						</a>
					</td>
					<td data-content="<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>">
						<a href="<?php echo $viewProductUrl; ?>">
							<?php echo $product->product_name; ?>
						</a>
					</td>
					<td data-content="<?php echo Text::_('ESHOP_MODEL'); ?>"><?php echo $product->product_sku; ?></td>
					<td data-content="<?php echo Text::_('ESHOP_AVAILABILITY'); ?>">
						<?php echo $product->availability; ?>
					</td>
					<td data-content="<?php echo Text::_('ESHOP_UNIT_PRICE'); ?>">
						<?php
						if (EShopHelper::showPrice())
						{
							if (!$product->product_call_for_price)
							{
								if ($product->sale_price)
								{
									?>
									<span class="eshop-base-price"><?php echo $product->base_price; ?></span>&nbsp;
									<span class="eshop-sale-price"><?php echo $product->sale_price; ?></span>
									<?php
								}
								else 
								{
									?>
									<span class="price"><?php echo $product->base_price; ?></span>
									<?php
								}
							}
							else
							{
								?>
								<span class="call-for-price"><?php echo Text::_('ESHOP_CALL_FOR_PRICE'); ?>: <?php echo EShopHelper::getConfigValue('telephone'); ?></span>
								<?php
							}
						}
						?>
					</td>
					<td data-content="<?php echo Text::_('ESHOP_ACTION'); ?>">
						<?php
						if (EShopHelper::isCartMode($product))
						{
						    $message = EShopHelper::getCartSuccessMessage($product->id, $product->product_name);
							?>
							<input id="add-to-cart-<?php echo $product->id; ?>" type="button" class="<?php echo $btnBtnPrimaryClass; ?>" onclick="addToCart(<?php echo $product->id; ?>, 1, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>', '<?php echo EShopHelper::getConfigValue('cart_popout')?>', '<?php echo Route::_(EShopRoute::getViewRoute('cart')); ?>', '<?php echo $message; ?>');" value="<?php echo Text::_('ESHOP_ADD_TO_CART'); ?>" />
							<?php
						}
						if (EShopHelper::isQuoteMode($product))
						{
							?>
							<input id="add-to-quote-<?php echo $product->id; ?>" type="button" class="<?php echo $btnBtnPrimaryClass; ?>" onclick="addToQuote(<?php echo $product->id; ?>, 1, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>');" value="<?php echo Text::_('ESHOP_ADD_TO_QUOTE'); ?>" />
							<?php
						}
						?>
						<input type="button" class="<?php echo $btnBtnPrimaryClass; ?>" onclick="removeFromWishlist(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>');" value="<?php echo Text::_('ESHOP_REMOVE'); ?>" />
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>	
	<?php
}