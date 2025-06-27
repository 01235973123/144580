<?php

/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;

class os_paypal extends OSFPayment
{
	/**
	 * Constructor functions, init some parameter
	 *
	 * @param JRegistry $params
	 * @param array     $config
	 */
	public function __construct($params, $config = array())
	{
		parent::__construct($params, $config);
		$this->mode = $params->get('paypal_mode');
		if ($this->mode)
		{
			$this->url = 'https://www.paypal.com/cgi-bin/webscr';
		}
		else
		{
			$this->url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}
		$this->setParameter('business', $params->get('paypal_id'));
		$this->setParameter('rm', 2);
		$this->setParameter('cmd', '_donations');
		$this->setParameter('no_shipping', 1);
		$this->setParameter('no_note', 1);
		$locale = $params->get('paypal_locale','');

		if ($locale == '')
		{
			if (Multilanguage::isEnabled())
			{
				$locale = Factory::getApplication()->getLanguage()->getTag();
				$locale = str_replace("-","_",$locale);
			}
			else
			{
				$locale = 'en_US';
			}
		}
		
		$this->setParameter('lc', $locale);
		$this->setParameter('charset', 'utf-8');
	}


	/**
	 * Check to see whether this payment gateway support recurring payment
	 *
	 */
	public function getEnableRecurring()
	{
		return 1;
	}

	/**
	 * Process Payment
	 *
	 * @param object $row
	 * @param array  $data
	 */
	public function processPayment($row, $data)
	{
		$db = Factory::getContainer()->get('db');
		$Itemid  = Factory::getApplication()->input->getInt('Itemid', 0);
		$siteUrl = Uri::base();
		$tag   = Factory::getApplication()->getLanguage()->getTag();
		$query = $db->getQuery(true);
	    $query->select('`sef`')
		->from('#__languages')
		->where('published = 1')
		->where('lang_code=' . $db->quote($tag));
	    $db->setQuery($query, 0, 1);
	    $langLink = '&lang=' . $db->loadResult();
		// Use Campaign PayPal account if it is setup in the campaign
		if ($row->campaign_id)
		{
			$query = $db->getQuery(true);
			$query->select('paypal_id')
				->from('#__jd_campaigns')
				->where('id=' . $row->campaign_id);
			$db->setQuery($query);
			if ($paypalId = $db->loadResult())
			{
				$this->setParameter('business', $paypalId);
			}
		}

		//checking currency
        $availableCurrenciesArr = array('AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD', 'NZD', 'CHF', 'HKD', 'SGD', 'SEK', 'DKK', 'PLN', 'NOK', 'HUF', 'CZK', 'ILS', 'MXN', 'BRL', 'MYR', 'PHP', 'TWD', 'THB', 'TRY', 'RUB');
        if (!in_array($data['currency'], $availableCurrenciesArr))
        {
            //convert to USD
            //$exchange = DonationHelper::get_conversion($data['currency'], 'USD');
            //$data['gateway_amount'] = $data['gateway_amount']*$exchange;
            //$data['currency'] = 'USD';
            //store into database
            //$db->setQuery("Update #__jd_donors set currency_code = '".$data['currency']."',amount_converted = '".$data['gateway_amount']."' where id = '$row->id'");
            //$db->execute();
        }else{
            //$db->setQuery("Update #__jd_donors set currency_code = '".$data['currency']."',amount_converted = '".$data['gateway_amount']."' where id = '$row->id'");
            //$db->execute();
        }

		if ($row->state)
        {
            $state   = DonationHelper::getStateCode($row->country, $row->state);
        }
        else
        {
            $state = '';
        }

		$this->setParameter('currency_code', $data['currency']);
		$this->setParameter('item_name', $data['item_name']);
		$this->setParameter('amount', $data['gateway_amount']);
		$this->setParameter('custom', $row->id);
		$this->setParameter('return', Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')) . Route::_(DonationHelperRoute::getDonationCompleteRoute($row->id, $row->campaign_id, $Itemid), false));

		$this->setParameter('cancel_return', $siteUrl . 'index.php?option=com_jdonation&view=cancel' . ($row->campaign_id > 0 ? '&campaign_id=' . $row->campaign_id : '') . '&Itemid=' . $Itemid);
		$this->setParameter('notify_url', $siteUrl . 'index.php?option=com_jdonation&task=payment_confirm&payment_method=os_paypal'.$langLink);
		$this->setParameter('address1', $row->address);
		$this->setParameter('address2', $row->address2);
		$this->setParameter('city', $row->city);
		$this->setParameter('country', $data['country']);
		$this->setParameter('first_name', $row->first_name);
		$this->setParameter('last_name', $row->last_name);
		$this->setParameter('state', $state);
		$this->setParameter('zip', $row->zip);
		$this->setParameter('email', $row->email);
		//$this->setParameter('campaign_id',$row->campaign_id);
		$this->setParameter('language',$row->language);
		$this->renderRedirectForm();
	}

	/**
	 * Process recurring payment
	 *
	 * @param object $row
	 * @param array  $data
	 */
	public function processRecurringPayment($row, $data)
	{
		$Itemid  = Factory::getApplication()->input->getInt('Itemid', 0);
		$siteUrl = Uri::base();

		// Use Campaign PayPal account if it is setup in the campaign
		if ($row->campaign_id)
		{
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('paypal_id')
				->from('#__jd_campaigns')
				->where('id=' . $row->campaign_id);
			$db->setQuery($query);
			if ($paypalId = $db->loadResult())
			{
				$this->setParameter('business', $paypalId);
			}
		}

		$this->setParameter('item_name', $data['item_name']);
		$this->setParameter('currency_code', $data['currency']);
		$this->setParameter('custom', $row->id);
		$this->setParameter('return', Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')) . Route::_(DonationHelperRoute::getDonationCompleteRoute($row->id, $row->campaign_id, $Itemid), false));
		$this->setParameter('cancel_return', $siteUrl . 'index.php?option=com_jdonation&view=cancel' . ($row->campaign_id > 0 ? '&campaign_id=' . $row->campaign_id : '') . '&Itemid=' . $Itemid);
		$this->setParameter('notify_url', $siteUrl . 'index.php?option=com_jdonation&task=recurring_donation_confirm&payment_method=os_paypal');
		$this->setParameter('cmd', '_xclick-subscriptions');
		$this->setParameter('src', 1);
		$this->setParameter('sra', 1);
		$this->setParameter('a3', round($data['gateway_amount'], 2));
		$this->setParameter('address1', $row->address);
		$this->setParameter('address2', $row->address2);
		$this->setParameter('city', $row->city);
		$this->setParameter('email', $row->email);
		$this->setParameter('country', $data['country']);
		$this->setParameter('first_name', $row->first_name);
		$this->setParameter('last_name', $row->last_name);
		$this->setParameter('state', $row->state);
		$this->setParameter('zip', $row->zip);
		switch ($row->r_frequency)
		{
			case 'd':
				$p3 = 1;
				$t3 = 'D';
				break;
			case 'w' :
				$p3 = 1;
				$t3 = 'W';
				break;
			case 'b' :
				$p3 = 2;
				$t3 = 'W';
				break;
			case 'm' :
				$p3 = 1;
				$t3 = 'M';
				break;
			case 'q' :
				$p3 = 3;
				$t3 = 'M';
				break;
			case 's' :
				$p3 = 6;
				$t3 = 'M';
				break;
			case 'a' :
				$p3 = 1;
				$t3 = 'Y';
				break;
		}
		$this->setParameter('p3', $p3);
		$this->setParameter('t3', $t3);
		$this->setParameter('lc', 'US');
		if ($row->r_times > 1)
		{
			$this->setParameter('srt', $row->r_times);
		}
		if($row->r_times == 0)
		{
			$this->setParameter('srt', 0);
		}
		$this->renderRedirectForm();
	}

	/**
	 * Verify payment
	 *
	 * @return bool
	 */
	public function verifyPayment()
	{
		$db = Factory::getContainer()->get('db');
		$ret = $this->validate();
		if ($ret)
		{
			$id            = $this->notificationData['custom'];
			$transactionId = $this->notificationData['txn_id'];
			$amount        = $this->notificationData['mc_gross'];
			$currency      = $this->notificationData['mc_currency'];
			$paymentFee    = $this->notificationData['mc_fee'];
			$receiverEmail = $this->notificationData['receiver_email'];
			$receiverId    = $this->notificationData['receiver_id'];

			if ($amount < 0)
			{
				return false;
			}
			$row = Table::getInstance('jdonation', 'Table');
			$row->load($id);
			if (!$row->id)
			{
				return false;
			}

			//verify amount and currency
            $org_currency       = $row->currency_code;
            $amount_converted   = $row->amount;
            if((strtoupper($currency) == "") || (strtoupper($currency) != strtoupper($org_currency))){
                return false;
            }

            if(floatval($amount) < $amount_converted){
                return false;
            }

			if ($row->published == 0)
			{
				//$row->payment_fee = $paymentFee;
				$this->onPaymentSuccess($row, $transactionId);
			}
		}
	}

	/**
	 * Verify recurring payment
	 *
	 */
	public function verifyRecurringPayment()
	{
		$db	 = Factory::getContainer()->get('db');
		$ret = $this->validate();
		if ($ret)
		{
			$id            = $this->notificationData['custom'];
			$transactionId = $this->notificationData['txn_id'];
			$amount        = $this->notificationData['mc_gross'];
			$paymentFee    = $this->notificationData['mc_fee'];
			$txnType       = $this->notificationData['txn_type'];
			$subscrId      = $this->notificationData['subscr_id'];
			if ($amount < 0)
			{
				return false;
			}
			if ($transactionId && DonationHelper::isTransactionProcessed($transactionId))
			{
				return false;
			}

			$row = Table::getInstance('jdonation', 'Table');
			switch ($txnType)
			{
				case 'subscr_signup' :
					$row->load($id);

					if (!$row->id)
					{
						return false;
					}

					if ($row->published == 0)
					{
						$row->subscr_id = $subscrId;
						if($transactionId == "")
						{
							$transactionId = $subscrId;
						}
						$this->onPaymentSuccess($row, $transactionId);
					}
					break;
				case 'subscr_payment' :
					$row->load($id);
					if (!$row->id)
					{
						return false;
					}
					$row->payment_made = $row->payment_made + 1;
					$row->amount       = $amount;
					//$row->payment_fee  = $paymentFee;
					$row->store();

					$donorId = $row->id;

					//Create a new one time donation record and send emails when a recurring payment happens
					if ($row->payment_made > 1)
					{
						$row                = clone $row;
						$row->id            = 0;
						$row->donation_type = 'I';
						$row->created_date  = gmdate('Y-m-d H:i:s');
						$row->invoice_number = (int) $row->invoice_number + 1;

						$this->onPaymentSuccess($row, $transactionId);

						//update for other custom fields
						$mainDonorId = $id;
						
						if($row->id > 0)
						{
							$db->setQuery("Select * from #__jd_field_value where donor_id = '$mainDonorId'");
							$fields = $db->loadObjectList();
							if(count($fields))
							{
								foreach($fields as $field)
								{
									$db->setQuery("Insert into #__jd_field_value (id,field_id,donor_id,field_value) values (NULL,'$field->field_id','".(int)$row->id."',".$db->quote($field->field_value).")");
									$db->execute();
								}
							}
						}
					}
					break;
                case 'subscr_cancel':
                    DonationHelper::cancelRecurringDonation($id);
                    break;
			}
		}
	}

	/**
	 * Get list of supported currencies
	 *
	 * @return array
	 */
	public function getSupportedCurrencies()
	{
		return array(
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
			'USD'
		);
	}

	/**
	 * Validate the post data from paypal to our server
	 *
	 * @return string
	 */
	protected function validate()
	{
		if (function_exists('curl_init'))
		{
			return $this->validateIPN();
		}
		$this->notificationData = $_POST;

		$hostname = $this->mode ? 'www.paypal.com' : 'www.sandbox.paypal.com';
		$url      = 'ssl://' . $hostname;
		$port     = 443;
		$req      = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value)
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		$header = '';
		$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Host: $hostname:$port\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n";
		$header .= "User-Agent: Events Booking\r\n";
		$header .= "Connection: Close\r\n\r\n";

		$errNum   = '';
		$errStr   = '';
		$response = '';
		$fp       = fsockopen($url, $port, $errNum, $errStr, 30);

		if (!$fp)
		{
			$response = 'Could not open SSL connection to ' . $hostname . ':' . $port;
			$this->logGatewayData($response);

			return false;
		}

		fputs($fp, $header . $req);
		while (!feof($fp))
		{
			$response .= fgets($fp, 1024);
		}
		fclose($fp);


		$this->logGatewayData($response);

		if (!$this->mode || stristr($response, "VERIFIED"))
		{
			return true;
		}

		return false;
	}
	/**
	 * Validate PayPal IPN using PayPal library
	 *
	 * @return bool
	 */
	protected function validateIPN()
	{
		JLoader::register('PaypalIPN', JPATH_ROOT . '/components/com_jdonation/payments/paypal/PayPalIPN.php');

		$ipn = new PaypalIPN;
		// Use sandbox URL if test mode is configured
		if (!$this->mode)
		{
			$ipn->useSandbox();
		}
		// Disable use custom certs
		$ipn->usePHPCerts();

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
     * Cancel recurring subscription
     *
     * @param $row
     *
     * @return bool
     * @throws Exception
     *
     * @since 1.0
     */
    public function cancelDonation($row)
    {
        list($apiUrl, $apiUser, $apiPassword, $apiSignature) = $this->getNvpApiParameters();

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
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
            'USER'      => $apiUser,
            'PWD'       => $apiPassword,
            'SIGNATURE' => $apiSignature,
            'VERSION'   => '108',
            'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
            'PROFILEID' => $row->subscr_id,
            'ACTION'    => 'Cancel',
        ]));

        $response = curl_exec($curl);
        curl_close($curl);

        $nvp = $this->deformatNVP($response);

        if ($nvp['ACK'] == 'Success')
        {
            return true;
        }
        else
        {
            Factory::getApplication()->enqueueMessage($nvp['L_LONGMESSAGE0'], 'error');

            return false;
        }
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
        list($apiUrl, $apiUser, $apiPassword, $apiSignature) = $this->getNvpApiParameters();

        return $apiUser && $apiPassword && $apiSignature;
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
}
