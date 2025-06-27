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

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

class EShopHelper
{
	/**
	 * Store product alias
	 *
	 * @var array
	 */
	public static $productsAlias = [];

	/**
	 * Store manufacturers alias
	 *
	 * @var array
	 */
	public static $manufacturersAlias = [];

	/**
	 *
	 * Function to get configuration object
	 */
	public static function getConfig()
	{
		static $config;

		if (is_null($config))
		{
			$config = new stdClass();
			$db     = Factory::getDbo();
			$query  = $db->getQuery(true);
			$query->select('config_key, config_value')
				->from('#__eshop_configs');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			foreach ($rows as $row)
			{
				$config->{$row->config_key} = $row->config_value;
			}
		}

		return $config;
	}

	/**
	 * Helper method to determine if we are in Joomla 4
	 *
	 * @return bool
	 */
	public static function isJoomla4()
	{
		return version_compare(JVERSION, '4.0.0', '>=');
	}

	/**
	 * Helper method to determine if we are in Joomla 5
	 *
	 * @return bool
	 */
	public static function isJoomla5()
	{
		return version_compare(JVERSION, '4.4.99', '>');
	}

	/**
	 *
	 * Function to get weight ids
	 * @return array
	 */
	public static function getWeightIds()
	{
		static $weightIds;

		if (is_null($weightIds))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__eshop_weights');
			$db->setQuery($query);
			$weightIds = $db->loadColumn();
		}

		return $weightIds;
	}

	/**
	 *
	 * Function to get length ids
	 * @return array
	 */
	public static function getLengthIds()
	{
		static $lengthIds;

		if (is_null($lengthIds))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__eshop_lengths');
			$db->setQuery($query);
			$lengthIds = $db->loadColumn();
		}

		return $lengthIds;
	}

	/**
	 * Function to check if joomla is version 3 or not
	 *
	 * @param   number  $minor
	 *
	 * @return boolean
	 */
	public static function isJ3($minor = 0)
	{
		static $status;

		if (!isset($status))
		{
			if (version_compare(JVERSION, '3.' . $minor . '.0', 'ge'))
			{
				$status = true;
			}
			else
			{
				$status = false;
			}
		}

		return $status;
	}

	/**
	 *
	 * Function to check if is mobile or not
	 * @return boolean
	 */
	public static function isMobile()
	{
		return preg_match(
			"/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
			$_SERVER["HTTP_USER_AGENT"]
		);
	}

	/**
	 *
	 * Function to get value of configuration variable
	 *
	 * @param   string  $configKey
	 * @param   string  $default
	 *
	 * @return string
	 */
	public static function getConfigValue($configKey, $default = null)
	{
		$config = self::getConfig();

		if (isset($config->{$configKey}))
		{
			return $config->{$configKey};
		}

		return $default;
	}

	/**
	 * Get the invoice number for an order
	 */
	public static function getInvoiceNumber()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('MAX(invoice_number)')
			->from('#__eshop_orders');

		if (self::getConfigValue('reset_invoice_number'))
		{
			$query->where('YEAR(created_date) = YEAR(CURDATE())');
		}

		$db->setQuery($query);
		$invoiceNumber = intval($db->loadResult());

		if (!$invoiceNumber)
		{
			$invoiceNumber = intval(self::getConfigValue('invoice_start_number'));

			if (!$invoiceNumber)
			{
				$invoiceNumber = 1;
			}
		}
		else
		{
			$invoiceNumber++;
		}

		return $invoiceNumber;
	}

	/**
	 * Format invoice number
	 *
	 * @param   string  $invoiceNumber
	 * @param   string  $createdDate
	 *
	 * @return string
	 */
	public static function formatInvoiceNumber($invoiceNumber, $createdDate)
	{
		return str_replace('[YEAR]', HTMLHelper::date($createdDate, 'Y'), self::getConfigValue('invoice_prefix')) . str_pad(
				$invoiceNumber,
				self::getConfigValue('invoice_number_length') ?: 5,
				'0',
				STR_PAD_LEFT
			);
	}

	/**
	 * Get request data, used for EshopRADList model
	 *
	 */
	public static function getRequestData()
	{
		$input   = Factory::getApplication()->input;
		$request = $input->getArray();

		//Remove cookie vars from request
		$cookieVars = array_keys($_COOKIE);

		if (count($cookieVars))
		{
			foreach ($cookieVars as $key)
			{
				if (!isset($_POST[$key]) && !isset($_GET[$key]))
				{
					unset($request[$key]);
				}
			}
		}

		if (isset($request['start']) && !isset($request['limitstart']))
		{
			//$request['limitstart'] = $request['start'];
		}

		if (!isset($request['limitstart']))
		{
			$request['limitstart'] = 0;
		}

		return $request;
	}

	public static function getCategory($categoryId, $processImage = true, $checkPermission = false)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
			'a.*, b.category_name, b.category_alias, b.category_desc, b.category_page_title, b.category_page_heading, b.category_alt_image, b.category_canoncial_link, b.meta_key, b.meta_desc'
		)
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_categorydetails AS b ON a.id = b.category_id')
			->where('a.id = ' . intval($categoryId))
			->where('a.published = 1')
			->where('b.language = "' . Factory::getLanguage()->getTag() . '"');

		if ($checkPermission)
		{
			//Check viewable of customer groups
			$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

			$query->where(
				'((a.category_customergroups = "") OR (a.category_customergroups IS NULL) OR (a.category_customergroups = "' . $customerGroupId . '") OR (a.category_customergroups LIKE "' . $customerGroupId . ',%") OR (a.category_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.category_customergroups LIKE "%,' . $customerGroupId . '"))'
			);
		}

		$db->setQuery($query);
		$category = $db->loadObject();

		if (is_object($category) && $processImage)
		{
			$imageSizeFunction = self::getConfigValue('category_image_size_function', 'resizeImage');

			if ($category->category_image && is_file(JPATH_ROOT . '/media/com_eshop/categories/' . $category->category_image))
			{
				if (self::getConfigValue('category_use_image_watermarks'))
				{
					$watermarkImage = self::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/categories/' . $category->category_image);
					$categoryImage  = $watermarkImage;
				}
				else
				{
					$categoryImage = $category->category_image;
				}

				$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
					[
						$categoryImage,
						JPATH_ROOT . '/media/com_eshop/categories/',
						self::getConfigValue('image_category_width'),
						self::getConfigValue('image_category_height'),
					]);
			}
			else
			{
				$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
					[
						EShopHelper::getConfigValue('default_category_image', 'no-image.png'),
						JPATH_ROOT . '/media/com_eshop/categories/',
						self::getConfigValue('image_category_width'),
						self::getConfigValue('image_category_height'),
					]);
			}

			if ($imageSizeFunction == 'notResizeImage')
			{
				$category->image = Uri::base(true) . '/media/com_eshop/categories/' . $image;
			}
			else
			{
				$category->image = Uri::base(true) . '/media/com_eshop/categories/resized/' . $image;
			}
		}

		return $category;
	}

	public static function getManufacturer($id, $processImage = true, $checkPermission = false)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
			'a.*, b.manufacturer_name, b.manufacturer_alias, b.manufacturer_desc, b.manufacturer_page_title, b.manufacturer_page_heading, b.manufacturer_alt_image'
		)
			->from('#__eshop_manufacturers AS a')
			->innerJoin('#__eshop_manufacturerdetails AS b ON (a.id = b.manufacturer_id)')
			->where('a.id = ' . (int) $id)
			->where('b.language = ' . $db->quote(Factory::getLanguage()->getTag()));

		if ($checkPermission)
		{
			//Check viewable of customer groups
			$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

			$query->where(
				'((a.manufacturer_customergroups = "") OR (a.manufacturer_customergroups IS NULL) OR (a.manufacturer_customergroups = "' . $customerGroupId . '") OR (a.manufacturer_customergroups LIKE "' . $customerGroupId . ',%") OR (a.manufacturer_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.manufacturer_customergroups LIKE "%,' . $customerGroupId . '"))'
			);
		}

		$db->setQuery($query);
		$manufacturer = $db->loadObject();

		if ($manufacturer && $processImage)
		{
			$imageSizeFunction = self::getConfigValue('manufacturer_image_size_function', 'resizeImage');

			if ($manufacturer->manufacturer_image && is_file(JPATH_ROOT . '/media/com_eshop/manufacturers/' . $manufacturer->manufacturer_image))
			{
				if (self::getConfigValue('manufacturer_use_image_watermarks'))
				{
					$watermarkImage    = self::generateWatermarkImage(
						JPATH_ROOT . '/media/com_eshop/manufacturers/' . $manufacturer->manufacturer_image
					);
					$manufacturerImage = $watermarkImage;
				}
				else
				{
					$manufacturerImage = $manufacturer->manufacturer_image;
				}

				$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
					[
						$manufacturerImage,
						JPATH_ROOT . '/media/com_eshop/manufacturers/',
						self::getConfigValue('image_manufacturer_width'),
						self::getConfigValue('image_manufacturer_height'),
					]);
			}
			else
			{
				$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
					[
						EShopHelper::getConfigValue('default_manufacturer_image', 'no-image.png'),
						JPATH_ROOT . '/media/com_eshop/manufacturers/',
						self::getConfigValue('image_manufacturer_width'),
						self::getConfigValue('image_manufacturer_height'),
					]);
			}

			if ($imageSizeFunction == 'notResizeImage')
			{
				$manufacturer->image = Uri::base(true) . '/media/com_eshop/manufacturers/' . $image;
			}
			else
			{
				$manufacturer->image = Uri::base(true) . '/media/com_eshop/manufacturers/resized/' . $image;
			}
		}

		return $manufacturer;
	}

	/**
	 * Get the associations.
	 *
	 */
	public static function getAssociations($id, $view = 'product')
	{
		$langCode     = Factory::getLanguage()->getTag();
		$associations = [];
		$db           = Factory::getDbo();
		$query        = $db->getQuery(true);
		$query->select($view . '_id, language')
			->from('#__eshop_' . $view . 'details')
			->where($view . '_id = ' . intval($id))
			->where('language != "' . $langCode . '"');
		$db->setQuery($query);

		try
		{
			$items = $db->loadObjectList('language');
		}
		catch (RuntimeException $e)
		{
			throw new Exception($e->getMessage(), 500);
		}

		if ($items)
		{
			foreach ($items as $tag => $item)
			{
				$associations[$tag] = $item;
			}
		}

		return $associations;
	}

	/**
	 *
	 * Function to update currencies
	 *
	 * @param   boolean  $force
	 * @param   int      $timePeriod
	 * @param   string   $timeUnit
	 */
	public static function updateCurrencies($force = false, $timePeriod = 1, $timeUnit = 'day')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($force)
		{
			$query->select('*')
				->from('#__eshop_currencies')
				->where('currency_code != ' . $db->quote(self::getConfigValue('default_currency_code')));
		}
		else
		{
			$query->select('*')
				->from('#__eshop_currencies')
				->where('currency_code != ' . $db->quote(self::getConfigValue('default_currency_code')))
				->where('modified_date <= ' . $db->quote(date('Y-m-d H:i:s', strtotime('-' . (int) $timePeriod . ' ' . $timeUnit))));
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$exchangedRate = self::getExchangedRate(self::getConfigValue('default_currency_code', 'USD'), $row->currency_code);

				if ((float) $exchangedRate && $exchangedRate != 1)
				{
					$query->clear();
					$query->update('#__eshop_currencies')
						->set('exchanged_value = ' . (float) $exchangedRate)
						->set('modified_date = ' . $db->quote(date('Y-m-d H:i:s')))
						->where('id = ' . intval($row->id));
					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		$query->clear()
			->update('#__eshop_currencies')
			->set('exchanged_value = 1.00000')
			->set('modified_date = ' . $db->quote(date('Y-m-d H:i:s')))
			->where('currency_code = ' . $db->quote(self::getConfigValue('default_currency_code')));
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 *
	 * Function to get exchanged rate from a currency code to another currency code
	 *
	 * @param   string  $fromCurrencyCode
	 * @param   string  $toCurrencyCode
	 *
	 * @return float exchanged rate
	 */
	public static function getExchangedRate_old($fromCurrencyCode, $toCurrencyCode)
	{
		$url = sprintf('https://www.google.com/search?q=1+%s+to+%s', $fromCurrencyCode, $toCurrencyCode);

		$headers = [
			'Accept'     => 'text/html',
			'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:21.0) Gecko/20100101 Firefox/21.0',
		];

		$http     = HttpFactory::getHttp();
		$response = $http->get($url, $headers);

		if (302 == $response->code && isset($response->headers['Location']))
		{
			$response = $http->get($response->headers['Location'], $headers);
		}

		$body = $response->body;

		$exchangedRate = 1;

		try
		{
			$exchangedRate = static::buildExchangeRate($body);
		}
		catch (Exception $e)
		{
		}

		return $exchangedRate;
	}

	/**
	 *
	 * Function to get exchanged rate from a currency code to another currency code
	 *
	 * @param   string  $fromCurrencyCode
	 * @param   string  $toCurrencyCode
	 *
	 * @return float exchanged rate
	 */
	public static function getExchangedRate($fromCurrencyCode, $toCurrencyCode)
	{
		$http          = HttpFactory::getHttp();
		$url           = 'https://free.currencyconverterapi.com/api/v6/convert?q=' . $fromCurrencyCode . '_' . $toCurrencyCode . '&compact=ultra&apiKey=' . self::getConfigValue(
				'currency_convert_api_key',
				'd3d91dd1c0af62db625b'
			);
		$response      = $http->get($url);
		$exchangedRate = 1;

		if ($response->code == 200)
		{
			$data          = $response->body;
			$returnArr     = json_decode($data);
			$exchangedRate = $returnArr->{$fromCurrencyCode . '_' . $toCurrencyCode};
		}

		return $exchangedRate;
	}

	/**
	 * Builds an exchange rate from the response content.
	 *
	 * @param   string  $content
	 *
	 * @return float
	 *
	 * @throws Exception
	 */
	protected static function buildExchangeRate($content)
	{
		$document = new DOMDocument();

		if (false === @$document->loadHTML('<?xml encoding="utf-8" ?>' . $content))
		{
			throw new Exception('The page content is not loadable');
		}

		$xpath = new DOMXPath($document);
		$nodes = $xpath->query('//span[@id="knowledge-currency__tgt-amount"]');

		if (1 !== $nodes->length)
		{
			$nodes = $xpath->query('//div[@class="vk_ans vk_bk" or @class="dDoNo vk_bk"]');
		}

		if (1 !== $nodes->length)
		{
			throw new Exception('The currency is not supported or Google changed the response format');
		}

		$nodeContent = $nodes->item(0)->textContent;

		// Beware of "3 417.36111 Colombian pesos", with a non breaking space
		$bid = strtr($nodeContent, ["\xc2\xa0" => '']);

		if (false !== strpos($bid, ' '))
		{
			$bid = strstr($bid, ' ', true);
		}
		// Does it have thousands separator?
		if (strpos($bid, ',') && strpos($bid, '.'))
		{
			$bid = str_replace(',', '', $bid);
		}

		// If the return value like 0,06 then change it to 0.06
		if (strpos($bid, ',') && !strpos($bid, '.'))
		{
			$bid = str_replace(',', '.', $bid);
		}

		if (!is_numeric($bid))
		{
			throw new Exception('The currency is not supported or Google changed the response format');
		}

		return $bid;
	}

	/**
	 *
	 * Function to update hits for category/manufacturer/product
	 *
	 * @param   int     $id
	 * @param   string  $element
	 */
	public static function updateHits($id, $element)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__eshop_' . $element)
			->set('hits = hits + 1')
			->where('id = ' . intval($id));
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 *
	 * Function to get name of a specific stock status
	 *
	 * @param   int     $stockStatusId
	 * @param   string  $langCode
	 *
	 * @return string
	 */
	public static function getStockStatusName($stockStatusId, $langCode)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('stockstatus_name')
			->from('#__eshop_stockstatusdetails')
			->where('stockstatus_id = ' . intval($stockStatusId))
			->where('language = "' . $langCode . '"');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
	 * Function to get name of a specific order status
	 *
	 * @param   int     $orderStatusId
	 * @param   string  $langCode
	 *
	 * @return string
	 */
	public static function getOrderStatusName($orderStatusId, $langCode)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('orderstatus_name')
			->from('#__eshop_orderstatusdetails')
			->where('orderstatus_id = ' . intval($orderStatusId))
			->where('language = "' . $langCode . '"');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
	 * Function to get unit of a specific length
	 *
	 * @param   int     $lengthId
	 * @param   string  $langCode
	 *
	 * @return string
	 */
	public static function getLengthUnit($lengthId, $langCode)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('length_unit')
			->from('#__eshop_lengthdetails')
			->where('length_id = ' . intval($lengthId))
			->where('language = "' . $langCode . '"');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
	 * Function to get unit of a specific weight
	 *
	 * @param   int     $weightId
	 * @param   string  $langCode
	 *
	 * @return string
	 */
	public static function getWeightUnit($weightId, $langCode)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('weight_unit')
			->from('#__eshop_weightdetails')
			->where('weight_id = ' . intval($weightId))
			->where('language = "' . $langCode . '"');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
	 * Function to get payment title
	 *
	 * @param   string  $paymentName
	 *
	 * @return string
	 */
	public static function getPaymentTitle($paymentName)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title')
			->from('#__eshop_payments')
			->where('name = "' . $paymentName . '"');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
	 * Function to get shipping title
	 *
	 * @param   string  $shippingName
	 *
	 * @return string
	 */
	public static function getShippingTitle($shippingName)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title')
			->from('#__eshop_shippings')
			->where('name = "' . $shippingName . '"');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Function to get all available languages
	 *
	 * @return array languages object list
	 */
	public static function getLanguages()
	{
		return LanguageHelper::getLanguages('default');
	}

	/**
	 *
	 * Function to get flags for languages
	 */
	public static function getLanguageData()
	{
		$rows = self::getLanguages();

		$languageData = [];

		foreach ($rows as $row)
		{
			$languageData['flag'][$row->lang_code]  = $row->image . '.gif';
			$languageData['title'][$row->lang_code] = $row->title;
		}

		return $languageData;
	}

	/**
	 *
	 * Function to get active language
	 */
	public static function getActiveLanguage()
	{
		$languages = LanguageHelper::getLanguages('lang_code');

		$langCode = Factory::getLanguage()->getTag();

		return $languages[$langCode] ?? null;
	}

	/**
	 *
	 * Function to get attached lang link
	 * @return string
	 */
	public static function getAttachedLangLink()
	{
		$attachedLangLink = '';

		if (Multilanguage::isEnabled())
		{
			$activeLanguage   = self::getActiveLanguage();
			$attachedLangLink = '&lang=' . $activeLanguage->sef;
		}

		return $attachedLangLink;
	}

	/**
	 *
	 * Function to get attribute groups
	 * @return array attribute groups object list
	 */
	public static function getAttributeGroups($langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, b.attributegroup_name')
			->from('#__eshop_attributegroups AS a')
			->innerJoin('#__eshop_attributegroupdetails AS b ON (a.id = b.attributegroup_id)')
			->where('a.published = 1')
			->where('b.language = ' . $db->quote($langCode))
			->order('a.ordering');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to get attributes for a specific products
	 *
	 * @param   int  $productId
	 * @param   int  $attributeGroupId
	 *
	 * @return array attribute object list
	 */
	public static function getAttributes($productId, $attributeGroupId, $langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('ad.attribute_name, pad.value')
			->from('#__eshop_attributes AS a')
			->innerJoin('#__eshop_attributedetails AS ad ON (a.id = ad.attribute_id)')
			->innerJoin('#__eshop_productattributes AS pa ON (a.id = pa.attribute_id)')
			->innerJoin('#__eshop_productattributedetails AS pad ON (pa.id = pad.productattribute_id)')
			->where('a.attributegroup_id = ' . intval($attributeGroupId))
			->where('a.published = 1')
			->where('pa.published = 1')
			->where('pa.product_id = ' . intval($productId))
			->where('ad.language = "' . $langCode . '"')
			->where('pad.language = ' . $db->quote($langCode));

		$attributeDefaultSorting = EShopHelper::getConfigValue('attribute_default_sorting', 'name-asc');

		switch ($attributeDefaultSorting)
		{
			case 'name-desc':
				$query->order('ad.attribute_name DESC');
				break;
			case 'ordering-asc':
				$query->order('a.ordering ASC');
				break;
			case 'ordering-desc':
				$query->order('a.ordering DESC');
				break;
			case 'id-asc':
				$query->order('a.id ASC');
				break;
			case 'id-desc':
				$query->order('a.id DESC');
				break;
			case 'random':
				$query->order('RAND()');
				break;
			case 'name-asc':
			default:
				$query->order('ad.attribute_name ASC');
				break;
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to get attributes for a specific products
	 *
	 * @param   array  $productIds
	 * @param   array  $attributeGroupId
	 *
	 * @return array attribute object list
	 */
	public static function getProductsAttributes($productIds, $attributeGroupIds, $langCode = '')
	{
		if (count($productIds) == 0 || count($attributeGroupIds) == 0)
		{
			return [];
		}

		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('ad.attribute_name, pad.value')
			->select('pa.product_id, a.attributegroup_id')
			->from('#__eshop_attributes AS a')
			->innerJoin('#__eshop_attributedetails AS ad ON (a.id = ad.attribute_id)')
			->innerJoin('#__eshop_productattributes AS pa ON (a.id = pa.attribute_id)')
			->innerJoin('#__eshop_productattributedetails AS pad ON (pa.id = pad.productattribute_id)')
			->where('a.attributegroup_id IN (' . implode(',', $attributeGroupIds) . ')')
			->where('a.published = 1')
			->where('pa.published = 1')
			->where('pa.product_id In ( ' . implode(',', $productIds) . ')')
			->where('ad.language = "' . $langCode . '"')
			->where('pad.language = ' . $db->quote($langCode));

		$attributeDefaultSorting = EShopHelper::getConfigValue('attribute_default_sorting', 'name-asc');

		switch ($attributeDefaultSorting)
		{
			case 'name-desc':
				$query->order('ad.attribute_name DESC');
				break;
			case 'ordering-asc':
				$query->order('a.ordering ASC');
				break;
			case 'ordering-desc':
				$query->order('a.ordering DESC');
				break;
			case 'id-asc':
				$query->order('a.id ASC');
				break;
			case 'id-desc':
				$query->order('a.id DESC');
				break;
			case 'random':
				$query->order('RAND()');
				break;
			case 'name-asc':
			default:
				$query->order('ad.attribute_name ASC');
				break;
		}

		$db->setQuery($query);

		$productsAttributes = [];

		foreach ($db->loadObjectList() as $row)
		{
			$productsAttributes[$row->product_id][$row->attributegroup_id][] = $row;
		}

		return $productsAttributes;
	}

	/**
	 *
	 * Function to get attribute group for a specific attribute
	 *
	 * @param   int  $attributeId
	 *
	 * @return mixed
	 */
	public static function getAttributeAttributeGroup($attributeId, $langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.attributegroup_id, b.attributegroup_name')
			->from('#__eshop_attributes AS a')
			->innerJoin('#__eshop_attributegroupdetails AS b ON (a.attributegroup_id = b.attributegroup_id)')
			->where('a.id = ' . intval($attributeId))
			->where('b.language = ' . $db->quote($langCode));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 *
	 * Function to get Categories
	 *
	 * @param   int  $categoryId
	 *
	 * @return array categories object list
	 */
	public static function getCategories($categoryId = 0, $langCode = '', $checkPermission = false, $categoryIds = array())
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.category_parent_id, a.category_image, b.category_name, b.category_desc')
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
			->where('a.category_parent_id = ' . intval($categoryId))
			->where('a.published = 1')
			->where('b.language = ' . $db->quote($langCode));
		
		if (count($categoryIds))
		{
			$query->where('a.id IN (' . implode(',', $categoryIds) . ')');
		}

		$categoryDefaultSorting = EShopHelper::getConfigValue('category_default_sorting', 'name-asc');

		switch ($categoryDefaultSorting)
		{
			case 'name-desc':
				$query->order('b.category_name DESC');
				break;
			case 'ordering-asc':
				$query->order('a.ordering ASC');
				break;
			case 'ordering-desc':
				$query->order('a.ordering DESC');
				break;
			case 'id-asc':
				$query->order('a.id ASC');
				break;
			case 'id-desc':
				$query->order('a.id DESC');
				break;
			case 'random':
				$query->order('RAND()');
				break;
			case 'name-asc':
			default:
				$query->order('b.category_name ASC');
				break;
		}

		if ($checkPermission)
		{
			//Check viewable of customer groups
			$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

			$query->where(
				'((a.category_customergroups = "") OR (a.category_customergroups IS NULL) OR (a.category_customergroups = "' . $customerGroupId . '") OR (a.category_customergroups LIKE "' . $customerGroupId . ',%") OR (a.category_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.category_customergroups LIKE "%,' . $customerGroupId . '"))'
			);
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get all categories for certain language, cache on static variable
	 * and use it for some other methods
	 *
	 * @param   string  $langCode
	 *
	 * @return array
	 */
	public static function getAllCategories($langCode = '')
	{
		static $categoriesData = [];

		if (!$langCode)
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		if (!isset($categoriesData[$langCode]))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id, a.category_parent_id, b.category_name, b.category_alias')
				->from('#__eshop_categories AS a')
				->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
				->where('a.published = 1')
				->where('b.language = ' . $db->quote($langCode));

			$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

			$query->where(
				'((category_customergroups = "") OR (category_customergroups IS NULL) OR (category_customergroups = "' . $customerGroupId . '") OR (category_customergroups LIKE "' . $customerGroupId . ',%") OR (category_customergroups LIKE "%,' . $customerGroupId . ',%") OR (category_customergroups LIKE "%,' . $customerGroupId . '"))'
			);

			$db->setQuery($query);
			$categoriesData[$langCode] = $db->loadObjectList('id');
		}

		return $categoriesData[$langCode];
	}

	/**
	 *
	 * Function to get all child categories levels of a category
	 *
	 * @param   int  $id
	 *
	 * @return array
	 */
	public static function getAllChildCategories($id)
	{
		$categories = [];

		foreach (self::getAllCategories() as $row)
		{
			$parentId              = $row->category_parent_id ?? 0;
			$children              = $categories[$parentId] ?? [];
			$children[]            = $row->id;
			$categories[$parentId] = $children;
		}

		$children = [];

		if (isset($categories[$id]))
		{
			foreach ($categories[$id] as $childCategoryId)
			{
				$children[] = $childCategoryId;
				$children   = array_merge($children, self::getAllChildCategories($childCategoryId));
			}
		}

		return $children;
	}

	/**
	 *
	 * Function to get number products for a specific category
	 *
	 * @param   int  $categoryId
	 *
	 * @return int
	 */
	public static function getNumCategoryProducts($categoryId, $allLevels = false)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($allLevels)
		{
			$categoryIds = array_merge([$categoryId], self::getAllChildCategories($categoryId));
		}
		else
		{
			$categoryIds = [$categoryId];
		}

		$query->select('COUNT(DISTINCT(a.id))')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productcategories AS b ON (a.id = b.product_id)')
			->where('a.published = 1')
			->where('b.category_id IN (' . implode(',', $categoryIds) . ')');

		//Check out of stock
		if (self::getConfigValue('hide_out_of_stock_products'))
		{
			$query->where('a.product_quantity > 0');
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
	 * Function to get list of parent categories
	 *
	 * @param   int  $categoryId
	 *
	 * @return array of object
	 */
	public static function getParentCategories($categoryId, $langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db               = Factory::getDbo();
		$query            = $db->getQuery(true);
		$langCode         = $db->quote($langCode);
		$parentCategories = [];

		while (true)
		{
			$query->clear()
				->select('a.id, a.category_parent_id, b.category_name')
				->from('#__eshop_categories AS a')
				->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
				->where('a.id = ' . intval($categoryId))
				->where('a.published = 1')
				->where('b.language = ' . $langCode);
			$db->setQuery($query);
			$row = $db->loadObject();

			if ($row)
			{
				$parentCategories[] = $row;
				$categoryId         = $row->category_parent_id;
			}
			else
			{
				break;
			}
		}

		return $parentCategories;
	}

	/**
	 * Function to get values for a specific option
	 *
	 * @param   int     $optionId
	 * @param   string  $langCode
	 * @param   string  $multipleLanguage
	 *
	 * @return array
	 */
	public static function getOptionValues($optionId, $langCode = '', $multipleLanguage = 'true')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db        = Factory::getDbo();
		$query     = $db->getQuery(true);
		$languages = self::getLanguages();

		if (Multilanguage::isEnabled() && count($languages) > 1 && $multipleLanguage)
		{
			$query->select('*')
				->from('#__eshop_optionvalues')
				->where('option_id = ' . intval($optionId))
				->order('ordering');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if (count($rows))
			{
				for ($i = 0; $n = count($rows), $i < $n; $i++)
				{
					$query->clear()
						->select('*')
						->from('#__eshop_optionvaluedetails')
						->where('option_id = ' . intval($optionId))
						->where('optionvalue_id = ' . intval($rows[$i]->id));
					$db->setQuery($query);
					$detailsRows = $db->loadObjectList('language');

					if (count($detailsRows))
					{
						foreach ($detailsRows as $language => $detailsRow)
						{
							$rows[$i]->{'optionvaluedetails_id_' . $language} = $detailsRow->id;
							$rows[$i]->{'value_' . $language}                 = $detailsRow->value;
						}
					}
				}
			}
		}
		else
		{
			$query->select('ov.*, ovd.id AS optionvaluedetails_id, ovd.value, ovd.language')
				->from('#__eshop_optionvalues AS ov')
				->innerJoin('#__eshop_optionvaluedetails AS ovd ON (ov.id = ovd.optionvalue_id)')
				->where('ov.option_id = ' . intval($optionId))
				->where('ovd.language = ' . $db->quote($langCode))
				->order('ov.ordering');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
		}

		return $rows;
	}

	/**
	 * Function to get information for a specific product
	 *
	 * @param   int     $productId
	 * @param   string  $langCode
	 *
	 * @return stdClass
	 */
	public static function getProduct($productId, $langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
			'a.*, b.product_name, b.product_alias, b.product_desc, b.product_short_desc, b.meta_key, b.meta_desc, b.tab1_title, b.tab1_content, b.tab2_title, b.tab2_content, b.tab3_title, b.tab3_content, b.tab4_title, b.tab4_content, b.tab5_title, b.tab5_content'
		)
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('b.language = ' . $db->quote($langCode))
			->where('a.id = ' . intval($productId));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Function to get categories for a specific product
	 *
	 * @param   int     $productId
	 * @param   string  $langCode
	 *
	 * @return mixed
	 */
	public static function getProductCategories($productId, $langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('c.id, cd.category_name')
			->from('#__eshop_categories AS c')
			->innerJoin('#__eshop_categorydetails AS cd ON (c.id = cd.category_id)')
			->innerJoin('#__eshop_productcategories AS pc ON (c.id = pc.category_id)')
			->where('pc.product_id = ' . intval($productId))
			->where('cd.language = ' . $db->quote($langCode));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to get category id for a specific product
	 *
	 * @param   int  $productId
	 *
	 * @return int
	 */
	public static function getProductCategory($productId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.category_id')
			->from('#__eshop_productcategories AS a')
			->innerJoin('#__eshop_categories AS b ON (a.category_id = b.id)')
			->where('a.product_id = ' . intval($productId))
			->where('a.main_category = 1')
			->where('b.published = 1');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
	 * Function to get manufacturer for a specific product
	 *
	 * @param   int  $productId
	 *
	 * @return stdClass manufacturer object
	 */
	public static function getProductManufacturer($productId, $langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.id, m.manufacturer_image, m.manufacturer_email, md.manufacturer_name')
			->from('#__eshop_products AS p')
			->innerJoin('#__eshop_manufacturers AS m ON (p.manufacturer_id = m.id)')
			->innerJoin('#__eshop_manufacturerdetails AS md ON (m.id = md.manufacturer_id)')
			->where('p.id = ' . intval($productId))
			->where('md.language = ' . $db->quote($langCode));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Function to get related products for a specific product
	 *
	 * @param   int     $productId
	 * @param   string  $langCode
	 *
	 * @return mixed
	 */
	public static function getProductRelations($productId, $langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
			'p.*, pd.product_name, pd.product_alias, pd.product_desc, pd.product_short_desc, pd.product_page_title, pd.product_page_heading, pd.product_alt_image, pd.meta_key, pd.meta_desc'
		)
			->from('#__eshop_products AS p')
			->innerJoin('#__eshop_productdetails AS pd ON (p.id = pd.product_id)')
			->innerJoin('#__eshop_productrelations AS pr ON (p.id = pr.related_product_id)')
			->where('p.published = 1')
			->where('pr.product_id = ' . intval($productId))
			->where('pd.language = ' . $db->quote($langCode))
			->order('p.ordering');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Function to get product downloads for a specific product
	 *
	 * @param   int     $productId
	 * @param   string  $langCode
	 *
	 * @return mixed
	 */
	public static function getProductDownloads($productId, $langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.filename, a.total_downloads_allowed, b.download_name')
			->from('#__eshop_downloads AS a')
			->innerJoin('#__eshop_downloaddetails AS b ON (a.id = b.download_id)')
			->innerJoin('#__eshop_productdownloads AS c ON (a.id = c.download_id)')
			->where('c.product_id = ' . intval($productId))
			->where('b.language = ' . $db->quote($langCode));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to reviews for a specific product
	 *
	 * @param   int  $productId
	 *
	 * @return array reviews object list
	 */
	public static function getProductReviews($productId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_reviews')
			->where('product_id = ' . intval($productId))
			->where('published = 1');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to get average rating for a specific product
	 *
	 * @param   int  $productId
	 *
	 * @return float rating
	 */
	public static function getProductRating($productId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('AVG(rating) as rating')
			->from('#__eshop_reviews')
			->where('product_id = ' . intval($productId))
			->where('published = 1');
		$db->setQuery($query);

		$productRating = $db->loadResult();

		if (!$productRating)
		{
			$productRating = 0;
		}

		return $productRating;
	}

	/**
	 * Function to get attributes for a specific product
	 *
	 * @param   int     $productId
	 * @param   string  $langCode
	 *
	 * @return mixed
	 */
	public static function getProductAttributes($productId, $langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db        = Factory::getDbo();
		$query     = $db->getQuery(true);
		$languages = self::getLanguages();

		if (Multilanguage::isEnabled() && count($languages) > 1)
		{
			$query->select('a.id, pa.id AS productattribute_id, pa.published')
				->from('#__eshop_attributes AS a')
				->innerJoin('#__eshop_productattributes AS pa ON (a.id = pa.attribute_id)')
				->where('pa.product_id = ' . intval($productId))
				->order('a.ordering');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if (count($rows))
			{
				for ($i = 0; $n = count($rows), $i < $n; $i++)
				{
					$query->clear()
						->select('*')
						->from('#__eshop_productattributedetails')
						->where('productattribute_id = ' . intval($rows[$i]->productattribute_id));
					$db->setQuery($query);
					$detailsRows = $db->loadObjectList('language');

					if (count($detailsRows))
					{
						foreach ($detailsRows as $language => $detailsRow)
						{
							$rows[$i]->{'productattributedetails_id_' . $language} = $detailsRow->id;
							$rows[$i]->{'value_' . $language}                      = $detailsRow->value;
						}
					}
				}
			}
		}
		else
		{
			$query->select('a.id, pa.id AS productattribute_id, pa.published, pad.id AS productattributedetails_id ,pad.value')
				->from('#__eshop_attributes AS a')
				->innerJoin('#__eshop_productattributes AS pa ON (a.id = pa.attribute_id)')
				->innerJoin('#__eshop_productattributedetails AS pad ON (pa.id = pad.productattribute_id)')
				->where('pa.product_id = ' . intval($productId))
				->where('pad.language = ' . $db->quote($langCode))
				->order('a.ordering');
			$db->setQuery($query);

			$rows = $db->loadObjectList();
		}

		return $rows;
	}

	/**
	 * Function to get options for a specific product
	 *
	 * @param   int     $productId
	 * @param   string  $langCode
	 *
	 * @return mixed
	 */
	public static function getProductOptions($productId, $langCode = '')
	{
		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('o.id, o.option_type, o.option_image, od.option_name, od.option_desc, po.required, po.id AS product_option_id')
			->from('#__eshop_options AS o')
			->innerJoin('#__eshop_optiondetails AS od ON (o.id = od.option_id)')
			->innerJoin('#__eshop_productoptions AS po ON (o.id = po.option_id)')
			->where('po.product_id = ' . intval($productId))
			->where('od.language = ' . $db->quote($langCode))
			->where('o.published = 1');

		$optionDefaultSorting = EShopHelper::getConfigValue('option_default_sorting', 'name-asc');

		switch ($optionDefaultSorting)
		{
			case 'name-desc':
				$query->order('od.option_name DESC');
				break;
			case 'ordering-asc':
				$query->order('o.ordering ASC');
				break;
			case 'ordering-desc':
				$query->order('o.ordering DESC');
				break;
			case 'id-asc':
				$query->order('o.id ASC');
				break;
			case 'id-desc':
				$query->order('o.id DESC');
				break;
			case 'random':
				$query->order('RAND()');
				break;
			case 'name-asc':
			default:
				$query->order('od.option_name ASC');
				break;
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to get option values
	 *
	 * @param   int  $productId
	 * @param   int  $optionId
	 *
	 * @return array option value object list
	 */
	public static function getProductOptionValues($productId, $optionId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('pov.*')
			->from('#__eshop_productoptionvalues AS pov')
			->where('product_id = ' . intval($productId))
			->where('option_id = ' . intval($optionId))
			->order('id');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get main category id of given products
	 *
	 * @param   array  $productIds
	 *
	 * @return array
	 */
	public static function getProductsMainCategory($productIds)
	{
		if (empty($productIds))
		{
			return [];
		}
		
		$productIds = array_map('intval', $productIds);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('product_id, category_id')
			->from('#__eshop_productcategories')
			->where('main_category = 1')
			->where('product_id IN (' . implode(',', $productIds) . ')');
		$db->setQuery($query);

		return $db->loadObjectList('product_id');
	}

	/**
	 * Get total number required options for each product
	 *
	 * @param   array  $productIds
	 *
	 * @return array
	 */
	public static function getNumberRequiredOptionsForProducts($productIds)
	{
		if (empty($productIds))
		{
			return [];
		}
		
		$productIds = array_map('intval', $productIds);
		
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(o.id) AS number_required_options, po.product_id')
			->from('#__eshop_options AS o')
			->innerJoin('#__eshop_productoptions AS po ON (o.id = po.option_id)')
			->where('po.product_id IN (' . implode(',', $productIds) . ')')
			->where('o.published = 1')
			->where('po.required = 1')
			->group('po.product_id');
		$db->setQuery($query);

		return $db->loadObjectList('product_id');
	}

	/**
	 * Method to get product's images for list of products, groupped by Product ID
	 *
	 * @param   array  $productIds
	 * @param   int    $published
	 *
	 * @return array
	 */
	public static function getProductsImages(array $productIds, int $published = 1): array
	{
		if (empty($productIds))
		{
			return [];
		}
		
		$productIds = array_map('intval', $productIds);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('pi.*')
			->from('#__eshop_productimages AS pi')
			->where('product_id IN (' . implode(',', $productIds) . ')')
			->order('pi.ordering');

		if ($published == 1)
		{
			$query->where('published = ' . intval($published));
		}

		$db->setQuery($query);

		$productsImages = [];

		foreach ($db->loadObjectList() as $row)
		{
			$productsImages[$row->product_id][] = $row;
		}

		return $productsImages;
	}
	
	/**
	 * 
	 * Function to check if a product is in cart mode (basic) or not
	 */
	public static function isProductCartMode($product)
	{
		$user       = Factory::getUser();
		$isCartMode = true;
	
		if (self::isCatalogMode() || $product->product_cart_mode == 'hide')
		{
			$isCartMode = false;
		}
		else
		{
			$productInventory = self::getProductInventory($product);
	
			if (($product->product_cart_mode == 'registered' && !$user->get('id')) || !self::showPrice(
				) || $product->product_call_for_price || ($product->product_quantity <= 0 && !$productInventory['product_stock_checkout']))
			{
				$isCartMode = false;
			}
		}
	
		return $isCartMode;
	}
	
	/**
	 * 
	 * Function to check if products is in cart mode or not.
	 */
	public static function isProductsCartMode($productIds)
	{
		if (empty($productIds))
		{
			return [];
		}
		
		$productIds = array_map('intval', $productIds);
		$isCartMode = true;
		
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();
	
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('b.product_id')
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_productcategories AS b ON (a.id = b.category_id)')
			->where('b.product_id IN (' . implode(',', $productIds) . ')')
			->where('b.main_category = 1')
			->where(
				'((a.category_cart_mode_customergroups = "") OR (a.category_cart_mode_customergroups IS NULL) OR (a.category_cart_mode_customergroups = "' . $customerGroupId . '") OR (a.category_cart_mode_customergroups LIKE "' . $customerGroupId . ',%") OR (a.category_cart_mode_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.category_cart_mode_customergroups LIKE "%,' . $customerGroupId . '"))'
				);
		$db->setQuery($query);
	
		return $db->loadObjectList('product_id');
	
	}

	/**
	 *
	 * Function to get images for a specific product
	 *
	 * @param   int  $productId
	 *
	 * @return array
	 */
	public static function getProductImages($productId, $published = 1)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('pi.*')
			->from('#__eshop_productimages AS pi')
			->where('product_id = ' . intval($productId))
			->order('pi.ordering');

		if ($published == 1)
		{
			$query->where('published = ' . intval($published));
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to get tags for a specific product
	 *
	 * @param   int  $productId
	 *
	 * @return array
	 */
	public static function getProductTags($productId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*')
			->from('#__eshop_tags AS a')
			->innerJoin('#__eshop_producttags AS b ON (a.id = b.tag_id)')
			->where('a.published = 1')
			->where('b.product_id = ' . intval($productId));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Function do nothing for image
	 *
	 * @param   string  $filename
	 * @param   string  $imagePath
	 * @param   int     $width
	 * @param   int     $height
	 */
	public static function notResizeImage($filename, $imagePath, $width, $height)
	{
		return $filename;
	}

	/**
	 *
	 * Function to resize image
	 *
	 * @param   string  $filename
	 * @param   string  $imagePath
	 * @param   int     $width
	 * @param   int     $height
	 *
	 * @return void|string
	 */
	public static function resizeImage($filename, $imagePath, $width, $height)
	{
		if (!file_exists($imagePath . $filename) || !is_file($imagePath . $filename))
		{
			return;
		}
		$info      = pathinfo($filename);
		$extension = $info['extension'];
		$oldImage  = $filename;
		$newImage  = substr($filename, 0, strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

		if (strpos($newImage, 'watermark-' . $width . 'x' . $height . '.' . $extension))
		{
			$newImage = str_replace('watermark-' . $width . 'x' . $height . '.' . $extension, $width . 'x' . $height . '.' . $extension, $newImage);
		}

		if (self::getConfigValue('recreate_watermark_images'))
		{
			$recreateResizeImage = true;
		}
		else
		{
			$recreateResizeImage = false;
		}

		if (!file_exists($imagePath . '/resized/' . $newImage) || (filemtime($imagePath . $oldImage) > filemtime(
					$imagePath . '/resized/' . $newImage
				)) || $recreateResizeImage)
		{
			[$width_orig, $height_orig] = getimagesize($imagePath . $oldImage);
			if ($width_orig != $width || $height_orig != $height)
			{
				$image = new EShopImage($imagePath . $oldImage);
				$image->resize($width, $height);
				$image->save($imagePath . '/resized/' . $newImage);
			}
			else
			{
				copy($imagePath . $oldImage, $imagePath . '/resized/' . $newImage);
			}
		}

		return $newImage;
	}

	/**
	 *
	 * Function to cropsize image
	 *
	 * @param   string  $filename
	 * @param   string  $imagePath
	 * @param   int     $width
	 * @param   int     $height
	 *
	 * @return void|string
	 */
	public static function cropsizeImage($filename, $imagePath, $width, $height)
	{
		if (!file_exists($imagePath . $filename) || !is_file($imagePath . $filename))
		{
			return;
		}

		$info      = pathinfo($filename);
		$extension = $info['extension'];
		$oldImage  = $filename;
		$newImage  = substr($filename, 0, strrpos($filename, '.')) . '-cr-' . $width . 'x' . $height . '.' . $extension;

		if (strpos($newImage, 'watermark-cr-' . $width . 'x' . $height . '.' . $extension))
		{
			$newImage = str_replace(
				'watermark-cr-' . $width . 'x' . $height . '.' . $extension,
				$width . 'x' . $height . '.' . $extension,
				$newImage
			);
		}

		if (self::getConfigValue('recreate_watermark_images'))
		{
			$recreateResizeImage = true;
		}
		else
		{
			$recreateResizeImage = false;
		}

		if (!file_exists($imagePath . '/resized/' . $newImage) || (filemtime($imagePath . $oldImage) > filemtime(
					$imagePath . '/resized/' . $newImage
				)) || $recreateResizeImage)
		{
			[$width_orig, $height_orig] = getimagesize($imagePath . $oldImage);

			if ($width_orig != $width || $height_orig != $height)
			{
				$image = new EShopImage($imagePath . $oldImage);
				$image->cropsize($width, $height);
				$image->save($imagePath . '/resized/' . $newImage);
			}
			else
			{
				copy($imagePath . $oldImage, $imagePath . '/resized/' . $newImage);
			}
		}

		return $newImage;
	}

	/**
	 *
	 * Function to max size image
	 *
	 * @param   string  $filename
	 * @param   string  $imagePath
	 * @param   int     $width
	 * @param   int     $height
	 *
	 * @return void|string
	 */
	public static function maxsizeImage($filename, $imagePath, $width, $height)
	{
		$maxsize = ($width > $height) ? $width : $height;

		if (!file_exists($imagePath . $filename) || !is_file($imagePath . $filename))
		{
			return;
		}

		$info      = pathinfo($filename);
		$extension = $info['extension'];
		$oldImage  = $filename;
		$newImage  = substr($filename, 0, strrpos($filename, '.')) . '-max-' . $width . 'x' . $height . '.' . $extension;

		if (strpos($newImage, 'watermark-max-' . $width . 'x' . $height . '.' . $extension))
		{
			$newImage = str_replace(
				'watermark-max-' . $width . 'x' . $height . '.' . $extension,
				$width . 'x' . $height . '.' . $extension,
				$newImage
			);
		}

		if (self::getConfigValue('recreate_watermark_images'))
		{
			$recreateResizeImage = true;
		}
		else
		{
			$recreateResizeImage = false;
		}

		if (!file_exists($imagePath . '/resized/' . $newImage) || (filemtime($imagePath . $oldImage) > filemtime(
					$imagePath . '/resized/' . $newImage
				)) || $recreateResizeImage)
		{
			$image = new EShopImage($imagePath . $oldImage);
			$image->maxsize($maxsize);
			$image->save($imagePath . '/resized/' . $newImage);
		}

		return $newImage;
	}

	/**
	 *
	 * Function to get discount for a specific product
	 *
	 * @param   int  $productId
	 *
	 * @return array
	 */
	public static function getProductDiscounts($productId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('pd.*')
			->from('#__eshop_productdiscounts AS pd')
			->innerJoin('#__eshop_customergroups AS cg ON (pd.customergroup_id = cg.id)')
			->where('pd.product_id = ' . intval($productId))
			->order('pd.priority');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return $rows;
	}

	/**
	 *
	 * Function to get special for a specific product
	 *
	 * @param   int  $productId
	 *
	 * @return array
	 */
	public static function getProductSpecials($productId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('ps.*')
			->from('#__eshop_productspecials AS ps')
			->innerJoin('#__eshop_customergroups AS cg ON (ps.customergroup_id = cg.id)')
			->where('ps.product_id = ' . intval($productId))
			->order('ps.priority');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to get discount price for a specific product
	 *
	 * @param   int  $productId
	 *
	 * @return float
	 */
	public static function getDiscountPrice($productId)
	{
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());

		$query->select('price')
			->from('#__eshop_productdiscounts')
			->where('product_id = ' . intval($productId))
			->where('published = 1')
			->where('customergroup_id = ' . intval($customerGroupId))
			->where('(date_start = ' . $nullDate . ' OR date_start IS NULL OR date_start <= ' . $currentDate . ')')
			->where('(date_end = ' . $nullDate . ' OR date_end IS NULL OR date_end >= ' . $currentDate . ')')
			->where('quantity = 1')
			->order('priority');
		$db->setQuery($query);
		$discountPrice = $db->loadResult();

		if ($discountPrice > 0)
		{
			return $discountPrice;
		}
		else
		{
			return -1;
		}
	}

	/**
	 *
	 * Function to get discount prices for a specific product - is used to dipslay product discounts on the product details page
	 *
	 * @param   int  $productId
	 *
	 * @return array
	 */
	public static function getDiscountPrices($productId)
	{
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());

		$query->select('quantity, price')
			->from('#__eshop_productdiscounts')
			->where('product_id = ' . intval($productId))
			->where('published = 1')
			->where('customergroup_id = ' . intval($customerGroupId))
			->where('(date_start = ' . $nullDate . ' OR date_start IS NULL OR date_start <= ' . $currentDate . ')')
			->where('(date_end = ' . $nullDate . ' OR date_end IS NULL OR date_end >= ' . $currentDate . ')')
			->where('quantity > 1')
			->order('priority');
		$db->setQuery($query);
		$discountPrices = $db->loadObjectList();

		for ($i = 0; $n = count($discountPrices), $i < $n; $i++)
		{
			$specialPrice = self::getSpecialPrice($productId, $discountPrices[$i]->price);

			if ($specialPrice >= 0)
			{
				$discountPrices[$i]->price = $specialPrice;
			}
		}

		return $discountPrices;
	}

	/**
	 *
	 * Function to get special price
	 *
	 * @param   int    $productId
	 * @param   float  $productPrice
	 *
	 * @return float
	 */
	public static function getSpecialPrice($productId, $productPrice)
	{
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		// First, check if there is a special price for the product or not. Special Price has highest priority
		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());

		$query->select('price')
			->from('#__eshop_productspecials')
			->where('product_id = ' . intval($productId))
			->where('published = 1')
			->where('customergroup_id = ' . intval($customerGroupId))
			->where('(date_start = ' . $nullDate . ' OR date_start IS NULL OR date_start <= ' . $currentDate . ')')
			->where('(date_end = ' . $nullDate . ' OR date_end IS NULL OR date_end >= ' . $currentDate . ')')
			->order('priority');
		$db->setQuery($query, 0, 1);
		$row                = $db->loadObject();
		$originProductPrice = $productPrice;

		if (is_object($row))
		{
			$specialPrice = $row->price;

			if ($specialPrice >= 0 && $productPrice > $specialPrice)
			{
				$productPrice = $specialPrice;
			}
			else
			{
				$specialPrice = -1;
			}
		}
		else
		{
			$specialPrice = -1;
		}


		// Check for product discount first
		$query->clear()
			->select('a.*')
			->from('#__eshop_discounts AS a')
			->innerJoin('#__eshop_discountelements AS b ON (a.id = b.discount_id)')
			->where('a.published = 1')
			->where('b.element_type = "product" AND (b.element_id = ' . intval($productId) . ' OR b.element_id = 0)')
			->where(
				'((a.discount_customergroups = "") OR (a.discount_customergroups IS NULL) OR (a.discount_customergroups = "' . $customerGroupId . '") OR (a.discount_customergroups LIKE "' . $customerGroupId . ',%") OR (a.discount_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.discount_customergroups LIKE "%,' . $customerGroupId . '"))'
			)
			->where('(a.discount_start_date = ' . $nullDate . ' OR a.discount_start_date IS NULL OR a.discount_start_date <= ' . $currentDate . ')')
			->where('(a.discount_end_date = ' . $nullDate . ' OR a.discount_end_date IS NULL OR a.discount_end_date >= ' . $currentDate . ')')
			->order('a.id DESC');
		$db->setQuery($query, 0, 1);
		$row = $db->loadObject();

		if (!is_object($row))
		{
			// Check for product categories and manufacturers
			$query->clear()
				->select('a.*')
				->from('#__eshop_discounts AS a')
				->innerJoin('#__eshop_discountelements AS b ON (a.id = b.discount_id)')
				->where('a.published = 1')
				->where(
					'((b.element_type = "manufacturer" AND (b.element_id = (SELECT manufacturer_id FROM #__eshop_products WHERE id = ' . intval(
						$productId
					) . ') OR b.element_id = 0)) OR (b.element_type = "category" AND (b.element_id IN (SELECT category_id FROM #__eshop_productcategories WHERE product_id = ' . intval(
						$productId
					) . ')  OR b.element_id = 0)))'
				)
				->where(
					'((a.discount_customergroups = "") OR (a.discount_customergroups IS NULL) OR (a.discount_customergroups = "' . $customerGroupId . '") OR (a.discount_customergroups LIKE "' . $customerGroupId . ',%") OR (a.discount_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.discount_customergroups LIKE "%,' . $customerGroupId . '"))'
				)
				->where(
					'(a.discount_start_date = ' . $nullDate . ' OR a.discount_start_date IS NULL OR a.discount_start_date <= ' . $currentDate . ')'
				)
				->where('(a.discount_end_date = ' . $nullDate . ' OR a.discount_end_date IS NULL OR a.discount_end_date >= ' . $currentDate . ')')
				->order('a.id DESC');
			$db->setQuery($query, 0, 1);
			$row = $db->loadObject();
		}

		if (is_object($row))
		{
			$discountValue = $row->discount_value;
			$discountType  = $row->discount_type;

			if ($discountType == 'P')
			{
				$specialPrice = $productPrice * (1 - $discountValue / 100);
			}
			else
			{
				if ($discountValue >= $productPrice)
				{
					$specialPrice = -1;
				}
				else
				{
					$specialPrice = $productPrice - $discountValue;
				}
			}
		}

		if ($specialPrice >= $originProductPrice)
		{
			$specialPrice = -1;
		}

		return $specialPrice;
	}

	/**
	 *
	 * Function to get special price for an option price
	 *
	 * @param   int    $productId
	 * @param   float  $optionPrice
	 *
	 * @return float
	 */
	public static function getOptionDiscountPrice($productId, $optionPrice)
	{
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());

		// Check for product discount
		$query->select('a.id')
			->from('#__eshop_discounts AS a')
			->innerJoin('#__eshop_discountelements AS b ON a.id = b.discount_id')
			->where('a.published = 1')
			->where('b.element_type = "product" AND (b.element_id = ' . intval($productId) . ' OR b.element_id = 0)')
			->order('a.id DESC');
		$db->setQuery($query, 0, 1);
		$discountId = $db->loadResult();

		if (!$discountId)
		{
			// Check for product categories and manufacturers
			$query->clear()
				->select('a.id')
				->from('#__eshop_discounts AS a')
				->innerJoin('#__eshop_discountelements AS b ON a.id = b.discount_id')
				->where('a.published = 1')
				->where(
					'(b.element_type = "manufacturer" AND (b.element_id = (SELECT manufacturer_id FROM #__eshop_products WHERE id = ' . intval(
						$productId
					) . ') OR b.element_id = 0)) OR (b.element_type = "category" AND (b.element_id IN (SELECT category_id FROM #__eshop_productcategories WHERE product_id = ' . intval(
						$productId
					) . ')  OR b.element_id = 0))'
				)
				->order('a.id DESC');
			$db->setQuery($query, 0, 1);
			$discountId = $db->loadResult();
		}

		if ($discountId)
		{
			$query->clear()
				->select('*')
				->from('#__eshop_discounts')
				->where('id = ' . intval($discountId))
				->where('published = 1')
				->where(
					'((discount_customergroups = "") OR (discount_customergroups IS NULL) OR (discount_customergroups = "' . $customerGroupId . '") OR (discount_customergroups LIKE "' . $customerGroupId . ',%") OR (discount_customergroups LIKE "%,' . $customerGroupId . ',%") OR (discount_customergroups LIKE "%,' . $customerGroupId . '"))'
				)
				->where('(discount_start_date = ' . $nullDate . ' OR discount_start_date IS NULL OR discount_start_date <= ' . $currentDate . ')')
				->where('(discount_end_date = ' . $nullDate . ' OR discount_end_date IS NULL OR discount_end_date >= ' . $currentDate . ')');
			$db->setQuery($query);
			$row = $db->loadObject();

			if (is_object($row))
			{
				$discountValue = $row->discount_value;
				$discountType  = $row->discount_type;

				if ($discountType == 'P')
				{
					$optionPrice = $optionPrice * (1 - $discountValue / 100);
				}
				/*
				else
				{
					if ($discountValue >= $optionPrice)
					{
						$optionPrice = 0;
					}
					else
					{
						$optionPrice = $optionPrice - $discountValue;
					}
				}
				*/
			}
		}

		return $optionPrice;
	}

	/**
	 *
	 * Function to get product price array
	 *
	 * @param   int    $productId
	 * @param   float  $productPrice
	 *
	 * @return array of price
	 */
	public static function getProductPriceArray($productId, $productPrice)
	{
		$specialPrice  = self::getSpecialPrice($productId, $productPrice);
		$discountPrice = self::getDiscountPrice($productId);

		if ($specialPrice >= 0)
		{
			$salePrice = $specialPrice;

			if ($discountPrice >= 0)
			{
				$basePrice = $discountPrice;
			}
			else
			{
				$basePrice = $productPrice;
			}
		}
		else
		{
			$basePrice = $productPrice;
			$salePrice = $discountPrice;
		}

		$productPriceArray = ["basePrice" => $basePrice, "salePrice" => $salePrice];

		return $productPriceArray;
	}

	/**
	 *
	 * Function to get currency format for a specific number
	 *
	 * @param   float  $number
	 * @param   int    $currencyId
	 *
	 * @return string
	 */

	public static function getCurrencyFormat($number, $currencyId = 0)
	{
		if (!$currencyId)
		{
			// Use default currency
			$currencyId = 4;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_currencies')
			->where('id = ' . intval($currencyId));
		$db->setQuery($query);
		$row = $db->loadObject();

		$currencyFormat = '';
		$sign           = '';

		if ($number < 0)
		{
			$sign   = '-';
			$number = abs($number);
		}

		if (is_object($row))
		{
			$currencyFormat = $sign . $row->left_symbol . number_format(
					$number,
					$row->decimal_place,
					$row->decimal_symbol,
					$row->thousands_separator
				) .
				$row->right_symbol;
		}

		return $currencyFormat;
	}

	/**
	 *
	 * Function to round out a number
	 *
	 * @param   float  $number
	 * @param   int    $places
	 *
	 * @return float
	 */
	public static function roundOut($number, $places = 0)
	{
		if ($places < 0)
		{
			$places = 0;
		}

		$mult = pow(10, $places);

		return ($number >= 0 ? ceil($number * $mult) : floor($number * $mult)) / $mult;
	}

	/**
	 *
	 * Function to round up a number
	 *
	 * @param   float  $number
	 * @param   int    $places
	 *
	 * @return float
	 */
	public static function roundUp($number, $places = 0)
	{
		if ($places < 0)
		{
			$places = 0;
		}

		$mult = pow(10, $places);

		return ceil($number * $mult) / $mult;
	}

	/**
	 *
	 * Function to get information for a specific address
	 *
	 * @param   int  $addressId
	 *
	 * @return array
	 */
	public static function getAddress($addressId)
	{
		$user  = Factory::getUser();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, z.zone_name, z.zone_code, c.country_name, c.iso_code_2, c.iso_code_3')
			->from('#__eshop_addresses AS a')
			->leftJoin('#__eshop_zones AS z ON (a.zone_id = z.id)')
			->leftJoin('#__eshop_countries AS c ON (a.country_id = c.id)')
			->where('a.id = ' . intval($addressId))
			->where('a.customer_id = ' . intval($user->get('id')));
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 *
	 * Function to get information for a specific customer
	 *
	 * @param   int  $customerId
	 *
	 * @return stdClass customer object
	 */
	public static function getCustomer($customerId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_customers')
			->where('customer_id = ' . intval($customerId));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 *
	 * Function to get information for a specific country
	 *
	 * @param   int  $countryId
	 *
	 * @return stdClass
	 */
	public static function getCountry($countryId)
	{
		static $countries = [];

		if (!isset($countries[$countryId]))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__eshop_countries')
				->where('id = ' . intval($countryId))
				->where('published = 1');
			$db->setQuery($query);
			$countries[$countryId] = $db->loadObject();
		}

		return $countries[$countryId];
	}

	/**
	 *
	 * Function to get Zones for a specific Country
	 *
	 * @param   int  $countryId
	 *
	 * @return stdClass
	 */
	public static function getCountryZones($countryId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, zone_name')
			->from('#__eshop_zones')
			->where('country_id = ' . intval($countryId))
			->where('published = 1')
			->order('zone_name');
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 *
	 * Function to get information for a specific zone
	 *
	 * @param   int  $zoneId
	 *
	 * @return stdClass
	 */
	public static function getZone($zoneId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_zones')
			->where('id = ' . intval($zoneId))
			->where('published = 1');
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 *
	 * Function to get information for a specific geozone
	 *
	 * @param   int  $geozoneId
	 *
	 * @return stdClass
	 */
	public static function getGeozone($geozoneId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_geozones')
			->where('id = ' . intval($geozoneId))
			->where('published = 1');
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 *
	 * Function to check if an order can be cancelled or not
	 *
	 * @param   order object $row
	 */
	public static function canCancelOrder($row)
	{
		$canCancel = true;

		if ($row->order_status_id != EShopHelper::getConfigValue('order_status_id'))
		{
			$canCancel = false;
		}

		return $canCancel;
	}

	/**
	 *
	 * Function to complete an order
	 *
	 * @param   order object $row
	 */
	public static function completeOrder($row)
	{
		self::updateInventory($row, '-');

		//Add coupon history and voucher history
		self::addCouponHistory($row);
		self::addVoucherHistory($row);
	}

	public static function updateInventory($row, $updateType = '+')
	{
		$orderId = intval($row->id);
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_orderproducts')
			->where('order_id = ' . intval($orderId));
		$db->setQuery($query);
		$orderProducts = $db->loadObjectList();

		foreach ($orderProducts as $orderProduct)
		{
			$productInventory = self::getProductInventory($orderProduct->product_id);

			if ($productInventory['product_manage_stock'])
			{
				//Update product quantity
				$query->clear()
					->update('#__eshop_products')
					->set('product_quantity = product_quantity ' . $updateType . ' ' . intval($orderProduct->quantity))
					->where('id = ' . intval($orderProduct->product_id));
				$db->setQuery($query);
				$db->execute();

				//Update product options
				$query->clear()
					->select('*')
					->from('#__eshop_orderoptions')
					->where('order_id = ' . intval($orderId))
					->where('order_product_id = ' . intval($orderProduct->id));
				$db->setQuery($query);
				$orderOptions = $db->loadObjectList();

				foreach ($orderOptions as $orderOption)
				{
					if ($orderOption->option_type == 'Select' || $orderOption->option_type == 'Radio' || $orderOption->option_type == 'Checkbox')
					{
						$query->clear()
							->update('#__eshop_productoptionvalues')
							->set('quantity = quantity ' . $updateType . ' ' . intval($orderProduct->quantity))
							->where('id = ' . intval($orderOption->product_option_value_id));
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}
	}

	/**
	 *
	 * function to get Inventory for a specific product
	 *
	 * @param   int|stdClass  $row
	 */
	public static function getProductInventory($row)
	{
		if (!is_object($row))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select(
				'product_manage_stock, product_stock_display, product_show_availability, product_stock_warning, product_inventory_global, product_quantity, product_threshold, product_stock_checkout, product_stock_status_id'
			)
				->from('#__eshop_products')
				->where('id = ' . intval($row));
			$db->setQuery($query);
			$row = $db->loadObject();
		}

		if (is_object($row) && !$row->product_inventory_global)
		{
			$productInventory = [
				'product_manage_stock'      => $row->product_manage_stock,
				'product_stock_display'     => $row->product_stock_display,
				'product_show_availability' => $row->product_show_availability,
				'product_stock_warning'     => $row->product_stock_warning,
				'product_quantity'          => $row->product_quantity,
				'product_threshold'         => $row->product_threshold,
				'product_stock_checkout'    => $row->product_stock_checkout,
				'product_stock_status_id'   => $row->product_stock_status_id,
			];
		}
		else
		{
			$productInventory = [
				'product_manage_stock'      => self::getConfigValue('stock_manage', 1),
				'product_stock_display'     => self::getConfigValue('stock_display', 1),
				'product_show_availability' => self::getConfigValue('show_availability'),
				'product_stock_warning'     => self::getConfigValue('stock_warning', 1),
				'product_quantity'          => is_object($row) ? $row->product_quantity : 0,
				'product_threshold'         => self::getConfigValue('threshold', 0),
				'product_stock_checkout'    => self::getConfigValue('stock_checkout', 1),
				'product_stock_status_id'   => self::getConfigValue('stock_status_id'),
			];
		}

		return $productInventory;
	}

	/**
	 *
	 * Function to add coupon history
	 *
	 * @param   order object $row
	 */
	public static function addCouponHistory($row)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('value')
			->from('#__eshop_ordertotals')
			->where('order_id = ' . intval($row->id))
			->where('name = "coupon"');
		$db->setQuery($query);
		$amount = $db->loadResult();

		if ($amount)
		{
			$couponId = $row->coupon_id;

			if ($couponId)
			{
				$coupon = new EShopCoupon();
				$coupon->addCouponHistory($couponId, $row->id, $row->customer_id, $amount);
			}
		}
	}

	/**
	 *
	 * Function to add voucher history
	 *
	 * @param   order object $row
	 */
	public static function addVoucherHistory($row)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('value')
			->from('#__eshop_ordertotals')
			->where('order_id = ' . intval($row->id))
			->where('name = "voucher"');
		$db->setQuery($query);
		$amount = $db->loadResult();

		if ($amount)
		{
			$voucherId = $row->voucher_id;

			if ($voucherId)
			{
				$voucher = new EShopVoucher();
				$voucher->addVoucherHistory($voucherId, $row->id, $row->customer_id, $amount);
			}
		}
	}

	/**
	 *
	 * Function to send quote emails
	 *
	 * @param   order object $row
	 */
	public static function sendQuoteEmails($row)
	{
		$mailer    = Factory::getMailer();
		$sendFrom  = self::getSendFrom();
		$fromName  = $sendFrom['from_name'];
		$fromEmail = $sendFrom['from_email'];

		//Send notification email to admin
		if (self::getConfigValue('quote_alert_mail_admin', 1))
		{
			$adminSubject = self::getMessageValue('admin_quote_email_subject');
			$adminSubject = str_replace('[CUSTOMER_NAME]', $row->name, $adminSubject);
			$adminBody    = self::getAdminQuoteEmailBody($row);
			$adminBody    = self::convertImgTags($adminBody);
			$adminEmail   = self::getConfigValue('email') ? trim(self::getConfigValue('email')) : $fromEmail;

			try
			{
				$mailer->sendMail($fromEmail, $fromName, $adminEmail, $adminSubject, $adminBody, 1);
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			}

			//Send notification email to additional emails
			$alertEmails = self::getConfigValue('quote_alert_emails');
			$alertEmails = str_replace(' ', '', $alertEmails);
			$alertEmails = explode(',', $alertEmails);
			for ($i = 0; $n = count($alertEmails), $i < $n; $i++)
			{
				if ($alertEmails[$i] != '')
				{
					$mailer->clearAllRecipients();

					try
					{
						$mailer->sendMail($fromEmail, $fromName, $alertEmails[$i], $adminSubject, $adminBody, 1);
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
					}
				}
			}
		}

		//Send email to customer
		if (self::getConfigValue('quote_alert_mail_customer', 1))
		{
			$customerSubject = self::getMessageValue('customer_quote_email_subject');
			$customerBody    = self::getCustomerQuoteEmailBody($row);
			$customerBody    = self::convertImgTags($customerBody);
			$mailer->clearAllRecipients();

			try
			{
				$mailer->sendMail($fromEmail, $fromName, $row->email, $customerSubject, $customerBody, 1);
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			}
		}
	}

	/**
	 *
	 * Function to get admin quote email body
	 *
	 * @param   quote object $row
	 *
	 * @return string
	 */
	public static function getAdminQuoteEmailBody($row)
	{
		$currency         = EShopCurrency::getInstance();
		$row->total_price = $currency->format($row->total, $row->currency_code, $row->currency_exchanged_value);
		$adminEmailBody   = self::getMessageValue('admin_quote_email');

		// Quote information
		$replaces                 = [];
		$replaces['name']         = $row->name;
		$replaces['email']        = $row->email;
		$replaces['company']      = $row->company;
		$replaces['telephone']    = $row->telephone;
		$replaces['address']      = $row->address;
		$replaces['city']         = $row->city;
		$replaces['postcode']     = $row->postcode;
		$replaces['country_name'] = $row->country_name;
		$replaces['zone_name']    = $row->zone_name;
		$replaces['message']      = $row->message;
		$replaces['quote_id']     = $row->id;

		// Products list
		$quoteProducts             = self::getQuoteProducts($row->id);
		$quoteTotals			   = self::getQuoteTotals($row->id);
		$text                      = EShopHtmlHelper::loadCommonLayout(
			'emailtemplates/quoteproducts.php',
			['quoteProducts' => $quoteProducts, 'quoteTotals' => $quoteTotals, 'row' => $row]
		);
		$replaces['products_list'] = $text;

		foreach ($replaces as $key => $value)
		{
			$key            = strtoupper($key);
			$adminEmailBody = str_replace("[$key]", $value, $adminEmailBody);
		}

		return $adminEmailBody;
	}

	/**
	 *
	 * Function to get customer quote email body
	 *
	 * @param   quote object $row
	 *
	 * @return string
	 */
	public static function getCustomerQuoteEmailBody($row)
	{
		$currency          = EShopCurrency::getInstance();
		$row->total_price  = $currency->format($row->total, $row->currency_code, $row->currency_exchanged_value);
		$customerEmailBody = self::getMessageValue('customer_quote_email');

		// Quote information
		$replaces = [];

		// Products list
		$quoteProducts = self::getQuoteProducts($row->id);
		$quoteTotals   = self::getQuoteTotals($row->id);
		$text          = EShopHtmlHelper::loadCommonLayout('emailtemplates/quoteproducts.php', ['quoteProducts' => $quoteProducts, 'quoteTotals' => $quoteTotals, 'row' => $row]);

		$replaces['name']          = $row->name;
		$replaces['email']         = $row->email;
		$replaces['company']       = $row->company;
		$replaces['telephone']     = $row->telephone;
		$replaces['message']       = $row->message;
		$replaces['quote_id']      = $row->id;
		$replaces['products_list'] = $text;

		foreach ($replaces as $key => $value)
		{
			$key               = strtoupper($key);
			$customerEmailBody = str_replace("[$key]", $value, $customerEmailBody);
		}

		return $customerEmailBody;
	}

	/**
	 *
	 * Function to send email
	 *
	 * @param   order object $row
	 */
	public static function sendEmails($row)
	{
		$mailer             = Factory::getMailer();
		$sendFrom           = self::getSendFrom();
		$fromName           = $sendFrom['from_name'];
		$fromEmail          = $sendFrom['from_email'];
		$customerAttachment = null;
		$adminAttachment    = null;

		if (self::isInvoiceAvailable($row, '0', true) || self::isInvoiceAvailable($row, '1', true))
		{
			if (!$row->invoice_number)
			{
				$row->invoice_number = self::getInvoiceNumber();
				$row->store();
			}

			self::generateInvoicePDF([$row->id]);

			$attachment = JPATH_ROOT . '/media/com_eshop/invoices/' . self::formatInvoiceNumber($row->invoice_number, $row->created_date) . '.pdf';

			if (self::isInvoiceAvailable($row, '0', true))
			{
				$customerAttachment = $attachment;
			}

			if (self::isInvoiceAvailable($row, '1', true))
			{
				$adminAttachment = $attachment;
			}
		}

		//Send notification email to admin
		if (self::getConfigValue('order_alert_mail_admin', 1))
		{
			$adminSubject = self::getMessageValue('admin_notification_email_subject', $row->language);
			$adminSubject = str_replace('[STORE_NAME]', self::getConfigValue('store_name'), $adminSubject);
			$adminSubject = str_replace('[ORDER_ID]', $row->id, $adminSubject);
			$adminSubject = str_replace('[ORDER_NUMBER]', $row->order_number, $adminSubject);
			$adminSubject = str_replace('[CUSTOMER_NAME]', $row->firstname . ' ' . $row->lastname, $adminSubject);
			$adminBody    = self::getAdminEmailBody($row);
			$adminBody    = self::convertImgTags($adminBody);
			$adminEmail   = self::getConfigValue('email') ? trim(self::getConfigValue('email')) : $fromEmail;

			try
			{
				if (self::getConfigValue('order_reply_to_customer', 0))
				{
					$mailer->sendMail($fromEmail, $fromName, $adminEmail, $adminSubject, $adminBody, 1, null, null, $adminAttachment, $row->email);
				}
				else
				{
					$mailer->sendMail($fromEmail, $fromName, $adminEmail, $adminSubject, $adminBody, 1, null, null, $adminAttachment);
				}
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			}

			//Send notification email to additional emails
			$alertEmails = self::getConfigValue('alert_emails');
			$alertEmails = str_replace(' ', '', $alertEmails);
			$alertEmails = explode(',', $alertEmails);

			for ($i = 0; $n = count($alertEmails), $i < $n; $i++)
			{
				if ($alertEmails[$i] != '')
				{
					$mailer->clearAllRecipients();
					$mailer->clearAttachments();

					try
					{
						if (self::getConfigValue('order_reply_to_customer', 0))
						{
							$mailer->sendMail(
								$fromEmail,
								$fromName,
								$alertEmails[$i],
								$adminSubject,
								$adminBody,
								1,
								null,
								null,
								$adminAttachment,
								$row->email
							);
						}
						else
						{
							$mailer->sendMail($fromEmail, $fromName, $alertEmails[$i], $adminSubject, $adminBody, 1, null, null, $adminAttachment);
						}
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
					}
				}
			}
		}

		//Send notification email to manufacturer
		if (self::getConfigValue('order_alert_mail_manufacturer', 1))
		{
			$manufacturers = [];
			$orderProducts = self::getOrderProducts($row->id);

			for ($i = 0; $n = count($orderProducts), $i < $n; $i++)
			{
				$product      = $orderProducts[$i];
				$manufacturer = self::getProductManufacturer($product->product_id, Factory::getLanguage()->getTag());

				if (is_object($manufacturer))
				{
					$manufacturer->product = $orderProducts[$i];

					if (!isset($manufacturers[$manufacturer->id]))
					{
						$manufacturers[$manufacturer->id] = [];
					}

					$manufacturers[$manufacturer->id][] = $manufacturer;
				}
			}

			$manufacturerSubject = self::getMessageValue('manufacturer_notification_email_subject', $row->language);

			foreach ($manufacturers as $manufacturer)
			{
				if ($manufacturer[0]->manufacturer_email != '')
				{
					$manufacturerBody = self::getManufacturerEmailBody($manufacturer, $row);
					$manufacturerBody = self::convertImgTags($manufacturerBody);
					$mailer->clearAllRecipients();
					$mailer->clearAttachments();

					try
					{
						$mailer->sendMail($fromEmail, $fromName, $manufacturer[0]->manufacturer_email, $manufacturerSubject, $manufacturerBody, 1);
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
					}
				}
			}
		}

		//Send email to customer
		if (self::getConfigValue('order_alert_mail_customer', 1))
		{
			$customerSubject = self::getMessageValue('customer_guest_notification_email_subject', $row->language);
			$customerSubject = str_replace('[STORE_NAME]', self::getConfigValue('store_name'), $customerSubject);
			$customerSubject = str_replace('[ORDER_ID]', $row->id, $customerSubject);
			$customerSubject = str_replace('[ORDER_NUMBER]', $row->order_number, $customerSubject);
			$customerSubject = str_replace('[CUSTOMER_NAME]', $row->firstname . ' ' . $row->lastname, $customerSubject);
			$customerBody    = self::getCustomerEmailBody($row);
			$customerBody    = self::convertImgTags($customerBody);
			$mailer->clearAllRecipients();
			$mailer->clearAttachments();

			try
			{
				$mailer->sendMail($fromEmail, $fromName, $row->email, $customerSubject, $customerBody, 1, null, null, $customerAttachment);
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			}
		}
	}

	/**
	 *
	 * Function to send admin notification emails
	 *
	 * @param   order object $row
	 */
	public static function sendAdminNotifyEmails($row, $notifyType)
	{
		$mailer    = Factory::getMailer();
		$sendFrom  = self::getSendFrom();
		$fromName  = $sendFrom['from_name'];
		$fromEmail = $sendFrom['from_email'];

		$adminSubject = self::getMessageValue('admin_' . $notifyType . '_notification_email_subject', $row->language);
		$adminSubject = str_replace('[STORE_NAME]', self::getConfigValue('store_name'), $adminSubject);
		$adminSubject = str_replace('[ORDER_ID]', $row->id, $adminSubject);
		$adminSubject = str_replace('[ORDER_NUMBER]', $row->order_number, $adminSubject);
		$adminSubject = str_replace('[CUSTOMER_NAME]', $row->firstname . ' ' . $row->lastname, $adminSubject);
		$adminBody    = self::getAdminNotifyEmailBody($row, $notifyType);
		$adminBody    = self::convertImgTags($adminBody);
		$adminEmail   = self::getConfigValue('email') ? trim(self::getConfigValue('email')) : $fromEmail;

		try
		{
			$mailer->sendMail($fromEmail, $fromName, $adminEmail, $adminSubject, $adminBody, 1);
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		//Send notification email to additional emails
		$alertEmails = self::getConfigValue('alert_emails');
		$alertEmails = str_replace(' ', '', $alertEmails);
		$alertEmails = explode(',', $alertEmails);

		for ($i = 0; $n = count($alertEmails), $i < $n; $i++)
		{
			if ($alertEmails[$i] != '')
			{
				$mailer->clearAllRecipients();
				$mailer->clearAttachments();

				try
				{
					$mailer->sendMail($fromEmail, $fromName, $alertEmails[$i], $adminSubject, $adminBody, 1);
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}
			}
		}
	}

	/**
	 *
	 * Function to get admin email body
	 *
	 * @param   order object $row
	 *
	 * @return string
	 */
	public static function getAdminEmailBody($row)
	{
		$adminEmailBody = self::getMessageValue('admin_notification_email', $row->language);
		// Order information
		$replaces                       = [];
		$replaces['customer_name']      = $row->firstname . ' ' . $row->lastname;
		$replaces['customergroup_name'] = self::getOrderCustomergroupName($row);
		$replaces['order_id']           = $row->id;
		$replaces['order_number']       = $row->order_number;
		$replaces['order_status']       = self::getOrderStatusName($row->order_status_id, $row->language);
		$replaces['date_added']         = HTMLHelper::date($row->created_date, self::getConfigValue('date_format', 'm-d-Y'));
		$replaces['date_modified']      = HTMLHelper::date($row->modified_date, self::getConfigValue('date_format', 'm-d-Y'));
		$replaces['store_owner']        = self::getConfigValue('store_owner');
		$replaces['store_name']         = self::getConfigValue('store_name');
		$replaces['store_address']      = str_replace("\r\n", "<br />", self::getConfigValue('address'));
		$replaces['store_telephone']    = self::getConfigValue('telephone');
		$replaces['store_fax']          = self::getConfigValue('fax');
		$replaces['store_email']        = self::getConfigValue('email');
		$replaces['store_url']          = Uri::root();

		if ($row->payment_method == 'os_creditcard')
		{
			$cardNumber = Factory::getApplication()->input->getString('card_number', '');

			if ($cardNumber)
			{
				$last4Digits                = substr($cardNumber, strlen($cardNumber) - 4);
				$replaces['payment_method'] = Text::_($row->payment_method_title) . ' (' . Text::_(
						'ESHOP_LAST_4DIGITS_CREDIT_CARD_NUMBER'
					) . ': ' . $last4Digits . ')';
			}
			else
			{
				$replaces['payment_method'] = Text::_($row->payment_method_title);
			}
		}
		else
		{
			$replaces['payment_method'] = Text::_($row->payment_method_title);
		}

		$replaces['shipping_method']    = Text::_($row->shipping_method_title);
		$replaces['customer_email']     = $row->email;
		$replaces['customer_telephone'] = $row->telephone;
		// Comment
		$replaces['comment'] = $row->comment;
		// Delivery Date
		$replaces['delivery_date'] = HTMLHelper::date($row->delivery_date, self::getConfigValue('date_format', 'm-d-Y'), null);
		// Payment information
		$replaces['payment_address'] = self::getPaymentAddress($row);
		//Payment custom fields here
		$excludedFields = [
			'firstname',
			'lastname',
			'email',
			'telephone',
			'fax',
			'company',
			'company_id',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'country_id',
			'zone_id',
		];
		$form           = new EshopRADForm(self::getFormFields('B'));
		$fields         = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'payment_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['payment_' . $fieldName] = $fieldValue;
			}
		}

		// Shipping information
		$replaces['shipping_address'] = self::getShippingAddress($row);
		//Shipping custom fields here
		$form   = new EshopRADForm(self::getFormFields('S'));
		$fields = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'shipping_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['shipping_' . $fieldName] = $fieldValue;
			}
		}

		// Products list
		$orderProducts             = self::getOrderProducts($row->id);
		$orderTotals               = self::getOrderTotals($row->id);
		$text                      = EShopHtmlHelper::loadCommonLayout(
			'emailtemplates/admin.php',
			['orderProducts' => $orderProducts, 'orderTotals' => $orderTotals, 'row' => $row]
		);
		$replaces['products_list'] = $text;

		foreach ($replaces as $key => $value)
		{
			$key            = strtoupper($key);
			$adminEmailBody = str_replace("[$key]", $value, $adminEmailBody);
		}

		return $adminEmailBody;
	}

	/**
	 *
	 * Function to get admin cancel email body
	 *
	 * @param   order object $row
	 *
	 * @return string
	 */
	public static function getAdminNotifyEmailBody($row, $notifyType)
	{
		$adminNotifyEmailBody = self::getMessageValue('admin_' . $notifyType . '_notification_email', $row->language);
		// Order information
		$replaces                       = [];
		$replaces['customer_name']      = $row->firstname . ' ' . $row->lastname;
		$replaces['customergroup_name'] = self::getOrderCustomergroupName($row);
		$replaces['order_id']           = $row->id;
		$replaces['order_number']       = $row->order_number;
		$replaces['order_status']       = self::getOrderStatusName($row->order_status_id, $row->language);
		$replaces['date_added']         = HTMLHelper::date($row->created_date, self::getConfigValue('date_format', 'm-d-Y'));
		$replaces['date_modified']      = HTMLHelper::date($row->modified_date, self::getConfigValue('date_format', 'm-d-Y'));
		$replaces['store_owner']        = self::getConfigValue('store_owner');
		$replaces['store_name']         = self::getConfigValue('store_name');
		$replaces['store_address']      = str_replace("\r\n", "<br />", self::getConfigValue('address'));
		$replaces['store_telephone']    = self::getConfigValue('telephone');
		$replaces['store_fax']          = self::getConfigValue('fax');
		$replaces['store_email']        = self::getConfigValue('email');
		$replaces['store_url']          = Uri::root();

		if ($row->payment_method == 'os_creditcard')
		{
			$cardNumber = Factory::getApplication()->input->getString('card_number', '');

			if ($cardNumber)
			{
				$last4Digits                = substr($cardNumber, strlen($cardNumber) - 4);
				$replaces['payment_method'] = Text::_($row->payment_method_title) . ' (' . Text::_(
						'ESHOP_LAST_4DIGITS_CREDIT_CARD_NUMBER'
					) . ': ' . $last4Digits . ')';
			}
			else
			{
				$replaces['payment_method'] = Text::_($row->payment_method_title);
			}
		}
		else
		{
			$replaces['payment_method'] = Text::_($row->payment_method_title);
		}

		$replaces['shipping_method']    = Text::_($row->shipping_method_title);
		$replaces['customer_email']     = $row->email;
		$replaces['customer_telephone'] = $row->telephone;
		// Comment
		$replaces['comment'] = $row->comment;
		// Delivery Date
		$replaces['delivery_date'] = HTMLHelper::date($row->delivery_date, self::getConfigValue('date_format', 'm-d-Y'), null);
		// Payment information
		$replaces['payment_address'] = self::getPaymentAddress($row);
		//Payment custom fields here
		$excludedFields = [
			'firstname',
			'lastname',
			'email',
			'telephone',
			'fax',
			'company',
			'company_id',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'country_id',
			'zone_id',
		];
		$form           = new EshopRADForm(self::getFormFields('B'));
		$fields         = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'payment_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['payment_' . $fieldName] = $fieldValue;
			}
		}

		// Shipping information
		$replaces['shipping_address'] = self::getShippingAddress($row);
		//Shipping custom fields here
		$form   = new EshopRADForm(self::getFormFields('S'));
		$fields = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'shipping_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['shipping_' . $fieldName] = $fieldValue;
			}
		}

		// Products list
		$orderProducts             = self::getOrderProducts($row->id);
		$orderTotals               = self::getOrderTotals($row->id);
		$text                      = EShopHtmlHelper::loadCommonLayout(
			'emailtemplates/admin.php',
			['orderProducts' => $orderProducts, 'orderTotals' => $orderTotals, 'row' => $row]
		);
		$replaces['products_list'] = $text;

		foreach ($replaces as $key => $value)
		{
			$key                  = strtoupper($key);
			$adminNotifyEmailBody = str_replace("[$key]", $value, $adminNotifyEmailBody);
		}

		return $adminNotifyEmailBody;
	}

	/**
	 *
	 * Function to get manufacturer email body
	 *
	 * @param   array  $manufacturer
	 *
	 * @return string
	 */
	public static function getManufacturerEmailBody($manufacturer, $row)
	{
		$manufacturerEmailBody          = self::getMessageValue('manufacturer_notification_email', $row->language);
		$replaces                       = [];
		$replaces['manufacturer_name']  = $manufacturer[0]->manufacturer_name;
		$replaces['store_owner']        = self::getConfigValue('store_owner');
		$replaces['store_name']         = self::getConfigValue('store_name');
		$replaces['store_address']      = str_replace("\r\n", "<br />", self::getConfigValue('address'));
		$replaces['store_telephone']    = self::getConfigValue('telephone');
		$replaces['store_fax']          = self::getConfigValue('fax');
		$replaces['store_email']        = self::getConfigValue('email');
		$replaces['store_url']          = Uri::root();
		$replaces['order_id']           = $row->id;
		$replaces['order_number']       = $row->order_number;
		$replaces['order_status']       = self::getOrderStatusName($row->order_status_id, $row->language);
		$replaces['date_added']         = HTMLHelper::date($row->created_date, self::getConfigValue('date_format', 'm-d-Y'));
		$replaces['date_modified']      = HTMLHelper::date($row->modified_date, self::getConfigValue('date_format', 'm-d-Y'));
		$replaces['payment_method']     = Text::_($row->payment_method_title);
		$replaces['shipping_method']    = Text::_($row->shipping_method_title);
		$replaces['customer_name']     = $row->firstname . ' ' . $row->lastname;
		$replaces['customer_email']     = $row->email;
		$replaces['customer_telephone'] = $row->telephone;

		// Products list
		$text                      = EShopHtmlHelper::loadCommonLayout('emailtemplates/manufacturer.php', ['manufacturer' => $manufacturer]);
		$replaces['products_list'] = $text;
		// Comment
		$replaces['comment'] = $row->comment;
		// Delivery Date
		$replaces['delivery_date'] = HTMLHelper::date($row->delivery_date, self::getConfigValue('date_format', 'm-d-Y'), null);
		// Payment information
		$replaces['payment_address'] = self::getPaymentAddress($row);
		//Payment custom fields here
		$excludedFields = [
			'firstname',
			'lastname',
			'email',
			'telephone',
			'fax',
			'company',
			'company_id',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'country_id',
			'zone_id',
		];
		$form           = new EshopRADForm(self::getFormFields('B'));
		$fields         = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'payment_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['payment_' . $fieldName] = $fieldValue;
			}
		}

		// Shipping information
		$replaces['shipping_address'] = self::getShippingAddress($row);
		//Shipping custom fields here
		$form   = new EshopRADForm(self::getFormFields('S'));
		$fields = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;


			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'shipping_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['shipping_' . $fieldName] = $fieldValue;
			}
		}

		foreach ($replaces as $key => $value)
		{
			$key                   = strtoupper($key);
			$manufacturerEmailBody = str_replace("[$key]", $value, $manufacturerEmailBody);
		}

		return $manufacturerEmailBody;
	}

	/**
	 *
	 * Function to get customer email body
	 *
	 * @param   order object $row
	 *
	 * @return string
	 */
	public static function getCustomerEmailBody($row)
	{
		$hasDownload = false;
		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__eshop_orderdownloads')
			->where('order_id = ' . intval($row->id));
		$db->setQuery($query);

		if ($db->loadResult())
		{
			$hasDownload = true;
		}

		if ($row->customer_id)
		{
			if (strpos($row->payment_method, 'os_offline') !== false)
			{
				$offlinePaymentTitle = str_replace('os_', '', $row->payment_method);

				if ($hasDownload)
				{
					$message = self::getMessageValue($offlinePaymentTitle . '_payment_customer_notification_email_with_download', $row->language);

					if (!$message)
					{
						$message = self::getMessageValue('offline_payment_customer_notification_email_with_download', $row->language);
					}
				}
				else
				{
					$message = self::getMessageValue($offlinePaymentTitle . '_payment_customer_notification_email', $row->language);

					if (!$message)
					{
						$message = self::getMessageValue('offline_payment_customer_notification_email', $row->language);
					}
				}

				$customerEmailBody = $message;
			}
			else
			{
				if ($hasDownload)
				{
					$customerEmailBody = self::getMessageValue('customer_notification_email_with_download', $row->language);
				}
				else
				{
					$customerEmailBody = self::getMessageValue('customer_notification_email', $row->language);
				}
			}
		}
		else
		{
			if (strpos($row->payment_method, 'os_offline') !== false)
			{
				$offlinePaymentTitle = str_replace('os_', '', $row->payment_method);

				if ($hasDownload)
				{
					$message = self::getMessageValue($offlinePaymentTitle . '_payment_guest_notification_email_with_download', $row->language);

					if (!$message)
					{
						$message = self::getMessageValue('offline_payment_guest_notification_email_with_download', $row->language);
					}
				}
				else
				{
					$message = self::getMessageValue($offlinePaymentTitle . '_payment_guest_notification_email', $row->language);

					if (!$message)
					{
						$message = self::getMessageValue('offline_payment_guest_notification_email', $row->language);
					}
				}

				$customerEmailBody = $message;
			}
			else
			{
				if ($hasDownload)
				{
					$customerEmailBody = self::getMessageValue('guest_notification_email_with_download', $row->language);
				}
				else
				{
					$customerEmailBody = self::getMessageValue('guest_notification_email', $row->language);
				}
			}
		}

		// Order information
		$replaces                       = [];
		$replaces['customer_name']      = $row->firstname . ' ' . $row->lastname;
		$replaces['customergroup_name'] = self::getOrderCustomergroupName($row);
		$replaces['store_owner']        = self::getConfigValue('store_owner');
		$replaces['store_name']         = self::getConfigValue('store_name');
		$replaces['store_address']      = str_replace("\r\n", "<br />", self::getConfigValue('address'));
		$replaces['store_telephone']    = self::getConfigValue('telephone');
		$replaces['store_fax']          = self::getConfigValue('fax');
		$replaces['store_email']        = self::getConfigValue('email');
		$replaces['store_url']          = Uri::root();
		$replaces['order_link']         = Route::_(Uri::root() . 'index.php?option=com_eshop&view=customer&layout=order&order_id=' . $row->id);
		$replaces['download_link']      = Route::_(Uri::root() . 'index.php?option=com_eshop&view=customer&layout=downloads');
		$replaces['order_id']           = $row->id;
		$replaces['order_number']       = $row->order_number;
		$replaces['order_status']       = self::getOrderStatusName($row->order_status_id, $row->language);
		$replaces['date_added']         = HTMLHelper::date($row->created_date, self::getConfigValue('date_format', 'm-d-Y'));
		$replaces['date_modified']      = HTMLHelper::date($row->modified_date, self::getConfigValue('date_format', 'm-d-Y'));
		$replaces['payment_method']     = Text::_($row->payment_method_title);
		$replaces['shipping_method']    = Text::_($row->shipping_method_title);
		$replaces['customer_email']     = $row->email;
		$replaces['customer_telephone'] = $row->telephone;
		// Comment
		$replaces['comment'] = $row->comment;
		// Delivery Date
		$replaces['delivery_date'] = HTMLHelper::date($row->delivery_date, self::getConfigValue('date_format', 'm-d-Y'), null);
		// Payment information
		$replaces['payment_address'] = self::getPaymentAddress($row);
		//Payment custom fields here
		$excludedFields = [
			'firstname',
			'lastname',
			'email',
			'telephone',
			'fax',
			'company',
			'company_id',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'country_id',
			'zone_id',
		];
		$form           = new EshopRADForm(self::getFormFields('B'));
		$fields         = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'payment_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['payment_' . $fieldName] = $fieldValue;
			}
		}

		// Shipping information
		$replaces['shipping_address'] = self::getShippingAddress($row);
		//Shipping custom fields here
		$form   = new EshopRADForm(self::getFormFields('S'));
		$fields = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'shipping_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['shipping_' . $fieldName] = $fieldValue;
			}
		}

		// Products list
		$orderProducts = self::getOrderProducts($row->id);
		$orderTotals   = self::getOrderTotals($row->id);

		if ($hasDownload && $row->order_status_id == self::getConfigValue('complete_status_id'))
		{
			$showDownloadLink = true;
		}
		else
		{
			$showDownloadLink = false;
		}

		$text                      = EShopHtmlHelper::loadCommonLayout(
			'emailtemplates/customer.php',
			['orderProducts' => $orderProducts, 'orderTotals' => $orderTotals, 'row' => $row, 'showDownloadLink' => $showDownloadLink]
		);
		$replaces['products_list'] = $text;

		foreach ($replaces as $key => $value)
		{
			$key               = strtoupper($key);
			$customerEmailBody = str_replace("[$key]", $value, $customerEmailBody);
		}

		return $customerEmailBody;
	}

	/**
	 * Function to get notification email body
	 *
	 * @param   order object $row
	 *
	 * @return string
	 */
	public static function getNotificationEmailBody($row, $orderStatusFrom, $orderStatusTo)
	{
		if ($row->customer_id)
		{
			$notificationEmailBody = self::getMessageValue('order_status_change_customer', $row->language);
		}
		else
		{
			$notificationEmailBody = self::getMessageValue('order_status_change_guest', $row->language);
		}

		// Order information
		$replaces                       = [];
		$replaces['customer_name']      = $row->firstname . ' ' . $row->lastname;
		$replaces['customergroup_name'] = self::getOrderCustomergroupName($row);
		$replaces['order_status_from']  = self::getOrderStatusName($orderStatusFrom, $row->language);
		$replaces['order_status_to']    = self::getOrderStatusName($orderStatusTo, $row->language);
		$replaces['store_name']         = self::getConfigValue('store_name');
		$replaces['order_link']         = Route::_(Uri::root() . 'index.php?option=com_eshop&view=customer&layout=order&order_id=' . $row->id);
		$replaces['download_link']      = Route::_(Uri::root() . 'index.php?option=com_eshop&view=customer&layout=downloads');
		$replaces['order_id']           = $row->id;
		$replaces['order_number']       = $row->order_number;
		$replaces['date_added']         = HTMLHelper::date($row->created_date, self::getConfigValue('date_format', 'm-d-Y'));
		$replaces['date_modified']      = HTMLHelper::date($row->modified_date, self::getConfigValue('date_format', 'm-d-Y'));
		$replaces['payment_method']     = Text::_($row->payment_method_title);
		$replaces['shipping_method']    = Text::_($row->shipping_method_title);
		$replaces['customer_email']     = $row->email;
		$replaces['customer_telephone'] = $row->telephone;
		// Comment
		$replaces['comment'] = $row->comment;
		// Delivery Date
		$replaces['delivery_date'] = HTMLHelper::date($row->delivery_date, self::getConfigValue('date_format', 'm-d-Y'), null);
		// Payment information
		$replaces['payment_address'] = self::getPaymentAddress($row);
		//Payment custom fields here
		$excludedFields = [
			'firstname',
			'lastname',
			'email',
			'telephone',
			'fax',
			'company',
			'company_id',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'country_id',
			'zone_id',
		];
		$form           = new EshopRADForm(self::getFormFields('B'));
		$fields         = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'payment_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['payment_' . $fieldName] = $fieldValue;
			}
		}

		// Shipping information
		$replaces['shipping_address'] = self::getShippingAddress($row);
		//Shipping custom fields here
		$form   = new EshopRADForm(self::getFormFields('S'));
		$fields = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'shipping_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['shipping_' . $fieldName] = $fieldValue;
			}
		}

		// Products list
		$orderProducts = self::getOrderProducts($row->id);
		$orderTotals   = self::getOrderTotals($row->id);
		$hasDownload   = false;

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__eshop_orderdownloads')
			->where('order_id = ' . intval($row->id));
		$db->setQuery($query);

		if ($db->loadResult())
		{
			$hasDownload = true;
		}

		if ($hasDownload && $row->order_status_id == self::getConfigValue('complete_status_id'))
		{
			$showDownloadLink = true;
		}
		else
		{
			$showDownloadLink = false;
		}

		$text                      = EShopHtmlHelper::loadCommonLayout(
			'emailtemplates/customer.php',
			['orderProducts' => $orderProducts, 'orderTotals' => $orderTotals, 'row' => $row, 'showDownloadLink' => $showDownloadLink]
		);
		$replaces['products_list'] = $text;

		foreach ($replaces as $key => $value)
		{
			$key                   = strtoupper($key);
			$notificationEmailBody = str_replace("[$key]", $value, $notificationEmailBody);
		}

		return $notificationEmailBody;
	}

	/**
	 *
	 * Function to get shipping notification email body
	 *
	 * @param   order object $row
	 *
	 * @return string
	 */
	public static function getShippingNotificationEmailBody($row)
	{
		$shippingNotificationEmailBody = self::getMessageValue('shipping_notification_email', $row->language);
		// Order information
		$replaces                             = [];
		$replaces['customer_name']            = $row->firstname . ' ' . $row->lastname;
		$replaces['customergroup_name']       = self::getOrderCustomergroupName($row);
		$replaces['order_id']                 = $row->id;
		$replaces['order_number']             = $row->order_number;
		$replaces['shipping_tracking_number'] = $row->shipping_tracking_number;
		$replaces['shipping_tracking_url']    = $row->shipping_tracking_url;
		$replaces['comment']                  = $row->comment;
		// Shipping information
		$replaces['shipping_address'] = self::getShippingAddress($row);
		//Shipping custom fields here
		$excludedFields = [
			'firstname',
			'lastname',
			'email',
			'telephone',
			'fax',
			'company',
			'company_id',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'country_id',
			'zone_id',
		];
		$form           = new EshopRADForm(self::getFormFields('S'));
		$fields         = $form->getFields();

		foreach ($fields as $field)
		{
			$fieldName = $field->name;

			if (!in_array($fieldName, $excludedFields))
			{
				$fieldValue = $row->{'shipping_' . $fieldName};

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$replaces['shipping_' . $fieldName] = $fieldValue;
			}
		}

		foreach ($replaces as $key => $value)
		{
			$key                           = strtoupper($key);
			$shippingNotificationEmailBody = str_replace("[$key]", $value, $shippingNotificationEmailBody);
		}

		return $shippingNotificationEmailBody;
	}

	/**
	 * Function to get ask question email body
	 *
	 * @param   stdClass object product
	 *
	 * @return string
	 */
	public static function getAskQuestionEmailBody($data, $product)
	{
		$askQuestionEmailBody = self::getMessageValue('ask_question_notification_email');

		// Products list
		$text                 = EShopHtmlHelper::loadCommonLayout('emailtemplates/askquestion.php', ['data' => $data, 'product' => $product]);
		$askQuestionEmailBody = str_replace('[PRODUCTS_LIST]', $text, $askQuestionEmailBody);
		$askQuestionEmailBody = str_replace('[PRODUCT_NAME]', $product->product_name, $askQuestionEmailBody);
		$askQuestionEmailBody = str_replace(
			'[PRODUCT_LINK]',
			Route::_(self::getSiteUrl() . EShopRoute::getProductRoute($product->id, EShopHelper::getProductCategory($product->id))),
			$askQuestionEmailBody
		);

		return $askQuestionEmailBody;
	}
	
	/**
	 * Function to get price match email body
	 *
	 * @param   stdClass object product
	 *
	 * @return string
	 */
	public static function getPriceMatchEmailBody($data)
	{
		$priceMatchEmailBody = self::getMessageValue('price_match_notification_email');
		$priceMatchEmailBody = str_replace('[CUSTOMER_NAME]', $data['name'], $priceMatchEmailBody);
		$priceMatchEmailBody = str_replace('[CUSTOMER_EMAIL]', $data['email'], $priceMatchEmailBody);
		$priceMatchEmailBody = str_replace('[PRODUCT_SKU]', $data['product_sku'], $priceMatchEmailBody);
		$priceMatchEmailBody = str_replace('[MATCH_PRICE_URL]', $data['price_to_match_url'], $priceMatchEmailBody);
		$priceMatchEmailBody = str_replace('[PRODUCT_PRICE]', $data['product_price'], $priceMatchEmailBody);
		$priceMatchEmailBody = str_replace('[MATCH_PRICE]', $data['price_to_match'], $priceMatchEmailBody);
	
		return $priceMatchEmailBody;
	}

	/**
	 *
	 * function to get review notification email body
	 *
	 * @param   array  $data
	 *
	 * @return string
	 */
	public static function getReviewNotificationEmailBody($data)
	{
		$reviewEmailBody = self::getMessageValue('review_notification_email');

		return $reviewEmailBody;
	}

	/**
	 *
	 * Function to load jQuery chosen plugin
	 */
	public static function chosen()
	{
		static $chosenLoaded;

		if (!$chosenLoaded)
		{
			$document = Factory::getApplication()->getDocument();

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				HTMLHelper::_('formbehavior.chosen', '.chosen');
			}
			else
			{
				$rootUri = Uri::root(true);
				$document->addScript($rootUri . '/media/com_eshop/assets/chosen/chosen.jquery.js');
				$document->addStyleSheet($rootUri . '/media/com_eshop/assets/chosen/chosen.css');
			}

			$document->addScriptDeclaration(
				"jQuery(document).ready(function(){
	                    jQuery(\".chosen\").chosen();
	                });"
			);

			$chosenLoaded = true;
		}
	}

	/**
	 *
	 * Function to load bootstrap css
	 */
	public static function loadBootstrapCss()
	{
		$document = Factory::getApplication()->getDocument();
		$rootUri  = Uri::root(true);

		if (self::isJoomla4())
		{
			HTMLHelper::_('bootstrap.loadCss');
		}
		else
		{
			$document->addStyleSheet($rootUri . '/media/com_eshop/assets/bootstrap/css/bootstrap.min.css');
		}
	}

	/**
	 *
	 * Function to load bootstrap javascript
	 */
	public static function loadBootstrapJs($loadJs = true)
	{
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			HTMLHelper::_('bootstrap.framework');
		}
		else
		{
			$document = Factory::getApplication()->getDocument();
			$rootUri  = Uri::root(true);
			$document->addScript($rootUri . '/media/com_eshop/assets/bootstrap/js/jquery.min.js');
			$document->addScript($rootUri . '/media/com_eshop/assets/bootstrap/js/jquery-noconflict.js');
			$document->addScript($rootUri . '/media/com_eshop/assets/bootstrap/js/bootstrap.min.js');
		}
	}

	/**
	 *
	 * Function to load scripts for share product
	 */
	public static function loadShareScripts($product)
	{
		/* @var JDocumentHtml $document */
		$document = Factory::getApplication()->getDocument();

		//Add script for Twitter
		if (self::getConfigValue('show_twitter_button'))
		{
			$script = '!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");';
			$document->addScriptDeclaration($script);
		}

		//Add script for PinIt
		if (self::getConfigValue('show_pinit_button'))
		{
			$script = '(function() {
				window.PinIt = window.PinIt || { loaded:false };
				if (window.PinIt.loaded) return;
				window.PinIt.loaded = true;
				function async_load(){
					var s = document.createElement("script");
					s.type = "text/javascript";
					s.async = true;
					s.src = "https://assets.pinterest.com/js/pinit.js";
					var x = document.getElementsByTagName("script")[0];
					x.parentNode.insertBefore(s, x);
				}
				if (window.attachEvent)
					window.attachEvent("onload", async_load);
				else
					window.addEventListener("load", async_load, false);
			})();';
			$document->addScriptDeclaration($script);
		}

		// Add script for LinkedIn
		if (self::getConfigValue('show_linkedin_button'))
		{
			$document->addScript('//platform.linkedin.com/in.js');
		}

		// Add script for Google
		if (self::getConfigValue('show_google_button'))
		{
			$script = '(function() {
				var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;
				po.src = "https://apis.google.com/js/plusone.js";
				var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);
			})();';

			$document->addScriptDeclaration($script);
		}

		// Add script for Facebook
		if (self::getConfigValue('show_facebook_button') || self::getConfigValue('show_facebook_comment'))
		{
			$script = '(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/' . self::getConfigValue(
					'button_language',
					'en_US'
				) . '/all.js#xfbml=1&appId=' . self::getConfigValue('app_id', '372958799407679') . '";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, "script","facebook-jssdk"));';
			$document->addScriptDeclaration($script);

			$uri  = Uri::getInstance();
			$conf = Factory::getConfig();
			$document->addCustomTag('<meta property="og:title" content="' . $product->product_name . '"/>');
			$document->addCustomTag(
				'<meta property="og:image" content="' . self::getSiteUrl() . 'media/com_eshop/products/' . $product->product_image . '"/>'
			);
			$document->addCustomTag('<meta property="og:url" content="' . $uri->toString() . '"/>');
			$document->addCustomTag('<meta property="og:description" content="' . $product->product_name . '"/>');
			$document->addCustomTag('<meta property="og:site_name" content="' . $conf->get('sitename') . '"/>');
		}
	}

	/**
	 *
	 * Function to get Itemid of Eshop component
	 *
	 * @return int
	 */
	public static function getItemid()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where('link LIKE "%index.php?option=com_eshop%"')
			->where('published = 1')
			->where('`access` IN ("' . implode(',', Factory::getUser()->getAuthorisedViewLevels()) . '")')
			->order('access');
		$db->setQuery($query);
		$itemId = $db->loadResult();

		if (!$itemId)
		{
			$Itemid = Factory::getApplication()->input->getInt('Itemid');

			if ($Itemid == 1)
			{
				$itemId = 999999;
			}
			else
			{
				$itemId = $Itemid;
			}
		}

		return $itemId;
	}

	/**
	 *
	 * Function to get a list of the actions that can be performed.
	 * @return CMSObject
	 * @since 1.6
	 */
	public static function getActions()
	{
		$user      = Factory::getUser();
		$result    = new CMSObject();
		$actions   = ['core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'];
		$assetName = 'com_eshop';

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 *
	 * Function to display copy right information
	 */
	public static function displayCopyRight()
	{
		echo '<div class="copyright"><a href="https://joomdonation.com/joomla-extensions/eshop-joomla-shopping-cart.html" target="_blank"><strong>EShop Shopping Cart</strong></a> version ' . self::getInstalledVersion(
			) . ', Copyright (C) 2012-2024 <a href="https://joomdonation.com" target="_blank"><strong>Ossolution Team</strong></a></div>';
	}

	/**
	 *
	 * Function to add dropdown menu
	 *
	 * @param   string  $vName
	 */
	public static function renderSubmenu($vName = 'dashboard')
	{
		$user  = Factory::getUser();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_menus')
			->where('published = 1')
			->where('menu_parent_id = 0')
			->order('ordering');
		$db->setQuery($query);
		$menus = $db->loadObjectList();

		if (self::isJoomla4())
		{
			$dataToggle = 'data-bs-toggle="dropdown"';
		}
		else
		{
			$dataToggle = 'data-toggle="dropdown"';
		}

		$html = '';
		$html .= '<ul class="nav nav-tabs nav-hover">';

		$lName = Factory::getApplication()->input->getCmd('layout');

		for ($i = 0; $n = count($menus), $i < $n; $i++)
		{
			$menu          = $menus[$i];
			$showCondition = true;

			if ($menu->menu_name == 'ESHOP_PLUGINS')
			{
				if (!$user->authorise('eshop.payments', 'com_eshop') && !$user->authorise('eshop.shippings', 'com_eshop') && !$user->authorise(
						'eshop.themes',
						'com_eshop'
					))
				{
					$showCondition = false;
				}
			}
			elseif ($menu->menu_name == 'ESHOP_SALES')
			{
				if (!$user->authorise('eshop.orders', 'com_eshop') && !$user->authorise('eshop.customers', 'com_eshop') && !$user->authorise(
						'eshop.customergroups',
						'com_eshop'
					) && !$user->authorise('eshop.coupons', 'com_eshop') && !$user->authorise('eshop.vouchers', 'com_eshop'))
				{
					$showCondition = false;
				}
			}
			elseif ($menu->menu_name == 'ESHOP_REPORTS')
			{
				if (!$user->authorise('eshop.reports', 'com_eshop'))
				{
					$showCondition = false;
				}
			}

			if ($showCondition)
			{
				$query->clear()
					->select('*')
					->from('#__eshop_menus')
					->where('published = 1')
					->where('menu_parent_id = ' . intval($menu->id))
					->order('ordering');
				$db->setQuery($query);
				$subMenus = $db->loadObjectList();

				if (!count($subMenus))
				{
					$class      = '';
					$extraClass = '';
					if ($menu->menu_view == $vName)
					{
						$class      = ' class="active"';
						$extraClass = 'active';
					}

					$html .= '<li' . $class . '><a class="nav-link dropdown-item ' . $extraClass . '" href="index.php?option=com_eshop&view=' . $menu->menu_view . '"><span class="icon-' . $menu->menu_class . '"></span> ' . Text::_(
							$menu->menu_name
						) . '</a></li>';
				}
				else
				{
					$class = ' class="dropdown"';

					for ($j = 0; $m = count($subMenus), $j < $m; $j++)
					{
						$subMenu = $subMenus[$j];

						if ((!$subMenu->menu_layout && $vName == $subMenu->menu_view) || ($lName != '' && $lName == $subMenu->menu_layout))
						{
							$class = ' class="dropdown active"';
							break;
						}
					}

					$html .= '<li' . $class . '>';
					$html .= '<a id="drop_' . $menu->id . '" href="#" ' . $dataToggle . ' role="button" class="dropdown-toggle nav-link dropdown-toggle"><span class="icon-' . $menu->menu_class . '"></span> ' .
						Text::_($menu->menu_name) . ' <b class="caret"></b></a>';
					$html .= '<ul aria-labelledby="drop_' . $menu->id . '" role="menu" class="dropdown-menu" id="menu_' . $menu->id . '">';

					for ($j = 0; $m = count($subMenus), $j < $m; $j++)
					{
						$subMenu          = $subMenus[$j];
						$showSubCondition = true;

						if ($subMenu->menu_view == 'reviews' && !$user->authorise('eshop.reviews', 'com_eshop'))
						{
							$showSubCondition = false;
						}
						elseif ($subMenu->menu_view == 'taxclasses' && !$user->authorise('eshop.taxclasses', 'com_eshop'))
						{
							$showSubCondition = false;
						}
						elseif ($subMenu->menu_view == 'taxrates' && !$user->authorise('eshop.taxrates', 'com_eshop'))
						{
							$showSubCondition = false;
						}
						elseif ($subMenu->menu_view == 'configuration' && !$user->authorise('eshop.configuration', 'com_eshop'))
						{
							$showSubCondition = false;
						}
						elseif ($subMenu->menu_view == 'tools' && !$user->authorise('eshop.tools', 'com_eshop'))
						{
							$showSubCondition = false;
						}

						if ($showSubCondition)
						{
							$layoutLink = '';

							if ($subMenu->menu_layout)
							{
								$layoutLink = '&layout=' . $subMenu->menu_layout;
							}

							$class      = '';
							$extraClass = '';

							if ((!$subMenu->menu_layout && $vName == $subMenu->menu_view) || ($lName != '' && $lName == $subMenu->menu_layout))
							{
								$class      = ' class="active"';
								$extraClass = 'active';
							}

							$html .= '<li' . $class . '><a class="nav-link dropdown-item ' . $extraClass . '" href="index.php?option=com_eshop&view=' .
								$subMenu->menu_view . $layoutLink . '" tabindex="-1"><span class="icon-' . $subMenu->menu_class . '"></span> ' . Text::_(
									$subMenu->menu_name
								) . '</a></li>';
						}
					}

					$html .= '</ul>';
					$html .= '</li>';
				}
			}
		}

		$html .= '</ul>';

		echo $html;
	}

	/**
	 *
	 * Function to get value for a message
	 *
	 * @param   string  $messageName
	 *
	 * @return string
	 */
	public static function getMessageValue($messageName, $langCode = '')
	{
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$language = Factory::getLanguage();

		if (!$langCode)
		{
			$langCode = $language->getTag();
		}

		if (!$langCode)
		{
			$langCode = 'en-GB';
		}

		$language->load('com_eshop', JPATH_ROOT, $langCode);

		$query->select('a.message_value')
			->from('#__eshop_messagedetails AS a')
			->innerJoin('#__eshop_messages AS b ON a.message_id = b.id')
			->where('a.language = ' . $db->quote($langCode))
			->where('b.message_name = ' . $db->quote($messageName));
		$db->setQuery($query);
		$messageValue = $db->loadResult();

		if (!$messageValue)
		{
			$query->clear()
				->select('a.message_value')
				->from('#__eshop_messagedetails AS a')
				->innerJoin('#__eshop_messages AS b ON a.message_id = b.id')
				->where('a.language = "en-GB"')
				->where('b.message_name = ' . $db->quote($messageName));
			$db->setQuery($query);
			$messageValue = $db->loadResult();
		}

		return $messageValue;
	}

	/**
	 *
	 * Function to get information for a specific order
	 *
	 * @param   int  $orderId
	 *
	 * @return stdClass order Object
	 */
	public static function getOrder($orderId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_orders')
			->where('id = ' . (int) $orderId);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 *
	 * Function to get products for a specific order
	 *
	 * @param   int  $orderId
	 *
	 * @return array
	 */
	public static function getOrderProducts($orderId)
	{
		$order    = self::getOrder($orderId);
		$currency = EShopCurrency::getInstance();
		$tax      = new EShopTax(self::getConfig());
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$query->select('a.*, b.product_image, c.product_custom_message')
			->from('#__eshop_orderproducts AS a')
			->innerJoin('#__eshop_products AS b ON a.product_id = b.id')
			->innerJoin('#__eshop_productdetails AS c ON a.product_id = c.product_id')
			->where('order_id = ' . intval($orderId))
			->where('c.language = ' . $db->quote(Factory::getLanguage()->getTag()));
		$db->setQuery($query);
		$orderProducts = $db->loadObjectList();

		for ($i = 0; $n = count($orderProducts), $i < $n; $i++)
		{
			$orderProducts[$i]->orderOptions = self::getOrderOptions($orderProducts[$i]->id);

			if (self::getConfigValue('include_tax_anywhere', '0'))
			{
				$orderProducts[$i]->price       = $currency->format(
					$orderProducts[$i]->price + $orderProducts[$i]->tax,
					$order->currency_code,
					$order->currency_exchanged_value
				);
				$orderProducts[$i]->total_price = $currency->format(
					$orderProducts[$i]->total_price + $orderProducts[$i]->quantity * $orderProducts[$i]->tax,
					$order->currency_code,
					$order->currency_exchanged_value
				);
			}
			else
			{
				$orderProducts[$i]->price       = $currency->format(
					$orderProducts[$i]->price,
					$order->currency_code,
					$order->currency_exchanged_value
				);
				$orderProducts[$i]->total_price = $currency->format(
					$orderProducts[$i]->total_price,
					$order->currency_code,
					$order->currency_exchanged_value
				);
			}

			// Image
			$imageSizeFunction = self::getConfigValue('cart_image_size_function', 'resizeImage');

			if ($orderProducts[$i]->product_image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $orderProducts[$i]->product_image))
			{
				if (self::getConfigValue('product_use_image_watermarks'))
				{
					$watermarkImage = self::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/products/' . $orderProducts[$i]->product_image);
					$productImage   = $watermarkImage;
				}
				else
				{
					$productImage = $orderProducts[$i]->product_image;
				}

				$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
					[
						$productImage,
						JPATH_ROOT . '/media/com_eshop/products/',
						self::getConfigValue('image_cart_width'),
						self::getConfigValue('image_cart_height'),
					]);
			}
			else
			{
				$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
					[
						self::getConfigValue('default_product_image', 'no-image.png'),
						JPATH_ROOT . '/media/com_eshop/products/',
						self::getConfigValue('image_cart_width'),
						self::getConfigValue('image_cart_height'),
					]);
			}

			if ($imageSizeFunction == 'notResizeImage')
			{
				$image = Uri::root() . '/media/com_eshop/products/' . $image;
			}
			else
			{
				$image = Uri::root() . '/media/com_eshop/products/resized/' . $image;
			}

			$orderProducts[$i]->image = $image;

			//Get downloads for each order product
			$query->clear()
				->select('*')
				->from('#__eshop_orderdownloads')
				->where('order_id = ' . intval($orderId))
				->where('order_product_id = ' . $orderProducts[$i]->id);
			$db->setQuery($query);
			$orderProducts[$i]->downloads = $db->loadObjectList();
		}

		return $orderProducts;
	}

	/**
	 *
	 * Function to get totals for a specific order
	 *
	 * @param   int  $orderId
	 *
	 * @return array total object list
	 */
	public static function getOrderTotals($orderId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_ordertotals')
			->where('order_id = ' . intval($orderId))
			->order('id');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to get options for a specific order product
	 *
	 * @param   int  $orderProductId
	 *
	 * @return array
	 */
	public static function getOrderOptions($orderProductId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_orderoptions')
			->where('order_product_id = ' . (int) $orderProductId);
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to get information for a specific quote
	 *
	 * @param   int  $quoteId
	 *
	 * @return stdClass quote Object
	 */
	public static function getQuote($quoteId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_quotes')
			->where('id = ' . (int) $quoteId);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 *
	 * Function to get products for a specific quote
	 *
	 * @param   int  $quoteId
	 *
	 * @return array
	 */
	public static function getQuoteProducts($quoteId)
	{
		$quote    = self::getQuote($quoteId);
		$currency = EShopCurrency::getInstance();
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$query->select('a.*, b.product_call_for_price')
			->from('#__eshop_quoteproducts AS a')
			->innerJoin('#__eshop_products AS b ON (a.product_id = b.id)')
			->where('a.quote_id = ' . (int) $quoteId);
		$db->setQuery($query);
		$quoteProducts = $db->loadObjectList();

		for ($i = 0; $n = count($quoteProducts), $i < $n; $i++)
		{
			$quoteProducts[$i]->quoteOptions = self::getQuoteOptions($quoteProducts[$i]->id);
			$quoteProducts[$i]->price        = $currency->format($quoteProducts[$i]->price, $quote->currency_code, $quote->currency_exchanged_value);
			$quoteProducts[$i]->total_price  = $currency->format(
				$quoteProducts[$i]->total_price,
				$quote->currency_code,
				$quote->currency_exchanged_value
			);
		}

		return $quoteProducts;
	}
	
	/**
	 *
	 * Function to get quote totals of a specific quote
	 *
	 * @param   int  $quoteId
	 *
	 * @return array
	 */
	public static function getQuoteTotals($quoteId)
	{
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_quotetotals')
			->where('quote_id = ' . intval($quoteId))
			->order('id');
		$db->setQuery($query);
		$quoteTotals = $db->loadObjectList();
		
		return $quoteTotals;
	}

	/**
	 *
	 * Function to get options for a specific quote product
	 *
	 * @param   int  $quoteProductId
	 *
	 * @return array
	 */
	public static function getQuoteOptions($quoteProductId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_quoteoptions')
			->where('quote_product_id = ' . (int) $quoteProductId);
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Function to get invoice output for products
	 *
	 * @param   int  $orderId
	 *
	 * @return string
	 */
	public static function getInvoiceProducts($orderId)
	{
		$orderProducts = self::getOrderProducts($orderId);
		$db            = Factory::getDbo();
		$query         = $db->getQuery(true);

		for ($i = 0; $n = count($orderProducts), $i < $n; $i++)
		{
			$query->clear()
				->select('*')
				->from('#__eshop_orderoptions')
				->where('order_product_id = ' . intval($orderProducts[$i]->id));
			$db->setQuery($query);
			$orderProducts[$i]->options = $db->loadObjectList();
		}

		$orderTotals = self::getOrderTotals($orderId);

		$text = EShopHtmlHelper::loadCommonLayout('invoicetemplates/default.php', ['orderProducts' => $orderProducts, 'orderTotals' => $orderTotals]);

		return $text;
	}

	/**
	 * Generate invoice PDF
	 *
	 * @param   array  $cid
	 */
	public static function generateInvoicePDF($cid)
	{
		require_once JPATH_ROOT . "/components/com_eshop/tcpdf/tcpdf.php";
		require_once JPATH_ROOT . "/components/com_eshop/tcpdf/config/lang/eng.php";

		$app      = Factory::getApplication();
		$sitename = $app->get("sitename");
		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_eshop/tables');
		$invoiceOutputs = '';

		for ($i = 0; $n = count($cid), $i < $n; $i++)
		{
			$id  = $cid[$i];
			$row = Table::getInstance('Eshop', 'Order');
			$row->load($id);
			// Initial pdf object
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor($sitename);
			$pdf->SetTitle('Invoice');
			$pdf->SetSubject('Invoice');
			$pdf->SetKeywords('Invoice');
			$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
			$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			// Set auto page breaks
			$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
			// Set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdfFont     = self::getConfigValue('pdf_font', 'times');
			$pdfFontSize = self::getConfigValue('pdf_font_size', '8');
			$pdf->SetFont($pdfFont, '', $pdfFontSize);
			$pdf->AddPage();
			$invoiceOutput = self::getMessageValue('invoice_layout', $row->language);

			// Store information
			$replaces                       = [];
			$replaces['customer_name']      = $row->firstname . ' ' . $row->lastname;
			$replaces['customergroup_name'] = self::getOrderCustomergroupName($row);
			$replaces['customergroup_name'] = self::getOrderCustomergroupName($row);
			$replaces['customer_email']     = $row->email;
			$replaces['customer_telephone'] = $row->telephone;
			$replaces['invoice_number']     = self::formatInvoiceNumber($row->invoice_number, $row->created_date);
			$replaces['store_owner']        = self::getConfigValue('store_owner');
			$replaces['store_name']         = self::getConfigValue('store_name');
			$replaces['store_address']      = str_replace("\r\n", "<br />", self::getConfigValue('address'));
			$replaces['store_telephone']    = self::getConfigValue('telephone');
			$replaces['store_fax']          = self::getConfigValue('fax');
			$replaces['store_email']        = self::getConfigValue('email');
			$replaces['store_url']          = Uri::root();
			$replaces['date_added']         = HTMLHelper::date($row->created_date, self::getConfigValue('date_format', 'm-d-Y'));
			$replaces['date_modified']      = HTMLHelper::date($row->modified_date, self::getConfigValue('date_format', 'm-d-Y'));
			$replaces['order_id']           = $row->id;
			$replaces['order_number']       = $row->order_number;
			$replaces['transaction_id']     = $row->transaction_id;
			$replaces['order_status']       = self::getOrderStatusName($row->order_status_id, $row->language);
			$replaces['payment_method']     = Text::_($row->payment_method_title);
			$replaces['shipping_method']    = Text::_($row->shipping_method_title);

			// Payment information
			$replaces['payment_address'] = self::getPaymentAddress($row);
			//Payment custom fields here
			$excludedFields = [
				'firstname',
				'lastname',
				'email',
				'telephone',
				'fax',
				'company',
				'company_id',
				'address_1',
				'address_2',
				'city',
				'postcode',
				'country_id',
				'zone_id',
			];
			$form           = new EshopRADForm(self::getFormFields('B'));
			$fields         = $form->getFields();

			foreach ($fields as $field)
			{
				$fieldName = $field->name;

				if (!in_array($fieldName, $excludedFields))
				{
					$fieldValue = $row->{'payment_' . $fieldName};

					if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
					{
						$fieldValue = implode(', ', json_decode($fieldValue));
					}

					$replaces['payment_' . $fieldName] = $fieldValue;
				}
			}

			// Shipping information
			$replaces['shipping_address'] = self::getShippingAddress($row);
			//Shipping custom fields here
			$form   = new EshopRADForm(self::getFormFields('S'));
			$fields = $form->getFields();

			foreach ($fields as $field)
			{
				$fieldName = $field->name;

				if (!in_array($fieldName, $excludedFields))
				{
					$fieldValue = $row->{'shipping_' . $fieldName};
					if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
					{
						$fieldValue = implode(', ', json_decode($fieldValue));
					}
					$replaces['shipping_' . $fieldName] = $fieldValue;
				}
			}
			// Products list
			$replaces['products_list'] = self::getInvoiceProducts($row->id);
			// Comment
			$replaces['comment'] = $row->comment;
			// Delivery Date
			$replaces['delivery_date'] = HTMLHelper::date($row->delivery_date, self::getConfigValue('date_format', 'm-d-Y'), null);

			foreach ($replaces as $key => $value)
			{
				$key           = strtoupper($key);
				$invoiceOutput = str_replace("[$key]", $value, $invoiceOutput);
			}

			if ($n > 1 && $i < ($n - 1))
			{
				$invoiceOutput = '<div style="page-break-after: always;">' . $invoiceOutput . '</div>';
			}

			$invoiceOutputs .= $invoiceOutput;

			if ($n == 1)
			{
				$filename = self::formatInvoiceNumber($row->invoice_number, $row->created_date) . '.pdf';
			}
		}

		if ($n > 1)
		{
			$filename = 'multiple-invoices.pdf';
		}

		$pdf->writeHTML($invoiceOutputs, true, false, false, false, '');

		$filePath = JPATH_ROOT . '/media/com_eshop/invoices/' . $filename;
		$pdf->Output($filePath, 'F');
	}

	/**
	 *
	 * Function to generate product PDF
	 *
	 * @param   int  $productId
	 */
	public static function generateProductPDF($productId)
	{
		require_once JPATH_ROOT . "/components/com_eshop/tcpdf/tcpdf.php";
		require_once JPATH_ROOT . "/components/com_eshop/tcpdf/config/lang/eng.php";

		$app      = Factory::getApplication();
		$sitename = $app->get("sitename");

		// Initial pdf object
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($sitename);
		$pdf->SetTitle('Product');
		$pdf->SetSubject('Product');
		$pdf->SetKeywords('Product');
		$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
		$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(PDF_MARGIN_LEFT, 12, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		// Set auto page breaks
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		// Set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdfFont     = self::getConfigValue('pdf_font', 'times');
		$pdfFontSize = self::getConfigValue('pdf_font_size', '8');
		$pdf->SetFont($pdfFont, '', $pdfFontSize);
		$pdf->AddPage();
		$productOutput = self::getMessageValue('product_pdf_layout', '');
		// Store information
		$replaces                    = [];
		$replaces['store_owner']     = self::getConfigValue('store_owner');
		$replaces['store_name']      = self::getConfigValue('store_name');
		$replaces['store_address']   = str_replace("\r\n", "<br />", self::getConfigValue('address'));
		$replaces['store_telephone'] = self::getConfigValue('telephone');
		$replaces['store_fax']       = self::getConfigValue('fax');
		$replaces['store_email']     = self::getConfigValue('email');
		$replaces['store_url']       = Uri::root();
		// Products list
		$replaces['product_details'] = self::getProductDetailsPDF($productId);

		foreach ($replaces as $key => $value)
		{
			$key           = strtoupper($key);
			$productOutput = str_replace("[$key]", $value, $productOutput);
		}

		$pdf->writeHTML($productOutput, true, false, false, false, '');
		$product  = self::getProduct($productId, Factory::getLanguage()->getTag());
		$filename = 'product_' . $productId . '.pdf';
		$filePath = JPATH_ROOT . '/media/com_eshop/pdf/' . $filename;
		$pdf->Output($filePath, 'F');
	}

	/**
	 *
	 * Function to download invoice
	 *
	 * @param   array  $cid
	 */
	public static function downloadInvoice($cid)
	{
		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_eshop/tables');
		$invoiceStorePath = JPATH_ROOT . '/media/com_eshop/invoices/';

		if (count($cid) == 1)
		{
			$id  = $cid[0];
			$row = Table::getInstance('Eshop', 'Order');
			$row->load($id);
			$filename = self::formatInvoiceNumber($row->invoice_number, $row->created_date) . '.pdf';
		}
		else
		{
			$filename = 'multiple-invoices.pdf';
		}

		self::generateInvoicePDF($cid);

		$invoicePath = $invoiceStorePath . $filename;
		while (@ob_end_clean())
		{
		}
		self::processDownload($invoicePath, $filename, true);
	}

	/**
	 *
	 * Function to get product details PDF
	 *
	 * @param   int  $productId
	 *
	 * @return string
	 */
	public static function getProductDetailsPDF($productId)
	{
		$product = self::getProduct($productId, Factory::getLanguage()->getTag());
		$db      = Factory::getDbo();

		// Product availability
		$productInventory = self::getProductInventory($productId);

		if ($product->product_quantity <= 0)
		{
			$nullDate = $db->getNullDate();

			if ($product->product_available_date != $nullDate)
			{
				$product->product_available_date = HTMLHelper::date($product->product_available_date, self::getConfigValue('date_format', 'm-d-Y'));
			}

			$availability = self::getStockStatusName($productInventory['product_stock_status_id'], Factory::getLanguage()->getTag());
		}
		elseif ($productInventory['product_stock_display'])
		{
			$availability = $product->product_quantity;
		}
		else
		{
			$availability = Text::_('ESHOP_IN_STOCK');
		}

		$product->availability = $availability;
		//Main image resize
		$thumbImageSizeFunction = self::getConfigValue('thumb_image_size_function', 'resizeImage');

		if ($product->product_image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $product->product_image))
		{
			if (self::getConfigValue('product_use_image_watermarks'))
			{
				$watermarkImage = self::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/products/' . $product->product_image);
				$productImage   = $watermarkImage;
			}
			else
			{
				$productImage = $product->product_image;
			}
			$thumbImage = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
				[
					$productImage,
					JPATH_ROOT . '/media/com_eshop/products/',
					self::getConfigValue('image_thumb_width'),
					self::getConfigValue('image_thumb_height'),
				]);
		}
		else
		{
			$thumbImage = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
				[
					EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
					JPATH_ROOT . '/media/com_eshop/products/',
					self::getConfigValue('image_thumb_width'),
					self::getConfigValue('image_thumb_height'),
				]);
		}

		if ($thumbImageSizeFunction == 'notResizeImage')
		{
			if (EShopHelper::getConfigValue('pdf_image_path', 'absolutely') == 'absolutely')
			{
				$product->thumb_image = Uri::root() . '/media/com_eshop/products/' . $thumbImage;
			}
			else
			{
				$product->thumb_image = 'media/com_eshop/products/' . $thumbImage;
			}
		}
		else
		{
			if (EShopHelper::getConfigValue('pdf_image_path', 'absolutely') == 'absolutely')
			{
				$product->thumb_image = Uri::root() . '/media/com_eshop/products/resized/' . $thumbImage;
			}
			else
			{
				$product->thumb_image = 'media/com_eshop/products/resized/' . $thumbImage;
			}
		}

		//Additional images resize
		$productImages = self::getProductImages($productId);

		for ($i = 0; $n = count($productImages), $i < $n; $i++)
		{
			if ($productImages[$i]->image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $productImages[$i]->image))
			{
				if (self::getConfigValue('product_use_image_watermarks'))
				{
					$watermarkImage = self::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/products/' . $productImages[$i]->image);
					$productImage   = $watermarkImage;
				}
				else
				{
					$productImage = $productImages[$i]->image;
				}

				$thumbImage = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
					[
						$productImage,
						JPATH_ROOT . '/media/com_eshop/products/',
						self::getConfigValue('image_thumb_width'),
						self::getConfigValue('image_thumb_height'),
					]);
			}
			else
			{
				$thumbImage = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
					[
						EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
						JPATH_ROOT . '/media/com_eshop/products/',
						self::getConfigValue('image_thumb_width'),
						self::getConfigValue('image_thumb_height'),
					]);
			}

			if ($thumbImageSizeFunction == 'notResizeImage')
			{
				if (EShopHelper::getConfigValue('pdf_image_path', 'absolutely') == 'absolutely')
				{
					$productImages[$i]->thumb_image = Uri::root() . '/media/com_eshop/products/' . $thumbImage;
				}
				else
				{
					$productImages[$i]->thumb_image = 'media/com_eshop/products/' . $thumbImage;
				}
			}
			else
			{
				if (EShopHelper::getConfigValue('pdf_image_path', 'absolutely') == 'absolutely')
				{
					$productImages[$i]->thumb_image = Uri::root() . '/media/com_eshop/products/resized/' . $thumbImage;
				}
				else
				{
					$productImages[$i]->thumb_image = 'media/com_eshop/products/resized/' . $thumbImage;
				}
			}
		}

		$discountPrices    = self::getDiscountPrices($productId);
		$manufacturer      = self::getProductManufacturer($productId, Factory::getLanguage()->getTag());
		$hasSpecification  = false;
		$attributeGroups   = self::getAttributeGroups(Factory::getLanguage()->getTag());
		$productAttributes = [];

		for ($i = 0; $n = count($attributeGroups), $i < $n; $i++)
		{
			$productAttributes[] = self::getAttributes($productId, $attributeGroups[$i]->id, Factory::getLanguage()->getTag());
			if (count($productAttributes[$i]))
			{
				$hasSpecification = true;
			}
		}

		//Custom fields handle
		if (EShopHelper::getConfigValue('product_custom_fields'))
		{
			EShopHelper::prepareCustomFieldsData([$product]);
		}

		$text = EShopHtmlHelper::loadCommonLayout(
			'pdftemplates/default.php',
			[
				'currency'          => EShopCurrency::getInstance(),
				'tax'               => new EShopTax(self::getConfig()),
				'product'           => $product,
				'discountPrices'    => $discountPrices,
				'manufacturer'      => $manufacturer,
				'productImages'     => $productImages,
				'hasSpecification'  => $hasSpecification,
				'attributeGroups'   => $attributeGroups,
				'productAttributes' => $productAttributes,
			]
		);

		return $text;
	}

	/**
	 *
	 * Function to process download
	 *
	 * @param   string   $filePath
	 * @param   string   $filename
	 * @param   boolean  $download
	 */
	public static function processDownload($filePath, $filename, $download = false)
	{
		$fsize    = @filesize($filePath);
		$mod_date = date('r', filemtime($filePath));

		if ($download)
		{
			$cont_dis = 'attachment';
		}
		else
		{
			$cont_dis = 'inline';
		}

		$ext  = EShopHelper::getFileExt($filename);
		$mime = self::getMimeType($ext);
		// required for IE, otherwise Content-disposition is ignored
		if (ini_get('zlib.output_compression'))
		{
			ini_set('zlib.output_compression', 'Off');
		}
		header("Pragma: public");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
		header("Content-Transfer-Encoding: binary");
		header(
			'Content-Disposition:' . $cont_dis . ';'
			. ' filename="' . basename($filename) . '";'
			. ' modification-date="' . $mod_date . '";'
			. ' size=' . $fsize . ';'
		); //RFC2183
		header("Content-Type: " . $mime);            // MIME type
		header("Content-Length: " . $fsize);

		if (!ini_get('safe_mode'))
		{ // set_time_limit doesn't work in safe mode
			@set_time_limit(0);
		}
		self::readfile_chunked($filePath);
		exit();
	}

	/**
	 *
	 * Function to get mimetype of file
	 *
	 * @param   string  $ext
	 *
	 * @return string
	 */
	public static function getMimeType($ext)
	{
		require_once JPATH_ROOT . "/components/com_eshop/helpers/mime.mapping.php";
		foreach ($mime_extension_map as $key => $value)
		{
			if ($key == $ext)
			{
				return $value;
			}
		}

		return "";
	}

	/**
	 *
	 * Function to read file
	 *
	 * @param   string   $filename
	 * @param   boolean  $retbytes
	 *
	 * @return boolean|number
	 */
	public static function readfile_chunked($filename, $retbytes = true)
	{
		$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
		$buffer    = '';
		$cnt       = 0;
		$handle    = fopen($filename, 'rb');

		if ($handle === false)
		{
			return false;
		}

		while (!feof($handle))
		{
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			@ob_flush();
			flush();

			if ($retbytes)
			{
				$cnt += strlen($buffer);
			}
		}

		$status = fclose($handle);

		if ($retbytes && $status)
		{
			return $cnt; // return num. bytes delivered like readfile() does.
		}

		return $status;
	}

	/**
	 * Convert all img tags to use absolute URL
	 *
	 * @param   string  $html_content
	 *
	 * @return string
	 */
	public static function convertImgTags($html_content)
	{
		$patterns     = [];
		$replacements = [];
		$i            = 0;
		$src_exp      = "/src=\"(.*?)\"/";
		$link_exp     = "[^http:\/\/www\.|^www\.|^https:\/\/|^http:\/\/]";
		$siteURL      = Uri::root();
		preg_match_all($src_exp, $html_content, $out, PREG_SET_ORDER);

		foreach ($out as $val)
		{
			$links = preg_match($link_exp, $val[1], $match, PREG_OFFSET_CAPTURE);

			if ($links == '0')
			{
				$patterns[$i]     = $val[1];
				$patterns[$i]     = "\"$val[1]";
				$replacements[$i] = $siteURL . $val[1];
				$replacements[$i] = "\"$replacements[$i]";
			}

			$i++;
		}

		$mod_html_content = str_replace($patterns, $replacements, $html_content);

		return $mod_html_content;
	}

	/**
	 *
	 * Function to get order number product
	 *
	 * @param   int  $orderId
	 *
	 * @return int
	 */
	public static function getNumberProduct($orderId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)')
			->from('#__eshop_orderproducts')
			->where('order_id=' . intval($orderId));
		$db->setQuery($query);

		return $db->loadResult();
	}
	
	/**
	 *
	 * Function to get quote number product
	 *
	 * @param   int  $quoteId
	 *
	 * @return int
	 */
	public static function getNumberProductQuote($quoteId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)')
			->from('#__eshop_quoteproducts')
			->where('quote_id=' . intval($quoteId));
		$db->setQuery($query);
	
		return $db->loadResult();
	}

	/**
	 *
	 * Function to get substring
	 *
	 * @param   string   $text
	 * @param   int      $length
	 * @param   string   $replacer
	 * @param   boolean  $isAutoStripsTag
	 *
	 * @return string
	 */
	public static function substring($text, $length = 100, $replacer = '...', $isAutoStripsTag = true)
	{
		$string = $isAutoStripsTag ? strip_tags($text) : $text;

		return StringHelper::strlen($string) > $length ? HTMLHelper::_('string.truncate', $string, $length) : $string;
	}

	/**
	 *
	 * Function to get alement alias
	 *
	 * @param   int     $id
	 * @param   string  $element
	 * @param   string  $langCode
	 *
	 * @return string
	 */
	public static function getElementAlias($id, $element, $langCode = '')
	{
		if (!$langCode)
		{
			$langCode = Factory::getLanguage()->getTag();
		}

		if ($element === 'product' && isset(self::$productsAlias[$langCode . '.' . $id]))
		{
			return self::$productsAlias[$langCode . '.' . $id];
		}

		if ($element === 'manufacturer' && isset(self::$manufacturersAlias[$langCode . '.' . $id]))
		{
			return self::$manufacturersAlias[$langCode . '.' . $id];
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($element . '_name, ' . $element . '_alias')
			->from('#__eshop_' . $element . 'details')
			->where($element . '_id = ' . (int) $id)
			->where('language = ' . $db->quote($langCode));
		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row->{$element . '_alias'} != '')
		{
			return $row->{$element . '_alias'};
		}

		return $row->{$element . '_name'};
	}

	/**
	 *
	 * Function to get categories navigation
	 *
	 * @param   int  $id
	 *
	 * @return array
	 */
	public static function getCategoriesNavigation($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		//Find previous/next categories
		$query->select('a.id, b.category_id, b.category_name, b.category_page_title')
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
			->where('a.published = 1')
			->where('a.category_parent_id = (SELECT category_parent_id FROM #__eshop_categories WHERE id = ' . intval($id) . ')')
			->where('b.language = ' . $db->quote(Factory::getLanguage()->getTag()))
			->order('a.ordering');

		//Check viewable of customer groups
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$query->where(
			'((a.category_customergroups = "") OR (a.category_customergroups IS NULL) OR (a.category_customergroups = "' . $customerGroupId . '") OR (a.category_customergroups LIKE "' . $customerGroupId . ',%") OR (a.category_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.category_customergroups LIKE "%,' . $customerGroupId . '"))'
		);

		$db->setQuery($query);
		$categories = $db->loadObjectList();

		for ($i = 0; $n = count($categories), $i < $n; $i++)
		{
			if ($categories[$i]->id == $id)
			{
				break;
			}
		}

		return [$categories[$i - 1] ?? '', $categories[$i + 1] ?? ''];
	}

	/**
	 *
	 * Function to get products navigation
	 *
	 * @param   int  $id
	 *
	 * @return array
	 */
	public static function getProductsNavigation($id)
	{
		$app              = Factory::getApplication();
		$fromView         = $app->getUserState('from_view');
		$sortOptions      = $app->getUserState('sort_options');
		$allowedSortArr   = [
			'a.ordering',
			'b.product_name',
			'a.product_sku',
			'a.product_price',
			'a.product_length',
			'a.product_width',
			'a.product_height',
			'a.product_weight',
			'a.product_quantity',
			'b.product_short_desc',
			'b.product_desc',
			'product_rates',
			'product_reviews',
			'a.id',
			'product_best_sellers',
		];
		$allowedDirectArr = ['ASC', 'DESC'];
		$sort             = 'a.ordering';
		$direct           = 'ASC';

		if ($sortOptions != '')
		{
			$sortOptions = explode('-', $sortOptions);

			if (isset($sortOptions[0]) && in_array($sortOptions[0], $allowedSortArr))
			{
				$sort = $sortOptions[0];
			}

			if (isset($sortOptions[1]) && in_array($sortOptions[1], $allowedDirectArr))
			{
				$direct = $sortOptions[1];
			}
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($fromView == 'manufacturer')
		{
			//Find previous/next products
			$query->select('a.id, b.product_id, b.product_name, b.product_page_title')
				->from('#__eshop_products AS a')
				->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
				->where('a.published = 1')
				->where('a.manufacturer_id = (SELECT manufacturer_id FROM #__eshop_products WHERE id = ' . intval($id) . ')')
				->where('b.language = "' . Factory::getLanguage()->getTag() . '"')
				->order($sort . ' ' . $direct)
				->order('a.ordering');
		}
		else
		{
			$categoryId = $app->input->getInt('catid');

			if (!$categoryId)
			{
				$categoryId = intval(self::getProductCategory($id));
			}

			//Find previous/next products
			$query->select('a.id, b.product_id, b.product_name, b.product_page_title, pc.category_id')
				->from('#__eshop_products AS a')
				->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
				->innerJoin('#__eshop_productcategories AS pc ON (a.id = pc.product_id)')
				->where('a.published = 1')
				->where('pc.category_id = ' . intval($categoryId))
				->where('b.language = ' . $db->quote(Factory::getLanguage()->getTag()))
				->order($sort . ' ' . $direct)
				->order('a.ordering');
		}

		//Check viewable of customer groups
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$query->where(
			'((a.product_customergroups = "") OR (a.product_customergroups IS NULL) OR (a.product_customergroups = "' . $customerGroupId . '") OR (a.product_customergroups LIKE "' . $customerGroupId . ',%") OR (a.product_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.product_customergroups LIKE "%,' . $customerGroupId . '"))'
		);

		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());

		$query->where(
			'(a.product_available_date = ' . $nullDate . ' OR a.product_available_date IS NULL OR a.product_available_date <= ' . $currentDate . ')'
		);
		$query->where('(a.product_end_date = ' . $nullDate . ' OR a.product_end_date IS NULL OR a.product_end_date >= ' . $currentDate . ')');

		$langCode = Factory::getLanguage()->getTag();
		$query->where(
			'((a.product_languages = "") OR (a.product_languages IS NULL) OR (a.product_languages = "' . $langCode . '") OR (a.product_languages LIKE "' . $langCode . ',%") OR (a.product_languages LIKE "%,' . $langCode . ',%") OR (a.product_languages LIKE "%,' . $langCode . '"))'
		);

		//Check out of stock
		if (EShopHelper::getConfigValue('hide_out_of_stock_products'))
		{
			$query->where('a.product_quantity > 0');
		}

		$sortOptions = $app->getUserState('sort_options');

		if ($sortOptions == 'product_rates-ASC' || $sortOptions == 'product_rates-DESC' || $sortOptions == 'product_reviews-ASC' || $sortOptions == 'product_reviews-DESC')
		{
			if ($sortOptions == 'product_rates-ASC' || $sortOptions == 'product_rates-DESC')
			{
				$query->select('AVG(r.rating) AS product_rates');
			}
			elseif ($sortOptions == 'product_reviews-ASC' || $sortOptions == 'product_reviews-DESC')
			{
				$query->select('COUNT(r.id) AS product_reviews');
			}

			$query->leftJoin('#__eshop_reviews AS r ON (a.id = r.product_id AND r.published = 1)');

			$query->group('a.id');
		}
		else
		{
			if ($sortOptions == 'product_best_sellers-DESC')
			{
				$query->select('SUM(op.quantity) AS product_best_sellers');
				$query->leftJoin(
					'#__eshop_orderproducts AS op ON (a.id = op.product_id AND op.order_id IN (SELECT id FROM #__eshop_orders WHERE order_status_id = ' . self::getConfigValue(
						'complete_status_id'
					) . '))'
				);
				$query->group('a.id');
			}
		}

		$db->setQuery($query);
		$products = $db->loadObjectList();

		for ($i = 0; $n = count($products), $i < $n; $i++)
		{
			if ($products[$i]->id == $id)
			{
				break;
			}
		}

		return [$products[$i - 1] ?? '', $products[$i + 1] ?? ''];
	}

	/**
	 *
	 * Function to get category id/alias path
	 *
	 * @param   int     $id
	 * @param   string  $type
	 * @param   string  $langCode
	 * @param   int     $parentId
	 *
	 * @return array
	 */
	public static function getCategoryPath($id, $type, $langCode = '', $parentId = 0)
	{
		$categories = self::getAllCategories($langCode);

		$alias = [];
		$ids   = [];

		do
		{
			if (!isset($categories[$id]))
			{
				break;
			}

			$alias[] = $categories[$id]->category_alias;
			$ids[]   = $categories[$id]->id;
			$id      = $categories[$id]->category_parent_id;
		} while ($id != $parentId);

		if ($type == 'id')
		{
			return array_reverse($ids);
		}

		return array_reverse($alias);
	}

	/**
	 *
	 * Function to get categories bread crumb
	 *
	 * @param   int     $id
	 * @param   int     $parentId
	 * @param   string  $langCode
	 *
	 * @return array
	 */
	public static function getCategoriesBreadcrumb($id, $parentId, $langCode = '')
	{
		$categories = self::getAllCategories($langCode);
		$paths      = [];

		while ($id != $parentId)
		{
			if (isset($categories[$id]))
			{
				$paths[] = $categories[$id];
				$id      = $categories[$id]->category_parent_id;
			}
			else
			{
				break;
			}
		}

		return $paths;
	}

	/**
	 *
	 * Function to get category name path
	 *
	 * @param   int     $id
	 * @param   string  $langCode
	 *
	 * @return array
	 */
	public static function getCategoryNamePath($id, $langCode = '')
	{
		$categories = self::getAllCategories($langCode);
		$names      = [];

		do
		{
			$names[] = $categories[$id]->category_name;
			$id      = $categories[$id]->category_parent_id;
		} while ($id != 0);

		return array_reverse($names);
	}

	/**
	 *
	 * Function to identify if price will be showed or not
	 * @return boolean
	 */
	public static function showPrice()
	{
		$displayPrice = self::getConfigValue('display_price', 'public');

		if ($displayPrice == 'public')
		{
			return true;
		}

		if ($displayPrice == 'hide')
		{
			return false;
		}

		if (Factory::getUser()->get('id'))
		{
			return true;
		}

		return false;
	}

	/**
	 *
	 * Function to get default address id
	 *
	 * @param   int  $id
	 *
	 * @return int
	 */
	public static function getDefaultAddressId($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('address_id')
			->from('#__eshop_customers')
			->where('customer_id = ' . (int) $id);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
	 * Function to count address for current user
	 *
	 * @return int
	 */
	public static function countAddress()
	{
		$user  = Factory::getUser();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)')
			->from('#__eshop_addresses')
			->where('customer_id=' . (int) $user->get('id'));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
	 * Function to get continue shopping url
	 *
	 * @return string
	 */
	public static function getContinueShopingUrl()
	{
		if (Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
		{
			$langCode = Factory::getLanguage()->getTag();

			if ($url = self::getConfigValue('continue_shopping_url_' . $langCode))
			{
				return $url;
			}
		}
		else
		{
			if ($url = self::getConfigValue('continue_shopping_url'))
			{
				return $url;
			}
		}

		if ($url = Factory::getApplication()->getSession()->get('continue_shopping_url'))
		{
			return $url;
		}

		return Uri::root();
	}

	/**
	 *
	 * Function to get coupon
	 *
	 * @param   string  $couponCode
	 *
	 * @return stdClass
	 */
	public static function getCoupon($couponCode)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_coupons')
			->where('coupon_code = ' . $db->quote($couponCode));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 *
	 * Function to get voucher
	 *
	 * @param   string  $voucherCode
	 *
	 * @return object
	 */
	public static function getVoucher($voucherCode)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_vouchers')
			->where('voucher_code = ' . $db->quote($voucherCode));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to get list of product's lables
	 *
	 * @param   int     $productId
	 * @param   string  $langCode
	 *
	 * @return array|mixed
	 */
	public static function getProductLabels($productId, $langCode = '')
	{
		if (!$langCode)
		{
			$langCode = Factory::getLanguage()->getTag();
		}

		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());

		$query->select('DISTINCT(label_id)')
			->from('#__eshop_labelelements')
			->where(
				'(element_type = "product" AND element_id = ' . intval(
					$productId
				) . ') OR (element_type = "manufacturer" AND element_id = (SELECT manufacturer_id FROM #__eshop_products WHERE id = ' . intval(
					$productId
				) . ')) OR (element_type = "category" AND element_id IN (SELECT category_id FROM #__eshop_productcategories WHERE product_id = ' . intval(
					$productId
				) . '))'
			);
		$db->setQuery($query);
		$labelIds = $db->loadColumn();

		//Get out of stock label
		$query->clear()
			->select('product_quantity')
			->from('#__eshop_products')
			->where('id = ' . intval($productId));
		$db->setQuery($query);
		$productQuantity = $db->loadResult();

		if ($productQuantity <= 0)
		{
			$query->clear()
				->select('id')
				->from('#__eshop_labels')
				->where('label_out_of_stock_products = 1');

			if (count($labelIds))
			{
				$query->where('id NOT IN (' . implode(',', $labelIds) . ')');
			}

			$db->setQuery($query);
			$additionalLabelIds = $db->loadColumn();

			if (count($additionalLabelIds))
			{
				$labelIds = array_merge($labelIds, $additionalLabelIds);
			}
		}

		if (count($labelIds))
		{
			$query->clear()
				->select('a.*, b.label_name')
				->from('#__eshop_labels AS a')
				->innerJoin('#__eshop_labeldetails AS b ON (a.id = b.label_id)')
				->where('a.id IN (' . implode(',', $labelIds) . ')')
				->where('a.published = 1')
				->where('(label_start_date = ' . $nullDate . ' OR label_start_date IS NULL OR label_start_date <= ' . $currentDate . ')')
				->where('(label_end_date = ' . $nullDate . ' OR label_end_date IS NULL OR label_end_date >= ' . $currentDate . ')')
				->where('b.language = ' . $db->quote($langCode))
				->order('a.ordering');
			$db->setQuery($query);

			$rows              = $db->loadObjectList();
			$imagePath         = JPATH_ROOT . '/media/com_eshop/labels/';
			$imageSizeFunction = self::getConfigValue('label_image_size_function', 'resizeImage');
			$imageLabelWidth   = self::getConfigValue('image_label_width');
			$imageLabelHeight  = self::getConfigValue('image_label_height');
			$baseUri           = Uri::base(true);

			for ($i = 0; $n = count($rows), $i < $n; $i++)
			{
				$row = $rows[$i];

				if ($row->label_image)
				{
					//Do the resize
					$imageWidth = $row->label_image_width > 0 ? $row->label_image_width : $imageLabelWidth;

					if (!$imageWidth)
					{
						$imageWidth = 50;
					}

					$imageHeight = $row->label_image_height > 0 ? $row->label_image_height : $imageLabelHeight;

					if (!$imageHeight)
					{
						$imageHeight = 50;
					}

					if ($imageSizeFunction != 'notResizeImage')
					{
						if (!is_file(
							$imagePath . 'resized/' . File::stripExt($row->label_image) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt(
								$row->label_image
							)
						))
						{
							$row->label_image = $baseUri . '/media/com_eshop/labels/resized/' . call_user_func_array(
									['EShopHelper', $imageSizeFunction],
									[$row->label_image, $imagePath, $imageWidth, $imageHeight]
								);
						}
						else
						{
							$row->label_image = $baseUri . '/media/com_eshop/labels/resized/' . File::stripExt(
									$row->label_image
								) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt($row->label_image);
						}
					}
					else
					{
						$row->label_image = $baseUri . '/media/com_eshop/labels/' . $row->label_image;
					}
				}
			}

			return $rows;
		}

		return [];
	}

	/**
	 * Method to get list of product's lables
	 *
	 * @param   stdClass  $product
	 * @param   string    $langCode
	 *
	 * @return array|mixed
	 */
	public static function getProductLabelsData($product, $langCode = '')
	{
		if (!$langCode)
		{
			$langCode = Factory::getLanguage()->getTag();
		}

		$productId = $product->id;

		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());

		$query->select('DISTINCT(label_id)')
			->from('#__eshop_labelelements')
			->where(
				'(element_type = "product" AND element_id = ' . intval(
					$productId
				) . ') OR (element_type = "manufacturer" AND element_id = (SELECT manufacturer_id FROM #__eshop_products WHERE id = ' . intval(
					$productId
				) . ')) OR (element_type = "category" AND element_id IN (SELECT category_id FROM #__eshop_productcategories WHERE product_id = ' . intval(
					$productId
				) . '))'
			);
		$db->setQuery($query);
		$labelIds = $db->loadColumn();

		//Get out of stock label
		$productQuantity = $product->product_quantity;

		if ($productQuantity <= 0)
		{
			$query->clear()
				->select('id')
				->from('#__eshop_labels')
				->where('label_out_of_stock_products = 1');

			if (count($labelIds))
			{
				$query->where('id NOT IN (' . implode(',', $labelIds) . ')');
			}

			$db->setQuery($query);
			$additionalLabelIds = $db->loadColumn();

			if (count($additionalLabelIds))
			{
				$labelIds = array_merge($labelIds, $additionalLabelIds);
			}
		}

		if (count($labelIds))
		{
			$query->clear()
				->select('a.*, b.label_name')
				->from('#__eshop_labels AS a')
				->innerJoin('#__eshop_labeldetails AS b ON (a.id = b.label_id)')
				->where('a.id IN (' . implode(',', $labelIds) . ')')
				->where('a.published = 1')
				->where('(label_start_date = ' . $nullDate . ' OR label_start_date IS NULL OR label_start_date <= ' . $currentDate . ')')
				->where('(label_end_date = ' . $nullDate . ' OR label_end_date IS NULL OR label_end_date >= ' . $currentDate . ')')
				->where('b.language = ' . $db->quote($langCode))
				->order('a.ordering');
			$db->setQuery($query);

			$rows              = $db->loadObjectList();
			$imagePath         = JPATH_ROOT . '/media/com_eshop/labels/';
			$imageSizeFunction = self::getConfigValue('label_image_size_function', 'resizeImage');
			$imageLabelWidth   = self::getConfigValue('image_label_width');
			$imageLabelHeight  = self::getConfigValue('image_label_height');
			$baseUri           = Uri::base(true);

			for ($i = 0; $n = count($rows), $i < $n; $i++)
			{
				$row = $rows[$i];

				if ($row->label_image)
				{
					//Do the resize
					$imageWidth = $row->label_image_width > 0 ? $row->label_image_width : $imageLabelWidth;

					if (!$imageWidth)
					{
						$imageWidth = 50;
					}

					$imageHeight = $row->label_image_height > 0 ? $row->label_image_height : $imageLabelHeight;

					if (!$imageHeight)
					{
						$imageHeight = 50;
					}

					if ($imageSizeFunction != 'notResizeImage')
					{
						if (!is_file(
							$imagePath . 'resized/' . File::stripExt($row->label_image) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt(
								$row->label_image
							)
						))
						{
							$row->label_image = $baseUri . '/media/com_eshop/labels/resized/' . call_user_func_array(
									['EShopHelper', $imageSizeFunction],
									[$row->label_image, $imagePath, $imageWidth, $imageHeight]
								);
						}
						else
						{
							$row->label_image = $baseUri . '/media/com_eshop/labels/resized/' . File::stripExt(
									$row->label_image
								) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt($row->label_image);
						}
					}
					else
					{
						$row->label_image = $baseUri . '/media/com_eshop/labels/' . $row->label_image;
					}
				}
			}

			return $rows;
		}

		return [];
	}

	/**
	 * Get URL of the site, using for Ajax request
	 */
	public static function getSiteUrl()
	{
		$uri  = Uri::getInstance();
		$base = $uri->toString(['scheme', 'host', 'port']);

		if (strpos(php_sapi_name(), 'cgi') !== false && !ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI']))
		{
			$script_name = $_SERVER['PHP_SELF'];
		}
		else
		{
			$script_name = $_SERVER['SCRIPT_NAME'];
		}

		$path = rtrim(dirname($script_name), '/\\');

		if ($path)
		{
			$siteUrl = $base . $path . '/';
		}
		else
		{
			$siteUrl = $base . '/';
		}

		if (Factory::getApplication()->isClient('administrator'))
		{
			$adminPos = strrpos($siteUrl, 'administrator/');
			$siteUrl  = substr_replace($siteUrl, '', $adminPos, 14);
		}

		return $siteUrl;
	}

	/**
	 * Function to get checkout type
	 *
	 * @return string
	 */
	public static function getCheckoutType()
	{
		if (self::getConfigValue('display_price') == 'registered')
		{
			//Only registered
			return 'registered_only';
		}

		return self::getConfigValue('checkout_type');
	}

	/**
	 * Get form billing or shopping form fields
	 *
	 * @param   string  $addressType
	 * @param   array   $excludedFields
	 *
	 * @return array
	 */
	public static function getFormFields($addressType = '', $excludedFields = [], $langCode = '')
	{
		$db = Factory::getDbo();

		if ($langCode == '')
		{
			$langCode = Factory::getLanguage()->getTag();
		}

		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_fields AS a')
			->innerJoin('#__eshop_fielddetails AS b ON a.id=b.field_id')
			->where('a.published = 1')
			->where('b.language = ' . $db->quote($langCode))
			->order('a.ordering');

		if ($addressType != '')
		{
			$query->where('(address_type=' . $db->quote($addressType) . ' OR address_type="A")');
		}

		if (count($excludedFields) > 0)
		{
			foreach ($excludedFields as $fieldName)
			{
				$query->where('name != "' . $fieldName . '"');
			}
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Check if the country has zones or not
	 *
	 * @param   int  $countryId
	 *
	 * @return boolean
	 */
	public static function hasZone($countryId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__eshop_zones')
			->where('country_id = ' . (int) $countryId)
			->where('published = 1');
		$db->setQuery($query);
		$total = (int) $db->loadResult();

		if ($total)
		{
			return true;
		}

		return false;
	}

	/**
	 *
	 * Function to get Shipping Address Format
	 *
	 * @param   order object $row
	 *
	 * @return string
	 */
	public static function getShippingAddress($row)
	{
		$shippingAddress = self::getConfigValue(
			'shipping_address_format',
			'[SHIPPING_FIRSTNAME] [SHIPPING_LASTNAME]<br /> [SHIPPING_ADDRESS_1], [SHIPPING_ADDRESS_2]<br /> [SHIPPING_CITY], [SHIPPING_POSTCODE] [SHIPPING_ZONE_NAME]<br /> [SHIPPING_EMAIL]<br /> [SHIPPING_TELEPHONE]<br /> [SHIPPING_FAX]'
		);
		$shippingAddress = str_replace('[SHIPPING_FIRSTNAME]', $row->shipping_firstname, $shippingAddress);

		if (self::isFieldPublished('lastname') && $row->shipping_lastname != '')
		{
			$shippingAddress = str_replace('[SHIPPING_LASTNAME]', $row->shipping_lastname, $shippingAddress);
		}
		else
		{
			$shippingAddress = str_replace('[SHIPPING_LASTNAME]', '', $shippingAddress);
		}

		$shippingAddress = str_replace('[SHIPPING_ADDRESS_1]', $row->shipping_address_1, $shippingAddress);

		if (self::isFieldPublished('address_2') && $row->shipping_address_2 != '')
		{
			$shippingAddress = str_replace(', [SHIPPING_ADDRESS_2]', ', ' . $row->shipping_address_2, $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_ADDRESS_2]', $row->shipping_address_2, $shippingAddress);
		}
		else
		{
			$shippingAddress = str_replace(', [SHIPPING_ADDRESS_2]', '', $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_ADDRESS_2]', '', $shippingAddress);
		}

		if (self::isFieldPublished('city'))
		{
			$shippingAddress = str_replace('<br /> [SHIPPING_CITY]', '<br />' . $row->shipping_city, $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_CITY]', $row->shipping_city, $shippingAddress);
		}
		else
		{
			$shippingAddress = str_replace('<br /> [SHIPPING_CITY]', '', $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_CITY]', '', $shippingAddress);
		}

		if (self::isFieldPublished('postcode') && $row->shipping_postcode != '')
		{
			$shippingAddress = str_replace(', [SHIPPING_POSTCODE]', ', ' . $row->shipping_postcode, $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_POSTCODE]', $row->shipping_postcode, $shippingAddress);
		}
		else
		{
			$shippingAddress = str_replace(', [SHIPPING_POSTCODE]', '', $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_POSTCODE]', '', $shippingAddress);
		}

		$shippingAddress = str_replace('[SHIPPING_EMAIL]', $row->shipping_email, $shippingAddress);

		if (self::isFieldPublished('telephone') && $row->shipping_telephone != '')
		{
			$shippingAddress = str_replace('<br /> [SHIPPING_TELEPHONE]', '<br /> ' . $row->shipping_telephone, $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_TELEPHONE]', $row->shipping_telephone, $shippingAddress);
		}
		else
		{
			$shippingAddress = str_replace('<br /> [SHIPPING_TELEPHONE]', '', $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_TELEPHONE]', '', $shippingAddress);
		}

		if (self::isFieldPublished('fax') && $row->shipping_fax != '')
		{
			$shippingAddress = str_replace('<br /> [SHIPPING_FAX]', '<br /> ' . $row->shipping_fax, $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_FAX]', $row->shipping_fax, $shippingAddress);
		}
		else
		{
			$shippingAddress = str_replace('<br /> [SHIPPING_FAX]', '', $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_FAX]', '', $shippingAddress);
		}

		if (self::isFieldPublished('company') && $row->shipping_company != '')
		{
			$shippingAddress = str_replace('[SHIPPING_COMPANY]', $row->shipping_company, $shippingAddress);
		}
		else
		{
			$shippingAddress = str_replace('<br />[SHIPPING_COMPANY]', '', $shippingAddress);
			$shippingAddress = str_replace('[SHIPPING_COMPANY]', '', $shippingAddress);
		}

		if (self::isFieldPublished('company_id') && $row->shipping_company_id != '')
		{
			$shippingAddress = str_replace('[SHIPPING_COMPANY_ID]', $row->shipping_company_id, $shippingAddress);
		}
		else
		{
			$shippingAddress = str_replace('[SHIPPING_COMPANY_ID]', '', $shippingAddress);
		}

		if (self::isFieldPublished('zone_id') && $row->shipping_zone_name != '')
		{
			$shippingAddress = str_replace('[SHIPPING_ZONE_NAME]', $row->shipping_zone_name, $shippingAddress);
		}
		else
		{
			$shippingAddress = str_replace('[SHIPPING_ZONE_NAME]', '', $shippingAddress);
		}

		if (self::isFieldPublished('country_id') && $row->shipping_country_name != '')
		{
			$shippingAddress = str_replace('[SHIPPING_COUNTRY_NAME]', $row->shipping_country_name, $shippingAddress);
		}
		else
		{
			$shippingAddress = str_replace('[SHIPPING_COUNTRY_NAME]', '', $shippingAddress);
		}

		return $shippingAddress;
	}

	/**
	 *
	 * Function to get Payment Address Format
	 *
	 * @param   order object $row
	 *
	 * @return string
	 */
	public static function getPaymentAddress($row)
	{
		$paymentAddress = self::getConfigValue(
			'payment_address_format',
			'[PAYMENT_FIRSTNAME] [PAYMENT_LASTNAME]<br /> [PAYMENT_ADDRESS_1], [PAYMENT_ADDRESS_2]<br /> [PAYMENT_CITY], [PAYMENT_POSTCODE] [PAYMENT_ZONE_NAME]<br /> [PAYMENT_EMAIL]<br /> [PAYMENT_TELEPHONE]<br /> [PAYMENT_FAX]'
		);
		$paymentAddress = str_replace('[PAYMENT_FIRSTNAME]', $row->payment_firstname, $paymentAddress);

		if (self::isFieldPublished('lastname') && $row->payment_lastname != '')
		{
			$paymentAddress = str_replace('[PAYMENT_LASTNAME]', $row->payment_lastname, $paymentAddress);
		}
		else
		{
			$paymentAddress = str_replace('[PAYMENT_LASTNAME]', '', $paymentAddress);
		}

		$paymentAddress = str_replace('[PAYMENT_ADDRESS_1]', $row->payment_address_1, $paymentAddress);

		if (self::isFieldPublished('address_2') && $row->payment_address_2 != '')
		{
			$paymentAddress = str_replace(', [PAYMENT_ADDRESS_2]', ', ' . $row->payment_address_2, $paymentAddress);
			$paymentAddress = str_replace('[PAYMENT_ADDRESS_2]', $row->payment_address_2, $paymentAddress);
		}
		else
		{
			$paymentAddress = str_replace(', [PAYMENT_ADDRESS_2]', '', $paymentAddress);
			$paymentAddress = str_replace('[PAYMENT_ADDRESS_2]', '', $paymentAddress);
		}

		if (self::isFieldPublished('city'))
		{
			$paymentAddress = str_replace('<br /> [PAYMENT_CITY]', '<br /> ' . $row->payment_city, $paymentAddress);
			$paymentAddress = str_replace('[PAYMENT_CITY]', $row->payment_city, $paymentAddress);
		}
		else
		{
			$paymentAddress = str_replace('<br /> [PAYMENT_CITY]', '', $paymentAddress);
			$paymentAddress = str_replace('[PAYMENT_CITY]', '', $paymentAddress);
		}

		if (self::isFieldPublished('postcode') && $row->payment_postcode != '')
		{
			$paymentAddress = str_replace(', [PAYMENT_POSTCODE]', ', ' . $row->payment_postcode, $paymentAddress);
			$paymentAddress = str_replace('[PAYMENT_POSTCODE]', $row->payment_postcode, $paymentAddress);
		}
		else
		{
			$paymentAddress = str_replace(', [PAYMENT_POSTCODE]', '', $paymentAddress);
			$paymentAddress = str_replace('[PAYMENT_POSTCODE]', '', $paymentAddress);
		}

		$paymentAddress = str_replace('[PAYMENT_EMAIL]', $row->payment_email, $paymentAddress);

		if (self::isFieldPublished('telephone') && $row->payment_telephone != '')
		{
			$paymentAddress = str_replace('<br /> [PAYMENT_TELEPHONE]', '<br /> ' . $row->payment_telephone, $paymentAddress);
			$paymentAddress = str_replace('[PAYMENT_TELEPHONE]', $row->payment_telephone, $paymentAddress);
		}
		else
		{
			$paymentAddress = str_replace('<br /> [PAYMENT_TELEPHONE]', '', $paymentAddress);
			$paymentAddress = str_replace('[PAYMENT_TELEPHONE]', '', $paymentAddress);
		}

		if (self::isFieldPublished('fax') && $row->payment_fax != '')
		{
			$paymentAddress = str_replace('<br /> [PAYMENT_FAX]', '<br /> ' . $row->payment_fax, $paymentAddress);
			$paymentAddress = str_replace('[PAYMENT_FAX]', $row->payment_fax, $paymentAddress);
		}
		else
		{
			$paymentAddress = str_replace('<br /> [PAYMENT_FAX]', '', $paymentAddress);
			$paymentAddress = str_replace('[PAYMENT_FAX]', '', $paymentAddress);
		}

		if (self::isFieldPublished('company') && $row->payment_company != '')
		{
			$paymentAddress = str_replace('[PAYMENT_COMPANY]', $row->payment_company, $paymentAddress);
		}
		else
		{
			$paymentAddress = str_replace('[PAYMENT_COMPANY]', '', $paymentAddress);
		}

		if (self::isFieldPublished('company_id') && $row->payment_company_id != '')
		{
			$paymentAddress = str_replace('[PAYMENT_COMPANY_ID]', $row->payment_company_id, $paymentAddress);
		}
		else
		{
			$paymentAddress = str_replace('[PAYMENT_COMPANY_ID]', '', $paymentAddress);
		}

		if (self::isFieldPublished('zone_id') && $row->payment_zone_name != '')
		{
			$paymentAddress = str_replace('[PAYMENT_ZONE_NAME]', $row->payment_zone_name, $paymentAddress);
		}
		else
		{
			$paymentAddress = str_replace('[PAYMENT_ZONE_NAME]', '', $paymentAddress);
		}

		if (self::isFieldPublished('country_id') && $row->payment_country_name != '')
		{
			$paymentAddress = str_replace('[PAYMENT_COUNTRY_NAME]', $row->payment_country_name, $paymentAddress);
		}
		else
		{
			$paymentAddress = str_replace('[PAYMENT_COUNTRY_NAME]', '', $paymentAddress);
		}

		return $paymentAddress;
	}

	/**
	 *
	 * Function to identify if cart mode is available for a specific product or not
	 *
	 * @param   object  $product
	 *
	 * @return boolean
	 */
	public static function isCartMode($product)
	{
		$user       = Factory::getUser();
		$isCartMode = true;

		if (self::isCatalogMode() || $product->product_cart_mode == 'hide')
		{
			$isCartMode = false;
		}
		else
		{
			$productInventory = self::getProductInventory($product);

			if (($product->product_cart_mode == 'registered' && !$user->get('id')) || !self::showPrice(
				) || $product->product_call_for_price || ($product->product_quantity <= 0 && !$productInventory['product_stock_checkout']))
			{
				$isCartMode = false;
			}
		}

		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_productcategories AS b ON (a.id = b.category_id)')
			->where('b.product_id = ' . intval($product->id))
			->where('b.main_category = 1')
			->where(
				'((a.category_cart_mode_customergroups = "") OR (a.category_cart_mode_customergroups IS NULL) OR (a.category_cart_mode_customergroups = "' . $customerGroupId . '") OR (a.category_cart_mode_customergroups LIKE "' . $customerGroupId . ',%") OR (a.category_cart_mode_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.category_cart_mode_customergroups LIKE "%,' . $customerGroupId . '"))'
			);
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$isCartMode = false;
		}

		return $isCartMode;
	}

	/**
	 *
	 * Function to identify if quote mode is available for a specific product or not
	 *
	 * @param   object  $product
	 *
	 * @return boolean
	 */
	public static function isQuoteMode($product)
	{	
		$user			= Factory::getUser();
		$isQuoteMode	= false;
		
		if (!self::isQuoteModeGlobal() || !$product->product_quote_mode)
		{
			$isQuoteMode = false;
		}
		else if ($product->product_quote_mode == 1)
		{
			$isQuoteMode = true;
		}
		else if ($product->product_quote_mode == 2 && $user->get('id') > 0)
		{
			$isQuoteMode = true;
		}
		
		return $isQuoteMode;
	}

	/**
	 *
	 * Function to integrate with iDevAffiliate
	 *
	 * @param   order object $order
	 */
	public static function iDevAffiliate($order)
	{
		$orderNumber = $order->order_number;
		$orderTotal  = $order->total;
		$ipAddress   = $_SERVER['REMOTE_ADDR'];
		$ch          = curl_init();
		curl_setopt(
			$ch,
			CURLOPT_URL,
			self::getSiteUrl() . self::getConfigValue(
				'idevaffiliate_path'
			) . "/sale.php?profile=72198&idev_saleamt=" . $orderTotal . "&idev_ordernum=" . $orderNumber . "&ip_address=" . $ipAddress
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
		curl_close($ch);
	}

	/**
	 *
	 * Function to check if a field is published or not
	 *
	 * @param   string  $fieldName
	 *
	 * @return boolean
	 */
	public static function isFieldPublished($fieldName)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__eshop_fields')
			->where('name = "' . $fieldName . '"')
			->where('published = 1');
		$db->setQuery($query);

		if ($db->loadResult())
		{
			return true;
		}

		return false;
	}

	/**
	 *
	 * Function to send notify to customers when product is available
	 *
	 * @param   int     $numberEmails
	 * @param   string  $bccEmail
	 */
	public static function sendNotify($numberEmails, $bccEmail = null)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, b.product_name')
			->from('#__eshop_notify AS a')
			->innerJoin('#__eshop_productdetails AS b ON a.product_id = b.product_id')
			->innerJoin('#__eshop_products AS c ON (a.product_id = c.id AND c.product_quantity > 0)')
			->where('a.sent_email = 0')
			->where('b.language = a.language')
			->order('a.id');
		$db->setQuery($query, 0, $numberEmails);
		$rows = $db->loadObjectList();

		if (count($rows))
		{
			$mailer = Factory::getMailer();

			if ($bccEmail)
			{
				$mailer->addBcc($bccEmail);
			}

			$sendFrom  = self::getSendFrom();
			$fromName  = $sendFrom['from_name'];
			$fromEmail = $sendFrom['from_email'];

			for ($i = 0; $n = count($rows), $i < $n; $i++)
			{
				$row = $rows[$i];
				// Send email first
				$notifySubject = self::getMessageValue('notify_email_subject', $row->language);
				$notifySubject = str_replace('[PRODUCT_NAME]', $row->product_name, $notifySubject);
				$notifyBody    = self::getNotifyEmailBody($row);
				$notifyBody    = self::convertImgTags($notifyBody);

				try
				{
					$mailer->sendMail($fromEmail, $fromName, $row->notify_email, $notifySubject, $notifyBody, 1);
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}

				$mailer->clearAddresses();

				$query->clear()
					->update('#__eshop_notify')
					->set('sent_email = 1')
					->set('sent_date = ' . $db->quote(gmdate('Y-m-d H:i:s')))
					->where('id = ' . (int) $row->id);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 *
	 * Function to send reminder to store's admin when products nearly out of stock
	 *
	 * @param   int     $numberProducts
	 * @param   string  $receivedEmails
	 */
	public static function sendReminder($numberProducts, $receivedEmails)
	{
		$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$query->select('a.id, a.product_quantity, b.product_name')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('b.language = "' . $langCode . '"')
			->where(
				'((a.product_inventory_global = 1 AND ' . self::getConfigValue(
					'threshold',
					0
				) . ' > 0 AND a.product_quantity <= ' . self::getConfigValue(
					'threshold',
					0
				) . ') OR (a.product_inventory_global = 0 AND a.product_threshold > 0 AND a.product_quantity <= a.product_threshold))'
			)
			->where('(a.product_threshold_notify IS NULL OR a.product_threshold_notify != 1)');
		$db->setQuery($query, 0, $numberProducts);
		$products = $db->loadObjectList();

		if (count($products))
		{
			$mailer         = Factory::getMailer();
			$sendFrom       = self::getSendFrom();
			$fromName       = $sendFrom['from_name'];
			$fromEmail      = $sendFrom['from_email'];
			$receivedEmails = str_replace(' ', '', $receivedEmails);
			$receivedEmails = explode(',', $receivedEmails);

			if (!count($receivedEmails))
			{
				if (self::getConfigValue('email'))
				{
					$receivedEmails = [self::getConfigValue('email')];
				}
				else
				{
					$receivedEmails = [$fromEmail];
				}
			}

			$reminderSubject = self::getMessageValue('reminder_email_subject');
			$reminderBody    = self::getReminderEmailBody($products);

			for ($i = 0; $n = count($receivedEmails), $i < $n; $i++)
			{
				if ($receivedEmails[$i] != '')
				{
					$mailer->clearAllRecipients();

					try
					{
						$mailer->sendMail($fromEmail, $fromName, $receivedEmails[$i], $reminderSubject, $reminderBody, 1);
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
					}
				}
			}

			//Update the threshold notify
			$productsIdArr = [];

			foreach ($products as $product)
			{
				$productsIdArr[] = $product->id;
			}

			$query->clear()
				->update('#__eshop_products')
				->set('product_threshold_notify = 1')
				->where('id IN (' . implode(',', $productsIdArr) . ')');
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 *
	 * Function to get notify email body
	 *
	 * @param   stdClass  $row
	 *
	 * @return string
	 *
	 */
	public static function getNotifyEmailBody($row)
	{
		$notifyEmailBody          = self::getMessageValue('notify_email', $row->language);
		$replaces                 = [];
		$replaces['product_name'] = $row->product_name;
		$replaces['product_link'] = Route::_(
			Uri::root() . EShopRoute::getProductRoute($row->product_id, self::getProductCategory($row->product_id), $row->language)
		);

		foreach ($replaces as $key => $value)
		{
			$key             = strtoupper($key);
			$notifyEmailBody = str_replace("[$key]", $value, $notifyEmailBody);
		}

		return $notifyEmailBody;
	}

	/**
	 *
	 * Function to get reminder email body
	 *
	 * @param   array  $products
	 *
	 * @return string
	 */
	public static function getReminderEmailBody($products)
	{
		$reminderEmailBody      = self::getMessageValue('reminder_email');
		$replaces               = [];
		$replaces['STORE_NAME'] = self::getConfigValue('store_name');
		$productsList           = '';

		for ($i = 0; $n = count($products), $i < $n; $i++)
		{
			$product      = $products[$i];
			$productsList .= ($i + 1) . '. <a href="' . Route::_(
					EShopRoute::getProductRoute($product->id, self::getProductCategory($product->id))
				) . '">' . $product->product_name . '</a> (' . Text::_('ESHOP_QUANTITY') . ': ' . $product->product_quantity . ')';
		}

		$replaces['PRODUCTS_LIST'] = $productsList;

		foreach ($replaces as $key => $value)
		{
			$key               = strtoupper($key);
			$reminderEmailBody = str_replace("[$key]", $value, $reminderEmailBody);
		}

		return $reminderEmailBody;
	}

	/**
	 *
	 * Function to check if a product is available or not
	 *
	 * @param   int  $productId
	 *
	 * @return boolean
	 */
	public static function isAvailableProduct($productId)
	{
		$product = self::getProduct($productId);

		if (is_object($product) && self::isCartMode($product) && $product->published)
		{
			return true;
		}

		return false;
	}

	/**
	 *
	 * Function to get installed version
	 * @return string
	 */
	public static function getInstalledVersion()
	{
		return '4.0.2';
	}

	/**
	 *
	 * Function generate watermark for photo
	 *
	 * @param   string  $imagePath
	 */
	public static function generateWatermarkImage($imagePath)
	{
		$info           = pathinfo($imagePath);
		$extension      = $info['extension'];
		$watermarkImage = substr($imagePath, 0, strrpos($imagePath, '.')) . '-watermark.' . $extension;
		$config         = self::getConfig();

		if ($config->recreate_watermark_images)
		{
			$watermarkType = $config->watermark_type;

			switch ($watermarkType)
			{
				case "1":
					$customText = $config->custom_text;
					self::processTextWatermark($imagePath, $customText, $watermarkImage);
					break;
				case "2":
					$watermarkPhoto = $config->watermark_photo;

					if ($watermarkPhoto != "" && file_exists(JPATH_ROOT . "/images/" . $watermarkPhoto))
					{
						self::processPhotoWatermark($imagePath, $watermarkPhoto, $watermarkImage);
					}

					break;
			}
		}

		$watermarkImageArr = explode('/', $watermarkImage);

		return $watermarkImageArr[count($watermarkImageArr) - 1];
	}

	/**
	 *
	 * Function to process watermark with photo type
	 *
	 * @param   string  $sourceFile
	 * @param   string  $tempPhoto
	 * @param   string  $destinationFile
	 */
	public static function processPhotoWatermark($sourceFile, $tempPhoto, $destinationFile)
	{
		$config = self::getConfig();
		[$sw, $sh] = getimagesize(JPATH_ROOT . "/images/" . $tempPhoto);
		$tempPhotoArr  = explode(".", $tempPhoto);
		$sourceFileArr = explode(".", $sourceFile);
		$sourceExt     = strtolower($sourceFileArr[count($sourceFileArr) - 1]);
		$ext           = strtolower($tempPhotoArr[count($tempPhotoArr) - 1]);

		switch ($ext)
		{
			case "jpg":
				$p = imagecreatefromjpeg(JPATH_ROOT . "/images/" . $tempPhoto);
				break;
			case "png":
				$p = imagecreatefrompng(JPATH_ROOT . "/images/" . $tempPhoto);
				break;
			case "gif":
				$p = imagecreatefromgif(JPATH_ROOT . "/images/" . $tempPhoto);
				break;
		}

		[$width, $height] = getimagesize($sourceFile);
		$image = imagecreatetruecolor($sw, $sh);
		imagealphablending($image, false);

		switch ($sourceExt)
		{
			case "jpg":
				$image = imagecreatefromjpeg($sourceFile);
				break;
			case "png":
				$image = imagecreatefrompng($sourceFile);
				break;
			case "gif":
				$image = imagecreatefromgif($sourceFile);
				break;
		}

		$image_p = imagecreatetruecolor($width, $height);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width, $height);
		$watermarkPosition = $config->watermark_position;
		$matrix_width3     = round($width / 3);
		$matrix_height3    = round($height / 3);
		$matrix_width2     = round($width / 2);
		$matrix_height2    = round($height / 2);

		switch ($watermarkPosition)
		{
			case "1":
				$w = 20;
				$h = 20;
				break;
			case "2":
				$w = $matrix_width2 - $sw / 2;
				$h = 20;
				break;
			case "3":
				$w = $matrix_width3 * 3 - 20 - $sw;
				$h = 20;
				break;
			case "4":
				$w = $matrix_width3 * 3 - 20 - $sw;
				$h = $matrix_height2 - $sh / 2;
				break;
			case "5":
				$w = $matrix_width2 - $sw / 2;
				$h = $matrix_height2 - $sh / 2;
				break;
			case "6":
				$w = 20;
				$h = $matrix_height2 - $sh / 2;
				break;
			case "7":
				$w = $matrix_width3 * 3 - 20 - $sw;
				$h = $matrix_height3 * 3 - 20 - $sh;
				break;
			case "8":
				$w = $matrix_width2 - $sw / 2;
				$h = $matrix_height3 * 3 - 20 - $sh;
				break;
			case "9":
				$w = 20;
				$h = $matrix_height3 * 3 - 20 - $sh;
				break;
		}

		imagecopy($image_p, $p, $w, $h, 0, 0, $sw, $sh);
		imagesavealpha($image_p, true);

		switch ($sourceExt)
		{
			case "jpg":
				if ($destinationFile != "")
				{
					imagejpeg($image_p, $destinationFile, 100);
				}
				else
				{
					header('Content-Type: image/jpeg');
					imagejpeg($image_p, null, 100);
				}
				break;
			case "png":
				if ($destinationFile != "")
				{
					imagejpeg($image_p, $destinationFile, 100);
				}
				else
				{
					header('Content-Type: image/jpeg');
					imagejpeg($image_p, null, 100);
				}
				break;
			case "gif":
				if ($destinationFile != "")
				{
					imagejpeg($image_p, $destinationFile);
				}
				else
				{
					header('Content-Type: image/gif');
					imagegif($image_p, null, 100);
				}
				break;
		}

		imagedestroy($image);
		imagedestroy($image_p);
	}

	/**
	 *
	 * Function to process watermark with text type
	 *
	 * @param   string  $sourceFile
	 * @param   string  $watermarkText
	 * @param   string  $destinationFile
	 */
	public static function processTextWatermark($sourceFile, $watermarkText, $destinationFile)
	{
		$config = self::getConfig();
		[$width, $height] = getimagesize($sourceFile);
		$image_p       = imagecreatetruecolor($width, $height);
		$sourceFileArr = explode(".", $sourceFile);
		$sourceExt     = strtolower($sourceFileArr[count($sourceFileArr) - 1]);

		switch ($sourceExt)
		{
			case "jpg":
				$image = imagecreatefromjpeg($sourceFile);
				break;
			case "png":
				$image = imagecreatefrompng($sourceFile);
				break;
			case "gif":
				$image = imagecreatefromgif($sourceFile);
				break;
		}

		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width, $height);
		$watermarkColor = $config->watermark_color;
		$watermarkArr   = explode(",", $watermarkColor);
		$textColor      = imagecolorallocate($image_p, $watermarkArr[0], $watermarkArr[1], $watermarkArr[2]);
		$watermarkFont  = $config->watermark_font;

		if ($watermarkFont == "")
		{
			$watermarkFont = "arial.ttf";
		}

		$font              = JPATH_COMPONENT . '/tcpdf/fonts/' . $watermarkFont;
		$watermarkFontsize = $config->watermark_fontsize;
		$matrix_width3     = round($width / 3);
		$matrix_height3    = round($height / 3);
		$matrix_width2     = round($width / 2);
		$matrix_height2    = round($height / 2);
		$watermarkPosition = $config->watermark_position;

		switch ($watermarkPosition)
		{
			case "1":
				$w = 20;
				$h = 20 + $watermarkFontsize;
				break;
			case "2":
				$w = $matrix_width2;
				$h = 20 + $watermarkFontsize;
				break;
			case "3":
				$w = $matrix_width3 * 2 - 20;
				$h = 20 + $watermarkFontsize;
				break;
			case "4":
				$w = $matrix_width3 * 2 - 20;
				$h = $matrix_height2;
				break;
			case "5":
				$p = imagettfbbox($watermarkFontsize, 0, $font, $watermarkText);

				$txt_width = $p[2] - $p[0];
				$w         = $matrix_width2;
				$w         = $matrix_width2 - round($txt_width / 2);
				$h         = $matrix_height2;
				break;
			case "6":
				$w = 20;
				$h = $matrix_height2;
				break;
			case "7":
				$w = $matrix_width3 * 2 - 20;
				$h = $matrix_height3 * 3 - 10 - $watermarkFontsize;
				break;
			case "8":
				$w = $matrix_width2;
				$h = $matrix_height3 * 3 - 10 - $watermarkFontsize;
				break;
			case "9":
				$w = 20;
				$h = $matrix_height3 * 3 - 10 - $watermarkFontsize;
				break;
		}

		imagettftext($image_p, $watermarkFontsize, 0, $w, $h, $textColor, $font, $watermarkText);

		if ($destinationFile != "")
		{
			imagejpeg($image_p, $destinationFile, intval($config->images_opacity));
		}
		else
		{
			header('Content-Type: image/jpeg');
			imagejpeg($image_p, null, intval($config->images_opacity));
		}

		imagedestroy($image);
		imagedestroy($image_p);
	}

	/**
	 * Decode custom fields data and store it for each product record
	 *
	 * @param $items
	 */
	public static function prepareCustomFieldsData($items, $getEmptyValue = false)
	{
		$xml          = simplexml_load_file(JPATH_ROOT . '/components/com_eshop/fields.xml');
		$fields       = $xml->fields->fieldset->children();
		$customFields = [];

		foreach ($fields as $field)
		{
			$name                  = $field->attributes()->name;
			$label                 = Text::_($field->attributes()->label);
			$customFields["$name"] = $label;
		}

		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item      = $items[$i];
			$params    = new Registry($item->custom_fields ?? '{}');
			$paramData = [];

			foreach ($customFields as $name => $label)
			{
				if (Multilanguage::isEnabled() && count(self::getLanguages()) > 1)
				{
					$langCode = Factory::getLanguage()->getTag();

					if (!strpos($name, $langCode))
					{
						continue;
					}
				}

				$fieldValue = $params->get($name);

				if (is_array($fieldValue))
				{
					$fieldValue = implode(', ', $fieldValue);
				}

				if (($getEmptyValue) || (!$getEmptyValue && $fieldValue != ''))
				{
					$paramData[$name]['title'] = $label;
					$paramData[$name]['value'] = $fieldValue;
				}

				if (!property_exists($item, $name))
				{
					$item->{$name} = $fieldValue;
				}
			}
			$item->paramData = $paramData;
		}
	}

	/**
	 *
	 * Function to get send from name and from email
	 * @return array
	 */
	public static function getSendFrom()
	{
		static $sendFromArr;

		if (is_null($sendFromArr))
		{
			$sendFrom = self::getConfigValue('send_from', 'global');

			if ($sendFrom == 'store' && self::getConfigValue('store_name') != '' && self::getConfigValue('email') != '')
			{
				$sendFromArr = ['from_name' => self::getConfigValue('store_name'), 'from_email' => self::getConfigValue('email')];
			}
			else
			{
				$jconfig     = new JConfig();
				$sendFromArr = ['from_name' => $jconfig->fromname, 'from_email' => $jconfig->mailfrom];
			}
		}

		return $sendFromArr;
	}

	/**
	 *
	 * Function to check if a specific product is belong to a specific category or not
	 *
	 * @param   int  $productId
	 * @param   int  $catId
	 *
	 * @return boolean
	 */
	public static function isProductCategory($productId, $catId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__eshop_productcategories')
			->where('product_id = ' . intval($productId))
			->where('category_id = ' . intval($catId));
		$db->setQuery($query);
		if ($db->loadResult())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 *
	 * Function to get the Geozone Postcode status base on geozone ID and postcode
	 *
	 * @param   int     $geozoneId
	 * @param   string  $postcode
	 *
	 * @return boolean
	 */
	public static function getGzpStatus($geozoneId, $postcode)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_geozonepostcodes')
			->where('geozone_id = ' . intval($geozoneId));
		$db->setQuery($query);
		$gzpRows   = $db->loadObjectList();
		$gzpStatus = false;

		if (!count($gzpRows))
		{
			$gzpStatus = true;
		}
		else
		{
			foreach ($gzpRows as $gzpRow)
			{
				if ($gzpRow->start_postcode <= $postcode && $gzpRow->end_postcode >= $postcode)
				{
					$gzpStatus = true;
					break;
				}
			}
		}

		return $gzpStatus;
	}

	/**
	 *
	 * Function to check if a specific product has required option or not
	 *
	 * @param   int  $productId
	 *
	 * @return boolean - true if product has required option and false if vice versa
	 */
	public static function isRequiredOptionProduct($productId)
	{
		$productOptions = self::getProductOptions($productId, Factory::getLanguage()->getTag());

		for ($i = 0; $n = count($productOptions), $i < $n; $i++)
		{
			$productOption = $productOptions[$i];

			if ($productOption->required)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 *
	 * Function to get conversion tracking code for a specific order
	 *
	 * @param   order object $row
	 *
	 * @return string
	 */
	public static function getConversionTrackingCode($row)
	{
		$conversionTrackingCode = self::getConfigValue('conversion_tracking_code');

		if ($conversionTrackingCode != '')
		{
			//Prepare replaces array elements
			$replaces                    = [];
			$replaces['store_name']      = self::getConfigValue('store_name');
			$replaces['order_id']        = $row->id;
			$replaces['order_number']    = $row->order_number;
			$replaces['transaction_id']  = $row->transaction_id;
			$replaces['shipping_amount'] = self::getOrderTotalValue($row->id, 'shipping');
			$replaces['tax_amount']      = self::getOrderTotalValue($row->id, 'tax');
			$replaces['currency_code']   = $row->currency_code;
			$replaces['sub_total']       = self::getOrderTotalValue($row->id, 'sub_total');
			$replaces['total']           = self::getOrderTotalValue($row->id, 'total');

			foreach ($replaces as $key => $value)
			{
				$key                    = strtoupper($key);
				$conversionTrackingCode = str_replace("[$key]", $value, $conversionTrackingCode);
			}
		}

		return $conversionTrackingCode;
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
	 * Method to get server time from GMT time
	 *
	 * @param   string  $time
	 * @param   string  $format
	 *
	 * @return string
	 */
	public static function getServerTimeFromGMTTime($time = 'now', $format = 'Y-m-d H:i:s')
	{
		$gmtTz  = new DateTimeZone('GMT');
		$userTz = new DateTimeZone(Factory::getApplication()->get('offset', 'GMT'));
		$date   = new DateTime($time, $gmtTz);
		$date->setTimezone($userTz);

		return $date->format($format);
	}

	/**
	 *
	 * Method to get article object
	 *
	 * @param   integer  $articleId
	 *
	 * @return article object
	 */
	public static function getArticle($articleId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, catid')
			->from('#__content')
			->where('id = ' . intval($articleId));
		$db->setQuery($query);
		$article = $db->loadObject();

		return $article;
	}

	/**
	 *
	 * Function to get customer group name of a specific order
	 *
	 * @param   order object $row
	 *
	 * @return string customer group name
	 */
	public static function getOrderCustomergroupName($row)
	{
		$customergroupName = '';
		if ($row->customergroup_id > 0)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('customergroup_name')
				->from('#__eshop_customergroupdetails')
				->where('customergroup_id = ' . intval($row->customergroup_id))
				->where('language = ' . $db->quote($row->language));
			$db->setQuery($query);
			$customergroupName = $db->loadResult();
		}

		return $customergroupName;
	}

	/**
	 * Get User IP address
	 *
	 * @return mixed
	 */
	public static function getUserIp()
	{
		if (self::getConfigValue('collect_user_ip', 0))
		{
			return $_SERVER['REMOTE_ADDR'] ?? '';
		}
		else
		{
			return '';
		}
	}

	/**
	 *
	 * Function to get cart success message for a specific product
	 *
	 * @param   int     $productId
	 * @param   string  $productName
	 */
	public static function getCartSuccessMessage($productId, $productName)
	{
		$viewProductLink = Route::_(EShopRoute::getProductRoute($productId, EShopHelper::getProductCategory($productId)));
		$viewCartLink    = Route::_(EShopRoute::getViewRoute('cart'));
		$message         = sprintf(Text::_('ESHOP_ADD_TO_CART_SUCCESS_MESSAGE_NEW'), $viewProductLink, $productName, $viewCartLink);

		return $message;
	}

	/**
	 *
	 * Function to get cart success message for a specific product
	 *
	 * @param   stdClass  $product
	 * @param   string    $productUrl
	 *
	 * @return string
	 */
	public static function getAddToCartSuccessMessage($product, $productUrl)
	{
		$viewCartLink = Route::_(EShopRoute::getViewRoute('cart'));

		return sprintf(Text::_('ESHOP_ADD_TO_CART_SUCCESS_MESSAGE_NEW'), $productUrl, $product->product_name, $viewCartLink);
	}

	/**
	 *
	 * Function to check if invoice is available for a specific order or not
	 *
	 * @param   order object $row
	 * @param   int  $invoiceFor  - 0 is for customer, 1 is for admin
	 * @param        $boolean     $forSend - true to check the send setting
	 */
	public static function isInvoiceAvailable($row, $invoiceFor, $forSend)
	{
		if (!self::getConfigValue('invoice_enable'))
		{
			return false;
		}
		else
		{
			if (self::getConfigValue('invoice_status_ids') != '')
			{
				$invoiceStatusIds = explode(',', self::getConfigValue('invoice_status_ids'));
			}
			else
			{
				$invoiceStatusIds = [self::getConfigValue('complete_status_id')];
			}

			if (self::getConfigValue('always_generate_invoice') || (!self::getConfigValue('always_generate_invoice') && in_array(
						$row->order_status_id,
						$invoiceStatusIds
					)))
			{
				if ($invoiceFor == '1' && (!$forSend || ($forSend && self::getConfigValue('send_invoice_to_admin'))))
				{
					return true;
				}

				if ($invoiceFor == '0' && (!$forSend || ($forSend && self::getConfigValue('send_invoice_to_customer'))))
				{
					return true;
				}
			}
		}
	}

	/* Get link to article, multilingual association is supported
	 *
	 * @param   int  $articleId
	 *
	 * @return string
	 */
	public static function getArticleUrl($articleId)
	{
		JLoader::register('ContentHelperRoute', JPATH_ROOT . '/components/com_content/helpers/route.php');

		if (Multilanguage::isEnabled())
		{
			$associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $articleId);
			$langCode     = Factory::getLanguage()->getTag();

			if (isset($associations[$langCode]))
			{
				$article = $associations[$langCode];
			}
		}

		if (!isset($article))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, catid')
				->from('#__content')
				->where('id = ' . (int) $articleId);
			$db->setQuery($query);
			$article = $db->loadObject();
		}

		$link = ContentHelperRoute::getArticleRoute($article->id, $article->catid) . '&tmpl=component&format=html';

		return $link;
	}

	/**
	 *
	 * Function to get checkout terms link
	 */
	public static function getCheckoutTermsLink()
	{
		$checkoutTerms     = EShopHelper::getConfigValue('checkout_terms');
		$checkoutTermsLink = '';

		if ($checkoutTerms)
		{
			JLoader::register('ContentHelperRoute', JPATH_ROOT . '/components/com_content/helpers/route.php');

			if (Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
			{
				$associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $checkoutTerms);
				$langCode     = Factory::getLanguage()->getTag();

				if (isset($associations[$langCode]))
				{
					$article = EShopHelper::getArticle($associations[$langCode]->id);
				}
				else
				{
					$article = EShopHelper::getArticle($checkoutTerms);
				}
			}
			else
			{
				$article = EShopHelper::getArticle($checkoutTerms);
			}

			if (is_object($article))
			{
				$checkoutTermsLink = ContentHelperRoute::getArticleRoute($article->id, $article->catid) . '&tmpl=component&format=html';
			}
		}

		return $checkoutTermsLink;
	}

	/**
	 *
	 * Function to get privacy policy article link
	 */
	public static function getPrivacyPolicyArticleLink()
	{
		$privacyPolicyArticle     = EShopHelper::getConfigValue('privacy_policy_article');
		$privacyPolicyArticleLink = '';

		if ($privacyPolicyArticle)
		{
			JLoader::register('ContentHelperRoute', JPATH_ROOT . '/components/com_content/helpers/route.php');

			if (Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
			{
				$associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $privacyPolicyArticle);
				$langCode     = Factory::getLanguage()->getTag();

				if (isset($associations[$langCode]))
				{
					$article = EShopHelper::getArticle($associations[$langCode]->id);
				}
				else
				{
					$article = EShopHelper::getArticle($privacyPolicyArticle);
				}
			}
			else
			{
				$article = EShopHelper::getArticle($privacyPolicyArticle);
			}

			if (is_object($article))
			{
				$privacyPolicyArticleLink = ContentHelperRoute::getArticleRoute($article->id, $article->catid) . '&tmpl=component&format=html';
			}
		}

		return $privacyPolicyArticleLink;
	}

	/**
	 *
	 * Function to get products which are bought by other customers when they bought the current product.
	 *
	 * @param   int  $productId
	 *
	 * @return products object list
	 */
	public static function getAlsoBoughtProducts($productId, $langCode = '')
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('op.order_id')
			->from('#__eshop_orderproducts AS op')
			->where('op.order_id > 0')
			->where('op.product_id = ' . intval($productId));

		$db->setQuery($query);
		$orderIds = $db->loadColumn();

		if ($langCode == '')
		{
			$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$products = [];

		if (count($orderIds))
		{
			$query->clear();
			$query->select(
				'DISTINCT p.*, pd.product_name, pd.product_alias, pd.product_desc, pd.product_short_desc, pd.product_page_title, pd.product_page_heading, pd.product_alt_image, pd.meta_key, pd.meta_desc'
			)
				->from('#__eshop_products AS p')
				->innerJoin('#__eshop_productdetails AS pd ON (p.id = pd.product_id)')
				->innerJoin('#__eshop_orderproducts AS op ON (pd.product_id = op.product_id)')
				->where('p.published = 1')
				->where('op.order_id IN (' . implode(',', $orderIds) . ')')
				->where('pd.product_id != ' . intval($productId))
				->where('pd.language = ' . $db->quote($langCode))
				->order('p.ordering');
			$db->setQuery($query);
			$products = $db->loadObjectList();
		}

		return $products;
	}

	/**
	 *
	 * Function to check if product sku is existed or not
	 *
	 * @param   string  $productSku
	 * @param   int     $productId
	 */
	public static function isExistedProductSku($productSku, $productId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__eshop_products')
			->where('LOWER(product_sku) = ' . $db->quote(strtolower($productSku)));

		if ($productId > 0)
		{
			$query->where('id != ' . intval($productId));
		}

		$db->setQuery($query);

		if ($db->loadResult() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 *
	 * Function to count number attributes of a specific attribute group
	 * @return array
	 */
	public static function numberAttributes($attributeGroupId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)')
			->from('#__eshop_attributes')
			->where('attributegroup_id = ' . intval($attributeGroupId));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to get default products sorting value for a specific category
	 *
	 *
	 * @return string
	 * @var int|stdClass $category
	 *
	 */
	public static function getDefaultSortingCategory($category)
	{
		if (is_object($category))
		{
			$params = new Registry($category->params);
		}
		else
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('params')
				->from('#__eshop_categories')
				->where('id = ' . intval($category));
			$db->setQuery($query);
			$params = new Registry($db->loadResult());
		}

		if ($params->get('default_sorting') != '')
		{
			return $params->get('default_sorting');
		}

		return self::getConfigValue('default_sorting');
	}

	/**
	 *
	 * Function to get order options for a specific product
	 *
	 * @param   int  $productId
	 */
	public static function getProductOrderOptions($productId, $dateStart, $dateEnd, $orderStatusId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.option_name, a.option_value, a.sku, SUM(a.quantity) AS total_quantity, SUM(a.quantity * a.price) AS total_price')
			->from('#__eshop_orderoptions AS a')
			->innerJoin('#__eshop_orders AS b ON (a.order_id = b.id)')
			->where('a.product_id = ' . $productId)
			->where('a.quantity > 0');

		if (!empty($dateStart))
		{
			$query->where('b.created_date >= ' . $db->quote($dateStart));
		}

		if (!empty($dateEnd))
		{
			$query->where('b.created_date <= ' . $db->quote($dateEnd));
		}

		if ($orderStatusId > 0)
		{
			$query->where('b.order_status_id = ' . (int) $orderStatusId);
		}

		$query->group('a.option_value');
		$query->order('a.option_value');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Execute queries from the given file
	 *
	 * @param   string  $file
	 */
	public static function executeSqlFile($file)
	{
		$db      = Factory::getDbo();
		$sql     = file_get_contents($file);
		$queries = $db->splitSql($sql);

		if (count($queries))
		{
			foreach ($queries as $query)
			{
				$query = trim($query);

				if ($query != '' && $query[0] != '#')
				{
					$db->setQuery($query)
						->execute();
				}
			}
		}
	}

	/**
	 *
	 * Function to remove images of category/manufacturer
	 *
	 * @param   int     $id
	 * @param   string  $element
	 */
	public static function removeImages($id, $element)
	{
		$db            = Factory::getDbo();
		$query         = $db->getQuery(true);
		$pluralElement = EShopInflector::pluralize($element);

		//Get image name first
		$query->select($element . '_image')
			->from('#__eshop_' . $pluralElement)
			->where('id = ' . intval($id));
		$db->setQuery($query);
		$imageName = $db->loadResult();

		$imagePath = JPATH_ROOT . '/media/com_eshop/' . $pluralElement;

		//Delete the image if it is existed
		if ($imageName != '' && is_file($imagePath . '/' . $imageName))
		{
			File::delete($imagePath . '/' . $imageName);
		}

		if ($element == 'category' || $element == 'manufacturer')
		{
			$width  = self::getConfigValue('image_' . $element . '_width');
			$height = self::getConfigValue('image_' . $element . '_height');
			self::removeResizedImage($imageName, $imagePath, $width, $height);
		}
	}

	/**
	 *
	 * Function to remove resized image of a specific image
	 *
	 * @param   string  $filename
	 * @param   string  $imagePath
	 * @param   int     $width
	 * @param   int     $height
	 *
	 */
	public static function removeResizedImage($filename, $imagePath, $width, $height)
	{
		$name = File::stripExt($filename);
		$ext  = EShopHelper::getFileExt($filename);

		$defaultResizeImage = $name . '-100x100.' . $ext;
		$resizeImage        = $name . '-' . $width . 'x' . $height . '.' . $ext;
		$cropImage          = $name . '-cr-' . $width . 'x' . $height . '.' . $ext;
		$maxImage           = $name . '-max-' . $width . 'x' . $height . '.' . $ext;

		if (is_file($imagePath . '/resized/' . $defaultResizeImage))
		{
			File::delete($imagePath . '/resized/' . $defaultResizeImage);
		}

		if (is_file($imagePath . '/resized/' . $resizeImage))
		{
			File::delete($imagePath . '/resized/' . $resizeImage);
		}

		if (is_file($imagePath . '/resized/' . $cropImage))
		{
			File::delete($imagePath . '/resized/' . $cropImage);
		}

		if (is_file($imagePath . '/resized/' . $maxImage))
		{
			File::delete($imagePath . '/resized/' . $maxImage);
		}
	}

	/**
	 * Export the given data to Excel
	 *
	 * @param   array   $fields
	 * @param   array   $rows
	 * @param   string  $filename
	 * @param   array   $headers
	 * @param   string  $exportFormat
	 *
	 * @return string
	 */
	public static function excelExport($fields, $rows, $filename, $headers = [], $exportFormat = 'csv', $filePath = '')
	{
		if (empty($headers))
		{
			$headers = $fields;
		}

		if (empty($filePath))
		{
			$filePath = JPATH_ROOT . '/media/com_eshop/files/';
		}

		$filename = File::stripExt($filename);

		if ($exportFormat == 'csv')
		{
			$writer = WriterEntityFactory::createCSVWriter();
			$writer->setFieldDelimiter(self::getConfigValue('csv_delimiter', ','));

			$filePath .= $filename . '.csv';
		}
		else
		{
			$writer = WriterEntityFactory::createXLSXWriter();

			$filePath .= $filename . '.xlsx';
		}

		//Delete the file if exist
		if (is_file($filePath))
		{
			File::delete($filePath);
		}

		$writer->openToFile($filePath);

		if (empty($headers))
		{
			$headers = $fields;
		}

		// Write header columns
		$writer->addRow(WriterEntityFactory::createRowFromArray($headers));

		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$data = [];

				foreach ($fields as $field)
				{
					if (property_exists($row, $field))
					{
						$data[] = $row->{$field};
					}
					else
					{
						$data[] = '';
					}
				}

				$writer->addRow(WriterEntityFactory::createRowFromArray($data));
			}
		}

		$writer->close();

		return $filePath;
	}

	/**
	 * Get data from excel file using Spout library
	 *
	 * @param $file
	 * @param $filename
	 *
	 * @return array
	 */
	public static function getDataFromFile($file, $filename = '')
	{
		// Use spout to get data
		try
		{
			$reader = ReaderEntityFactory::createReaderFromFile($filename);

			if ($reader instanceof Reader)
			{
				$reader->setFieldDelimiter(self::getConfigValue('csv_delimiter', ','));
			}

			$reader->open($file);
			$headers = [];
			$rows    = [];
			$count   = 0;

			foreach ($reader->getSheetIterator() as $sheet)
			{
				foreach ($sheet->getRowIterator() as $row)
				{
					$cells = $row->getCells();

					if ($count === 0)
					{
						foreach ($cells as $cell)
						{
							$headers[] = $cell->getValue();
						}

						$count++;
					}
					else
					{
						$cellIndex = 0;
						$row       = [];

						foreach ($cells as $cell)
						{
							$row[$headers[$cellIndex++]] = $cell->getValue();
						}

						$rows[] = $row;
					}
				}
			}

			$reader->close();

			return $rows;
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return [];
		}
	}

	/**
	 *
	 * Function to get Users Menu Item String
	 */
	public static function getUsersMenuItemStr()
	{
		$usersMenuItem    = self::getConfigValue('users_menu_item', 0);
		$usersMenuItemStr = '';

		if ($usersMenuItem > 0)
		{
			$usersMenuItemStr = '&Itemid=' . $usersMenuItem;
		}

		return $usersMenuItemStr;
	}

	/**
	 *
	 * Function to check if quantity box should be showed for a product or not
	 *
	 * @param   stdClass|int  $productId
	 */
	public static function showProductQuantityBox($productId, $page = 'show_quantity_box_in_product_page')
	{
		// This is the case category object is passed to the method call
		if (is_object($productId))
		{
			$params = new Registry($productId->params);
		}
		else
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('b.params')
				->from('#__eshop_productcategories AS a')
				->innerJoin('#__eshop_categories AS b ON (a.category_id = b.id)')
				->where('a.product_id = ' . intval($productId))
				->where('a.main_category = 1')
				->where('b.published = 1');
			$db->setQuery($query);
			$params = new Registry($db->loadResult());
		}

		$showQuantityBox = self::getConfigValue($page, 1);

		if ($params->get('quantity_box', '') == '1')
		{
			$showQuantityBox = true;
		}
		else
		{
			if ($params->get('quantity_box', '') == '0')
			{
				$showQuantityBox = false;
			}
		}

		return $showQuantityBox;
	}

	/**
	 *
	 * Function to check catalog mode
	 */
	public static function isCatalogMode()
	{
		$catalogMode = self::getConfigValue('catalog_mode');

		if ($catalogMode == '1')
		{
			return true;
		}

		if ($catalogMode == '0')
		{
			return false;
		}

		$userId = Factory::getUser()->get('id');

		if ($catalogMode == '2' && $userId)
		{
			return true;
		}

		if ($catalogMode == '3' && !$userId)
		{
			return true;
		}

		return false;
	}
	
	/**
	 *
	 * Function to check quote mode
	 */
	public static function isQuoteModeGlobal()
	{
		$quoteMode = self::getConfigValue('quote_cart_mode');
	
		if ($quoteMode == '1')
		{
			return true;
		}
	
		if ($quoteMode == '0')
		{
			return false;
		}
	
		$userId = Factory::getUser()->get('id');
	
		if ($quoteMode == '2' && $userId)
		{
			return true;
		}
	
		return false;
	}

	/**
	 *
	 * Function to send abandon cart reminder
	 *
	 * @param   string  $orderingName  - 1st, 2nd or 3rd
	 */
	public static function sendAbandonCartReminder($numberCustomers, $orderingName)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($orderingName == '1st')
		{
			$defaultValue = '1';
		}
		elseif ($orderingName == '2nd')
		{
			$defaultValue = '24';
		}
		else
		{
			$defaultValue = '72';
		}

		$timezone = Factory::getApplication()->get('offset');
		$date     = Factory::getDate('now', $timezone);
		$date->modify('-' . intval(self::getConfigValue('send_' . $orderingName . '_abandon_cart_reminder_after', $defaultValue)) . ' hours');
		$date = $date->toSql(false);

		$query->clear()
			->select('id, customer_id')
			->from('#__eshop_carts')
			->where('is_' . $orderingName . '_sent = 0')
			->where('modified_date <= ' . $db->quote($date));
		$db->setQuery($query, 0, $numberCustomers);
		$rows = $db->loadAssocList();

		if (isset($rows) && count($rows) > 0)
		{
			$mailer    = Factory::getMailer();
			$sendFrom  = self::getSendFrom();
			$fromName  = $sendFrom['from_name'];
			$fromEmail = $sendFrom['from_email'];
			$cartIdArr = [];

			foreach ($rows as $row)
			{
				$cartIdArr[] = $row['id'];
				$customerId  = $row['customer_id'];
				$customer    = self::getCustomer($customerId);

				if (is_object($customer))
				{
					$abandonCartReminderSubject = self::getMessageValue($orderingName . '_abandon_cart_email_subject');
					$abandonCartReminderBody    = self::getAbandonCartEmailBody($orderingName);

					//Send abandon cart reminder
					$mailer->clearAllRecipients();

					try
					{
						$mailer->sendMail($fromEmail, $fromName, $customer->email, $abandonCartReminderSubject, $abandonCartReminderBody, 1);
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
					}
				}
			}

			//Update to set the sent mail field to yes
			$query->clear()
				->update('#__eshop_carts')
				->set('is_' . $orderingName . '_sent = 1')
				->where('id IN (' . implode(',', $cartIdArr) . ')');
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 *
	 * Function to get Abandon Cart Email
	 *
	 * @param   string  $orderingName
	 */
	public static function getAbandonCartEmailBody($orderingName)
	{
		$abandonCartEmailBody = self::getMessageValue($orderingName . '_abandon_cart_email');

		$replaces              = [];
		$replaces['CART_LINK'] = self::getSiteUrl() . Route::_(EShopRoute::getViewRoute('cart'));

		foreach ($replaces as $key => $value)
		{
			$key                  = strtoupper($key);
			$abandonCartEmailBody = str_replace("[$key]", $value, $abandonCartEmailBody);
		}

		return $abandonCartEmailBody;
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return string The escaped output.
	 */
	public static function escape($output)
	{
		return htmlspecialchars((string) $output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Check if the given message entered via HTML editor has actual data
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	public static function isValidMessage($string)
	{
		if (!is_string($string) || strlen($string) === 0)
		{
			return false;
		}

		$string = strip_tags($string, '<img>');

		$string = str_replace('&nbsp;', '', $string);
		$string = str_replace("\xc2\xa0", ' ', $string);

		// Remove all special characters
		$string = str_replace(['.', ' ', "\n", "\t", "\r"], '', $string);

		$string = trim($string);

		if (strlen($string) > 10)
		{
			return true;
		}

		return false;
	}

	/**
	 * Load component css to use it inside module
	 */
	public static function loadComponentCssForModules()
	{
		static $loaded = false;

		if ($loaded == true)
		{
			return;
		}

		if (Factory::getApplication()->input->getCmd('option') === 'com_eshop')
		{
			return;
		}

		$document = Factory::getApplication()->getDocument();
		$rootUri  = Uri::root(true);

		// Load Bootstrap CSS
		if (EShopHelper::getConfigValue('load_bootstrap_css', 1) && in_array(EShopHelper::getConfigValue('twitter_bootstrap_version', 2), [2, 5]))
		{
			EShopHelper::loadBootstrapCss();
		}

		// Load CSS of corresponding theme
		$theme = EShopHelper::getConfigValue('theme');

		if (is_file(JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/css/style.css'))
		{
			$document->addStyleSheet($rootUri . '/components/com_eshop/themes/' . $theme . '/css/style.css');
		}
		else
		{
			$document->addStyleSheet($rootUri . '/components/com_eshop/themes/default/css/style.css');
		}

		// Load custom CSS file of component
		if (is_file(JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/css/custom.css'))
		{
			$document->addStyleSheet($rootUri . '/components/com_eshop/themes/' . $theme . '/css/custom.css');
		}
		else
		{
			$document->addStyleSheet($rootUri . '/components/com_eshop/themes/default/css/custom.css');
		}

		// Mark it as loaded to avoid the code from running again from second call
		$loaded = true;
	}
	
	/**
	 * 
	 * Function to get language link to use on URL
	 */
	public static function getLangLink()
	{
		$langLink = '';
		
		if (Multilanguage::isEnabled())
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$tag   = Factory::getLanguage()->getTag();
			$query->select('sef')
				->from('#__languages')
				->where('published = 1')
				->where('lang_code = ' . $db->quote($tag));
			$db->setQuery($query, 0, 1);
			$langLink = '&lang=' . $db->loadResult();
		}
		
		return $langLink;
	}
	
	/**
	 * Method to get file extension
	 *
	 * @param   string  $file
	 *
	 * @return string
	 */
	public static function getFileExt($file)
	{
		// Use framework code if available
		if (is_callable([File::class, 'getExt']))
		{
			return File::getExt($file);
		}
	
		// String manipulation should be faster than pathinfo() on newer PHP versions.
		$dot = strrpos($file, '.');
	
		if ($dot === false)
		{
			return '';
		}
	
		$ext = substr($file, $dot + 1);
	
		// Extension cannot contain slashes.
		if (strpos($ext, '/') !== false || (DIRECTORY_SEPARATOR === '\\' && strpos($ext, '\\') !== false))
		{
			return '';
		}
	
		return $ext;
	}
}
