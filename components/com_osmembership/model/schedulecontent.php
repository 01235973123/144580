<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\Database\DatabaseQuery;

defined('_JEXEC') or die;

class OSMembershipModelSchedulecontent extends MPFModelList
{
	/**
	 * Clear join clause for getTotal method
	 *
	 * @var bool
	 */
	protected $clearJoin = false;

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['table'] = '#__osmembership_schedulecontent';

		parent::__construct($config);

		$this->state->insert('id', 'int', 0);
	}

	/**
	 * Build the query object which is used to get list of records from database
	 *
	 * @return DatabaseQuery
	 */
	protected function buildListQuery()
	{
		$query = $this->query;

		$activePlanIds = array_keys(
			OSMembershipHelper::callOverridableHelperMethod('Subscription', 'getUserSubscriptionsInfo')
		);

		if (empty($activePlanIds))
		{
			$activePlanIds = [0];
		}

		if ($this->state->id && in_array($this->state->id, $activePlanIds))
		{
			$activePlanIds = [$this->state->id];
		}

		$query->select(
			'a.id, a.catid, a.title, a.alias, a.hits, a.created, a.publish_up, c.title AS category_title, b.plan_id, b.number_days'
		)
			->from('#__content AS a')
			->innerJoin('#__categories AS c ON a.catid = c.id')
			->innerJoin('#__osmembership_schedulecontent AS b ON a.id = b.article_id')
			->whereIn('b.plan_id', $activePlanIds)
			->where('a.state = 1')
			->order('plan_id')
			->order('b.number_days')
			->order('b.ordering')
			->order('a.title');

		return $query;
	}
}
