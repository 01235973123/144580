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

$uri = Uri::getInstance();
$bootstrapHelper        = $this->bootstrapHelper;
$rowFluidClass          = $bootstrapHelper->getClassMapping('row-fluid');
$span3Class             = $bootstrapHelper->getClassMapping('span3');
$span4Class             = $bootstrapHelper->getClassMapping('span4');
$span6Class             = $bootstrapHelper->getClassMapping('span6');
$span8Class             = $bootstrapHelper->getClassMapping('span8');
$span12Class            = $bootstrapHelper->getClassMapping('span12');
$pullLeftClass          = $bootstrapHelper->getClassMapping('pull-left');
$pullRightClass         = $bootstrapHelper->getClassMapping('pull-right');
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass      = $bootstrapHelper->getClassMapping('control-label');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$inputAppendClass       = $bootstrapHelper->getClassMapping('input-append');
$inputPrependClass      = $bootstrapHelper->getClassMapping('input-prepend');
$imgPolaroid            = $bootstrapHelper->getClassMapping('img-polaroid');
$btnClass				= $bootstrapHelper->getClassMapping('btn');
$btnBtnSecondaryClass	= $bootstrapHelper->getClassMapping('btn btn-secondary');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

$rootUri = Uri::root(true);
?>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/colorbox/jquery.colorbox.js" type="text/javascript"></script>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/js/slick.js" type="text/javascript"></script>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/js/eshop-pagination.js" type="text/javascript"></script>

<div class="product-info">
	<div class="<?php echo $rowFluidClass; ?> product-cart clearfix">
		<?php 
		if (EShopHelper::isCartMode($this->item) || EShopHelper::isQuoteMode($this->item))
		{
			?>
			<div class="<?php echo $span8Class; ?> no_margin_left">
				<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
				<?php
				if (EShopHelper::showProductQuantityBox($this->item->id, 'show_quantity_box_in_product_page'))
				{
					?>
					<div class="<?php echo $inputAppendClass; ?> <?php echo $inputPrependClass; ?>">
						<label class="<?php echo $btnBtnSecondaryClass; ?>"><?php echo Text::_('ESHOP_QTY'); ?>:</label>
						<span class="eshop-quantity">
							<a onclick="quantityUpdate('-', 'quantity_<?php echo $this->item->id; ?>', <?php echo EShopHelper::getConfigValue('quantity_step', '1'); ?>)" class="<?php echo $btnBtnSecondaryClass; ?> button-minus" id="<?php echo $this->item->id; ?>">-</a>
							<input type="text" class="input-small form-control eshop-quantity-value" id="quantity_<?php echo $this->item->id; ?>" name="quantity" value="<?php echo EShopHelper::getConfigValue('start_quantity_number', '1'); ?>" />
							<a onclick="quantityUpdate('+', 'quantity_<?php echo $this->item->id; ?>', <?php echo EShopHelper::getConfigValue('quantity_step', '1'); ?>)" class="<?php echo $btnBtnSecondaryClass; ?> button-plus" id="<?php echo $this->item->id; ?>">+</a>
						</span>
					</div>
					<?php
				}
				if (EShopHelper::isCartMode($this->item))
				{
					?>
					<button id="add-to-cart" class="<?php echo $btnBtnPrimaryClass; ?>" type="button"><?php echo Text::_('ESHOP_ADD_TO_CART'); ?></button>
					<?php
				}
				if (EShopHelper::isQuoteMode($this->item))
				{
					?>
					<button id="add-to-quote" class="<?php echo $btnBtnPrimaryClass; ?>" type="button"><?php echo Text::_('ESHOP_ADD_TO_QUOTE'); ?></button>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>

<script type="text/javascript">
	// Add to cart button
	Eshop.jQuery(function($){
		$('#add-to-cart').bind('click', function() {
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				type: 'POST',
				url: siteUrl + 'index.php?option=com_eshop&task=cart.add<?php echo EShopHelper::getAttachedLangLink(); ?>',
				data: $('.product-info input[type=\'text\'], .product-info input[type=\'hidden\'], .product-info input[type=\'radio\']:checked, .product-info input[type=\'checkbox\']:checked, .product-info select, .product-info textarea'),
				dataType: 'json',
				beforeSend: function() {
					$('#add-to-cart').attr('disabled', true);
					$('#add-to-cart').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
				},
				complete: function() {
					$('#add-to-cart').attr('disabled', false);
					$('.wait').remove();
				},
				success: function(json) {
					$('.error').remove();
					if (json['error']) {
						if (json['error']['option']) {
							for (i in json['error']['option']) {
								$('#option-' + i).after('<span class="error">' + json['error']['option'][i] + '</span>');
							}
						}
					}
					if (json['success']) {
						<?php
						if (EShopHelper::getConfigValue('cart_popout', 'popout') == 'message')
						{
						    $message = EShopHelper::getCartSuccessMessage($this->item->id, $this->item->product_name);
                            ?>
                            $.ajax({
								url: siteUrl + 'index.php?option=com_eshop&view=cart&layout=mini&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=' + json['time'],
								dataType: 'html',
								success: function(html) {
									$('#eshop-cart').html(html);
									$('.eshop-content').hide();
									$('.alert-success').remove();
                                    $('.product-cart').before('<div class="alert-success"><?php echo $message; ?></div>');
								},
								error: function(xhr, ajaxOptions, thrownError) {
									alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
								}
							});
                            <?php
						}
						elseif (EShopHelper::getConfigValue('cart_popout', 'popout') == 'redirect')
						{
						    ?>
    						window.location.href = '<?php echo Route::_(EShopRoute::getViewRoute('cart')); ?>';
    						<?php
						}
						else
						{
							?>
							$.ajax({
								url: siteUrl + 'index.php?option=com_eshop&view=cart&layout=popout&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=' + json['time'],
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
										url: siteUrl + 'index.php?option=com_eshop&view=cart&layout=mini&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=' + json['time'],
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
							<?php
						}
						?>
					}
			  	},
			  	error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});
		
		$('#add-to-quote').bind('click', function() {
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				type: 'POST',
				url: siteUrl + 'index.php?option=com_eshop&task=quote.add<?php echo EShopHelper::getAttachedLangLink(); ?>',
				data: $('.product-info input[type=\'text\'], .product-info input[type=\'hidden\'], .product-info input[type=\'radio\']:checked, .product-info input[type=\'checkbox\']:checked, .product-info select, .product-info textarea'),
				dataType: 'json',
				beforeSend: function() {
					$('#add-to-quote').attr('disabled', true);
					$('#add-to-quote').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
				},
				complete: function() {
					$('#add-to-quote').attr('disabled', false);
					$('.wait').remove();
				},
				success: function(json) {
					$('.error').remove();
					if (json['error']) {
						if (json['error']['option']) {
							for (i in json['error']['option']) {
								$('#option-' + i).after('<span class="error">' + json['error']['option'][i] + '</span>');
							}
						}
					}
					if (json['success']) {
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=quote&layout=popout&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=' + json['time'],
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
									url: siteUrl + 'index.php?option=com_eshop&view=quote&layout=mini&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=' + json['time'],
									dataType: 'html',
									success: function(html) {
										$('#eshop-quote').html(html);
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
			  	}
			});
		});
	})
</script>