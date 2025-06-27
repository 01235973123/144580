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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewCheckout extends EShopView
{
	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	/**
	 *
	 * @var $weight
	 */
	protected $weight;

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
	 * @var $showCaptcha
	 */
	protected $showCaptcha;

	/**
	 *
	 * @var $captcha
	 */
	protected $captcha;

	/**
	 *
	 * @var $captchaPlugin
	 */
	protected $captchaPlugin;

	/**
	 *
	 * @var $paymentFields
	 */
	protected $paymentFields;

	/**
	 *
	 * @var $shippingFields
	 */
	protected $shippingFields;

	/**
	 *
	 * @var $orderProducts
	 */
	protected $orderProducts;

	/**
	 *
	 * @var $orderTotals
	 */
	protected $orderTotals;

	/**
	 *
	 * @var $tax
	 */
	protected $tax;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	/**
	 *
	 * @var $orderInfor
	 */
	protected $orderInfor;

	/**
	 *
	 * @var $conversionTrackingCode
	 */
	protected $conversionTrackingCode;

	public function display($tpl = null)
	{
		HTMLHelper::_('calendar', '', 'id', 'name');

		$app                   = Factory::getApplication();
		$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

		if (EShopHelper::isCatalogMode())
		{
			$app->getSession()->set('warning', Text::_('ESHOP_CATALOG_MODE_ON'));
			$app->redirect(Route::_(EShopRoute::getViewRoute('categories')));
		}
		else
		{
			$menu     = $app->getMenu();
			$menuItem = $menu->getActive();

			if ($menuItem && (isset($menuItem->query['view']) && ($menuItem->query['view'] == 'frontpage')))
			{
				$pathway = $app->getPathway();
				$pathUrl = EShopRoute::getViewRoute('frontpage');
				$pathway->addItem(Text::_('ESHOP_CHECKOUT'), $pathUrl);
			}

			if ($this->getLayout() == 'complete')
			{
				$this->displayComplete($tpl);
			}
			elseif ($this->getLayout() == 'cancel')
			{
				$this->displayCancel($tpl);
			}
			else
			{
				$cart = new EShopCart();

				// Check if cart has products or not
				if (!$cart->hasProducts() || !$cart->canCheckout() || $cart->getMinSubTotalWarning() != '' || $cart->getMinQuantityWarning(
					) != '' || $cart->getMinProductQuantityWarning() != '' || $cart->getMaxProductQuantityWarning() != '')
				{
					$app->redirect(Route::_(EShopRoute::getViewRoute('cart')));
				}

				$app->getDocument()->addStyleSheet(Uri::root(true) . '/media/com_eshop/assets/colorbox/colorbox.css');

				$this->setPageTitle(Text::_('ESHOP_CHECKOUT'));

				if (EShopHelper::getConfigValue('checkout_weight', 0) && $cart->hasShipping())
				{
					$eshopWeight  = EShopWeight::getInstance();
					$this->weight = $eshopWeight->format($cart->getWeight(), EShopHelper::getConfigValue('weight_id'));
				}
				else
				{
					$this->weight = 0;
				}

				$user                    = Factory::getUser();
				$this->user              = $user;
				$this->shipping_required = $cart->hasShipping();

				//Captcha
				$this->showCaptcha = false;

				if (EShopHelper::getConfigValue('enable_checkout_captcha') || EShopHelper::getConfigValue('enable_register_account_captcha'))
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
						$this->showCaptcha = true;
						$this->captcha     = Captcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required');
					}
					else
					{
						$app->enqueueMessage(Text::_('ESHOP_CAPTCHA_IS_NOT_ACTIVATED'), 'error');
					}

					$this->captchaPlugin = $captchaPlugin;
				}

				parent::display($tpl);
			}
		}
	}

	/**
	 *
	 * Function to display complete layout
	 *
	 * @param   string  $tpl
	 */
	protected function displayComplete($tpl)
	{
		$cart        = new EShopCart();
		$app         = Factory::getApplication();
		$session     = $app->getSession();
		$input       = $app->input;
		$orderNumber = $input->getString('order_number');

		if ($orderNumber == '')
		{
			$orderNumber = $session->get('order_number');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_orders')
			->where('order_number = ' . $db->quote($orderNumber));
		$db->setQuery($query);
		$orderInfor = $db->loadObject();

		if (is_object($orderInfor))
		{
			$orderId = $orderInfor->id;

			if ($orderInfor->payment_method == 'os_ideal' && $orderInfor->order_status_id != EShopHelper::getConfigValue('complete_status_id'))
			{
				$app->redirect('index.php?option=com_eshop&view=checkout&layout=cancel&id=' . $orderId);
			}

			$tax           = new EShopTax(EShopHelper::getConfig());
			$currency      = EShopCurrency::getInstance();
			$orderProducts = EShopHelper::getOrderProducts($orderId);

			for ($i = 0; $n = count($orderProducts), $i < $n; $i++)
			{
				$orderProducts[$i]->options = $orderProducts[$i]->orderOptions;
			}

			$orderTotals = EShopHelper::getOrderTotals($orderId);

			//Payment custom fields here
			$form                = new EshopRADForm(EShopHelper::getFormFields('B'));
			$this->paymentFields = $form->getFields();

			//Shipping custom fields here
			$form                 = new EshopRADForm(EShopHelper::getFormFields('S'));
			$this->shippingFields = $form->getFields();
			$this->orderProducts  = $orderProducts;
			$this->orderTotals    = $orderTotals;
			$this->tax            = $tax;
			$this->currency       = $currency;

			// Clear cart and session
			$cart->clear();
			$session->clear('shipping_method');
			$session->clear('shipping_methods');
			$session->clear('payment_method');
			$session->clear('guest');
			$session->clear('customer');
			$session->clear('comment');
			$session->clear('order_id');
			$session->clear('coupon_code');
			$session->clear('voucher_code');
		}

		$this->orderInfor = $orderInfor;

		if (is_object($orderInfor))
		{
			$this->conversionTrackingCode = EShopHelper::getConversionTrackingCode($orderInfor);

			if (EShopHelper::getConfigValue('ga_tracking_id') != '')
			{
				$gaJsType = EShopHelper::getConfigValue('ga_js_type', 'ga.js');

				if ($gaJsType == 'ga.js')
				{
					EShopGoogleAnalytics::processClassicAnalytics($orderInfor);
				}
				else
				{
					if ($gaJsType == 'analytics.js')
					{
						EShopGoogleAnalytics::processUniversalAnalytics($orderInfor);
					}
					else
					{
						EShopGoogleAnalytics::processGA4Analytics($orderInfor);
					}
				}
			}
		}
		else
		{
			$this->conversionTrackingCode = '';
		}

		$checkoutCompleteUrl = trim(EShopHelper::getConfigValue('completed_url', ''));

		if (strlen($checkoutCompleteUrl) > 0)
		{
			$app->redirect($checkoutCompleteUrl);
		}
		else
		{
			parent::display($tpl);
		}
	}


	/**
	 *
	 * Function to display cancel layout
	 *
	 * @param   string  $tpl
	 */
	protected function displayCancel($tpl)
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$id    = $input->getInt('id');

		if ($id)
		{
			$row = Table::getInstance('Eshop', 'Order');
			$row->load($id);

			if ($row->order_status_id == EShopHelper::getConfigValue('complete_status_id'))
			{
				$app->redirect(Route::_(EShopRoute::getViewRoute('checkout') . '&layout=complete'));
			}
		}

		parent::display($tpl);
	}
}