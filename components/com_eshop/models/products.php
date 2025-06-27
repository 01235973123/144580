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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class EShopModelProducts extends EshopRADModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config
	 */
	public function __construct($config = [])
	{
		$config['table']               = '#__eshop_products';
		$config['translatable']        = true;
		$config['translatable_fields'] = [
			'b.product_name',
			'b.product_alias',
			'b.product_desc',
			'b.product_short_desc',
			'b.product_page_title',
			'b.product_page_heading',
			'b.product_alt_image',
			'b.product_canoncial_link',
			'b.product_price_text',
			'b.meta_key',
			'b.meta_desc',
		];

		parent::__construct($config);

		$app        = Factory::getApplication();
		$request    = EShopHelper::getRequestData();
		$name       = $this->getName();
		$listLength = 0;

		if ($name == 'Category' && $request['view'] == 'category')
		{
			$category   = EShopHelper::getCategory((int) $request['id'], false);
			$listLength = (int) $category->products_per_page;
		}

		if (!$listLength)
		{
			$listLength = EShopHelper::getConfigValue('catalog_limit');
		}

		if (!$listLength)
		{
			$listLength = $app->get('list_limit');
		}

		$limit = $app->getUserStateFromRequest('com_eshop.' . $name . '.limit', 'limit', $listLength, 'int');
		$this->state->insert('id', 'int', 0)
			->insert('category_id', 'int', 0)
			->insert('product_type', 'string', '')
			->insert('limit', 'int', $limit)
			->insert('sort_options', 'string', '');

		//Search filters
		if ($this->name == 'Search')
		{
			$this->state->insert('min_price', 'float', 0)
				->insert('max_price', 'float', 0)
				->insert('min_weight', 'float', '')
				->insert('max_weight', 'float', '')
				->insert('same_weight_unit', 'int', '1')
				->insert('min_length', 'float', '')
				->insert('max_length', 'float', '')
				->insert('min_width', 'float', '')
				->insert('max_width', 'float', '')
				->insert('min_height', 'float', '')
				->insert('max_height', 'float', '')
				->insert('same_length_unit', 'int', '1')
				->insert('product_in_stock', 'int', 2)
				->insert('category_ids', 'string', '')
				->insert('manufacturer_ids', 'string', '')
				->insert('attribute_ids', 'string', '')
				->insert('optionvalue_ids', 'string', '')
				->insert('keyword', 'string', '');
		}

		if (!isset($request['sort_options']))
		{
			if ($name == 'Category' && $request['view'] == 'category')
			{
				$request['sort_options'] = EShopHelper::getDefaultSortingCategory((int) $request['id']);
			}
			else
			{
				$request['sort_options'] = EShopHelper::getConfigValue('default_sorting');
			}
		}

		$this->state->setData($request);
		$app->setUserState('limit', $this->state->limit);
	}

	/**
	 * Method to get categories data
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$rows              = parent::getData();
			$imageSizeFunction = EShopHelper::getConfigValue('list_image_size_function', 'resizeImage');

			$input = Factory::getApplication()->input;

			if ($input->getCmd('view') == 'search' && $input->getCmd('layout') == 'ajax')
			{
				$imageListWidth  = $input->get('image_width', 50);
				$imageListHeight = $input->get('image_height', 50);
			}
			else
			{
				$imageListWidth  = EShopHelper::getConfigValue('image_list_width');
				$imageListHeight = EShopHelper::getConfigValue('image_list_height');
			}

			$baseUri = Uri::base(true);

			$n = count($rows);

			$productIds = [];

			for ($i = 0; $i < $n; $i++)
			{
				$productIds[] = $rows[$i]->id;
			}

			$productsImages = EShopHelper::getProductsImages($productIds);
			$mainCategoryIds = EShopHelper::getProductsMainCategory($productIds);
			$requiredOptionsCount = EShopHelper::getNumberRequiredOptionsForProducts($productIds);
			$isProductsCartMode = EShopHelper::isProductsCartMode($productIds);

			for ($i = 0; $i < $n; $i++)
			{
				$row = $rows[$i];

				// Product main image
				if ($row->product_image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $row->product_image))
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
						[$productImage, JPATH_ROOT . '/media/com_eshop/products/', $imageListWidth, $imageListHeight]);
				}
				else
				{
					$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
						[
							EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
							JPATH_ROOT . '/media/com_eshop/products/',
							$imageListWidth,
							$imageListHeight,
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

				if (count($productImages) > 0)
				{
					$productImage = $productImages[0]->image;

					if ($productImage && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $productImage))
					{
						if (EShopHelper::getConfigValue('product_use_image_watermarks'))
						{
							$watermarkImage = EShopHelper::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/products/' . $productImage);
							$productImage   = $watermarkImage;
						}

						$additionalProductImage = call_user_func_array(['EShopHelper', $imageSizeFunction],
							[$productImage, JPATH_ROOT . '/media/com_eshop/products/', $imageListWidth, $imageListHeight]);
					}
					else
					{
						$additionalProductImage = call_user_func_array(['EShopHelper', $imageSizeFunction],
							[
								EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
								JPATH_ROOT . '/media/com_eshop/products/',
								$imageListWidth,
								$imageListHeight,
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
				
				if (!EShopHelper::isProductCartMode($row))
				{
					$row->is_product_cart_mode = false;
				}
				else 
				{
					if (isset($isProductsCartMode[$row->id]))
					{
						$row->is_product_cart_mode = true;
					}
					else 
					{
						$row->is_product_cart_mode = false;
					}
				}

				$row->labels = EShopHelper::getProductLabelsData($row);

				// Product availability
				$productInventory = EShopHelper::getProductInventory($row);

				if ($row->product_quantity <= 0)
				{
					$availability = EShopHelper::getStockStatusName($productInventory['product_stock_status_id'], Factory::getLanguage()->getTag());
				}
				elseif ($productInventory['product_stock_display'])
				{
					$availability = $row->product_quantity;
				}
				else
				{
					$availability = Text::_('ESHOP_IN_STOCK');
				}

				$row->availability       = $availability;
				$row->product_short_desc = HTMLHelper::_('content.prepare', $row->product_short_desc);
				$row->product_desc       = HTMLHelper::_('content.prepare', $row->product_desc);
			}

			$this->data = $rows;
		}

		return $this->data;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		$query->innerJoin('#__eshop_productdetails AS b ON a.id = b.product_id');

		$sortOptions = $this->state->sort_options;

		if ($sortOptions == 'product_rates-ASC' || $sortOptions == 'product_rates-DESC' || $sortOptions == 'product_reviews-ASC' || $sortOptions == 'product_reviews-DESC')
		{
			$query->leftJoin('#__eshop_reviews AS r ON (a.id = r.product_id AND r.published = 1)');
		}
		else
		{
			if ($sortOptions == 'product_best_sellers-DESC')
			{
				$query->leftJoin(
					'#__eshop_orderproducts AS op ON (a.id = op.product_id AND op.order_id IN (SELECT id FROM #__eshop_orders WHERE order_status_id = ' . EShopHelper::getConfigValue(
						'complete_status_id'
					) . '))'
				);
			}
		}

		return $this;
	}

	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		parent::_buildQueryWhere($query);

		if ($this->state->category_id > 0)
		{
			$categoryId = $this->state->category_id;

			if (EShopHelper::getConfigValue('show_products_in_all_levels'))
			{
				$categoryIds = array_merge([$categoryId], EShopHelper::getAllChildCategories($categoryId));
			}
			else
			{
				$categoryIds = [$categoryId];
			}

			$db       = $this->getDbo();
			$subQuery = $db->getQuery(true);
			$subQuery->select('pc.product_id FROM #__eshop_productcategories AS pc WHERE pc.category_id IN (' . implode(',', $categoryIds) . ')');
			$query->where('a.id IN (' . (string) $subQuery . ')');
		}


		$productType = $this->state->product_type;

		if ($productType != '')
		{
			if ($productType == 'featured')
			{
				$query->where('a.product_featured = 1');
			}
			elseif ($productType == 'latest')
			{
				$query->order('a.id DESC');
			}
			else
			{
				$query->order('RAND()');
			}
		}

		//Check viewable of customer groups
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$query->where(
			'((a.product_customergroups = "") OR (a.product_customergroups IS NULL) OR (a.product_customergroups = "' . $customerGroupId . '") OR (a.product_customergroups LIKE "' . $customerGroupId . ',%") OR (a.product_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.product_customergroups LIKE "%,' . $customerGroupId . '"))'
		);

		$nullDate    = $this->getDbo()->quote($this->getDbo()->getNullDate());
		$currentDate = $this->getDbo()->quote(EShopHelper::getServerTimeFromGMTTime());

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

		return $this;
	}

	protected function _buildQueryOrder(JDatabaseQuery $query)
	{
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
		$sortOptions      = $this->state->sort_options;

		if ($sortOptions != '')
		{
			$sortOptions = explode('-', $sortOptions);

			if (isset($sortOptions[0]) && in_array($sortOptions[0], $allowedSortArr))
			{
				$sort = $sortOptions[0];
			}
			else
			{
				$sort = 'a.ordering';
			}

			if (isset($sortOptions[1]) && in_array($sortOptions[1], $allowedDirectArr))
			{
				$direct = $sortOptions[1];
			}
			else
			{
				$direct = 'ASC';
			}

			$query->order($sort . ' ' . $direct)
				->order('a.ordering');

			return $this;
		}
		else
		{
			return parent::_buildQueryOrder($query);
		}
	}

	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select(['a.*']);

		if ($this->translatable)
		{
			$query->select($this->translatableFields);
		}

		$sortOptions = $this->state->sort_options;

		if ($sortOptions == 'product_rates-ASC' || $sortOptions == 'product_rates-DESC')
		{
			$query->select('AVG(r.rating) AS product_rates');
		}
		elseif ($sortOptions == 'product_reviews-ASC' || $sortOptions == 'product_reviews-DESC')
		{
			$query->select('COUNT(r.id) AS product_reviews');
		}
		elseif ($sortOptions == 'product_best_sellers-DESC')
		{
			$query->select('SUM(op.quantity) AS product_best_sellers');
		}

		return $this;
	}

	protected function _buildQueryGroup(JDatabaseQuery $query)
	{
		$sortOptions = $this->state->sort_options;

		if ($sortOptions == 'product_rates-ASC' || $sortOptions == 'product_rates-DESC' || $sortOptions == 'product_reviews-ASC' || $sortOptions == 'product_reviews-DESC' || $sortOptions == 'product_best_sellers-DESC')
		{
			$query->group('a.id');
		}

		return $this;
	}

	/**
	 * Get total record
	 *
	 * @return integer Number of records
	 *
	 */
	public function getTotal()
	{
		$request = EShopHelper::getRequestData();
		$name    = $this->getName();

		if ($name == 'Category' && $request['view'] == 'category')
		{
			if (empty($this->total))
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select('COUNT(*)');
				$this->_buildQueryFrom($query)
					->_buildQueryWhere($query);
				$query->innerJoin('#__eshop_productdetails AS b ON a.id = b.product_id');
				$db->setQuery($query);
				$this->total = (int) $db->loadResult();
			}

			return $this->total;
		}
		else
		{
			return parent::getTotal();
		}
	}
}