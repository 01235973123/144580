<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

class OSMembershipModelUrls extends MPFModelList
{
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

		$query->select('*')
			->from('#__osmembership_urls')
			->whereIn('plan_id', $activePlanIds)
			->order('id');

		return $query;
	}
}
