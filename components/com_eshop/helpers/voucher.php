<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2013 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class EShopVoucher
{
	/**
	 *
	 * Function to get Costs, passed by reference to update
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getCosts(&$totalData, &$total, &$taxes)
	{
		$session     = Factory::getApplication()->getSession();
		$currency    = EShopCurrency::getInstance();
		$voucherData = $this->getVoucherData($session->get('voucher_code'));

		if (count($voucherData))
		{
			if ($voucherData['voucher_amount'] > $total)
			{
				$amount = $total;
			}
			else
			{
				$amount = $voucherData['voucher_amount'];
			}

			$totalData[] = [
				'name'  => 'voucher',
				'title' => sprintf(Text::_('ESHOP_VOUCHER'), $session->get('voucher_code')),
				'text'  => $currency->format(-$amount),
				'value' => -$amount,
			];

			$total -= $amount;
		}
	}

	/**
	 *
	 * Function to get information for a specific voucher
	 *
	 * @param   string  $code
	 *
	 * @return array
	 */
	public function getVoucherData($code)
	{
		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());

		$query->select('*')
			->from('#__eshop_vouchers')
			->where('voucher_code = ' . $db->quote($code))
			->where('(voucher_start_date = ' . $nullDate . ' OR voucher_start_date IS NULL OR voucher_start_date <= ' . $currentDate . ')')
			->where('(voucher_end_date = ' . $nullDate . ' OR voucher_end_date IS NULL OR voucher_end_date >= ' . $currentDate . ')')
			->where('published = 1');
		$db->setQuery($query);
		$voucher = $db->loadObject();

		if (!$voucher)
		{
			return [];
		}

		$voucherAmount = $voucher->voucher_amount;

		//Get total used amount of this voucher
		$query->clear()
			->select('ABS(SUM(amount))')
			->from('#__eshop_voucherhistory')
			->where('voucher_id = ' . intval($voucher->id));
		$db->setQuery($query);
		$usedAmount = $db->loadResult();

		if ($voucherAmount <= $usedAmount)
		{
			return [];
		}

		return [
			'voucher_id'         => $voucher->id,
			'voucher_code'       => $voucher->voucher_code,
			'voucher_amount'     => $voucherAmount - $usedAmount,
			'voucher_start_date' => $voucher->voucher_start_date,
			'voucher_end_date'   => $voucher->voucher_end_date,
		];
	}

	/**
	 *
	 * Function to add voucher history
	 *
	 * @param   int    $voucherId
	 * @param   int    $orderId
	 * @param   int    $userId
	 * @param   float  $amount
	 */
	public function addVoucherHistory($voucherId, $orderId, $userId, $amount)
	{
		$row               = Table::getInstance('Eshop', 'Voucherhistory');
		$row->id           = '';
		$row->order_id     = $orderId;
		$row->voucher_id   = $voucherId;
		$row->user_id      = $userId;
		$row->amount       = $amount;
		$row->created_date = gmdate('Y-m-d H:i:s');
		$row->store();
	}
}