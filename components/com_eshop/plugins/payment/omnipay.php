<?php
/**
 * Part of the Ossolution Payment Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Ossolution Team. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Message\AbstractRequest;
use Ossolution\Payment\OmnipayPayment;

require_once JPATH_LIBRARIES . '/omnipay3/vendor/autoload.php';

/**
 * Payment class which use Omnipay payment class for processing payment
 *
 * @since 1.0
 */
class EShopOmnipayPayment extends OmnipayPayment
{

	/**
	 * Method to check whether we need to show card type on form for this payment method.
	 * Always return false as when use Omnipay, we don't need card type parameter. It can be detected automatically
	 * from given card number
	 *
	 * @return bool|int
	 */
	public function getCardType()
	{
		return 0;
	}

	/**
	 * Method to check whether we need to show card holder name in the form
	 *
	 * @return bool|int
	 */
	public function getCardHolderName()
	{
		return $this->type;
	}

	/**
	 * Method to check whether we need to show card cvv input on form
	 *
	 * @return bool|int
	 */
	public function getCardCvv()
	{
		return $this->type;
	}

	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to after a successful payment. The url is stored in paymentSuccessUrl property
	 *
	 * @param   Table  $row
	 * @param   array  $data
	 *
	 * @return void
	 */
	protected function setPaymentSuccessUrl($id, $data = [])
	{
		$this->paymentSuccessUrl = Route::_(EShopRoute::getViewRoute('checkout') . '&layout=complete');
	}


	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to when the payment is not success for some reasons. The url is stored in paymentFailureUrl property
	 *
	 * @param   int    $id
	 * @param   array  $data
	 *
	 * @return void
	 */
	protected function setPaymentFailureUrl($id, $data = [])
	{
		$this->paymentFailureUrl = Route::_(EShopRoute::getViewRoute('checkout') . '&layout=failure');
	}

	/**
	 * This method need to be implemented by the payment plugin class. It is called when a payment success. Usually,
	 * this method will update status of the order to success, trigger onPaymentSuccess event and send notification emails
	 * to administrator(s) and customer
	 *
	 * @param   Table   $row
	 * @param   string  $transactionId
	 *
	 * @return void
	 */
	protected function onPaymentSuccess($row, $transactionId)
	{
		$row->transaction_id  = $transactionId;
		$row->order_status_id = EShopHelper::getConfigValue('complete_status_id');
		$row->store();
		EShopHelper::completeOrder($row);
		PluginHelper::importPlugin('eshop');
		Factory::getApplication()->triggerEvent('onAfterCompleteOrder', [$row]);
		//Send confirmation email here
		if (EShopHelper::getConfigValue('order_alert_mail'))
		{
			EShopHelper::sendEmails($row);
		}
	}

	/**
	 * This method need to be implemented by the payment gateway class. It needs to init the Table order record,
	 * update it with transaction data and then call onPaymentSuccess method to complete the order.
	 *
	 * @param   int     $id
	 * @param   string  $transactionId
	 *
	 * @return mixed
	 */
	protected function onVerifyPaymentSuccess($id, $transactionId)
	{
		$row = Table::getInstance('Eshop', 'Order');
		$row->load($id);

		if (!$row->id)
		{
			return false;
		}

		if ($row->order_status_id == EShopHelper::getConfigValue('complete_status_id'))
		{
			return false;
		}

		$this->onPaymentSuccess($row, $transactionId);
	}
	
	/**
	 * This method need to be implemented by the payment plugin class. It is called when a payment failure. Usually,
	 * this method will update status of the order to failure, trigger onPaymentFailure event and send notification emails
	 * to administrator(s)
	 *
	 * @param   Table   $row
	 *
	 * @return void
	 */
	protected function onPaymentFailure($row)
	{
		$row->order_status_id = EShopHelper::getConfigValue('failed_status_id');
		$row->store();
	
		// Trigger onAfterFailureOrder event
		PluginHelper::importPlugin('eshop');
		Factory::getApplication()->triggerEvent('onAfterFailureOrder', [$row]);
	
		//Send failure notification email here
		if (EShopHelper::getConfigValue('order_failure_mail_admin', 1))
		{
			EShopHelper::sendAdminNotifyEmails($row, 'failure');
		}
	}
	
	/**
	 * This method need to be implemented by the payment gateway class. It needs to init the Table order record,
	 * then call onPaymentFailure method to process the order.
	 *
	 * @param   int     $id
	 * @param   string  $transactionId
	 *
	 * @return mixed
	 */
	protected function onVerifyPaymentFailure($id)
	{
		$row = Table::getInstance('Eshop', 'Order');
		$row->load($id);
	
		$this->onPaymentFailure($row);
	}

	/**
	 * This method is usually called by payment method class to add additional data
	 * to the request message before that message is actually sent to the payment gateway
	 *
	 * @param   AbstractRequest  $request
	 * @param   Table            $row
	 * @param   array            $data
	 */
	protected function beforeRequestSend($request, $row, $data)
	{
		parent::beforeRequestSend($request, $row, $data);

		$Itemid = Factory::getApplication()->input->getInt('Itemid', 0);
		
		if (!$Itemid)
		{
			$Itemid = EShopRoute::getDefaultItemId();
		}
		
		// Set return, cancel and notify URL
		$siteUrl	= Uri::base();
		$langLink	= EShopHelper::getLangLink();

		$request->setCancelUrl($siteUrl . 'index.php?option=com_eshop&task=checkout.cancelOrder&order_number=' . $data['order_number'] . $langLink . '&Itemid=' . $Itemid);
		$request->setReturnUrl($siteUrl . 'index.php?option=com_eshop&task=checkout.verifyPayment&payment_method=' . $this->name . $langLink . '&Itemid=' . $Itemid);
		$request->setNotifyUrl(
			$siteUrl . 'index.php?option=com_eshop&task=checkout.verifyPayment&payment_method=' . $this->name . '&notify=1' . $langLink . '&Itemid=' . $Itemid
		);
		if (EShopHelper::getConfigValue('default_currency_code') != $data['currency_code'])
		{
			$currency = EShopCurrency::getInstance();
			$amount   = round($currency->convert($data['total'], EShopHelper::getConfigValue('default_currency_code'), $data['currency_code']), 2);
		}
		else
		{
			$amount = round($data['total'], 2);
		}
		$request->setAmount($amount);
		$request->setCurrency($data['currency_code']);
		$request->setDescription(Text::sprintf('ESHOP_PAYMENT_FOR_ORDER', $data['order_id']));
		if (empty($this->redirectHeading))
		{
			$language    = Factory::getLanguage();
			$languageKey = 'ESHOP_WAIT_' . strtoupper(substr($this->name, 3));
			if ($language->hasKey($languageKey))
			{
				$redirectHeading = Text::_($languageKey);
			}
			else
			{
				$redirectHeading = Text::sprintf('ESHOP_REDIRECT_HEADING', $this->getTitle());
			}
			$this->setRedirectHeading($redirectHeading);
		}
	}

	/**
	 * Get Omnipay Creditcard object use for processing payment
	 *
	 * @param $data
	 *
	 * @return CreditCard
	 */
	public function getOmnipayCard($data)
	{
		$cardData      = [];
		$fieldMappings = [
			'payment_firstname'   => 'billingFirstName',
			'payment_lastname'    => 'billingLastName',
			'payment_company'     => 'billingCompany',
			'payment_address_1'   => 'billingAddress1',
			'payment_address_2'   => 'billingAddress2',
			'payment_city'        => 'billingCity',
			'payment_postcode'    => 'billingPostcode',
			'payment_zone_name'   => 'billingState',
			'payment_country_id'  => 'billingCountry',
			'payment_telephone'   => 'billingPhone',
			'payment_fax'         => 'billingFax',
			'shipping_firstname'  => 'shippingFirstName',
			'shipping_lastname'   => 'shippingLastName',
			'shipping_company'    => 'shippingCompany',
			'shipping_address_1'  => 'shippingAddress1',
			'shipping_address_2'  => 'shippingAddress2',
			'shipping_city'       => 'shippingCity',
			'shipping_postcode'   => 'shippingPostcode',
			'shipping_zone_name'  => 'shippingState',
			'shipping_country_id' => 'shippingCountry',
			'shipping_telephone'  => 'shippingPhone',
			'shipping_fax'        => 'shippingFax',
			'email'               => 'email',
			'card_number'         => 'number',
			'exp_month'           => 'expiryMonth',
			'exp_year'            => 'expiryYear',
			'cvv_code'            => 'cvv',
			'card_holder_name'    => 'name',
		];

		foreach ($fieldMappings as $field => $omnipayField)
		{
			if ($field == 'payment_country_id' || $field == 'shipping_country_id')
			{
				$countryInfo             = EShopHelper::getCountry($data[$field]);
				$cardData[$omnipayField] = $countryInfo->iso_code_2;
			}
			elseif (isset($data[$field]))
			{
				$cardData[$omnipayField] = trim($data[$field]);
			}
		}

		return new CreditCard($cardData);
	}

	/**
	 * Default function to render payment information, the child class can override it if needed
	 */
	public function renderPaymentInformation($privacyPolicyArticleLink = '', $checkoutTermsLink = '')
	{
		$app               = Factory::getApplication();
		$bootstrapHelper   = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
		$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
		$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
		$controlsClass     = $bootstrapHelper->getClassMapping('controls');
		$rootUri           = Uri::root(true);

		$Itemid = $app->input->getInt('Itemid', 0);

		if (!$Itemid)
		{
			$Itemid = EShopRoute::getDefaultItemId();
		}
		?>
		<script type="text/javascript">
			<?php
			if (EShopHelper::getConfigValue('enable_checkout_captcha'))
			{
				$captchaPlugin = $app->get('captcha') ?: 'recaptcha';
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
			function checkNumber(input) {
				var num = input.value
				if (isNaN(num)) {
					alert("<?php echo Text::_('ESHOP_ONLY_NUMBER_IS_ACCEPTED'); ?>");
					input.value = "";
					input.focus();
				}
			}

			function checkPaymentData() {
				form = document.getElementById('payment_method_form');
				<?php
				if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'confirm_step')
				{
				if (EShopHelper::getConfigValue('show_privacy_policy_checkbox'))
				{
				?>
				if (!form.privacy_policy_agree.checked) {
					alert("<?php echo Text::_('ESHOP_AGREE_PRIVACY_POLICY_ERROR'); ?>");
					form.privacy_policy_agree.focus();
					return false;
				}
				<?php
				}

				if (EShopHelper::getConfigValue('checkout_terms'))
				{
				?>
				if (!form.checkout_terms_agree.checked) {
					alert("<?php echo Text::_('ESHOP_ERROR_CHECKOUT_TERMS_AGREE'); ?>");
					form.checkout_terms_agree.focus();
					return false;
				}
				<?php
				}
				}

				if ($this->type)
				{
				?>
				if (form.card_number.value == "") {
					alert("<?php echo Text::_('ESHOP_ENTER_CARD_NUMBER'); ?>");
					form.card_number.focus();
					return false;
				}
				if (form.cvv_code.value == "") {
					alert("<?php echo Text::_('ESHOP_ENTER_CARD_CVV_CODE'); ?>");
					form.cvv_code.focus();
					return false;
				}
				if (form.card_holder_name.value == '') {
					alert("<?php echo Text::_('ESHOP_ENTER_CARD_HOLDER_NAME'); ?>");
					form.card_holder_name.focus();
					return false;
				}
				return true;
				<?php
				}
				else
				{
				?>
				return true;
				<?php
				}
				?>
			}

			Eshop.jQuery(document).ready(function ($) {
				<?php
				if ($this->name == 'os_stripe')
				{
				?>
				if (typeof stripe !== 'undefined' && $('#stripe-card-element').length > 0) {
					var style = {
						base: {
							// Add your base input styles here. For example:
							fontSize: '16px',
							color: "#32325d",
						}
					};

					// Create an instance of the card Element.
					var card = elements.create('card', {style: style});

					// Add an instance of the card Element into the `card-element` <div>.
					card.mount('#stripe-card-element');
				}
				<?php
				}
				?>
				// Confirm button
				$('#button-confirm').click(function () {
					if (checkPaymentData()) {
						<?php
						if (EShopHelper::getConfigValue('enable_checkout_captcha'))
						{
						if ($plugin)
						{
						if (in_array($captchaPlugin, ['recaptcha', 'recaptcha_invisible']))
						{
						?>
						var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
						jQuery.ajax({
							url: siteUrl + 'index.php?option=com_eshop&task=checkout.validateCaptcha',
							type: 'post',
							dataType: 'json',
							data: jQuery('#payment_method_form').serialize(),
							beforeSend: function () {
								$('#button-confirm').attr('disabled', true);
								$('#button-confirm').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
							},
							complete: function () {
								$('.wait').remove();
							},
							success: function (data) {
								if (data['error']) {
									alert(data['error']);
									$('#button-confirm').attr('disabled', false);
								}
								if (data['success']) {
									<?php
									if ($this->name == 'os_stripe')
									{
									?>
									if (typeof stripePublicKey !== 'undefined' && $('#tr_card_number').is(":visible")) {
										Stripe.card.createToken({
											number: $('input[name^=card_number]').val(),
											cvc: $('input[name^=cvv_code]').val(),
											exp_month: $('select[name^=exp_month]').val(),
											exp_year: $('select[name^=exp_year]').val(),
											name: $('input[name^=card_holder_name]').val()
										}, stripeResponseHandler);
									}

									// Stripe card element
									if (typeof stripe !== 'undefined' && $('#stripe-card-form').is(":visible")) {
										stripe.createToken(card).then(function (result) {
											if (result.error) {
												// Inform the customer that there was an error.
												//var errorElement = document.getElementById('card-errors');
												//errorElement.textContent = result.error.message;
												$('.wait').remove();
												$('#button-confirm').prop('disabled', false);
												alert(result.error.message);
											} else {
												// Send the token to your server.
												stripeTokenHandler(result.token);
											}
										});
									}
									<?php
									}
									else
									{
									?>
									$('#payment_method_form').submit();
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
						?>
						$('#button-confirm').attr('disabled', true);
						$('#button-confirm').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
						<?php
						if ($this->name == 'os_stripe')
						{
						?>
						if (typeof stripePublicKey !== 'undefined' && $('#tr_card_number').is(":visible")) {
							Stripe.card.createToken({
								number: $('input[name^=card_number]').val(),
								cvc: $('input[name^=cvv_code]').val(),
								exp_month: $('select[name^=exp_month]').val(),
								exp_year: $('select[name^=exp_year]').val(),
								name: $('input[name^=card_holder_name]').val()
							}, stripeResponseHandler);
						}

						// Stripe card element
						if (typeof stripe !== 'undefined' && $('#stripe-card-form').is(":visible")) {
							stripe.createToken(card).then(function (result) {
								if (result.error) {
									// Inform the customer that there was an error.
									//var errorElement = document.getElementById('card-errors');
									//errorElement.textContent = result.error.message;
									$('.wait').remove();
									$('#button-confirm').prop('disabled', false);
									alert(result.error.message);
								} else {
									// Send the token to your server.
									stripeTokenHandler(result.token);
								}
							});
						}
						<?php
						}
						else
						{
						?>
						$('#payment_method_form').submit();
						<?php
						}
						}
						}
						else
						{
						?>
						$('#button-confirm').attr('disabled', true);
						$('#button-confirm').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
						<?php
						if ($this->name == 'os_stripe')
						{
						?>
						if (typeof stripePublicKey !== 'undefined' && $('#tr_card_number').is(":visible")) {
							Stripe.card.createToken({
								number: $('input[name^=card_number]').val(),
								cvc: $('input[name^=cvv_code]').val(),
								exp_month: $('select[name^=exp_month]').val(),
								exp_year: $('select[name^=exp_year]').val(),
								name: $('input[name^=card_holder_name]').val()
							}, stripeResponseHandler);
						}

						// Stripe card element
						if (typeof stripe !== 'undefined' && $('#stripe-card-form').is(":visible")) {
							stripe.createToken(card).then(function (result) {
								if (result.error) {
									// Inform the customer that there was an error.
									//var errorElement = document.getElementById('card-errors');
									//errorElement.textContent = result.error.message;
									$('.wait').remove();
									$('#button-confirm').prop('disabled', false);
									alert(result.error.message);
								} else {
									// Send the token to your server.
									stripeTokenHandler(result.token);
								}
							});
						}
						<?php
						}
						else
						{
						?>
						$('#payment_method_form').submit();
						<?php
						}
						}
						}
						else
						{
						?>
						$('#button-confirm').attr('disabled', true);
						$('#button-confirm').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
						<?php
						if ($this->name == 'os_stripe')
						{
						?>
						if (typeof stripePublicKey !== 'undefined' && $('#tr_card_number').is(":visible")) {
							Stripe.card.createToken({
								number: $('input[name^=card_number]').val(),
								cvc: $('input[name^=cvv_code]').val(),
								exp_month: $('select[name^=exp_month]').val(),
								exp_year: $('select[name^=exp_year]').val(),
								name: $('input[name^=card_holder_name]').val()
							}, stripeResponseHandler);
						}

						// Stripe card element
						if (typeof stripe !== 'undefined' && $('#stripe-card-form').is(":visible")) {
							stripe.createToken(card).then(function (result) {
								if (result.error) {
									// Inform the customer that there was an error.
									//var errorElement = document.getElementById('card-errors');
									//errorElement.textContent = result.error.message;
									$('.wait').remove();
									$('#button-confirm').prop('disabled', false);
									alert(result.error.message);
								} else {
									// Send the token to your server.
									stripeTokenHandler(result.token);
								}
							});
						}
						<?php
						}
						else
						{
						?>
						$('#payment_method_form').submit();
						<?php
						}
						}
						?>
					}
				})
			})
			var stripeResponseHandler = function (status, response) {
				Eshop.jQuery(function ($) {
					var $form = $('#payment_method_form');
					if (response.error) {
						// Show the errors on the form
						//$form.find('.payment-errors').text(response.error.message);
						alert(response.error.message);
						$('.wait').remove();
						$form.find('#button-confirm').prop('disabled', false);
					} else {
						// token contains id, last4, and card type
						var token = response.id;
						// Empty card data since we now have token
						$('#card_number').val('');
						$('#cvv_code').val('');
						$('#card_holder_name').val('');
						// Insert the token into the form so it gets submitted to the server
						$form.append($('<input type="hidden" name="stripeToken" />').val(token));
						// and re-submit
						$form.get(0).submit();
					}
				});
			};

			var stripeTokenHandler = function (token) {
				Eshop.jQuery(function ($) {
					// Insert the token ID into the form so it gets submitted to the server
					var form = $('#payment_method_form');
					var hiddenInput = document.createElement('input');
					hiddenInput.setAttribute('type', 'hidden');
					hiddenInput.setAttribute('name', 'stripeToken');
					hiddenInput.setAttribute('value', token.id);
					form.append(hiddenInput);

					// Submit the form
					form.submit();
				});
			};
		</script>
		<form action="<?php
		echo EShopHelper::getSiteUrl(); ?>index.php?option=com_eshop&task=checkout.processOrder&Itemid=<?php
		echo $Itemid; ?>" method="post" name="payment_method_form" id="payment_method_form" class="form form-horizontal">
			<?php
			if ($this->name == 'os_stripe')
			{
				$useStripeCardElement = $this->params->get('use_stripe_card_element', 0);

				if ($useStripeCardElement)
				{
					?>
					<div class="<?php
					echo $controlGroupClass; ?>" id="stripe-card-form">
						<div class="<?php
						echo $controlLabelClass; ?>" for="stripe-card-element">
							<?php
							echo Text::_('ESHOP_CREDIT_OR_DEBIT_CARD'); ?><span class="required">*</span>
						</div>
						<div class="<?php
						echo $controlsClass; ?>" id="stripe-card-element">

						</div>
					</div>
					<?php
				}
			}
			?>
			<div class="no_margin_left">
				<?php
				if ($this->type)
				{
					$currentYear = date('Y');
					?>
					<div class="<?php
					echo $controlGroupClass; ?>" id="tr_card_number">
						<div class="<?php
						echo $controlLabelClass; ?>">
							<?php
							echo Text::_('ESHOP_CARD_NUMBER'); ?><span class="required">*</span>
						</div>
						<div class="<?php
						echo $controlsClass; ?>">
							<input type="text" id="card_number" name="card_number" class="input-xlarge form-control" onkeyup="checkNumber(this)"
							       value=""/>
						</div>
					</div>
					<div class="<?php
					echo $controlGroupClass; ?>" id="tr_exp_date">
						<div class="<?php
						echo $controlLabelClass; ?>">
							<?php
							echo Text::_('ESHOP_CARD_EXPIRY_DATE'); ?><span class="required">*</span>
						</div>
						<div class="<?php
						echo $controlsClass; ?>">
							<?php
							echo HTMLHelper::_(
									'select.integerlist',
									1,
									12,
									1,
									'exp_month',
									' class="input-medium form-select" ',
									date('m'),
									'%02d'
								) . '  /  ' . HTMLHelper::_(
									'select.integerlist',
									$currentYear,
									$currentYear + 10,
									1,
									'exp_year',
									' class="input-medium form-select"'
								); ?>
						</div>
					</div>
					<div class="<?php
					echo $controlGroupClass; ?>" id="tr_cvv_code">
						<div class="<?php
						echo $controlLabelClass; ?>" for="cvv_code">
							<?php
							echo Text::_('ESHOP_CVV_CODE'); ?><span class="required">*</span>
						</div>
						<div class="<?php
						echo $controlsClass; ?>">
							<input type="text" id="cvv_code" name="cvv_code" class="input-small form-control" onKeyUp="checkNumber(this)" value=""/>
						</div>
					</div>
					<div class="<?php
					echo $controlGroupClass; ?>" id="tr_card_holder_name">
						<div class="<?php
						echo $controlLabelClass; ?>" for="card_holder_name">
							<?php
							echo Text::_('ESHOP_CARD_HOLDER_NAME'); ?><span class="required">*</span>
						</div>
						<div class="<?php
						echo $controlsClass; ?>">
							<input type="text" id="card_holder_name" name="card_holder_name" class="input-xlarge form-control" value=""/>
						</div>
					</div>
					<?php
				}

				if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'confirm_step')
				{
					if (EShopHelper::getConfigValue('show_privacy_policy_checkbox'))
					{
						?>
						<div class="<?php
						echo $controlGroupClass; ?> eshop-privacy-policy">
							<div class="<?php
							echo $controlLabelClass; ?>">
								<?php
								if ($privacyPolicyArticleLink != '')
								{
									?>
									<a class="colorbox cboxElement" href="<?php
									echo $privacyPolicyArticleLink; ?>"><?php
										echo Text::_('ESHOP_PRIVACY_POLICY'); ?></a>
									<?php
								}
								else
								{
									echo Text::_('ESHOP_PRIVACY_POLICY');
								}
								?>
							</div>
							<div class="<?php
							echo $controlsClass; ?>">
								<input type="checkbox" class="form-check-input" name="privacy_policy_agree" value="1"/>
								<?php
								$agreePrivacyPolicyMessage = Text::_('ESHOP_AGREE_PRIVACY_POLICY_MESSAGE');

								if (strlen($agreePrivacyPolicyMessage))
								{
									?>
									<div class="eshop-agree-privacy-policy-message alert alert-info"><?php
										echo $agreePrivacyPolicyMessage; ?></div>
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
						<div class="<?php
						echo $controlGroupClass; ?> eshop-newsletter-interest">
							<label for="textarea" class="checkbox">
								<input type="checkbox" class="form-check-input" value="1" name="newsletter_interest"/><?php
								echo Text::_('ESHOP_NEWSLETTER_INTEREST'); ?>
							</label>
						</div>
						<?php
					}

					if ($checkoutTermsLink != '')
					{
						?>
						<div class="<?php
						echo $controlGroupClass; ?> eshop-checkout-terms">
							<label for="textarea" class="checkbox">
								<input type="checkbox" class="form-check-input" value="1" name="checkout_terms_agree" <?php
								echo $this->checkout_terms_agree ?: ''; ?>/>
								<?php
								echo Text::_('ESHOP_CHECKOUT_TERMS_AGREE'); ?>&nbsp;<a class="colorbox cboxElement" href="<?php
								echo $checkoutTermsLink; ?>"><?php
									echo Text::_('ESHOP_CHECKOUT_TERMS_AGREE_TITLE'); ?></a>
							</label>
						</div>
						<?php
					}
				}

				if (EShopHelper::getConfigValue('enable_checkout_captcha') && $plugin)
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
				?>
				<div class="no_margin_left">
					<input id="button-confirm" type="button" class="<?php
					echo $bootstrapHelper->getClassMapping('btn'); ?> btn-primary pull-right" value="<?php
					echo Text::_('ESHOP_CONFIRM_ORDER'); ?>"/>
				</div>
			</div>
			<?php
			echo HTMLHelper::_('form.token'); ?>
		</form>
		<?php
	}
}