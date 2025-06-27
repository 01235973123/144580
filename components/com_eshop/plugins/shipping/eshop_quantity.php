<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop - Quantity Shipping
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class eshop_quantity extends eshop_shipping
{

	/**
	 *
	 * Constructor function
	 */
	public function __construct()
	{
		parent::setName('eshop_quantity');
		parent::__construct();
	}

	/**
	 *
	 * Function tet get quote for quantity shipping
	 *
	 * @param   array   $addressData
	 * @param   object  $params
	 */
	public function getQuote($addressData, $params)
	{
		//Check geozone condition
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
		if ($status)
		{
			$currency = EShopCurrency::getInstance();
			$tax      = new EShopTax(EShopHelper::getConfig());
			$rates    = explode("|", $params->get('rates'));
			for ($i = 0; $n = count($rates), $i < $n; $i++)
			{
				$data = explode(";", $rates[$i]);
				if (isset($data[0]) && $data[0] >= $quantity && isset($data[1]))
				{
					$cost = $data[1];
					break;
				}
			}
			if (isset($cost) && $cost > 0)
			{
				$packageFee = $params->get('package_fee', 0);
				$cost       = $cost + $packageFee;

				$query->clear();
				$query->select('*')
					->from('#__eshop_shippings')
					->where('name = "eshop_quantity"');
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

				$quoteData['quantity'] = [
					'name'        => 'eshop_quantity.quantity',
					'title'       => Text::_('PLG_ESHOP_QUANTITY_TITLE'),
					'desc'        => Text::_('PLG_ESHOP_QUANTITY_DESC'),
					'cost'        => $cost,
					'taxclass_id' => $params->get('taxclass_id'),
					'text'        => $text,
				];

				$methodData = [
					'name'     => 'eshop_quantity',
					'title'    => Text::_('PLG_ESHOP_QUANTITY_TITLE'),
					'quote'    => $quoteData,
					'ordering' => $row->ordering,
					'error'    => false,
				];
			}
		}

		return $methodData;
	}
}