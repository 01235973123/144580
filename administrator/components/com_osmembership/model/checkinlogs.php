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

class OSMembershipModelCheckinlogs extends MPFModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['table']         = '#__osmembership_checkinlogs';
		$config['search_fields'] = ['u.username', 'u.name', 'u.email'];
		$config['clear_join']    = false;

		parent::__construct($config);

		$this->state->insert('filter_plan_id', 'int', 0)
			->setDefault('filter_order_Dir', 'DESC');
	}

	/**
	 * Builds SELECT columns list for the query
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryColumns(DatabaseQuery $query)
	{
		$query->select('tbl.*')
			->select('s.user_id')
			->select('u.username, u.name, u.email')
			->select('p.title AS plan_title');

		return $this;
	}

	/**
	 * Builds JOINS clauses for the query
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryJoins(DatabaseQuery $query)
	{
		$query->leftJoin('#__osmembership_subscribers AS s ON tbl.subscriber_id = s.id')
			->leftJoin('#__osmembership_plans AS p ON s.plan_id = p.id')
			->leftJoin('#__users AS u ON s.user_id = u.id');

		return $this;
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

		if ($this->state->filter_plan_id)
		{
			$query->where('s.plan_id = ' . $this->state->filter_plan_id);
		}

		return $this;
	}
}
