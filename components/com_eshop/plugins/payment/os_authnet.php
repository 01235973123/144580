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

use Joomla\Registry\Registry;

class os_authnet extends EShopOmnipayPayment
{
	protected $omnipayPackage = 'AuthorizeNet_AIM';

	/**
	 * Constructor
	 *
	 * @param   Registry  $params
	 * @param   array     $config
	 */
	public function __construct($params, $config = ['type' => 1])
	{
		$config['params_map'] = [
			'apiLoginId'     => 'x_login',
			'transactionKey' => 'x_tran_key',
			'developerMode'  => 'authnet_mode',
		];

		parent::__construct($params, $config);
	}
}