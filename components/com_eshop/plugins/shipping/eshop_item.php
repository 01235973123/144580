<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop - Item Shipping
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class eshop_item extends eshop_shipping
{

	/**
	 *
	 * Constructor function
	 */
	public function __construct()
	{
		parent::setName('eshop_item');
		parent::__construct();
	}

	/**
	 *
	 * Function tet get quote for item shipping
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
			$packageFee = $params->get('package_fee', 0);
			$cost       = 0;
			foreach ($cart->getCartData() as $product)
			{
				if ($product['product_shipping'])
				{
					$optionData = $product['option_data'];

					if (count($optionData))
					{
						for ($i = 0; $n = count($optionData), $i < $n; $i++)
						{
							if ($optionData[$i]['shipping'])
							{
								if ($params->get('depend_quantity', 0))
								{
									$additionalQuantityCost = $params->get('additional_quantity_cost', 0);

									if ($additionalQuantityCost > 0)
									{
										$cost += $product['product_shipping_cost'];

										if ($product['quantity'] > 1)
										{
											$cost += $additionalQuantityCost * ($product['quantity'] - 1);
										}
									}
									else
									{
										$cost += $product['product_shipping_cost'] * $product['quantity'];
									}
								}
								else
								{
									$cost += $product['product_shipping_cost'];
								}

								break;
							}
						}
					}
					else
					{
						if ($params->get('depend_quantity', 0))
						{
							$additionalQuantityCost = $params->get('additional_quantity_cost', 0);

							if ($additionalQuantityCost > 0)
							{
								$cost += $product['product_shipping_cost'];

								if ($product['quantity'] > 1)
								{
									$cost += $additionalQuantityCost * ($product['quantity'] - 1);
								}
							}
							else
							{
								$cost += $product['product_shipping_cost'] * $product['quantity'];
							}
						}
						else
						{
							$cost += $product['product_shipping_cost'];
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
				->where('name = "eshop_item"');
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
				'name'        => 'eshop_item.item',
				'title'       => Text::_('PLG_ESHOP_ITEM_TITLE'),
				'desc'        => Text::_('PLG_ESHOP_ITEM_DESC'),
				'cost'        => $cost,
				'taxclass_id' => $params->get('taxclass_id'),
				'text'        => $text,
			];

			$methodData = [
				'name'     => 'eshop_item',
				'title'    => Text::_('PLG_ESHOP_ITEM_TITLE'),
				'quote'    => $quoteData,
				'ordering' => $row->ordering,
				'error'    => false,
			];
		}

		return $methodData;
	}
}