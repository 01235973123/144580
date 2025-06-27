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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

require_once __DIR__ . '/products.php';

class EShopModelSearch extends EShopModelProducts
{

	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		parent::_buildQueryWhere($query);
		$state    = $this->getState();
		$db       = $this->getDbo();
		$subQuery = $db->getQuery(true);
		// Filter by categories
		if ($state->category_ids)
		{
			$categoryIdsArr = explode(',', $state->category_ids);
			ArrayHelper::toInteger($categoryIdsArr);
			if (EShopHelper::getConfigValue('show_products_in_all_levels'))
			{
				$tempCategoryIdsArr = [];
				foreach ($categoryIdsArr as $categoryId)
				{
					$tempCategoryIdsArr = array_merge($tempCategoryIdsArr, [$categoryId], EShopHelper::getAllChildCategories($categoryId));
				}
				$categoryIdsArr = $tempCategoryIdsArr;
			}
			$subQuery->clear();
			$subQuery->select('pc.product_id FROM #__eshop_productcategories AS pc WHERE pc.category_id IN (' . implode(',', $categoryIdsArr) . ')');
			$query->where('a.id IN (' . (string) $subQuery . ')');
		}
		// Filter by manufacturers
		if ($state->manufacturer_ids)
		{
			$manufacturerIdsArr = explode(',', $state->manufacturer_ids);
			ArrayHelper::toInteger($manufacturerIdsArr);
			$query->where('a.manufacturer_id IN (' . implode(',', $manufacturerIdsArr) . ')');
		}
		// Filter by price
		if ($state->min_price)
		{
			$query->where('a.product_price >= ' . $state->min_price);
		}
		if ($state->max_price)
		{
			$query->where('a.product_price <= ' . $state->max_price);
		}
		// Filter by weight
		$minWeight = $state->min_weight;
		$maxWeight = $state->max_weight;
		if ($state->same_weight_unit)
		{
			if ($minWeight)
			{
				$query->where('a.product_weight >= ' . $minWeight);
			}
			if ($maxWeight)
			{
				$query->where('a.product_weight <= ' . $maxWeight);
			}
		}
		else
		{
			$eshopWeight     = EShopWeight::getInstance();
			$weightIds       = EShopHelper::getWeightIds();
			$defaultWeightId = EShopHelper::getConfigValue('weight_id');
			if ($minWeight || $maxWeight)
			{
				$minWeightQuery = [];
				$maxWeightQuery = [];
				foreach ($weightIds as $weightId)
				{
					if ($minWeight)
					{
						$minWeightQuery[] = '(a.product_weight_id = ' . $weightId . ' AND a.product_weight >= ' . $eshopWeight->convert(
								$minWeight,
								$defaultWeightId,
								$weightId
							) . ')';
					}
					if ($maxWeight)
					{
						$maxWeightQuery[] = '(a.product_weight_id = ' . $weightId . ' AND a.product_weight <= ' . $eshopWeight->convert(
								$maxWeight,
								$defaultWeightId,
								$weightId
							) . ')';
					}
				}
				if (count($minWeightQuery))
				{
					$query->where('(' . implode(' OR ', $minWeightQuery) . ')');
				}
				if (count($maxWeightQuery))
				{
					$query->where('(' . implode(' OR ', $maxWeightQuery) . ')');
				}
			}
		}
		// Filter by length
		$minLength = $state->min_length;
		$maxLength = $state->max_length;
		if ($state->same_length_unit)
		{
			if ($minLength)
			{
				$query->where('a.product_length >= ' . $minLength);
			}
			if ($maxLength)
			{
				$query->where('a.product_length <= ' . $maxLength);
			}
		}
		else
		{
			$eshopLength     = EShopLength::getInstance();
			$lengthIds       = EShopHelper::getLengthIds();
			$defaultLengthId = EShopHelper::getConfigValue('length_id');
			if ($minLength || $maxLength)
			{
				$minLengthQuery = [];
				$maxLengthQuery = [];
				foreach ($lengthIds as $lengthId)
				{
					if ($minLength)
					{
						$minLengthQuery[] = '(a.product_length_id = ' . $lengthId . ' AND a.product_length >= ' . $eshopLength->convert(
								$minLength,
								$defaultLengthId,
								$lengthId
							) . ')';
					}
					if ($maxLength)
					{
						$maxLengthQuery[] = '(a.product_length_id = ' . $lengthId . ' AND a.product_length <= ' . $eshopLength->convert(
								$maxLength,
								$defaultLengthId,
								$lengthId
							) . ')';
					}
				}
				if (count($minLengthQuery))
				{
					$query->where('(' . implode(' OR ', $minLengthQuery) . ')');
				}
				if (count($maxLengthQuery))
				{
					$query->where('(' . implode(' OR ', $maxLengthQuery) . ')');
				}
			}
		}
		// Filter by width
		$minWidth = $state->min_width;
		$maxWidth = $state->max_width;
		if ($state->same_length_unit)
		{
			if ($minWidth)
			{
				$query->where('a.product_width >= ' . $minWidth);
			}
			if ($maxWidth)
			{
				$query->where('a.product_width <= ' . $maxWidth);
			}
		}
		else
		{
			$eshopLength     = EShopLength::getInstance();
			$lengthIds       = EShopHelper::getLengthIds();
			$defaultLengthId = EShopHelper::getConfigValue('length_id');
			if ($minWidth || $maxWidth)
			{
				$minWidthQuery = [];
				$maxWidthQuery = [];
				foreach ($lengthIds as $lengthId)
				{
					if ($minWidth)
					{
						$minWidthQuery[] = '(a.product_length_id = ' . $lengthId . ' AND a.product_width >= ' . $eshopLength->convert(
								$minWidth,
								$defaultLengthId,
								$lengthId
							) . ')';
					}
					if ($maxWidth)
					{
						$maxWidthQuery[] = '(a.product_length_id = ' . $lengthId . ' AND a.product_width <= ' . $eshopLength->convert(
								$maxWidth,
								$defaultLengthId,
								$lengthId
							) . ')';
					}
				}
				if (count($minWidthQuery))
				{
					$query->where('(' . implode(' OR ', $minWidthQuery) . ')');
				}
				if (count($maxWidthQuery))
				{
					$query->where('(' . implode(' OR ', $maxWidthQuery) . ')');
				}
			}
		}
		// Filter by height
		$minHeight = $state->min_height;
		$maxHeight = $state->max_height;
		if ($state->same_length_unit)
		{
			if ($minHeight)
			{
				$query->where('a.product_height >= ' . $minHeight);
			}
			if ($maxHeight)
			{
				$query->where('a.product_height <= ' . $maxHeight);
			}
		}
		else
		{
			$eshopLength     = EShopLength::getInstance();
			$lengthIds       = EShopHelper::getLengthIds();
			$defaultLengthId = EShopHelper::getConfigValue('length_id');
			if ($minHeight || $maxHeight)
			{
				$minHeightQuery = [];
				$maxHeightQuery = [];
				foreach ($lengthIds as $lengthId)
				{
					if ($minHeight)
					{
						$minHeightQuery[] = '(a.product_length_id = ' . $lengthId . ' AND a.product_height >= ' . $eshopLength->convert(
								$minHeight,
								$defaultLengthId,
								$lengthId
							) . ')';
					}
					if ($maxHeight)
					{
						$maxHeightQuery[] = '(a.product_length_id = ' . $lengthId . ' AND a.product_height <= ' . $eshopLength->convert(
								$maxHeight,
								$defaultLengthId,
								$lengthId
							) . ')';
					}
				}
				if (count($minHeightQuery))
				{
					$query->where('(' . implode(' OR ', $minHeightQuery) . ')');
				}
				if (count($maxHeightQuery))
				{
					$query->where('(' . implode(' OR ', $maxHeightQuery) . ')');
				}
			}
		}
		// Filter by stock
		if ($state->product_in_stock == 1)
		{
			$query->where('a.product_quantity > 0');
		}
		elseif ($state->product_in_stock == -1)
		{
			$query->where('a.product_quantity <= 0');
		}
		//Filter by attributes
		if ($state->attribute_ids)
		{
			$attributeIdsArr = explode(',', $state->attribute_ids);
			ArrayHelper::toInteger($attributeIdsArr);
			$subQuery->clear();
			$subQuery->select(
				'pa.product_id FROM #__eshop_productattributes AS pa WHERE pa.attribute_id IN (' . implode(',', $attributeIdsArr) . ')'
			);
			$query->where('a.id IN (' . (string) $subQuery . ')');
		}
		//Filter by options
		if ($state->optionvalue_ids)
		{
			$optionvalueIdsArr = explode(',', $state->optionvalue_ids);
			ArrayHelper::toInteger($optionvalueIdsArr);
			$subQuery->clear();
			$subQuery->select(
				'po.product_id FROM #__eshop_productoptionvalues AS po WHERE po.option_value_id IN (' . implode(',', $optionvalueIdsArr) . ')'
			);
			$query->where('a.id IN (' . (string) $subQuery . ')');
		}
		// Filter by keyword
		if ($state->keyword)
		{
			$keywordArr = explode(' ', $state->keyword);

			foreach ($keywordArr as $keyword)
			{
				$keyword            = $db->quote('%' . trim($keyword) . '%');
				$searchKeywordArr   = [];
				$searchKeywordArr[] = 'b.product_name LIKE ' . $keyword;

				if (EShopHelper::getConfigValue('search_sku', 1))
				{
					$searchKeywordArr[] = 'a.product_sku LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_short_desc', 1))
				{
					$searchKeywordArr[] = 'b.product_short_desc LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_desc', 1))
				{
					$searchKeywordArr[] = 'b.product_desc LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tab1_title', 1))
				{
					$searchKeywordArr[] = 'b.tab1_title LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tab1_content', 1))
				{
					$searchKeywordArr[] = 'b.tab1_content LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tab2_title', 1))
				{
					$searchKeywordArr[] = 'b.tab2_title LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tab2_content', 1))
				{
					$searchKeywordArr[] = 'b.tab2_content LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tab3_title', 1))
				{
					$searchKeywordArr[] = 'b.tab3_title LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tab3_content', 1))
				{
					$searchKeywordArr[] = 'b.tab3_content LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tab4_title', 1))
				{
					$searchKeywordArr[] = 'b.tab4_title LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tab4_content', 1))
				{
					$searchKeywordArr[] = 'b.tab4_content LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tab5_title', 1))
				{
					$searchKeywordArr[] = 'b.tab5_title LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tab5_content', 1))
				{
					$searchKeywordArr[] = 'b.tab5_content LIKE ' . $keyword;
				}

				if (EShopHelper::getConfigValue('search_tag', 1))
				{
					$searchKeywordArr[] = 'a.id IN (SELECT product_id FROM #__eshop_producttags WHERE tag_id IN (SELECT id FROM #__eshop_tags WHERE tag_name LIKE ' . $keyword . '))';
				}

				if (EShopHelper::getConfigValue('search_option_value', 1))
				{
					$subQuery->clear();
					$subQuery->select('po.product_id FROM #__eshop_productoptionvalues AS po WHERE po.sku LIKE ' . $keyword);
					$searchKeywordArr[] = 'a.id IN (' . (string) $subQuery . ')';
				}

				$query->where('(' . implode(' OR ', $searchKeywordArr) . ')');
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

		return $this;
	}
}