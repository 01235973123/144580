<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Database\DatabaseQuery;

class OSMembershipModelCoupons extends MPFModelList
{
	protected $choicesStates = ['filter_plan_id'];

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['search_fields'] = ['tbl.code', 'tbl.note'];

		parent::__construct($config);

		$this->state->insert('filter_plan_id', 'int', 0)
			->setDefault('filter_order', 'tbl.code');
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(DatabaseQuery $query)
	{
		parent::buildQueryWhere($query);
		$state = $this->getState();

		if ($state->filter_plan_id)
		{
			$query->where(
				'(tbl.plan_id = 0 OR id IN (SELECT coupon_id FROM #__osmembership_coupon_plans WHERE plan_id = ' . $this->state->filter_plan_id . '))'
			);
		}

		return $this;
	}

	/**
	 * Get list of categories belong to each event before it is displayed
	 *
	 * @param   array  $rows
	 */
	protected function beforeReturnData($rows)
	{
		if (count($rows))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('title')
				->from('#__osmembership_plans AS a')
				->innerJoin('#__osmembership_coupon_plans AS b ON a.id = b.plan_id');

			foreach ($rows as $row)
			{
				$query->where('b.coupon_id = ' . $row->id);
				$db->setQuery($query);
				$row->plan = implode(', ', $db->loadColumn());

				$query->clear('where');
			}
		}
	}
}
