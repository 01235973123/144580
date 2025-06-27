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

use Joomla\CMS\Language\Text;

class EShopModelCart extends EShopModel
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

	public function __construct($config = [])
	{
		parent::__construct();

		$this->cartData  = null;
		$this->totalData = null;
		$this->total     = null;
		$this->taxes     = null;
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
		$this->getCouponCosts($totalData, $total, $taxes);
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
}