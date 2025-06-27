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

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

$bootstrapHelper        = $this->bootstrapHelper;
$rowFluidClass          = $bootstrapHelper->getClassMapping('row-fluid');
$span6Class             = $bootstrapHelper->getClassMapping('span6');
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass      = $bootstrapHelper->getClassMapping('control-label');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$pullRightClass         = $bootstrapHelper->getClassMapping('pull-right');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

$rootUri = Uri::root(true);
$Itemid  = Factory::getApplication()->input->getInt('Itemid', 0);

if (!$Itemid)
{
    $Itemid = EShopRoute::getDefaultItemId();
}
?>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/colorbox/jquery.colorbox.js" type="text/javascript"></script>
<script type="text/javascript">
	Eshop.jQuery(document).ready(function($){
		$(".colorbox").colorbox({
			overlayClose: true,
			opacity: 0.5,
			width: '90%',
			maxWidth: '900px',
		});
	});
</script>
<div class="<?php echo $rowFluidClass; ?> clearfix">
	<div class="<?php echo $span6Class; ?> no_margin_left">
		<legend><?php echo Text::_('ESHOP_YOUR_PERSONAL_DETAILS'); ?></legend>
		<?php 
			$personalFields = array(
				'firstname',
				'lastname',
				'email',
				'telephone',
				'fax'
			);
			$fields = $this->form->getFields();
			foreach ($fields as $field)
			{
				if (in_array($field->name, $personalFields))
				{
					echo $field->getControlGroup();
				}
			}
		?>
		<legend><?php echo Text::_('ESHOP_USER_DETAILS'); ?></legend>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="username"><span class="required">*</span><?php echo Text::_('ESHOP_USERNAME');?></label>
			<div class="<?php echo $controlsClass; ?>  docs-input-sizes">
				<input type="text" id="username" name="username" class="input-xlarge form-control" />
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="password1"><span class="required">*</span><?php echo Text::_('ESHOP_PASSWORD'); ?></label>
			<div class="<?php echo $controlsClass; ?>  docs-input-sizes">
				<input type="password" id="password1" name="password1" class="input-xlarge form-control" />
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="password2"><span class="required">*</span><?php echo Text::_('ESHOP_CONFIRM_PASSWORD'); ?></label>
			<div class="<?php echo $controlsClass; ?>  docs-input-sizes">
				<input type="password" id="password2" name="password2" class="input-xlarge form-control" />
			</div>
		</div>
		<?php
		if (EShopHelper::getConfigValue('enable_register_account_captcha'))
		{
		    $captchaPlugin = Factory::getApplication()->get('captcha') ?: 'recaptcha';
		    $plugin = PluginHelper::getPlugin('captcha', $captchaPlugin);
		    
		    if ($plugin)
		    {
				if (in_array($captchaPlugin, ['recaptcha_invisible', 'recaptcha_v3']))
	            {
	                $style = ' style="display:none;"';
	            }
	            else
	            {
	                $style = '';
	            }
          		?>
          		<div class="<?php echo $controlGroupClass; ?>">
					<div class="<?php echo $controlLabelClass; ?>"<?php echo $style; ?>>
            				<?php echo Text::_('ESHOP_CAPTCHA'); ?>
						<span class="required">*</span>
					</div>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo Captcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required'); ?>
					</div>
				</div>
    			<?php 
			}
		}
		?>
	</div>
	<div class="<?php echo $span6Class; ?>">
		<legend><?php echo Text::_('ESHOP_YOUR_ADDRESS'); ?></legend>
		<?php
		if (isset($this->lists['customergroup_id']))
		{
		?>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>" for="customergroup_id"><?php echo Text::_('ESHOP_CUSTOMER_GROUP'); ?></label>
				<div class="<?php echo $controlsClass; ?>  docs-input-sizes">
					<?php echo $this->lists['customergroup_id']; ?>
				</div>
			</div>
		<?php
		}
		elseif (isset($this->lists['default_customergroup_id']))
		{
			?>
			<input type="hidden" name="customergroup_id" value="<?php echo $this->lists['default_customergroup_id']; ?>" />
			<?php
		}
		foreach ($fields as $field)
		{
			if (!in_array($field->name, $personalFields))
			{
				echo $field->getControlGroup();
			}
		}
		?>		
	</div>
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
?>
<div class="no_margin_left">
	<?php
	if (isset($this->accountTermsLink) && $this->accountTermsLink != '')
	{
		?>
        <span class="privacy">
			<input type="checkbox" class="form-check-input" value="1" name="account_terms_agree" />
			&nbsp;<?php echo Text::_('ESHOP_ACCOUNT_TERMS_AGREE'); ?>&nbsp;<a class="colorbox cboxElement" href="<?php echo $this->accountTermsLink; ?>"><?php echo Text::_('ESHOP_ACCOUNT_TERMS_AGREE_TITLE'); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </span>
		<?php
	}
	?>
	<input type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullRightClass; ?>" id="button-register" value="<?php echo Text::_('ESHOP_CONTINUE'); ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</div>	
<script type="text/javascript">
	<?php
    if (EShopHelper::getConfigValue('enable_register_account_captcha'))
	{
		$captchaPlugin = Factory::getApplication()->get('captcha') ?: 'recaptcha';
		$plugin = PluginHelper::getPlugin('captcha', $captchaPlugin);
		
		if ($plugin && ($captchaPlugin == 'recaptcha' || $captchaPlugin == 'recaptcha_invisible'))
		{
			?>
    		(function($) {
    			$(document).ready(function() {
	    			<?php 
	      			if ($captchaPlugin == 'recaptcha')
					{
						?>
						EShopInitReCaptcha2();
						<?php
	    			}
					else 
					{
						?>
						EShopInitReCaptchaInvisible();
						<?php
	    			}
	      			?>
    			})
    		})(jQuery);
    	<?php 
		}
	}
	?>
	//Register
	Eshop.jQuery(function($){
		$('#button-register').click(function(){
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				url: siteUrl + 'index.php?option=com_eshop&task=checkout.register<?php echo EShopHelper::getAttachedLangLink(); ?>',
				type: 'post',
				data: $('#payment-address input[type=\'text\'], #payment-address input[type=\'password\'], #payment-address input[type=\'checkbox\']:checked, #payment-address input[type=\'radio\']:checked, #payment-address input[type=\'hidden\'], #payment-address select, #payment-address textarea'),
				dataType: 'json',
				beforeSend: function() {
					$('#button-register').attr('disabled', true);
					$('#button-register').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
				},
				complete: function() {
					$('#button-register').attr('disabled', false); 
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
							var errorMessage = errors[field];
							$('#payment-address #' + field)
							$('#payment-address #' + field).after('<span class="error">' + errorMessage + '</span>');
						}

						if (json['error']['captcha']) {
							$('#payment-address #dynamic_recaptcha_1').after('<span class="error">' + json['error']['captcha'] + '</span>');
						}
					} else {
						<?php
						//If shipping required, then we must considering Step 3: Delivery Details and Step 4: Delivery Method
						if ($this->shipping_required)
						{
							if (EShopHelper::getConfigValue('require_shipping_address' , 1))
							{
								?>
								var shipping_address = $('#payment-address input[name=\'shipping_address\']:checked').attr('value');
								//If shipping address is same as billing address, then ignore Step 3: Delivery Details, go to Step 4: Delivery Method
								if (shipping_address) {
									var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
									$.ajax({
										url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=shipping_method&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>&Itemid=<?php echo $Itemid; ?>',
										dataType: 'html',
										success: function(html) {
											$('#shipping-method .checkout-content').html(html);
											$('#payment-address .checkout-content').slideUp('slow');
											$('#shipping-method .checkout-content').slideDown('slow');
											$('#checkout-options .checkout-heading a').remove();
											$('#payment-address .checkout-heading a').remove();
											$('#shipping-address .checkout-heading a').remove();
											$('#shipping-method .checkout-heading a').remove();
											$('#payment-method .checkout-heading a').remove();
											$('#shipping-address .checkout-heading').append('<a><?php echo Jtext::_('ESHOP_EDIT'); ?></a>');
											$('#payment-address .checkout-heading').append('<a><?php echo Jtext::_('ESHOP_EDIT'); ?></a>');
											$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
											//Update shipping address for Step 3: Delivery Details
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
								} else {
									//Else, show Step 3: Delivery Details
									var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
									$.ajax({
										url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=shipping_address&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
										dataType: 'html',
										success: function(html) {
											$('#shipping-address .checkout-content').html(html);
											$('#payment-address .checkout-content').slideUp('slow');
											$('#shipping-address .checkout-content').slideDown('slow');
											$('#checkout-options .checkout-heading a').remove();
											$('#payment-address .checkout-heading a').remove();
											$('#shipping-address .checkout-heading a').remove();
											$('#shipping-method .checkout-heading a').remove();
											$('#payment-method .checkout-heading a').remove();
											$('#payment-address .checkout-heading').append('<a><?php echo Jtext::_('ESHOP_EDIT'); ?></a>');
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
								var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
								$.ajax({
									url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=shipping_method&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>&Itemid=<?php echo $Itemid; ?>',
									dataType: 'html',
									success: function(html) {
										$('#shipping-method .checkout-content').html(html);
										$('#payment-address .checkout-content').slideUp('slow');
										$('#shipping-method .checkout-content').slideDown('slow');
										$('#checkout-options .checkout-heading a').remove();
										$('#payment-address .checkout-heading a').remove();
										$('#shipping-method .checkout-heading a').remove();
										$('#payment-method .checkout-heading a').remove();
										$('#payment-address .checkout-heading').append('<a><?php echo Jtext::_('ESHOP_EDIT'); ?></a>');
										$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
									},
									error: function(xhr, ajaxOptions, thrownError) {
										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
									}
								});
								<?php
							}
							?>
						<?php
						}
						else
						{
						//Else, we go to Step 5: Payment Method
						?>
						var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=payment_method&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>&Itemid=<?php echo $Itemid; ?>',
							dataType: 'html',
							success: function(html) {
								$('#payment-method .checkout-content').html(html);
								$('#payment-address .checkout-content').slideUp('slow');
								$('#payment-method .checkout-content').slideDown('slow');
								$('#checkout-options .checkout-heading a').remove();
								$('#payment-address .checkout-heading a').remove();
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
						?>
						//Finally, we must update payment address
						var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=payment_address&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
							dataType: 'html',
							success: function(html) {
								$('#payment-address .checkout-content').html(html);
								$('#payment-address .checkout-heading span').html('<?php echo Text::_('ESHOP_CHECKOUT_STEP_2_REGISTER'); ?>');
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
</script>