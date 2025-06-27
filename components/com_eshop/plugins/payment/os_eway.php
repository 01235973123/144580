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

/**
 * Eway payment class
 *
 */
class os_eway extends EShopOmnipayPayment
{

	protected $omnipayPackage = 'Eway_Direct';

	/**
	 * Constructor
	 *
	 * @param   Registry  $params
	 * @param   array     $config
	 */
	public function __construct($params, $config = ['type' => 1])
	{
		$config['params_map'] = [
			'customerId' => 'eway_customer_id',
			'testMode'   => 'eway_mode',
		];

		parent::__construct($params, $config);
	}
}