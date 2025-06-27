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
use Joomla\Utilities\ArrayHelper;

JLoader::register(
	'OSMembershipModelSubscriptions',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/model/subscriptions.php'
);

class OSMembershipModelSubscribers extends OSMembershipModelSubscriptions
{
	/**
	 * Override this to make sure filter dropdowns on frontend subscriptions management works
	 *
	 * @var array
	 */
	protected $choicesStates = [];

	/**
	 * Constructors
	 *
	 * @param $config
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		if ($this->params->get('default_subscription_status', '') !== '')
		{
			$this->state->setDefault('published', $this->params->get('default_subscription_status', ''));
		}

		if ($this->params->get('list_limit'))
		{
			$this->state->setDefault('limit', (int) $this->params->get('list_limit'));
		}
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
		$user = Factory::getApplication()->getIdentity();

		if (!$user->authorise('core.admin', 'com_osmembership'))
		{
			$query->where(
				'tbl.plan_id IN (SELECT id FROM #__osmembership_plans WHERE subscriptions_manage_user_id IN (0, ' . $user->id . '))'
			);
		}

		$params = $this->params;

		if ($params->get('plan_ids'))
		{
			$query->whereIn('tbl.plan_id', ArrayHelper::toInteger($params->get('plan_ids')));
		}

		if ($params->get('exclude_plan_ids'))
		{
			$query->whereNotIn('tbl.plan_id', ArrayHelper::toInteger($params->get('exclude_plan_ids')));
		}

		return parent::buildQueryWhere($query);
	}
}
