<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

class os_authnet extends OSFPaymentOmnipay
{
	protected $omnipayPackage = 'AuthorizeNet_AIM';


	/**
	 * The parameters which will be passed to payment gateway for processing payment
	 *
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * Success or not
	 *
	 * @var boolean
	 */
	protected $success = false;

	/**
	 * Result code of the operation
	 *
	 * @var string
	 */
	protected $resultCode;

	/**
	 * Subscription ID
	 *
	 * @var string
	 */
	protected $subscriptionId;

	/**
	 * Return code of the operation
	 *
	 * @var string
	 */
	protected $code;

	/**
	 * Result text of the operation
	 *
	 * @var string
	 */
	protected $text;

	/**
	 * Post data sent from Authorize.net (via Slient Post)
	 *
	 * @var array
	 */
	protected $notificationData = [];
	/**
	 * Constructor
	 *
	 * @param JRegistry $params
	 * @param array     $config
	 */
	public function __construct($params, $config = array('type' => 1))
	{
		$config['params_map'] = array(
			'apiLoginId'     => 'x_login',
			'transactionKey' => 'x_tran_key',
			'developerMode'  => 'authnet_mode'
		);

		parent::__construct($params, $config);
	}

	/**
	 * Pass additional gateway data to payment gateway
	 *
	 * @param AbstractRequest $request
	 * @param JTable          $row
	 * @param array           $data
	 */
	protected function beforeRequestSend($request, $row, $data)
	{
		if($row->campaign_id > 0)
		{
			$db     = Factory::getContainer()->get('db');
			$query  = $db->getQuery(true);
			$query->select('*')
				  ->from('#__jd_campaigns')
				  ->where('id='.$row->campaign_id);
			$db->setQuery($query);
			$campaign = $db->loadObject();
			if ($campaign->authorize_api_login && $campaign->authorize_transaction_key)
			{
				$request->setApiLoginId($campaign->authorize_api_login);
				$request->setTransactionKey($campaign->authorize_transaction_key);
			}
		}
		parent::beforeRequestSend($request, $row, $data);
	}

	/**
	 * Check to see whether this payment gateway support recurring payment
	 *
	 */
	public function getEnableRecurring()
	{
		return 1;
	}
}