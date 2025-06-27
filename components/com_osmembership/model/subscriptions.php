<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseQuery;

class OSMembershipModelSubscriptions extends MPFModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['table'] = '#__osmembership_subscribers';

		parent::__construct($config);

		$this->state->set('filter_order_Dir', 'DESC');
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
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();
		$query->select('tbl.*')
			->select($this->db->quoteName('b.title' . $fieldSuffix, 'plan_title'))
			->select(
				'b.lifetime_membership, b.enable_renewal, b.recurring_subscription, b.activate_member_card_feature'
			)
			->select('b.currency, b.currency_symbol');

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
		$query->leftJoin('#__osmembership_plans AS b  ON tbl.plan_id = b.id');

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
		$config = OSMembershipHelper::getConfig();

		$query->where('tbl.user_id = ' . Factory::getApplication()->getIdentity()->id)
			->where('tbl.plan_id IN (SELECT id FROM #__osmembership_plans WHERE published = 1)');

		if (!$config->get('show_incomplete_payment_subscriptions', 1))
		{
			$query->where('(tbl.published != 0 OR tbl.gross_amount = 0 OR tbl.payment_method LIKE "os_offline%")');
		}

		return $this;
	}
}
