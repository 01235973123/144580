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

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelCustomergroups extends EShopModelList
{
	public function __construct($config)
	{
		$config['state_vars']          = ['filter_order' => ['b.customergroup_name', 'string', 1]];
		$config['translatable']        = true;
		$config['search_fields']       = ['b.customergroup_name'];
		$config['translatable_fields'] = ['customergroup_name'];
		parent::__construct($config);
	}
}