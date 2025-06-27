<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop - Item Geo Zones Shipping
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class eshop_itemgeozones extends eshop_shipping
{

	/**
	 *
	 * Constructor function
	 */
	public function __construct()
	{
		parent::setName('eshop_itemgeozones');
		parent::__construct();
	}

	/**
	 *
	 * Function tet get quote for item geozones shipping
	 *
	 * @param   array   $addressData
	 * @param   object  $params
	 */
	public function getQuote($addressData, $params)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$cart  = new EShopCart();

		if (!$params->get('geozone_id'))
		{
			$status = true;
		}
		else
		{
			$query->select('COUNT(*)')
				->from('#__eshop_geozonezones')
				->where('geozone_id = ' . intval($params->get('geozone_id')))
				->where('country_id = ' . intval($addressData['country_id']))
				->where('(zone_id = 0 OR zone_id = ' . intval($addressData['zone_id']) . ')');
			$db->setQuery($query);

			if ($db->loadResult())
			{
				$status = true;
			}
			else
			{
				$status = false;
			}

			//Check geozone postcode status
			if ($status)
			{
				$gzpStatus = EShopHelper::getGzpStatus($params->get('geozone_id'), $addressData['postcode']);

				if (!$gzpStatus)
				{
					$status = false;
				}
			}
		}

		//Check customer groups
		$customerGroups = $params->get('customer_groups');

		if (!empty($customerGroups))
		{
			$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

			if (!in_array($customerGroupId, $customerGroups))
			{
				$status = false;
			}
		}

		//Check disabled products
		$disabledProducts = $params->get('disabled_products');

		if (!empty($disabledProducts))
		{
			foreach ($cart->getCartData() as $product)
			{
				if (in_array($product['product_id'], $disabledProducts))
				{
					$status = false;
					break;
				}
			}
		}

		//Check min total
		$total    = $cart->getTotal();
		$minTotal = $params->get('min_total', 0);

		if ($minTotal > 0 && $total >= $minTotal)
		{
			$status = false;
		}

		$quantity    = $cart->countProducts(true);
		$minQuantity = $params->get('min_quantity', 0);

		if ($minQuantity > 0 && $quantity >= $minQuantity)
		{
			$status = false;
		}
		
		$weight = $cart->getWeight();
		$minWeight = $params->get('min_weight', 0);
			
		if ($minWeight > 0 && $weight >= $minWeight)
		{
			$status = false;
		}

		$methodData = [];

		//Find the Geo Zone first
		$query->clear()
			->select('geozone_id')
			->from('#__eshop_geozonezones')
			->where('country_id = ' . intval($addressData['country_id']))
			->where('(zone_id = 0 OR zone_id = ' . intval($addressData['zone_id']) . ')');
		$db->setQuery($query);
		$geozoneIds = $db->loadColumn();

		$foundGeozoneId = 0;
		foreach ($geozoneIds as $geozoneId)
		{
			$gzpStatus = EShopHelper::getGzpStatus($geozoneId, $addressData['postcode']);

			if ($gzpStatus)
			{
				$foundGeozoneId = $geozoneId;
				break;
			}
		}

		if ($status && $foundGeozoneId > 0)
		{
			$packageFee = $params->get('package_fee', 0);
			$cost       = 0;

			foreach ($cart->getCartData() as $product)
			{
				if ($product['product_shipping'])
				{
					$optionData = $product['option_data'];

					if (count($optionData))
					{
						for ($j = 0; $m = count($optionData), $j < $m; $j++)
						{
							if ($optionData[$j]['shipping'])
							{
								$productShippingCost            = 0;
								$productShippingCostGeozones    = $product['product_shipping_cost_geozones'];
								$productShippingCostGeozonesArr = explode('|', $productShippingCostGeozones);

								for ($i = 0; $n = count($productShippingCostGeozonesArr), $i < $n; $i++)
								{
									$productShippingCostGeozonesElement = explode(':', $productShippingCostGeozonesArr[$i]);
									if (isset($productShippingCostGeozonesElement[0]) && $productShippingCostGeozonesElement[0] == $foundGeozoneId && isset($productShippingCostGeozonesElement[1]))
									{
										$productShippingCost = $productShippingCostGeozonesElement[1];
										break;
									}
								}

								if (!$productShippingCost)
								{
									$productShippingCost = $product['product_shipping_cost'];
								}

								if ($params->get('depend_quantity', 0))
								{
									$cost += $productShippingCost * $product['quantity'];
								}
								else
								{
									$cost += $productShippingCost;
								}

								break;
							}
						}
					}
					else
					{
						$productShippingCost            = 0;
						$productShippingCostGeozones    = $product['product_shipping_cost_geozones'];
						$productShippingCostGeozonesArr = explode('|', $productShippingCostGeozones);

						for ($i = 0; $n = count($productShippingCostGeozonesArr), $i < $n; $i++)
						{
							$productShippingCostGeozonesElement = explode(':', $productShippingCostGeozonesArr[$i]);
							if (isset($productShippingCostGeozonesElement[0]) && $productShippingCostGeozonesElement[0] == $foundGeozoneId && isset($productShippingCostGeozonesElement[1]))
							{
								$productShippingCost = $productShippingCostGeozonesElement[1];
								break;
							}
						}

						if (!$productShippingCost)
						{
							$productShippingCost = $product['product_shipping_cost'];
						}

						if ($params->get('depend_quantity', 0))
						{
							$cost += $productShippingCost * $product['quantity'];
						}
						else
						{
							$cost += $productShippingCost;
						}
					}
				}
			}

			$cost      = $cost + $packageFee;
			$tax       = new EShopTax(EShopHelper::getConfig());
			$currency  = EShopCurrency::getInstance();
			$quoteData = [];
			$query->clear();
			$query->select('*')
				->from('#__eshop_shippings')
				->where('name = "eshop_itemgeozones"');
			$db->setQuery($query);
			$row = $db->loadObject();

			if ($params->get('show_shipping_cost_with_tax', 1))
			{
				$text = $currency->format($tax->calculate($cost, $params->get('taxclass_id'), EShopHelper::getConfigValue('tax')));
			}
			else
			{
				$text = $currency->format($cost);
			}

			$quoteData['item'] = [
				'name'        => 'eshop_itemgeozones.item',
				'title'       => Text::_('PLG_ESHOP_ITEM_GEOZONES_TITLE'),
				'desc'        => Text::_('PLG_ESHOP_ITEM_GEOZONES_DESC'),
				'cost'        => $cost,
				'taxclass_id' => $params->get('taxclass_id'),
				'text'        => $text,
			];

			$methodData = [
				'name'     => 'eshop_itemgeozones',
				'title'    => Text::_('PLG_ESHOP_ITEM_GEOZONES_TITLE'),
				'quote'    => $quoteData,
				'ordering' => $row->ordering,
				'error'    => false,
			];
		}

		return $methodData;
	}
}