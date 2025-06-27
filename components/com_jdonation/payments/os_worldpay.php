<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

class os_worldpay extends OSFPaymentOmnipay
{
	protected $omnipayPackage = 'WorldPay';

	/**
	 * Constructor
	 *
	 * @param JRegistry $params
	 * @param array     $config
	 */
	public function __construct($params, $config = array())
	{
		$config['params_map'] = array(
			'installationId'   => 'wp_installation_id',
			'callbackPassword' => 'wp_callback_password',
			'testMode'         => 'worldpay_mode'
		);

		parent::__construct($params, $config);
	}
}