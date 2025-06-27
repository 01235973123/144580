<?php
/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2012 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->addInlineScript('var siteUrl = "' . OSMembershipHelper::getSiteUrl() . '";');

OSMembershipHelperJquery::validateForm();

$headerText = Text::_('OSM_SUBSCRIPTION_PAYMENT');

if ($this->fieldSuffix && OSMembershipHelper::isValidMessage($this->message->{'subscription_payment_form_message' . $this->fieldSuffix}))
{
	$msg = $this->message->{'subscription_payment_form_message' . $this->fieldSuffix};
}
else
{
	$msg = $this->message->subscription_payment_form_message;
}

$replaces = [
	'amount'     => OSMembershipHelper::formatCurrency($this->row->gross_amount, $this->config, $this->plan->currency_symbol),
	'id'         => $this->row->id,
	'plan_title' => $this->plan->title,
];

$msg        = OSMembershipHelper::replaceUpperCaseTags($msg, $replaces);
$headerText = OSMembershipHelper::replaceUpperCaseTags($headerText, $replaces);

if ($this->config->use_https)
{
	$url = Route::_('index.php?option=com_osmembership&task=payment.process&Itemid=' . $this->Itemid, false, 1);
}
else
{
	$url = Route::_('index.php?option=com_osmembership&task=payment.process&Itemid=' . $this->Itemid, false);
}

$selectedState = '';

/* @var OSMembershipHelperBootstrap $bootstrapHelper */
$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$btnPrimary        = $bootstrapHelper->getClassMapping('btn btn-primary');
?>
<div id="osm-payment-page" class="osm-container">
	<h1 class="osm-page-title"><?php echo $this->escape($headerText); ?></h1>
	<form method="post" name="os_form" id="os_form" action="<?php echo $url; ?>" autocomplete="off"
	      class="form form-horizontal">
		<?php
		if (strlen($msg))
		{
		?>
			<div class="osm-message"><?php echo $msg; ?></div>
		<?php
		}

		$fields = $this->form->getFields();

		if (isset($fields['state']))
		{
			$selectedState = $fields['state']->value;
		}

		//We don't need to use ajax validation for email field for group members
		if (isset($fields['email']))
		{
			/* @var RADFormField $emailField */
			$emailField = $fields['email'];
			$cssClass   = $emailField->getAttribute('class');
			$cssClass   = str_replace(',ajax[ajaxEmailCall]', '', $cssClass);
			$emailField->setAttribute('class', $cssClass);
		}

		// Billing form fields
		foreach ($fields as $field)
		{
			echo $field->getControlGroup($bootstrapHelper);
		}

		// Payment amount
		echo $this->loadCommonLayout('payment/tmpl/payment_amounts.php', ['selectedState' => $selectedState]);

		// Payment methods
		echo $this->loadCommonLayout('payment/tmpl/payment_methods.php');

		if ($this->showCaptcha)
		{
			if ($this->captchaPlugin == 'recaptcha_invisible')
			{
				$style = ' style="display:none;"';
			}
			else
			{
				$style = '';
			}
			?>
			<div class="<?php echo $controlGroupClass ?> osm-captcha-container">
				<label class="<?php echo $controlLabelClass; ?>"<?php echo $style; ?>>
					<?php echo Text::_('OSM_CAPTCHA'); ?><span class="required">*</span>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo $this->captcha; ?>
				</div>
			</div>
			<?php
		}
		?>
		<div class="form-actions">
			<input type="submit" class="<?php echo $btnPrimary; ?>" name="btn-submit" id="btn-submit"
			       value="<?php echo Text::_('OSM_PROCESS_PAYMENT'); ?>"/>
			<img id="ajax-loading-animation"
			     src="<?php echo Uri::root(true); ?>/media/com_osmembership/ajax-loadding-animation.gif"
			     style="display: none;"/>
		</div>
		<?php
		if (count($this->methods) == 1)
		{
		?>
			<input type="hidden" name="payment_method" value="<?php echo $this->methods[0]->getName(); ?>"/>
		<?php
		}

		echo HTMLHelper::_('form.token');
		?>
		<input type="hidden" name="transaction_id" id="transaction_id"
		       value="<?php echo $this->row->transaction_id; ?>"/>
		<input type="hidden" id="card-nonce" name="nonce"/>
		<input type="hidden" name="show_payment_fee" id="show_payment_fee" value="0"/>
		<input type="hidden" name="country_base_tax" value="0"/>
		<input type="hidden" name="plan_id" value="<?php echo $this->row->plan_id; ?>"/>
		<?php echo $this->loadCommonLayout('payment/tmpl/payment_javascript.php', ['selectedState' => $selectedState]); ?>
	</form>
</div>