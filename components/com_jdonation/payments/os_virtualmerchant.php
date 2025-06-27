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

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;

class os_virtualmerchant extends OSFPayment
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
	public function processPayment($row, $data)
	{
		require_once JPATH_ROOT . '/components/com_jdonation/payments/virtualmerchant/ConvergeApi.php';
		$app    = Factory::getApplication();
		$Itemid = $app->input->getInt('Itemid');
		$data['x_description']	= $data['item_name'];
		$data['x_exp_date']		= str_pad($data['exp_month'], 2, '0', STR_PAD_LEFT) . '/' . substr($data['exp_year'], 2, 2);
		$data['amount']			= round($data['gateway_amount'], 2);
		//Set more parameters for the payment gateway to user
		$processor = new \markroland\Converge\ConvergeApi(
			$this->params->get('ssl_merchant_ID'),
			$this->params->get('ssl_user_id'),
			$this->params->get('ssl_pin'),
			$this->params->get('virtual_mode', 0)
		);

		if ($row->state)
        {
            $state   = DonationHelper::getStateCode($row->country, $row->state);
        }
        else
        {
            $state = '';
        }
		
		$response = $processor->ccsale(
			array
			(
				//Payment information
				"ssl_card_number"        => $data['x_card_num'],
				"ssl_exp_date"           => $data['x_exp_date'],
				"ssl_cvv2cvc2"           => $data['x_card_code'],
				"ssl_description"        => $data['x_description'],
				"ssl_amount"             => $data['gateway_amount'],

				//  ###########  CUSTOMER DETAILS  ################
				"ssl_first_name"         => $row->first_name,
				"ssl_last_name"          => $row->last_name,
				"ssl_avs_address"        => $row->address,
				"ssl_city"               => $row->city,
				"ssl_state"              => $state,
				"ssl_phone"              => $row->phone,
				"ssl_avs_zip"            => $row->zip,
				"ssl_company"            => '',
				"ssl_email"              => $row->email,
				"ssl_country"            => $data['country'],

				//  ###########3  SHIPPING DETAILS  ################3
				"ssl_ship_to_first_name" => $row->first_name,
				"ssl_ship_to_last_name"  => $row->last_name,
				"ssl_ship_to_address1"   => $row->address,
				"ssl_ship_to_city"       => $row->city,
				"ssl_ship_to_state"      => $state,
				"ssl_ship_to_country"    => $data['country'],
				"ssl_ship_to_zip"        => $row->zip,
				"ssl_ship_to_phone"      => $row->phone,
			)
		);

		$id = $row->id;
		if ($response['ssl_result'] == 0)
		{
			$row						= Table::getInstance('jdonation', 'Table');
			$row->load($id);
			if ((int)$row->published == 0)
			{
				$this->onPaymentSuccess($row, $response['ssl_txn_id']);
			}
			$app->redirect(Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')) . Route::_(DonationHelperRoute::getDonationCompleteRoute($row->id, $row->campaign_id, $Itemid), false));

		}
		else
		{
			$session = Factory::getSession();
			$session->set('omnipay_payment_error_reason', $response['errorMessage']);
			//echo $response['errorMessage'];die();
			$app->redirect(Route::_('index.php?option=com_jdonation&view=failure&id=' . $row->id . '&Itemid='.$Itemid, false));
		}
	}
}