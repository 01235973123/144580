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

class OSMembershipModelReports extends MPFModelList
{
	protected $choicesStates = ['plan_id'];

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['table']         = '#__osmembership_subscribers';
		$config['search_fields'] = [
			'tbl.first_name',
			'tbl.last_name',
			'tbl.email',
			'tbl.subscription_id',
			'tbl.membership_id',
			'c.username',
			'c.name',
		];
		$config['clear_join']    = false;

		parent::__construct($config);

		// Dynamic searchable fields
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('name')
			->from('#__osmembership_fields')
			->where('published = 1')
			->where('is_searchable = 1');
		$db->setQuery($query);
		$searchableFields = $db->loadColumn();

		foreach ($searchableFields as $field)
		{
			$field = 'tbl.' . $field;

			if (!in_array($field, $this->searchFields))
			{
				$this->searchFields[] = $field;
			}
		}

		$this->state->insert('plan_id', 'int', 0)
			->insert('published', 'int', -1)
			->insert('filter_in', 'int', 30)
			->setDefault('filter_order', 'tbl.created_date')
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
		$query->select(['tbl.*'])
			->select('b.title AS plan_title, b.lifetime_membership')
			->select('b.currency, b.currency_symbol')
			->select('c.username AS username');

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
		$query->leftJoin('#__osmembership_plans AS b ON tbl.plan_id = b.id')
			->leftJoin('#__users AS c ON tbl.user_id = c.id');

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

		$config = OSMembershipHelper::getConfig();
		$state  = $this->getState();

		$query->where('group_admin_id = 0')
			->where('plan_main_record = 1');

		if ($state->plan_id)
		{
			$query->where('tbl.plan_id = ' . $state->plan_id);
		}

		if (in_array($state->published, [0, 1, 2, 3]))
		{
			$query->where('tbl.plan_subscription_status = ' . $state->published);
		}

		if (!$config->get('show_incomplete_payment_subscriptions', 1))
		{
			$query->where('(tbl.published != 0 OR tbl.payment_method LIKE "os_offline%")');
		}

		$db  = $this->getDbo();
		$now = $db->quote(Factory::getDate()->toSql());

		if ($state->published == 4)
		{
			$query->where('DATEDIFF(plan_subscription_to_date, ' . $now . ') >= 0');

			if ($state->filter_in > 0)
			{
				$query->where('DATEDIFF(plan_subscription_to_date, ' . $now . ') <= ' . $state->filter_in);
			}

			if ($state->plan_id <= 0)
			{
				$query->where('b.recurring_subscription = 0');
			}
		}

		if ($state->published == 5)
		{
			$query->where('DATEDIFF(plan_subscription_to_date, ' . $now . ') >= 0');

			if ($state->filter_in > 0)
			{
				$query->where('DATEDIFF(plan_subscription_to_date, ' . $now . ') <= ' . $state->filter_in);
			}

			if ($state->plan_id <= 0)
			{
				$query->where('b.recurring_subscription = 1');
			}
		}

		return $this;
	}

	/**
	 * Builds a generic ORDER BY clasue based on the model's state
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryOrder(DatabaseQuery $query)
	{
		if (in_array($this->state->published, [4, 5]))
		{
			$this->set('filter_order', 'plan_subscription_to_date')
				->set('filter_order_Dir', '');
		}

		return parent::buildQueryOrder($query);
	}
}
