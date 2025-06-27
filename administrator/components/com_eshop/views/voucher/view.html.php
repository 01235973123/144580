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

use Joomla\CMS\Factory;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewVoucher extends EShopViewForm
{
	/**
	 *
	 * @var $voucherHistories
	 */
	protected $voucherHistories;

	/**
	 *
	 * @var $nullDate
	 */
	protected $nullDate;

	public function _buildListArray(&$lists, $item)
	{
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$nullDate = $db->getNullDate();
		//Get history voucher
		$query->clear();
		$query->select('*')
			->from('#__eshop_voucherhistory')
			->where('voucher_id = ' . intval($item->id));
		$db->setQuery($query);
		$voucherHistories       = $db->loadObjectList();
		$this->voucherHistories = $voucherHistories;
		$this->lists            = $lists;
		$this->nullDate         = $nullDate;
	}
}