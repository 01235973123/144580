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
class EShopModelManufacturers extends EShopModelList
{

	public function __construct($config)
	{
		$config['search_fields']       = ['b.manufacturer_name', 'b.manufacturer_desc'];
		$config['translatable']        = true;
		$config['translatable_fields'] = ['manufacturer_name', 'manufacturer_alias', 'manufacturer_desc'];

		parent::__construct($config);
	}
}