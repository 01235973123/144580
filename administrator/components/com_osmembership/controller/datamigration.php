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
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

class OSMembershipControllerDatamigration extends OSMembershipController
{
	public function process()
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		$start = $this->input->getInt('start', 0);

		$query->select('id')
			->from('#__osmembership_subscribers')
			->where('is_profile = 1')
			->where('plan_id > 0')
			->where('published >= 1 OR payment_method LIKE "os_offline%"');
		$db->setQuery($query, $start, 1000);
		$profileIds = $db->loadColumn();

		if (empty($profileIds))
		{
			// No records left, redirect to complete page
			$this->setRedirect(
				'index.php?option=com_osmembership&view=dashboard',
				Text::_('The extension and data was successfully updated')
			);
		}
		else
		{
			$query->clear()
				->select('id, profile_id, plan_id, published, from_date, to_date')
				->from('#__osmembership_subscribers')
				->where('plan_id > 0')
				->where('profile_id IN (' . implode(',', $profileIds) . ')')
				->where('(published >= 1 OR payment_method LIKE "os_offline%")')
				->order('id');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$data = [];

			foreach ($rows as $row)
			{
				$data[$row->profile_id][$row->plan_id][] = $row;
			}

			foreach ($profileIds as $profileId)
			{
				foreach ($data[$profileId] as $planId => $subscriptions)
				{
					$isActive         = false;
					$isPending        = false;
					$isExpired        = false;
					$lastActiveDate   = null;
					$lastExpiredDate  = null;
					$planFromDate     = $subscriptions[0]->from_date;
					$planMainRecordId = $subscriptions[0]->id;

					foreach ($subscriptions as $subscription)
					{
						if ($subscription->published == 1)
						{
							$isActive       = true;
							$lastActiveDate = $subscription->to_date;
						}
						elseif ($subscription->published == 0)
						{
							$isPending = true;
						}
						elseif ($subscription->published == 2)
						{
							$isExpired       = true;
							$lastExpiredDate = $subscription->to_date;
						}
					}

					if ($isActive)
					{
						$published  = 1;
						$planToDate = $lastActiveDate;
					}
					elseif ($isPending)
					{
						$published = 0;
					}
					elseif ($isExpired)
					{
						$published  = 2;
						$planToDate = $lastExpiredDate;
					}
					else
					{
						$published  = 3;
						$planToDate = $subscription->to_date;
					}

					$query->clear()
						->update('#__osmembership_subscribers')
						->set('plan_subscription_status = ' . (int) $published)
						->set('plan_subscription_from_date = ' . $db->quote($planFromDate))
						->set('plan_subscription_to_date = ' . $db->quote($planToDate))
						->set('plan_main_record = 0')
						->where('plan_id = ' . $planId)
						->where('profile_id = ' . $profileId);
					$db->setQuery($query);
					$db->execute();

					$query->clear()
						->update('#__osmembership_subscribers')
						->set('plan_main_record = 1')
						->where('id = ' . $planMainRecordId);
					$db->setQuery($query);
					$db->execute();
				}
			}
			$start += count($profileIds);
			$this->setRedirect('index.php?option=com_osmembership&view=datamigration&start=' . $start);
		}
	}
}
