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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$bootstrapHelper        = $this->bootstrapHelper;
$rowFuildClass          = $bootstrapHelper->getClassMapping('row-fluid');
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass      = $bootstrapHelper->getClassMapping('control-label');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$pullRightClass         = $bootstrapHelper->getClassMapping('pull-right');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');
$rootUri           		= Uri::root(true);

$Itemid = Factory::getApplication()->input->getInt('Itemid', 0);

if (!$Itemid)
{
    $Itemid = EShopRoute::getDefaultItemId();
}

$session = Factory::getApplication()->getSession();

if ($session->get('currency_code') != '')
{
	$currencyCode = $session->get('currency_code');
}
elseif ($cookieCurrencyCode = Factory::getApplication()->input->cookie->getString('currency_code', ''))
{
	$currencyCode = $cookieCurrencyCode;
}
else
{
	$currencyCode = EShopHelper::getConfigValue('default_currency_code');
}

if (!EShopHelper::isJoomla4())
{
    HTMLHelper::_('behavior.framework');
}
?>
<div class="page-header">
	<h1 class="page-title eshop-title">
    	<?php echo Text::_('ESHOP_CHECKOUT'); ?>
    	<?php
    	if ($this->weight)
    	{
    		echo '&nbsp;(' . $this->weight . ')';
    	}
    	?>
	</h1>
</div>	
<div class="<?php echo $rowFuildClass; ?>">
    <div id="checkout-options">
    	<div class="checkout-heading"><?php echo Text::_('ESHOP_CHECKOUT_STEP_1'); ?></div>
    	<div class="checkout-content"></div>
    </div>
    <div id="payment-address">
    	<div class="checkout-heading">
    		<?php
    		if (EShopHelper::getCheckoutType() == 'guest_only')
    		{
    			echo Text::_('ESHOP_CHECKOUT_STEP_2_GUEST');
    		}
    		else 
    		{
    			echo Text::_('ESHOP_CHECKOUT_STEP_2_REGISTER');
    		}
    		?>
    	</div>
    	<div class="checkout-content"></div>
    </div>
    <?php
    if ($this->shipping_required)
    {
    	if (EShopHelper::getConfigValue('require_shipping_address', 1))
    	{
    		?>
    		<div id="shipping-address">
    			<div class="checkout-heading"><?php echo Text::_('ESHOP_CHECKOUT_STEP_3'); ?></div>
    			<div class="checkout-content"></div>
    		</div>
    		<?php
    	}
    	?>
    	<div id="shipping-method">
    		<div class="checkout-heading"><?php echo Text::_('ESHOP_CHECKOUT_STEP_4'); ?></div>
    		<div class="checkout-content form-horizontal"></div>
    	</div>
    	<?php
    }
    ?>
    <div id="payment-method">
    	<div class="checkout-heading"><?php echo Text::_('ESHOP_CHECKOUT_STEP_5'); ?></div>
    	<div class="checkout-content form-horizontal"></div>
    </div>
    <div id="confirm">
    	<div class="checkout-heading"><?php echo Text::_('ESHOP_CHECKOUT_STEP_6'); ?></div>
    	<div class="checkout-content"></div>
    		<?php
    		$paymentMethods = os_payments::getPaymentMethods();
    		foreach ($paymentMethods as $paymentMethod)
    		{
    		
    			$params = $paymentMethod->getParams();
    			$applicationId = $params->get('application_id');
    			
    			if ($paymentMethod->getName() == 'os_squareup' || $paymentMethod->getName() == 'os_squarecard')
    			{
    				$currentYear = date('Y');
    				?>
    				<div class="eshop-squareup-information" style="display: none;">
    					<script type="text/javascript">
    			            Eshop.jQuery(document).ready(function($){
    			        		// Confirm button
    			        		$('#squareup-button-confirm').click(function() {
    			        			$('#squareup-button-confirm').attr('disabled', true);
									$('#squareup-button-confirm').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
    			        			form = document.getElementById('payment_method_form');
    		            			<?php
    		            			if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'confirm_step')
    		            			{
    		            			    if (EShopHelper::getConfigValue('show_privacy_policy_checkbox'))
    		            			    {
    		            			        ?>
    										if (!form.privacy_policy_agree.checked)
    										{
    											alert("<?php echo Text::_('ESHOP_AGREE_PRIVACY_POLICY_ERROR'); ?>");
    											form.privacy_policy_agree.focus();
    											return false;
    										}
    			                            <?php
    			                        }
    			
    			                        if (EShopHelper::getConfigValue('checkout_terms'))
    			                        {
    			                            ?>
    										if (!form.checkout_terms_agree.checked)
    										{
    											alert("<?php echo Text::_('ESHOP_ERROR_CHECKOUT_TERMS_AGREE'); ?>");
    											form.checkout_terms_agree.focus();
    											return false;
    										}
    			                            <?php
    			                        }
    			                    }
    		            			
    								if ($this->showCaptcha && EShopHelper::getConfigValue('enable_checkout_captcha'))
    								{
							            if (in_array($this->captchaPlugin, ['recaptcha', 'recaptcha_invisible']))
    									{
    										?>
    										var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
    										jQuery.ajax({
    				            				url: siteUrl + 'index.php?option=com_eshop&task=checkout.validateCaptcha',
    				            				type: 'post',
    				            				dataType: 'json',
    											data: jQuery('#payment_method_form').serialize(),
    				            				beforeSend: function() {
    					            				// Do nothing					            					
    				            				},
    				            				complete: function() {
    				            					$('#squareup-button-confirm').attr('disabled', false);
    				            					$('.wait').remove();
    				            				},
    				            				success: function(data) {
    				            					if (data['error']) {
    				            						alert(data['error']);
    				            					}
    				            					if (data['success']) {
        				            					<?php
        				            					if ($paymentMethod->getName() == 'os_squareup')
        				            					{
                                                            ?>
                                                            sqPaymentForm.requestCardNonce();
                                                            <?php  
        				            					}
        				            					elseif ($paymentMethod->getName() == 'os_squarecard')
        				            					{
        				            					    ?>
        				            					    
        				            					    <?php
        				            					}
        				            					?>
    				            						
    				            					}
    				            				}
    			            				});
    			            				<?php
    									}
    									else 
    									{
    			                            if ($paymentMethod->getName() == 'os_squareup')
			            					{
                                                ?>
                                                sqPaymentForm.requestCardNonce();
                                                <?php  
			            					}
			            					elseif ($paymentMethod->getName() == 'os_squarecard')
			            					{
			            					    ?>
			            					    squareCardCallBackHandle();
			            					    <?php
			            					}
    									}
    								}
    								else 
    								{
			                            if ($paymentMethod->getName() == 'os_squareup')
		            					{
                                            ?>
                                            sqPaymentForm.requestCardNonce();
                                            <?php  
		            					}
		            					elseif ($paymentMethod->getName() == 'os_squarecard')
		            					{
		            					    ?>
		            					    squareCardCallBackHandle();
		            					    <?php
		            					}
    								}
    			            		?>
    			        		})
    			            })
    			        </script>
    			        <form action="<?php echo EShopHelper::getSiteUrl(); ?>index.php?option=com_eshop&task=checkout.processOrder&Itemid=<?php echo $Itemid; ?>" method="post" name="payment_method_form" id="payment_method_form" class="form form-horizontal">
			            	<?php
    			            if ($paymentMethod->getName() == 'os_squareup')
    			            {
    			                ?>
    			                <div class="<?php echo $controlGroupClass; ?>">
    	                            <div class="<?php echo $controlLabelClass; ?>">
    	                                <?php echo  Text::_('ESHOP_SQUAREUP_ZIPCODE'); ?><span class="required">*</span>
    	                            </div>
    	                            <div class="<?php echo $controlsClass; ?>" id="field_zip_input"></div>
    	                        </div>
    	                        <div class="<?php echo $controlGroupClass; ?>">
    	                            <div class="<?php echo $controlLabelClass; ?>">
    	                                <?php echo  Text::_('ESHOP_CARD_NUMBER'); ?><span class="required">*</span>
    	                            </div>
    	                            <div class="<?php echo $controlsClass; ?>" id="sq-card-number"></div>
    	                        </div>
    	                        <div class="<?php echo $controlGroupClass; ?>">
    	                            <div class="<?php echo $controlLabelClass; ?>">
    	                                <?php echo  Text::_('ESHOP_CARD_EXPIRY_DATE'); ?><span class="required">*</span>
    	                            </div>
    	                            <div class="<?php echo $controlsClass; ?>" id="sq-expiration-date"></div>
    	                        </div>
    	                        <div class="<?php echo $controlGroupClass; ?>">
    	                            <label class="<?php echo $controlLabelClass; ?>" for="cvv_code">
    	                                <?php echo Text::_('ESHOP_CVV_CODE'); ?><span class="required">*</span>
    	                            </label>
    	                            <div class="<?php echo $controlsClass; ?>" id="sq-cvv"></div>
    	                        </div>
    			                <?php
    			            }
    			            elseif ($paymentMethod->getName() == 'os_squarecard')
    			            {
    			                ?>
    			                <div class="<?php echo $controlGroupClass; ?> payment_information" id="square-card-form">
                                    <div class="<?php echo $controlLabelClass; ?>">
                            			<?php echo Text::_('ESHOP_CREDIT_OR_DEBIT_CARD'); ?><span class="required">*</span>
                                    </div>
                                    <div class="<?php echo $controlsClass; ?>" id="square-card-element">
                            
                                    </div>
                                </div>
                                <input type="hidden" name="square_card_token" value="" />
                                <input type="hidden" name="square_card_verification_token" value="" />
                                <input type="hidden" name="firstname" value="" />
                                <input type="hidden" name="lastname" value="" />
                                <input type="hidden" name="email" value="" />
                                <input type="hidden" name="phone" value="" />
                                <input type="hidden" name="address" value="" />
                                <input type="hidden" name="address2" value="" />
                                <input type="hidden" name="city" value="" />
                                <input type="hidden" name="amount" value="" />
                                <input type="hidden" name="currencyCode" value="<?php echo $currencyCode; ?>" />
    			                <?php
    			            }
    			            
                			if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'confirm_step')
    	                    {
    	                        $checkoutTermsLink = '';
    	                        $checkoutTerms     = EShopHelper::getConfigValue('checkout_terms');
    	                        
    	                        if ($checkoutTerms > 0)
    	                        {
    	                            $checkoutTermsLink = Route::_(EShopHelper::getArticleUrl($checkoutTerms));
    	                        }
    	                        
    	                        if (EShopHelper::getConfigValue('show_privacy_policy_checkbox'))
    	                        {
    	                            $privacyPolicyArticleLink  = '';
    	                            $privacyPolicyArticle      = EShopHelper::getConfigValue('privacy_policy_article');
    	                            
    	                            if ($privacyPolicyArticle > 0)
    	                            {
    	                                $privacyPolicyArticleLink = Route::_(EShopHelper::getArticleUrl($privacyPolicyArticle));
    	                            }
    	                            ?>
                                    <div class="<?php echo $controlGroupClass; ?> eshop-privacy-policy">
                                    	<div class="<?php echo $controlLabelClass; ?>">
                                        	<?php
                                        	if ($privacyPolicyArticleLink != '')
                                        	{
                                        	    ?>
                                        	    <a class="colorbox cboxElement" href="<?php echo $privacyPolicyArticleLink; ?>"><?php echo Text::_('ESHOP_PRIVACY_POLICY'); ?></a>
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
                                    	<label for="newsletter_interest" class="<?php echo $controlLabelClass; ?>">
                                    		<input type="checkbox" class="form-check-input" value="1" id="newsletter_interest" name="newsletter_interest" /><?php echo Text::_('ESHOP_NEWSLETTER_INTEREST'); ?>
                                    	</label>
                                    </div>
                                    <?php
                                }
                                
                                if ($checkoutTermsLink != '')
                                {
                                    ?>
                                    <div class="<?php echo $controlGroupClass; ?> eshop-checkout-terms">
                                    	<label for="textarea" class="<?php echo $controlLabelClass; ?>">
                                    		<input type="checkbox" class="form-check-input" value="1" name="checkout_terms_agree" <?php echo $this->checkout_terms_agree ?: ''; ?>/>
                                			<?php echo Text::_('ESHOP_CHECKOUT_TERMS_AGREE'); ?>&nbsp;<a class="colorbox cboxElement" href="<?php echo $checkoutTermsLink; ?>"><?php echo Text::_('ESHOP_CHECKOUT_TERMS_AGREE_TITLE'); ?></a>
                                    	</label>
                                    </div>
                                    <?php
                                }
                            }
	                        
	                        if ($this->showCaptcha && EShopHelper::getConfigValue('enable_checkout_captcha'))
	                        {
                        	    if (in_array($this->captchaPlugin, ['recaptcha_invisible', 'recaptcha_v3']))
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
                    					<?php echo $this->captcha; ?>
                    				</div>
                    			</div>
								<?php
							}
	                        ?>
			                <div class="no_margin_left">
			                	<input id="squareup-button-confirm" type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullRightClass; ?>" value="<?php echo Text::_('ESHOP_CONFIRM_ORDER'); ?>" />
			                </div>
    			            <input type="hidden" id="card-nonce" name="nonce" />
    			            <?php echo HTMLHelper::_('form.token'); ?>
    			        </form>
    				</div>
    				<?php
    			}
    		}
    		?>
    </div>
</div> 
<script type="text/javascript">
	Eshop.jQuery(function($){
		if (Joomla.getOptions('squareAppId')) {
            createSquareCardElement();
        }
		//Script to allow Edit step
		$('.checkout-heading').on('click', 'a', function() {
			$('.checkout-content').slideUp('slow');
			if ($('#confirm .eshop-squareup-information').length)
			{
				$('#confirm .eshop-squareup-information').css('display', 'none');
			}
			$(this).parent().parent().find('.checkout-content').slideDown('slow');
		});
		
		//If user is not logged in, then show login layout
		<?php
		if (!$this->user->get('id'))
		{
			if (EShopHelper::getConfigValue('checkout_type') == 'guest_only')
			{
				?>
				$(document).ready(function() {
					var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
					$.ajax({
						url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=guest&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
						dataType: 'html',
						success: function(html) {
							$('#payment-address .checkout-content').html(html);
							$('#payment-address .checkout-content').slideDown('slow');
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				});
				<?php
			}
			else 
			{
				?>
				$(document).ready(function() {
					var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
					$.ajax({
						url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=login&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
						dataType: 'html',
						success: function(html) {
							$('#checkout-options .checkout-content').html(html);
							$('#checkout-options .checkout-content').slideDown('slow');
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				});
				<?php
			}
		}
		//Else, show payment address layout
		else
		{
			?>
			$('#payment-address .checkout-heading').html('<?php echo Text::_('ESHOP_CHECKOUT_STEP_2_GUEST'); ?>');
			$(document).ready(function() {
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=payment_address&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
					dataType: 'html',
					success: function(html) {
						$('#payment-address .checkout-content').html(html);
						$('#payment-address .checkout-content').slideDown('slow');
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