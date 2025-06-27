<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;

class OSMembershipHelperAcl
{
	/**
	 * Check to see if the current user can change the status of given plan
	 *
	 * @param   int  $id
	 *
	 * @return bool
	 */
	public static function canChangePlanState($id = 0)
	{
		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.edit.state', 'com_osmembership'))
		{
			return true;
		}

		if ($user->authorise('core.edit.state.own', 'com_osmembership'))
		{
			if (!$id)
			{
				return true;
			}

			$plan = OSMembershipHelperDatabase::getPlan($id);

			if ($plan && $plan->created_by == $user->id)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Check to see if current user can edit a plan
	 *
	 * @param   int  $id
	 *
	 * @return bool
	 */
	public static function canEditPlan($id)
	{
		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.edit', 'com_osmembership'))
		{
			return true;
		}

		if ($user->authorise('core.edit.own', 'com_osmembership'))
		{
			$plan = OSMembershipHelperDatabase::getPlan($id);

			if ($plan && $plan->created_by == $user->id)
			{
				return true;
			}
		}

		return false;
	}
}
