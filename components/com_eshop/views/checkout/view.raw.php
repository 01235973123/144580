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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * HTML View class for EShop component
 *
 * @static
 *
 * @package    Joomla
 * @subpackage EShop
 * @since      1.5
 */
class EShopViewCheckout extends EShopView
{
	/**
	 *
	 * @var $user
	 */
	protected $user;

	/**
	 *
	 * @var $shipping_required
	 */
	protected $shipping_required;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	/**
	 *
	 * @var $form
	 */
	protected $form;

	/**
	 *
	 * @var $lists
	 */
	protected $lists;

	/**
	 *
	 * @var $payment_zone_id
	 */
	protected $payment_zone_id;

	/**
	 *
	 * @var $accountTermsLink
	 */
	protected $accountTermsLink;

	/**
	 *
	 * @var $shipping_zone_id
	 */
	protected $shipping_zone_id;

	/**
	 *
	 * @var $shipping_methods
	 */
	protected $shipping_methods;

	/**
	 *
	 * @var $shipping_method
	 */
	protected $shipping_method;

	/**
	 *
	 * @var $delivery_date
	 */
	protected $delivery_date;

	/**
	 *
	 * @var $comment
	 */
	protected $comment;

	/**
	 *
	 * @var $methods
	 */
	protected $methods;

	/**
	 *
	 * @var $paymentMethod
	 */
	protected $paymentMethod;

	/**
	 *
	 * @var $checkoutTermsLink
	 */
	protected $checkoutTermsLink;

	/**
	 *
	 * @var $privacyPolicyArticleLink
	 */
	protected $privacyPolicyArticleLink;

	/**
	 *
	 * @var $coupon_code
	 */
	protected $coupon_code;

	/**
	 *
	 * @var $voucher_code
	 */
	protected $voucher_code;

	/**
	 *
	 * @var $checkout_terms_agree
	 */
	protected $checkout_terms_agree;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	/**
	 *
	 * @var $cartData
	 */
	protected $cartData;

	/**
	 *
	 * @var $totalData
	 */
	protected $totalData;

	/**
	 *
	 * @var $total
	 */
	protected $total;

	/**
	 *
	 * @var $taxes
	 */
	protected $taxes;

	/**
	 *
	 * @var $tax
	 */
	protected $tax;

	/**
	 *
	 * @var $success
	 */
	protected $success;

	/**
	 *
	 * @var $paymentClass
	 */
	protected $paymentClass;

	public function display($tpl = null)
	{
		$cart                    = new EShopCart();
		$this->user              = Factory::getUser();
		$this->shipping_required = $cart->hasShipping();
		$this->bootstrapHelper   = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

		switch ($this->getLayout())
		{
			case 'login':
				$this->displayLogin($tpl);
				break;
			case 'guest':
				$this->displayGuest($tpl);
				break;
			case 'register':
				$this->displayRegister($tpl);
				break;
			case 'payment_address':
				$this->displayPaymentAddress($tpl);
				break;
			case 'shipping_address':
				$this->displayShippingAddress($tpl);
				break;
			case 'guest_shipping':
				$this->displayGuestShipping($tpl);
				break;
			case 'shipping_method':
				$this->displayShippingMethod($tpl);
				break;
			case 'payment_method':
				$this->displayPaymentMethod($tpl);
				break;
			case 'confirm':
				$this->displayConfirm($tpl);
				break;
			default:
				break;
		}
	}

	/**
	 *
	 * @param   string  $tpl
	 */
	protected function displayLogin($tpl = null)
	{
		parent::display($tpl);
	}

	/**
	 *
	 * Function to display Guest layout
	 *
	 * @param   string  $tpl
	 */
	protected function displayGuest($tpl = null)
	{
		$lists   = [];
		$session = Factory::getApplication()->getSession();
		$guest   = $session->get('guest');
		$fields  = EShopHelper::getFormFields('B');
		$form    = new EshopRADForm($fields);

		if (is_array($guest))
		{
			$form->bind($guest);

			if (isset($guest['payment']))
			{
				$form->bind($guest['payment']);
			}
		}

		// Prepare default data for zone - start
		if (EShopHelper::isFieldPublished('zone_id'))
		{
			if (EShopHelper::isFieldPublished('country_id'))
			{
				$countryField = $form->getField('country_id');
				$countryId    = (int) $session->get('payment_country_id') ? $session->get('payment_country_id') : $countryField->getValue();
			}
			else
			{
				$countryId = EShopHelper::getConfigValue('country_id');
			}


			if ($countryId)
			{
				$zoneField = $form->getField('zone_id');

				if ($zoneField instanceof EshopRADFormFieldZone)
				{
					$zoneField->setCountryId($countryId);
				}
			}
		}

		// Prepare default data for zone - end
		$this->getCustomerGroupList($lists, $guest['customergroup_id'] ?? '');
		$this->form            = $form;
		$this->lists           = $lists;
		$this->payment_zone_id = $session->get('payment_zone_id');

		parent::display($tpl);
	}

	/**
	 *
	 * Function to display Register layout
	 *
	 * @param   string  $tpl
	 */
	protected function displayRegister($tpl = null)
	{
		$lists   = [];
		$session = Factory::getApplication()->getSession();
		$fields  = EShopHelper::getFormFields('B');
		$form    = new EshopRADForm($fields);

		// Prepare default data for zone - start
		if (EShopHelper::isFieldPublished('zone_id'))
		{
			if (EShopHelper::isFieldPublished('country_id'))
			{
				$countryField = $form->getField('country_id');
				$countryId    = (int) $session->get('payment_country_id') ? $session->get('payment_country_id') : $countryField->getValue();
			}
			else
			{
				$countryId = EShopHelper::getConfigValue('country_id');
			}

			if ($countryId)
			{
				$zoneField = $form->getField('zone_id');

				if ($zoneField instanceof EshopRADFormFieldZone)
				{
					$zoneField->setCountryId($countryId);
				}
			}
		}
		// Prepare default data for zone - end
		$accountTerms = EShopHelper::getConfigValue('account_terms');

		if ($accountTerms)
		{
			JLoader::register('ContentHelperRoute', JPATH_ROOT . '/components/com_content/helpers/route.php');

			if (Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
			{
				$associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $accountTerms);
				$langCode     = Factory::getLanguage()->getTag();

				if (isset($associations[$langCode]))
				{
					$article = EShopHelper::getArticle($associations[$langCode]->id);
				}
				else
				{
					$article = EShopHelper::getArticle($accountTerms);
				}
			}
			else
			{
				$article = EShopHelper::getArticle($accountTerms);
			}

			if (is_object($article))
			{
				$this->accountTermsLink = ContentHelperRoute::getArticleRoute($article->id, $article->catid) . '&tmpl=component&format=html';
			}
		}

		$this->getCustomerGroupList($lists);
		$this->form            = $form;
		$this->lists           = $lists;
		$this->payment_zone_id = $session->get('payment_zone_id');

		parent::display($tpl);
	}

	/**
	 *
	 * Function to display Payment Address layout
	 *
	 * @param   string  $tpl
	 */
	protected function displayPaymentAddress($tpl = null)
	{
		$lists    = [];
		$session  = Factory::getApplication()->getSession();
		$customer = $session->get('customer');
		$fields   = EShopHelper::getFormFields('B');
		$form     = new EshopRADForm($fields);

		if (!EShopHelper::getConfigValue('enable_existing_addresses') && is_array($customer) && isset($customer['payment']))
		{
			$form->bind($customer['payment']);
		}

		// Prepare default data for zone - start
		if (EShopHelper::isFieldPublished('zone_id'))
		{
			if (EShopHelper::isFieldPublished('country_id'))
			{
				$countryField = $form->getField('country_id');
				$countryId    = (int) $session->get('payment_country_id') ? $session->get('payment_country_id') : $countryField->getValue();
			}
			else
			{
				$countryId = EShopHelper::getConfigValue('country_id');
			}

			if ($countryId)
			{
				$zoneField = $form->getField('zone_id');

				if ($zoneField instanceof EshopRADFormFieldZone)
				{
					$zoneField->setCountryId($countryId);
				}
			}
		}

		// Prepare default data for zone - end
		$this->getAddressList($lists, $session->get('payment_address_id'));
		$this->form            = $form;
		$this->lists           = $lists;
		$this->payment_zone_id = $session->get('payment_zone_id');

		parent::display($tpl);
	}

	/**
	 *
	 * Function to display Shipping Address layout
	 *
	 * @param   string  $tpl
	 */
	protected function displayShippingAddress($tpl = null)
	{
		$lists    = [];
		$session  = Factory::getApplication()->getSession();
		$customer = $session->get('customer');
		$fields   = EShopHelper::getFormFields('S');
		$form     = new EshopRADForm($fields);

		if (!EShopHelper::getConfigValue('enable_existing_addresses') && is_array($customer) && isset($customer['shipping']))
		{
			$form->bind($customer['shipping']);
		}

		// Prepare default data for zone - start
		if (EShopHelper::isFieldPublished('zone_id'))
		{
			if (EShopHelper::isFieldPublished('country_id'))
			{
				$countryField = $form->getField('country_id');
				$countryId    = (int) $session->get('shipping_country_id') ? $session->get('shipping_country_id') : $countryField->getValue();
			}
			else
			{
				$countryId = EShopHelper::getConfigValue('country_id');
			}

			if ($countryId)
			{
				$zoneField = $form->getField('zone_id');

				if ($zoneField instanceof EshopRADFormFieldZone)
				{
					$zoneField->setCountryId($countryId);
				}
			}
		}

		// Prepare default data for zone - end
		$this->getAddressList($lists, $session->get('shipping_address_id'));
		$this->form             = $form;
		$this->lists            = $lists;
		$this->shipping_zone_id = $session->get('shipping_zone_id');

		parent::display($tpl);
	}

	/**
	 *
	 * Function to display Guest Shipping layout
	 *
	 * @param   string  $tpl
	 */
	protected function displayGuestShipping($tpl = null)
	{
		$session = Factory::getApplication()->getSession();
		$guest   = $session->get('guest');
		$fields  = EShopHelper::getFormFields('S');
		$form    = new EshopRADForm($fields);

		if (is_array($guest) && isset($guest['shipping']))
		{
			$shipping = $guest['shipping'];
			$form->bind($shipping);
		}

		// Prepare default data for zone - start
		if (EShopHelper::isFieldPublished('zone_id'))
		{
			if (EShopHelper::isFieldPublished('country_id'))
			{
				$countryField = $form->getField('country_id');
				$countryId    = (int) $session->get('shipping_country_id') ? $session->get('shipping_country_id') : $countryField->getValue();
			}
			else
			{
				$countryId = EShopHelper::getConfigValue('country_id');
			}

			if ($countryId)
			{
				$zoneField = $form->getField('zone_id');

				if ($zoneField instanceof EshopRADFormFieldZone)
				{
					$zoneField->setCountryId($countryId);
				}
			}
		}

		// Prepare default data for zone - end
		$this->form             = $form;
		$this->shipping_zone_id = $session->get('shipping_zone_id');

		parent::display($tpl);
	}

	/**
	 *
	 * Function to display Shipping Method layout
	 *
	 * @param   string  $tpl
	 */
	protected function displayShippingMethod($tpl = null)
	{
		$session = Factory::getApplication()->getSession();
		$user    = Factory::getUser();

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
			$this->shipping_methods = $session->get('shipping_methods');
		}

		$shippingMethod = $session->get('shipping_method');

		if (is_array($shippingMethod))
		{
			$this->shipping_method = $shippingMethod['name'];
		}
		else
		{
			$this->shipping_method = '';
		}

		$this->delivery_date = $session->get('delivery_date') ?: '';
		$this->comment       = $session->get('comment') ?: '';

		parent::display($tpl);
	}

	/**
	 *
	 * Function to display Payment Method layout
	 *
	 * @param   string  $tpl
	 */
	protected function displayPaymentMethod($tpl = null)
	{
		$session       = Factory::getApplication()->getSession();
		$paymentMethod = $this->input->post->getString('payment_method', os_payments::getDefautPaymentMethod());

		if (!$paymentMethod)
		{
			$paymentMethod = os_payments::getDefautPaymentMethod();
		}

		$this->comment       = $session->get('comment') ?: '';
		$this->methods       = os_payments::getPaymentMethods();
		$this->paymentMethod = $paymentMethod;

		$this->checkoutTermsLink        = '';
		$this->privacyPolicyArticleLink = '';

		if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'payment_method_step')
		{
			$checkoutTerms = EShopHelper::getConfigValue('checkout_terms');

			if ($checkoutTerms > 0)
			{
				$this->checkoutTermsLink = Route::_(EShopHelper::getArticleUrl($checkoutTerms));
			}

			$privacyPolicyArticle = EShopHelper::getConfigValue('privacy_policy_article');

			if ($privacyPolicyArticle > 0)
			{
				$this->privacyPolicyArticleLink = Route::_(EShopHelper::getArticleUrl($privacyPolicyArticle));
			}
		}

		$this->coupon_code          = $session->get('coupon_code');
		$this->voucher_code         = $session->get('voucher_code');
		$this->checkout_terms_agree = $session->get('checkout_terms_agree');
		$this->currency             = EShopCurrency::getInstance();

		parent::display($tpl);
	}

	/**
	 *
	 * Function to display Confirm layout
	 *
	 * @param   string  $tpl
	 */
	protected function displayConfirm($tpl = null)
	{
		// Get information for the order
		$session  = Factory::getApplication()->getSession();
		$tax      = new EShopTax(EShopHelper::getConfig());
		$currency = EShopCurrency::getInstance();
		$cartData = $this->get('CartData');
		$model    = $this->getModel();
		$model->getCosts();
		$totalData       = $model->getTotalData();
		$total           = $model->getTotal();
		$taxes           = $model->getTaxes();
		$this->cartData  = $cartData;
		$this->totalData = $totalData;
		$this->total     = $total;
		$this->taxes     = $taxes;
		$this->tax       = $tax;
		$this->currency  = $currency;

		// Success message
		if ($session->get('success'))
		{
			$this->success = $session->get('success');
			$session->clear('success');
		}

		if ($total > 0)
		{
			// Payment method
			$db            = Factory::getDbo();
			$query         = $db->getQuery(true);
			$paymentMethod = $session->get('payment_method');
			require_once JPATH_COMPONENT . '/plugins/payment/' . $paymentMethod . '.php';
			$query->select('params')
				->from('#__eshop_payments')
				->where('name = "' . $paymentMethod . '"');
			$db->setQuery($query);
			$plugin             = $db->loadObject();
			$params             = new Registry($plugin->params);
			$paymentClass       = new $paymentMethod($params);
			$this->paymentClass = $paymentClass;
		}

		$this->checkoutTermsLink        = '';
		$this->privacyPolicyArticleLink = '';

		if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'confirm_step')
		{
			$checkoutTerms = EShopHelper::getConfigValue('checkout_terms');

			if ($checkoutTerms > 0)
			{
				$this->checkoutTermsLink = Route::_(EShopHelper::getArticleUrl($checkoutTerms));
			}

			$privacyPolicyArticle = EShopHelper::getConfigValue('privacy_policy_article');

			if ($privacyPolicyArticle > 0)
			{
				$this->privacyPolicyArticleLink = Route::_(EShopHelper::getArticleUrl($privacyPolicyArticle));
			}
		}

		parent::display($tpl);
	}

	/**
	 *
	 * Private method to get Customer Group List
	 *
	 * @param   array  $lists
	 */
	protected function getCustomerGroupList(&$lists, $selected = '')
	{
		if (!$selected)
		{
			$selected = EShopHelper::getConfigValue('customergroup_id');
		}

		$customerGroupDisplay = EShopHelper::getConfigValue('customer_group_display');
		$countCustomerGroup   = count(explode(',', $customerGroupDisplay));

		if ($countCustomerGroup > 1)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id, b.customergroup_name AS name')
				->from('#__eshop_customergroups AS a')
				->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
				->where('a.published = 1')
				->where('b.language = "' . Factory::getLanguage()->getTag() . '"');

			if ($customerGroupDisplay != '')
			{
				$query->where('a.id IN (' . $customerGroupDisplay . ')');
			}

			$query->order('b.customergroup_name');
			$db->setQuery($query);
			$lists['customergroup_id'] = HTMLHelper::_('select.genericlist', $db->loadObjectList(), 'customergroup_id', '', 'id', 'name', $selected);
		}
		elseif ($countCustomerGroup == 1)
		{
			$lists['default_customergroup_id'] = $customerGroupDisplay;
		}
	}

	/**
	 *
	 * Function to get Address List
	 *
	 * @param   array  $lists
	 * @param   mixed  $selected
	 */
	protected function getAddressList(&$lists, $selected = '')
	{
		//Get address list
		$user = Factory::getUser();

		if ($user->get('id'))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			//$query->select('a.id, CONCAT(a.firstname, " ", a.lastname, ", ", a.address_1, ", ", a.city, ", ", IF(z.zone_name <> "", CONCAT(z.zone_name, ", "), ""), c.country_name) AS name')
			$query->select('a.*, z.zone_name, c.country_name')
				->from('#__eshop_addresses AS a')
				->leftJoin('#__eshop_zones AS z ON (a.zone_id = z.id)')
				->leftJoin('#__eshop_countries AS c ON (a.country_id = c.id)')
				->where('a.customer_id = ' . (int) $user->get('id'))
				->where('a.address_1 != ""');
			$db->setQuery($query);
			$addresses = $db->loadObjectList();

			for ($i = 0; $n = count($addresses), $i < $n; $i++)
			{
				$address     = $addresses[$i];
				$addressText = $address->firstname;

				if (EShopHelper::isFieldPublished('lastname') && $address->lastname != '')
				{
					$addressText .= ' ' . $address->lastname;
				}

				$addressText .= ', ' . $address->address_1;

				if (EShopHelper::isFieldPublished('city') && $address->city != '')
				{
					$addressText .= ', ' . $address->city;
				}

				if (EShopHelper::isFieldPublished('zone_id') && $address->zone_name != '')
				{
					$addressText .= ', ' . $address->zone_name;
				}

				if (EShopHelper::isFieldPublished('country_id') && $address->country_id != '')
				{
					$addressText .= ', ' . $address->country_name;
				}

				$addresses[$i]->addressText = $addressText;
			}

			if (!$selected)
			{
				//Get default address
				$query->clear()
					->select('address_id')
					->from('#__eshop_customers')
					->where('customer_id = ' . (int) $user->get('id'));
				$db->setQuery($query);
				$selected = $db->loadResult();
			}

			if (count($addresses))
			{
				$lists['address_id'] = HTMLHelper::_(
					'select.genericlist',
					$addresses,
					'address_id',
					' style="width: 100%; margin-bottom: 15px;" size="5" ',
					'id',
					'addressText',
					$selected
				);
			}
		}
	}
}