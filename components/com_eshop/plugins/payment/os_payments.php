<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class os_payments
{
	public static $methods;

	/**
	 * Get list of payment methods
	 *
	 * @return array
	 */
	public static function getPaymentMethods()
	{
		if (self::$methods == null)
		{
			$session = Factory::getApplication()->getSession();
			$cart    = new EShopCart();
			$user    = Factory::getUser();

			if ($user->get('id') && $session->get('payment_address_id'))
			{
				$paymentAddress = EShopHelper::getAddress($session->get('payment_address_id'));
			}
			else
			{
				$guest          = $session->get('guest');
				$paymentAddress = $guest['payment'] ?? '';
			}

			$db             = Factory::getDbo();
			$query          = $db->getQuery(true);
			$shippingMethod = $session->get('shipping_method');

			if (is_array($shippingMethod))
			{
				$shippingMethodName    = $shippingMethod['name'];
				$shippingMethodNameArr = explode('.', $shippingMethodName);
				$query->select('params')
					->from('#__eshop_shippings')
					->where('name = "' . $shippingMethodNameArr[0] . '"');
				$db->setQuery($query);
				$params = $db->loadResult();

				if ($params)
				{
					$params         = new Registry($params);
					$paymentMethods = $params->get('payment_methods');
				}
			}

			$query->clear();
			$query->select('*')
				->from('#__eshop_payments')
				->where('published = 1')
				->order('ordering');

			if (isset($paymentMethods))
			{
				$query->where('id IN (' . implode(',', $paymentMethods) . ')');
			}

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			foreach ($rows as $row)
			{
				if (file_exists(JPATH_ROOT . '/components/com_eshop/plugins/payment/' . $row->name . '.php'))
				{
					require_once JPATH_ROOT . '/components/com_eshop/plugins/payment/' . $row->name . '.php';
					$params = new Registry($row->params);
					$status = true;

					if ($params->get('geozone_id', '0') && isset($paymentAddress['country_id']))
					{
						$query->clear();
						$query->select('COUNT(*)')
							->from('#__eshop_geozonezones')
							->where('geozone_id = ' . intval($params->get('geozone_id')))
							->where('country_id = ' . intval($paymentAddress['country_id']));

						if (isset($paymentAddress['zone_id']))
						{
							$query->where('(zone_id = 0 OR zone_id = ' . intval($paymentAddress['zone_id']) . ')');
						}
						else
						{
							$query->where('zone_id = 0');
						}

						$db->setQuery($query);

						if (!$db->loadResult())
						{
							$status = false;
						}
					}

					//Check min/max total
					$total    = $cart->getTotal();
					$minTotal = $params->get('min_total', 0);
					$maxTotal = $params->get('max_total', 0);

					if (($minTotal > 0 && $minTotal > $total) || ($maxTotal > 0 && $maxTotal < $total))
					{
						$status = false;
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

					if ($status)
					{
						$method = new $row->name($params);
						$method->setTitle(Text::_($row->title));
						$iconUri = '';
						$baseUri = Uri::base(true);
						$icon    = $params->get('icon');
						if ($icon != '')
						{
							if (file_exists(JPATH_ROOT . '/media/com_eshop/payments/' . $icon))
							{
								$iconUri = $baseUri . '/media/com_eshop/payments/' . $icon;
							}
							elseif (file_exists(JPATH_ROOT . '/' . $icon))
							{
								$iconUri = $baseUri . '/' . $icon;
							}
						}

						$method->iconUri = $iconUri;
						self::$methods[] = $method;
					}
				}
			}
		}

		return self::$methods;
	}

	/**
	 * Load information about the payment method
	 *
	 * @param   string  $name
	 * Name of the payment method
	 */
	public static function loadPaymentMethod($name)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_payments')
			->where('name = "' . $name . '"');
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get default payment gateway
	 *
	 * @return string
	 */
	public static function getDefautPaymentMethod()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('name')
			->from('#__eshop_payments')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query, 0, 1);

		return $db->loadResult();
	}

	/**
	 * Get the payment method object based on it's name
	 *
	 * @param   string  $name
	 *
	 * @return object
	 */
	public static function getPaymentMethod($name)
	{
		$methods = self::getPaymentMethods();
		foreach ($methods as $method)
		{
			if ($method->getName() == $name)
			{
				return $method;
			}
		}

		return null;
	}
}

?>