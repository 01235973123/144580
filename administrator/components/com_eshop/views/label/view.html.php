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
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewLabel extends EShopViewForm
{
	/**
	 *
	 * @var $nullDate
	 */
	protected $nullDate;

	public function _buildListArray(&$lists, $item)
	{
		$document = Factory::getApplication()->getDocument();
		$document->addScript(Uri::base(true) . '/components/com_eshop/assets/colorpicker/jscolor.js');
		$db = Factory::getDbo();
		//Label style
		$options              = [];
		$options[]            = HTMLHelper::_('select.option', 'rotated', Text::_('ESHOP_LABEL_STYLE_ROTATED'));
		$options[]            = HTMLHelper::_('select.option', 'round', Text::_('ESHOP_LABEL_STYLE_ROUND'));
		$options[]            = HTMLHelper::_('select.option', 'horizontal', Text::_('ESHOP_LABEL_STYLE_HORIZONTAL'));
		$lists['label_style'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'label_style',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$item->label_style
		);
		//Label position
		$options                 = [];
		$options[]               = HTMLHelper::_('select.option', 'top_left', Text::_('ESHOP_LABEL_POSITION_TOP_LEFT'));
		$options[]               = HTMLHelper::_('select.option', 'top_right', Text::_('ESHOP_LABEL_POSITION_TOP_RIGHT'));
		$options[]               = HTMLHelper::_('select.option', 'bottom_left', Text::_('ESHOP_LABEL_POSITION_BOTTOM_LEFT'));
		$options[]               = HTMLHelper::_('select.option', 'bottom_right', Text::_('ESHOP_LABEL_POSITION_BOTTOM_RIGHT'));
		$lists['label_position'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'label_position',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$item->label_position
		);
		//Label bold
		$lists['label_bold'] = EShopHtmlHelper::getBooleanInput('label_bold', $item->label_bold);
		//Label for out of stock products
		$lists['label_out_of_stock_products'] = EShopHtmlHelper::getBooleanInput('label_out_of_stock_products', $item->label_out_of_stock_products);
		//Label opacity
		$options                = [];
		$options[]              = HTMLHelper::_('select.option', '1', '100%');
		$options[]              = HTMLHelper::_('select.option', '0.9', '90%');
		$options[]              = HTMLHelper::_('select.option', '0.8', '80%');
		$options[]              = HTMLHelper::_('select.option', '0.7', '70%');
		$options[]              = HTMLHelper::_('select.option', '0.6', '60%');
		$options[]              = HTMLHelper::_('select.option', '0.5', '50%');
		$options[]              = HTMLHelper::_('select.option', '0.4', '40%');
		$options[]              = HTMLHelper::_('select.option', '0.3', '30%');
		$options[]              = HTMLHelper::_('select.option', '0.2', '20%');
		$options[]              = HTMLHelper::_('select.option', '0.1', '10%');
		$lists['label_opacity'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'label_opacity',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$item->label_opacity
		);
		$lists['enable_image']  = EShopHtmlHelper::getBooleanInput('enable_image', $item->enable_image);
		//Get multiple products
		$query = $db->getQuery(true);
		$query->select('a.id AS value, b.product_name AS text')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.product_name');
		$db->setQuery($query);
		$products = $db->loadObjectList();
		$query->clear();
		$query->select('element_id')
			->from('#__eshop_labelelements')
			->where('label_id = ' . intval($item->id))
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
		$query->clear();
		$query->select('element_id')
			->from('#__eshop_labelelements')
			->where('label_id = ' . intval($item->id))
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
		$query->clear();
		$query->select('element_id')
			->from('#__eshop_labelelements')
			->where('label_id = ' . intval($item->id))
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