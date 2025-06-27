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
use Joomla\Registry\Registry;

class EShopPayment
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
		$session       = Factory::getApplication()->getSession();
		$paymentMethod = $session->get('payment_method');
		$currency      = EShopCurrency::getInstance();
		$db            = Factory::getDbo();
		$query         = $db->getQuery(true);
		$query->select('params')
			->from('#__eshop_payments')
			->where('name = ' . $db->quote($paymentMethod));
		$db->setQuery($query);
		$paymentPlugin = $db->loadObject();

		if (is_object($paymentPlugin))
		{
			$params               = new Registry($paymentPlugin->params);
			$paymentFee           = $params->get('payment_fee');
			$paymentFeePercentage = $params->get('payment_fee_percentage');
			$paymentFeeMinTotal   = $params->get('payment_fee_min_total');
			$paymentFeeTotal      = 0;

			if (!$paymentFeeMinTotal || ($paymentFeeMinTotal > 0 && $total <= $paymentFeeMinTotal))
			{
				if ($paymentMethod == 'os_paypal')
				{
					$paymentFeeTotal = ($total + $paymentFee) / (1 - $paymentFeePercentage / 100) - $total;
				}
				else
				{
					if ($paymentFee != 0)
					{
						$paymentFeeTotal += $paymentFee;
					}

					if ($paymentFeePercentage != 0)
					{
						$paymentFeeTotal += $paymentFeePercentage * $total / 100;
					}
				}
			}

			if ($paymentFeeTotal != 0)
			{
				$totalData[] = [
					'name'  => 'payment_fee',
					'title' => Text::_('ESHOP_PAYMENT_FEE'),
					'text'  => $currency->format($paymentFeeTotal),
					'value' => $paymentFeeTotal,
				];

				if ($params->get('taxclass_id'))
				{
					$tax      = new EShopTax(EShopHelper::getConfig());
					$taxRates = $tax->getTaxRates($paymentFeeTotal, $params->get('taxclass_id'));

					foreach ($taxRates as $taxRate)
					{
						if (!isset($taxes[$taxRate['tax_rate_id']]))
						{
							$taxes[$taxRate['tax_rate_id']] = $taxRate['amount'];
						}
						else
						{
							$taxes[$taxRate['tax_rate_id']] += $taxRate['amount'];
						}
					}
				}

				$total += $paymentFeeTotal;
			}
		}
	}
}