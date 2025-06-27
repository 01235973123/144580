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

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelOrders extends EShopModelList
{
	public function __construct($config)
	{
		$config['search_fields'] = [
			'order_number',
			'firstname',
			'lastname',
			'email',
			'payment_firstname',
			'payment_lastname',
			'shipping_firstname',
			'shipping_lastname',
			'CONCAT(firstname, " ", lastname)',
		];
		$config['state_vars']    = [
			'filter_order_Dir' => ['DESC', 'cmd', 1],
		];
		parent::__construct($config);
	}

	/**
	 * Basic build Query function.
	 * The child class must override it if it is necessary
	 *
	 * @return string
	 */
	public function _buildQuery()
	{
		$db    = $this->getDbo();
		$state = $this->getState();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($this->mainTable . ' AS a ');

		$where = $this->_buildContentWhereArray();

		if (count($where))
		{
			$query->where($where);
		}

		$orderby = $this->_buildContentOrderBy();

		if ($orderby != '')
		{
			$query->order($orderby);
		}

		return $query;
	}

	/**
	 * Build an where clause array
	 *
	 * @return array
	 */
	public function _buildContentWhereArray()
	{
		$input = Factory::getApplication()->input;
		$db    = $this->getDbo();
		$state = $this->getState();
		$where = [];

		$paymentMethod = $input->getString('payment_method');

		if ($paymentMethod != '')
		{
			$where[] = 'a.payment_method = ' . $db->quote($paymentMethod);
		}

		$productId = $input->get('product_id');

		if ($productId)
		{
			$where[] = 'a.id IN (SELECT order_id FROM #__eshop_orderproducts WHERE product_id = ' . $productId . ')';
		}

		$manufacturerId = $input->get('manufacturer_id');

		if ($manufacturerId)
		{
			$where[] = 'a.id IN (SELECT order_id FROM #__eshop_orderproducts WHERE product_id IN (SELECT id FROM #__eshop_products WHERE manufacturer_id = ' . intval(
					$manufacturerId
				) . '))';
		}

		$orderStatusId = $input->getInt('order_status_id', 0);

		if ($orderStatusId)
		{
			$where[] = 'a.order_status_id = ' . intval($orderStatusId);
		}

		if ($state->search)
		{
			$search = $db->quote('%' . $db->escape($state->search, true) . '%', false);

			if (is_array($this->searchFields))
			{
				$whereOr = [];
				foreach ($this->searchFields as $titleField)
				{
					$whereOr[] = " LOWER($titleField) LIKE " . $search;
				}
				$where[] = ' (' . implode(' OR ', $whereOr) . ') ';
			}
			else
			{
				$where[] = 'LOWER(' . $this->searchFields . ') LIKE ' . $db->quote('%' . $db->escape($state->search, true) . '%', false);
			}
		}

		$shippingMethod = $input->getString('shipping_method');

		if ($shippingMethod != '')
		{
			$where[] = 'a.id IN (SELECT order_id FROM #__eshop_ordertotals WHERE name = "shipping" AND title = ' . $db->quote($shippingMethod) . ')';
		}

		return $where;
	}

	/**
	 *
	 * Function to process patch update orders
	 *
	 * @param   array  $data
	 */
	public function batch($data)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$setBatch = '';

		//Process order status
		if ($data['order_status_ids'])
		{
			$setBatch .= 'order_status_id = ' . $data['order_status_ids'] . ', ';
		}

		$setBatch = rtrim($setBatch, ', ');

		if ($setBatch != '')
		{
			$query->update('#__eshop_orders')
				->set($setBatch)
				->where('id in (' . implode(',', $data['cid']) . ')');
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
}