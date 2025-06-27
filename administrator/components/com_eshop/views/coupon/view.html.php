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
class EShopViewCoupon extends EShopViewForm
{
	/**
	 *
	 * @var $couponHistories
	 */
	protected $couponHistories;

	/**
	 *
	 * @var $nullDate
	 */
	protected $nullDate;

	public function _buildListArray(&$lists, $item)
	{
		$db                   = Factory::getDbo();
		$query                = $db->getQuery(true);
		$nullDate             = $db->getNullDate();
		$options              = [];
		$options[]            = HTMLHelper::_('select.option', 'P', Text::_('ESHOP_PERCENTAGE'));
		$options[]            = HTMLHelper::_('select.option', 'F', Text::_('ESHOP_FIXED_AMOUNT'));
		$lists['coupon_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'coupon_type',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$item->coupon_type
		);
		//Get list of categories
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
		$list      = HTMLHelper::_('menu.treerecurse', 0, '', [], $children, 9999, 0, 0);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_SELECT_CATEGORY'));
		foreach ($list as $listItem)
		{
			$options[] = HTMLHelper::_('select.option', $listItem->id, $listItem->treename);
		}
		$query->clear();
		$query->select('category_id')
			->from('#__eshop_couponcategories')
			->where('coupon_id = ' . intval($item->id));
		$db->setQuery($query);
		$categoryIds          = $db->loadColumn();
		$lists['category_id'] = HTMLHelper::_('select.genericlist', $options, 'category_id[]', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
			'list.select'        => $categoryIds,
		]);
		//Get multiple products
		$query->clear();
		$query->select('a.id AS value, b.product_name AS text')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.product_name');
		$db->setQuery($query);
		$products = $db->loadObjectList();
		$query->clear();
		$query->select('product_id')
			->from('#__eshop_couponproducts')
			->where('coupon_id = ' . intval($item->id));
		$db->setQuery($query);
		$productIds          = $db->loadColumn();
		$lists['product_id'] = HTMLHelper::_(
			'select.genericlist',
			$products,
			'product_id[]',
			'class="input-xlarge form-select chosen" multiple="multiple"',
			'value',
			'text',
			$productIds
		);
		//Get multiple customer groups
		$query->clear();
		$query->select('a.id, b.customergroup_name AS name')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.customergroup_name');
		$db->setQuery($query);
		$customergroups = $db->loadObjectList();
		$query->clear();
		$query->select('customergroup_id')
			->from('#__eshop_couponcustomergroups')
			->where('coupon_id = ' . intval($item->id));
		$db->setQuery($query);
		$customergroupArr                  = $db->loadColumn();
		$lists['customergroup_id']         = HTMLHelper::_(
			'select.genericlist',
			$customergroups,
			'customergroup_id[]',
			'class="input-xlarge form-select chosen" multiple="multiple"',
			'id',
			'name',
			$customergroupArr
		);
		$lists['coupon_shipping']          = EShopHtmlHelper::getBooleanInput('coupon_shipping', $item->coupon_shipping);
		$lists['coupon_for_free_shipping'] = EShopHtmlHelper::getBooleanInput(
			'coupon_for_free_shipping',
			$item->coupon_for_free_shipping ?? '0'
		);
		//Get history coupon
		$query->clear();
		$query->select('*')
			->from('#__eshop_couponhistory')
			->where('coupon_id = ' . intval($item->id));
		$db->setQuery($query);
		$couponHistories       = $db->loadObjectList();
		$this->lists           = $lists;
		$this->couponHistories = $couponHistories;
		$this->nullDate        = $nullDate;
	}
}