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

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * EShop Component Configuration Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelMigrate extends BaseDatabaseModel
{
	/**
	 * Containing all config data,  store in an object with key, value
	 *
	 * @var object
	 */
	var $_data = null;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Store the configuration data
	 *
	 * @param   array  $post
	 *
	 * @return Boolean
	 */
	public function migrateVE()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->clear()->select('*')->from('#__languages')->where('published=1');
		$db->setQuery($query);
		$languages = $db->loadObjectList();

		//categories vm
		$query->clear()->select('*')->from('#__virtuemart_categories');
		$db->setQuery($query);
		$categories = $db->loadObjectList('virtuemart_category_id');

		// categories vm parent
		$query->clear()->select('id, category_parent_id')->from('#__virtuemart_category_categories');
		$db->setQuery($query);
		$categories_parents = $db->loadAssocList('id', 'category_parent_id');

		// categories vm image
		$query->clear()
			->select('a.virtuemart_category_id')
			->from('#__virtuemart_category_medias AS a')
			->select('b.file_url')
			->leftJoin('#__virtuemart_medias AS b ON a.virtuemart_media_id=b.virtuemart_media_id');
		$db->setQuery($query);
		$categories_images = $db->loadAssocList('virtuemart_category_id', 'file_url');

		// eshop categories, parent, images
		$mapping_categories    = [];
		$image_categories_Path = JPATH_ROOT . '/media/com_eshop/categories/';
		foreach ($categories as $category)
		{
			$row = new EShopTable('#__eshop_categories', 'id', $db);
			// upload image category
			if (isset($categories_images[$category->virtuemart_category_id]) && $categories_images[$category->virtuemart_category_id] != '')
			{
				$categoryImage = pathinfo($categories_images[$category->virtuemart_category_id]);
				$imageFileName = File::makeSafe($categoryImage['basename']);
				if (is_file($image_categories_Path . $categoryImage['basename']))
				{
					$imageFileName = uniqid('image_') . '_' . File::makeSafe($categoryImage['basename']);
				}
				if (is_file(JPATH_ROOT . '/' . $categories_images[$category->virtuemart_category_id]))
				{
					$rel = File::copy(
						JPATH_ROOT . '/' . $categories_images[$category->virtuemart_category_id],
						$image_categories_Path . $imageFileName
					);
					if ($rel)
					{
						$row->category_image = $imageFileName;
					}
				}
			}

			// assign data
			$row->category_parent_id = 0;
			$row->products_per_row   = $category->products_per_row;
			$row->published          = $category->published;
			$row->ordering           = $category->ordering;
			$row->hits               = $category->hits;
			$row->created_date       = $category->created_on;
			$row->created_by         = $category->created_by;
			$row->modified_date      = $category->modified_on;
			$row->modified_by        = $category->modified_by;
			$row->checked_out        = $category->locked_by;
			$row->checked_out_time   = $category->locked_on;
			//$row->category_customergroups =$imageFileName;
			//$row->products_per_page =$imageFileName;

			if ($row->store())
			{
				$mapping_categories[$category->virtuemart_category_id] = $row->id;
			}
		}

		// update parent catogory
		foreach ($mapping_categories as $virtuemart_category_id => $eshop_catid)
		{
			if (!$categories_parents[$virtuemart_category_id])
			{
				continue;
			}
			$row = new EShopTable('#__eshop_categories', 'id', $db);
			$row->load($eshop_catid);
			$row->category_parent_id = $mapping_categories[$categories_parents[$virtuemart_category_id]];
			$row->store();
		}

		// eshop categories database
		foreach ($languages as $language)
		{
			$search = 'virtuemart_categories_' . strtolower(str_replace('-', '_', $language->lang_code));
			$search = $db->quote('%' . trim($search) . '%');
			$db->setQuery("SHOW TABLES LIKE $search");
			$table_category = $db->loadResult();
			if ($table_category != '')
			{
				$query->clear()->select('*')->from($table_category);
				$db->setQuery($query);
				$categories_datas = $db->loadObjectList('virtuemart_category_id');
				foreach ($categories_datas as $category_data)
				{
					if ($mapping_categories[$category_data->virtuemart_category_id])
					{
						$row                = new EShopTable('#__eshop_categorydetails', 'id', $db);
						$row->category_id   = $mapping_categories[$category_data->virtuemart_category_id];
						$row->language      = trim($language->lang_code);
						$row->category_name = $category_data->category_name;
						if (empty($category_data->slug))
						{
							$row->category_alias = ApplicationHelper::stringUrlSafe($row->category_name);
						}
						else
						{
							$row->category_alias = $category_data->slug;
						}
						$row->category_desc       = $category_data->category_description;
						$row->category_page_title = $category_data->customtitle;
						//$row->category_page_heading = $category_data->slug;
						$row->meta_key  = $category_data->metakey;
						$row->meta_desc = $category_data->metadesc;
						$row->store();
					}
				}
			}
		}

		// manufacturers vm
		$query->clear()->select('*')->from('#__virtuemart_manufacturers');
		$db->setQuery($query);
		$manufactures = $db->loadObjectList('virtuemart_manufacturer_id');

		// manufacturers vm images
		$query->clear()
			->select('a.virtuemart_manufacturer_id')
			->from('#__virtuemart_manufacturer_medias AS a')
			->select('b.file_url')
			->leftJoin('#__virtuemart_medias AS b ON a.virtuemart_media_id=b.virtuemart_media_id');
		$db->setQuery($query);
		$manufactures_images = $db->loadAssocList('virtuemart_manufacturer_id', 'file_url');

		// eshop manufacturers, images
		$mapping_manufactures     = [];
		$image_manufacturers_Path = JPATH_ROOT . '/media/com_eshop/manufacturers/';
		foreach ($manufactures as $manufacture)
		{
			$row = new EShopTable('#__eshop_manufacturers', 'id', $db);
			if (isset($manufactures_images[$manufacture->virtuemart_manufacturer_id]) && $manufactures_images[$manufacture->virtuemart_manufacturer_id] != '')
			{
				$manufactureImage = pathinfo($manufactures_images[$manufacture->virtuemart_manufacturer_id]);
				$imageFileName    = File::makeSafe($manufactureImage['basename']);
				if (is_file($image_manufacturers_Path . $manufactureImage['basename']))
				{
					$imageFileName = uniqid('image_') . '_' . File::makeSafe($manufactureImage['basename']);
				}
				if (is_file(JPATH_ROOT . '/' . $manufactures_images[$manufacture->virtuemart_manufacturer_id]))
				{
					$rel = File::copy(
						JPATH_ROOT . '/' . $manufactures_images[$manufacture->virtuemart_manufacturer_id],
						$image_manufacturers_Path . $imageFileName
					);
					if ($rel)
					{
						$row->manufacturer_image = $imageFileName;
					}
				}
			}

			// assign database
			//$row->manufacturer_email            = $imageFileName;
			//$row->manufacturer_url              = $imageFileName;
			//$row->manufacturer_customergroups   = $imageFileName;
			$row->published = $manufacture->published;
			//$row->ordering                      = $imageFileName;
			$row->hits             = $manufacture->hits;
			$row->created_date     = $manufacture->created_on;
			$row->created_by       = $manufacture->created_by;
			$row->modified_date    = $manufacture->modified_on;
			$row->modified_by      = $manufacture->modified_by;
			$row->checked_out      = $manufacture->locked_by;
			$row->checked_out_time = $manufacture->locked_on;
			if ($row->store())
			{
				$mapping_manufactures[$manufacture->virtuemart_manufacturer_id] = $row->id;
			}
		}

		// eshop manufacturers database
		foreach ($languages as $language)
		{
			$search = 'virtuemart_manufacturers_' . strtolower(str_replace('-', '_', $language->lang_code));
			$search = $db->quote('%' . trim($search) . '%');
			$db->setQuery("SHOW TABLES LIKE $search");
			$table_manufacturer = $db->loadResult();
			if ($table_manufacturer != '')
			{
				$query->clear()->select('*')->from($table_manufacturer);
				$db->setQuery($query);
				$manufacturers_datas = $db->loadObjectList('virtuemart_manufacturer_id');
				foreach ($manufacturers_datas as $manufacturer_data)
				{
					if ($mapping_manufactures[$manufacturer_data->virtuemart_manufacturer_id])
					{
						// update email and url
						$row = new EShopTable('#__eshop_manufacturers', 'id', $db);
						$row->load($mapping_manufactures[$manufacturer_data->virtuemart_manufacturer_id]);
						$row->manufacturer_email = $manufacturer_data->mf_email;
						$row->manufacturer_url   = $manufacturer_data->mf_url;
						$row->store();

						// save database
						$row                    = new EShopTable('#__eshop_manufacturerdetails', 'id', $db);
						$row->manufacturer_id   = $mapping_manufactures[$manufacturer_data->virtuemart_manufacturer_id];
						$row->language          = trim($language->lang_code);
						$row->manufacturer_name = $manufacturer_data->mf_name;
						if (empty($manufacturer_data->slug))
						{
							$row->manufacturer_alias = ApplicationHelper::stringUrlSafe($row->manufacturer_name);
						}
						else
						{
							$row->manufacturer_alias = $manufacturer_data->slug;
						}
						$row->manufacturer_desc = $manufacturer_data->mf_desc;
						//$row->manufacturer_page_title   = $manufacturer_data->customtitle;
						//$row->manufacturer_page_heading = $manufacturer_data->slug;
						$row->store();
					}
				}
			}
		}


		// products vm
		$query->clear()->select('*')->from('#__virtuemart_products');
		$db->setQuery($query);
		$products = $db->loadObjectList('virtuemart_product_id');

		// products category vm
		$query->clear()->select('*')->from('#__virtuemart_product_categories');
		$db->setQuery($query);
		$products_categories = $db->loadObjectList();

		// products manufacturers vm
		$query->clear()->select('DISTINCT virtuemart_product_id, virtuemart_manufacturer_id')->from('#__virtuemart_product_manufacturers');
		$db->setQuery($query);
		$products_manus = $db->loadAssocList('virtuemart_product_id', 'virtuemart_manufacturer_id');

		// products image vm
		$query->clear()
			->select('a.virtuemart_product_id')
			->from('#__virtuemart_product_medias AS a')
			//->select('b.file_url')
			->select('b.*')
			->innerJoin('#__virtuemart_medias AS b ON a.virtuemart_media_id=b.virtuemart_media_id');
		$db->setQuery($query);
		$products_images = $db->loadObjectList();
		// upload image
		$mapping_products_images = [];
		$imageProductPath        = JPATH_ROOT . '/media/com_eshop/products/';
		foreach ($products_images as $image)
		{
			$productImage  = pathinfo($image->file_url);
			$imageFileName = File::makeSafe($productImage['basename']);
			if (is_file($imageProductPath . $imageFileName))
			{
				$imageFileName = uniqid('image_') . '_' . File::makeSafe($productImage['basename']);
			}
			if (is_file(JPATH_ROOT . '/' . $image->file_url))
			{
				$rel = File::copy(JPATH_ROOT . '/' . $image->file_url, $imageProductPath . $imageFileName);
				if ($rel)
				{
					$image->image = $imageFileName;
				}
			}
			$mapping_products_images[$image->virtuemart_product_id][] = $image;
		}

		// products price vm
		$query->clear()
			->select('virtuemart_product_id, product_price')
			->from('#__virtuemart_product_prices');
		$db->setQuery($query);
		$products_prices = $db->loadAssocList('virtuemart_product_id', 'product_price');

		// eshop product, image;
		$imagePath        = JPATH_ROOT . '/media/com_eshop/products/';
		$mapping_products = [];
		foreach ($products as $product)
		{
			// save product and main image
			$row = new EShopTable('#__eshop_products', 'id', $db);
			if (isset($products_prices[$product->virtuemart_product_id]))
			{
				$product_price          = $products_prices[$product->virtuemart_product_id];
				$product_call_for_price = 0;
			}
			else
			{
				$product_call_for_price = 1;
				$product_price          = 0;
			}
			$product_minimum_quantity = 0;
			$product_maximum_quantity = 0;
			$product_params           = [];
			if ($product->product_params != '')
			{
				$params = explode('|', $product->product_params);
				foreach ($params as $param)
				{
					if ($param != '')
					{
						[$index, $value] = explode('=', $param);
						$product_params[$index] = substr($value, 1, strlen($value) - 2);
					}
				}
			}
			if (isset($product_params['min_order_level']))
			{
				$product_minimum_quantity = $product_params['min_order_level'];
			}
			if (isset($product_params['max_order_level']))
			{
				$product_maximum_quantity = $product_params['max_order_level'];
			}

			$row->manufacturer_id          = $mapping_manufactures[$products_manus[$product->virtuemart_product_id]];
			$row->product_sku              = $product->product_sku;
			$row->product_weight           = $product->product_weight;
			$row->product_weight_id        = 1;
			$row->product_length           = $product->product_length;
			$row->product_width            = $product->product_width;
			$row->product_height           = $product->product_height;
			$row->product_length_id        = 1;
			$row->product_price            = $product_price;
			$row->product_call_for_price   = $product_call_for_price;
			$row->product_taxclass_id      = 1;
			$row->product_quantity         = $product->product_in_stock;
			$row->product_minimum_quantity = $product_minimum_quantity;
			$row->product_maximum_quantity = $product_maximum_quantity;
			//$row->product_shipping          = $product->
			//$row->product_shipping_cost     = $product->
			if (count($mapping_products_images[$product->virtuemart_product_id]))
			{
				$row->product_image = $mapping_products_images[$product->virtuemart_product_id][0];
			}
			$row->product_available_date = $product->product_available_date;
			$row->product_featured       = $product->product_special;
			//$row->product_customergroups    = $product->
			//$row->product_stock_status_id   = $product->
			//$row->product_quote_mode        = $product->
			$row->published        = $product->published;
			$row->ordering         = $product->pordering;
			$row->hits             = $product->hits;
			$row->created_date     = $product->created_on;
			$row->created_by       = $product->created_by;
			$row->modified_date    = $product->modified_on;
			$row->modified_by      = $product->modified_by;
			$row->checked_out      = $product->locked_by;
			$row->checked_out_time = $product->locked_on;
			if ($row->store())
			{
				$mapping_products[$product->virtuemart_product_id] = $row->id;
			}

			if ($row->id)
			{
				unset($mapping_products_images[$product->virtuemart_product_id][0]);
				// save extra image
				foreach ($mapping_products_images[$product->virtuemart_product_id] as $image)
				{
					$row                   = new EShopTable('#__eshop_productimages', 'id', $db);
					$row->id               = 0;
					$row->product_id       = $mapping_products[$product->virtuemart_product_id];
					$row->image            = $image->image;
					$row->published        = published;
					$row->ordering         = 1;
					$row->created_date     = $image->created_on;
					$row->created_by       = $image->created_by;
					$row->modified_date    = $image->modified_on;
					$row->modified_by      = $image->modified_by;
					$row->checked_out      = $image->locked_by;
					$row->checked_out_time = $image->locked_on;
					$row->store();
				}
			}
		}

		// product relation category
		foreach ($products_categories as $products_category)
		{
			$product_id  = $mapping_products[$products_category->virtuemart_product_id];
			$category_id = $mapping_categories[$products_category->virtuemart_category_id];
			$query->clear()->insert('#__eshop_productcategories')->values("null,$product_id,$category_id");
			$db->setQuery($query);
			$db->execute();
		}

		// eshop Products database
		foreach ($languages as $language)
		{
			$search = 'virtuemart_products_' . strtolower(str_replace('-', '_', $language->lang_code));
			$search = $db->quote('%' . trim($search) . '%');
			$db->setQuery("SHOW TABLES LIKE $search");
			$table_product = $db->loadResult();
			if ($table_product != '')
			{
				$query->clear()->select('*')->from($table_product);
				$db->setQuery($query);
				$products_datas = $db->loadObjectList('virtuemart_product_id');
				foreach ($products_datas as $products_data)
				{
					if ($mapping_products[$products_data->virtuemart_product_id])
					{
						// save database
						$row             = new EShopTable('#__eshop_productdetails', 'id', $db);
						$row->product_id = $mapping_products[$products_data->virtuemart_product_id];
						$row->language   = trim($language->lang_code);

						$row->product_name = $products_data->product_name;
						if (empty($products_data->slug))
						{
							$row->product_alias = ApplicationHelper::stringUrlSafe($row->product_name);
						}
						else
						{
							$row->product_alias = $products_data->slug;
						}
						$row->product_desc         = $products_data->product_desc;
						$row->product_short_desc   = $products_data->product_s_desc;
						$row->product_page_title   = $products_data->customtitle;
						$row->product_page_heading = $products_data->customtitle;
						$row->meta_key             = $products_data->metakey;
						$row->meta_desc            = $products_data->metadesc;
//                         $row->product_tag        = ;
//                         $row->tab1_title         = ;
//                         $row->tab1_content       = ;
//                         $row->tab2_title         = ;
//                         $row->tab2_content       = ;
//                         $row->tab3_title         = ;
//                         $row->tab3_content       = ;
						$row->store();
					}
				}
			}
		}
	}
}