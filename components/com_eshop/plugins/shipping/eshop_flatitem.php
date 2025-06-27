<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop - Flat Item Shipping
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class eshop_flatitem extends eshop_shipping
{

	/**
	 *
	 * Constructor function
	 */
	public function __construct()
	{
		parent::setName('eshop_flatitem');
		parent::__construct();
	}

	/**
	 *
	 * Function tet get quote for flat item shipping
	 *
	 * @param   array   $addressData
	 * @param   object  $params
	 */
	public function getQuote($addressData, $params)
	{
		$db        = Factory::getDbo();
		$query     = $db->getQuery(true);
		$cart      = new EShopCart();
		$tax       = new EShopTax(EShopHelper::getConfig());
		$currency  = EShopCurrency::getInstance();
		$rates     = explode("\r\n", $params->get('rates'));
		$quoteData = [];
		$cart      = new EShopCart();
		$total     = $cart->getTotal();
		$minTotal  = $params->get('min_total', 0);
		$status    = true;

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
					$cost = $rate[1];
				}
				$cost = $cart->countProducts() * $cost;
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

					$quoteData['flatitem_' . $geozoneId] = [
						'name'        => 'eshop_flatitem.flatitem_' . $geozoneId,
						'title'       => Text::_('PLG_ESHOP_FLATITEM_TITLE'),
						'desc'        => $geozone->geozone_name,
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
				->where('name = "eshop_flatitem"');
			$db->setQuery($query);
			$row        = $db->loadObject();
			$methodData = [
				'name'     => 'eshop_flatitem',
				'title'    => Text::_('PLG_ESHOP_FLATITEM_TITLE'),
				'quote'    => $quoteData,
				'ordering' => $row->ordering,
				'error'    => false,
			];
		}

		return $methodData;
	}
}