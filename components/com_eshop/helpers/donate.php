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

class EShopDonate
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
		$currency     = EShopCurrency::getInstance();
		$session      = Factory::getApplication()->getSession();
		$donateAmount = $session->get('donate_amount');

		if ($donateAmount > 0)
		{
			$totalData[] = [
				'name'  => 'donate_amount',
				'title' => Text::_('ESHOP_DONATE_AMOUNT'),
				'text'  => $currency->format($donateAmount),
				'value' => $donateAmount,
			];

			$total += $donateAmount;
		}
	}
}