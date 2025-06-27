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
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 *
 * @package    Joomla
 * @subpackage EShop
 * @since      1.5
 */
class EShopViewCart extends EShopView
{

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	/**
	 *
	 * @var $items
	 */
	protected $items;

	/**
	 *
	 * @var $countProducts
	 */
	protected $countProducts;

	/**
	 *
	 * @var $totalData
	 */
	protected $totalData;

	/**
	 *
	 * @var $totalPrice
	 */
	protected $totalPrice;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	/**
	 *
	 * @var $tax
	 */
	protected $tax;

	/**
	 *
	 * @var $cartData
	 */
	protected $cartData;

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
	 * @var $weight
	 */
	protected $weight;

	/**
	 *
	 * @var $success
	 */
	protected $success;

	/**
	 *
	 * @var $warning
	 */
	protected $warning;

	public function display($tpl = null)
	{
		$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

		switch ($this->getLayout())
		{
			case 'mini':
				$this->displayMini($tpl);
				break;
			case 'popout':
				$this->displayPopout($tpl);
				break;
			default:
				break;
		}
	}

	/**
	 *
	 * @param   string  $tpl
	 */
	protected function displayMini($tpl = null)
	{
		//Get cart data
		$cart          = new EShopCart();
		$items         = $cart->getCartData();
		$countProducts = $cart->countProducts();
		$currency      = EShopCurrency::getInstance();
		$tax           = new EShopTax(EShopHelper::getConfig());

		$model = $this->getModel();
		$model->getCosts();
		$totalData  = $model->getTotalData();
		$totalPrice = $currency->format($model->getTotal());

		$this->items         = $items;
		$this->countProducts = $countProducts;
		$this->totalData     = $totalData;
		$this->totalPrice    = $totalPrice;
		$this->currency      = $currency;
		$this->tax           = $tax;

		parent::display($tpl);
	}

	/**
	 *
	 * @param   string  $tpl
	 */
	protected function displayPopout($tpl = null)
	{
		$app = Factory::getApplication();
		$app->getDocument()->addStyleSheet(Uri::root(true) . '/media/com_eshop/assets/colorbox/colorbox.css');
		$session  = $app->getSession();
		$tax      = new EShopTax(EShopHelper::getConfig());
		$cart     = new EShopCart();
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

		if (EShopHelper::getConfigValue('cart_weight') && $cart->hasShipping())
		{
			$eshopWeight  = EShopWeight::getInstance();
			$this->weight = $eshopWeight->format($cart->getWeight(), EShopHelper::getConfigValue('weight_id'));
		}
		else
		{
			$this->weight = 0;
		}

		// Success message
		if ($session->get('success'))
		{
			$this->success = $session->get('success');
			$session->clear('success');
		}

		if ($cart->getStockWarning() != '')
		{
			$this->warning = $cart->getStockWarning();
		}
		elseif ($cart->getMinSubTotalWarning() != '')
		{
			$this->warning = $cart->getMinSubTotalWarning();
		}
		elseif ($cart->getMinQuantityWarning() != '')
		{
			$this->warning = $cart->getMinQuantityWarning();
		}
		elseif ($cart->getMinProductQuantityWarning() != '')
		{
			$this->warning = $cart->getMinProductQuantityWarning();
		}
		elseif ($cart->getMaxProductQuantityWarning() != '')
		{
			$this->warning = $cart->getMaxProductQuantityWarning();
		}

		if ($session->get('warning'))
		{
			$this->warning = $session->get('warning');
			$session->clear('warning');
		}

		parent::display($tpl);
	}
}