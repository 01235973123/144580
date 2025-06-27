<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class os_payment
{

	/**
	 * @var String payment method name
	 */
	public $name = null;

	/**
	 * Title of payment method
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * Url of icon
	 *
	 * @var string
	 */
	public $iconUri = null;

	/**
	 * Payment Mode
	 * @var bool
	 */
	protected $mode = null;

	/**
	 * The payment gateway url
	 *
	 * @var string
	 */
	protected $url = null;

	/**
	 * Redirect Heading
	 *
	 * @var null
	 */
	protected $redirectHeading = null;

	/**
	 * @var Registry Payment method data
	 */
	protected $params = null;

	/**
	 * @var Int of the payment method. 0 : Redirect, 1 : Creditcard
	 */
	protected $type = 0;

	/**
	 * @var bool Show Cardtype or not
	 */
	protected $showCardType = false;

	/**
	 * @var bool Show card holder name or not
	 */
	protected $showCardHolderName = false;

	/**
	 * @var Array Data which will be posted to the payment gateway
	 */
	protected $data = null;

	/**
	 * @var Array Data posted from the payment gateway back to server
	 */
	protected $postData = null;


	/**
	 * Constructor function, init the payment method data
	 *
	 * @param $params
	 */

	public function __construct($params, $config = [])
	{
		$this->name = get_class($this);
		if (isset($config['type']))
		{
			$this->type = $config['type'];
		}
		else
		{
			$this->type = 0;
		}

		if (isset($config['show_card_type']))
		{
			$this->showCardType = $config['show_card_type'];
		}
		else
		{
			$this->showCardType = false;
		}

		if (isset($config['show_card_holder_name']))
		{
			$this->showCardHolderName = $config['show_card_holder_name'];
		}
		else
		{
			$this->showCardHolderName = false;
		}

		$this->params = $params;
		$this->loadLanguage();
	}

	/**
	 * Getter method for name property
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Setter method for name property
	 *
	 * @param   string  $value
	 */
	public function setName($value)
	{
		$this->name = $value;
	}

	/**
	 * @return String Get title of the payment method
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param $title String title of the payment method
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 *
	 * Set data for a variable which will be passed to server
	 *
	 * @param $name
	 * @param $value
	 */
	public function setData($name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * Get data for a variable
	 *
	 * @param         $name
	 * @param   null  $default
	 *
	 * @return null
	 */
	public function getData($name, $default = null)
	{
		return $this->data[$name] ?? $default;
	}

	/**
	 *
	 * Function to get params
	 * @return Registry
	 */
	public function getParams()
	{
		return $this->params;
	}


	/**
	 * Build the parameters in key=value format used to send to the payment gateway
	 *
	 * @return string
	 */
	public function buildParameters()
	{
		$fields = '';
		foreach ($this->data as $key => $value)
		{
			$fields .= "$key=" . urlencode($value) . "&";
		}

		return $fields;
	}

	/**
	 * Load language file for this payment plugin
	 */
	protected function loadLanguage()
	{
		$pluginName = $this->getName();
		$lang       = Factory::getLanguage();
		$tag        = $lang->getTag();
		if (!$tag)
		{
			$tag = 'en-GB';
		}
		$lang->load($pluginName, JPATH_ROOT, $tag);
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
		$row->order_status_id = EShopHelper::getConfigValue('failure_status_id');
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
	 * Default function to render payment information, the child class can override it if needed
	 */
	public function renderPaymentInformation($privacyPolicyArticleLink = '', $checkoutTermsLink = '')
	{
		$app               = Factory::getApplication();
		$bootstrapHelper   = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
		$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
		$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
		$controlsClass     = $bootstrapHelper->getClassMapping('controls');
		$Itemid            = $app->input->getInt('Itemid', 0);
		$rootUri           = Uri::root(true);

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
				<?php
				if ($this->showCardHolderName)
				{
				?>
				if (form.card_holder_name.value == '') {
					alert("<?php echo Text::_('ESHOP_ENTER_CARD_HOLDER_NAME'); ?>");
					form.card_holder_name.focus();
					return false;
				}
				<?php
				}
				?>
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
							//data: jQuery('#payment_method_form input[type=\'text\'], #payment_method_form input[type=\'radio\']:checked, #payment_method_form input[type=\'hidden\']'),
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
									$('#payment_method_form').submit();
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
						$('#payment_method_form').submit();
						<?php
						}
						}
						else
						{
						?>
						$('#button-confirm').attr('disabled', true);
						$('#button-confirm').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
						$('#payment_method_form').submit();
						<?php
						}
						}
						else
						{
						?>
						$('#button-confirm').attr('disabled', true);
						$('#button-confirm').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
						$('#payment_method_form').submit();
						<?php
						}
						?>
					}
				})
			})
		</script>
		<form action="<?php
		echo EShopHelper::getSiteUrl(); ?>index.php?option=com_eshop&task=checkout.processOrder&Itemid=<?php
		echo $Itemid; ?>" method="post" name="payment_method_form" id="payment_method_form" class="form form-horizontal">
			<?php
			if ($this->type)
			{
				$currentYear = date('Y');
				?>
				<div class="<?php
				echo $controlGroupClass; ?>">
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
				echo $controlGroupClass; ?>">
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
				echo $controlGroupClass; ?>">
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
				<?php
				if ($this->showCardType)
				{
					$options   = [];
					$options[] = HTMLHelper::_('select.option', 'Visa', 'Visa');
					$options[] = HTMLHelper::_('select.option', 'MasterCard', 'MasterCard');
					$options[] = HTMLHelper::_('select.option', 'Discover', 'Discover');
					$options[] = HTMLHelper::_('select.option', 'Amex', 'American Express');
					?>
					<div class="<?php
					echo $controlGroupClass; ?>">
						<div class="<?php
						echo $controlLabelClass; ?>" for="cvv_code">
							<?php
							echo Text::_('ESHOP_CARD_TYPE'); ?><span class="required">*</span>
						</div>
						<div class="<?php
						echo $controlsClass; ?>">
							<?php
							echo HTMLHelper::_('select.genericlist', $options, 'card_type', ' class="input-xlarge form-select" ', 'value', 'text'); ?>
						</div>
					</div>
					<?php
				}

				if ($this->showCardHolderName)
				{
					?>
					<div class="<?php
					echo $controlGroupClass; ?>">
						<label class="<?php
						echo $controlLabelClass; ?>" for="card_holder_name">
							<?php
							echo Text::_('ESHOP_CARD_HOLDER_NAME'); ?><span class="required">*</span>
						</label>
						<div class="<?php
						echo $controlsClass; ?>">
							<input type="text" id="card_holder_name" name="card_holder_name" class="input-xlarge form-control" value=""/>
						</div>
					</div>
					<?php
				}
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
			<?php
			echo HTMLHelper::_('form.token'); ?>
		</form>
		<?php
	}

	/**
	 * Submit post to paypal server
	 */
	public function submitPost($url = null, $data = [])
	{
		if (empty($url))
		{
			$url = $this->url;
		}

		if (empty($data))
		{
			$data = $this->data;
		}

		if (!$this->redirectHeading)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('title')
				->from('#__eshop_payments')
				->where('name = "' . $this->name . '"');
			$db->setQuery($query);
			$this->redirectHeading = Text::sprintf('ESHOP_REDIRECT_HEADING', Text::_($db->loadResult()));
		}
		?>
		<div class="eshop-heading"><?php
			echo $this->redirectHeading; ?></div>
		<form method="post" action="<?php
		echo $url; ?>" name="eshop_order_form" id="eshop_order_form">
			<?php
			if (count($data))
			{
				foreach ($data as $key => $val)
				{
					echo '<input type="hidden" name="' . $key . '" value="' . EShopHelper::escape($val) . '" />';
					echo "\n";
				}
			}
			?>
			<script type="text/javascript">
				function redirect() {
					document.eshop_order_form.submit();
				}

				setTimeout('redirect()', 5000);
			</script>
		</form>
		<?php
	}

	/**
	 * Log gateway data
	 */
	public function logGatewayData($extraData = null)
	{
		if (!$this->params->get('ipn_log'))
		{
			return;
		}

		$text = '[' . date('m/d/Y g:i A') . '] - ';
		$text .= "Log Data From : " . $this->title . " \n";
		foreach ($this->postData as $key => $value)
		{
			$text .= "$key=$value, ";
		}
		if (strlen($extraData))
		{
			$text .= $extraData;
		}
		$ipnLogFile = JPATH_COMPONENT . '/ipn_' . $this->getName() . '.txt';
		$fp         = fopen($ipnLogFile, 'a');
		fwrite($fp, $text . "\n\n");
		fclose($fp);
	}
}