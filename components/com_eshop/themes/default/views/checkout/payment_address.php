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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$bootstrapHelper        = $this->bootstrapHelper;
$pullRightClass         = $bootstrapHelper->getClassMapping('pull-right');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

$rootUri = Uri::root(true);
$Itemid  = Factory::getApplication()->input->getInt('Itemid', 0);

if (!$Itemid)
{
    $Itemid = EShopRoute::getDefaultItemId();
}

if(EShopHelper::getConfigValue('enable_existing_addresses') == 1)
{
    if (isset($this->lists['address_id']))
    {
    ?>
    	<label class="radio">
    		<input type="radio" class="form-check-input" value="existing" name="payment_address" checked="checked" /> <?php echo Text::_('ESHOP_EXISTING_ADDRESS'); ?>
    	</label>
    	<div id="payment-existing">
    		<?php echo $this->lists['address_id']; ?>
    	</div>
    	<label class="radio">
    		<input type="radio" class="form-check-input" value="new" name="payment_address" /> <?php echo Text::_('ESHOP_NEW_ADDRESS'); ?>
    	</label>
    	<?php
    }
    else 
    {
    	?>
    	<input type="hidden" name="payment_address" value="new" />
    	<?php
    }
    ?>
    <div id="payment-new" style="display: <?php echo (isset($this->lists['address_id']) ? 'none' : 'block'); ?>;" class="form-horizontal">
    	<?php
    		echo $this->form->render(); 
    	?>	
    </div>
    <?php
}
else 
{
    ?>
    <div id="payment-new" class="form-horizontal">
        <?php
            echo $this->form->render();
        ?>
    </div>
    <?php
    if ($this->shipping_required && EShopHelper::getConfigValue('require_shipping_address', 1))
    {
    ?>
    	<div class="no_margin_right">
    		<label class="checkbox"><input type="checkbox" class="form-check-input" value="1" name="shipping_address" checked="checked" /><?php echo Text::_('ESHOP_SHIPPING_ADDRESS_SAME'); ?></label>
    	</div>
    <?php
    }
}
?>
<div class="no_margin_left">
	<input type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullRightClass; ?>" id="button-payment-address" value="<?php echo Text::_('ESHOP_CONTINUE'); ?>" />
</div>
<script type="text/javascript"><!--
	// Payment Address
	Eshop.jQuery(function($){
		$('#button-payment-address').click(function(){
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				url: siteUrl + 'index.php?option=com_eshop&task=checkout.processPaymentAddress<?php echo EShopHelper::getAttachedLangLink(); ?>',
				type: 'post',
				data: $('#payment-address input[type=\'text\'], #payment-address input[type=\'password\'], #payment-address input[type=\'checkbox\']:checked, #payment-address input[type=\'radio\']:checked, #payment-address input[type=\'hidden\'], #payment-address select'),
				dataType: 'json',
				beforeSend: function() {
					$('#button-payment-address').attr('disabled', true);
					$('#button-payment-address').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
				},
				complete: function() {
					$('#button-payment-address').attr('disabled', false);
					$('.wait').remove();
				},
				success: function(json) {
					$('.warning, .error').remove();
					
					if (json['return']) {
						window.location.href = json['return'];
					} else if (json['error']) {
						if (json['error']['warning']) {
							$('#payment-address .checkout-content').prepend('<div class="warning" style="display: none;">' + json['error']['warning'] + '</div>');
							$('.warning').fadeIn('slow');
						}
						var errors = json['error'];
						for (var field in errors)
						{
							errorMessage = errors[field];						
							$('#payment-address #' + field).after('<span class="error">' + errorMessage + '</span>');							
						}											
					} else {
						<?php
						if ($this->shipping_required)
						{
							?>
							var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
							<?php
							if (EShopHelper::getConfigValue('require_shipping_address' , 1))
							{
								?>
								var shipping_address = $('#payment-address input[name=\'shipping_address\']:checked').attr('value');

								if (shipping_address) {
									var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
									$.ajax({
										url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=shipping_method&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>&Itemid=<?php echo $Itemid; ?>',
										dataType: 'html',
										success: function(html) {
											$('#shipping-method .checkout-content').html(html);
											$('#payment-address .checkout-content').slideUp('slow');
											$('#shipping-method .checkout-content').slideDown('slow');
											$('#payment-address .checkout-heading a').remove();
											$('#shipping-address .checkout-heading a').remove();
											$('#shipping-method .checkout-heading a').remove();
											$('#payment-method .checkout-heading a').remove();
											$('#payment-address .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
											$('#shipping-address .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
											$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
											$.ajax({
												url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=shipping_address&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
												dataType: 'html',
												success: function(html) {
													$('#shipping-address .checkout-content').html(html);
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
								else
								{
									$.ajax({
    									url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=shipping_address&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
    									dataType: 'html',
    									success: function(html) {
    										$('#shipping-address .checkout-content').html(html);
    										$('#payment-address .checkout-content').slideUp('slow');
    										$('#shipping-address .checkout-content').slideDown('slow');
    										$('#payment-address .checkout-heading a').remove();
    										$('#shipping-address .checkout-heading a').remove();
    										$('#shipping-method .checkout-heading a').remove();
    										$('#payment-method .checkout-heading a').remove();
    										$('#payment-address .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
    										$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
    									},
    									error: function(xhr, ajaxOptions, thrownError) {
    										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
    									}
    								});
								}    								
								<?php
							}
							else 
							{
								?>
								$.ajax({
									url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=shipping_method&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>&Itemid=<?php echo $Itemid; ?>',
									dataType: 'html',
									success: function(html) {
										$('#shipping-method .checkout-content').html(html);
										$('#payment-address .checkout-content').slideUp('slow');
										$('#shipping-method .checkout-content').slideDown('slow');
										$('#payment-address .checkout-heading a').remove();
										$('#shipping-method .checkout-heading a').remove();
										$('#payment-method .checkout-heading a').remove();
										$('#payment-address .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
										$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
									},
									error: function(xhr, ajaxOptions, thrownError) {
										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
									}
								});
								<?php
							}
						}
						else
						{
							?>
							if (json['total'] > 0)
							{
								var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
								$.ajax({
									url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=payment_method&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>&Itemid=<?php echo $Itemid; ?>',
									dataType: 'html',
									success: function(html) {
										$('#payment-method .checkout-content').html(html);
										$('#payment-address .checkout-content').slideUp('slow');
										$('#payment-method .checkout-content').slideDown('slow');
										$('#payment-address .checkout-heading a').remove();
										$('#payment-method .checkout-heading a').remove();
										$('#payment-address .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
										$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
									},
									error: function(xhr, ajaxOptions, thrownError) {
										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
									}
								});
							}
							else
							{
								var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
								$.ajax({
									url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=confirm&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
									dataType: 'html',
									success: function(html) {
										$('#confirm .checkout-content').html(html);
										$('#payment-address .checkout-content').slideUp('slow');
										$('#confirm .checkout-content').slideDown('slow');
										$('#payment-address .checkout-heading a').remove();
										$('#payment-method .checkout-heading a').remove();
										$('#payment-address .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
										$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
									},
									error: function(xhr, ajaxOptions, thrownError) {
										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
									}
								});
							}
							<?php
						}
						?>
						var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=payment_address&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
							dataType: 'html',
							success: function(html) {
								$('#payment-address .checkout-content').html(html);
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});					
					}	  
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});	
		});
	
		$('#payment-address input[name=\'payment_address\']').change(function(){
			if (this.value == 'new') {
				$('#payment-existing').hide();
				$('#payment-new').show();
			} else {
				$('#payment-existing').show();
				$('#payment-new').hide();
			}
		});
		<?php
		if (EShopHelper::isFieldPublished('zone_id'))
		{
			?>
			$('#payment-address select[name=\'country_id\']').bind('change', function() {
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					url: siteUrl + 'index.php?option=com_eshop&task=cart.getZones<?php echo EShopHelper::getAttachedLangLink(); ?>&country_id=' + this.value,
					dataType: 'json',
					beforeSend: function() {
						$('.wait').remove();
						$('#payment-address select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
					},
					complete: function() {
						$('.wait').remove();
					},
					success: function(json) {				
						html = '<option value=""><?php echo Text::_('ESHOP_PLEASE_SELECT'); ?></option>';
						if (json['zones'] != '')
						{
							for (var i = 0; i < json['zones'].length; i++)
							{
			        			html += '<option value="' + json['zones'][i]['id'] + '"';
								if (json['zones'][i]['id'] == '<?php $this->payment_zone_id; ?>')
								{
				      				html += ' selected="selected"';
				    			}
				    			html += '>' + json['zones'][i]['zone_name'] + '</option>';
							}
						}
						$('select[name=\'zone_id\']').html(html);
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			});
			<?php
		}
		?>
	});
//--></script>