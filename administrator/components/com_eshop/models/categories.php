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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Pagination\Pagination;

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelCategories extends EShopModelList
{

	public function __construct($config)
	{
		$config['search_fields']       = ['b.category_name', 'b.category_desc'];
		$config['translatable']        = true;
		$config['translatable_fields'] = ['category_name', 'category_alias', 'category_desc', 'meta_key', 'meta_desc'];

		parent::__construct($config);
	}

	public function getItems()
	{
		if (empty($this->_data))
		{
			$db    = $this->getDbo();
			$query = $this->_buildQuery();
			$query->select(' a.category_parent_id AS parent_id ')->select(' b.category_name AS title ');

			// We will build the data here
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$children = [];

			// first pass - collect children
			if (count($rows))
			{
				foreach ($rows as $v)
				{
					if (EShopHelper::getConfigValue('show_products_in_all_levels'))
					{
						$categoryIds = array_merge([$v->id], EShopHelper::getAllChildCategories($v->id));
					}
					else
					{
						$categoryIds = [$v->id];
					}

					$query->clear()
						->select('COUNT(pc.product_id)')
						->from('#__eshop_productcategories AS pc')
						->innerJoin('#__eshop_categories AS c ON(pc.category_id = c.id)')
						->where('category_id IN (' . implode(',', $categoryIds) . ')');
					$db->setQuery($query);
					$v->number_products = $db->loadResult();

					$pt   = $v->parent_id;
					$list = @$children[$pt] ? $children[$pt] : [];
					array_push($list, $v);
					$children[$pt] = $list;
				}
			}
			$list              = HTMLHelper::_('menu.treerecurse', 0, '', [], $children, 9999);
			$total             = count($list);
			$this->_pagination = new Pagination($total, $this->getState('limitstart'), $this->getState('limit'));
			// slice out elements based on limits
			$list        = array_slice($list, $this->_pagination->limitstart, $this->_pagination->limit);
			$this->_data = $list;
		}

		return $this->_data;
	}
}