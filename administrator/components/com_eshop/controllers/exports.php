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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Uri\Uri;

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerExports extends BaseController
{

	/**
	 * Save the category
	 *
	 */
	public function process()
	{
		$input      = Factory::getApplication()->input;
		$exportType = $input->getString('export_type', 'products');
		switch ($exportType)
		{
			case 'products':
				$this->_exportProducts();
				break;
			case 'categories':
				$this->_exportCategories();
				break;
			case 'manufacturers':
				$this->_exportManufacturers();
				break;
			case 'customers':
				$this->_exportCustomers();
				break;
			case 'orders':
				$this->_exportOrders();
				break;
			case 'google_feed':
				$this->_exportGoogleFeed();
			case 'pinterest_feed':
				$this->_exportPinterestFeed();
				break;
		}
	}

	/**
	 *
	 * Function to export products
	 */
	public function _exportProducts()
	{
		$input = Factory::getApplication()->input;

		$imageSeparator = $input->getString('image_separator', ';');
		$language       = $input->getString('language', 'en-GB');
		$exportFormat   = $input->getString('export_format', EShopHelper::getConfigValue('export_data_format', 'csv'));
		$categoryIds    = $input->get('category_ids');
		$productStatus  = $input->getInt('product_status', 1);
		$exportFields   = $input->get('export_fields');
		$startRecord    = $input->getInt('start_record', 0);
		$totalRecords   = $input->getInt('total_records', 0);

		$db          = Factory::getDbo();
		$languageSql = $db->quote($language);
		$query       = $db->getQuery(true);
		$query->select('a.*, b.*, a.id AS id')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('b.language = ' . $languageSql);

		if (isset($categoryIds) && count($categoryIds) > 0)
		{
			$query->innerJoin('#__eshop_productcategories AS pc ON (a.id = pc.product_id)')
				->where('pc.category_id IN (' . implode(',', $categoryIds) . ')');
		}

		if ($productStatus != 2)
		{
			$query->where('a.published = ' . $productStatus);
		}

		$db->setQuery($query, $startRecord, $totalRecords);
		$rows = $db->loadObjectList();

		if (isset($rows) && count($rows))
		{
			if (EShopHelper::getConfigValue('product_custom_fields'))
			{
				EShopHelper::prepareCustomFieldsData($rows, true);
			}

			if ($exportFormat == 'csv' || $exportFormat == 'xlsx')
			{
				$fields = [];

				if (isset($exportFields) && count($exportFields))
				{
					foreach ($exportFields as $exportField)
					{
						$fields[] = $exportField;
					}
				}
				else
				{
					$fields[] = 'id';
					$fields[] = 'language';
					$fields[] = 'product_sku';
					$fields[] = 'product_name';
					$fields[] = 'product_alias';
					$fields[] = 'product_desc';
					$fields[] = 'product_short_desc';
					$fields[] = 'product_page_title';
					$fields[] = 'product_page_heading';
					$fields[] = 'tab1_title';
					$fields[] = 'tab1_content';
					$fields[] = 'tab2_title';
					$fields[] = 'tab2_content';
					$fields[] = 'tab3_title';
					$fields[] = 'tab3_content';
					$fields[] = 'tab4_title';
					$fields[] = 'tab4_content';
					$fields[] = 'tab5_title';
					$fields[] = 'tab5_content';
					$fields[] = 'product_meta_key';
					$fields[] = 'product_meta_desc';
					$fields[] = 'product_weight';
					$fields[] = 'product_weight_id';
					$fields[] = 'product_length';
					$fields[] = 'product_width';
					$fields[] = 'product_height';
					$fields[] = 'product_length_id';
					$fields[] = 'product_cost';
					$fields[] = 'product_price';
					$fields[] = 'product_call_for_price';
					$fields[] = 'product_taxclass_id';
					$fields[] = 'product_manage_stock';
					$fields[] = 'product_stock_display';
					$fields[] = 'product_stock_warning';
					$fields[] = 'product_inventory_global';
					$fields[] = 'product_quantity';
					$fields[] = 'product_threshold';
					$fields[] = 'product_threshold_notify';
					$fields[] = 'product_stock_checkout';
					$fields[] = 'product_minimum_quantity';
					$fields[] = 'product_maximum_quantity';
					$fields[] = 'product_shipping';
					$fields[] = 'product_shipping_cost';
					$fields[] = 'product_shipping_cost_geozones';
					$fields[] = 'product_image';
					$fields[] = 'product_available_date';
					$fields[] = 'product_featured';
					$fields[] = 'product_customergroups';
					$fields[] = 'product_stock_status_id';
					$fields[] = 'product_cart_mode';
					$fields[] = 'product_quote_mode';
					$fields[] = 'product_published';
					$fields[] = 'product_ordering';
					$fields[] = 'product_hits';
					$fields[] = 'product_additional_images';
					$fields[] = 'manufacturer_name';
					$fields[] = 'category_name';
					$fields[] = 'option_type';
					$fields[] = 'option_name';
					$fields[] = 'option_value';
					$fields[] = 'option_sku';
					$fields[] = 'option_quantity';
					$fields[] = 'option_price';
					$fields[] = 'option_price_sign';
					$fields[] = 'option_price_type';
					$fields[] = 'option_weight';
					$fields[] = 'option_weight_sign';
					$fields[] = 'option_image';
					$fields[] = 'attributegroup_name';
					$fields[] = 'attribute_name';
					$fields[] = 'attribute_value';
				}

				//Prepare product custom fields title
				if (EShopHelper::getConfigValue('product_custom_fields'))
				{
					$row = $rows[0];

					foreach ($row->paramData as $key => $param)
					{
						$fields[] = $key;
					}
				}
			}

			foreach ($rows as $row)
			{
				//Get additional images for product
				$query->clear();
				$query->select('image')
					->from('#__eshop_productimages')
					->where('product_id = ' . $row->id);
				$db->setQuery($query);
				$images = $db->loadColumn();

				//Get product manufacturer
				$manufacturer = EShopHelper::getProductManufacturer($row->id, $language);

				//Get product categories
				$productCategories = EShopHelper::getProductCategories($row->id, $language);

				// field options
				$query->clear()->select('a.option_type')->from('#__eshop_options AS a')
					->select('b.option_name')->innerJoin('#__eshop_optiondetails AS b ON (b.option_id = a.id AND b.language=' . $languageSql . ')')
					->select('c.value AS option_value')->innerJoin(
						'#__eshop_optionvaluedetails AS c ON (c.option_id = a.id AND c.language = ' . $languageSql . ')'
					)
					->select(
						'd.sku AS option_sku, d.quantity AS option_quantity, d.price AS option_price, d.price_sign AS option_price_sign, d.price_type AS option_price_type, d.weight AS option_weight, d.weight_sign AS option_weight_sign, d.image AS option_image'
					)
					->innerJoin(
						'#__eshop_productoptionvalues AS d ON (d.option_id = a.id AND d.option_value_id = c.optionvalue_id  AND d.product_id=' . $row->product_id . ')'
					)
					->order('a.ordering');
				$db->setQuery($query);
				$optionlist   = $db->loadObjectList();
				$valueoptions = [];

				if (isset($optionlist) && count($optionlist))
				{
					foreach ($optionlist as $obj)
					{
						$valueoptions['option_type'][]        = $obj->option_type;
						$valueoptions['option_name'][]        = $obj->option_name;
						$valueoptions['option_value'][]       = $obj->option_value;
						$valueoptions['option_sku'][]         = $obj->option_sku;
						$valueoptions['option_quantity'][]    = $obj->option_quantity;
						$valueoptions['option_price'][]       = $obj->option_price;
						$valueoptions['option_price_sign'][]  = $obj->option_price_sign;
						$valueoptions['option_price_type'][]  = $obj->option_price_type;
						$valueoptions['option_weight'][]      = $obj->option_weight;
						$valueoptions['option_weight_sign'][] = $obj->option_weight_sign;
						$valueoptions['option_image'][]       = $obj->option_image;
					}
				}

				// field attribute
				$query->clear()
					->select('a.attributegroup_name')
					->from('#__eshop_attributegroupdetails AS a')
					->innerJoin('#__eshop_attributes AS b ON a.attributegroup_id = b.attributegroup_id')
					->select('c.attribute_name')
					->innerJoin('#__eshop_attributedetails AS c ON (b.id=c.attribute_id AND c.language = ' . $languageSql . ')')
					->innerJoin('#__eshop_productattributes AS d ON (c.attribute_id = d.attribute_id AND d.product_id = ' . $row->product_id . ')')
					->select('e.value AS attribute_value')
					->innerJoin(
						'#__eshop_productattributedetails AS e ON (e.productattribute_id = d.id AND e.product_id = ' . $row->product_id . ' AND e.language = ' . $languageSql . ')'
					)
					->where('a.language = ' . $languageSql);
				$db->setQuery($query);
				$attributelist   = $db->loadObjectList();
				$valueattributes = [];

				if (isset($attributelist) && count($attributelist))
				{
					foreach ($attributelist as $obj)
					{
						$valueattributes['attributegroup_name'][] = $obj->attributegroup_name;
						$valueattributes['attribute_name'][]      = $obj->attribute_name;
						$valueattributes['attribute_value'][]     = $obj->attribute_value;
					}
				}

				if ($exportFormat == 'csv' || $exportFormat == 'xlsx')
				{
					$row->id                = $row->product_id;
					$row->language          = $language;
					$row->product_meta_key  = $row->meta_key;
					$row->product_meta_desc = $row->meta_desc;
					$row->product_published = $row->published;
					$row->product_ordering  = $row->ordering;
					$row->product_hits      = $row->hits;

					if (isset($images) && count($images))
					{
						$row->product_additional_images = implode($imageSeparator, $images);
					}
					else
					{
						$row->product_additional_images = '';
					}

					if (is_object($manufacturer))
					{
						$row->manufacturer_name = $manufacturer->manufacturer_name;
					}
					else
					{
						$row->manufacturer_name = '';
					}

					$categories = [];

					if (isset($productCategories) && count($productCategories))
					{
						foreach ($productCategories as $category)
						{
							$categories[] = implode('/', EShopHelper::getCategoryNamePath($category->id, $language));
						}

						$row->category_name = implode(' | ', $categories);
					}
					else
					{
						$row->category_name = '';
					}

					$row->option_type         = isset($valueoptions['option_type']) ? implode(';', $valueoptions['option_type']) : '';
					$row->option_name         = isset($valueoptions['option_name']) ? implode(';', $valueoptions['option_name']) : '';
					$row->option_value        = isset($valueoptions['option_value']) ? implode(';', $valueoptions['option_value']) : '';
					$row->option_sku          = isset($valueoptions['option_sku']) ? implode(';', $valueoptions['option_sku']) : '';
					$row->option_quantity     = isset($valueoptions['option_quantity']) ? implode(';', $valueoptions['option_quantity']) : '';
					$row->option_price        = isset($valueoptions['option_price']) ? implode(';', $valueoptions['option_price']) : '';
					$row->option_price_sign   = isset($valueoptions['option_price_sign']) ? implode(';', $valueoptions['option_price_sign']) : '';
					$row->option_price_type   = isset($valueoptions['option_price_type']) ? implode(';', $valueoptions['option_price_type']) : '';
					$row->option_weight       = isset($valueoptions['option_weight']) ? implode(';', $valueoptions['option_weight']) : '';
					$row->option_weight_sign  = isset($valueoptions['option_weight_sign']) ? implode(';', $valueoptions['option_weight_sign']) : '';
					$row->option_image        = isset($valueoptions['option_image']) ? implode(';', $valueoptions['option_image']) : '';
					$row->attributegroup_name = isset($valueattributes['attributegroup_name']) ? implode(
						';',
						$valueattributes['attributegroup_name']
					) : '';
					$row->attribute_name      = isset($valueattributes['attribute_name']) ? implode(';', $valueattributes['attribute_name']) : '';
					$row->attribute_value     = isset($valueattributes['attribute_value']) ? implode(';', $valueattributes['attribute_value']) : '';

					//Prepare product custom fields value
					if (EShopHelper::getConfigValue('product_custom_fields'))
					{
						foreach ($row->paramData as $key => $param)
						{
							$row->{$key} = $param['value'];
						}
					}
				}
				else
				{
					$productUrl = EShopHelper::getSiteUrl() . EShopRoute::getProductRoute(
							$row->product_id,
							EShopHelper::getProductCategory($row->product_id)
						);
					$productUrl = str_replace("/administrator/", '', $productUrl);
					$categories = [];
					if (isset($productCategories) && count($productCategories))
					{
						foreach ($productCategories as $category)
						{
							$categories[] = implode(' > ', EShopHelper::getCategoryNamePath($category->id, $language));
						}
					}
					$productXmlArray = [];
					$productArray    = [
						'id'                        => $row->product_id,
						'link'                      => $productUrl,
						'product_sku'               => $row->product_sku,
						'name'                      => $row->product_name,
						'product_alias'             => $row->product_alias,
						'product_desc'              => $row->product_desc,
						'product_short_desc'        => $row->product_short_desc,
						'product_page_title'        => $row->product_page_title,
						'product_page_heading'      => $row->product_page_heading,
						'tab1_title'                => $row->tab1_title,
						'tab1_content'              => $row->tab1_content,
						'tab2_title'                => $row->tab2_title,
						'tab2_content'              => $row->tab2_content,
						'tab3_title'                => $row->tab3_title,
						'tab3_content'              => $row->tab3_content,
						'tab4_title'                => $row->tab4_title,
						'tab4_content'              => $row->tab4_content,
						'tab5_title'                => $row->tab5_title,
						'tab5_content'              => $row->tab5_content,
						'product_meta_key'          => $row->meta_key,
						'product_meta_desc'         => $row->meta_desc,
						'product_weight'            => $row->product_weight,
						'product_weight_id'         => $row->product_weight_id,
						'product_length'            => $row->product_length,
						'product_width'             => $row->product_width,
						'product_height'            => $row->product_height,
						'product_length_id'         => $row->product_length_id,
						'product_price'             => $row->product_price,
						'product_call_for_price'    => $row->product_call_for_price,
						'product_taxclass_id'       => $row->product_taxclass_id,
						'product_quantity'          => $row->product_quantity,
						'product_threshold'         => $row->product_threshold,
						'product_threshold_notify'  => $row->product_threshold_notify,
						'product_stock_checkout'    => $row->product_stock_checkout,
						'product_minimum_quantity'  => $row->product_minimum_quantity,
						'product_maximum_quantity'  => $row->product_maximum_quantity,
						'product_shipping'          => $row->product_shipping,
						'product_shipping_cost'     => $row->product_shipping_cost,
						'image'                     => Uri::root() . 'media/com_eshop/products/' . $row->product_image,
						'product_available_date'    => $row->product_available_date,
						'product_featured'          => $row->product_featured,
						'product_customergroups'    => $row->product_customergroups,
						'product_stock_status_id'   => $row->product_stock_status_id,
						'product_quote_mode'        => $row->product_quote_mode,
						'product_published'         => $row->published,
						'product_ordering'          => $row->ordering,
						'product_hits'              => $row->hits,
						'product_additional_images' => implode($imageSeparator, $images),
						'manufacturer_name'         => $manufacturer->manufacturer_name,
						'category'                  => implode(';', $categories),
						'option_type'               => isset($valueoptions['option_type']) ? implode(';', $valueoptions['option_type']) : '',
						'option_name'               => isset($valueoptions['option_name']) ? implode(';', $valueoptions['option_name']) : '',
						'option_value'              => isset($valueoptions['option_value']) ? implode(';', $valueoptions['option_value']) : '',
						'option_sku'                => isset($valueoptions['option_sku']) ? implode(';', $valueoptions['option_sku']) : '',
						'option_quantity'           => isset($valueoptions['option_quantity']) ? implode(';', $valueoptions['option_quantity']) : '',
						'option_price'              => isset($valueoptions['option_price']) ? implode(';', $valueoptions['option_price']) : '',
						'option_price_sign'         => isset($valueoptions['option_price_sign']) ? implode(
							';',
							$valueoptions['option_price_sign']
						) : '',
						'option_price_type'         => isset($valueoptions['option_price_type']) ? implode(
							';',
							$valueoptions['option_price_type']
						) : '',
						'option_weight'             => isset($valueoptions['option_weight']) ? implode(';', $valueoptions['option_weight']) : '',
						'option_weight_sign'        => isset($valueoptions['option_weight_sign']) ? implode(
							';',
							$valueoptions['option_weight_sign']
						) : '',
						'option_image'              => isset($valueoptions['option_image']) ? implode(';', $valueoptions['option_image']) : '',
						'attributegroup_name'       => isset($valueattributes['attributegroup_name']) ? implode(
							';',
							$valueattributes['attributegroup_name']
						) : '',
						'attribute_name'            => isset($valueattributes['attribute_name']) ? implode(
							';',
							$valueattributes['attribute_name']
						) : '',
						'attribute_value'           => isset($valueattributes['attribute_value']) ? implode(
							';',
							$valueattributes['attribute_value']
						) : '',
					];

					//Prepare product custom fields title and value
					if (EShopHelper::getConfigValue('product_custom_fields'))
					{
						foreach ($row->paramData as $key => $param)
						{
							$productArray = array_merge($productArray, [$key => $param['value']]);
						}
					}

					$xmlarray['products']['product'][] = $productArray;

					$productXmlArray[] = $xmlarray;
				}
			}

			if ($exportFormat == 'csv' || $exportFormat == 'xlsx')
			{
				$filename = 'products_' . date('YmdHis') . '.' . $exportFormat;
				$filePath = EShopHelper::excelExport($fields, $rows, $filename, $fields, $exportFormat);
				EShopHelper::processDownload($filePath, $filename, true);
				Factory::getApplication()->close();
			}
			else
			{
				$filename = 'products_' . date('YmdHis') . '.xml';
				include_once JPATH_ROOT . '/components/com_eshop/helpers/array2xml.php';
				$xml = Array2XML::createXML('mywebstore', $productXmlArray[0]);
				File::write(JPATH_ROOT . '/media/com_eshop/files/' . $filename, $xml->saveXML());
				EShopHelper::processDownload(JPATH_ROOT . '/media/com_eshop/files/' . $filename, $filename, true);
				Factory::getApplication()->close();
			}
		}
		else
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_NO_DATA_TO_EXPORT'), 'notice');
			$mainframe->redirect('index.php?option=com_eshop&view=exports');
		}
	}

	/**
	 *
	 * Function to export categories
	 */
	public function _exportCategories()
	{
		$input        = Factory::getApplication()->input;
		$exportFormat = $input->getString('export_format', EShopHelper::getConfigValue('export_data_format', 'csv'));
		$language     = $input->getString('language', 'en-GB');
		$db           = Factory::getDbo();
		$query        = $db->getQuery(true);
		$query->select(
			'a.*, b.category_name, b.category_alias, b.category_desc, b.category_page_title, b.category_page_heading, b.meta_key, b.meta_desc'
		)
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
			->where('b.language = "' . $language . '"');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (isset($rows) && count($rows))
		{
			$fields   = [];
			$fields[] = 'id';
			$fields[] = 'language';
			$fields[] = 'category_name';
			$fields[] = 'category_alias';
			$fields[] = 'category_desc';
			$fields[] = 'category_page_title';
			$fields[] = 'category_page_heading';
			$fields[] = 'category_image';
			$fields[] = 'products_per_page';
			$fields[] = 'products_per_row';
			$fields[] = 'category_published';
			$fields[] = 'category_ordering';
			$fields[] = 'category_hits';
			$fields[] = 'category_meta_key';
			$fields[] = 'category_meta_desc';

			foreach ($rows as $row)
			{
				$row->language = $language;
			}

			$filename = 'categories_' . date('YmdHis') . '.' . $exportFormat;
			$filePath = EShopHelper::excelExport($fields, $rows, $filename, $fields, $exportFormat);
			EShopHelper::processDownload($filePath, $filename, true);
			Factory::getApplication()->close();
		}
		else
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_NO_DATA_TO_EXPORT'), 'notice');
			$mainframe->redirect('index.php?option=com_eshop&view=exports');
		}
	}

	/**
	 *
	 * Function to export manufacturers
	 */
	public function _exportManufacturers()
	{
		$input        = Factory::getApplication()->input;
		$exportFormat = $input->getString('export_format', EShopHelper::getConfigValue('export_data_format', 'csv'));
		$language     = $input->getString('language', 'en-GB');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, b.manufacturer_name, b.manufacturer_alias, b.manufacturer_desc, b.manufacturer_page_title, b.manufacturer_page_heading')
			->from('#__eshop_manufacturers AS a')
			->innerJoin('#__eshop_manufacturerdetails AS b ON (a.id = b.manufacturer_id)')
			->where('b.language = "' . $language . '"');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (isset($rows) && count($rows))
		{
			$fields   = [];
			$fields[] = 'id';
			$fields[] = 'manufacturer_email';
			$fields[] = 'manufacturer_url';
			$fields[] = 'manufacturer_image';
			$fields[] = 'manufacturer_customergroups';
			$fields[] = 'manufacturer_published';
			$fields[] = 'manufacturer_ordering';
			$fields[] = 'manufacturer_hits';
			$fields[] = 'language';
			$fields[] = 'manufacturer_name';
			$fields[] = 'manufacturer_alias';
			$fields[] = 'manufacturer_desc';
			$fields[] = 'manufacturer_page_title';
			$fields[] = 'manufacturer_page_heading';

			foreach ($rows as $row)
			{
				$row->language = $language;
			}

			$filename = 'manufacturers_' . date('YmdHis') . '.' . $exportFormat;
			$filePath = EShopHelper::excelExport($fields, $rows, $filename, $fields, $exportFormat);
			EShopHelper::processDownload($filePath, $filename, true);
			Factory::getApplication()->close();
		}
		else
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_NO_DATA_TO_EXPORT'), 'notice');
			$mainframe->redirect('index.php?option=com_eshop&view=exports');
		}
	}

	/**
	 *
	 * Function to export customers
	 */
	public function _exportCustomers()
	{
		$input        = Factory::getApplication()->input;
		$exportFormat = $input->getString('export_format', EShopHelper::getConfigValue('export_data_format', 'csv'));
		$db           = Factory::getDbo();
		$query        = $db->getQuery(true);
		$query->select('a.name, a.email')
			->from('#__users AS a');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (isset($rows) && count($rows))
		{
			$fields   = [];
			$fields[] = 'name';
			$fields[] = 'email';

			$filename = 'customers_' . date('YmdHis') . '.' . $exportFormat;
			$filePath = EShopHelper::excelExport($fields, $rows, $filename, $fields, $exportFormat);
			EShopHelper::processDownload($filePath, $filename, true);
			Factory::getApplication()->close();
		}
		else
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_NO_DATA_TO_EXPORT'), 'notice');
			$mainframe->redirect('index.php?option=com_eshop&view=exports');
		}
	}

	/**
	 *
	 * Function to export orders
	 */
	public function _exportOrders()
	{
		ini_set('memory_limit', '-1');
		set_time_limit(0);

		$input    = Factory::getApplication()->input;
		$currency = EShopCurrency::getInstance();

		$dateStart = $input->getString('date_start', '');
		$dateEnd   = $input->getString('date_end', '');

		$groupBy            = $input->getString('group_by', 'week');
		$orderStatusId      = $input->getInt('order_status_id', 0);
		$orderIdFrom        = $input->getInt('order_id_from', 0);
		$orderIdTo          = $input->getInt('order_id_to', 0);
		$listOrderId        = $input->getString('list_order_id', '');
		$exportFormat       = $input->getString('export_format', EShopHelper::getConfigValue('export_data_format', 'csv'));
		$exportOrdersFields = $input->get('export_orders_fields');

		$db       = Factory::getDbo();
		$nullDate = $db->getNullDate();
		$query    = $db->getQuery(true);

		$query->select('*')
			->from('#__eshop_orders');

		if ($orderStatusId)
		{
			$query->where('order_status_id = ' . (int) $orderStatusId);
		}

		if ($orderIdFrom)
		{
			$query->where('id >= ' . intval($orderIdFrom));
		}

		if ($orderIdTo)
		{
			$query->where('id <= ' . intval($orderIdTo));
		}

		if ($listOrderId != '')
		{
			$query->where('id IN (' . trim($listOrderId) . ')');
		}

		if ($dateStart != '')
		{
			// In case use only select date, we will set time of From Date to 00:00:00
			if (strpos($dateStart, ' ') === false && strlen($dateStart) <= 10)
			{
				$dateStart = $dateStart . ' 00:00:00';
			}

			try
			{
				$date = DateTime::createFromFormat('Y-m-d H:i:s', $dateStart, new DateTimeZone(Factory::getApplication()->get('offset')));

				if ($date !== false)
				{
					$date->setTime(0, 0, 0);
					$date->setTimezone(new DateTimeZone("UTC"));
					$query->where('created_date >= ' . $db->quote($date->format('Y-m-d H:i:s')));
				}
			}
			catch (Exception $e)
			{
				// Do-nothing
			}
		}

		if ($dateEnd != '')
		{
			// In case use only select date, we will set time of To Date to 23:59:59
			if (strpos($dateEnd, ' ') === false && strlen($dateEnd) <= 10)
			{
				$dateEnd = $dateEnd . ' 23:59:59';
			}

			try
			{
				$date = DateTime::createFromFormat('Y-m-d H:i:s', $dateEnd, new DateTimeZone(Factory::getApplication()->get('offset')));

				if ($date !== false)
				{
					$date->setTime(23, 59, 59);
					$date->setTimezone(new DateTimeZone("UTC"));
					$query->where('created_date <= ' . $db->quote($date->format('Y-m-d H:i:s')));
				}
			}
			catch (Exception $e)
			{
				// Do-nothing
			}
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$orderRows = [];

		if (isset($rows) && count($rows))
		{
			if ($exportFormat == 'csv' || $exportFormat == 'xlsx')
			{
				$fields = [];

				if (isset($exportOrdersFields) && count($exportOrdersFields))
				{
					foreach ($exportOrdersFields as $exportOrdersField)
					{
						$fields[] = $exportOrdersField;
					}
				}
				else
				{
					$fields[] = 'order_id';
					$fields[] = 'order_number';
					$fields[] = 'invoice_number';
					$fields[] = 'customer_firstname';
					$fields[] = 'customer_lastname';
					$fields[] = 'customer_email';
					$fields[] = 'customer_telephone';
					$fields[] = 'customer_fax';
					$fields[] = 'payment_firstname';
					$fields[] = 'payment_lastname';
					$fields[] = 'payment_email';
					$fields[] = 'payment_telephone';
					$fields[] = 'payment_fax';
					$fields[] = 'payment_company';
					$fields[] = 'payment_company_id';
					$fields[] = 'payment_address_1';
					$fields[] = 'payment_address_2';
					$fields[] = 'payment_city';
					$fields[] = 'payment_postcode';
					$fields[] = 'payment_country_name';
					$fields[] = 'payment_zone_name';
					$fields[] = 'payment_method';
					$fields[] = 'payment_method_title';
					$fields[] = 'transaction_id';
					$fields[] = 'shipping_firstname';
					$fields[] = 'shipping_lastname';
					$fields[] = 'shipping_email';
					$fields[] = 'shipping_telephone';
					$fields[] = 'shipping_fax';
					$fields[] = 'shipping_company';
					$fields[] = 'shipping_company_id';
					$fields[] = 'shipping_address_1';
					$fields[] = 'shipping_address_2';
					$fields[] = 'shipping_city';
					$fields[] = 'shipping_postcode';
					$fields[] = 'shipping_country_name';
					$fields[] = 'shipping_zone_name';
					$fields[] = 'shipping_method';
					$fields[] = 'shipping_method_title';
					$fields[] = 'shipping_tracking_number';
					$fields[] = 'shipping_tracking_url';
					$fields[] = 'shipping_amount';
					$fields[] = 'tax_amount';
					$fields[] = 'total';
					$fields[] = 'comment';
					$fields[] = 'order_status';
					$fields[] = 'created_date';
					$fields[] = 'modified_date';
					$fields[] = 'product_id';
					$fields[] = 'product_name';
					$fields[] = 'option_name';
					$fields[] = 'option_value';
					$fields[] = 'option_sku';
					$fields[] = 'model';
					$fields[] = 'quantity';
					$fields[] = 'unit_price';
					$fields[] = 'unit_total';
				}

				foreach ($rows as $row)
				{
					$row->order_id           = $row->id;
					$row->customer_firstname = $row->firstname;
					$row->customer_lastname  = $row->lastname;
					$row->customer_email     = $row->email;
					$row->customer_telephone = $row->telephone;
					$row->customer_fax       = $row->fax;
					$row->comment            = strip_tags($row->comment);

					$query->clear()
						->select('text')
						->from('#__eshop_ordertotals')
						->where('order_id = ' . intval($row->id))
						->where('(name = "shipping" OR name="tax")')
						->order('name ASC');
					$db->setQuery($query);
					$orderTotals = $db->loadColumn();

					$row->shipping_amount = $orderTotals[0] ?? '';
					$row->tax_amount      = $orderTotals[1] ?? '';
					$row->total           = $currency->format($row->total, $row->currency_code, $row->currency_exchanged_value);
					$row->order_status    = EShopHelper::getOrderStatusName(
						$row->order_status_id,
						ComponentHelper::getParams('com_languages')->get('site', 'en-GB')
					);

					if ($row->created_date != $nullDate)
					{
						$row->created_date = HTMLHelper::_('date', $row->created_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'), null);
					}
					else
					{
						$row->created_date = '';
					}

					if ($row->modified_date != $nullDate)
					{
						$row->modified_date = HTMLHelper::_('date', $row->modified_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'), null);
					}
					else
					{
						$row->modified_date = '';
					}

					$query->clear();
					$query->select('*')
						->from('#__eshop_orderproducts')
						->where('order_id = ' . intval($row->id));

					$db->setQuery($query);
					$orderProducts = $db->loadObjectList();

					for ($i = 0; $n = count($orderProducts), $i < $n; $i++)
					{
						$orderProductRow = clone $row;

						if ($i > 0)
						{
							$orderProductRow->order_id           = '';
							$orderProductRow->order_number       = '';
							$orderProductRow->invoice_number     = '';
							$orderProductRow->customer_firstname = '';
							$orderProductRow->customer_lastname  = '';
							$orderProductRow->customer_email     = '';
							$orderProductRow->customer_telephone = '';
							$orderProductRow->customer_fax       = '';
							$orderProductRow->transaction_id     = '';
							$orderProductRow->shipping_amount    = '';
							$orderProductRow->tax_amount         = '';
							$orderProductRow->total              = '';
							$orderProductRow->comment            = '';
							$orderProductRow->order_status       = '';
							$orderProductRow->created_date       = '';
							$orderProductRow->modified_date      = '';
						}

						$orderProductRow->product_id   = $orderProducts[$i]->product_id;
						$orderProductRow->product_name = $orderProducts[$i]->product_name;
						$orderProductRow->model        = $orderProducts[$i]->product_sku;
						$orderProductRow->quantity     = $orderProducts[$i]->quantity;
						$orderProductRow->unit_price   = $currency->format(
							$orderProducts[$i]->price,
							$row->currency_code,
							$row->currency_exchanged_value
						);
						$orderProductRow->unit_total   = $currency->format(
							$orderProducts[$i]->total_price,
							$row->currency_code,
							$row->currency_exchanged_value
						);

						$query->clear();
						$query->select('*')
							->from('#__eshop_orderoptions')
							->where('order_product_id = ' . intval($orderProducts[$i]->id));

						$db->setQuery($query);
						$options = $db->loadObjectList();

						if (count($options))
						{
							for ($j = 0; $m = count($options), $j < $m; $j++)
							{
								$optionRow = clone $orderProductRow;

								if ($j > 0)
								{
									$optionRow->order_id           = '';
									$optionRow->order_number       = '';
									$optionRow->invoice_number     = '';
									$optionRow->customer_firstname = '';
									$optionRow->customer_lastname  = '';
									$optionRow->customer_email     = '';
									$optionRow->customer_telephone = '';
									$optionRow->customer_fax       = '';
									$optionRow->transaction_id     = '';
									$optionRow->shipping_amount    = '';
									$optionRow->tax_amount         = '';
									$optionRow->total              = '';
									$optionRow->comment            = '';
									$optionRow->order_status       = '';
									$optionRow->created_date       = '';
									$optionRow->modified_date      = '';
									$optionRow->product_id         = '';
									$optionRow->product_name       = '';
									$optionRow->model              = '';
									$optionRow->quantity           = '';
									$optionRow->unit_price         = '';
									$optionRow->unit_total         = '';
								}

								$optionRow->option_name  = $options[$j]->option_name;
								$optionRow->option_value = $options[$j]->option_value;
								$optionRow->option_sku   = $options[$j]->sku;

								$orderRows[] = $optionRow;
							}
						}
						else
						{
							$orderRows[] = $orderProductRow;
						}
					}
				}

				$filename = 'orders_' . date('YmdHis') . '.' . $exportFormat;
				$filePath = EShopHelper::excelExport($fields, $orderRows, $filename, $fields, $exportFormat);
				EShopHelper::processDownload($filePath, $filename, true);
				Factory::getApplication()->close();
			}
			//end csv/xlsx export
			else
			{
				foreach ($rows as $row)
				{
					//list product order
					$orderProductXmlArray = [];
					$query->clear();
					$query->select('*')
						->from('#__eshop_orderproducts')
						->where('order_id = ' . intval($row->id));
					$db->setQuery($query);
					$orderProducts = $db->loadObjectList();
					foreach ($orderProducts as $orderProduct)
					{
						$productXmlArray        = [
							'sku'          => $orderProduct->product_sku,
							'product_name' => $orderProduct->product_name,
							'quantity'     => $orderProduct->quantity,
							'unit_price'   => $orderProduct->price,
						];
						$orderProductXmlArray[] = $productXmlArray;
					}
					//end list product order

					//start customer xml
					$customerXml = [
						'firstname' => $row->firstname,
						'lastname'  => $row->lastname,
						'email'     => $row->email,
						'telephone' => $row->telephone,
						'fax'       => $row->fax,
					];
					//end customer xml


					$orderXmlArray                 = [];
					$xmlarray['Orders']['Order'][] = [
						'order_id'                 => $row->id,
						'order_number'             => $row->order_number,
						'order_date'               => ($row->created_date != $nullDate) ? HTMLHelper::_(
							'date',
							$row->created_date,
							EShopHelper::getConfigValue('date_format', 'm-d-Y'),
							null
						) : '',
						'invoice_number'           => $row->invoice_number,
						'payment_firstname'        => $row->payment_firstname,
						'payment_lastname'         => $row->payment_lastname,
						'payment_email'            => $row->payment_email,
						'payment_telephone'        => $row->payment_telephone,
						'payment_fax'              => $row->payment_fax,
						'payment_company'          => $row->payment_company,
						'payment_company_id'       => $row->payment_company_id,
						'payment_address_1'        => $row->payment_address_1,
						'payment_address_2'        => $row->payment_address_2,
						'payment_city'             => $row->payment_city,
						'payment_postcode'         => $row->payment_postcode,
						'payment_country_name'     => $row->payment_country_name,
						'payment_zone_name'        => $row->payment_zone_name,
						'payment_method'           => $row->payment_method,
						'payment_method_title'     => $row->payment_method_title,
						'transaction_id'           => $row->transaction_id,
						'shipping_firstname'       => $row->shipping_firstname,
						'shipping_lastname'        => $row->shipping_lastname,
						'shipping_email'           => $row->shipping_email,
						'shipping_telephone'       => $row->shipping_telephone,
						'shipping_fax'             => $row->shipping_fax,
						'shipping_company'         => $row->shipping_company,
						'shipping_company_id'      => $row->shipping_company_id,
						'shipping_address_1'       => $row->shipping_address_1,
						'shipping_address_2'       => $row->shipping_address_2,
						'shipping_city'            => $row->shipping_city,
						'shipping_postcode'        => $row->shipping_postcode,
						'shipping_country_name'    => $row->shipping_country_name,
						'shipping_zone_name'       => $row->shipping_zone_name,
						'shipping_method'          => $row->shipping_method,
						'shipping_method_title'    => $row->shipping_method_title,
						'shipping_tracking_number' => $row->shipping_tracking_number,
						'shipping_tracking_url'    => $row->shipping_tracking_url,
						'Orderlines'               => ['line' => $orderProductXmlArray,],
						'customers'                => $customerXml,
					];
					$orderXmlArray[]               = $xmlarray;
				}

				$filename = 'orders_' . date('YmdHis') . '.xml';
				include_once JPATH_ROOT . '/components/com_eshop/helpers/array2xml.php';
				$xml = Array2XML::createXML('SConnectData', $orderXmlArray[0]);
				File::write(JPATH_ROOT . '/media/com_eshop/files/' . $filename, $xml->saveXML());
				EShopHelper::processDownload(JPATH_ROOT . '/media/com_eshop/files/' . $filename, $filename, true);
				exit();
			}
		}
		else
		{
			$mainframe = Factory::getApplication();
			$mainframe->enqueueMessage(Text::_('ESHOP_NO_DATA_TO_EXPORT'), 'notice');
			$mainframe->redirect(
				'index.php?option=com_eshop&view=reports&layout=orders&date_start=' . $dateStart . '&date_end=' . $dateEnd . '&group_by=' . $groupBy . '&order_status_id=' . $orderStatusId
			);
		}
	}

	/**
	 *
	 * Function to export google feed
	 */
	public function _exportGoogleFeed()
	{
		$input                     = Factory::getApplication()->input;
		$exportFormat              = $input->getString('export_format', EShopHelper::getConfigValue('export_data_format', 'csv'));
		$language                  = $input->getString('language', 'en-GB');
		$productStatus             = $input->getInt('product_status', 1);
		$removeZeroPriceProducts   = $input->getInt('remove_zero_price_products', '0');
		$removeOutOfStockProducts  = $input->getInt('remove_out_of_stock_products', '0');
		$googleId                  = $input->getInt('google_id', '1');
		$googleTitle               = $input->getInt('google_title', '1');
		$googleDescription         = $input->getInt('google_description', '1');
		$googleProductType         = $input->getInt('google_product_type', '1');
		$googleLink                = $input->getInt('google_link', '1');
		$googleMobileLink          = $input->getInt('google_mobile_link', '1');
		$googleImageLink           = $input->getInt('google_image_link', '1');
		$googleAdditionalImageLink = $input->getInt('google_additional_image_link', '1');
		$googleAvailability        = $input->getInt('google_availability', '1');
		$googlePrice               = $input->getInt('google_price', '1');
		$googleSalePrice           = $input->getInt('google_sale_price', '1');
		$googleMpn                 = $input->getInt('google_mpn', '1');
		$googleBrand               = $input->getInt('google_brand', '1');
		$googleShippingWeight      = $input->getInt('google_shipping_weight', '1');
		$googleAlias               = $input->getInt('google_alias', '1');

		$startRecord  = $input->getInt('start_record', 0);
		$totalRecords = $input->getInt('total_records', 0);

		$db          = Factory::getDbo();
		$languageSql = $db->quote($language);
		$query       = $db->getQuery(true);
		$query->select('a.*, b.*')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('b.language = ' . $languageSql);

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

				$filename = 'google_feed_' . date('YmdHis') . '.' . $exportFormat;
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

				$filename = 'google_feed_' . date('YmdHis') . '.xml';
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
			$mainframe->redirect('index.php?option=com_eshop&view=exports');
		}
	}

	/**
	 *
	 * Function to export pinterest feed
	 */
	public function _exportPinterestFeed()
	{
		$input                 = Factory::getApplication()->input;
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
		$query->select('a.*, b.*')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('b.language = ' . $languageSql);

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
					//Get product manufacturer
					$manufacturer = EShopHelper::getProductManufacturer($row->product_id, $language);

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
						$row->link = EShopHelper::getSiteUrl() . EShopRoute::getProductRoute($row->product_id, $categoryId);
					}

					if ($pinterestImageLink)
					{
						$row->image_link = EShopHelper::getSiteUrl() . 'media/com_eshop/products/' . $row->product_image;
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

					$defaultCurrency = EShopHelper::getConfigValue('default_currency_code');

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
						if (is_object($manufacturer))
						{
							$row->brand = $manufacturer->manufacturer_name;
						}
						else
						{
							$row->brand = '';
						}
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

					//Get product manufacturer
					$manufacturer = EShopHelper::getProductManufacturer($row->product_id, $language);

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

					$defaultCurrency = EShopHelper::getConfigValue('default_currency_code');

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
							$productArray1['link'] = EShopHelper::getSiteUrl() . EShopRoute::getProductRoute($row->product_id, $categoryId);
						}

						if ($pinterestImageLink)
						{
							$productArray1['g:image_link'] = EShopHelper::getSiteUrl() . 'media/com_eshop/products/' . $row->product_image;
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
							$productArray1['g:brand'] = $manufacturer->manufacturer_name;
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
			$mainframe->redirect('index.php?option=com_eshop&view=exports');
		}
	}

	/**
	 * Cancel the exports
	 *
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eshop&view=dashboard');
	}
}