<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

namespace OSSolution\MembershipPro\Module\MembershipStatus\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;

defined('_JEXEC') or die;

/**
 * Dispatcher class for mod_membershipstatus
 *
 * @since  4.2.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
	protected function getLayoutData()
	{
		if (!file_exists(JPATH_ROOT . '/components/com_osmembership/osmembership.php'))
		{
			return false;
		}

		$userId = $this->app->getIdentity()->id;

		if ($userId == 0)
		{
			return false;
		}

		$data = parent::getLayoutData();

		$data['rowSubscriptions'] = $this->getSubscriptions($userId);

		return $data;
	}

	/**
	 * Override load loadLanguage to load component language
	 *
	 * @return void
	 */
	protected function loadLanguage()
	{
		\OSMembershipHelper::loadLanguage();
	}

	/**
	 * Get subscriptions for the given user
	 *
	 * @param   int  $userId
	 *
	 * @return array
	 */
	private function getSubscriptions(int $userId): array
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$rowProfile = \OSMembershipHelperSubscription::getMembershipProfile($userId);

		if ($rowProfile)
		{
			$rowSubscriptions = \OSMembershipHelperSubscription::getSubscriptionsForMembershipStatusModule(
				$rowProfile->id
			);

			for ($i = 0, $n = count($rowSubscriptions); $i < $n; $i++)
			{
				$rowSubscription = $rowSubscriptions[$i];

				if ($rowSubscription->subscription_status != 1)
				{
					unset($rowSubscriptions[$i]);
				}
			}

			return $rowSubscriptions;
		}

		return [];
	}
}