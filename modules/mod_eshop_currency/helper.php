<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2011 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

abstract class modEShopCurrencyHelper
{
	/**
	 *
	 * Function to get Currencies
	 * @return currencies list
	 */
	public static function getCurrencies()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_currencies')
			->where('published = 1');
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}