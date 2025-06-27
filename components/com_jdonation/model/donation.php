<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

class DonationModelDonation extends OSFModel
{

	/**
	 * Process Donation
	 *
	 * @param $data
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function processDonation($data)
	{
		$db                     = Factory::getContainer()->get('db');
		$query                  = $db->getQuery(true);
		$config                 = DonationHelper::getConfig();
		$user                   = Factory::getApplication()->getIdentity();
		$donationType           = isset($data['donation_type']) ? $data['donation_type'] : 'onetime';
		$paymentMethod          = $data['payment_method'];
		$data['transaction_id'] = strtoupper(UserHelper::genRandomPassword());

		PluginHelper::importPlugin('jdonation');
		Factory::getApplication()->triggerEvent('onBeforeStoreDonor', array());
		//$row                    = Table::getInstance('Donor', 'DonationTable');
		require_once JPATH_ADMINISTRATOR.'/components/com_jdonation/table/donor.php';
		$row					= new DonationTableDonor($db);

		//Convert the amount to the format supported by PHP
		$dec_point				= isset($config->dec_point) ? $config->dec_point : '.';
		$thousands_sep			= isset($config->thousands_sep) ? $config->thousands_sep : ',';
		$data['amount']			= str_replace($thousands_sep, '', $data['amount']);
		$data['amount']			= str_replace($dec_point, '.', $data['amount']);

		$row->bind($data);

		$row->published			= 0;
		$row->created_date		= gmdate('Y-m-d H:i:s');
		$row->user_id			= $user->id;

		if ($donationType == 'onetime' || $paymentMethod == 'os_offline')
		{
			$row->donation_type = 'I';
		}
		else
		{
			$row->donation_type = 'R';
		}

		if($config->store_ip_address == 1) 
		{
            $row->ip_address	= $_SERVER['REMOTE_ADDR'];
        }
		else
		{
		    $row->ip_address	= '';
        }

		if (!$user->id && $config->registration_integration && !empty($data['username']) && !empty($data['password1']))
		{
			if($config->create_account_when_donation_active == 0)
			{
				$row->user_id	= DonationHelper::saveRegistration($data);
			}
			else
			{
				$row->user_id	= 0;
				$data['user_password'] = DonationHelper::encrypt($data['password1']);
				$row->user_password	= $data['user_password'];
			}
		}

		$campaignTitle			= '';
		$campaignCurrency		= '';
		if ($row->campaign_id)
		{
			$query->select('title, donation_type, currency')
					->from('#__jd_campaigns')
					->where('id=' . (int) $row->campaign_id);
			$db->setQuery($query);
			$rowCampaign = $db->loadObject();
			
			$campaignTitle				= $rowCampaign->title;
			$campaignDonationType		= $rowCampaign->donation_type;
			$campaignCurrency			= $rowCampaign->currency;
		}
		else
		{
			//Disabled recurring but Donation type is recurring 
			if(!$config->enable_recurring && $row->donation_type == "R")
			{
				throw new \Exception(Text::_('JD_DONATION_TYPE_IS_NOT_SUPPORTED'), 500);
			}

			$query->select('id, title, donation_type')
					->from('#__jd_campaigns')
					->where('published = 1')
					->order('ordering');

			$db->setQuery($query, 0, 1);
			$rowCampaign = $db->loadObject();
			if ($rowCampaign)
			{
				$row->campaign_id = $rowCampaign->id;
				$campaignTitle    = $rowCampaign->title;
				$campaignDonationType = $rowCampaign->donation_type;
			}
		}

		switch($campaignDonationType)
		{
			case "1":
				//this campaign only allows one time donation but this is recurring donation
				if($row->donation_type == "R")
				{
					throw new \Exception(Text::_('JD_DONATION_TYPE_IS_NOT_SUPPORTED'), 500);
				}
			break;
			case "2":
				//this campaign only allows recurring donation but this is one time donation
				if($row->donation_type == "I")
				{
					throw new \Exception(Text::_('JD_DONATION_TYPE_IS_NOT_SUPPORTED'), 500);
				}
			break;
		}

		//Save the active language
		if (Factory::getApplication()->getLanguageFilter())
		{
			$row->language = Factory::getApplication()->getLanguage()->getTag();
		}
		else
		{
			$row->language = '*';
		}
		$currency               = $config->currency;
		if($config->activate_campaign_currency && $campaignCurrency != "")
		{
			$currency			= $campaignCurrency;
		}
		
        $converted_amount       = $row->amount;
        $currency_code          = $row->currency_code;
		if($currency_code == "")
		{
			$currency_code		= $currency;
		}
        if($currency_code != "" && $currency_code != $currency && $config->convert_currency_before_donation)
		{
            $converted_amount   = DonationHelper::convertAmountToDefaultCurrency($row->amount,$currency_code);
			$row->currency_code			= $currency;
	        $row->currency_converted	= $currency_code;
			$data['currency_code']		= $currency;
        }
		else
		{
			$converted_amount			= $row->amount;
			$row->currency_code			= $currency_code;
	        $row->currency_converted	= $currency_code;
			$data['currency_code']		= $currency_code;
		}
        $row->amount_converted  = (float)$row->amount;
        $row->amount            = (float)$converted_amount;

        // Store the payment gateway fee as part of donation amount, too
		//print_r($data);die();
        if($config->pay_payment_gateway_fee)
		{
            $pay_payment_gateway_fee = (int)$data['pay_payment_gateway_fee'];
        }
		else
		{
            $pay_payment_gateway_fee = 1;
        }

		$query->clear();
		$query->select('params')
				->from('#__jd_payment_plugins')
				->where('name=' . $db->quote($paymentMethod))
				->where('published = 1');
		$db->setQuery($query);
		$plugin = $db->loadObject();
		if (!$plugin)
		{
			throw new Exception(Text::sprintf('The payemnt method %s is not available', $paymentMethod), 403);
		}

		$params = new Registry($plugin->params);

		$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
		$paymentFeePercent = (float) $params->get('payment_fee_percent');

		//If the payment plugin has different payment gateway fee, we will use those parameters
		if (($paymentFeeAmount != 0 || $paymentFeePercent != 0) && $pay_payment_gateway_fee == 1)
		{
			$payment_plugin_fee		= round($paymentFeeAmount + $row->amount * $paymentFeePercent / 100, 2);
			$data['gateway_amount'] = round($row->amount + $payment_plugin_fee, 2);
            $data['payment_fee']    = $payment_plugin_fee;
		}
		//no payment fee on Offline payment
        elseif ($config->convenience_fee && $pay_payment_gateway_fee == 1 && $paymentMethod != "os_offline")
        {
            $data['gateway_amount'] = round($row->amount * (1 + $config->convenience_fee / 100), 2);
            $data['payment_fee']    = round($row->amount * $config->convenience_fee / 100, 2);
        }
        else
        {
            $data['gateway_amount'] = round($row->amount, 2);
            $data['payment_fee']    = 0;
        }
        $data['amount']         = $row->amount;
		$row->payment_fee		= $data['payment_fee'];
		$row->receive_user_id	= (int) $row->receive_user_id;
		$row->r_times			= (int) $row->r_times;
		$row->dedicate_type		= (int) $row->dedicate_type;
		$row->dedicate_name		= (string) $row->dedicate_name;
		$row->dedicate_email	= (string) $row->dedicate_email;
		//$row->category_id		= (string) $row->category_id;
		if($row->mollie_recurring_start_date == "")
		{
			$row->mollie_recurring_start_date = "0000-00-00";
		}
		$row->params			= "";
		$row->id				= 0;
		$row->mollie_customer_id= (string) $row->mollie_customer_id;
		$row->gateway_customer_id= (string) $row->gateway_customer_id;
		if(!$row->store())
		{
			throw new Exception( $row->getError() );
		}

        /* Accept privacy consent to avoid Joomla requires users to accept it again */
        if (PluginHelper::isEnabled('system', 'privacyconsent') && $row->user_id > 0 && $config->show_privacy)
        {
            DonationHelper::acceptPrivacyConsent($row);
        }

        //print_r($data);die();
		DonationHelper::storeFormData($row->id, $data);
		PluginHelper::importPlugin('jdonation');
		Factory::getApplication()->triggerEvent('onAfterStoreDonor', array($row));
		$itemName               = Text::_('JD_ONLINE_DONATION_PAYMENT_TITLE');
		$itemName               = str_replace('[CAMPAIGN_TITLE]', $campaignTitle, $itemName);
		$data['item_name']      = $itemName;


		// Store ID of donation record into session to use on donation complete page
		Factory::getSession()->set('id', $row->id);

		$country				= empty($data['country']) ? $config->default_country : $data['country'];
		$data['country']		= DonationHelper::getCountryCode($country);

		$currency				= empty($data['currency_code']) ? $config->currency : $data['currency_code'];
		if ($donationType == 'onetime')
		{
			require_once JPATH_COMPONENT . '/payments/' . $paymentMethod . '.php';
			$paymentClass		= new $paymentMethod($params);

			if (method_exists($paymentClass, 'getSupportedCurrencies'))
			{
				$currencies = $paymentClass->getSupportedCurrencies();
				if (!in_array($currency, $currencies))
				{
					$data['gateway_amount'] = DonationHelper::convertAmountToUSD($data['gateway_amount'], $currency);
					$currency               = 'USD';
				}
			}
			$data['currency'] = $currency;
			$paymentClass->processPayment($row, $data);
		}
		else
		{
			if ($paymentMethod == 'os_authnet')
			{
				$paymentMethod = 'os_authnet_arb';
			}
			require_once JPATH_COMPONENT . '/payments/' . $paymentMethod . '.php';
			$paymentClass = new $paymentMethod($params);

			if (method_exists($paymentClass, 'getSupportedCurrencies'))
			{
				$currencies = $paymentClass->getSupportedCurrencies();
				if (!in_array($currency, $currencies))
				{
					$data['gateway_amount'] = DonationHelper::convertAmountToUSD($data['gateway_amount'], $currency);
					$currency               = 'USD';
				}
			}

			$data['currency'] = $currency;
			$paymentClass->processRecurringPayment($row, $data);
		}
	}

	public function processPayment($id)
	{
		$db		= Factory::getContainer()->get('db');
		$config = DonationHelper::getConfig();
		$row	= Table::getInstance('Donor', 'DonationTable');
		$row->load($id);
		$data	= json_decode(json_encode($row), true);
		$data['gateway_amount'] = $data['amount'];
		$paymentMethod          = $data['payment_method'];
		$donationType           = isset($data['donation_type']) ? $data['donation_type'] : 'onetime';
		$currency				= empty($data['currency_code']) ? $config->currency : $data['currency_code'];

		$query = $db->getQuery(true);
		$query->select('params')
				->from('#__jd_payment_plugins')
				->where('name=' . $db->quote($paymentMethod))
				->where('published = 1');
		$db->setQuery($query);
		$plugin = $db->loadObject();
		if (!$plugin)
		{
			throw new Exception(Text::sprintf('The payemnt method %s is not available', $paymentMethod), 403);
		}

		$params = new Registry($plugin->params);
		if ($donationType == 'onetime' || $donationType == 'I')
		{
			require_once JPATH_COMPONENT . '/payments/' . $paymentMethod . '.php';
			$paymentClass		= new $paymentMethod($params);

			if (method_exists($paymentClass, 'getSupportedCurrencies'))
			{
				$currencies = $paymentClass->getSupportedCurrencies();
				if (!in_array($currency, $currencies))
				{
					$data['gateway_amount'] = DonationHelper::convertAmountToUSD($data['gateway_amount'], $currency);
					$currency               = 'USD';
				}
			}
			$data['currency'] = $currency;
			$paymentClass->processPayment($row, $data);
		}
		else
		{
			if ($paymentMethod == 'os_authnet')
			{
				$paymentMethod = 'os_authnet_arb';
			}
			require_once JPATH_COMPONENT . '/payments/' . $paymentMethod . '.php';
			$paymentClass = new $paymentMethod($params);

			if (method_exists($paymentClass, 'getSupportedCurrencies'))
			{
				$currencies = $paymentClass->getSupportedCurrencies();
				if (!in_array($currency, $currencies))
				{
					$data['gateway_amount'] = DonationHelper::convertAmountToUSD($data['gateway_amount'], $currency);
					$currency               = 'USD';
				}
			}

			$data['currency'] = $currency;
			$paymentClass->processRecurringPayment($row, $data);
		}
	}

	public function cancelRecurringDonation($row)
    {
        $method = DonationHelper::loadPaymentMethod($row->payment_method);

        /* @var os_authnet $method */

        $ret = false;

        if (method_exists($method, 'cancelDonation'))
        {
            $ret = $method->cancelDonation($row);
        }


        if ($ret)
        {
            DonationHelper::cancelRecurringDonation($row->id);
        }

        return $ret;
    }
}
