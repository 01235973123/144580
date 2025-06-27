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
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\User\UserHelper;

require_once __DIR__ . '/jsonresponse.php';

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerCheckout extends BaseController
{
	use EShopControllerJsonresponse;

	/**
	 * Function to login user
	 */
	public function login()
	{
		Session::checkToken('post') or jexit(Text::_('JInvalid_Token'));

		$app = Factory::getApplication();

		// Populate the data array:
		$data             = [];
		$data['username'] = $this->input->post->get('username', '', 'USERNAME');
		$data['password'] = $this->input->post->get('password', '', 'RAW');

		// Get the log in options.
		$options             = [];
		$options['remember'] = $this->input->post->getBool('remember', false);

		// Get the log in credentials.
		$credentials             = [];
		$credentials['username'] = $data['username'];
		$credentials['password'] = $data['password'];

		$json = [];

		// Perform the log in.
		if (true === $app->login($credentials, $options))
		{
			// Success
			if (EShopHelper::getConfigValue('active_https'))
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
			}
			else
			{
				$json['return'] = Route::_(EShopRoute::getViewRoute('checkout'));
			}
		}
		else
		{
			// Login failed !
			$json['error']['warning'] = Text::_('ESHOP_LOGIN_WARNING');
		}

		$this->sendJsonResponse($json);
	}

	/**
	 * Function to register user
	 */
	public function register()
	{
		$post = $this->getFilteredPostData();

		/* @var EShopModelCheckout $model */
		$model = $this->getModel('Checkout');
		$json  = $model->register($post);

		$this->sendJsonResponse($json);
	}

	/**
	 * Function to guest
	 */
	public function guest()
	{
		$post = $this->getFilteredPostData();

		/* @var EShopModelCheckout $model */
		$model = $this->getModel('Checkout');
		$json  = $model->guest($post);

		$this->sendJsonResponse($json);
	}

	/**
	 * Function to process guest shipping
	 */
	public function processGuestShipping()
	{
		$post = $this->getFilteredPostData();

		/* @var EShopModelCheckout $model */
		$model = $this->getModel('Checkout');
		$json  = $model->processGuestShipping($post);

		$this->sendJsonResponse($json);
	}

	/**
	 * Function to process payment address
	 */
	public function processPaymentAddress()
	{
		$post = $this->getFilteredPostData();

		/* @var EShopModelCheckout $model */
		$model = $this->getModel('Checkout');
		$json  = $model->processPaymentAddress($post);

		$this->sendJsonResponse($json);
	}

	/**
	 * Function to process shipping address
	 */
	public function processShippingAddress()
	{
		$post = $this->getFilteredPostData();

		/* @var EShopModelCheckout $model */
		$model = $this->getModel('Checkout');
		$json  = $model->processShippingAddress($post);

		$this->sendJsonResponse($json);
	}

	/**
	 * Function to process shipping method
	 */
	public function processShippingMethod()
	{
		/* @var EShopModelCheckout $model */
		$model = $this->getModel('Checkout');
		$json  = $model->processShippingMethod();

		$this->sendJsonResponse($json);
	}

	/**
	 * Function to process payment method
	 */
	public function processPaymentMethod()
	{
		/* @var EShopModelCheckout $model */
		$model = $this->getModel('Checkout');
		$json  = $model->processPaymentMethod();

		$model->getCosts();
		$totalData = $model->getTotalData();
		$total     = $model->getTotal();

		$json['total'] = $total;

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to validate captcha from checkout form
	 */
	public function validateCaptcha()
	{
		/* @var \Joomla\CMS\Application\SiteApplication $app */
		$app  = Factory::getApplication();
		$json = [];

		if (EShopHelper::getConfigValue('enable_checkout_captcha'))
		{
			$captchaPlugin = $app->get('captcha');

			if (!$captchaPlugin)
			{
				// Hardcode to recaptcha, reduce support request
				$captchaPlugin = 'recaptcha';
			}

			$plugin = PluginHelper::getPlugin('captcha', $captchaPlugin);

			if ($plugin)
			{
				try
				{
					Captcha::getInstance($captchaPlugin)->checkAnswer($this->input->post->get('recaptcha_response_field', '', 'string'));
				}
				catch (Exception $e)
				{
					$json['error'] = Text::_('ESHOP_INVALID_CAPTCHA');
				}
			}
		}

		if (!$json)
		{
			$json['success'] = true;
		}

		$this->sendJsonResponse($json);
	}

	/**
	 * Function to process order
	 */
	public function processOrder()
	{
		Session::checkToken('post') or jexit(Text::_('JInvalid_Token'));

		$app     = Factory::getApplication();
		$session = $app->getSession();
		$user    = Factory::getUser();

		/* @var EShopModelCheckout $model */
		$model = $this->getModel('Checkout');
		$cart  = new EShopCart();

		// Get information for the order
		$model->getCosts();
		$totalData = $model->getTotalData();
		$total     = $model->getTotal();
		$return    = '';

		if ($cart->hasShipping())
		{
			// Validate if shipping address is set
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
					$return = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
				}
				else
				{
					$return = Route::_(EShopRoute::getViewRoute('checkout'));
				}
			}

			// Validate if shipping method is set
			if (!$session->get('shipping_method'))
			{
				if (EShopHelper::getConfigValue('active_https'))
				{
					$return = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
				}
				else
				{
					$return = Route::_(EShopRoute::getViewRoute('checkout'));
				}
			}
		}
		else
		{
			$session->clear('shipping_method');
			$session->clear('shipping_methods');
		}

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
				$return = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
			}
			else
			{
				$return = Route::_(EShopRoute::getViewRoute('checkout'));
			}
		}

		// Validate if payment method has been set
		if ($total > 0 && !$session->get('payment_method'))
		{
			if (EShopHelper::getConfigValue('active_https'))
			{
				$return = Route::_(EShopRoute::getViewRoute('checkout'), true, 1);
			}
			else
			{
				$return = Route::_(EShopRoute::getViewRoute('checkout'));
			}
		}

		// Validate if cart has products
		if (!$cart->hasProducts())
		{
			$return = Route::_(EShopRoute::getViewRoute('cart'));
		}

		if ($return)
		{
			$app->redirect($return);

			return;
		}

		$filterInput = InputFilter::getInstance();
		$data        = $this->input->post->getArray();
		$data        = $filterInput->clean($data, null);

		// Prepare customer data
		if ($user->get('id'))
		{
			$data['customer_id'] = $user->get('id');
			$data['email']       = $user->get('email');
			$customer            = EShopHelper::getCustomer($user->get('id'));

			if (is_object($customer))
			{
				$data['customergroup_id'] = $customer->customergroup_id;
				$data['firstname']        = $customer->firstname;
				$data['lastname']         = $customer->lastname;
				$data['telephone']        = $customer->telephone;
				$data['fax']              = $customer->fax;
			}
			else
			{
				$data['customergroup_id'] = '';
				$data['firstname']        = '';
				$data['lastname']         = '';
				$data['telephone']        = '';
				$data['fax']              = '';
			}

			$paymentAddress = EShopHelper::getAddress($session->get('payment_address_id'));
		}
		else
		{
			$data['customer_id']      = 0;
			$data['customergroup_id'] = $guest['customergroup_id'];
			$data['firstname']        = $guest['firstname'];
			$data['lastname']         = $guest['lastname'];
			$data['email']            = $guest['email'];
			$data['telephone']        = $guest['telephone'];
			$data['fax']              = $guest['fax'];

			$guest          = $session->get('guest');
			$paymentAddress = $guest['payment'];
		}

		// Prepare payment data
		$billingFields = EShopHelper::getFormFields('B');

		foreach ($billingFields as $field)
		{
			$fieldName = $field->name;

			if (isset($paymentAddress[$fieldName]))
			{
				if (is_array($paymentAddress[$fieldName]))
				{
					$data['payment_' . $fieldName] = json_encode($paymentAddress[$fieldName]);
				}
				else
				{
					$data['payment_' . $fieldName] = $paymentAddress[$fieldName];
				}
			}
			else
			{
				$data['payment_' . $fieldName] = '';
			}
		}

		$data['payment_zone_name']    = $paymentAddress['zone_name'];
		$data['payment_country_name'] = $paymentAddress['country_name'];
		$data['payment_method']       = $session->get('payment_method');
		$data['payment_method_title'] = EShopHelper::getPaymentTitle($data['payment_method']);

		// Prepare shipping data
		$shippingFields = EShopHelper::getFormFields('S');

		if ($cart->hasShipping())
		{
			if ($user->get('id'))
			{
				$shippingAddress = EShopHelper::getAddress($session->get('shipping_address_id'));
			}
			else
			{
				$guest           = $session->get('guest');
				$shippingAddress = $guest['shipping'];
			}

			foreach ($shippingFields as $field)
			{
				$fieldName = $field->name;

				if (isset($shippingAddress[$fieldName]))
				{
					if (is_array($shippingAddress[$fieldName]))
					{
						$data['shipping_' . $fieldName] = json_encode($shippingAddress[$fieldName]);
					}
					else
					{
						$data['shipping_' . $fieldName] = $shippingAddress[$fieldName];
					}
				}
				else
				{
					$data['shipping_' . $fieldName] = '';
				}
			}

			$data['shipping_zone_name']    = $shippingAddress['zone_name'];
			$data['shipping_country_name'] = $shippingAddress['country_name'];
			$shippingMethod                = $session->get('shipping_method');

			if (is_array($shippingMethod))
			{
				$data['shipping_method']       = $shippingMethod['name'];
				$data['shipping_method_title'] = $shippingMethod['title'];
			}
			else
			{
				$data['shipping_method']       = '';
				$data['shipping_method_title'] = '';
			}
		}
		else
		{
			foreach ($shippingFields as $field)
			{
				$fieldName                      = $field->name;
				$data['shipping_' . $fieldName] = '';
			}

			$data['shipping_zone_name']    = '';
			$data['shipping_country_name'] = '';
			$data['shipping_method']       = '';
			$data['shipping_method_title'] = '';
		}

		$data['totals']        = $totalData;
		$data['delivery_date'] = $session->get('delivery_date');

		if (isset($data['delivery_date']) && $data['delivery_date'] != '')
		{
			$data['delivery_date'] = HTMLHelper::_('date', $data['delivery_date'], 'Y-m-d H:i:s');
		}

		$data['comment']         = $session->get('comment');
		$data['order_status_id'] = EShopHelper::getConfigValue('order_status_id');
		$data['language']        = Factory::getLanguage()->getTag();
		$currency                = EShopCurrency::getInstance();
		$data['currency_id']     = $currency->getCurrencyId();
		$data['currency_code']   = $currency->getCurrencyCode();

		if ($session->get('coupon_code'))
		{
			$coupon              = EShopHelper::getCoupon($session->get('coupon_code'));
			$data['coupon_id']   = $coupon->id;
			$data['coupon_code'] = $coupon->coupon_code;
		}
		else
		{
			$data['coupon_id']   = 0;
			$data['coupon_code'] = '';
		}

		if ($session->get('voucher_code'))
		{
			$voucher              = EShopHelper::getVoucher($session->get('voucher_code'));
			$data['voucher_id']   = $voucher->id;
			$data['voucher_code'] = $voucher->voucher_code;
		}
		else
		{
			$data['voucher_id']   = 0;
			$data['voucher_code'] = '';
		}

		$data['currency_exchanged_value'] = $currency->getExchangedValue();
		$data['total']                    = $total;
		$data['order_number']             = strtoupper(UserHelper::genRandomPassword(10));
		$data['invoice_number']           = EShopHelper::getInvoiceNumber();

		$model->processOrder($data);
	}

	/**
	 * Function to verify payment
	 */
	public function verifyPayment()
	{
		/* @var EShopModelCheckout $model */
		$model = $this->getModel('Checkout');
		$model->verifyPayment();
	}

	/**
	 * Function to cancel order
	 */
	public function cancelOrder()
	{
		/* @var EShopModelCheckout $model */
		$model = $this->getModel('Checkout');
		$model->cancelOrder();
	}

	/**
	 * Get filtered post data
	 *
	 * @return array
	 */
	protected function getFilteredPostData()
	{
		$filterInput = InputFilter::getInstance();
		$post        = $this->input->post->getArray();
		$post        = $filterInput->clean($post, null);

		foreach ($post as $field => $value)
		{
			if (is_array($post[$field]))
			{
				$post[$field] = json_encode($value);
			}
		}

		return $post;
	}
}