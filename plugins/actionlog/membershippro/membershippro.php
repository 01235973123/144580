<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgActionlogMembershippro extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterSaveSubscriptionPlan' => 'onAfterSaveSubscriptionPlan',
			'onPlanChangeState'           => 'onPlanChangeState',
			'onPlansAfterDelete'          => 'onPlansAfterDelete',
			'onSubscriptionAfterSave'     => 'onSubscriptionAfterSave',
			'onSubscriptionChangeState'   => 'onSubscriptionChangeState',
			'onSubscriptionsAfterDelete'  => 'onSubscriptionsAfterDelete',
			'onSubscriptionsExport'       => 'onSubscriptionsExport',
		];
	}

	/**
	 * Log add/edit plan action
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

		$message = [];

		if ($isNew)
		{
			$messageKey = 'MP_LOG_PLAN_ADDED';
		}
		else
		{
			$messageKey = 'MP_LOG_PLAN_UPDATED';
		}

		$message['itemlink'] = 'index.php?option=com_osmembership&view=plan&id=' . $row->id;
		$message['title']    = $row->title;

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log publish/unpublish event action
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onPlanChangeState(Event $event): void
	{
		/**
		 * @var string $context
		 * @var array  $pks
		 * @var int    $value
		 */
		[$context, $pks, $value] = array_values($event->getArguments());

		$message = [];

		if ($value)
		{
			$messageKey = 'MP_LOG_PLANS_PUBLISHED';
		}
		else
		{
			$messageKey = 'MP_LOG_PLANS_UNPUBLISHED';
		}

		$message['ids']         = implode(',', $pks);
		$message['numberplans'] = count($pks);

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log delete plans action
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onPlansAfterDelete(Event $event): void
	{
		[$context, $pks] = array_values($event->getArguments());

		$message = [];

		$messageKey             = 'MP_LOG_PLANS_DELETED';
		$message['ids']         = implode(',', $pks);
		$message['numberplans'] = count($pks);

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log add/edit registrant action
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onSubscriptionAfterSave(Event $event): void
	{
		[$context, $row, $data, $isNew] = array_values($event->getArguments());

		$message = [];

		if ($isNew)
		{
			$messageKey = 'MP_LOG_SUBSCRIPTION_ADDED';
		}
		else
		{
			$messageKey = 'MP_LOG_SUBSCRIPTION_UPDATED';
		}

		$message['id']       = $row->id;
		$message['name']     = trim($row->first_name . ' ' . $row->last_name);
		$message['itemlink'] = 'index.php?option=com_osmembership&view=subscription&id=' . $row->id;

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log delete subscriptions action
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onSubscriptionsAfterDelete(Event $event): void
	{
		/**
		 * @var string $context
		 * @var array  $pks
		 */
		[$context, $pks] = array_values($event->getArguments());

		$message = [];

		$messageKey                     = 'MP_LOG_SUBSCRIPTIONS_DELETED';
		$message['ids']                 = implode(',', $pks);
		$message['numbersubscriptions'] = count($pks);

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log publish/unpublish event action
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onSubscriptionChangeState(Event $event): void
	{
		/**
		 * @var string $context
		 * @var  array $pks
		 * @var int    $value
		 */
		[$context, $pks, $value] = array_values($event->getArguments());

		$message = [];

		if ($value)
		{
			$messageKey = 'MP_LOG_SUBSCRIPTIONS_PUBLISHED';
		}
		else
		{
			$messageKey = 'MP_LOG_SUBSCRIPTIONS_UNPUBLISHED';
		}

		$message['ids']                 = implode(',', $pks);
		$message['numbersubscriptions'] = count($pks);

		$this->addLog($message, $messageKey);
	}

	/**
	 * Log publish/unpublish event action
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onSubscriptionsExport(Event $event): void
	{
		[$planId, $numberSubscriptions] = array_values($event->getArguments());

		$message = [];

		$message['numbersubscriptions'] = $numberSubscriptions;

		if ($planId)
		{
			$messageKey    = 'MP_LOG_PLAN_SUBSCRIPTIONS_EXPORTED';
			$message['id'] = $planId;
		}
		else
		{
			$messageKey = 'MP_LOG_SUBSCRIPTIONS_EXPORTED';
		}

		$this->addLog($message, $messageKey);
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		// Make sure Membership Pro is installed and enabled
		if (!ComponentHelper::isEnabled('com_osmembership'))
		{
			return;
		}

		// No point in logging guest actions
		if (!$this->app->getIdentity()
			|| $this->app->getIdentity()->guest)
		{
			return;
		}

		parent::registerListeners();
	}

	/**
	 * Log an action
	 *
	 * @param   array   $message
	 * @param   string  $messageKey
	 */
	private function addLog($message, $messageKey)
	{
		$user = $this->app->getIdentity();

		if (!array_key_exists('userid', $message))
		{
			$message['userid'] = $user->id;
		}

		if (!array_key_exists('username', $message))
		{
			$message['username'] = $user->username;
		}

		if (!array_key_exists('accountlink', $message))
		{
			$message['accountlink'] = 'index.php?option=com_users&task=user.edit&id=' . $user->id;
		}

		try
		{
			/** @var ActionlogModel $model */
			$model = $this->app->bootComponent('com_actionlogs')
				->getMVCFactory()->createModel('Actionlog', 'Administrator', ['ignore_request' => true]);

			$model->addLog([$message], $messageKey, 'com_osmembership', $user->id);
		}
		catch (Exception $e)
		{
			// Ignore any error
		}
	}
}
