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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipActive;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipExpire;

class plgOSMembershipGroupmembership extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

	/**
	 * The application object
	 *
	 * @var \Joomla\CMS\Application\CMSApplication
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
			'onMembershipActive' => 'onMembershipActive',
			'onMembershipExpire' => 'onMembershipExpire',
			'onProfileDisplay'   => 'onProfileDisplay',
		];
	}

	/**
	 * Run when a membership activated
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipActive(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if ($row->user_id && !$row->group_admin_id)
		{
			// Change subscription end date of the group members
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('MAX(to_date)')
				->from('#__osmembership_subscribers')
				->where('user_id=' . $row->user_id . ' AND plan_id=' . $row->plan_id . ' AND published = 1');
			$db->setQuery($query);
			$maxToDate = $db->loadResult();

			if ($maxToDate)
			{
				$query->clear()
					->update('#__osmembership_subscribers')
					->set('published = 1')
					->set('to_date = ' . $db->quote($maxToDate))
					->where('group_admin_id = ' . $row->user_id)
					->where('plan_id = ' . $row->plan_id);
				$db->setQuery($query);
				$db->execute();

				// Need to trigger onMembershipActive event
				$query->clear()
					->select('id')
					->from('#__osmembership_subscribers')
					->where('plan_id = ' . $row->plan_id)
					->where('group_admin_id = ' . $row->user_id);
				$db->setQuery($query);
				$groupMemberIds = $db->loadColumn();

				if (count($groupMemberIds))
				{
					$app = Factory::getApplication();

					foreach ($groupMemberIds as $groupMemberId)
					{
						$groupMember = new OSMembershipTableSubscriber($db);
						$groupMember->load($groupMemberId);

						$event = new MembershipActive(['row' => $groupMember]);

						$app->triggerEvent($event->getName(), $event);
					}

					// Update subscription status to active, just in case they were marked as expired before for some reasons
					$query->clear()
						->update('#__osmembership_subscribers')
						->set('published = 1')
						->where('plan_id = ' . $row->plan_id)
						->where('group_admin_id = ' . $row->user_id);
					$db->setQuery($query);
					$db->execute();
				}
			}

			if ($row->act == 'upgrade')
			{
				// Process upgrade group members to new membership
				$fromPlan = OSMembershipHelperDatabase::getPlan($row->from_plan_id);
				$toPlan   = OSMembershipHelperDatabase::getPlan($row->plan_id);

				if ($fromPlan->number_group_members > 0 && $toPlan->number_group_members > 0)
				{
					// Get all group members of old plan
					$query->clear()
						->select('id')
						->from('#__osmembership_subscribers')
						->where('plan_id = ' . (int) $row->from_plan_id)
						->where('group_admin_id = ' . $row->user_id);
					$db->setQuery($query);
					$groupMemberIds = $db->loadColumn();

					if (count($groupMemberIds))
					{
						$app = Factory::getApplication();

						foreach ($groupMemberIds as $groupMemberId)
						{
							$groupMember = new OSMembershipTableSubscriber($db);
							$groupMember->load($groupMemberId);
							$groupMember->plan_id   = $row->plan_id;
							$groupMember->from_date = $row->from_date;
							$groupMember->to_date   = $row->to_date;
							$groupMember->published = 1;
							$groupMember->store();

							$event = new MembershipActive(['row' => $groupMember]);

							$app->triggerEvent($event->getName(), $event);
						}
					}
				}
			}
		}
	}

	/**
	 * Run when a membership expired die
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipExpire(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if ($row->user_id && !$row->group_admin_id)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('published = 1')
				->where('plan_id = ' . $row->plan_id)
				->where('user_id = ' . $row->user_id);
			$db->setQuery($query);
			$total = (int) $db->loadResult();

			if (!$total)
			{
				// Expired subscription, so need to trigger all group members as expired
				$query->clear()
					->select('id')
					->from('#__osmembership_subscribers')
					->where('plan_id = ' . $row->plan_id)
					->where('group_admin_id = ' . $row->user_id);
				$db->setQuery($query);
				$groupMemberIds = $db->loadColumn();

				if (count($groupMemberIds))
				{
					$app = Factory::getApplication();

					foreach ($groupMemberIds as $groupMemberId)
					{
						$groupMember = new OSMembershipTableSubscriber($db);
						$groupMember->load($groupMemberId);

						$event = new MembershipExpire(['row' => $groupMember]);

						$app->triggerEvent($event->getName(), $event);
					}

					// Need to mark the subscription as expired
					$query->clear()
						->update('#__osmembership_subscribers')
						->set('published = 2')
						->where('plan_id = ' . $row->plan_id)
						->where('group_admin_id = ' . $row->user_id);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	/**
	 * Render setting form
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onProfileDisplay(Event $event): void
	{
		// Only show this tab on user profile page frontend
		if (!$this->app->isClient('site'))
		{
			return;
		}

		if (!$this->params->get('show_group_members_on_profile'))
		{
			return;
		}

		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		ob_start();
		$this->showGroupMembers($row);
		$form = ob_get_contents();
		ob_end_clean();

		$result = [
			'title' => Text::_('OSM_GROUP_MEMBERS_LIST'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return void
	 */
	private function showGroupMembers($row)
	{
		JLoader::register('OSMembershipModelGroupmembers', JPATH_ROOT . '/components/com_osmembership/model/groupmembers.php');

		/* @var OSMembershipModelGroupmembers $model */
		$model = MPFModel::getTempInstance('Groupmembers', 'OSMembershipModel');
		$model->setGroupAdminUserId($row->user_id);

		$rowMembers = $model->getData();

		if (count($rowMembers) === 0)
		{
			return;
		}

		$fields = OSMembershipHelper::getProfileFields(0, true);

		foreach ($fields as $i => $field)
		{
			if (!$field->show_on_subscriptions)
			{
				unset($fields[$i]);
			}
		}

		$fieldsData = $model->getFieldsData();

		$Itemid = OSMembershipHelperRoute::getViewRoute('groupmembers', $this->app->input->getInt('Itemid'));

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'members');
	}
}
