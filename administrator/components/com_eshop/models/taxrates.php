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
class EShopModelTaxrates extends EShopModelList
{

	public function __construct($config)
	{
		$config['main_table']    = '#__eshop_taxes';
		$config['state_vars']    = ['filter_order' => ['a.tax_name', 'cmd', 1], 'geozone_id' => [0, 'int', 1]];
		$config['search_fields'] = ['a.tax_name'];
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
		$query->select('a.*, b.geozone_name')
			->from('#__eshop_taxes AS a')
			->join('LEFT', '#__eshop_geozones AS b ON a.geozone_id = b.id ');
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
		if ($state->geozone_id)
		{
			$where[] = ' a.geozone_id = ' . intval($state->geozone_id);
		}

		return $where;
	}
}