<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**@var OSMembershipHelperBootstrap $bootstrapHelper **/
$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$inputPrependClass = $bootstrapHelper->getClassMapping('input-prepend');
$inputAppendClass  = $bootstrapHelper->getClassMapping('input-append');
$addOnClass        = $bootstrapHelper->getClassMapping('add-on');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$radioClass        = $bootstrapHelper->getFrameworkClass('uk-radio', 3);

$stripePaymentMethod = null;

if (count($this->methods) > 1)
{
?>
    <div class="<?php echo $controlGroupClass; ?> payment_information<?php if ($this->useIconForPaymentMethods) echo ' payment-methods-icons'; ?>" id="payment_method_container">
        <div class="<?php echo $controlLabelClass; ?>" >
            <label for="payment_method">
				<?php echo Text::_('OSM_PAYMENT_OPTION'); ?>
                <span class="required">*</span>
            </label>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <fieldset id="osm-payment-method-list"<?php echo $bootstrapHelper->getFrameworkClass('uk-fieldset', 3); ?>>
				<?php
				$baseUri = Uri::base(true);
				$method = null ;

				for ($i = 0 , $n = count($this->methods); $i < $n; $i++)
				{
					$paymentMethod = $this->methods[$i];

					if ($paymentMethod->getName() == $this->paymentMethod)
					{
						$checked = ' checked="checked" ';
						$method = $paymentMethod ;
					}
					else
					{
						$checked = '';
					}

					if (str_contains($paymentMethod->getName(), 'os_stripe'))
					{
						$stripePaymentMethod = $paymentMethod;
					}
					?>
                    <div class="osm-payment-method-item <?php echo $paymentMethod->getName(); ?> clearfix">
                        <label>
                            <input onclick="changePaymentMethod();" id="osm-payment-method-item-<?php echo $i; ?>" type="radio" name="payment_method" value="<?php echo $paymentMethod->getName(); ?>" <?php echo $checked . $radioClass; ?> />
							<?php
							if ($paymentMethod->iconUri)
							{
								?>
                                <img class="osm-payment-method-icon clearfix" src="<?php echo $paymentMethod->iconUri; ?>" title="<?php echo Text::_($paymentMethod->title); ?>"  />
								<?php
							}
							else
							{
								echo Text::_($paymentMethod->title);
							}
							?>
                        </label>
                    </div>
					<?php
				}
				?>
            </fieldset>
        </div>
    </div>
<?php
}
else
{
	$method = $this->methods[0];

	if (str_contains($method->getName(), 'os_stripe'))
	{
		$stripePaymentMethod = $method;
	}
	?>
    <div class="<?php echo $controlGroupClass; ?> payment_information" id="payment_method_container">
        <div class="<?php echo $controlLabelClass; ?>">
            <label for="payment_method">
				<?php echo Text::_('OSM_PAYMENT_OPTION'); ?>
            </label>
        </div>
        <div class="<?php echo $controlsClass; ?>">
			<?php
			if ($method->iconUri)
			{
				?>
                <img class="osm-payment-method-icon clearfix" src="<?php echo $method->iconUri; ?>" title="<?php echo Text::_($method->title); ?>"  />
				<?php
			}
			else
			{
				echo Text::_($method->title);
			}
			?>
        </div>
    </div>
	<?php
}

if ($method->getCreditCard())
{
	$style = '' ;
}
else
{
	$style = 'style = "display:none"';
}
?>
    <div class="<?php echo $controlGroupClass; ?> payment_information mp-credit-card-input" id="tr_card_number" <?php echo $style; ?>>
        <div class="<?php echo $controlLabelClass; ?>">
            <label><?php echo  Text::_('AUTH_CARD_NUMBER'); ?><span class="required">*</span></label>
        </div>
        <div class="<?php echo $controlsClass; ?>" id="sq-card-number">
            <input type="text" name="x_card_num" class="validate[required]><?php echo $bootstrapHelper->getFrameworkClass('uk-input', 1); ?>" value="<?php echo $this->escape($this->input->post->getAlnum('x_card_num'));?>" size="20" />
        </div>
    </div>
    <div class="<?php echo $controlGroupClass; ?> payment_information mp-credit-card-input" id="tr_exp_date" <?php echo $style; ?>>
        <div class="<?php echo $controlLabelClass; ?>">
            <label>
				<?php echo Text::_('AUTH_CARD_EXPIRY_DATE'); ?><span class="required">*</span>
            </label>
        </div>
        <div class="<?php echo $controlsClass; ?>" id="sq-expiration-date">
			<?php echo $this->lists['exp_month'] . '  /  ' . $this->lists['exp_year'] ; ?>
        </div>
    </div>
    <div class="<?php echo $controlGroupClass; ?> payment_information mp-credit-card-input" id="tr_cvv_code" <?php echo $style; ?>>
        <div class="<?php echo $controlLabelClass; ?>">
            <label>
				<?php echo Text::_('AUTH_CVV_CODE'); ?><span class="required">*</span>
            </label>
        </div>
        <div class="<?php echo $controlsClass; ?>" id="sq-cvv">
            <input type="text" name="x_card_code" class="validate[required,custom[number]]<?php echo $bootstrapHelper->getFrameworkClass('uk-input', 1); ?>" value="<?php echo $this->escape($this->input->post->getString('x_card_code')); ?>" size="20" />
        </div>
    </div>
<?php
if ($method->getCardHolderName())
{
	$style = '' ;
}
else
{
	$style = ' style = "display:none;" ' ;
}
?>
    <div class="<?php echo $controlGroupClass; ?> payment_information mp-credit-card-input" id="tr_card_holder_name" <?php echo $style; ?>>
        <div class="<?php echo $controlLabelClass; ?>">
            <label>
				<?php echo Text::_('OSM_CARD_HOLDER_NAME'); ?><span class="required">*</span>
            </label>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <input type="text" name="card_holder_name" class="validate[required]<?php echo $bootstrapHelper->getFrameworkClass('uk-input', 1); ?>"  value="<?php echo $this->input->post->getString('card_holder_name'); ?>" size="40" />
        </div>
    </div>

<?php
if ($stripePaymentMethod !== null && method_exists($stripePaymentMethod, 'getParams'))
{
	/* @var os_stripe $stripePaymentMethod */
	$params = $stripePaymentMethod->getParams();
	$useStripeCardElement = $params->get('use_stripe_card_element', 0);

	if ($useStripeCardElement)
	{
		if ($method->getName() === 'os_stripe')
		{
			$style = '';
		}
		else
		{
			$style = ' style = "display:none;" ';
		}
		?>
        <div class="control-group payment_information" <?php echo $style; ?> id="stripe-card-form">
            <label class="control-label" for="stripe-card-element">
				<?php echo Text::_('OSM_CREDIT_OR_DEBIT_CARD'); ?><span class="required">*</span>
            </label>
            <div class="controls" id="stripe-card-element">

            </div>
        </div>
		<?php
	}
}

if ($this->hasSquareCard)
{
	if (str_contains($method->getName(), 'os_squarecard'))
	{
		$style = '';
	}
	else
	{
		$style = ' style = "display:none;" ';
	}
	?>
    <div class="<?php echo $controlGroupClass;  ?> payment_information" <?php echo $style; ?> id="square-card-form">
        <div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('OSM_CREDIT_OR_DEBIT_CARD'); ?><span class="required">*</span>
        </div>
        <div class="<?php echo $controlsClass; ?>" id="square-card-element">

        </div>
    </div>
    <input type="hidden" name="square_card_token" value="" />
    <input type="hidden" name="square_card_verification_token" value="" />
	<?php
}

// Having a way for payment method to render input to collect it own payment information
foreach ($this->methods as $paymentMethodObj)
{
	if (is_callable([$paymentMethodObj, 'renderCollectPaymentInformation']))
	{
		$paymentMethodObj->renderCollectPaymentInformation($method);
	}
}