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

class EShopGoogleAnalytics
{
	/**
	 *
	 * Function to process Classic Analytics
	 *
	 * @param   order object $row
	 */
	public static function processClassicAnalytics($row)
	{
		//Initialise
		$doc = Factory::getApplication()->getDocument();

		if ($row->id)
		{
			//Get transaction Id
			$transactionId = $row->id;

			//Get sub total
			$subTotal = self::getOrderTotalValue($row->id, 'sub_total');

			//Get tax
			$tax = self::getOrderTotalValue($row->id, 'tax');

			//Get shipping
			$shipping = self::getOrderTotalValue($row->id, 'shipping');

			$script = "";
			$script .= "
				(function() {
				  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
				})();
    			";

			//Add Transaction
			$script .= "
    				var _gaq = _gaq || [];
					  _gaq.push(['_setAccount', '" . EShopHelper::getConfigValue('ga_tracking_id') . "']);
					  _gaq.push(['_trackPageview']);
					  _gaq.push(['_addTrans',
					    '" . $transactionId . "',
					    '" . addslashes(EShopHelper::getConfigValue('store_name')) . "',
					    '" . number_format($subTotal, 2, '.', '') . "',
					    '" . number_format($tax, 2, '.', '') . "',
					    '" . number_format($shipping, 2, '.', '') . "',
					    '" . $row->payment_city . "',
					    '" . $row->payment_zone_name . "',
					    '" . $row->payment_country_name . "'
					  ]);
    				";


			//Now Add Items
			$orderProducts = self::getOrderProducts($row->id);

			foreach ($orderProducts as $product)
			{
				$productVariation = self::getProductVariation($row, $product);

				$script .= "
				_gaq.push(['_addItem',
				    '" . $transactionId . "',
				    '" . $product->product_sku . "',
				    '" . addslashes($product->product_name) . "',";

				if ($productVariation != '')
				{
					$script .= "'" . addslashes($productVariation) . "',";
				}

				$script .= "
				    '" . number_format($product->price, 2, '.', '') . "',
				    '" . $product->quantity . "'
				  ]);
    			";
			}

			//Add currency
			$script .= "
    				_gaq.push(['_set', 'currencyCode', '" . $row->currency_code . "']);
    				";

			//Submit transaction
			$script .= "_gaq.push(['_trackTrans']);";

			$doc->addScriptDeclaration($script);
		}
	}

	/**
	 *
	 * Function to process Universal Analytics
	 *
	 * @param   order object $row
	 */
	public static function processUniversalAnalytics($row)
	{
		//Initialise
		$doc = Factory::getApplication()->getDocument();

		if ($row->id)
		{
			//Get transaction Id
			$transactionId = $row->id;

			//Get sub total
			$subTotal = self::getOrderTotalValue($row->id, 'sub_total');

			//Get tax
			$tax = self::getOrderTotalValue($row->id, 'tax');

			//Get shipping
			$shipping = self::getOrderTotalValue($row->id, 'shipping');

			$script = "";
			$script .= "
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
					(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
					m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    			";

			//Add Transaction
			$script .= "
    				ga('create', '" . EShopHelper::getConfigValue('ga_tracking_id') . "', 'auto');
					ga('send', 'pageview');
					ga('require', 'ecommerce');
    				ga('ecommerce:addTransaction', {
					  'id': '" . $transactionId . "',
					  'affiliation': '" . addslashes(EShopHelper::getConfigValue('store_name')) . "',
					  'revenue': '" . number_format($subTotal, 2) . "',
					  'shipping': '" . number_format($shipping, 2) . "',
					  'tax': '" . number_format($tax, 2) . "',
					  'currency': '" . $row->currency_code . "'
					});
    				";

			//Now Add Items

			$orderProducts = self::getOrderProducts($row->id);

			foreach ($orderProducts as $product)
			{
				$productSku       = $product->product_sku;
				$productVariation = self::getProductVariation($row, $product);

				$script .= "
    			ga('ecommerce:addItem', {
				  'id': '" . $transactionId . "',
				  'name': '" . addslashes($product->product_name) . "',
				  'sku': '" . $product->product_sku . "',";

				if ($productVariation != '')
				{
					$script .= "'category': '" . addslashes($productVariation) . "',";
				}
				$script .= "
				  'price': '" . number_format($product->price, 2) . "',
				  'quantity': '" . $product->quantity . "'
				});
    			";
			}

			//Submit transaction
			$script .= "ga('ecommerce:send');";

			$doc->addScriptDeclaration($script);
		}
	}

	/**
	 *
	 * Function to process Google Analytics 4
	 *
	 * @param   order object $row
	 */
	public static function processGA4Analytics($row)
	{
		//Initialise
		$doc = Factory::getApplication()->getDocument();

		if ($row->id)
		{
			//Get transaction Id
			$transactionId = $row->id;

			//Get sub total
			$subTotal = self::getOrderTotalValue($row->id, 'sub_total');

			//Get tax
			$tax = self::getOrderTotalValue($row->id, 'tax');

			//Get shipping
			$shipping = self::getOrderTotalValue($row->id, 'shipping');

			$doc->addScript('https://www.googletagmanager.com/gtag/js?id=' . EShopHelper::getConfigValue('ga_tracking_id'), [], ['async' => 'async']);

			$script = "";
			$script .= "<!-- Google tag (gtag.js) -->
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '" . EShopHelper::getConfigValue('ga_tracking_id') . "');";

			//Add Transaction
			$script .= "gtag('event', 'purchase', {
                transaction_id: '" . $transactionId . "',
                affiliation: '" . addslashes(EShopHelper::getConfigValue('store_name')) . "',
                revenue: '" . number_format($subTotal, 2, ".", "") . "',
                value: '" . number_format($subTotal, 2, ".", "") . "',
                tax: '" . number_format($tax, 2, ".", "") . "',
                shipping: '" . number_format($shipping, 2, ".", "") . "',
                currency: '" . $row->currency_code . "',
                 items: [";

			//Now Add Items
			$orderProducts = self::getOrderProducts($row->id);

			foreach ($orderProducts as $product)
			{
				$productSku       = $product->product_sku;
				$productVariation = self::getProductVariation($row, $product);

				$script .= "{
                    item_id: '" . $productSku . "',
                    item_name: '" . addslashes($product->product_name) . "',
                    affiliation: '" . addslashes(EShopHelper::getConfigValue('store_name')) . "',
                    currency: '" . $row->currency_code . "',";

				if ($productVariation != '')
				{
					$script .= "'item_variant': '" . addslashes($productVariation) . "',";
				}

				$script .= "
                    price: '" . number_format($product->price, 2, ".", "") . "',
                    quantity: '" . $product->quantity . "'
                    },";
			}

			$script = substr($script, 0, -1);
			$script .= "]});";

			$doc->addScriptDeclaration($script);
		}
	}

	/**
	 *
	 * Private function to get order products
	 *
	 * @param   int  $orderId
	 *
	 * @return order products objects list
	 */
	public static function getOrderProducts($orderId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from('#__eshop_orderproducts')
			->where('order_id = ' . intval($orderId));
		$db->setQuery($query);
		$orderProducts = $db->loadObjectList();

		return $orderProducts;
	}

	/**
	 *
	 * Function to get a total value of a specific order
	 *
	 * @param   string  $totalName  - example: total, sub_total, shipping, tax
	 *
	 * @return float
	 */
	public static function getOrderTotalValue($orderId, $totalName)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('value')
			->from('#__eshop_ordertotals')
			->where('order_id = ' . intval($orderId))
			->where('name = ' . $db->quote($totalName));
		$db->setQuery($query);
		$totalValue = $db->loadResult();

		if (!$totalValue)
		{
			$totalValue = 0;
		}

		return $totalValue;
	}

	/**
	 *
	 * Function to get product variation
	 *
	 * @param   order object $row
	 * @param   order product object $product
	 *
	 * @return string product variation
	 */
	public static function getProductVariation($row, $product)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$variationType    = EShopHelper::getConfigValue('variation_type', 'none');
		$productVariation = '';

		if ($variationType == 'variation')
		{
			$query->select('option_name, option_value')
				->from('#__eshop_orderoptions')
				->where('order_product_id = ' . intval($product->id));
			$db->setQuery($query);
			$productOrderOptions = $db->loadObjectList();

			if (count($productOrderOptions))
			{
				foreach ($productOrderOptions as $productOrderOption)
				{
					$productVariation .= ' | ' . $productOrderOption->option_name . ':' . $productOrderOption->option_value;
				}
			}
		}
		elseif ($variationType == 'category')
		{
			$categoryId = EShopHelper::getProductCategory($product->product_id);

			if ($categoryId > 0)
			{
				$query->select('category_name')
					->from('#__eshop_categorydetails')
					->where('category_id = ' . intval($categoryId))
					->where('language = ' . $db->quote($row->language));
				$db->setQuery($query);
				$productVariation = $db->loadResult();
			}
		}

		return $productVariation;
	}
}