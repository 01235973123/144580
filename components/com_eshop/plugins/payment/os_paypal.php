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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;

class os_paypal extends os_payment
{
	/**
	 * Constructor functions, init some parameter
	 *
	 * @param   object  $config
	 */
	public function __construct($params)
	{
		$config = [
			'type'                  => 0,
			'show_card_type'        => false,
			'show_card_holder_name' => false,
		];

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
		$this->setData('business', $params->get('paypal_id'));
		$this->setData('rm', 2);
		$this->setData('cmd', '_cart');
		$this->setData('upload', '1');

		if ($params->get('shipping_label', '0'))
		{
			$this->setData('no_shipping', 0);
			$this->setData('address_override', 1);
		}
		else
		{
			$this->setData('no_shipping', 1);
		}

		$this->setData('no_note', 1);
		$locale = Factory::getLanguage()->getTag();
		$locale = str_replace('-', '_', $locale);
		$this->setData('lc', $locale);
		$this->setData('currency_code', $params->get('paypal_currency', 'USD'));
		$this->setData('charset', 'utf-8');
		$this->setData('tax', 0);
	}

	/**
	 * Process Payment
	 *
	 * @param   array  $data
	 */
	public function processPayment($data)
	{
		$Itemid = Factory::getApplication()->input->getInt('Itemid', 0);
		
		if (!$Itemid)
		{
			$Itemid = EShopRoute::getDefaultItemId();
		}
		
		$siteUrl      = EShopHelper::getSiteUrl();
		$countryInfo  = EShopHelper::getCountry($data['payment_country_id']);
		$countProduct = 1;
		//Do the currency convert to USD if the selected currency does not supported by PayPal
		$rate                   = 1;
		$availableCurrenciesArr = [
			'AUD',
			'CAD',
			'EUR',
			'GBP',
			'JPY',
			'USD',
			'NZD',
			'CHF',
			'HKD',
			'SGD',
			'SEK',
			'DKK',
			'PLN',
			'NOK',
			'HUF',
			'CZK',
			'ILS',
			'MXN',
			'BRL',
			'MYR',
			'PHP',
			'TWD',
			'THB',
			'TRY',
			'RUB',
		];

		if (!in_array($data['currency_code'], $availableCurrenciesArr))
		{
			$currency              = EShopCurrency::getInstance();
			$rate                  = $currency->getExchangedValue('USD') / $currency->getExchangedValue($data['currency_code']);
			$data['currency_code'] = 'USD';
		}

		foreach ($data['products'] as $product)
		{
			$this->setData('item_name_' . $countProduct, $product['product_name']);
			$this->setData('item_number_' . $countProduct, $product['product_sku']);
			$this->setData('amount_' . $countProduct, round($product['price'] * $rate, 2));
			$this->setData('quantity_' . $countProduct, $product['quantity']);
			$this->setData('weight_' . $countProduct, $product['weight']);

			$countProduct++;
		}

		if ($data['discount_amount_cart'])
		{
			$this->setData('discount_amount_cart', round($data['discount_amount_cart'] * $rate, 2));
		}

		$langLink	= EShopHelper::getLangLink();

		$this->setData('currency_code', $data['currency_code']);
		$this->setData('custom', $data['order_id']);
		$this->setData('return', $siteUrl . 'index.php?option=com_eshop&view=checkout&layout=complete&order_number=' . $data['order_number'] . $langLink . '&Itemid=' . $Itemid);
		$this->setData('cancel_return', $siteUrl . 'index.php?option=com_eshop&task=checkout.cancelOrder&order_number=' . $data['order_number'] . $langLink . '&Itemid=' . $Itemid);
		$this->setData('notify_url', $siteUrl . 'index.php?option=com_eshop&task=checkout.verifyPayment&payment_method=os_paypal' . $langLink . '&Itemid=' . $Itemid);
		$this->setData('address1', $data['payment_address_1']);
		$this->setData('address2', $data['payment_address_2']);
		$this->setData('city', $data['payment_city']);
		$this->setData('country', $countryInfo->iso_code_2);
		$this->setData('first_name', $data['payment_firstname']);
		$this->setData('last_name', $data['payment_lastname']);
		$this->setData('state', $data['payment_zone_name']);
		$this->setData('zip', $data['payment_postcode']);
		$this->setData('email', $data['email']);
		$this->submitPost();
	}

	/**
	 * Process payment
	 */
	public function verifyPayment()
	{
		$ret      = $this->validate();
		$currency = EShopCurrency::getInstance();

		if ($ret)
		{
			$row    = Table::getInstance('Eshop', 'Order');
			$id     = $this->postData['custom'];
			$amount = $this->postData['mc_gross'];

			if ($amount < 0)
			{
				$this->onPaymentFailure($row);
				return false;
			}

			$row->load($id);

			if (!$row->id)
			{
				$this->onPaymentFailure($row);
				return false;
			}

			// Validate payment status
			if ($row->order_status_id == EShopHelper::getConfigValue('complete_status_id'))
			{
				$this->onPaymentFailure($row);
				return false;
			}

			// Validate receiver account
			$payPalId      = strtoupper($this->params->get('paypal_id'));
			$receiverEmail = strtoupper($this->postData['receiver_email']);
			$receiverId    = strtoupper($this->postData['receiver_id']);
			$business      = strtoupper($this->postData['business']);

			if ($receiverEmail != $payPalId && $receiverId != $payPalId && $business != $payPalId)
			{
				$this->onPaymentFailure($row);
				return false;
			}

			// Validate currency
			$availableCurrenciesArr = [
				'AUD',
				'CAD',
				'EUR',
				'GBP',
				'JPY',
				'USD',
				'NZD',
				'CHF',
				'HKD',
				'SGD',
				'SEK',
				'DKK',
				'PLN',
				'NOK',
				'HUF',
				'CZK',
				'ILS',
				'MXN',
				'BRL',
				'MYR',
				'PHP',
				'TWD',
				'THB',
				'TRY',
				'RUB',
			];
			$receiverCurrency       = strtoupper($this->postData['mc_currency']);
			$orderCurrency          = strtoupper($row->currency_code);

			if (!in_array($orderCurrency, $availableCurrenciesArr))
			{
				$orderCurrency = 'USD';
			}

			if ($receiverCurrency != $orderCurrency)
			{
				$this->onPaymentFailure($row);
				return false;
			}

			// Validate payment amount
			if (!in_array(strtoupper($row->currency_code), $availableCurrenciesArr))
			{
				$total = $currency->convert($row->total, EShopHelper::getConfigValue('default_currency_code'), 'USD');
			}
			else
			{
				$total = round($row->total * $row->currency_exchanged_value, 2);
			}

			if (abs($total - $amount) > 1)
			{
				$this->onPaymentFailure($row);
				return false;
			}

			$transactionId = $this->postData['txn_id'];
			$this->onPaymentSuccess($row, $transactionId);

			return true;
		}
		else
		{
			$this->onPaymentFailure($row);
			return false;
		}
	}

	/**
	 * Validate the post data from paypal to our server
	 *
	 * @return string
	 */
	protected function validate()
	{
		JLoader::register('PaypalIPN', JPATH_ROOT . '/components/com_eshop/plugins/payment/paypal/PayPalIPN.php');

		$ipn = new PaypalIPN;

		// Use sandbox URL if test mode is configured
		if (!$this->mode)
		{
			$ipn->useSandbox();
		}

		// Disable use custom certs
		$ipn->usePHPCerts();

		$this->postData = $_POST;

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
}