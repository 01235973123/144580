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

if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'payment_method_step')
{
    if (EShopHelper::getConfigValue('show_privacy_policy_checkbox') || (isset($this->checkoutTermsLink) && $this->checkoutTermsLink != ''))
    {
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
        <?php
    }
}

if (isset($this->methods))
{
	?>
	<div>
		<p><?php echo Text::_('ESHOP_PAYMENT_METHOD_TITLE'); ?></p>
		<?php
		for ($i = 0 , $n = count($this->methods); $i < $n; $i++)
		{
			$checked = '';
			$paymentMethod = $this->methods[$i];
			if ($this->paymentMethod != '')
			{
				if ($paymentMethod->getName() == $this->paymentMethod)
				{
					$checked = ' checked="checked" ';
				}	
			}
			else
			{
				if ($i == 0)
				{
					$checked = ' checked="checked" ';
				}
			}
			?>
			<label class="radio">
				<input type="radio" class="form-check-input" name="payment_method" value="<?php echo $paymentMethod->getName(); ?>" <?php echo $checked; ?> />
					<?php
					if ($paymentMethod->iconUri != '')
					{
						?>
						<img alt="<?php echo $paymentMethod->title; ?>" src="<?php echo $paymentMethod->iconUri; ?>" />
						<?php
					}
					else 
					{
						echo Text::_($paymentMethod->title);
					}
					?>
				<br />
			</label>
			<?php
		}
		?>
	</div>
	<?php
}
else
{
	?>
	<div class="no-payment-method"><?php echo Text::_('ESHOP_NO_PAYMENT_METHOD_AVAILABLE'); ?></div>
	<?php
}
if (EShopHelper::getConfigValue('enable_checkout_donate'))
{
	?>
	<br />
	<div class="<?php echo $controlGroupClass; ?>">
		<p><?php echo Text::_('ESHOP_CHECKOUT_DONATE_INTRO'); ?></p>
		<?php
		if (EShopHelper::getConfigValue('donate_amounts') != '')
		{
			$donateAmounts = explode("\n", EShopHelper::getConfigValue('donate_amounts'));
			$donateExplanations = explode("\n", EShopHelper::getConfigValue('donate_explanations'));
			for ($i = 0 , $n = count($donateAmounts); $i < $n; $i++)
			{
				?>
				<label class="<?php echo $controlLabelClass; ?>">
					<?php
					if ($donateAmounts[$i] > 0)
					{
						?>
						<input type="radio" class="form-check-input" name="donate_amount" value="<?php echo trim($donateAmounts[$i]); ?>" /> <?php echo $this->currency->format(trim($donateAmounts[$i])) . (isset($donateExplanations[$i]) && $donateExplanations[$i] != '' ? ' (' . trim($donateExplanations[$i]) . ')' : ''); ?><br />
						<?php
					}
					else 
					{
						?>
						<input type="radio" class="form-check-input" checked="checked" name="donate_amount" value="<?php echo trim($donateAmounts[$i]); ?>" /> <?php echo (isset($donateExplanations[$i]) && $donateExplanations[$i] != '' ? trim($donateExplanations[$i]) : ''); ?><br />
						<?php
					}
					?>
				</label>
				<?php
			}
			?>
				<label class="<?php echo $controlLabelClass; ?>">
					<input type="radio" class="form-check-input" name="donate_amount" value="other_amount" /><?php echo Text::_('ESHOP_DONATE_OTHER_AMOUNT'); ?><br />
				</label>
				<input type="text" class="input-xlarge form-control" name="other_amount" id="other_amount" class="input-small" />
			<?php
		}
		else 
		{
			?>
			<label for="other_amount" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_DONATE_AMOUNT'); ?></label>
			<div class="<?php echo $controlsClass; ?>">
				<input type="text" class="input-xlarge form-control" name="other_amount" id="other_amount" class="input-small" />
			</div>	
			<?php
		}
		?>
	</div>
	<?php
}
if (EShopHelper::getConfigValue('allow_coupon'))
{
	?>
	<br />
	<div class="<?php echo $controlGroupClass; ?>">
		<label for="coupon_code" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_COUPON_TEXT'); ?></label>
		<div class="<?php echo $controlsClass; ?>">
			<input type="text" id="coupon_code" name="coupon_code" class="input-xlarge form-control" value="<?php echo EShopHelper::escape($this->coupon_code); ?>">
		</div>
	</div>
	<?php
}
if (EShopHelper::getConfigValue('allow_voucher'))
{
	?>
	<div class="<?php echo $controlGroupClass; ?>">
		<label for="voucher_code" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_VOUCHER_TEXT'); ?></label>
		<div class="<?php echo $controlsClass; ?>">
			<input type="text" id="voucher_code" name="voucher_code" class="input-xlarge form-control" value="<?php echo EShopHelper::escape($this->voucher_code); ?>">
		</div>
	</div>
	<?php
}

$displayComment = EShopHelper::getConfigValue('display_comment', '45');

if ( $displayComment== '5' || $displayComment == '45')
{
    ?>
    <br />
    <div class="<?php echo $controlGroupClass; ?>">
    	<label for="comment" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_COMMENT_ORDER'); ?></label>
    	<div class="<?php echo $controlsClass; ?>">
    		<textarea rows="8" id="comment" class="input-xlarge form-control" name="comment"><?php echo $this->comment; ?></textarea>
    	</div>
    </div>
    <?php
}

if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'payment_method_step')
{
    if (EShopHelper::getConfigValue('show_privacy_policy_checkbox'))
    {
        ?>
        <div class="<?php echo $controlGroupClass; ?> eshop-privacy-policy">
        	<div class="<?php echo $controlLabelClass; ?>">
            	<?php
            	if (isset($this->privacyPolicyArticleLink) && $this->privacyPolicyArticleLink != '')
            	{
            	    ?>
            	    <a class="colorbox cboxElement" href="<?php echo $this->privacyPolicyArticleLink; ?>"><?php echo Text::_('ESHOP_PRIVACY_POLICY'); ?></a>
            	    <?php
            	}
            	else 
            	{
            	    echo Text::_('ESHOP_PRIVACY_POLICY');
            	}
            	?>
        	</div>
        	<div class="<?php echo $controlsClass; ?>">
        		<input type="checkbox" class="form-check-input" name="privacy_policy_agree" value="1" />
    			<?php
    			$agreePrivacyPolicyMessage = Text::_('ESHOP_AGREE_PRIVACY_POLICY_MESSAGE');
    
    			if (strlen($agreePrivacyPolicyMessage))
    			{
    			?>
                    <div class="eshop-agree-privacy-policy-message alert alert-info"><?php echo $agreePrivacyPolicyMessage;?></div>
    			<?php
    			}
    			?>
        	</div>
        </div>
        <?php
    }
    
    if (EShopHelper::getConfigValue('acymailing_integration') || EShopHelper::getConfigValue('mailchimp_integration'))
    {
        ?>
        <div class="<?php echo $controlGroupClass; ?> eshop-newsletter-interest">
        	<label for="textarea" class="<?php echo $controlLabelClass; ?>">
        		<input type="checkbox" class="form-check-input" value="1" name="newsletter_interest" /><?php echo Text::_('ESHOP_NEWSLETTER_INTEREST'); ?>
        	</label>
        </div>
        <?php
    }
    
    if (isset($this->checkoutTermsLink) && $this->checkoutTermsLink != '')
    {
        ?>
        <div class="<?php echo $controlGroupClass; ?> eshop-checkout-terms">
        	<label for="textarea" class="<?php echo $controlLabelClass; ?>">
        		<input type="checkbox" class="form-check-input" value="1" name="checkout_terms_agree" <?php echo $this->checkout_terms_agree ?: ''; ?>/>
    			<?php echo Text::_('ESHOP_CHECKOUT_TERMS_AGREE'); ?>&nbsp;<a class="colorbox cboxElement" href="<?php echo $this->checkoutTermsLink; ?>"><?php echo Text::_('ESHOP_CHECKOUT_TERMS_AGREE_TITLE'); ?></a>
        	</label>
        </div>
        <?php
    }
}
?>
<div class="no_margin_left">
	<input type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullRightClass; ?>" id="button-payment-method" value="<?php echo Text::_('ESHOP_CONTINUE'); ?>" />
</div>
<script type="text/javascript">
	Eshop.jQuery(function($){
		// Payment Method
		$('#button-payment-method').click(function(){
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				url: siteUrl + 'index.php?option=com_eshop&task=checkout.processPaymentMethod<?php echo EShopHelper::getAttachedLangLink(); ?>',
				type: 'post',
				data: $('#payment-method input[type=\'radio\']:checked, #payment-method input[type=\'checkbox\']:checked, #payment-method input[type=\'text\'],  #payment-method textarea'),
				dataType: 'json',
				beforeSend: function() {
					$('#button-payment-method').attr('disabled', true);
					$('#button-payment-method').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
				},	
				complete: function() {
					$('#button-payment-method').attr('disabled', false);
					$('.wait').remove();
				},			
				success: function(json) {
					$('.warning, .error').remove();
					
					if (json['return']) {
						window.location.href = json['return'];
					} else if (json['error']) {
						if (json['error']['warning']) {
							$('#payment-method .checkout-content').prepend('<div class="warning" style="display: none;">' + json['error']['warning'] + '</div>');
							$('.warning').fadeIn('slow');
						}
					} else {
						var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=confirm&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
							dataType: 'html',
							success: function(html) {
								$('#confirm .checkout-content').html(html);
								
								if (json['payment_method'] == 'os_squareup' || json['payment_method'] == 'os_squarecard')
								{
									$('#confirm .eshop-squareup-information').css('display', '');
									const form = document.getElementById('payment_method_form');
									<?php
                                    $session = Factory::getApplication()->getSession();
                                    $user    = Factory::getUser();
									
									if ($user->get('id') && $session->get('payment_address_id'))
									{
									    $paymentAddress = EShopHelper::getAddress($session->get('payment_address_id'));
									}
									else
									{
									    $guest          = $session->get('guest');
									    $paymentAddress = $guest['payment'] ?? '';
									}
									?>
									form.firstname.value = '<?php echo isset($paymentAddress['firstname']) ? addslashes($paymentAddress['firstname']) : ''; ?>';
									form.lastname.value = '<?php echo isset($paymentAddress['lastname']) ? addslashes($paymentAddress['lastname']) : ''; ?>';
									form.email.value = '<?php echo $paymentAddress['email'] ?? ''; ?>';
									form.phone.value = '<?php echo $paymentAddress['telephone'] ?? ''; ?>';
									form.address.value = '<?php echo isset($paymentAddress['address_1']) ? addslashes($paymentAddress['address_1']) : ''; ?>';
									form.address2.value = '<?php echo isset($paymentAddress['address_2']) ? addslashes($paymentAddress['address_2']) : ''; ?>';
									form.city.value = '<?php echo isset($paymentAddress['city']) ? addslashes($paymentAddress['city']) : ''; ?>';
									form.amount.value = json['total'];
								}
								else
								{
									$('#confirm .eshop-squareup-information').css('display', 'none');
								}
								
								$('#payment-method .checkout-content').slideUp('slow');
								$('#confirm .checkout-content').slideDown('slow');
								$('#payment-method .checkout-heading a').remove();
								$('#payment-method .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
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
	})
</script>