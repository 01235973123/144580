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

class EShopModelFilter extends EShopModelProducts
{
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		parent::_buildQueryWhere($query);

		$filterData = EShopFilter::getFilterData();

		EShopFilter::applyFilters($query, $filterData);

		return $this;
	}
}