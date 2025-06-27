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
?>
<div class="form-horizontal">
	<?php 
		echo $this->form->render();
	?>
</div>
<div class="no_margin_left">
	<input type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullRightClass; ?>" id="button-guest-shipping" value="<?php echo Text::_('ESHOP_CONTINUE'); ?>" />
</div>
<script type="text/javascript"><!--
	Eshop.jQuery(document).ready(function($){
		// Guest Shipping
		$('#button-guest-shipping').click(function(){
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				url: siteUrl + 'index.php?option=com_eshop&task=checkout.processGuestShipping<?php echo EShopHelper::getAttachedLangLink(); ?>',
				type: 'post',
				data: $('#shipping-address input[type=\'text\'], #shipping-address textarea, #shipping-address select, #shipping-address input[type=\'radio\']:checked, #shipping-address input[type=\'checkbox\']:checked'),
				dataType: 'json',
				beforeSend: function() {
					$('#button-guest-shipping').attr('disabled', true);
					$('#button-guest-shipping').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
				},
				complete: function() {
					$('#button-guest-shipping').attr('disabled', false); 
					$('.wait').remove();
				},
				success: function(json) {
					$('.warning, .error').remove();
					if (json['return']) {
						window.location.href = json['return'];
					} else if (json['error']) {
						if (json['error']['warning']) {
							$('#shipping-address .checkout-content').prepend('<div class="warning" style="display: none;">' + json['error']['warning'] + '</div>');
							$('.warning').fadeIn('slow');
						}
						var errors = json['error'];
						for (var field in errors)
						{
							errorMessage = errors[field];						
							$('#shipping-address #' + field).after('<span class="error">' + errorMessage + '</span>');							
						}					
					} else {
						var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=shipping_method&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>&Itemid=<?php echo $Itemid; ?>',
							dataType: 'html',
							success: function(html) {
								$('#shipping-method .checkout-content').html(html);
								$('#shipping-address .checkout-content').slideUp('slow');
								$('#shipping-method .checkout-content').slideDown('slow');
								$('#shipping-address .checkout-heading a').remove();
								$('#shipping-method .checkout-heading a').remove();
								$('#payment-method .checkout-heading a').remove();
								$('#shipping-address .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
								$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
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

		$('#shipping-address input[name=\'shipping_address\']').click(function(){
			if (this.value == 'new') {
				$('#shipping-existing').hide();
				$('#shipping-new').show();
			} else {
				$('#shipping-existing').show();
				$('#shipping-new').hide();
			}
		});
		$('#shipping-address select[name=\'country_id\']').change(function(){
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				url: siteUrl + 'index.php?option=com_eshop&task=cart.getZones<?php echo EShopHelper::getAttachedLangLink(); ?>&country_id=' + this.value,
				dataType: 'json',
				beforeSend: function() {
					$('.wait').remove();
					$('#shipping-address select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
				},
				complete: function() {
					$('.wait').remove();
				},
				success: function(json) {
					if (json['postcode_required'] == '1')
					{
						$('#shipping-postcode-required').show();
					}
					else
					{
						$('#shipping-postcode-required').hide();
					}
					html = '<option value=""><?php echo Text::_('ESHOP_PLEASE_SELECT'); ?></option>';
					if (json['zones'] != '')
					{
						for (var i = 0; i < json['zones'].length; i++)
						{
		        			html += '<option value="' + json['zones'][i]['id'] + '"';
							if (json['zones'][i]['id'] == '<?php $this->shipping_zone_id; ?>')
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
		
	})
//--></script>