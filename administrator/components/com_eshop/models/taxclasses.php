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
class EShopModelTaxclasses extends EShopModelList
{

	public function __construct($config)
	{
		$config['search_fields'] = ['a.taxclass_name'];
		$config['state_vars']    = ['filter_order' => ['a.taxclass_name', 'string', 1]];

		parent::__construct($config);
	}
}