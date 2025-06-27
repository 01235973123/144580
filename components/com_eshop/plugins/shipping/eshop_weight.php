<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop - Weight Shipping
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class eshop_weight extends eshop_shipping
{

	/**
	 *
	 * Constructor function
	 */
	public function __construct()
	{
		parent::setName('eshop_weight');
		parent::__construct();
	}

	/**
	 *
	 * Function tet get quote for Weight Shipping
	 *
	 * @param   array   $addressData
	 * @param   object  $params
	 */
	public function getQuote($addressData, $params)
	{
		$db         = Factory::getDbo();
		$query      = $db->getQuery(true);
		$cart       = new EShopCart();
		$tax        = new EShopTax(EShopHelper::getConfig());
		$currency   = EShopCurrency::getInstance();
		$weight     = EShopWeight::getInstance();
		$cartWeight = $cart->getWeight();
		$rates      = explode("\r\n", $params->get('rates'));
		$quoteData  = [];
		$cart       = new EShopCart();
		$total      = $cart->getTotal();
		$minTotal   = $params->get('min_total', 0);
		$status     = true;

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
		
		$minWeight = $params->get('min_weight', 0);
			
		if ($minWeight > 0 && $cartWeight >= $minWeight)
		{
			$status = false;
		}

		if ($status)
		{
			for ($i = 0; $n = count($rates), $i < $n; $i++)
			{
				$status    = false;
				$rate      = explode("|", $rates[$i]);
				$geozoneId = $rate[0];
				if ($geozoneId)
				{
					$query->clear();
					$query->select('COUNT(*)')
						->from('#__eshop_geozonezones')
						->where('geozone_id = ' . intval($geozoneId))
						->where('country_id = ' . intval($addressData['country_id']))
						->where('(zone_id = 0 OR zone_id = ' . intval($addressData['zone_id']) . ')');
					$db->setQuery($query);
					if ($db->loadResult())
					{
						$status = true;
					}

					//Check geozone postcode status
					if ($status)
					{
						$gzpStatus = EShopHelper::getGzpStatus($geozoneId, $addressData['postcode']);

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

				$cost = 0;
				if ($status)
				{
					for ($j = 1; $m = count($rate), $j < $m; $j++)
					{
						$data = explode(";", $rate[$j]);
						if (isset($data[0]) && $data[0] >= $cartWeight && isset($data[1]))
						{
							$cost = $data[1];
							break;
						}
					}
				}
				if ($cost)
				{
					$packageFee = $params->get('package_fee', 0);
					$cost       = $cost + $packageFee;
					$geozone    = EShopHelper::getGeozone($geozoneId);

					if ($params->get('show_shipping_cost_with_tax', 1))
					{
						$text = $currency->format($tax->calculate($cost, $params->get('taxclass_id'), EShopHelper::getConfigValue('tax')));
					}
					else
					{
						$text = $currency->format($cost);
					}

					$quoteData['weight_' . $geozoneId] = [
						'name'        => 'eshop_weight.weight_' . $geozoneId,
						'title'    	  => Text::_($geozone->geozone_name),
						'desc'        => Text::_($geozone->geozone_name) . ($params->get('show_weight') ? ' (' . Text::_(
									'PLG_ESHOP_WEIGHT_WEIGHT'
								) . ': ' . $weight->format($cartWeight, EShopHelper::getConfigValue('weight_id')) . ')' : ''),
						'cost'        => $cost,
						'taxclass_id' => $params->get('taxclass_id'),
						'text'        => $text,
					];
				}
			}
		}
		$methodData = [];
		if ($quoteData)
		{
			$query->clear();
			$query->select('*')
				->from('#__eshop_shippings')
				->where('name = "eshop_weight"');
			$db->setQuery($query);
			$row        = $db->loadObject();
			$methodData = [
				'name'     => 'eshop_weight',
				'title'    => Text::_('PLG_ESHOP_WEIGHT_TITLE'),
				'quote'    => $quoteData,
				'ordering' => $row->ordering,
				'error'    => false,
			];
		}

		return $methodData;
	}
}