<?php

/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Eshop Export Plugin
 *
 * @package        Joomla
 * @subpackage     EShop
 */
class plgSystemEshopExport extends CMSPlugin
{
	public function onAfterRender()
	{
		if (is_file(JPATH_ROOT . '/components/com_eshop/eshop.php') && Factory::getApplication()->isClient('site'))
		{
			$lastRun    = (int) $this->params->get('last_run', 0);
			$timePeriod = (int) $this->params->get('time_period', 30);
			$now        = time();
			$cacheTime  = $timePeriod * 60;

			if (($now - $lastRun) < $cacheTime)
			{
				return;
			}

			// Store last run time
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$this->params->set('last_run', $now);
			$params = $this->params->toString();
			$query->clear();
			$query->update('#__extensions')
				->set('params=' . $db->quote($params))
				->where('`element` = "eshopexport"')
				->where('`folder`="system"');
			try
			{
				// Lock the tables to prevent multiple plugin executions causing a race condition
				$db->lockTable('#__extensions');
			}
			catch (Exception $e)
			{
				// If we can't lock the tables it's too risk continuing execution
				return;
			}

			try
			{
				// Update the plugin parameters
				$result = $db->setQuery($query)->execute();
				$this->clearCacheGroups(['com_plugins'], [0, 1]);
			}
			catch (Exception $exc)
			{
				// If we failed to execite
				$db->unlockTables();
				$result = false;
			}
			try
			{
				// Unlock the tables after writing
				$db->unlockTables();
			}
			catch (Exception $e)
			{
				// If we can't lock the tables assume we have somehow failed
				$result = false;
			}
			// Abort on failure
			if (!$result)
			{
				return;
			}

			require_once(JPATH_ROOT . '/components/com_eshop/helpers/helper.php');

			if ((version_compare(JVERSION, '3.0', 'ge') && Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1))
			{
				$routeFile = 'routev3.php';
			}
			else
			{
				$routeFile = 'route.php';
			}

			require_once(JPATH_ROOT . '/components/com_eshop/helpers/' . $routeFile);

			$this->export();
		}

		return true;
	}

	/**
	 *
	 * Function to export data
	 */
	public function export()
	{
		require_once JPATH_ROOT . '/administrator/components/com_eshop/libraries/autoload.php';
		require_once JPATH_ROOT . '/components/com_eshop/helpers/helper.php';
		require_once JPATH_ROOT . '/components/com_eshop/helpers/weight.php';

		$folderPath                = $this->params->get('folder_path', '');
		$fileName                  = $this->params->get('file_name', 'eshop_google_feed');
		$language                  = $this->params->get('language', 'en-GB');
		$exportFormat              = $this->params->get('export_data_format', EShopHelper::getConfigValue('export_data_format', 'csv'));
		$productStatus             = $this->params->get('product_status', 1);
		$startRecord               = $this->params->get('start_record', 0);
		$totalRecords              = $this->params->get('total_records', 0);
		$removeZeroPriceProducts   = $this->params->get('remove_zero_price_products', '0');
		$removeOutOfStockProducts  = $this->params->get('remove_out_of_stock_products', '0');
		$googleId                  = $this->params->get('google_id', '1');
		$googleTitle               = $this->params->get('google_title', '1');
		$googleDescription         = $this->params->get('google_description', '1');
		$googleProductType         = $this->params->get('google_product_type', '1');
		$googleLink                = $this->params->get('google_link', '1');
		$googleMobileLink          = $this->params->get('google_mobile_link', '1');
		$googleImageLink           = $this->params->get('google_image_link', '1');
		$googleAdditionalImageLink = $this->params->get('google_additional_image_link', '1');
		$googleAvailability        = $this->params->get('google_availability', '1');
		$googleCondition           = $this->params->get('google_condition', 'new');
		$googlePrice               = $this->params->get('google_price', '1');
		$googleSalePrice           = $this->params->get('google_sale_price', '1');
		$googleMpn                 = $this->params->get('google_mpn', '1');
		$googleBrand               = $this->params->get('google_brand', '1');
		$googleShippingWeight      = $this->params->get('google_shipping_weight', '1');
		$googleAlias               = $this->params->get('google_alias', '1');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, b.*')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('b.language = ' . $db->quote($language));

		if ($removeZeroPriceProducts)
		{
			$query->where('a.product_price > 0');
		}

		if ($removeOutOfStockProducts)
		{
			$query->where('a.product_quantity > 0');
		}

		if ($productStatus != 2)
		{
			$query->where('a.published = ' . $productStatus);
		}

		$db->setQuery($query, $startRecord, $totalRecords);
		$rows = $db->loadObjectList();

		if (isset($rows) && count($rows))
		{
			if ($exportFormat == 'csv' || $exportFormat == 'xlsx')
			{
				$fields = [];

				if ($googleId)
				{
					$fields[] = 'id';
				}

				if ($googleTitle)
				{
					$fields[] = 'title';
				}

				if ($googleDescription)
				{
					$fields[] = 'description';
				}

				if ($googleProductType)
				{
					$fields[] = 'product_type';
				}

				if ($googleLink)
				{
					$fields[] = 'link';
				}

				if ($googleMobileLink)
				{
					$fields[] = 'mobile_link';
				}

				if ($googleImageLink)
				{
					$fields[] = 'image_link';
				}

				if ($googleAdditionalImageLink)
				{
					$fields[] = 'additional_image_link';
				}

				if ($googleAvailability)
				{
					$fields[] = 'availability';
				}

				$fields[] = 'condition';

				if ($googlePrice)
				{
					$fields[] = 'price';
				}

				if ($googleSalePrice)
				{
					$fields[] = 'sale_price';
				}

				if ($googleMpn)
				{
					$fields[] = 'mpn';
				}

				if ($googleBrand)
				{
					$fields[] = 'brand';
				}

				if ($googleShippingWeight)
				{
					$fields[] = 'shipping_weight';
				}

				if ($googleAlias)
				{
					$fields[] = 'alias';
				}

				foreach ($rows as $row)
				{
					//Get additional images for product
					$query->clear();
					$query->select('image')
						->from('#__eshop_productimages')
						->where('product_id = ' . $row->product_id);
					$db->setQuery($query);
					$images = $db->loadColumn();

					//Get product manufacturer
					$manufacturer = EShopHelper::getProductManufacturer($row->product_id, $language);

					//Get product category
					$categoryId = EShopHelper::getProductCategory($row->product_id);

					if ($googleId)
					{
						$row->id = $row->product_id;
					}

					if ($googleTitle)
					{
						$row->title = $row->product_name;
					}

					if ($googleDescription)
					{
						$row->description = $row->product_desc;
					}

					if ($googleProductType)
					{
						if ($categoryId > 0)
						{
							$row->product_type = implode('/', EShopHelper::getCategoryNamePath($categoryId, $language));
						}
						else
						{
							$row->product_type = '';
						}
					}

					if ($googleLink)
					{
						$row->link = EShopHelper::getSiteUrl() . EShopRoute::getProductRoute($row->product_id, $categoryId);
					}

					if ($googleMobileLink)
					{
						$row->mobile_link = EShopHelper::getSiteUrl() . EShopRoute::getProductRoute($row->product_id, $categoryId);
					}

					if ($googleImageLink)
					{
						$row->image_link = EShopHelper::getSiteUrl() . 'media/com_eshop/products/' . $row->product_image;
					}

					if ($googleAdditionalImageLink)
					{
						if (isset($images) && count($images))
						{
							$row->additional_image_link = EShopHelper::getSiteUrl() . 'media/com_eshop/products/' . $images[0];
						}
						else
						{
							$row->additional_image_link = '';
						}
					}

					if ($googleAvailability)
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

					$row->condition = $googleCondition;

					$defaultCurrency = EShopHelper::getConfigValue('default_currency_code');

					if ($googlePrice)
					{
						$row->price = number_format($row->product_price, 2) . ' ' . $defaultCurrency;
					}

					if ($googleSalePrice)
					{
						$row->sale_price = number_format($row->product_price, 2) . ' ' . $defaultCurrency;
					}

					if ($googleMpn)
					{
						$row->mpn = $row->product_sku;
					}

					if ($googleBrand)
					{
						if (is_object($manufacturer))
						{
							$row->brand = $manufacturer->manufacturer_name;
						}
						else
						{
							$row->brand = '';
						}
					}

					if ($googleShippingWeight)
					{
						$eshopWeight          = EShopWeight::getInstance();
						$row->shipping_weight = $eshopWeight->format($row->product_weight, $row->product_weight_id);
					}

					if ($googleAlias)
					{
						$row->alias = $row->product_alias;
					}
				}

				$filename = $fileName . '.' . $exportFormat;
				$filePath = EShopHelper::excelExport($fields, $rows, $filename, $fields, $exportFormat, JPATH_ROOT . $folderPath);
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
					//Get additional images for product
					$query->clear();
					$query->select('image')
						->from('#__eshop_productimages')
						->where('product_id = ' . $row->product_id);
					$db->setQuery($query);
					$images = $db->loadColumn();

					//Get product manufacturer
					$manufacturer = EShopHelper::getProductManufacturer($row->product_id, $language);

					//Get product category
					$categoryId = EShopHelper::getProductCategory($row->product_id);

					if ($categoryId > 0)
					{
						$productType = implode('/', EShopHelper::getCategoryNamePath($categoryId, $language));
					}
					else
					{
						$productType = '';
					}

					if ($googleAvailability)
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

					$defaultCurrency = EShopHelper::getConfigValue('default_currency_code');

					$productArray = [];
					$eshopWeight  = EShopWeight::getInstance();

					$productArray[] = 'id';
					$productArray[] = 'title';
					$productArray[] = 'description';
					$productArray[] = 'product_type';
					$productArray[] = 'link';
					$productArray[] = 'mobile_link';
					$productArray[] = 'image_link';
					$productArray[] = 'additional_image_link';
					$productArray[] = 'availability';
					$productArray[] = 'condition';
					$productArray[] = 'price';
					$productArray[] = 'sale_price';
					$productArray[] = 'mpn';
					$productArray[] = 'brand';
					$productArray[] = 'shipping_weight';

					foreach ($productArray as $value)
					{
						if ($googleId)
						{
							$productArray1['g:id'] = $row->product_id;
						}

						if ($googleTitle)
						{
							$productArray1['g:title'] = $row->product_name;
						}

						if ($googleDescription)
						{
							$productArray1['g:description'] = $row->product_desc;
						}

						if ($googleProductType)
						{
							$productArray1['g:product_type'] = $productType;
						}

						if ($googleLink)
						{
							$productArray1['g:link'] = EShopHelper::getSiteUrl() . EShopRoute::getProductRoute($row->product_id, $categoryId);
						}

						if ($googleMobileLink)
						{
							$productArray1['g:mobile_link'] = EShopHelper::getSiteUrl() . EShopRoute::getProductRoute($row->product_id, $categoryId);
						}

						if ($googleImageLink)
						{
							$productArray1['g:image_link'] = EShopHelper::getSiteUrl() . 'media/com_eshop/products/' . $row->product_image;
						}

						if ($googleAdditionalImageLink)
						{
							if (isset($images) && count($images))
							{
								$productArray1['g:additional_image_link'] = EShopHelper::getSiteUrl() . 'media/com_eshop/products/' . $images[0];
							}
							else
							{
								$productArray1['g:additional_image_link'] = '';
							}
						}

						if ($googleAvailability)
						{
							$productArray1['g:availability'] = $availability;
						}

						$productArray1['g:condition'] = $googleCondition;

						if ($googlePrice)
						{
							$productArray1['g:price'] = number_format($row->product_price, 2) . ' ' . $defaultCurrency;
						}

						if ($googleSalePrice)
						{
							$productArray1['g:sale_price'] = number_format($row->product_price, 2) . ' ' . $defaultCurrency;
						}

						if ($googleMpn)
						{
							$productArray1['g:mpn'] = $row->product_sku;
						}

						if ($googleBrand)
						{
							$productArray1['g:brand'] = $manufacturer->manufacturer_name;
						}

						if ($googleShippingWeight)
						{
							$productArray1['g:shipping_weight'] = $eshopWeight->format($row->product_weight, $row->product_weight_id);
						}
					}

					$xmlarray['channel']['item'][] = $productArray1;
					$productXmlArray[]             = $xmlarray;
				}

				$productXmlArray[0]['@attributes'] = ['version' => '2.0', 'xmlns:g' => 'http://base.google.com/ns/1.0'];

				$filename = $fileName . '.xml';
				include_once JPATH_ROOT . '/components/com_eshop/helpers/array2xml.php';
				$xml = Array2XML::createXML('rss', $productXmlArray[0]);
				File::write(JPATH_ROOT . $folderPath . $filename, $xml->saveXML());
			}
		}
	}

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   2.0.4
	 */
	private function clearCacheGroups(array $clearGroups, array $cacheClients = [0, 1])
	{
		$conf = Factory::getConfig();
		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $client_id)
			{
				try
				{
					$options = [
						'defaultgroup' => $group,
						'cachebase'    => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' :
							$conf->get('cache_path', JPATH_SITE . '/cache'),
					];
					$cache   = Cache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// Ignore it
				}
			}
		}
	}
}