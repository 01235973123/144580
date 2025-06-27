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
class EShopModelFields extends EShopModelList
{
	/**
	 *
	 * Constructor
	 *
	 * @param   array  $config
	 */
	public function __construct($config)
	{
		$config['search_fields']       = ['a.name', 'b.title'];
		$config['translatable']        = true;
		$config['translatable_fields'] = [
			'title',
			'description',
			'place_holder',
			'default_values',
			'`values`',
			'validation_error_message',
		];
		parent::__construct($config);
	}
}