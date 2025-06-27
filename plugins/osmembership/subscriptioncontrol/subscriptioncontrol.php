<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipExpire;

class plgOSMembershipSubscriptionControl extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

	/**
	 * Application object.
	 *
	 * @var    CMSApplication
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var DatabaseDriver
	 */
	protected $db;

	/**
	 * Make language files will be loaded automatically.
	 *
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	public static function getSubscribedEvents(): array
	{
		return [
			'onEditSubscriptionPlan'      => 'onEditSubscriptionPlan',
			'onAfterSaveSubscriptionPlan' => 'onAfterSaveSubscriptionPlan',
			'onMembershipActive'          => 'onMembershipActive',
			'onMembershipExpire'          => 'onMembershipExpire',
		];
	}

	/**
	 * Render settings from
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onEditSubscriptionPlan(Event $event): void
	{
		/* @var OSMembershipTablePlan $row */
		[$row] = array_values($event->getArguments());

		if (!$this->isExecutable())
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);
		$form = ob_get_contents();
		ob_end_clean();

		$result = [
			'title' => Text::_('PLG_OSMEMBERSHIP_SUBSCRIPTION_CONTROL_SETTINGS'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * Store setting into database
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterSaveSubscriptionPlan(Event $event): void
	{
		/**
		 * @var string                $context
		 * @var OSMembershipTablePlan $row
		 * @var array                 $data
		 * @var                       $isNew
		 */
		[$context, $row, $data, $isNew] = array_values($event->getArguments());

		if (!$this->isExecutable())
		{
			return;
		}

		$params = new Registry($row->params);

		$keys = ['expiring_plan_ids', 'subscription_expired_subscribe_plan_ids'];

		foreach ($keys as $key)
		{
			$params->set($key, implode(',', $data[$key] ?? []));
		}

		$row->params = $params->toString();

		$row->store();
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

		if (!$row->user_id)
		{
			return;
		}

		$plan = new OSMembershipTablePlan($this->db);
		$plan->load($row->plan_id);
		$params          = new Registry($plan->params);
		$expiringPlanIds = array_filter(ArrayHelper::toInteger(explode(',', $params->get('expiring_plan_ids', ''))));

		if (count($expiringPlanIds) === 0)
		{
			return;
		}

		// Find all active and pending subscriptions of the current user for these plans and expiring it
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('id')
			->from('#__osmembership_subscribers')
			->where('published IN (0, 1)')
			->where('user_id = ' . $row->user_id)
			->whereIn('plan_id', $expiringPlanIds);
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (count($ids) === 0)
		{
			return;
		}

		PluginHelper::importPlugin('osmembership');

		$app             = Factory::getApplication();
		$rowSubscription = new OSMembershipTableSubscriber($db);
		$now             = Factory::getDate()->toSql();

		// Extra reminders
		$extraReminderSentFields = [
			'fourth_reminder_sent',
			'fifth_reminder_sent',
			'sixth_reminder_sent',
		];

		foreach ($ids as $id)
		{
			$rowSubscription->load($id);
			$rowSubscription->to_date              = $now;
			$rowSubscription->published            = 2;
			$rowSubscription->first_reminder_sent  = 1;
			$rowSubscription->second_reminder_sent = 1;
			$rowSubscription->third_reminder_sent  = 1;

			foreach ($extraReminderSentFields as $extraField)
			{
				if (property_exists($rowSubscription, $extraField))
				{
					$rowSubscription->{$extraField} = 1;
				}
			}

			$rowSubscription->store();

			$event = new MembershipExpire(['row' => $rowSubscription]);

			//Trigger plugins
			$app->triggerEvent($event->getName(), $event);
		}
	}

	/**
	 * Run when a membership expiried die
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipExpire(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!$row->user_id)
		{
			return;
		}

		$activePlans = OSMembershipHelperSubscription::getActiveMembershipPlans($row->user_id, [$row->id]);

		// There are still other active subscriptions of this plan, no need to process subscribing
		if (in_array($row->plan_id, $activePlans))
		{
			return;
		}

		$plan = new OSMembershipTablePlan($this->db);
		$plan->load($row->plan_id);
		$params           = new Registry($plan->params);
		$subscribePlanIds = array_filter(
			ArrayHelper::toInteger(explode(',', $params->get('subscription_expired_subscribe_plan_ids', '')))
		);

		if (count($subscribePlanIds) === 0)
		{
			return;
		}

		// Get data of the current subscription
		/* @var OSMembershipModelApi $model */
		$model = MPFModel::getTempInstance('Api', 'OSMembershipModel');
		$data  = $model->getSubscriptionData($row->id);

		foreach ($subscribePlanIds as $planId)
		{
			$subscriptionData            = $data;
			$subscriptionData['plan_id'] = $planId;
			$subscriptionData['user_id'] = $row->user_id;

			unset($subscriptionData['act']);

			try
			{
				$model->store($subscriptionData);
			}
			catch (Exception $e)
			{
			}
		}
	}

	/**
	 * Method to check if the plugin is executable
	 *
	 * @return bool
	 */
	private function isExecutable()
	{
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Display form allows users to change setting for this subscription plan
	 *
	 * @param   OSMembershipTablePlan  $row
	 */
	private function drawSettingForm($row)
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$options = [];

		foreach ($db->loadObjectList() as $plan)
		{
			$options[] = HTMLHelper::_('select.option', $plan->id, $plan->title);
		}

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'form');
	}
}
