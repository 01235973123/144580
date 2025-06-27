<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2011 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Uri\Uri;

class modEShopProductHelper
{

	static public function getItems($params)
	{
		//get config xml
		$numberProducts = $params->get('number_products', 6);
		$display        = $params->get('display', 'featured');
		$language       = Factory::getLanguage();
		$tag            = $language->getTag();

		if (!$tag)
		{
			$tag = 'en-GB';
		}

		$language->load('com_eshop', JPATH_ROOT, $tag);
		$categoryIds = $params->get('category_ids');
		$productIds  = $params->get('product_ids');
		//check limit product
		$limit = '';

		if ($numberProducts > 0)
		{
			$limit = ' LIMIT 0, ' . $numberProducts;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, c.product_name, c.product_alias, c.product_short_desc')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productcategories AS b ON a.id = b.product_id')
			->innerJoin('#__eshop_productdetails AS c ON a.id = c.product_id')
			->where('a.published = 1')
			->where('c.language = ' . $db->quote($tag))
			->group('b.product_id');

		if (!empty($productIds))
		{
			$query->where('a.id IN (' . implode(',', $productIds) . ')');
		}
		else
		{
			if (!empty($categoryIds))
			{
				$query->where('b.category_id IN (' . implode(',', $categoryIds) . ')');
			}
		}

		if ($display == 'featured')
		{
			$query->where('a.product_featured=1');

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
				'product_best_sellers',
				'RAND()',
			];
			$allowedDirectArr = ['ASC', 'DESC'];
			$defaultSorting   = EShopHelper::getConfigValue('default_sorting', '');

			if ($defaultSorting != '')
			{
				$defaultSorting = explode('-', $defaultSorting);

				if (isset($defaultSorting[0]) && in_array($defaultSorting[0], $allowedSortArr))
				{
					$sort = $defaultSorting[0];
				}
				else
				{
					$sort = 'a.ordering';
				}

				if (isset($defaultSorting[1]) && in_array($defaultSorting[1], $allowedDirectArr))
				{
					$direct = $defaultSorting[1];
				}
				else
				{
					$direct = 'ASC';
				}

				$sort = str_replace('b.', 'c.', $sort);

				$query->order($sort . ' ' . $direct)
					->order('a.ordering' . $limit);
			}
			else
			{
				$query->order('a.ordering' . $limit);
			}
		}
		elseif ($display == 'latest')
		{
			$query->order('a.id DESC' . $limit);
		}
		elseif ($display == 'random')
		{
			$query->order('RAND()' . $limit);
		}
		elseif ($display == 'most_viewed')
		{
			$query->order('a.hits DESC' . $limit);
		}
		elseif ($display == 'recently_viewed')
		{
			$session          = Factory::getApplication()->getSession();
			$viewedProductIds = $session->get('viewed_product_ids');

			if (!empty($viewedProductIds))
			{
				$query->where('a.id IN (' . implode(',', $viewedProductIds) . ')');
				$query->order('FIND_IN_SET (a.id, "' . implode(',', $viewedProductIds) . '")' . $limit);
			}
			else
			{
				$query->where('a.id = 0');
			}
		}
		elseif ($display == 'most_rated')
		{
			$query->clear('select');
			$query->select('a.*, c.product_name, c.product_short_desc, COUNT(*) AS reviews')
				->leftJoin('#__eshop_reviews AS r ON (a.id = r.product_id AND r.published = 1)')
				->order('reviews DESC' . $limit);
		}
		elseif ($display == 'top_rated')
		{
			$query->clear('select');
			$query->select('a.*, c.product_name, c.product_short_desc, AVG(r.rating) AS rates')
				->leftJoin('#__eshop_reviews AS r ON (a.id = r.product_id AND r.published = 1)')
				->order('rates DESC' . $limit);
		}
		elseif ($display == 'top_sellers')
		{
			$query->clear('select');
			$query->select('a.*, c.product_name, c.product_short_desc, COUNT(*) AS sellers')
				->leftJoin('#__eshop_orderproducts AS op ON (a.id = op.product_id)')
				->innerJoin(
					'#__eshop_orders AS o ON (op.order_id = o.id AND o.order_status_id = ' . EShopHelper::getConfigValue('complete_status_id') . ')'
				)
				->order('sellers DESC' . $limit);
		}
		elseif ($display == 'related')
		{
			$productId = Factory::getApplication()->input->getInt('id');

			$query->where('a.id IN (SELECT related_product_id FROM #__eshop_productrelations WHERE product_id = ' . intval($productId) . ')')
				->order('a.ordering ASC' . $limit);
		}
		elseif ($display == 'alpha_az')
		{
			$query->order('c.product_name ASC' . $limit);
		}
		elseif ($display == 'alpha_za')
		{
			$query->order('c.product_name DESC' . $limit);
		}
		elseif ($display == 'price_low_high')
		{
			$query->order('a.product_price ASC' . $limit);
		}
		elseif ($display == 'price_high_low')
		{
			$query->order('a.product_price DESC' . $limit);
		}
		elseif ($display == 'ordering_low_high')
		{
			$query->order('a.ordering ASC' . $limit);
		}
		elseif ($display == 'ordering_high_low')
		{
			$query->order('a.ordering DESC' . $limit);
		}
		else
		{
			$query->order('a.ordering ASC' . $limit);
		}

		//Check viewable of customer groups
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$query->where(
			'((a.product_customergroups = "") OR (a.product_customergroups IS NULL) OR (a.product_customergroups = "' . $customerGroupId . '") OR (a.product_customergroups LIKE "' . $customerGroupId . ',%") OR (a.product_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.product_customergroups LIKE "%,' . $customerGroupId . '"))'
		);

		//Check out of stock
		if ($params->get('hide_out_of_stock_products', 0))
		{
			$query->where('a.product_quantity > 0');
		}
		
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());
		
		$query->where(
			'(a.product_available_date = ' . $nullDate . ' OR a.product_available_date IS NULL OR a.product_available_date <= ' . $currentDate . ')'
			);
		$query->where('(a.product_end_date = ' . $nullDate . ' OR a.product_end_date IS NULL OR a.product_end_date >= ' . $currentDate . ')');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$baseUri           = Uri::base(true);
		$imageSizeFunction = $params->get('image_resize_function', 'resizeImage');
		$thumbnailWidth    = $params->get('image_width', 100);
		$thumbnailHeight   = $params->get('image_height', 100);

		$langCode = Factory::getLanguage()->getTag();

		$productIds = [];

		foreach ($rows as $row)
		{
			$productIds[] = $row->id;
		}

		$productsImages       = EShopHelper::getProductsImages($productIds);
		$mainCategoryIds      = EShopHelper::getProductsMainCategory($productIds);
		$requiredOptionsCount = EShopHelper::getNumberRequiredOptionsForProducts($productIds);

		for ($i = 0; $n = count($rows), $i < $n; $i++)
		{
			$row = $rows[$i];

			// Product main image
			if ($row->product_image && is_file((JPATH_ROOT . '/media/com_eshop/products/' . $row->product_image)))
			{
				if (EShopHelper::getConfigValue('product_use_image_watermarks'))
				{
					$watermarkImage = EShopHelper::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/products/' . $row->product_image);
					$productImage   = $watermarkImage;
				}
				else
				{
					$productImage = $row->product_image;
				}

				$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
					[$productImage, JPATH_ROOT . '/media/com_eshop/products/', $thumbnailWidth, $thumbnailHeight]);
			}
			else
			{
				$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
					[
						EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
						JPATH_ROOT . '/media/com_eshop/products/',
						$thumbnailWidth,
						$thumbnailHeight,
					]);
			}

			if ($imageSizeFunction == 'notResizeImage')
			{
				$row->image = $baseUri . '/media/com_eshop/products/' . $image;
			}
			else
			{
				$row->image = $baseUri . '/media/com_eshop/products/resized/' . $image;
			}

			//Product additional image
			$productImages = $productsImages[$row->id] ?? [];

			if (isset($mainCategoryIds[$row->id]))
			{
				$row->product_main_category_id = $mainCategoryIds[$row->id]->category_id;
			}
			else
			{
				$row->product_main_category_id = 0;
			}

			if (isset($requiredOptionsCount[$row->id]))
			{
				$row->has_required_otpion =  $requiredOptionsCount[$row->id]->number_required_options > 0;
			}
			else
			{
				$row->has_required_otpion = false;
			}

			if (count($productImages) > 0)
			{
				$productImage = $productImages[0]->image;

				if ($productImage && is_file((JPATH_ROOT . '/media/com_eshop/products/' . $productImage)))
				{
					if (EShopHelper::getConfigValue('product_use_image_watermarks'))
					{
						$watermarkImage = EShopHelper::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/products/' . $productImage);
						$productImage   = $watermarkImage;
					}

					$additionalProductImage = call_user_func_array(['EShopHelper', $imageSizeFunction],
						[$productImage, JPATH_ROOT . '/media/com_eshop/products/', $thumbnailWidth, $thumbnailHeight]);
				}
				else
				{
					$additionalProductImage = call_user_func_array(['EShopHelper', $imageSizeFunction],
						[
							EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
							JPATH_ROOT . '/media/com_eshop/products/',
							$thumbnailWidth,
							$thumbnailHeight,
						]);
				}

				if ($imageSizeFunction == 'notResizeImage')
				{
					$row->additional_image = $baseUri . '/media/com_eshop/products/' . $additionalProductImage;
				}
				else
				{
					$row->additional_image = $baseUri . '/media/com_eshop/products/resized/' . $additionalProductImage;
				}
			}

			$row->labels = EShopHelper::getProductLabels($row->id);

			EShopHelper::$productsAlias[$langCode . '.' . $row->id] = strlen($row->product_alias ?? '') > 0 ? $row->product_alias : $row->product_name;
		}

		return $rows;
	}
}