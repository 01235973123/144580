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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgOSMembershipDeleteAccount extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	public static function getSubscribedEvents(): array
	{
		return [
			'onSubscriptionAfterDelete' => 'onSubscriptionAfterDelete',
		];
	}

	/**
	 * Recalculate some important subscription information when a subscription record is being deleted
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onSubscriptionAfterDelete(Event $event): void
	{
		/**
		 * @var string                      $context
		 * @var OSMembershipTableSubscriber $row
		 */
		[$context, $row] = array_values($event->getArguments());

		if (!$row->user_id)
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $row->user_id)
			->where('(published >= 1 OR payment_method LIKE "os_offline%")');
		$db->setQuery($query);

		// No subscription records left
		if (!$db->loadResult())
		{
			$user              = Factory::getUser($row->user_id);
			$excludeUserGroups = $this->params->get('exclude_user_groups', [3, 4, 5, 6, 7, 8]);

			if (is_string($excludeUserGroups))
			{
				$excludeUserGroups = explode(',', $excludeUserGroups);
			}

			$excludeUserGroups = array_unique(array_merge($excludeUserGroups, [7, 8]));

			if ($user->id && count(array_intersect($user->groups, $excludeUserGroups)) === 0)
			{
				// Delete the user account
				$user->delete();

				// Delete the remaining orphans records
				$query->clear()
					->delete('#__osmembership_subscribers')
					->where('user_id = ' . $user->id);
				$db->setQuery($query)
					->execute();
			}
		}
	}
}
