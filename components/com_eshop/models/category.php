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
require_once __DIR__ . '/products.php';

class EShopModelCategory extends EShopModelProducts
{
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		return parent::_buildQueryJoins($query);
	}

	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		parent::_buildQueryWhere($query);

		if (EShopHelper::getConfigValue('show_products_in_all_levels'))
		{
			$categoryIds = array_merge([$this->state->id], EShopHelper::getAllChildCategories($this->state->id));
		}
		else
		{
			$categoryIds = [$this->state->id];
		}

		$db       = $this->getDbo();
		$subQuery = $db->getQuery(true);
		$subQuery->select('pc.product_id FROM #__eshop_productcategories AS pc WHERE pc.category_id IN (' . implode(',', $categoryIds) . ')');
		$query->where('a.id IN (' . (string) $subQuery . ')');

		return $this;
	}
}