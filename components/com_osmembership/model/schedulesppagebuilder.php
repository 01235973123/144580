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

class OSMembershipModelSchedulesppagebuilder extends MPFModelList
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
		$config['table'] = '#__osmembership_schedule_sppagebuilder_pages';

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

		$query->select('a.id, a.title, a.hits, a.created_on, b.plan_id, b.number_days')
			->from('#__sppagebuilder AS a')
			->innerJoin('#__osmembership_schedule_sppagebuilder_pages AS b ON a.id = b.page_id')
			->whereIn('b.plan_id', $activePlanIds)
			->order('b.plan_id')
			->order('b.number_days');

		return $query;
	}
}
