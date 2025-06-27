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
class EShopModelMessages extends EShopModelList
{
	public function __construct($config)
	{
		$config['translatable']        = true;
		$config['translatable_fields'] = ['message_value'];
		$config['state_vars']          = [
			'search'           => ['', 'string', 1],
			'filter_order'     => ['a.message_title', 'cmd', 1],
			'filter_order_Dir' => ['', 'cmd', 1],
			'filter_state'     => ['', 'cmd', 1],
		];
		parent::__construct($config);
	}
}