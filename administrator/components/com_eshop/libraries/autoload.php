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
use Joomla\CMS\Language\Multilanguage;

require_once __DIR__ . '/vendor/box/spout/src/Spout/Autoloader/autoload.php';

$classes = [
	'EShopViewList'        => __DIR__ . '/mvc/viewlist.php',
	'EShopViewForm'        => __DIR__ . '/mvc/viewform.php',
	'EShopModelList'       => __DIR__ . '/mvc/modellist.php',
	'EShopModel'           => __DIR__ . '/mvc/model.php',
	'EShopAdminController' => __DIR__ . '/mvc/admincontroller.php',
	'EShopTable'           => __DIR__ . '/mvc/table.php',
	'EShopView'            => __DIR__ . '/mvc/view.php',
	'EShopCart'            => JPATH_ROOT . '/components/com_eshop/helpers/cart.php',
	'EShopQuote'           => JPATH_ROOT . '/components/com_eshop/helpers/quote.php',
	'EShopCoupon'          => JPATH_ROOT . '/components/com_eshop/helpers/coupon.php',
	'EShopVoucher'         => JPATH_ROOT . '/components/com_eshop/helpers/voucher.php',
	'EShopCurrency'        => JPATH_ROOT . '/components/com_eshop/helpers/currency.php',
	'EShopCustomer'        => JPATH_ROOT . '/components/com_eshop/helpers/customer.php',
	'EShopHelper'          => JPATH_ROOT . '/components/com_eshop/helpers/helper.php',
	'EShopAPI'             => JPATH_ROOT . '/components/com_eshop/helpers/api.php',
	'EShopGoogleAnalytics' => JPATH_ROOT . '/components/com_eshop/helpers/googleanalytics.php',
	'EShopHtmlHelper'      => JPATH_ROOT . '/components/com_eshop/helpers/html.php',
	'EShopImage'           => JPATH_ROOT . '/components/com_eshop/helpers/image.php',
	'EShopLength'          => JPATH_ROOT . '/components/com_eshop/helpers/length.php',
	'EShopOption'          => JPATH_ROOT . '/components/com_eshop/helpers/option.php',
	'EShopShipping'        => JPATH_ROOT . '/components/com_eshop/helpers/shipping.php',
	'EShopPayment'         => JPATH_ROOT . '/components/com_eshop/helpers/payment.php',
	'EShopDiscount'        => JPATH_ROOT . '/components/com_eshop/helpers/discount.php',
	'EShopDonate'          => JPATH_ROOT . '/components/com_eshop/helpers/donate.php',
	'EShopEuvat'           => JPATH_ROOT . '/components/com_eshop/helpers/euvat.php',
	'EShopFile'            => JPATH_ROOT . '/components/com_eshop/helpers/file.php',
	'EShopTax'             => JPATH_ROOT . '/components/com_eshop/helpers/tax.php',
	'EShopWeight'          => JPATH_ROOT . '/components/com_eshop/helpers/weight.php',
	'EShopHelperBootstrap' => JPATH_ROOT . '/components/com_eshop/helpers/bootstrap.php',
	'EShopOmnipayPayment'  => JPATH_ROOT . '/components/com_eshop/plugins/payment/omnipay.php',
	'eshop_shipping'       => JPATH_ROOT . '/components/com_eshop/plugins/shipping/eshop_shipping.php',
];

foreach ($classes as $className => $path)
{
	JLoader::register($className, $path);
}

$input = Factory::getApplication()->input;

JLoader::register('os_payments', JPATH_ROOT . '/components/com_eshop/plugins/payment/os_payments.php');
JLoader::register('os_payment', JPATH_ROOT . '/components/com_eshop/plugins/payment/os_payment.php');

JLoader::register(
	'EShopRoute',
	JPATH_ROOT . '/components/com_eshop/helpers/' . ((Multilanguage::isEnabled() && count(
			EShopHelper::getLanguages()
		) > 1) ? 'routev3.php' : 'route.php')
);

// Disable ONLY_FULL_GROUP_BY mode
if (version_compare(JVERSION, '4.0.0', 'ge'))
{
	$db = Factory::getDbo();
	$db->setQuery("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
	$db->execute();
}