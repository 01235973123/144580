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

class OSMembershipModelDocuments extends MPFModelList
{
	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(DatabaseQuery $query)
	{
		$activePlanIds = OSMembershipHelperSubscription::getActiveMembershipPlans();

		$query->where(
			'tbl.id IN (SELECT document_id FROM #__osmembership_plan_documents AS b WHERE b.plan_id  IN (' . implode(
				',',
				$activePlanIds
			) . ') )'
		);

		return $this;
	}

	/**
	 * Builds a generic ORDER BY clause based on the model's state
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryOrder(DatabaseQuery $query)
	{
		$query->order('tbl.plan_id, tbl.ordering');

		return $this;
	}
}
