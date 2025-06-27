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

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelZones extends EShopModelList
{

	public function __construct($config)
	{
		$config['state_vars']    = ['filter_order' => ['b.country_name', 'cmd', 1], 'country_id' => [0, 'int', 1]];
		$config['search_fields'] = ['a.zone_name'];
		parent::__construct($config);
	}

	/**
	 * Build query to get list of records to display
	 *
	 * @see EShopModelList::_buildQuery()
	 */
	public function _buildQuery()
	{
		$db    = $this->getDbo();
		$state = $this->getState();
		$where = $this->_buildContentWhereArray();
		$query = $db->getQuery(true);
		$query->select('a.*, b.country_name')
			->from('#__eshop_zones AS a')
			->join('LEFT', '#__eshop_countries AS b ON a.country_id = b.id ');
		if (count($where))
		{
			$query->where($where);
		}
		$query->order($state->filter_order . ' ' . $state->filter_order_Dir);

		return $query;
	}

	public function _buildContentWhereArray()
	{
		$state = $this->getState();
		$where = parent::_buildContentWhereArray();
		if ($state->country_id)
		{
			$where[] = ' a.country_id = ' . intval($state->country_id);
		}

		return $where;
	}
}