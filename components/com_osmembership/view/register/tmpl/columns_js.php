<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Layout variables
 *
 * @var string $selectedState
 * @var bool   $hasFeeFields
 * @var string $inputPrependClass
 * @var string $addOnClass
 *
 */

// Calculate and pass PHP variables to Javascript
if (isset($this->fees['gross_amount']) && $this->fees['gross_amount'] == 0 && !$this->plan->recurring_subscription)
{
	$hidePaymentInformation = true;
}
else
{
	$hidePaymentInformation = false;
}

if ($this->plan->price > 0 || $this->plan->setup_fee > 0 || $hasFeeFields)
{
	$paymentNeeded = true;
}
else
{
	$paymentNeeded = false;
}

Factory::getApplication()
	->getDocument()
	->addScriptOptions('hasStripePaymentMethod', $this->hasStripe)
	->addScriptOptions('hidePaymentInformation', $hidePaymentInformation)
	->addScriptOptions('paymentNeeded', $paymentNeeded)
	->addScriptOptions('selectedState', $selectedState)
	->addScriptOptions('vatNumberField', $this->config->eu_vat_number_field)
	->addScriptOptions('showVatNumberField', $this->fees['show_vat_number_field'])
	->addScriptOptions('inputPrependClass', $inputPrependClass)
	->addScriptOptions('addOnClass', $addOnClass)
	->addScriptOptions('countryCode', $this->countryCode)
	->addScriptOptions('maxErrorsPerField', (int) $this->config->max_errors_per_field)
	->getWebAssetManager()
	->useScript('core')
	->addInlineScript('var siteUrl = "' . Uri::root(true) . '/";')
	->addInlineScript(
		'
    var taxStateCountries = "' . $this->taxStateCountries . '";
    taxStateCountries = taxStateCountries.split(",");
'
	);

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

OSMembershipHelperJquery::validateForm();
OSMembershipHelperPayments::writeJavascriptObjects();

OSMembershipHelperHtml::addOverridableScript('media/com_osmembership/js/site-register-columns.min.js');

Text::script('OSM_INVALID_VATNUMBER', true);
