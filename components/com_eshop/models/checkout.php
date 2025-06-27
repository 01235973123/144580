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

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;

class EShopModelCheckout extends EShopModel
{

	/**
	 * Entity data
	 *
	 * @var array
	 */
	protected $cartData = null;

	/**
	 *
	 * Total Data object array, each element is an price price in the cart
	 * @var object array
	 */
	protected $totalData = null;

	/**
	 *
	 * Final total price of the cart
	 * @var float
	 */
	protected $total = null;

	/**
	 *
	 * Taxes of all elements in the cart
	 * @var array
	 */
	protected $taxes = null;

	/**
	 *
	 * Function to register user
	 *
	 * @param   array  $data
	 *
	 * @return  array
	 */
	public function register($data)
	{
		$cart    = new EShopCart();
		$app     = Factory::getApplication();
		$user    = Factory::getUser();
		$session = $app->getSession();

		//Process EU Vat Number
		if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'payment')
		{
			$this->validateEUTaxNumber($data);
		}

		$json = $this->validateRequireUserNotLoggedInAndCartHasProducts($user, $cart);

		if ($json)
		{
			return $json;
		}

		$fields = EShopHelper::getFormFields('B');
		$form   = new EshopRADForm($fields);

		if (isset($data['country_id']) && !EShopHelper::hasZone($data['country_id']))
		{
			$form->removeRule('zone_id');
		}

		$valid = $form->validate($data);

		if (!$valid)
		{
			$json['error'] = $form->getErrors();
		}

		if ($errors = $this->validateNewUserAccountData($data))
		{
			foreach ($errors as $key => $message)
			{
				$json['error'][$key] = $message;
			}
		}

		// Captcha validation
		if (EShopHelper::getConfigValue('enable_register_account_captcha'))
		{
			$input         = $app->input;
			$captchaPlugin = $app->get('captcha') ?: 'recaptcha';

			$plugin = PluginHelper::getPlugin('captcha', $captchaPlugin);

			if ($plugin)
			{
				try
				{
					Captcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('dynamic_recaptcha_1', null, 'string'));
				}
				catch (Exception $e)
				{
					$json['error']['captcha'] = Text::_('ESHOP_INVALID_CAPTCHA');
				}
			}
		}

		// Validate account terms agree
		if (EShopHelper::getConfigValue('account_terms') && !isset($data['account_terms_agree']))
		{
			$json['error']['warning'] = Text::_('ESHOP_ERROR_ACCOUNT_TERMS_AGREE');
		}

		if ($json)
		{
			return $json;
		}

		$session->set('account', 'register');

		// Register user here
		$json = $this->createNewUserAccount($data);

		if ($json)
		{
			return $json;
		}

		// Login user first
		$credentials             = [];
		$credentials['username'] = $data['username'];
		$credentials['password'] = $data['password1'];
		$options                 = [];

		if (true === $app->login($credentials, $options))
		{
			// Login success - store address
			$user = Factory::getUser();
			$row  = Table::getInstance('Eshop', 'Address');

			if (!isset($data['country_id']))
			{
				$data['country_id'] = EShopHelper::getConfigValue('country_id');
			}

			if (!isset($data['zone_id']))
			{
				$data['zone_id'] = EShopHelper::getConfigValue('zone_id');
			}

			$row->bind($data);
			$row->customer_id   = $user->get('id');
			$row->created_date  = gmdate('Y-m-d H:i:s');
			$row->modified_date = gmdate('Y-m-d H:i:s');
			$row->store();
			$addressId = $row->id;
			// Store customer
			$row = Table::getInstance('Eshop', 'Customer');
			$row->bind($data);
			$row->customer_id      = $user->get('id');
			$row->customergroup_id = $this->getSelectedCustomerGroup($data);
			$row->address_id       = $addressId;
			$row->published        = 1;
			$row->created_date     = gmdate('Y-m-d H:i:s');
			$row->modified_date    = gmdate('Y-m-d H:i:s');
			$row->store();

			//Assign billing address
			$addressInfo = EShopHelper::getAddress($addressId);
			$session->set('payment_address_id', $addressId);

			if (count($addressInfo))
			{
				$session->set('payment_country_id', $addressInfo['country_id']);
				$session->set('payment_zone_id', $addressInfo['zone_id']);
				$session->set('payment_postcode', $addressInfo['postcode']);
			}
			else
			{
				$session->clear('payment_country_id');
				$session->clear('payment_zone_id');
				$session->clear('payment_postcode');
			}

			if (isset($data['shipping_address']))
			{
				$session->set('shipping_address_id', $addressId);

				if (count($addressInfo))
				{
					$session->set('shipping_country_id', $addressInfo['country_id']);
					$session->set('shipping_zone_id', $addressInfo['zone_id']);
					$session->set('shipping_postcode', $addressInfo['postcode']);
				}
				else
				{
					$session->clear('shipping_country_id');
					$session->clear('shipping_zone_id');
					$session->clear('shipping_postcode');
				}

				//Process EU Vat Number
				if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'shipping')
				{
					$this->validateEUTaxNumber($data);
				}
			}
		}
		else
		{
			$json['error']['warning'] = Text::_('ESHOP_WARNING_LOGIN_FAILED');
		}

		$session->clear('guest');
		$session->clear('customer');
		$session->clear('shipping_method');
		$session->clear('shipping_methods');
		$session->clear('payment_method');

		return $json;
	}

	/**
	 *
	 * Function to guest
	 *
	 * @param   array  $data
	 *
	 * @return  array
	 */
	public function guest($data)
	{
		$cart    = new EShopCart();
		$app     = Factory::getApplication();
		$user    = Factory::getUser();
		$session = $app->getSession();

		//Process EU Vat Number
		if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'payment')
		{
			$this->validateEUTaxNumber($data);
		}

		$json = $this->validateRequireUserNotLoggedInAndCartHasProducts($user, $cart);

		if ($json)
		{
			return $json;
		}

		$fields = EShopHelper::getFormFields('B');
		$form   = new EshopRADForm($fields);

		if (isset($data['country_id']) && !EShopHelper::hasZone($data['country_id']))
		{
			$form->removeRule('zone_id');
		}

		$valid = $form->validate($data);

		if (!$valid)
		{
			$json['error'] = $form->getErrors();

			return $json;
		}

		// Set guest information session
		$guest                     = [];
		$guest['customer_id']      = 0;
		$guest['customergroup_id'] = $this->getSelectedCustomerGroup($data);
		$guest['firstname']        = $data['firstname'];
		$guest['lastname']         = $data['lastname'] ?? '';
		$guest['email']            = $data['email'];
		$guest['telephone']        = $data['telephone'] ?? '';
		$guest['fax']              = $data['fax'] ?? '';

		$countryId   = $data['country_id'] ?? EShopHelper::getConfigValue('country_id');
		$countryInfo = EShopHelper::getCountry($countryId);

		$zoneId   = $data['zone_id'] ?? EShopHelper::getConfigValue('zone_id');
		$zoneInfo = EShopHelper::getZone($zoneId);

		// Set payment (billing) address session
		$guest['payment'] = $this->getAddressInformation($fields, $data, $countryInfo, $zoneInfo);;

		// Default Payment Address
		$session->set('payment_country_id', $countryId);
		$session->set('payment_zone_id', $zoneId);
		$session->set('payment_postcode', $data['postcode'] ?? EShopHelper::getConfigValue('postcode'));

		// Set shipping address session
		if (isset($data['shipping_address']))
		{
			$guest['shipping_address'] = true;
		}
		else
		{
			$guest['shipping_address'] = false;
		}

		if ($guest['shipping_address'])
		{
			$shippingFields = EShopHelper::getFormFields('S');

			$guest['shipping'] = $this->getAddressInformation($shippingFields, $data, $countryInfo, $zoneInfo);

			// Default Shipping Address
			$session->set('shipping_country_id', $countryId);
			$session->set('shipping_zone_id', $zoneId);
			$session->set('shipping_postcode', $data['postcode'] ?? EShopHelper::getConfigValue('postcode'));

			//Process EU Vat Number
			if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'shipping')
			{
				$this->validateEUTaxNumber($data);
			}
		}
		else
		{
			$tempGuest = $session->get('guest');

			if (isset($tempGuest['shipping']))
			{
				$guest['shipping'] = $tempGuest['shipping'];
			}
		}

		self::getCosts();

		$json['total'] = $this->total;
		$session->set('guest', $guest);
		$session->set('account', 'guest');
		$session->clear('shipping_method');
		$session->clear('shipping_methods');
		$session->clear('payment_method');

		return $json;
	}

	/**
	 *
	 * Function to process guest shipping
	 *
	 * @param   array  $data
	 *
	 * @return array
	 */
	public function processGuestShipping($data)
	{
		$cart    = new EShopCart();
		$app     = Factory::getApplication();
		$user    = Factory::getUser();
		$session = $app->getSession();

		//Process EU Vat Number
		if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'shipping')
		{
			$this->validateEUTaxNumber($data);
		}

		$json = $this->validateRequireUserNotLoggedInAndCartHasProducts($user, $cart);

		if ($json)
		{
			return $json;
		}

		$fields = EShopHelper::getFormFields('S');
		$form   = new EshopRADForm($fields);

		if (isset($data['country_id']) && !EShopHelper::hasZone($data['country_id']))
		{
			$form->removeRule('zone_id');
		}

		$valid = $form->validate($data);

		if (!$valid)
		{
			$json['error'] = $form->getErrors();

			return $json;
		}

		$guest = $session->get('guest');

		$countryId   = $data['country_id'] ?? EShopHelper::getConfigValue('country_id');
		$countryInfo = EShopHelper::getCountry($countryId);
		$zoneId      = $data['zone_id'] ?? EShopHelper::getConfigValue('zone_id');
		$zoneInfo    = EShopHelper::getZone($zoneId);

		$guest['shipping'] = $this->getAddressInformation($fields, $data, $countryInfo, $zoneInfo);

		$session->set('guest', $guest);

		// Default Shipping Address
		$session->set('shipping_country_id', $countryId);
		$session->set('shipping_zone_id', $zoneId);
		$session->set('shipping_postcode', $data['postcode'] ?? EShopHelper::getConfigValue('postcode'));

		$session->clear('shipping_method');
		$session->clear('shipping_methods');

		return $json;
	}

	/**
	 *
	 * Function to process payment address
	 *
	 * @param   array  $data
	 *
	 * @return  array
	 */
	public function processPaymentAddress($data)
	{
		$cart    = new EShopCart();
		$app     = Factory::getApplication();
		$user    = Factory::getUser();
		$session = $app->getSession();

		$json = $this->validateRequireUserLoggedInAndCartHasProducts($user, $cart);

		if ($json)
		{
			return $json;
		}

		$customerFirstName = '';
		$customerLastName  = '';

		// User choose an existing address
		if (isset($data['payment_address']) && $data['payment_address'] == 'existing')
		{
			if (!$data['address_id'])
			{
				$json['error']['warning'] = Text::_('ESHOP_ERROR_ADDRESS');
			}
			else
			{
				$addressInfo       = EShopHelper::getAddress($data['address_id']);
				$customerFirstName = $addressInfo['firstname'];
				$customerLastName  = $addressInfo['lastname'];
				$customerAddressId = $data['address_id'];
				$session->set('payment_address_id', $data['address_id']);

				if (count($addressInfo))
				{
					$session->set('payment_country_id', $addressInfo['country_id']);
					$session->set('payment_zone_id', $addressInfo['zone_id']);
					$session->set('payment_postcode', $addressInfo['postcode']);

					//Process EU Vat Number
					if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'payment')
					{
						$this->validateEUTaxNumber($addressInfo);
					}
				}
				else
				{
					$session->clear('payment_country_id');
					$session->clear('payment_zone_id');
					$session->clear('payment_postcode');

					if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'payment')
					{
						$session->clear('eu_vat_number');
					}
				}

				$session->clear('payment_method');
			}
		}
		else
		{
			$fields = EShopHelper::getFormFields('B');
			$form   = new EshopRADForm($fields);

			if (isset($data['country_id']) && !EShopHelper::hasZone($data['country_id']))
			{
				$form->removeRule('zone_id');
			}

			$valid = $form->validate($data);

			if (!$valid)
			{
				$json['error'] = $form->getErrors();
			}

			if (!$json)
			{
				// Store new address
				$row = Table::getInstance('Eshop', 'Address');

				if (!isset($data['country_id']))
				{
					$data['country_id'] = EShopHelper::getConfigValue('country_id');
				}

				if (!isset($data['zone_id']))
				{
					$data['zone_id'] = EShopHelper::getConfigValue('zone_id');
				}

				$row->bind($data);
				$row->customer_id   = $user->get('id');
				$row->created_date  = gmdate('Y-m-d H:i:s');
				$row->modified_date = gmdate('Y-m-d H:i:s');
				$row->store();
				$addressId         = $row->id;
				$customerFirstName = $data['firstname'];
				$customerLastName  = $data['lastname'] ?? '';
				$customerAddressId = $addressId;

				$session->set('payment_address_id', $addressId);
				$countryId = $data['country_id'] ?? EShopHelper::getConfigValue('country_id');
				$session->set('payment_country_id', $countryId);
				$zoneId = $data['zone_id'] ?? EShopHelper::getConfigValue('zone_id');
				$session->set('payment_zone_id', $zoneId);
				$session->set('payment_postcode', $data['postcode'] ?? EShopHelper::getConfigValue('postcode'));
				$session->clear('payment_method');

				//Process EU Vat Number
				if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'payment')
				{
					$this->validateEUTaxNumber($data);
				}

				$customer = [];

				$countryId   = $data['country_id'] ?? EShopHelper::getConfigValue('country_id');
				$countryInfo = EShopHelper::getCountry($countryId);
				$zoneId      = $data['zone_id'] ?? EShopHelper::getConfigValue('zone_id');
				$zoneInfo    = EShopHelper::getZone($zoneId);

				//Start - set payment address session
				$customer['payment'] = $this->getAddressInformation($fields, $data, $countryInfo, $zoneInfo);

				// Default Payment Address
				$session->set('payment_country_id', $countryId);
				$session->set('payment_zone_id', $zoneId);
				$session->set('payment_postcode', $data['postcode'] ?? EShopHelper::getConfigValue('postcode'));

				//Start - process for same shipping address
				if (isset($data['shipping_address']))
				{
					$session->set('shipping_address_id', $addressId);

					$shippingFields = EShopHelper::getFormFields('S');

					$customer['shipping'] = $this->getAddressInformation($shippingFields, $data, $countryInfo, $zoneInfo);

					// Default Shipping Address
					$session->set('shipping_country_id', $countryId);
					$session->set('shipping_zone_id', $zoneId);
					$session->set('shipping_postcode', $data['postcode'] ?? EShopHelper::getConfigValue('postcode'));

					//Process EU Vat Number
					if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'shipping')
					{
						$this->validateEUTaxNumber($data);
					}
				}

				$session->set('customer', $customer);
				//End - process for same shipping address
			}
		}

		if ($customerFirstName != '')
		{
			$customerId = $user->get('id');
			$db         = $this->getDbo();
			$query      = $db->getQuery(true)
				->select('id')
				->from('#__eshop_customers')
				->where('customer_id = ' . intval($customerId));
			$db->setQuery($query);

			if (!$db->loadResult())
			{
				$row                   = Table::getInstance('Eshop', 'Customer');
				$row->id               = '';
				$row->customer_id      = $user->get('id');
				$row->customergroup_id = EShopHelper::getConfigValue('customergroup_id');
				$row->address_id       = $customerAddressId;
				$row->firstname        = $customerFirstName;
				$row->lastname         = $customerLastName;
				$row->email            = $user->get('email');
				$row->telephone        = $data['telephone'] ?? '';
				$row->fax              = $data['fax'] ?? '';
				$row->published        = 1;
				$row->created_date     = gmdate('Y-m-d H:i:s');
				$row->modified_date    = gmdate('Y-m-d H:i:s');
				$row->store();
			}
		}

		self::getCosts();

		$json['total'] = $this->total;

		return $json;
	}

	/**
	 *
	 * Function to process shipping address
	 *
	 * @param   array  $data
	 *
	 * @return array
	 */
	public function processShippingAddress($data)
	{
		$cart    = new EShopCart();
		$app     = Factory::getApplication();
		$user    = Factory::getUser();
		$session = $app->getSession();
		$json    = $this->validateRequireUserLoggedInAndCartHasProducts($user, $cart);

		if ($json)
		{
			return $json;
		}

		// User choose an existing address
		if ($data['shipping_address'] == 'existing')
		{
			if (!$data['address_id'])
			{
				$json['error']['warning'] = Text::_('ESHOP_ERROR_ADDRESS');
			}
			else
			{
				$addressInfo = EShopHelper::getAddress($data['address_id']);
				$session->set('shipping_address_id', $data['address_id']);

				if (count($addressInfo))
				{
					$session->set('shipping_country_id', $addressInfo['country_id']);
					$session->set('shipping_zone_id', $addressInfo['zone_id']);
					$session->set('shipping_postcode', $addressInfo['postcode']);

					//Process EU Vat Number
					if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'shipping')
					{
						$this->validateEUTaxNumber($addressInfo);
					}
				}
				else
				{
					$session->clear('shipping_country_id');
					$session->clear('shipping_zone_id');
					$session->clear('shipping_postcode');

					if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'shipping')
					{
						$session->clear('eu_vat_number');
					}
				}

				$session->clear('shipping_method');
				$session->clear('shipping_methods');
			}
		}
		else
		{
			$fields = EShopHelper::getFormFields('S');
			$form   = new EshopRADForm($fields);

			if (isset($data['country_id']) && !EShopHelper::hasZone($data['country_id']))
			{
				$form->removeRule('zone_id');
			}

			$valid = $form->validate($data);

			if (!$valid)
			{
				$json['error'] = $form->getErrors();
			}

			if (!$json)
			{
				// Store new address
				$row = Table::getInstance('Eshop', 'Address');

				if (!isset($data['country_id']))
				{
					$data['country_id'] = EShopHelper::getConfigValue('country_id');
				}

				if (!isset($data['zone_id']))
				{
					$data['zone_id'] = EShopHelper::getConfigValue('zone_id');
				}

				$row->bind($data);
				$row->customer_id   = $user->get('id');
				$row->created_date  = gmdate('Y-m-d H:i:s');
				$row->modified_date = gmdate('Y-m-d H:i:s');
				$row->store();
				$addressId = $row->id;

				$session->set('shipping_address_id', $addressId);
				$countryId = $data['country_id'] ?? EShopHelper::getConfigValue('country_id');
				$session->set('shipping_country_id', $countryId);
				$zoneId = $data['zone_id'] ?? EShopHelper::getConfigValue('zone_id');
				$session->set('shipping_zone_id', $zoneId);
				$session->set('shipping_postcode', $data['postcode'] ?? EShopHelper::getConfigValue('postcode'));
				$session->clear('shipping_method');
				$session->clear('shipping_methods');

				//Process EU Vat Number
				if (EShopHelper::getConfigValue('enable_eu_vat_rules') && EShopHelper::getConfigValue('eu_vat_rules_based_on') == 'shipping')
				{
					$this->validateEUTaxNumber($data);
				}
			}
		}

		return $json;
	}

	/**
	 *
	 * Function to process shipping method
	 *
	 * @return array
	 */
	public function processShippingMethod()
	{
		$cart    = new EShopCart();
		$app     = Factory::getApplication();
		$user    = Factory::getUser();
		$session = $app->getSession();
		$input   = $app->input;
		$json    = [];

		// If shipping is not required, the customer shoud not have reached this page
		if (!$cart->hasShipping())
		{
			if (EShopHelper::getConfigValue('active_https'))
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
			}
			else
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'));
			}
		}

		// Validate if shipping address has been set or not
		if ($user->get('id') && $session->get('shipping_address_id'))
		{
			$shippingAddress = EShopHelper::getAddress($session->get('shipping_address_id'));
		}
		else
		{
			$guest           = $session->get('guest');
			$shippingAddress = $guest['shipping'] ?? '';
		}

		if (empty($shippingAddress) && EShopHelper::getConfigValue('require_shipping_address', 1))
		{
			if (EShopHelper::getConfigValue('active_https'))
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
			}
			else
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'));
			}
		}

		if (EShopHelper::getConfigValue('delivery_date'))
		{
			$deliveryDate = $input->getString('delivery_date');

			if ($deliveryDate == '')
			{
				$json['error']['warning'] = Text::_('ESHOP_DELIVERY_DATE_PROMPT');
			}
			elseif ($deliveryDate < HTMLHelper::_('date', '', 'Y-m-d', null))
			{
				$json['error']['warning'] = Text::_('ESHOP_DELIVERY_DATE_WARNING');
			}
		}

		if ($json)
		{
			return $json;
		}

		if (!$input->getString('shipping_method'))
		{
			$json['error']['warning'] = Text::_('ESHOP_ERROR_SHIPPING_METHOD');
		}
		else
		{
			if (EShopHelper::getConfigValue('require_shipping_address', 1))
			{
				if ($user->get('id') && $session->get('shipping_address_id'))
				{
					//User Shipping
					$addressInfo = EShopHelper::getAddress($session->get('shipping_address_id'));
				}
				else
				{
					//Guest Shipping
					$guest       = $session->get('guest');
					$addressInfo = $guest['shipping'];
				}
			}
			else
			{
				if ($user->get('id') && $session->get('shipping_address_id'))
				{
					//User Shipping
					$addressInfo = EShopHelper::getAddress($session->get('shipping_address_id'));
				}
				else
				{
					//Guest Shipping
					$guest       = $session->get('guest');
					$addressInfo = $guest['payment'];
				}
			}

			$addressData = [
				'firstname'    => $addressInfo['firstname'] ?? '',
				'lastname'     => $addressInfo['lastname'] ?? '',
				'company'      => $addressInfo['company'] ?? '',
				'address_1'    => $addressInfo['address_1'] ?? '',
				'address_2'    => $addressInfo['address_2'] ?? '',
				'postcode'     => $addressInfo['postcode'] ?? '',
				'city'         => $addressInfo['city'] ?? '',
				'zone_id'      => $addressInfo['zone_id'] ?? EShopHelper::getConfigValue('zone_id'),
				'zone_name'    => $addressInfo['zone_name'] ?? '',
				'zone_code'    => $addressInfo['zone_code'] ?? '',
				'country_id'   => $addressInfo['country_id'] ?? EShopHelper::getConfigValue('country_id'),
				'country_name' => $addressInfo['country_name'] ?? '',
				'iso_code_2'   => $addressInfo['iso_code_2'] ?? '',
				'iso_code_3'   => $addressInfo['iso_code_3'] ?? '',
			];

			$quoteData = [];

			$db    = $this->getDbo();
			$query = $db->getQuery(true);
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
			}

			$shippingMethods = $quoteData;
			$shippingMethod  = explode('.', $input->getString('shipping_method'));

			if (isset($shippingMethods) && isset($shippingMethods[$shippingMethod[0]]))
			{
				$session->set('shipping_method', $shippingMethods[$shippingMethod[0]]['quote'][$shippingMethod[1]]);
				$session->set('delivery_date', $input->getString('delivery_date'));

				$displayComment = EShopHelper::getConfigValue('display_comment', '45');

				if ($displayComment == '4' || $displayComment == '45')
				{
					$session->set('comment', $input->getString('comment'));
				}

				//Get total
				self::getCosts();

				$json['total'] = $this->total;
			}
			else
			{
				$json['error']['warning'] = Text::_('ESHOP_ERROR_SHIPPING_METHOD');
			}
		}

		return $json;
	}

	/**
	 * Function to process payment method
	 */
	public function processPaymentMethod()
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$cart    = new EShopCart();
		$user    = Factory::getUser();
		$session = $app->getSession();
		$json    = [];

		// Validate if payment address has been set.
		if ($user->get('id') && $session->get('payment_address_id'))
		{
			$paymentAddress = EShopHelper::getAddress($session->get('payment_address_id'));
		}
		else
		{
			$guest          = $session->get('guest');
			$paymentAddress = $guest['payment'] ?? '';
		}

		if (empty($paymentAddress))
		{
			if (EShopHelper::getConfigValue('active_https'))
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
			}
			else
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'));
			}
		}

		//Validate if cart has products
		if (!$cart->hasProducts())
		{
			if (EShopHelper::getConfigValue('active_https'))
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
			}
			else
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'));
			}
		}

		if ($json)
		{
			return $json;
		}

		$paymentMethod = $input->getString('payment_method');

		if (!$paymentMethod)
		{
			$json['error']['warning'] = Text::_('ESHOP_ERROR_PAYMENT_METHOD');
		}
		else
		{
			$methods        = os_payments::getPaymentMethods();
			$paymentMethods = [];

			for ($i = 0; $n = count($methods), $i < $n; $i++)
			{
				$paymentMethods[] = $methods[$i]->getName();
			}

			if (isset($paymentMethods) && in_array($paymentMethod, $paymentMethods))
			{
				$session->set('payment_method', $paymentMethod);
				$json['payment_method'] = $paymentMethod;

				//Check coupon
				$couponCode = $input->getString('coupon_code');

				if ($couponCode != '')
				{
					$coupon     = new EShopCoupon();
					$couponData = $coupon->getCouponData($couponCode);

					if (!count($couponData))
					{
						$couponInfo = $coupon->getCouponInfo($couponCode);

						if (is_object($couponInfo) && $couponInfo->coupon_per_customer && !$user->get('id'))
						{
							$json['error']['warning'] = Text::_('ESHOP_COUPON_IS_ONLY_FOR_REGISTERED_USER');
						}
						else
						{
							$json['error']['warning'] = Text::_('ESHOP_COUPON_APPLY_ERROR');
						}
					}
					else
					{
						$session->set('coupon_code', $couponCode);
						$session->set('success', Text::_('ESHOP_COUPON_APPLY_SUCCESS'));
					}
				}

				//Check voucher
				$voucherCode = $input->getString('voucher_code');

				if ($voucherCode != '')
				{
					$voucher     = new EShopVoucher();
					$voucherData = $voucher->getVoucherData($voucherCode);

					if (!count($voucherData))
					{
						$json['error']['warning'] = Text::_('ESHOP_VOUCHER_APPLY_ERROR');
					}
					else
					{
						$session->set('voucher_code', $voucherCode);
						$session->set('success', Text::_('ESHOP_VOUCHER_APPLY_SUCCESS'));
					}
				}

				$donateAmount = $input->getFloat('donate_amount', 0);
				$otherAmount  = $input->getFloat('other_amount', 0);
				$amount       = 0;

				if ($donateAmount > 0)
				{
					$amount = $donateAmount;
				}
				elseif ($otherAmount > 0)
				{
					$amount = $otherAmount;
				}

				if ($amount > 0)
				{
					$session->set('donate_amount', $amount);
				}
				else
				{
					$session->clear('donate_amount');
				}

				$displayComment = EShopHelper::getConfigValue('display_comment', '45');

				if ($displayComment == '5' || $displayComment == '45')
				{
					$session->set('comment', $input->getString('comment'));
				}

				$errorWarning = [];

				if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'payment_method_step')
				{
					if (EShopHelper::getConfigValue('checkout_terms') && !$input->get('checkout_terms_agree'))
					{
						$errorWarning[] = Text::_('ESHOP_ERROR_CHECKOUT_TERMS_AGREE');
					}

					if (EShopHelper::getConfigValue('show_privacy_policy_checkbox') && !$input->get('privacy_policy_agree'))
					{
						$errorWarning[] = Text::_('ESHOP_AGREE_PRIVACY_POLICY_ERROR');
					}

					if (count($input->get('newsletter_interest', [], 'array')) > 0)
					{
						$session->set('newsletter_interest', true);
					}
					else
					{
						$session->set('newsletter_interest', false);
					}
				}

				if (count($errorWarning) > 0)
				{
					$json['error']['warning'] = implode("<br />", $errorWarning);
				}
			}
			else
			{
				$json['error']['warning'] = Text::_('ESHOP_ERROR_PAYMENT_METHOD');
			}
		}

		return $json;
	}

	/**
	 * Function to process order
	 *
	 * @param   array  $data
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function processOrder($data)
	{
		$app      = Factory::getApplication();
		$session  = $app->getSession();
		$cart     = new EShopCart();
		$tax      = new EShopTax(EShopHelper::getConfig());
		$currency = EShopCurrency::getInstance();
		$db       = $this->getDbo();
		$query    = $db->getQuery(true);

		// Check if cart has products or not
		if (!$cart->hasProducts() || !$cart->canCheckout() || $cart->getMinSubTotalWarning() != '' || $cart->getMinQuantityWarning(
			) != '' || $cart->getMinProductQuantityWarning() != '' || $cart->getMaxProductQuantityWarning() != '')
		{
			$app->redirect(Route::_(EShopRoute::getViewRoute('cart')));
		}

		// Store Order
		$row = Table::getInstance('Eshop', 'Order');
		$row->bind($data);
		$row->user_ip = EShopHelper::getUserIp();

		if ($session->get('newsletter_interest'))
		{
			$row->newsletter_interest = 1;
		}

		$row->privacy_policy_agree = 1;
		$row->created_date         = gmdate('Y-m-d H:i:s');
		$row->modified_date        = gmdate('Y-m-d H:i:s');
		$row->modified_by          = 0;
		$row->checked_out          = 0;
		$row->checked_out_time     = '0000-00-00 00:00:00';
		$row->store();
		$orderRow = $row;
		$orderId  = $row->id;
		$session->set('order_id', $orderId);
		$session->set('order_number', $data['order_number']);

		$row->load($orderId);
		$orderTotal = $row->total;

		// Store Order Products, Order Options and Order Downloads
		foreach ($cart->getCartData() as $product)
		{
			// Order Products
			$row               = Table::getInstance('Eshop', 'Orderproducts');
			$row->id           = '';
			$row->order_id     = $orderId;
			$row->product_id   = $product['product_id'];
			$row->product_name = $product['product_name'];
			$row->product_sku  = $product['product_sku'];
			$row->quantity     = $product['quantity'];
			$row->price        = $product['price'];
			$row->total_price  = $product['total_price'];
			$row->tax          = $tax->getTax($product['price'], $product['product_taxclass_id']);
			$row->store();
			$orderProductId = $row->id;

			// Order Options
			foreach ($product['option_data'] as $option)
			{
				$row                          = Table::getInstance('Eshop', 'Orderoptions');
				$row->id                      = '';
				$row->order_id                = $orderId;
				$row->product_id              = $product['product_id'];
				$row->order_product_id        = $orderProductId;
				$row->product_option_id       = $option['product_option_id'];
				$row->product_option_value_id = $option['product_option_value_id'];
				$row->option_name             = $option['option_name'];
				$row->option_value            = $option['option_value'];
				$row->option_type             = $option['option_type'];
				$row->quantity                = $product['quantity'];
				$row->price                   = $option['price'];
				$row->sku                     = $option['sku'];
				$row->store();
			}

			// Order Downloads
			foreach ($product['download_data'] as $download)
			{
				$row                   = Table::getInstance('Eshop', 'Orderdownloads');
				$row->id               = '';
				$row->order_id         = $orderId;
				$row->order_product_id = $orderProductId;
				$row->download_id      = $download['id'];
				$row->download_name    = $download['download_name'];
				$row->filename         = $download['filename'];

				//Generate download code
				$downloadCode = '';

				while (true)
				{
					$downloadCode = UserHelper::genRandomPassword(10);

					$query->clear()
						->select('COUNT(*)')
						->from('#__eshop_orderdownloads')
						->where('download_code = "' . $downloadCode . '"');
					$db->setQuery($query);

					if (!$db->loadResult())
					{
						break;
					}
				}

				$row->download_code = $downloadCode;
				$row->remaining     = $download['total_downloads_allowed'];
				$row->store();
			}
		}

		// Store Order Totals
		foreach ($data['totals'] as $total)
		{
			$row           = Table::getInstance('Eshop', 'Ordertotals');
			$row->id       = '';
			$row->order_id = $orderId;
			$row->name     = $total['name'];
			$row->title    = $total['title'];
			$row->text     = $total['text'];
			$row->value    = $total['value'];
			$row->store();
		}

		PluginHelper::importPlugin('eshop');
		$app->triggerEvent('onAfterStoreOrder', [$orderRow]);
		$data['order_id'] = $orderId;

		// Prepare products data
		$productData = [];

		foreach ($cart->getCartData() as $product)
		{
			$optionData = [];

			foreach ($product['option_data'] as $option)
			{
				$optionData[] = [
					'option_name'  => $option['option_name'],
					'option_value' => $option['option_value'],
				];
			}

			$productData[] = [
				'product_name' => $product['product_name'],
				'product_sku'  => $product['product_sku'],
				'option_data'  => $optionData,
				'quantity'     => $product['quantity'],
				'weight'       => $product['weight'],
				'price'        => round(
					$currency->convert($product['price'], EShopHelper::getConfigValue('default_currency_code'), $data['currency_code']),
					2
				),
			];
		}

		//Get total for shipping, taxes
		$otherTotal                   = round(
			$currency->convert($data['total'] - $cart->getSubTotal(), EShopHelper::getConfigValue('default_currency_code'), $data['currency_code']),
			2
		);
		$data['discount_amount_cart'] = 0;

		if ($otherTotal > 0)
		{
			$productData[] = [
				'product_name' => Text::_('ESHOP_SHIPPING_DISCOUNTS_AND_TAXES'),
				'product_sku'  => '',
				'option_data'  => [],
				'quantity'     => 1,
				'weight'       => 0,
				'price'        => $otherTotal,
			];
		}
		else
		{
			$data['discount_amount_cart'] -= $otherTotal;
		}

		$data['products'] = $productData;

		if ($session->get('newsletter_interest'))
		{
			if (EShopHelper::getConfigValue('acymailing_integration') && is_file(
					JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php'
				))
			{
				$acyMailingIntegration = true;
			}
			else
			{
				$acyMailingIntegration = false;
			}

			$mailchimpIntegration = EShopHelper::getConfigValue('mailchimp_integration');

			foreach ($cart->getCartData() as $product)
			{
				//Store customer to AcyMailing
				if ($acyMailingIntegration)
				{
					$this->processACYMailingIntegration($row, $data, $product);
				}

				//Store subscriber to MailChimp
				if ($mailchimpIntegration)
				{
					$this->processMailChimpIntegration($row, $data, $product);
				}
			}
		}

		if ($orderTotal > 0)
		{
			// Process Payment here
			$paymentMethod = $data['payment_method'];
			require_once JPATH_COMPONENT . '/plugins/payment/' . $paymentMethod . '.php';

			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('params, title')
				->from('#__eshop_payments')
				->where('name = ' . $db->quote($paymentMethod));
			$db->setQuery($query);

			$plugin       = $db->loadObject();
			$params       = new Registry($plugin->params);
			$paymentClass = new $paymentMethod($params);
			$paymentClass->setTitle($plugin->title);

			$rf = new ReflectionMethod($paymentClass, 'processPayment');

			if ($rf->getNumberOfParameters() == '1')
			{
				$paymentClass->processPayment($data);
			}
			else
			{
				$paymentClass->processPayment($orderRow, $data);
			}
		}
		else
		{
			// If total = 0, then complete order
			$row = Table::getInstance('Eshop', 'Order');
			$id  = $data['order_id'];
			$row->load($id);
			$row->order_status_id = EShopHelper::getConfigValue('complete_status_id');
			$row->store();
			EShopHelper::completeOrder($row);
			PluginHelper::importPlugin('eshop');
			$app->triggerEvent('onAfterCompleteOrder', [$row]);

			//Send confirmation email here
			if (EShopHelper::getConfigValue('order_alert_mail'))
			{
				EShopHelper::sendEmails($row);
			}

			$app->redirect(Route::_(EShopRoute::getViewRoute('checkout') . '&layout=complete'));
		}
	}

	/**
	 * Function to verify payment
	 */
	public function verifyPayment()
	{
		$paymentMethod = Factory::getApplication()->input->getCmd('payment_method');
		require_once JPATH_COMPONENT . '/plugins/payment/' . $paymentMethod . '.php';

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('params')
			->from('#__eshop_payments')
			->where('name = ' . $db->quote($paymentMethod));
		$db->setQuery($query);
		$plugin       = $db->loadObject();
		$params       = new Registry($plugin->params);
		$paymentClass = new $paymentMethod($params);

		$paymentClass->verifyPayment();
	}

	/**
	 * Function to cancel order
	 */
	public function cancelOrder()
	{
		$app         = Factory::getApplication();
		$session     = $app->getSession();
		$input       = $app->input;
		$orderNumber = $input->getString('order_number');

		if ($orderNumber == '')
		{
			$orderNumber = $session->get('order_number');
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__eshop_orders')
			->where('order_number = ' . $db->quote($orderNumber));
		$db->setQuery($query);
		$row = $db->loadObject();

		if (is_object($row))
		{
			if (EShopHelper::canCancelOrder($row))
			{
				// Change order status first
				$order = Table::getInstance('Eshop', 'Order');
				$order->load($row->id);
				$order->order_status_id = EShopHelper::getConfigValue('canceled_status_id');
				$order->store();

				// Trigger onAfterCancelOrder event
				PluginHelper::importPlugin('eshop');
				$app->triggerEvent('onAfterCancelOrder', [$row]);

				//Send cancel notification email here
				if (EShopHelper::getConfigValue('order_cancel_mail_admin', 1))
				{
					EShopHelper::sendAdminNotifyEmails($row, 'cancel');
				}

				$app->redirect(Route::_(EShopRoute::getViewRoute('checkout') . '&layout=cancel'));
			}
			else
			{
				$session->set('warning', Text::_('ESHOP_ORDER_CAN_NOT_CANCELLED'));
				$app->redirect(Route::_(EShopRoute::getViewRoute('categories')));
			}
		}
		else
		{
			$session->set('warning', Text::_('ESHOP_ORDER_NOT_EXISTED'));
			$app->redirect(Route::_(EShopRoute::getViewRoute('categories')));
		}
	}

	/**
	 *
	 * Function to get Cart Data
	 */
	public function getCartData()
	{
		$cart = new EShopCart();

		if (!$this->cartData)
		{
			$this->cartData = $cart->getCartData();
		}

		return $this->cartData;
	}

	/**
	 *
	 * Function to get Costs
	 */
	public function getCosts()
	{
		$totalData = [];
		$total     = 0;
		$taxes     = [];
		$this->getSubTotalCosts($totalData, $total, $taxes);
		$this->getDiscountCosts($totalData, $total, $taxes);
		$this->getShippingCosts($totalData, $total, $taxes);
		$this->getDonateCosts($totalData, $total, $taxes);
		$this->getCouponCosts($totalData, $total, $taxes);
		$this->getPaymentFeeCosts($totalData, $total, $taxes);
		$this->getTaxesCosts($totalData, $total, $taxes);
		$this->getVoucherCosts($totalData, $total, $taxes);
		$this->getTotalCosts($totalData, $total, $taxes);
		$this->totalData = $totalData;
		$this->total     = $total;
		$this->taxes     = $taxes;
	}

	/**
	 *
	 * Function to get Sub Total Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getSubTotalCosts(&$totalData, &$total, &$taxes)
	{
		$cart        = new EShopCart();
		$currency    = EShopCurrency::getInstance();
		$total       = $cart->getSubTotal();
		$totalData[] = [
			'name'  => 'sub_total',
			'title' => Text::_('ESHOP_SUB_TOTAL'),
			'text'  => $currency->format(max(0, $total)),
			'value' => max(0, $total),
		];
		$taxes       = $cart->getTaxes();
	}

	/**
	 *
	 * Function to get Discount Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getDiscountCosts(&$totalData, &$total, &$taxes)
	{
		$discount = new EShopDiscount();
		$discount->getCosts($totalData, $total, $taxes);
	}

	/**
	 *
	 * Function to get Coupon Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getCouponCosts(&$totalData, &$total, &$taxes)
	{
		$coupon = new EShopCoupon();
		$coupon->getCosts($totalData, $total, $taxes);
	}

	/**
	 *
	 * Function to get Voucher Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getVoucherCosts(&$totalData, &$total, &$taxes)
	{
		$voucher = new EShopVoucher();
		$voucher->getCosts($totalData, $total, $taxes);
	}

	/**
	 *
	 * Function to get Shipping Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getShippingCosts(&$totalData, &$total, &$taxes)
	{
		$shipping = new EShopShipping();
		$shipping->getCosts($totalData, $total, $taxes);
	}

	/**
	 *
	 * Function to get Payment Fee Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getPaymentFeeCosts(&$totalData, &$total, &$taxes)
	{
		$payment = new EShopPayment();
		$payment->getCosts($totalData, $total, $taxes);
	}

	/**
	 *
	 * Function to get Donate Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getDonateCosts(&$totalData, &$total, &$taxes)
	{
		$donate = new EShopDonate();
		$donate->getCosts($totalData, $total, $taxes);
	}

	/**
	 *
	 * Function to get Taxes Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getTaxesCosts(&$totalData, &$total, &$taxes)
	{
		$tax = new EShopTax(EShopHelper::getConfig());
		$tax->getCosts($totalData, $total, $taxes);
	}

	/**
	 *
	 * Function to get Total Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getTotalCosts(&$totalData, &$total, &$taxes)
	{
		$currency    = EShopCurrency::getInstance();
		$totalData[] = [
			'name'  => 'total',
			'title' => Text::_('ESHOP_TOTAL'),
			'text'  => $currency->format(max(0, $total)),
			'value' => max(0, $total),
		];
	}

	/**
	 *
	 * Function to get Total Data
	 */
	public function getTotalData()
	{
		return $this->totalData;
	}

	/**
	 *
	 * Function to get Total
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 *
	 * Function to get Taxes
	 */
	public function getTaxes()
	{
		return $this->taxes;
	}

	/**
	 * Method to valid and store EU VAT Number
	 *
	 * @param   array  $data  This data array which contains VAT NUMBER need to be validated
	 *
	 * @return void
	 */
	protected function validateEUTaxNumber($data): void
	{
		$app = Factory::getApplication();

		if (array_key_exists('eu_vat_number', $data))
		{
			$euVatNumber = $data['eu_vat_number'];
		}
		else
		{
			$euVatNumber = $app->input->get('eu_vat_number');
		}

		if ($euVatNumber != '' && EShopEuvat::validateEUVATNumber($euVatNumber))
		{
			$app->getSession()->set('eu_vat_number', $euVatNumber);
		}
		else
		{
			$app->getSession()->clear('eu_vat_number');
		}
	}

	/**
	 * Validate to make sure user is logged in and cart has products
	 *
	 * @param   User       $user
	 * @param   EShopCart  $cart
	 *
	 * @return array
	 */
	protected function validateRequireUserLoggedInAndCartHasProducts($user, $cart): array
	{
		$json = [];

		// If user is already logged in, return to checkout page
		if (!$user->get('id'))
		{
			if (EShopHelper::getConfigValue('active_https'))
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
			}
			else
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'));
			}
		}

		// Validate products in the cart
		if (!$cart->hasProducts())
		{
			$json['return'] = Route::_(EShopRoute::getViewRoute('cart'));
		}

		return $json;
	}

	/**
	 * Validate to make sure user is not logged in and cart has products
	 *
	 * @param   User       $user
	 * @param   EShopCart  $cart
	 *
	 * @return array
	 */
	protected function validateRequireUserNotLoggedInAndCartHasProducts($user, $cart): array
	{
		$json = [];

		// If user is already logged in, return to checkout page
		if ($user->get('id'))
		{
			if (EShopHelper::getConfigValue('active_https'))
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
			}
			else
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'));
			}
		}

		// Validate products in the cart
		if (!$cart->hasProducts())
		{
			$json['return'] = Route::_(EShopRoute::getViewRoute('cart'));
		}

		return $json;
	}

	/**
	 * Method to validate data for new user account
	 *
	 * @param   array  $data  The request data
	 *
	 * @return array
	 */
	protected function validateNewUserAccountData($data)
	{
		$errors = [];
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);

		//Email validate
		if ($data['email'] != '')
		{
			$query->select('COUNT(*)')
				->from('#__users')
				->where('email = ' . $db->quote($data['email']));
			$db->setQuery($query);

			if ($db->loadResult())
			{
				$errors['email'] = Text::_('ESHOP_ERROR_EMAIL_EXISTED');
			}
		}

		// Username validate
		if ($data['username'] == '')
		{
			$errors['username'] = Text::_('ESHOP_ERROR_USERNAME');
		}
		else
		{
			$query->clear()
				->select('COUNT(*)')
				->from('#__users')
				->where('username = ' . $db->quote($data['username']));
			$db->setQuery($query);

			if ($db->loadResult())
			{
				$errors['username'] = Text::_('ESHOP_ERROR_USERNAME_EXISTED');
			}
		}

		// Password validate
		if ($data['password1'] == '')
		{
			$errors['password1'] = Text::_('ESHOP_ERROR_PASSWORD');
		}

		// Confirm password validate
		if ($data['password1'] != $data['password2'])
		{
			$errors['password2'] = Text::_('ESHOP_ERROR_CONFIRM_PASSWORD');
		}

		return $errors;
	}

	/**
	 * Method to create new user account, return errors if something bad happens
	 *
	 * @param   array  $data
	 *
	 * @return array
	 */
	protected function createNewUserAccount(&$data): array
	{
		$json = [];

		// Load com_users language file
		$lang = Factory::getLanguage();
		$tag  = $lang->getTag();

		if (!$tag)
		{
			$tag = 'en-GB';
		}

		$lang->load('com_users', JPATH_ROOT, $tag);
		$data['name'] = $data['firstname'];

		if (isset($data['lastname']))
		{
			$data['name'] .= ' ' . $data['lastname'];
		}

		$data['password'] = $data['password2'] = $data['password1'];
		$data['email1']   = $data['email2'] = $data['email'];

		$user             = new User();
		$params           = ComponentHelper::getParams('com_users');
		$data['groups']   = [];
		$data['groups'][] = $params->get('new_usertype', 2);
		$data['block']    = 0;

		if (!$user->bind($data))
		{
			$json['error']['warning'] = Text::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError());
		}
		else
		{
			// Store the data.
			if (!$user->save())
			{
				$json['error']['warning'] = Text::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError());
			}
		}

		return $json;
	}

	/**
	 * Method to get Customer Group ID selected by user during registration
	 *
	 * @param   array  $data  The request data array, it is not used now but in the future, we should
	 *                        change the code to get selected customer user group from data array instead
	 *                        of getting it from global input
	 *
	 * @return int
	 */
	protected function getSelectedCustomerGroup($data)
	{
		$customerGroupId      = EShopHelper::getConfigValue('customergroup_id');
		$customerGroupDisplay = EShopHelper::getConfigValue('customer_group_display');

		if ($customerGroupDisplay != '')
		{
			$customerGroupDisplay = explode(',', $customerGroupDisplay);

			$selectedCustomerGroupId = Factory::getApplication()->input->getInt('customergroup_id', 0);

			if ($selectedCustomerGroupId && in_array($selectedCustomerGroupId, $customerGroupDisplay))
			{
				$customerGroupId = $selectedCustomerGroupId;
			}
		}

		return $customerGroupId;
	}

	/**
	 * Method to get address (billing/shipping) data from request data
	 *
	 * @param   array    $fields
	 * @param   array    $data
	 * @param ?stdClass  $country
	 * @param ?stdClass  $zone
	 *
	 * @return array
	 */
	protected function getAddressInformation($fields, $data, $country, $zone): array
	{
		$address = [];

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			$address[$fieldName] = $data[$fieldName] ?? null;
		}

		if (is_object($country))
		{
			$address['country_id']   = $country->id;
			$address['country_name'] = $country->country_name;
			$address['iso_code_2']   = $country->iso_code_2;
			$address['iso_code_3']   = $country->iso_code_3;
		}
		else
		{
			$address['country_id']   = 0;
			$address['country_name'] = '';
			$address['iso_code_2']   = '';
			$address['iso_code_3']   = '';
		}

		if (is_object($zone))
		{
			$address['zone_name'] = $zone->zone_name;
			$address['zone_code'] = $zone->zone_code;
		}
		else
		{
			$address['zone_name'] = '';
			$address['zone_code'] = '';
		}

		return $address;
	}

	/**
	 * Method to process ACYMailing integration
	 *
	 * @param   OrderEshop  $row
	 * @param   array       $data
	 * @param   array       $product
	 *
	 * @return void
	 */
	protected function processACYMailingIntegration($row, $data, $product): void
	{
		$params  = new Registry($product['params']);
		$listIds = $params->get('acymailing_list_ids', '');

		if ($listIds == '')
		{
			$listIds = EShopHelper::getConfigValue('acymailing_list_ids', '');
		}

		if ($listIds != '')
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';
			$userClass = acymailing_get('class.subscriber');
			$subId     = $userClass->subid($row->email);

			if (!$subId)
			{
				$myUser         = new stdClass();
				$myUser->email  = $data['email'];
				$myUser->name   = $data['firstname'] . ' ' . $data['lastname'];
				$myUser->userid = $data['customer_id'];
				$eventClass     = acymailing_get('class.subscriber');
				$subId          = $eventClass->save($myUser);
			}

			$listIds    = explode(',', $listIds);
			$newProduct = [];

			foreach ($listIds as $listId)
			{
				$newList             = [];
				$newList['status']   = 1;
				$newProduct[$listId] = $newList;
			}

			$userClass->saveSubscription($subId, $newProduct);
		}
	}

	/**
	 * Method to process MailChimp integration
	 *
	 * @param   OrderEshop  $row
	 * @param   array       $data
	 * @param   array       $product
	 *
	 * @return void
	 * @throws Exception
	 */
	protected function processMailChimpIntegration($row, $data, $product): void
	{
		$params  = new Registry($product['params']);
		$listIds = $params->get('mailchimp_list_ids', '');

		if ($listIds != '')
		{
			$listIds = explode(',', $listIds);

			if (count($listIds))
			{
				require_once JPATH_SITE . '/components/com_eshop/helpers/MailChimp.php';

				$mailchimp = new MailChimp(EShopHelper::getConfigValue('api_key_mailchimp'));

				foreach ($listIds as $listId)
				{
					if ($listId)
					{
						/*
						$mailchimp->call('lists/subscribe', array(
							'id'                => $listId,
							'email'             => array('email' => $data['email']),
							'merge_vars'        => array('FNAME' => $data['firstname'], 'LNAME' => $data['lastname']),
							'double_optin'      => false,
							'update_existing'   => true,
							'replace_interests' => false,
							'send_welcome'      => false,
						));
						*/

						$data = [
							'skip_merge_validation' => true,
							'id'                    => $listId,
							'email_address'         => $data['email'],
							'merge_fields'          => [],
							'status'                => 'subscribed',
							'update_existing'       => true,
						];

						if ($data['firstname'])
						{
							$data['merge_fields']['FNAME'] = $data['firstname'];
						}

						if ($data['lastname'])
						{
							$data['merge_fields']['LNAME'] = $data['lastname'];
						}

						if ($data['payment_address_1'] && $data['payment_address_2'] && $data['payment_city'] && $data['payment_zone_name'] && $data['payment_postcode'])
						{
							$data['merge_fields']['ADDRESS'] = [
								'addr1'   => $data['payment_address_1'],
								'addr2'   => $data['payment_address_2'],
								'city'    => $data['payment_city'],
								'state'   => $data['payment_zone_name'],
								'zip'     => $data['payment_postcode'],
								'country' => $data['payment_country_name'],
							];
						}

						if ($data['telephone'])
						{
							$data['merge_fields']['PHONE'] = $data['telephone'];
						}

						$result = $mailchimp->post("lists/$listId/members", $data);
					}
				}
			}
		}
	}
}