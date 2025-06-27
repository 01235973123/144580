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
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopController extends BaseController
{
	/**
	 * Constructor function
	 *
	 * @param   array  $config
	 */
	public function __construct($config = [])
	{
		//By adding this code, the system will first find the model from backend, if not exist, it will use the model class defined in the front-end
		$config['model_path'] = JPATH_ADMINISTRATOR . '/components/com_eshop/models';
		parent::__construct($config);
		$this->addModelPath(JPATH_COMPONENT . '/models', $this->model_prefix);
	}


	public function search()
	{
		$input        = Factory::getApplication()->input;
		$currency     = EShopCurrency::getInstance();
		$currencyCode = $currency->getCurrencyCode();
		$db           = Factory::getDbo();
		$query        = $db->getQuery(true);
		$query->select('left_symbol, right_symbol')
			->from('#__eshop_currencies')
			->where('currency_code = ' . $db->quote($currencyCode));
		$db->setQuery($query);
		$row = $db->loadObject();
		($row->left_symbol) ? $symbol = $row->left_symbol : $symbol = $row->right_symbol;

		// Get weight unit
		$weight     = EShopWeight::getInstance();
		$weightId   = EShopHelper::getConfigValue('weight_id');
		$weightUnit = $weight->getUnit($weightId);

		//Get length unit
		$length     = EShopLength::getInstance();
		$lengthId   = EShopHelper::getConfigValue('length_id');
		$lengthUnit = $length->getUnit($lengthId);

		// Get submitted values
		$minPrice       = (float) str_replace($symbol, '', $input->get('min_price'));
		$maxPrice       = (float) str_replace($symbol, '', $input->get('max_price'));
		$minWeight      = (float) str_replace($weightUnit, '', $input->get('min_weight'));
		$maxWeight      = (float) str_replace($weightUnit, '', $input->get('max_weight'));
		$sameWeightUnit = $input->get('same_weight_unit');
		$minLength      = (float) str_replace($lengthUnit, '', $input->get('min_length'));
		$maxLength      = (float) str_replace($lengthUnit, '', $input->get('max_length'));
		$minWidth       = (float) str_replace($lengthUnit, '', $input->get('min_width'));
		$maxWidth       = (float) str_replace($lengthUnit, '', $input->get('max_width'));
		$minHeight      = (float) str_replace($lengthUnit, '', $input->get('min_height'));
		$maxHeight      = (float) str_replace($lengthUnit, '', $input->get('max_height'));
		$sameLengthUnit = $input->get('same_length_unit');
		$productInStock = $input->get('product_in_stock', 0);
		$categoryIds    = $input->get('category_ids');
		if (!$categoryIds)
		{
			$categoryIds = [];
		}
		$manufacturerIds = $input->get('manufacturer_ids');
		if (!$manufacturerIds)
		{
			$manufacturerIds = [];
		}
		$attributeIds = $input->get('attribute_ids');
		if (!$attributeIds)
		{
			$attributeIds = [];
		}
		$optionValueIds = $input->get('optionvalue_ids');
		if (!$optionValueIds)
		{
			$optionValueIds = [];
		}
		$keyword = $input->getString('keyword');

		// Build query string
		$query = [];
		if ($minPrice > 0)
		{
			$query['min_price'] = $minPrice;
		}

		if ($maxPrice > 0)
		{
			$query['max_price'] = $maxPrice;
		}

		if ($minWeight > 0)
		{
			$query['min_weight'] = $minWeight;
		}

		if ($maxWeight)
		{
			$query['max_weight'] = $maxWeight;
		}

		if ($minWeight > 0 || $maxWeight > 0)
		{
			$query['same_weight_unit'] = $sameWeightUnit;
		}

		if ($minLength > 0)
		{
			$query['min_length'] = $minLength;
		}

		if ($maxLength)
		{
			$query['max_length'] = $maxLength;
		}

		if ($minWidth > 0)
		{
			$query['min_width'] = $minWidth;
		}

		if ($maxWidth)
		{
			$query['max_width'] = $maxWidth;
		}

		if ($minHeight > 0)
		{
			$query['min_height'] = $minHeight;
		}

		if ($maxHeight)
		{
			$query['max_height'] = $maxHeight;
		}

		if ($minLength > 0 || $maxLength > 0 || $minWidth > 0 || $maxWidth > 0 || $minHeight > 0 || $maxHeight > 0)
		{
			$query['same_length_unit'] = $sameLengthUnit;
		}

		if ($productInStock != 0)
		{
			$query['product_in_stock'] = $productInStock;
		}

		if (count($categoryIds))
		{
			$query['category_ids'] = implode(',', $categoryIds);
		}

		if (count($manufacturerIds))
		{
			$query['manufacturer_ids'] = implode(',', $manufacturerIds);
		}

		if (count($attributeIds))
		{
			$query['attribute_ids'] = implode(',', $attributeIds);
		}

		if (count($optionValueIds))
		{
			$query['optionvalue_ids'] = implode(',', $optionValueIds);
		}

		if ($keyword)
		{
			$query['keyword'] = $keyword;
		}
		$uri = Uri::getInstance();
		$uri->setQuery($query);
		$searchQuery = substr($uri->toString(['query', 'fragment']), 1);
		$this->setRedirect(Route::_(EShopRoute::getViewRoute('search') . '&' . $searchQuery, false));
	}

	/**
	 *
	 * Function to download option file
	 */
	public function downloadOptionFile()
	{
		$input = Factory::getApplication()->input;
		$id    = $input->get('id');
		$app   = Factory::getApplication();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('option_value')
			->from('#__eshop_orderoptions')
			->where('id = ' . intval($id));
		$db->setQuery($query);
		$filename = $db->loadResult();
		while (@ob_end_clean())
		{
			;
		}
		EShopHelper::processDownload(JPATH_ROOT . '/media/com_eshop/files/' . $filename, $filename, true);
		$app->close(0);
	}

	/**
	 *
	 * Function to export pinterest feed
	 */
	public function exportPinterestFeed()
	{
		$input                 = Factory::getApplication()->input;
		$siteUrl               = Uri::root();
		$defaultCurrency       = EShopHelper::getConfigValue('default_currency_code');
		$exportFormat          = $input->getString('export_format', EShopHelper::getConfigValue('export_data_format', 'csv'));
		$language              = $input->getString('language', 'en-GB');
		$productStatus         = $input->getInt('product_status', 1);
		$pinterestId           = $input->getInt('pinterest_id', '1');
		$pinterestTitle        = $input->getInt('pinterest_title', '1');
		$pinterestDescription  = $input->getInt('pinterest_description', '1');
		$pinterestLink         = $input->getInt('pinterest_link', '1');
		$pinterestImageLink    = $input->getInt('pinterest_image_link', '1');
		$pinterestAvailability = $input->getInt('pinterest_availability', '1');
		$pinterestPrice        = $input->getInt('pinterest_price', '1');
		$pinterestCondition    = $input->getInt('pinterest_mpn', '1');
		$pinterestBrand        = $input->getInt('pinterest_brand', '1');

		$startRecord  = $input->getInt('start_record', 0);
		$totalRecords = $input->getInt('total_records', 0);

		$db          = Factory::getDbo();
		$languageSql = $db->quote($language);
		$query       = $db->getQuery(true);
		$query->select('a.*, b.*, pc.category_id, md.manufacturer_name')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->innerJoin('#__eshop_productcategories AS pc ON (a.id = pc.product_id)')
			->innerJoin('#__eshop_manufacturerdetails AS md ON (a.manufacturer_id = md.manufacturer_id)')
			->where('b.language = ' . $languageSql)
			->where('md.language = ' . $languageSql)
			->where('pc.main_category = 1');

		if ($productStatus != 2)
		{
			$query->where('a.published = ' . $productStatus);
		}

		$db->setQuery($query, $startRecord, $totalRecords);
		$rows      = $db->loadObjectList();
		$csvOutput = [];

		if (isset($rows) && count($rows))
		{
			if ($exportFormat == 'csv' || $exportFormat == 'xlsx')
			{
				$fields = [];

				if ($pinterestId)
				{
					$fields[] = 'id';
				}

				if ($pinterestTitle)
				{
					$fields[] = 'title';
				}

				if ($pinterestDescription)
				{
					$fields[] = 'description';
				}

				if ($pinterestLink)
				{
					$fields[] = 'link';
				}

				if ($pinterestImageLink)
				{
					$fields[] = 'image_link';
				}

				if ($pinterestAvailability)
				{
					$fields[] = 'availability';
				}

				if ($pinterestPrice)
				{
					$fields[] = 'price';
				}

				if ($pinterestCondition)
				{
					$fields[] = 'condition';
				}

				if ($pinterestBrand)
				{
					$fields[] = 'brand';
				}

				foreach ($rows as $row)
				{
					if ($pinterestId)
					{
						$row->id = $row->product_id;
					}

					if ($pinterestTitle)
					{
						$row->title = $row->product_name;
					}

					if ($pinterestDescription)
					{
						$row->description = $row->product_desc;
					}

					if ($pinterestLink)
					{
						$row->link = $siteUrl . EShopRoute::getProductRoute($row->product_id, $row->category_id);
					}

					if ($pinterestImageLink)
					{
						$row->image_link = $siteUrl . 'media/com_eshop/products/' . $row->product_image;
					}

					if ($pinterestAvailability)
					{
						if ($row->product_quantity > 0)
						{
							$row->availability = 'in stock';
						}
						else
						{
							$row->availability = 'out of stock';
						}
					}

					if ($pinterestPrice)
					{
						$row->price = number_format($row->product_price, 2) . ' ' . $defaultCurrency;
					}

					if ($pinterestCondition)
					{
						$row->condition = 'New';
					}

					if ($pinterestBrand)
					{
						$row->brand = $row->manufacturer_name;
					}
				}

				$filename = 'pinterest_feed_' . date('YmdHis') . '.' . $exportFormat;
				$filePath = EShopHelper::excelExport($fields, $rows, $filename, $fields, $exportFormat);
				EShopHelper::processDownload($filePath, $filename, true);
				Factory::getApplication()->close();
			}
			elseif ($exportFormat == 'xml')
			{
				$lang = Factory::getLanguage();
				$app  = Factory::getApplication();

				$infoSite = [];
				$infoSite = [
					'title' => $app->get('sitename'),
					'link'  => EShopHelper::getSiteUrl(),
				];

				foreach ($rows as $row)
				{
					$productXmlArray = [];

					if ($pinterestAvailability)
					{
						if ($row->product_quantity > 0)
						{
							$availability = 'in stock';
						}
						else
						{
							$availability = 'out of stock';
						}
					}

					$productArray = [];

					$productArray[] = 'id';
					$productArray[] = 'title';
					$productArray[] = 'description';
					$productArray[] = 'link';
					$productArray[] = 'image_link';
					$productArray[] = 'availability';
					$productArray[] = 'price';
					$productArray[] = 'condition';
					$productArray[] = 'brand';

					foreach ($productArray as $value)
					{
						if ($pinterestId)
						{
							$productArray1['g:id'] = $row->product_id;
						}

						if ($pinterestTitle)
						{
							$productArray1['title'] = $row->product_name;
						}

						if ($pinterestDescription)
						{
							$productArray1['description'] = $row->product_desc;
						}

						if ($pinterestLink)
						{
							$productArray1['link'] = $siteUrl . EShopRoute::getProductRoute($row->product_id, $row->category_id);
						}

						if ($pinterestImageLink)
						{
							$productArray1['g:image_link'] = $siteUrl . 'media/com_eshop/products/' . $row->product_image;
						}

						if ($pinterestAvailability)
						{
							$productArray1['g:availability'] = $availability;
						}

						if ($pinterestPrice)
						{
							$productArray1['g:price'] = number_format($row->product_price, 2) . ' ' . $defaultCurrency;
						}

						if ($pinterestCondition)
						{
							$productArray1['g:condition'] = 'New';
						}

						if ($pinterestBrand)
						{
							$productArray1['g:brand'] = $row->manufacturer_name;
						}
					}

					$xmlarray['channel']['item'][] = $productArray1;
					$productXmlArray[]             = $xmlarray;
				}

				$productXmlArray[0]['@attributes'] = ['version' => '2.0', 'xmlns:g' => 'http://base.google.com/ns/1.0'];

				$filename = 'pinterest_feed_' . date('YmdHis') . '.xml';
				include_once JPATH_ROOT . '/components/com_eshop/helpers/array2xml.php';
				$xml = Array2XML::createXML('rss', $productXmlArray[0]);
				File::write(JPATH_ROOT . '/' . $filename, $xml->saveXML());
				EShopHelper::processDownload(JPATH_ROOT . '/' . $filename, $filename, true);
				Factory::getApplication()->close();
			}
		}
		else
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_NO_DATA_TO_EXPORT'), 'notice');
			$mainframe->redirect('index.php?option=com_eshop&view=frontpage');
		}
	}

	/**
	 *
	 * Function to send abandon cart reminder
	 */
	public function process_abandon_cart()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Delete the store cart data based on schedule setting from Configuration
		$storeCartSchedule = EShopHelper::getConfigValue('store_cart_schedule', 7);

		if ($storeCartSchedule > 0)
		{
			$timezone = Factory::getApplication()->get('offset');
			$date     = Factory::getDate('now', $timezone);
			$date->modify('-' . $storeCartSchedule . ' days');
			$date = $date->toSql(false);

			$query->clear()
				->delete('#__eshop_carts')
				->where('modified_date <= ' . $db->quote($date));
			$db->setQuery($query);
			$db->execute();
		}

		$numberCustomers = EShopHelper::getConfigValue('number_customers', 10);

		// Check to send the Abandon Cart Reminder emails automatically
		if (EShopHelper::getConfigValue('send_1st_abandon_cart_reminder', 1))
		{
			EShopHelper::sendAbandonCartReminder($numberCustomers, '1st');
		}

		if (EShopHelper::getConfigValue('send_2nd_abandon_cart_reminder', 1))
		{
			EShopHelper::sendAbandonCartReminder($numberCustomers, '2nd');
		}

		if (EShopHelper::getConfigValue('send_3rd_abandon_cart_reminder', 1))
		{
			EShopHelper::sendAbandonCartReminder($numberCustomers, '3rd');
		}
	}
}