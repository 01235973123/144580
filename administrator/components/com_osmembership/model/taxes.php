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

class OSMembershipModelTaxes extends MPFModelList
{
	protected $choicesStates = ['filter_country', 'filter_plan_id'];

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['search_fields'] = ['tbl.country'];

		parent::__construct($config);

		$this->state->insert('filter_country', 'string', '')
			->insert('filter_plan_id', 'int', 0)
			->insert('vies', 'int', -1)
			->setDefault('filter_order', 'tbl.country');
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
		$query->select(['tbl.*'])
			->select('b.title');

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
		$query->leftJoin('#__osmembership_plans AS b ON tbl.plan_id = b.id');

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

		$db    = $this->getDbo();
		$state = $this->getState();

		if ($state->filter_country)
		{
			$query->where('tbl.country = ' . $db->quote($state->filter_country));
		}

		if ($state->filter_plan_id > 0)
		{
			$query->where('tbl.plan_id = ' . $state->filter_plan_id);
		}

		if ($state->vies != -1)
		{
			$query->where('tbl.vies = ' . $state->vies);
		}

		return $this;
	}
}
