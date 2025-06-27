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
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

class os_paypal extends MPFPayment
{
	/**
	 * Constructor
	 *
	 * @param   Registry  $params
	 * @param   array     $config
	 */
	public function __construct($params, $config = [])
	{
		parent::__construct($params, $config);

		$this->mode = $params->get('paypal_mode', 0);

		if ($this->mode)
		{
			$this->url = 'https://www.paypal.com/cgi-bin/webscr';
		}
		else
		{
			$this->url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}

		$this->setParameter('business', $this->mode ? $this->params->get('paypal_id') : $this->params->get('sandbox_paypal_id'));
		$this->setParameter('rm', 2);
		$this->setParameter('cmd', '_xclick');
		$this->setParameter('no_shipping', 1);
		$this->setParameter('no_note', 1);

		$locale = $params->get('paypal_locale');

		if (empty($locale))
		{
			if (Multilanguage::isEnabled())
			{
				$locale = Factory::getApplication()->getLanguage()->getTag();
				$locale = str_replace('-', '_', $locale);
			}
			else
			{
				$locale = 'en_US';
			}
		}

		$this->setParameter('lc', $locale);
		$this->setParameter('charset', 'utf-8');

		// Disable tax calculation if it is setup in the owner Paypal account
		$this->setParameter('tax', 0);
	}

	/**
	 * Process onetime subscription payment
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   array                        $data
	 */
	public function processPayment($row, $data)
	{
		$app    = Factory::getApplication();
		$Itemid = $app->getInput()->getInt('Itemid', 0);
		$this->setParameter('currency_code', $data['currency']);
		$this->setParameter('item_name', $data['item_name']);
		$this->setParameter('amount', round($data['amount'], 2));
		$this->setParameter('custom', $row->id);

		$rowPlan = OSMembershipHelperDatabase::getPlan($row->plan_id);

		// Override PayPal email
		if ($rowPlan->paypal_email)
		{
			$this->setParameter('business', $rowPlan->paypal_email);
		}

		$this->setParameter('return', $this->getPaymentCompleteUrl($row, $Itemid, true));
		$this->setParameter('cancel_return', $this->getPaymentCancelUrl($row, $Itemid, true));
		$this->setParameter('notify_url', $this->getPaymentNotifyUrl($row));
		$this->setParameter('address1', $row->address);
		$this->setParameter('address2', $row->address2);
		$this->setParameter('city', $row->city);
		$this->setParameter('country', $data['country']);
		$this->setParameter('first_name', $row->first_name);
		$this->setParameter('last_name', $row->last_name);
		$this->setParameter('state', $row->state);
		$this->setParameter('zip', $row->zip);
		$this->setParameter('email', $row->email);

		// Store receiver PayPal email before redirecting to PayPal
		$row->receiver_email = $this->getParameter('business');
		$row->store();

		$this->renderRedirectForm();
	}

	/**
	 * Verify onetime subscription payment
	 *
	 * @return bool
	 */
	public function verifyPayment()
	{
		// First, validate and make sure the IPN message is valid
		if (!$this->validate())
		{
			return false;
		}

		$row           = $this->getSubscriberTable();
		$id            = $this->notificationData['custom'];
		$transactionId = $this->notificationData['txn_id'];
		$amount        = floatval($this->notificationData['mc_gross']);

		if ($amount < 0)
		{
			return false;
		}

		if (!$row->load($id))
		{
			$this->logGatewayData(sprintf('Invalid Subscription ID %s', $id));

			return false;
		}

		if ($this->transactionProcessed($row, $transactionId))
		{
			$this->logGatewayData(sprintf('Subscription ID %s with transaction ID %s was published before', $id, $transactionId));

			return false;
		}

		// Accept 0.05$ difference to avoid bug causes by rounding
		if (($row->payment_amount - $amount) > 0.05)
		{
			$this->logGatewayData(sprintf('Subscription ID %s has invalid payment amount', $id));

			return false;
		}

		// Validate receiver
		if (!$this->validateReceiver($row))
		{
			$this->logGatewayData(sprintf('Subscription ID %s has invalid receiver', $id));

			return false;
		}

		// Validate currency
		if (!$this->validateCurrency($row))
		{
			$this->logGatewayData(sprintf('Subscription ID %s has invalid currency', $id));

			return false;
		}

		// Validate payment status (only on live mode because PayPal sandbox sometime doesn't work very well)
		if ($this->mode && ($this->notificationData['payment_status'] != 'Completed'))
		{
			$this->logGatewayData(sprintf('Subscription ID %s has invalid payment status %s', $id, $this->notificationData['payment_status']));

			return false;
		}

		$this->onPaymentSuccess($row, $transactionId);
	}

	/**
	 * Process recurring subscription payment
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   array                        $data
	 */
	public function processRecurringPayment($row, $data)
	{
		$app    = Factory::getApplication();
		$Itemid = $app->getInput()->getInt('Itemid', 0);

		$rowPlan = OSMembershipHelperDatabase::getPlan($row->plan_id);

		$this->setParameter('currency_code', $data['currency']);
		$this->setParameter('item_name', $data['item_name']);
		$this->setParameter('custom', $row->id);

		// Override PayPal email if needed
		if ($rowPlan->paypal_email)
		{
			$this->setParameter('business', $rowPlan->paypal_email);
		}

		$this->setParameter('return', $this->getPaymentCompleteUrl($row, $Itemid, true));
		$this->setParameter('cancel_return', $this->getPaymentCancelUrl($row, $Itemid, true));
		$this->setParameter('notify_url', $this->getRecurringPaymentNotifyUrl($row));
		$this->setParameter('cmd', '_xclick-subscriptions');
		$this->setParameter('src', 1);
		$this->setParameter('sra', 1);
		$this->setParameter('a3', $data['regular_price']);
		$this->setParameter('address1', $row->address);
		$this->setParameter('address2', $row->address2);
		$this->setParameter('city', $row->city);
		$this->setParameter('country', $data['country']);
		$this->setParameter('first_name', $row->first_name);
		$this->setParameter('last_name', $row->last_name);
		$this->setParameter('state', $row->state);
		$this->setParameter('zip', $row->zip);
		$this->setParameter('p3', $rowPlan->subscription_length);
		$this->setParameter('t3', $rowPlan->subscription_length_unit);

		if ($rowPlan->number_payments > 1)
		{
			$this->setParameter('srt', $rowPlan->number_payments);
		}

		if ($data['trial_duration'])
		{
			$this->setParameter('a1', $data['trial_amount']);
			$this->setParameter('p1', $data['trial_duration']);
			$this->setParameter('t1', $data['trial_duration_unit']);
		}

		// Store receiver PayPal email before redirecting to PayPal
		$row->receiver_email = $this->getParameter('business');
		$row->store();

		//Redirect users to PayPal for processing payment
		$this->renderRedirectForm();
	}

	/**
	 * Verify recurring payment and extend the subscription if needed
	 */
	public function verifyRecurringPayment()
	{
		// First, validate and
		if (!$this->validate())
		{
			return false;
		}

		$id             = $this->notificationData['custom'];
		$transactionId  = $this->notificationData['txn_id'];
		$subscriptionId = $this->notificationData['subscr_id'];
		$amount         = floatval($this->notificationData['mc_gross']);
		$txnType        = $this->notificationData['txn_type'];

		if ($subscriptionId)
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select('id')
				->from('#__osmembership_subscribers')
				->where('subscription_id = ' . $db->quote($subscriptionId))
				->order('id');
			$db->setQuery($query);

			if ($recordId = $db->loadResult())
			{
				$id = $recordId;
			}
		}

		if ($amount < 0)
		{
			return false;
		}

		if ($transactionId && OSMembershipHelper::isTransactionProcessed($transactionId))
		{
			return false;
		}

		$row = $this->getSubscriberTable();

		if (!$row->load($id))
		{
			return false;
		}

		switch ($txnType)
		{
			case 'subscr_signup':
				if ($row->published)
				{
					return false;
				}

				$row->subscription_id = $subscriptionId;

				if ($row->is_free_trial)
				{
					$row->transaction_id = '';
					$this->onPaymentSuccess($row, $transactionId);
				}
				else
				{
					$row->store();
				}
				break;
			case 'subscr_payment':
				// Validate payment amount and payment currency
				if ($row->payment_currency && !$this->validatePaymentAmountAndCurrency($row))
				{
					return false;
				}

				// First payment (for not free trial subscription), calling  onPaymentSuccess method to send email to subscribers
				if (!$row->is_free_trial && !$row->published && $row->payment_made == 0)
				{
					$row->payment_made    = 1;
					$row->subscription_id = $subscriptionId;
					$this->onPaymentSuccess($row, $transactionId);

					return true;
				}

				// Valid recurring payment, extend the subscription
				$this->processRenewRecurringSubscription($row, $subscriptionId, $transactionId);
				break;
			case 'subscr_cancel':
				OSMembershipHelperSubscription::cancelRecurringSubscription($id);
				break;
		}
	}

	/**
	 * Get list of supported currencies
	 *
	 * @return array
	 */
	public function getSupportedCurrencies()
	{
		return [
			'AUD',
			'BRL',
			'CAD',
			'CZK',
			'DKK',
			'EUR',
			'HKD',
			'HUF',
			'ILS',
			'JPY',
			'MYR',
			'MXN',
			'NOK',
			'NZD',
			'PHP',
			'PLN',
			'GBP',
			'RUB',
			'SGD',
			'SEK',
			'CHF',
			'TWD',
			'THB',
			'TRY',
			'USD',
		];
	}

	/**
	 * Validate the post data from PayPal to our server
	 *
	 * @return string
	 */
	protected function validate()
	{
		JLoader::register('PaypalIPN', JPATH_ROOT . '/components/com_osmembership/plugins/paypal/PayPalIPN.php');

		$ipn = new PaypalIPN();

		// Use sandbox URL if test mode is configured
		if (!$this->mode)
		{
			$ipn->useSandbox();
		}

		if ($this->params->get('use_local_certs', 0) == 0)
		{
			// Disable use custom certs
			$ipn->usePHPCerts();
		}

		$this->notificationData = $_POST;

		try
		{
			$valid = $ipn->verifyIPN();
			$this->logGatewayData($ipn->getResponse());

			if (!$this->mode || $valid)
			{
				return true;
			}

			return false;
		}
		catch (Exception $e)
		{
			$this->logGatewayData($e->getMessage());

			return false;
		}
	}

	/**
	 * Validate and make sure the payment is sent to correct receiver
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return bool
	 */
	protected function validateReceiver($row)
	{
		$receiverEmail = strtoupper($this->notificationData['receiver_email']);
		$receiverId    = strtoupper($this->notificationData['receiver_id']);
		$business      = strtoupper($this->notificationData['business']);

		$validReceiver = strtoupper($row->receiver_email);

		if ($receiverEmail != $validReceiver
			&& $receiverId != $validReceiver
			&& $business != $validReceiver)
		{
			return false;
		}

		return true;
	}

	/**
	 * Validate and make sure the payment is received in valid currency
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return bool
	 */
	protected function validateCurrency($row)
	{
		$receivedCurrency = strtoupper($this->notificationData['mc_currency']);
		$validCurrency    = strtoupper($row->payment_currency);

		if ($validCurrency && ($receivedCurrency != $validCurrency))
		{
			return false;
		}

		return true;
	}

	/**
	 * Validate payment amount and currency of recurring payment
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return bool
	 */
	protected function validatePaymentAmountAndCurrency($row)
	{
		// Validate receiver account
		if (!$this->validateCurrency($row))
		{
			return false;
		}

		// Validate currency
		if (!$this->validateCurrency($row))
		{
			return false;
		}

		// Validate payment amount
		$amount = floatval($this->notificationData['mc_gross']);

		if ($row->payment_made == 0)
		{
			if ($row->trial_payment_amount > 0)
			{
				$expectedPaymentAmount = $row->trial_payment_amount;
			}
			else
			{
				$expectedPaymentAmount = $row->payment_amount;
			}
		}
		else
		{
			$expectedPaymentAmount = $row->payment_amount;
		}

		if (($expectedPaymentAmount - $amount) > 0.05)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to check if cancel recurring subscription is supported
	 *
	 * @return bool
	 */
	public function supportCancelRecurringSubscription()
	{
		return $this->isApiCredentialsEntered();
	}

	/**
	 * Method to check if payment plugin supports refund payment
	 *
	 * @return bool|void
	 */
	public function supportRefundPayment()
	{
		return $this->isApiCredentialsEntered();
	}

	/**
	 * Cancel recurring subscription
	 *
	 * @param $row
	 *
	 * @return bool
	 * @throws Exception
	 *
	 * @since 1.0
	 */
	public function cancelSubscription($row)
	{
		[$apiUrl, $apiUser, $apiPassword, $apiSignature] = $this->getNvpApiParameters();

		if (!$apiUser || !$apiPassword || !$apiSignature)
		{
			Factory::getApplication()->enqueueMessage('Cancel Recurring Subscription is not supported for the payment method you are using', 'error');

			return false;
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_URL, $apiUrl);
		curl_setopt(
			$curl,
			CURLOPT_POSTFIELDS,
			http_build_query([
				'USER'      => $apiUser,
				'PWD'       => $apiPassword,
				'SIGNATURE' => $apiSignature,
				'VERSION'   => '108',
				'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
				'PROFILEID' => $row->subscription_id,
				'ACTION'    => 'Cancel',
			])
		);

		$response = curl_exec($curl);
		curl_close($curl);

		$nvp = $this->deformatNVP($response);

		if ($nvp['ACK'] == 'Success')
		{
			return true;
		}
		Factory::getApplication()->enqueueMessage($nvp['L_LONGMESSAGE0'], 'error');

		return false;
	}

	/**
	 * Cancel recurring subscription
	 *
	 * @param $row
	 *
	 * @return bool
	 * @throws Exception
	 *
	 * @since 1.0
	 */
	public function refund($row)
	{
		[$apiUrl, $apiUser, $apiPassword, $apiSignature] = $this->getNvpApiParameters();

		if (!$apiUser || !$apiPassword || !$apiSignature)
		{
			Factory::getApplication()->enqueueMessage('Cancel Recurring Subscription is not supported for the payment method you are using', 'error');

			return false;
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_URL, $apiUrl);
		curl_setopt(
			$curl,
			CURLOPT_POSTFIELDS,
			http_build_query([
				'USER'          => $apiUser,
				'PWD'           => $apiPassword,
				'SIGNATURE'     => $apiSignature,
				'VERSION'       => '108',
				'METHOD'        => 'RefundTransaction',
				'TRANSACTIONID' => $row->transaction_id,
				'REFUNDTYPE'    => 'Full',
			])
		);

		$response = curl_exec($curl);
		curl_close($curl);

		$nvp = $this->deformatNVP($response);

		if ($nvp['ACK'] == 'Success')
		{
			return true;
		}
		Factory::getApplication()->enqueueMessage($nvp['L_LONGMESSAGE0'], 'error');

		return false;
	}

	/**
	 * Get NvpApi Parameters
	 *
	 * @return array
	 */
	private function getNvpApiParameters()
	{
		if ($this->mode)
		{
			$apiUrl       = 'https://api-3t.paypal.com/nvp';
			$apiUser      = $this->params->get('paypal_api_user');
			$apiPassword  = $this->params->get('paypal_api_password');
			$apiSignature = $this->params->get('paypal_api_signature');
		}
		else
		{
			$apiUrl       = 'https://api-3t.sandbox.paypal.com/nvp';
			$apiUser      = $this->params->get('paypal_api_user_sandbox');
			$apiPassword  = $this->params->get('paypal_api_password_sandbox');
			$apiSignature = $this->params->get('paypal_api_signature_sandbox');
		}

		return [$apiUrl, $apiUser, $apiPassword, $apiSignature];
	}

	/**
	 * Extract response from PayPal into array
	 *
	 * @param $response
	 *
	 * @return array
	 */
	private function deformatNVP($response)
	{
		$nvp = [];

		parse_str(urldecode($response), $nvp);

		return $nvp;
	}

	/**
	 * Method to check if API Credentials is entered into the payment plugin parameters
	 */
	private function isApiCredentialsEntered()
	{
		[$apiUrl, $apiUser, $apiPassword, $apiSignature] = $this->getNvpApiParameters();

		return $apiUser && $apiPassword && $apiSignature;
	}
}
