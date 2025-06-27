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
use Joomla\CMS\Language\Text;

/**
 * EShop Component Voucher Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelVoucher extends EShopModel
{

	public function store(&$data)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Check duplicated voucher
		$query->select('COUNT(*)')
			->from('#__eshop_vouchers')
			->where('voucher_code = ' . $db->quote($data['voucher_code']));

		if ($data['id'])
		{
			$query->where('id != ' . intval($data['id']));
		}

		$db->setQuery($query);

		if ($db->loadResult())
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_VOUCHER_EXISTED'), 'error');
			$mainframe->redirect('index.php?option=com_eshop&task=voucher.edit&cid[]=' . $data['id']);
		}

		if ($data['voucher_start_date'] == '')
		{
			$data['voucher_start_date'] = '0000-00-00 00:00:00';
		}

		if ($data['voucher_end_date'] == '')
		{
			$data['voucher_end_date'] = '0000-00-00 00:00:00';
		}

		parent::store($data);

		return true;
	}

	/**
	 * Method to remove vouchers
	 *
	 * @access    public
	 * @return boolean True on success
	 * @since     1.5
	 */
	public function delete($cid = [])
	{
		//Remove voucher history
		if (count($cid))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->delete('#__eshop_voucherhistory')
				->where('voucher_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);
			$db->execute();
		}
		parent::delete($cid);
	}
}