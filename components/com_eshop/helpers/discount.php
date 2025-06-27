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

use Joomla\CMS\Language\Text;

class EShopDiscount
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
		if (EShopHelper::getConfigValue('enable_checkout_discount'))
		{
			$discountRange    = EShopHelper::getConfigValue('discount_range');
			$discountRangeArr = explode(';', $discountRange);
			$cost             = 0;
			$cart             = new EShopCart();
			$subTotal         = $cart->getSubTotal();

			$checkoutDiscountType = EShopHelper::getConfigValue('checkout_discount_type', 'total');

			if ($checkoutDiscountType == 'quantity')
			{
				$quantityRange    = EShopHelper::getConfigValue('quantity_range');
				$quantityRangeArr = explode(';', $quantityRange);

				if (count($quantityRangeArr) && count($discountRangeArr))
				{
					$quantity = $cart->countProducts();

					for ($i = 0; $n = count($quantityRangeArr), $i < ($n - 1); $i++)
					{
						if ($quantity >= $quantityRangeArr[$i] && $quantity <= $quantityRangeArr[$i + 1])
						{
							if (strpos($discountRangeArr[$i], '%'))
							{
								$percentageCost = str_replace('%', '', $discountRangeArr[$i]);
								$cost           = round($subTotal * $percentageCost / 100, 2);
							}
							else
							{
								$cost = $discountRangeArr[$i];
							}
							break;
						}
						else
						{
							continue;
						}
					}

					if ($i == ($n - 1) && $quantity > $quantityRangeArr[$i])
					{
						if (strpos($discountRangeArr[$i], '%'))
						{
							$percentageCost = str_replace('%', '', $discountRangeArr[$i]);
							$cost           = round($subTotal * $percentageCost / 100, 2);
						}
						else
						{
							$cost = $discountRangeArr[$i];
						}
					}
				}
			}
			else
			{
				$totalRange    = EShopHelper::getConfigValue('total_range');
				$totalRangeArr = explode(';', $totalRange);

				if (count($totalRangeArr) && count($discountRangeArr))
				{
					for ($i = 0; $n = count($totalRangeArr), $i < ($n - 1); $i++)
					{
						if ($subTotal >= $totalRangeArr[$i] && $subTotal <= $totalRangeArr[$i + 1])
						{
							if (strpos($discountRangeArr[$i], '%'))
							{
								$percentageCost = str_replace('%', '', $discountRangeArr[$i]);
								$cost           = round($subTotal * $percentageCost / 100, 2);
							}
							else
							{
								$cost = $discountRangeArr[$i];
							}
							break;
						}
						else
						{
							continue;
						}
					}

					if ($i == ($n - 1) && $subTotal > $totalRangeArr[$i])
					{
						if (strpos($discountRangeArr[$i], '%'))
						{
							$percentageCost = str_replace('%', '', $discountRangeArr[$i]);
							$cost           = round($subTotal * $percentageCost / 100, 2);
						}
						else
						{
							$cost = $discountRangeArr[$i];
						}
					}
				}
			}

			if ($cost != 0)
			{
				$currency    = EShopCurrency::getInstance();
				$totalData[] = [
					'name'  => 'checkout_discount',
					'title' => Text::_('ESHOP_CHECKOUT_DISCOUNT'),
					'text'  => $currency->format(-$cost),
					'value' => -$cost,
				];
				$total       -= $cost;
			}

			//Apply tax for checkout discount
			if (EShopHelper::getConfigValue('tax_class') > 0)
			{
				$tax = new EShopTax(EShopHelper::getConfig());

				$taxRates = $tax->getTaxRates($cost, EShopHelper::getConfigValue('tax_class'));

				foreach ($taxRates as $taxRate)
				{
					if (!isset($taxes[$taxRate['tax_rate_id']]))
					{
						$taxes[$taxRate['tax_rate_id']] = -($taxRate['amount']);
					}
					else
					{
						$taxes[$taxRate['tax_rate_id']] -= $taxRate['amount'];
					}
				}
			}
		}
	}
}