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
use Joomla\Database\DatabaseDriver;

class OSMembershipHelperGroupmembership
{
	/**
	 * Get maximum number group members for the plan
	 *
	 * @param   OSMembershipTablePlan  $rowPlan
	 * @param   int                    $userId
	 *
	 * @return int|mixed
	 */
	public static function getMaxNumberGroupMembers($rowPlan, $userId)
	{
		if ($rowPlan->number_group_members)
		{
			return $rowPlan->number_group_members;
		}

		if ($rowPlan->number_members_field)
		{
			return static::getMaxNumberMembersFromFieldForPlan($rowPlan, $userId);
		}

		return 0;
	}

	/**
	 * Get group membership subscriptions for the current user
	 *
	 * @param   int  $userId
	 *
	 * @return array
	 */
	public static function getGroupMembershipSubscriptionsForUser($userId = 0)
	{
		if (!$userId)
		{
			$userId = (int) Factory::getApplication()->getIdentity()->id;
		}

		$canAddMembersPlanIds    = [];
		$canManageMembersPlanIds = [];

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('a.*, b.number_group_members, b.number_members_field')
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__osmembership_plans AS b ON a.plan_id = b.id')
			->where('a.published IN (1, 2)')
			->where('a.user_id = ' . $userId)
			->where('(b.number_group_members > 0 OR b.number_members_field > 0)');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (count($rows))
		{
			$activePlanIds = OSMembershipHelperSubscription::getActivePlanIdsForUser($userId);

			foreach ($rows as $row)
			{
				$canManageMembersPlanIds[] = $row->plan_id;

				// This is current active subscription, we need to get max number group members for this subscription
				if (in_array($row->plan_id, $activePlanIds))
				{
					if ($row->number_group_members > 0)
					{
						$maxNumberGroupMembers = $row->number_group_members;
					}
					else
					{
						$query->clear()
							->select('field_value')
							->from('#__osmembership_field_value')
							->where('field_id = ' . $row->number_members_field)
							->where('subscriber_id = ' . $row->id);
						$db->setQuery($query);
						$maxNumberGroupMembers = (int) $db->loadResult();
					}

					if ($maxNumberGroupMembers > 0)
					{
						$query->clear()
							->select('COUNT(*)')
							->from('#__osmembership_subscribers')
							->where('group_admin_id = ' . $userId)
							->where('plan_id = ' . $row->plan_id);
						$db->setQuery($query);
						$totalGroupMembers = (int) $db->loadResult();

						if ($totalGroupMembers < $maxNumberGroupMembers)
						{
							$canAddMembersPlanIds[] = $row->plan_id;
						}
					}
				}
			}
		}

		$canManageMembersPlanIds = array_unique($canManageMembersPlanIds);
		$canAddMembersPlanIds    = array_unique($canAddMembersPlanIds);

		return [$canManageMembersPlanIds, $canAddMembersPlanIds];
	}

	/**
	 * Get current active subscriptions of the given user
	 *
	 * @param   OSMembershipTablePlan  $rowPlan
	 * @param   int                    $userId
	 *
	 * @return int
	 */
	public static function getMaxNumberMembersFromFieldForPlan($rowPlan, $userId = 0)
	{
		if (!$userId)
		{
			$userId = (int) Factory::getApplication()->getIdentity()->id;
		}

		if ($userId == 0)
		{
			return 0;
		}

		$config      = OSMembershipHelper::getConfig();
		$gracePeriod = (int) $config->get('grace_period');

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('b.*')
			->from('#__osmembership_plans AS a')
			->innerJoin('#__osmembership_subscribers AS b ON a.id = b.plan_id')
			->where('a.id = ' . $rowPlan->id)
			->where('b.user_id = ' . $userId)
			->where('b.published = 1');

		if ($gracePeriod > 0)
		{
			$gracePeriodUnit = $config->get('grace_period_unit', 'd');

			switch ($gracePeriodUnit)
			{
				case 'm':
					$query->where(
						'(a.lifetime_membership = 1 OR (from_date <= UTC_TIMESTAMP() AND DATE_ADD(b.to_date, INTERVAL ' . $gracePeriod . ' MINUTE) >= UTC_TIMESTAMP()))'
					);
					break;
				case 'h':
					$query->where(
						'(a.lifetime_membership = 1 OR (from_date <= UTC_TIMESTAMP() AND DATE_ADD(b.to_date, INTERVAL ' . $gracePeriod . ' HOUR) >= UTC_TIMESTAMP()))'
					);
					break;
				default:
					$query->where(
						'(a.lifetime_membership = 1 OR (from_date <= UTC_TIMESTAMP() AND DATE_ADD(b.to_date, INTERVAL ' . $gracePeriod . ' DAY) >= UTC_TIMESTAMP()))'
					);
					break;
			}
		}
		else
		{
			$query->where(
				'(a.lifetime_membership = 1 OR (from_date <= UTC_TIMESTAMP() AND to_date >= UTC_TIMESTAMP()))'
			);
		}

		$db->setQuery($query);

		$maxNumberMembers = 0;

		foreach ($db->loadObjectList() as $row)
		{
			$query->clear()
				->select('field_value')
				->from('#__osmembership_field_value')
				->where('field_id = ' . $rowPlan->number_members_field)
				->where('subscriber_id = ' . $row->id);
			$db->setQuery($query);
			$maxNumberMembers = max($maxNumberMembers, (int) $db->loadResult());
		}

		return $maxNumberMembers;
	}
}