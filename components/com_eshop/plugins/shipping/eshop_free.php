<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop - Free Shipping
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class eshop_free extends eshop_shipping
{

	/**
	 *
	 * Constructor function
	 */
	public function __construct()
	{
		parent::setName('eshop_free');
		parent::__construct();
	}

	/**
	 *
	 * Function tet get quote for free shipping
	 *
	 * @param   array   $addressData
	 * @param   object  $params
	 */
	public function getQuote($addressData, $params)
	{
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$session = Factory::getApplication()->getSession();
		$cart    = new EShopCart();

		$couponCode            = $session->get('coupon_code');
		$couponForFreeShipping = 0;

		if ($couponCode != '')
		{
			$query->select('coupon_for_free_shipping')
				->from('#__eshop_coupons')
				->where('coupon_code = ' . $db->quote($session->get('coupon_code')))
				->where('published = 1');
			$db->setQuery($query);
			$couponForFreeShipping = $db->loadResult();
		}

		if ($couponForFreeShipping == '1')
		{
			$status = true;
		}
		else
		{
			if (!$params->get('geozone_id'))
			{
				$status = true;
			}
			else
			{
				$query->clear()
					->select('COUNT(*)')
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

			if ($minTotal > 0 && $total < $minTotal)
			{
				$status = false;
			}

			$quantity    = $cart->countProducts(true);
			$minQuantity = $params->get('min_quantity', 0);

			if ($minQuantity > 0 && $quantity < $minQuantity)
			{
				$status = false;
			}
			
			$weight = $cart->getWeight();
			$minWeight = $params->get('min_weight', 0);
			
			if ($minWeight > 0 && $weight < $minWeight)
			{
				$status = false;
			}
		}

		$methodData = [];

		if ($status)
		{
			$currency  = EShopCurrency::getInstance();
			$quoteData = [];
			$query->clear();
			$query->select('*')
				->from('#__eshop_shippings')
				->where('name = "eshop_free"');
			$db->setQuery($query);
			$row  = $db->loadObject();
			$text = '';

			if ($params->get('show_free_cost', 0))
			{
				$text = $currency->format(0.00);
			}

			$quoteData['free'] = [
				'name'        => 'eshop_free.free',
				'title'       => Text::_('PLG_ESHOP_FREE_TITLE'),
				'desc'        => Text::_('PLG_ESHOP_FREE_DESC'),
				'cost'        => 0.00,
				'taxclass_id' => $params->get('taxclass_id'),
				'text'        => $text,
			];

			$methodData = [
				'name'     => 'eshop_free',
				'title'    => Text::_('PLG_ESHOP_FREE_TITLE'),
				'quote'    => $quoteData,
				'ordering' => $row->ordering,
				'error'    => false,
			];
		}

		return $methodData;
	}
}