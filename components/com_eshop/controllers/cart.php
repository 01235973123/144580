<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2016 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

require_once __DIR__ . '/jsonresponse.php';

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerCart extends BaseController
{
	use EShopControllerJsonresponse;

	/**
	 *
	 * Function to add a product to the cart
	 */
	public function add()
	{
		$cart      = new EShopCart();
		$json      = [];
		$productId = $this->input->getInt('id');
		$quantity  = $this->input->getInt('quantity', 0);

		if ($quantity <= 0)
		{
			$quantity = 1;
		}

		$options = $this->input->get('options', [], 'array');

		//Validate options first
		$productOptions = EShopHelper::getProductOptions($productId, Factory::getLanguage()->getTag());

		for ($i = 0; $n = count($productOptions), $i < $n; $i++)
		{
			$productOption = $productOptions[$i];

			if ($productOption->required && $options[$productOption->product_option_id] == '')
			{
				$json['error']['option'][$productOption->product_option_id] = $productOption->option_name . ' ' . Text::_('ESHOP_REQUIRED');
			}
		}

		if (!$json)
		{
			$cart->add($productId, $quantity, $options);

			$json['success'] = true;
			$json['time']    = time();

			//Clear shipping and payment methods
			$this->afterCartUpdated();
		}
		else
		{
			$json['redirect'] = Route::_(EShopRoute::getProductRoute($productId, EShopHelper::getProductCategory($productId)));
		}

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to add multiple products to the cart at the same time
	 */
	public function addMultipleProducts()
	{
		$cart                 = new EShopCart();
		$json                 = [];
		$productIds           = $this->input->getString('product_ids');
		$productIdsArr        = explode(',', $productIds);
		$productQuantities    = $this->input->getString('product_quantities');
		$productQuantitiesArr = explode(',', $productQuantities);

		for ($i = 0; $n = count($productIdsArr), $i < $n; $i++)
		{
			$cart->add(intval($productIdsArr[$i]), intval($productQuantitiesArr[$i]));
		}

		$json['success'] = true;

		//Clear shipping and payment methods
		$this->afterCartUpdated();

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to re-order
	 */
	public function reOrder()
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		if (!$user->id)
		{
			$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
			$app->enqueueMessage(Text::_('ESHOP_RE_ORDER_LOGIN_PROMPT'), 'Notice');
			$app->redirect(Route::_('index.php?option=com_users&view=login' . $usersMenuItemStr));
		}
		else
		{
			$orderId = $this->input->getInt('order_id', 0);

			// Validate order
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__eshop_orders')
				->where('id = ' . intval($orderId))
				->where('customer_id = ' . intval($user->id));
			$db->setQuery($query);

			if (!$db->loadResult())
			{
				$app->enqueueMessage(Text::_('ESHOP_RE_ORDER_NOT_ALLOW'), 'Error');
				$app->redirect(EShopRoute::getViewRoute('customer') . '&layout=orders');
			}
			else
			{
				// Clear cart first
				$cart = new EShopCart();
				$cart->clear();

				// Then, clear shipping and payment methods
				$session = $app->getSession();
				$this->afterCartUpdated();

				// Re-add products and corresponding options to the cart
				$orderProducts = EShopHelper::getOrderProducts($orderId);

				if (!count($orderProducts))
				{
					$app->enqueueMessage(Text::_('ESHOP_RE_ORDER_NOT_ALLOW'), 'Error');
					$app->redirect(EShopRoute::getViewRoute('customer') . '&layout=orders');
				}
				else
				{
					$tempOrderProducts = [];

					for ($i = 0; $n = count($orderProducts), $i < $n; $i++)
					{
						//check if product is still available or not
						if (EShopHelper::isAvailableProduct($orderProducts[$i]->product_id))
						{
							$tempOrderProducts[] = $orderProducts[$i];
						}
					}

					$orderProducts = $tempOrderProducts;

					if (!count($orderProducts))
					{
						$app->enqueueMessage(Text::sprintf('ESHOP_RE_ORDER_ALL_PRODUCTS_NOT_AVAILABLE', $orderId), 'Warning');
						$app->redirect(EShopRoute::getViewRoute('customer') . '&layout=orders');
					}
					elseif (count($orderProducts) < $n)
					{
						$session->set('warning', Text::sprintf('ESHOP_RE_ORDER_SOME_PRODUCTS_NOT_AVAILABLE', $orderId));
					}

					for ($i = 0; $n = count($orderProducts), $i < $n; $i++)
					{
						$orderProduct = $orderProducts[$i];
						$options      = [];

						for ($j = 0; $m = count($orderProduct->orderOptions), $j < $m; $j++)
						{
							$option     = $orderProduct->orderOptions[$j];
							$optionType = $option->option_type;

							if ($optionType == 'Select' || $optionType == 'Radio')
							{
								$options[$option->product_option_id] = $option->product_option_value_id;
							}
							elseif ($optionType == 'Checkbox')
							{
								if (is_array($options[$option->product_option_id]))
								{
									$options[$option->product_option_id][] = $option->product_option_value_id;
								}
								else
								{
									$options[$option->product_option_id]   = [];
									$options[$option->product_option_id][] = $option->product_option_value_id;
								}
							}
							else
							{
								$options[$option->product_option_id] = $option->option_value;
							}
						}

						$cart->add($orderProduct->product_id, $orderProduct->quantity, $options);
					}

					$app->redirect(Route::_(EShopRoute::getViewRoute('cart')));
				}
			}
		}
	}

	/**
	 *
	 * Function to update quantity of a product in the cart
	 */
	public function update()
	{
		Factory::getApplication()->getSession()->set('success', Text::_('ESHOP_CART_UPDATE_MESSAGE'));

		$key      = $this->input->getString('key');
		$quantity = $this->input->getInt('quantity');

		$cart = new EShopCart();
		$cart->update($key, $quantity);

		//Clear shipping and payment methods
		$this->afterCartUpdated();
	}

	/**
	 *
	 * Function to update quantities of all products in the cart
	 */
	public function updates()
	{
		Factory::getApplication()->getSession()->set('success', Text::_('ESHOP_CART_UPDATE_MESSAGE'));

		$key      = $this->input->get('key', [], 'array');
		$quantity = $this->input->get('quantity', [], 'array');

		$cart = new EShopCart();
		$cart->updates($key, $quantity);

		//Clear shipping and payment methods
		$this->afterCartUpdated();
	}

	/**
	 *
	 * Function to remove a product from the cart
	 */
	public function remove()
	{
		$app = Factory::getApplication();
		$key = $this->input->getString('key');

		$cart = new EShopCart();
		$cart->remove($key);

		//Clear shipping and payment methods
		$this->afterCartUpdated();

		if ($this->input->getInt('redirect'))
		{
			$app->getSession()->set('success', Text::_('ESHOP_CART_REMOVED_MESSAGE'));
		}

		$app->close();
	}
	
	/**
	 *
	 * Function to clear cart
	 */
	public function clear()
	{
		Factory::getApplication()->getSession()->set('success', Text::_('ESHOP_CART_CLEAR_MESSAGE'));
	
		$cart = new EShopCart();
		$cart->clear();
	
		//Clear shipping and payment methods
		$this->afterCartUpdated();
	}

	/**
	 *
	 * Function to apply coupon to the cart
	 */
	public function applyCoupon()
	{
		$session    = Factory::getApplication()->getSession();
		$couponCode = $this->input->getString('coupon_code');
		$coupon     = new EShopCoupon();
		$couponData = $coupon->getCouponData($couponCode);

		if (!count($couponData))
		{
			$couponInfo = $coupon->getCouponInfo($couponCode);
			$user       = Factory::getUser();

			if (is_object($couponInfo) && $couponInfo->coupon_per_customer && !$user->get('id'))
			{
				$session->set('warning', Text::_('ESHOP_COUPON_IS_ONLY_FOR_REGISTERED_USER'));
			}
			else
			{
				$session->set('warning', Text::_('ESHOP_COUPON_APPLY_ERROR'));
			}
		}
		else
		{
			$session->set('coupon_code', $couponCode);
			$session->set('success', Text::_('ESHOP_COUPON_APPLY_SUCCESS'));
		}
	}

	/**
	 *
	 * Function to remove coupon
	 */
	public function removeCoupon()
	{
		Factory::getApplication()->getSession()->clear('coupon_code');
	}

	/**
	 *
	 * Function to apply voucher to the cart
	 */
	public function applyVoucher()
	{
		$session     = Factory::getApplication()->getSession();
		$voucherCode = $this->input->getString('voucher_code');
		$voucher     = new EShopVoucher();
		$voucherData = $voucher->getVoucherData($voucherCode);

		if (!count($voucherData))
		{
			$session->set('warning', Text::_('ESHOP_VOUCHER_APPLY_ERROR'));
		}
		else
		{
			$session->set('voucher_code', $voucherCode);
			$session->set('success', Text::_('ESHOP_VOUCHER_APPLY_SUCCESS'));
		}
	}

	/**
	 *
	 * Function to remove voucher
	 */
	public function removeVoucher()
	{
		Factory::getApplication()->getSession()->clear('voucher_code');
	}

	/**
	 *
	 * Function to apply shipping to the cart
	 */
	public function applyShipping()
	{
		$shippingMethod  = explode('.', $this->input->getString('shipping_method'));
		$session         = Factory::getApplication()->getSession();
		$shippingMethods = $session->get('shipping_methods');

		if (isset($shippingMethods) && isset($shippingMethods[$shippingMethod[0]]))
		{
			$session->set('shipping_method', $shippingMethods[$shippingMethod[0]]['quote'][$shippingMethod[1]]);
			$session->set('success', Text::_('ESHOP_SHIPPING_APPLY_SUCCESS'));
		}
		else
		{
			$session->set('warning', Text::_('ESHOP_SHIPPING_APPLY_ERROR'));
		}
	}

	/**
	 *
	 * Function to get Quote
	 */
	public function getQuote()
	{
		$app       = Factory::getApplication();
		$json      = [];
		$cart      = new EShopCart();
		$input     = $this->input;
		$countryId = $input->getInt('country_id');
		$zoneId    = $input->getInt('zone_id');
		$postcode  = $input->getString('postcode');

		if (!$cart->hasProducts())
		{
			$json['error']['warning'] = Text::_('ESHOP_ERROR_HAS_PRODUCTS');
		}

		if (!$cart->hasShipping())
		{
			$json['error']['warning'] = Text::_('ESHOP_ERROR_HAS_SHIPPING');
		}

		if (!$countryId)
		{
			$json['error']['country'] = Text::_('ESHOP_ERROR_COUNTRY');
		}

		if (!$zoneId)
		{
			$json['error']['zone'] = Text::_('ESHOP_ERROR_ZONE');
		}

		$countryInfo = EShopHelper::getCountry($countryId);

		if (is_object($countryInfo) && $countryInfo->postcode_required && ((strlen($postcode) < 2) || (strlen($postcode) > 8)))
		{
			$json['error']['postcode'] = Text::_('ESHOP_ERROR_POSTCODE');
		}

		if ($json)
		{
			$this->sendJsonResponse($json);

			return;
		}


		$session = $app->getSession();
		$tax     = new EShopTax(EShopHelper::getConfig());
		$tax->setShippingAddress($countryId, $zoneId, $postcode);
		$session->set('shipping_country_id', $countryId);
		$session->set('shipping_zone_id', $zoneId);
		$session->set('shipping_postcode', $postcode);

		if (is_object($countryInfo))
		{
			$countryName = $countryInfo->country_name;
			$isoCode2    = $countryInfo->iso_code_2;
			$isoCode3    = $countryInfo->iso_code_3;
		}
		else
		{
			$countryName = '';
			$isoCode2    = '';
			$isoCode3    = '';
		}

		$zoneInfo = EShopHelper::getZone($zoneId);

		if (is_object($zoneInfo))
		{
			$zoneName = $zoneInfo->zone_name;
			$zoneCode = $zoneInfo->zone_code;
		}
		else
		{
			$zoneName = '';
			$zoneCode = '';
		}

		$addressData = [
			'firstname'    => '',
			'lastname'     => '',
			'company'      => '',
			'address_1'    => '',
			'address_2'    => '',
			'postcode'     => $postcode,
			'city'         => '',
			'zone_id'      => $zoneId,
			'zone_name'    => $zoneName,
			'zone_code'    => $zoneCode,
			'country_id'   => $countryId,
			'country_name' => $countryName,
			'iso_code_2'   => $isoCode2,
			'iso_code_3'   => $isoCode3,
		];

		$quoteData = [];
		$db        = Factory::getDbo();
		$query     = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_shippings')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		for ($i = 0; $n = count($rows), $i < $n; $i++)
		{
			$shippingName = $rows[$i]->name;
			$params       = new Registry($rows[$i]->params);
			require_once JPATH_COMPONENT . '/plugins/shipping/' . $shippingName . '.php';
			$shippingClass = new $shippingName();
			$quote         = $shippingClass->getQuote($addressData, $params);

			if ($quote)
			{
				$quoteData[$shippingName] = [
					'title'    => $quote['title'],
					'quote'    => $quote['quote'],
					'ordering' => $quote['ordering'],
					'error'    => $quote['error'],
				];
			}

			if (EShopHelper::getConfigValue('only_free_shipping', 0) && strpos($shippingName, 'eshop_free') !== false)
			{
				$session->clear('shipping_method');
				$quoteData                = [];
				$quoteData[$shippingName] = [
					'title'    => $quote['title'],
					'quote'    => $quote['quote'],
					'ordering' => $quote['ordering'],
					'error'    => $quote['error'],
				];
				break;
			}
		}

		$session->set('shipping_methods', $quoteData);

		if ($session->get('shipping_methods'))
		{
			$json['shipping_methods'] = $session->get('shipping_methods');
		}
		else
		{
			$json['error']['warning'] = Text::_('ESHOP_NO_SHIPPING_METHODS');
		}

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to get Zones for a specific Country
	 */
	public function getZones()
	{
		$json        = [];
		$countryId   = $this->input->getInt('country_id');
		$countryInfo = EShopHelper::getCountry($countryId);

		if (is_object($countryInfo))
		{
			$json = [
				'country_id'        => $countryInfo->id,
				'country_name'      => $countryInfo->country_name,
				'iso_code_2'        => $countryInfo->iso_code_2,
				'iso_code_3'        => $countryInfo->iso_code_3,
				'postcode_required' => $countryInfo->postcode_required,
				'zones'             => EShopHelper::getCountryZones($countryId),
			];
		}

		$this->sendJsonResponse($json);
	}

	/**
	 * Clean up session data after cart update
	 *
	 * @return void
	 */
	protected function afterCartUpdated(): void
	{
		$session = Factory::getApplication()->getSession();
		$session->clear('shipping_method');
		$session->clear('shipping_methods');
		$session->clear('payment_method');

		if (EShopHelper::getConfigValue('change_coupon', 0))
		{
			$session->clear('coupon_code');
		}

		if (EShopHelper::getConfigValue('change_voucher', 0))
		{
			$session->clear('voucher_code');
		}
	}
}