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
class EShopModelLengths extends EShopModelList
{
	public function __construct($config)
	{
		$config['search_fields']       = ['b.length_name', 'b.length_unit'];
		$config['translatable']        = true;
		$config['translatable_fields'] = ['length_name', 'length_unit'];
		parent::__construct($config);
	}
}