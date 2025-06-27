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

class EShopShipping
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
		$currency       = EShopCurrency::getInstance();
		$shippingMethod = Factory::getApplication()->getSession()->get('shipping_method');

		if (is_array($shippingMethod))
		{
			$totalData[] = [
				'name'  => 'shipping',
				'title' => $shippingMethod['title'],
				'text'  => $currency->format($shippingMethod['cost']),
				'value' => $shippingMethod['cost'],
			];

			if (!empty($shippingMethod['taxclass_id']))
			{
				$tax      = new EShopTax(EShopHelper::getConfig());
				$taxRates = $tax->getTaxRates($shippingMethod['cost'], $shippingMethod['taxclass_id']);

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

			$total += $shippingMethod['cost'];
		}
	}
}