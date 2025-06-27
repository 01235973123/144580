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

class OSMembershipModelFields extends MPFModelList
{
	protected $choicesStates = ['plan_id'];

	/**
	 * Constructor, Instantiate the model.
	 *
	 * @param   array  $config
	 */
	public function __construct($config)
	{
		parent::__construct($config);

		$this->state->insert('show_core_field', 'int', 1)
			->insert('plan_id', 'int', 0)
			->insert('filter_fee_field', 'int', -1)
			->insert('filter_fieldtype', 'string', '');
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

		if ($state->plan_id > 0)
		{
			$negPlanId = -1 * $state->plan_id;
			$query->where(
				'(plan_id = 0 OR id IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $state->plan_id . ' OR (plan_id < 0 AND plan_id != ' . $negPlanId . ')))'
			);
		}

		if ($state->show_core_field == 2)
		{
			$query->where('tbl.is_core = 0');
		}

		if ($state->filter_fee_field != -1)
		{
			$query->where('tbl.fee_field = ' . $state->filter_fee_field);
		}

		if ($state->filter_fieldtype)
		{
			$query->where('tbl.fieldtype = ' . $this->getDbo()->quote($state->filter_fieldtype));
		}

		return $this;
	}
}
