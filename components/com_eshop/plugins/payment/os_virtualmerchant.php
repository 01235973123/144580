<?php
/**
 * @version            1.0.0
 * @package            Joomla
 * @subpackage         EShop Shopping Cart
 * @author             Giang Dinh Truong
 * @copyright          Copyright (C) 2012 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class os_virtualmerchant extends os_payment
{
	public function __construct($params, $config = array('type' => 1))
	{
		parent::__construct($params, $config);
	}

	/**
	 * Process payment with the posted data
	 *
	 * @param array $data array
	 *
	 * @return void
	 */
	public function processPayment($data)
	{
		require_once JPATH_ROOT . '/components/com_eshop/plugins/payment/virtualmerchant/ConvergeApi.php';
		$app    = JFactory::getApplication();
		$data['x_description']	= JText::sprintf('ESHOP_PAYMENT_FOR_ORDER', $data['order_id']);
		$data['x_exp_date']		= str_pad($data['exp_month'], 2, '0', STR_PAD_LEFT) . '/' . substr($data['exp_year'], 2, 2);
		$data['amount']			= round($data['total'], 2);
		//Set more parameters for the payment gateway to user
		$processor = new \markroland\Converge\ConvergeApi(
			$this->params->get('ssl_merchant_ID'),
			$this->params->get('ssl_user_id'),
			$this->params->get('ssl_pin'),
			$this->params->get('virtual_mode', 0)
		);
		
		$response = $processor->ccsale(
			array
			(
				//Payment information
				"ssl_card_number"        => $data['card_number'],
				"ssl_exp_date"           => $data['x_exp_date'],
				"ssl_cvv2cvc2"           => $data['cvv_code'],
				"ssl_description"        => $data['x_description'],
				"ssl_amount"             => $data['amount'],

				//  ###########  CUSTOMER DETAILS  ################
				"ssl_first_name"         => $data['payment_firstname'],
				"ssl_last_name"          => $data['payment_lastname'],
				"ssl_avs_address"        => $data['payment_address_1'],
				"ssl_city"               => $data['payment_city'],
				"ssl_state"              => $data['payment_zone_name'],
				"ssl_phone"              => $data['payment_telephone'],
				"ssl_avs_zip"            => $data['payment_postcode'],
				"ssl_company"            => $data['payment_company'],
				"ssl_email"              => $data['payment_email'],
				"ssl_country"            => $data['payment_country_name'],

				//  ###########3  SHIPPING DETAILS  ################3
				"ssl_ship_to_first_name" => $data['shipping_firstname'],
				"ssl_ship_to_last_name"  => $data['shipping_lastname'],
				"ssl_ship_to_address1"   => $data['shipping_address_1'],
				"ssl_ship_to_city"       => $data['shipping_city'],
				"ssl_ship_to_state"      => $data['shipping_zone_name'],
				"ssl_ship_to_country"    => $data['shipping_country_name'],
				"ssl_ship_to_zip"        => $data['shipping_postcode'],
				"ssl_ship_to_phone"      => $data['shipping_telephone'],
			)
		);


		if ($response['ssl_result'] == 0)
		{
			$row = JTable::getInstance('Eshop', 'Order');
			$row->load($data['order_id']);
			$row->transaction_id = $response['ssl_txn_id'];
			$row->order_status_id = EshopHelper::getConfigValue('complete_status_id');
			$row->store();
			EshopHelper::completeOrder($row);
			JPluginHelper::importPlugin('eshop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterCompleteOrder', array($row));
			//Send confirmation email here
			if (EshopHelper::getConfigValue('order_alert_mail'))
			{
				EshopHelper::sendEmails($row);
			}
			$app->redirect(JRoute::_('index.php?option=com_eshop&view=checkout&layout=complete', false));

		}
		else
		{
			$session = JFactory::getSession();
			$session->set('omnipay_payment_error_reason', $response['errorMessage']);
			$app->redirect(JRoute::_('index.php?option=com_eshop&view=checkout&layout=failure&id=' . $data['order_id'], false));
		}
	}
}