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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

class OSMembershipModelPayment extends MPFModel
{
	/**
	 * Subscription data
	 *
	 * @var stdClass
	 */
	protected $data;

	/**
	 * Constructor.
	 *
	 * @param   array  $config
	 *
	 * @throws Exception
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);

		$this->state->insert('transaction_id', 'string', '');
	}

	/**
	 * Get data for the subscription which is processing payment
	 *
	 * @return stdClass
	 */
	public function getData()
	{
		if ($this->data === null)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from('#__osmembership_subscribers')
				->where('transaction_id = ' . $db->quote($this->state->transaction_id));
			$db->setQuery($query);
			$this->data = $db->loadObject();
		}

		return $this->data;
	}

	/**
	 * Process subscription payment
	 *
	 * @param   array  $data
	 *
	 * @return void
	 * @throws Exception
	 */
	public function processSubscriptionPayment($data)
	{
		$app    = Factory::getApplication();
		$config = OSMembershipHelper::getConfig();
		$db     = $this->getDbo();
		$query  = $db->getQuery(true)
			->select('id')
			->from('#__osmembership_subscribers')
			->where('transaction_id = ' . $db->quote($data['transaction_id']));
		$db->setQuery($query);

		$id = (int) $db->loadResult();
		/* @var OSMembershipTableSubscriber $row */
		$row = $this->getTable('Subscriber');

		if (!$row->load($id) || $row->published != 0 || $row->gross_amount == 0)
		{
			throw new Exception(Text::_('OSM_INVALID_SUBSCRIPTION_FOR_PROCESSING_PAYMENT'));
		}

		$row->process_payment_for_subscription = 1;
		$row->store();

		$paymentMethod = $data['payment_method'] ?? '';
		$rowPlan       = OSMembershipHelperDatabase::getPlan($row->plan_id);

		// Store ID of the subscription record into session for using on payment complete page
		$app->getSession()->set('mp_subscription_id', $row->id);

		$paymentClass = OSMembershipHelper::loadPaymentMethod($paymentMethod);

		$data['amount'] = $row->gross_amount;

		$itemName          = Text::_('OSM_SUBSCRIPTION_PAYMENT');
		$itemName          = str_replace('[PLAN_TITLE]', $rowPlan->title, $itemName);
		$itemName          = str_replace('[ID]', $row->id, $itemName);
		$data['item_name'] = $itemName;

		// Guess card type based on card number
		if (!empty($data['x_card_num']) && empty($data['card_type']))
		{
			$data['card_type'] = OSMembershipHelperCreditcard::getCardType($data['x_card_num']);
		}

		// Convert payment amount to USD if the currency is not supported by payment gateway
		$currency = $rowPlan->currency ?: $config->currency_code;

		if (method_exists($paymentClass, 'getSupportedCurrencies'))
		{
			$currencies = $paymentClass->getSupportedCurrencies();

			if (!in_array($currency, $currencies))
			{
				if ($data['amount'] > 0)
				{
					$data['amount'] = OSMembershipHelper::convertAmountToUSD($data['amount'], $currency);
				}

				$currency = 'USD';
			}
		}

		$data['currency'] = $currency;

		$country         = empty($data['country']) ? $config->default_country : $data['country'];
		$data['country'] = OSMembershipHelper::getCountryCode($country);

		// Round payment amount before passing to payment gateway
		if ($currency == 'JPY')
		{
			$precision = 0;
		}
		else
		{
			$precision = 2;
		}

		if ($data['amount'] > 0)
		{
			$data['amount'] = round($data['amount'], $precision);
		}

		// Store payment_amount for validation
		$row->payment_amount = $data['amount'];
		$row->store();

		try
		{
			$paymentClass->processPayment($row, $data);
		}
		catch (MPFPaymentException $e)
		{
			$Itemid = $app->getInput()->getInt('Itemid', 0);
			$app->getSession()->set('omnipay_payment_error_reason', $e->getMessage());
			$app->redirect(Route::_('index.php?option=com_osmembership&view=failure&Itemid=' . $Itemid, false));
		}
	}
}
