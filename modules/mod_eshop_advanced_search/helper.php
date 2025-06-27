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

use Joomla\CMS\Factory;

class modEShopAdvancedSearchHelper
{

	public static function categoriesTree($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;

				if ($type)
				{
					$pre    = '<sup>|_</sup>&#160;';
					$spacer = '.&#160;&#160;&#160;&#160;&#160;&#160;';
				}
				else
				{
					$pre    = '- ';
					$spacer = '&#160;&#160;';
				}

				if ($v->parent_id == 0)
				{
					$txt = $v->title;
				}
				else
				{
					$txt = $pre . $v->title;
				}

				$list[$id]              = $v;
				$list[$id]->treeElement = $indent . $txt;
				$list[$id]->children    = !empty(@$children[$id]) ? count(@$children[$id]) : 0;
				$list                   = static::categoriesTree($id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);
			}
		}

		return $list;
	}

	/**
	 *
	 * Function to get Categories
	 * @return categories list
	 */
	public static function getCategories($maxLevel = 9999)
	{
		$langCode = Factory::getLanguage()->getTag();
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$query->clear();
		$query->select(' a.*, a.category_parent_id AS parent_id, b.category_name AS title ')
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
			->where('a.published = 1')
			->where('b.language = "' . $langCode . '"');

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

		//Check viewable of customer groups
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$query->where(
			'((a.category_customergroups = "") OR (a.category_customergroups IS NULL) OR (a.category_customergroups = "' . $customerGroupId . '") OR (a.category_customergroups LIKE "' . $customerGroupId . ',%") OR (a.category_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.category_customergroups LIKE "%,' . $customerGroupId . '"))'
		);
		// We will build the data here
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$children = [];
		// first pass - collect children
		if (count($rows))
		{
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : [];
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$categories = self::categoriesTree(0, '', [], $children, $maxLevel, 0, 0);
		$categories = array_slice($categories, 0);

		return $categories;
	}

	/**
	 *
	 * Function to get manufacturers
	 * @return object list
	 */
	public static function getManufacturers()
	{
		$langCode = Factory::getLanguage()->getTag();
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$query->select('a.id, b.manufacturer_id, b.manufacturer_name')
			->from('#__eshop_manufacturers AS a')
			->innerJoin('#__eshop_manufacturerdetails AS b ON (a.id = b.manufacturer_id)')
			->where('a.published = 1')
			->where('language = ' . $db->quote($langCode));

		//Check viewable of customer groups
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$query->where(
			'((a.manufacturer_customergroups = "") OR (a.manufacturer_customergroups IS NULL) OR (a.manufacturer_customergroups = "' . $customerGroupId . '") OR (a.manufacturer_customergroups LIKE "' . $customerGroupId . ',%") OR (a.manufacturer_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.manufacturer_customergroups LIKE "%,' . $customerGroupId . '"))'
		);

		$manufacturerDefaultSorting = EShopHelper::getConfigValue('manufacturer_default_sorting', 'name-asc');

		switch ($manufacturerDefaultSorting)
		{
			case 'name-desc':
				$query->order('b.manufacturer_name DESC');
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
				$query->order('b.manufacturer_name ASC');
				break;
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Function get Attribute group
	 */
	public static function getAttributeGroups()
	{
		$attributeGroups = EShopHelper::getAttributeGroups(Factory::getLanguage()->getTag());
		for ($i = 0; $n = count($attributeGroups), $i < $n; $i++)
		{
			$attributeGroups[$i]->attribute = self::getAttributes($attributeGroups[$i]->id);
		}

		return $attributeGroups;
	}

	/**
	 *
	 * Function to get attributes for a specific products
	 *
	 * @param   int  $productId
	 * @param   int  $attributeGroupId
	 *
	 * @return attribute object list
	 */
	public static function getAttributes($attributeGroupId)
	{
		$langCode = Factory::getLanguage()->getTag();
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$query->select('a.id, ad.attribute_name')
			->from('#__eshop_attributes AS a')
			->innerJoin('#__eshop_attributedetails AS ad ON (a.id = ad.attribute_id)')
			->where('a.attributegroup_id = ' . intval($attributeGroupId))
			->where('a.published = 1')
			->where('ad.language = "' . $langCode . '"');

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
	 * Function to get Options
	 * @return Options list
	 */
	public static function getOptions()
	{
		$langCode = Factory::getLanguage()->getTag();
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$query->select('o.id, od.option_name')
			->from('#__eshop_options AS o')
			->innerJoin('#__eshop_optiondetails AS od ON o.id = od.option_id')
			->where('o.published = 1')
			->where('od.language = "' . $langCode . '"');

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
		$options = $db->loadObjectList();
		for ($i = 0; $n = count($options), $i < $n; $i++)
		{
			$options[$i]->optionValues = EShopHelper::getOptionValues($options[$i]->id, $langCode, false);
		}

		return $options;
	}
}