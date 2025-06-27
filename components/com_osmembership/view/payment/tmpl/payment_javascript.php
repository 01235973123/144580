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

/**
 * @var string $selectedState
 */

OSMembershipHelperPayments::writeJavascriptObjects();

if ($this->row->gross_amount > 0)
{
	$paymentNeeded = true;
}
else
{
	$paymentNeeded = false;
}

Factory::getApplication()->getDocument()
	->addScriptOptions('hasStripePaymentMethod', $this->hasStripe)
	->addScriptOptions('paymentNeeded', $paymentNeeded)
	->addScriptOptions('selectedState', $selectedState)
	->addScriptOptions('currencyCode', $this->plan->currency ?: $this->config->currency_code)
	->getWebAssetManager()
	->useScript('core')
	->registerAndUseScript('com_osmembership.site-payment-default', 'media/com_osmembership/js/site-payment-default.min.js');