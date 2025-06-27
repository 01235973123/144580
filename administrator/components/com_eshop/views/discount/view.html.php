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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewDiscount extends EShopViewForm
{
	/**
	 *
	 * @var $nullDate
	 */
	protected $nullDate;

	public function _buildListArray(&$lists, $item)
	{
		$options                = [];
		$options[]              = HTMLHelper::_('select.option', 'P', Text::_('ESHOP_PERCENTAGE'));
		$options[]              = HTMLHelper::_('select.option', 'F', Text::_('ESHOP_FIXED_AMOUNT'));
		$lists['discount_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'discount_type',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$item->discount_type
		);
		$db                     = Factory::getDbo();
		$query                  = $db->getQuery(true);
		//Build customer groups list
		$query->clear();
		$query->select('a.id AS value, b.customergroup_name AS text')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.customergroup_name');
		$db->setQuery($query);
		$options = $db->loadObjectList();
		if ($item->discount_customergroups != '')
		{
			$selectedItems = explode(',', $item->discount_customergroups);
		}
		else
		{
			$selectedItems = [];
		}
		$lists['discount_customergroups'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'discount_customergroups[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
				'list.select'        => $selectedItems,
			]
		);
		//Get multiple products
		$query->clear();
		$query->select('a.id AS value, b.product_name AS text')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.product_name');
		$db->setQuery($query);
		$products  = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_ALL_PRODUCTS'), 'value', 'text');
		$products  = array_merge($options, $products);
		$query->clear();
		$query->select('element_id')
			->from('#__eshop_discountelements')
			->where('discount_id = ' . intval($item->id))
			->where('element_type = "product"');
		$db->setQuery($query);
		$productIds = $db->loadObjectList();
		$productArr = [];
		for ($i = 0; $i < count($productIds); $i++)
		{
			$productArr[] = $productIds[$i]->element_id;
		}
		$lists['products'] = HTMLHelper::_(
			'select.genericlist',
			$products,
			'product_id[]',
			'class="input-xlarge form-select chosen" multiple="multiple"',
			'value',
			'text',
			$productArr
		);
		//Get multiple manufacturers
		$query = $db->getQuery(true);
		$query->select('a.id AS value, b.manufacturer_name AS text')
			->from('#__eshop_manufacturers AS a')
			->innerJoin('#__eshop_manufacturerdetails AS b ON (a.id = b.manufacturer_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.manufacturer_name');
		$db->setQuery($query);
		$manufacturers = $db->loadObjectList();
		$options       = [];
		$options[]     = HTMLHelper::_('select.option', 0, Text::_('ESHOP_ALL_MANUFACTURERS'), 'value', 'text');
		$manufacturers = array_merge($options, $manufacturers);
		$query->clear();
		$query->select('element_id')
			->from('#__eshop_discountelements')
			->where('discount_id = ' . intval($item->id))
			->where('element_type = "manufacturer"');
		$db->setQuery($query);
		$manufacturerIds = $db->loadObjectList();
		$manufacturerArr = [];
		for ($i = 0; $i < count($manufacturerIds); $i++)
		{
			$manufacturerArr[] = $manufacturerIds[$i]->element_id;
		}
		$lists['manufacturers'] = HTMLHelper::_(
			'select.genericlist',
			$manufacturers,
			'manufacturer_id[]',
			'class="input-xlarge form-select chosen" multiple="multiple"',
			'value',
			'text',
			$manufacturerArr
		);
		//Get multiple categories
		//Build categories list
		$query->clear();
		$query->select('a.id, b.category_name AS title, a.category_parent_id AS parent_id')
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$children = [];
		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : [];
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$list    = HTMLHelper::_('menu.treerecurse', 0, '', [], $children, 9999, 0, 0);
		$options = [];
		foreach ($list as $listItem)
		{
			$options[] = HTMLHelper::_('select.option', $listItem->id, $listItem->treename);
		}
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_ALL_CATEGORIES'), 'value', 'text');
		$query->clear();
		$query->select('element_id')
			->from('#__eshop_discountelements')
			->where('discount_id = ' . intval($item->id))
			->where('element_type = "category"');
		$db->setQuery($query);
		$categoryIds = $db->loadObjectList();
		$categoryArr = [];
		for ($i = 0; $i < count($categoryIds); $i++)
		{
			$categoryArr[] = $categoryIds[$i]->element_id;
		}
		$lists['categories'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'category_id[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
				'list.select'        => $categoryArr,
			]
		);
		$nullDate            = $db->getNullDate();
		$this->nullDate      = $nullDate;
	}
}