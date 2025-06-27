<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\IpHelper;

class os_virtualmerchant extends MPFPayment
{
	public function __construct($params, $config = ['type' => 1])
	{
		parent::__construct($params, $config);
	}

	/**
	 * Process payment with the posted data
	 *
	 * @param   array  $data  array
	 *
	 * @return void
	 */
	public function processPayment($row, $data)
	{
		require_once __DIR__ . '/virtualmerchant/ConvergeApi.php';

		$app    = Factory::getApplication();
		$Itemid = $app->input->getInt('Itemid');

		$data['x_description'] = $data['item_name'];
		$data['x_exp_date']    = str_pad($data['exp_month'], 2, '0', STR_PAD_LEFT) . substr($data['exp_year'], 2, 2);
		$data['amount']        = round($data['amount'], 2);

		//Set more parameters for the payment gateway to user

		$processor = new \markroland\Converge\ConvergeApi(
			$this->params->get('ssl_merchant_ID'),
			$this->params->get('ssl_user_id'),
			$this->params->get('ssl_pin'),
			$this->params->get('virtual_mode', 0)
		);

		$response = $processor->ccsale(
			[
				//Payment information
				"ssl_card_number"        => $data['x_card_num'],
				"ssl_exp_date"           => $data['x_exp_date'],
				"ssl_cvv2cvc2"           => $data['x_card_code'],
				"ssl_description"        => $data['x_description'],
				"ssl_amount"             => $data['amount'],

				//  ###########  CUSTOMER DETAILS  ################
				"ssl_first_name"         => $data['first_name'],
				"ssl_last_name"          => $data['last_name'],
				"ssl_avs_address"        => $data['address'],
				"ssl_city"               => $data['city'],
				"ssl_state"              => $data['state'],
				"ssl_phone"              => $data['phone'],
				"ssl_avs_zip"            => $data['zip'],
				"ssl_company"            => $data['organization'],
				"ssl_email"              => $data['email'],
				"ssl_country"            => $data['country'],
				"ssl_cardholder_ip"      => IpHelper::getIp(),

				//  ###########3  SHIPPING DETAILS  ################3
				"ssl_ship_to_first_name" => $data['first_name'],
				"ssl_ship_to_last_name"  => $data['last_name'],
				"ssl_ship_to_address1"   => $data['address'],
				"ssl_ship_to_city"       => $data['city'],
				"ssl_ship_to_state"      => $data['state'],
				"ssl_ship_to_country"    => $data['country'],
				"ssl_ship_to_zip"        => $data['zip'],
				"ssl_ship_to_phone"      => $data['phone'],
			]
		);

		$this->notificationData = $processor->debug;
		$this->logGatewayData('Debug Request');
		$this->notificationData = $response;
		$this->logGatewayData('Debug Response');

		if (isset($response['ssl_result']) && $response['ssl_result'] === '0')
		{
			$this->onPaymentSuccess($row, $response['ssl_txn_id']);
			$app->redirect($this->getPaymentCompleteUrl($row, $Itemid));
		}
		else
		{
			$this->setPaymentErrorMessage($response['errorMessage']);
			$app->redirect($this->getPaymentFailureUrl($row, $Itemid));
		}
	}
}