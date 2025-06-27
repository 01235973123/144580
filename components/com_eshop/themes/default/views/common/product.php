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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$rootUri	= Uri::root(true);
$currency	= new EShopCurrency();
$tax		= new EShopTax(EShopHelper::getConfig());

$bootstrapHelper        = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
$btnBtnSecondaryClass	= $bootstrapHelper->getClassMapping('btn btn-secondary');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

$viewProductUrl = Route::_(EShopRoute::getProductRoute($product->id, $product->product_main_category_id ?: EShopHelper::getProductCategory($product->id)));
$image = $product->image;
$labels = $product->labels;

// Get parameters
$showShortDesc     = $params->get('show_short_desc', 0);
$shortDescLimited  = $params->get('short_desc_limited', 100);
$showPrice         = $params->get('show_price', 1);
$showAddcart       = $params->get('show_addtocart', 1);
$showAddquote      = $params->get('show_addtoquote', 1);
$showAddToWishlist = $params->get('show_add_to_wishlist', 1);
$showAddToCompare  = $params->get('show_add_to_compare', 1);
$showRating        = $params->get('show_rating', 1);
?>
<div class="eshop-image-block">
	<a href="<?php echo $viewProductUrl; ?>">
		<?php
		if (count($labels) && $params->get('enable_labels'))
		{
			for ($i = 0; $n = count($labels), $i < $n; $i++)
			{
				$label = $labels[$i];
				if ($label->label_style == 'rotated' && !($label->enable_image && $label->label_image))
				{
					?>
					<div class="cut_rotated">
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
					<span class="horizontal <?php echo $label->label_position; ?> small-db" style="opacity: <?php echo $label->label_opacity; ?>;<?php echo 'background-image: url(' . $label->label_image . ')'; ?>; background-repeat: no-repeat; width: <?php echo $imageWidth; ?>px; height: <?php echo $imageHeight; ?>px; box-shadow: none;"></span>
					<?php
				}
				else 
				{
					?>
					<span class="<?php echo $label->label_style; ?> <?php echo $label->label_position; ?> small-db" style="background-color: <?php echo '#'.$label->label_background_color; ?>; color: <?php echo '#'.$label->label_foreground_color; ?>; opacity: <?php echo $label->label_opacity; ?>;<?php if ($label->label_bold) echo 'font-weight: bold;'; ?>">
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
		<span class="product-image-2">
			<img alt="<?php echo $product->product_name; ?>" src="<?php echo $image; ?>">
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
<div class="product-info-2">
	<a href="<?php echo $viewProductUrl; ?>" title="<?php echo $product->product_name; ?>">
		<?php echo $product->product_name; ?>
	</a>
	<div class="eshop-product-price">
		<?php
		if ($showPrice == 1 && EShopHelper::showPrice() && !$product->product_call_for_price)
		{
			$productPriceArray = EShopHelper::getProductPriceArray($product->id, $product->product_price);
			if ($productPriceArray['salePrice'] >= 0)
			{
				?>
				<span class="eshop-base-price"><?php echo $currency->format($tax->calculate($productPriceArray['basePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>&nbsp;
				<span class="eshop-sale-price"><?php echo $currency->format($tax->calculate($productPriceArray['salePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>
				<?php
			}
			else
			{
				?>
				<span class="price"><?php echo $currency->format($tax->calculate($productPriceArray['basePrice'], $product->product_taxclass_id, EShopHelper::getConfigValue('tax'))); ?></span>
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
						echo $currency->format($productPriceArray['salePrice']);
					}
					else
					{
						echo $currency->format($productPriceArray['basePrice']);
					}
					?>
				</small>
			<?php
			}
		}
		
		if ($product->product_call_for_price)
		{
			?>
			<span class="call-for-price"><?php echo Text::_('ESHOP_CALL_FOR_PRICE'); ?>: <?php echo EShopHelper::getConfigValue('telephone'); ?></span>
			<?php
		}
		?>
	</div>
	<?php	
	$productShortDesc = $product->product_short_desc;
	
	if ($showShortDesc && $productShortDesc != '')
	{
	    if ($shortDescLimited > 0 && strlen($productShortDesc) > $shortDescLimited)
	    {
			$productShortDesc = HTMLHelper::_('string.truncate', $productShortDesc, $shortDescLimited);
	    }
	    ?>
	    <div class="eshop-product-desc"><?php echo $productShortDesc; ?></div>
	    <?php
	}
	    				
	if ($showRating)
	{
		?>
		<div class="product-review">
			<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/stars-<?php echo round(EShopHelper::getProductRating($product->id)); ?>.png" />
		</div>
		<?php
	}
	?>
	<div class="eshop-buttons">
		<?php
		if (!$product->has_required_otpion)
		{
			if ($showAddcart == 1 && EShopHelper::isCartMode($product))
			{
				$message = EShopHelper::getAddToCartSuccessMessage($product, $viewProductUrl);
				?>
				<div class="eshop-cart-area">
					<input id="add-to-cart-<?php echo $product->id; ?>" type="button" class="<?php echo $btnBtnPrimaryClass; ?>" onclick="addToCart(<?php echo $product->id; ?>, 1, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>', '<?php echo EShopHelper::getConfigValue('cart_popout')?>', '<?php echo Route::_(EShopRoute::getViewRoute('cart')); ?>', '<?php echo $message; ?>');" value="<?php echo Text::_('ESHOP_ADD_TO_CART'); ?>" />
				</div>
				<?php
			}
			
			if ($showAddquote == 1 && EShopHelper::isQuoteMode($product))
			{
				?>
				<div class="eshop-quote-area">
					<input id="add-to-quote-<?php echo $product->id; ?>" type="button" class="<?php echo $btnBtnPrimaryClass; ?>"
					   onclick="addToQuote(<?php echo $product->id; ?>, 1, '<?php echo EShopHelper::getSiteUrl(); ?>', '<?php echo EShopHelper::getAttachedLangLink(); ?>');"
					   value="<?php echo Text::_('ESHOP_ADD_TO_QUOTE'); ?>"/>
				</div>
				<?php
			}
		}
		else 
		{
			?>
			<div class="eshop-cart-area">
				<a class="<?php echo $btnBtnPrimaryClass; ?>" href="<?php echo $viewProductUrl; ?>" title="<?php echo $product->product_name; ?>"><?php echo Text::_('ESHOP_PRODUCT_VIEW_DETAILS'); ?></a>
			</div>
			<?php
		}
							
		if ((EShopHelper::getConfigValue('allow_wishlist') && $showAddToWishlist) || (EShopHelper::getConfigValue('allow_compare') && $showAddToCompare))
		{
			if (EShopHelper::getConfigValue('use_button_icons', 0))
			{
				?>
				<div class="box-action-icons-list">
					<?php
					if (EShopHelper::getConfigValue('allow_wishlist') && $showAddToWishlist)
					{
						?>
						<a class="<?php echo $btnBtnSecondaryClass; ?>" style="cursor: pointer;" onclick="addToWishList(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>')" title="<?php echo Text::_('ESHOP_ADD_TO_WISH_LIST'); ?>"><?php echo EShopHelper::getConfigValue('wishlist_button_code'); ?></a>
						<?php
					}
					if (EShopHelper::getConfigValue('allow_compare') && $showAddToCompare)
					{
						?>
						<a class="<?php echo $btnBtnSecondaryClass; ?>" style="cursor: pointer;" onclick="addToCompare(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>')" title="<?php echo Text::_('ESHOP_ADD_TO_COMPARE'); ?>"><?php echo EShopHelper::getConfigValue('compare_button_code'); ?></a>
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
					if (EShopHelper::getConfigValue('allow_wishlist') && $showAddToWishlist)
					{
						?>
						<a class="<?php echo $btnBtnSecondaryClass; ?>" style="cursor: pointer;" onclick="addToWishList(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>')" title="<?php echo Text::_('ESHOP_ADD_TO_WISH_LIST'); ?>"><?php echo Text::_('ESHOP_ADD_TO_WISH_LIST'); ?></a>
						<?php
					}
					if (EShopHelper::getConfigValue('allow_compare') && $showAddToCompare)
					{
						?>
						<a class="<?php echo $btnBtnSecondaryClass; ?>" style="cursor: pointer;" onclick="addToCompare(<?php echo $product->id; ?>, '<?php echo EShopHelper::getSiteUrl(); ?>')" title="<?php echo Text::_('ESHOP_ADD_TO_COMPARE'); ?>"><?php echo Text::_('ESHOP_ADD_TO_COMPARE'); ?></a>
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