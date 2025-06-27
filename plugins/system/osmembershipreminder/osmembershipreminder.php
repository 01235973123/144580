<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use OSSolution\MembershipPro\Admin\Event\Reminder\BeforeSendingReminderEmails;

class plgSystemOSMembershipReminder extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterRespond' => 'onAfterRespond',
		];
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_osmembership'))
		{
			return;
		}

		parent::registerListeners();
	}

	/**
	 * The sending reminder emails is triggered after the page has fully rendered.
	 *
	 * @return  void
	 *
	 */
	public function onAfterRespond(Event $event): void
	{
		if (!$this->canRun())
		{
			return;
		}

		$bccEmail                = $this->params->get('bcc_email', '');
		$numberEmailSendEachTime = (int) $this->params->get('number_subscribers', 5);

		$db    = $this->db;
		$query = $db->getQuery(true);

		if (!trim($this->params->get('trigger_reminder_code', '')))
		{
			$now = time();

			//Store last run time
			$this->params->set('last_run', time());
			$params = $this->params->toString();
			$query->update('#__extensions')
				->set('params=' . $db->quote($params))
				->where('`element`="osmembershipreminder"')
				->where('`folder`="system"');

			try
			{
				// Lock the tables to prevent multiple plugin executions causing a race condition
				$db->lockTable('#__extensions');
			}
			catch (Exception $e)
			{
				// If we can't lock the tables it's too risk continuing execution
				return;
			}

			try
			{
				// Update the plugin parameters
				$result = $db->setQuery($query)->execute();
				$this->clearCacheGroups(['com_plugins'], [0, 1]);
			}
			catch (Exception $exc)
			{
				// If we failed to execite
				$db->unlockTables();
				$result = false;
			}

			try
			{
				// Unlock the tables after writing
				$db->unlockTables();
			}
			catch (Exception $e)
			{
				// If we can't lock the tables assume we have somehow failed
				$result = false;
			}

			// Abort on failure
			if (!$result)
			{
				return;
			}
		}

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$message = OSMembershipHelper::getMessages();

		PluginHelper::importPlugin('osmembership');

		// Send first reminder
		$rows = $this->getSubscriptionsToSendReminder(1, $numberEmailSendEachTime);

		if (count($rows) > 0)
		{
			$reminderType = 'first_reminder';

			$event = new BeforeSendingReminderEmails([
				'rows'         => $rows,
				'reminderType' => $reminderType,
			]);
			$this->app->triggerEvent($event->getName(), $event);

			OSMembershipHelper::callOverridableHelperMethod('Mail', 'sendReminderEmails', [$rows, $bccEmail, 1]);
		}

		// Send second reminder
		$rows = $this->getSubscriptionsToSendReminder(2, $numberEmailSendEachTime);

		if (count($rows) > 0)
		{
			$reminderType = 'second_reminder';

			$event = new BeforeSendingReminderEmails([
				'rows'         => $rows,
				'reminderType' => $reminderType,
			]);
			$this->app->triggerEvent($event->getName(), $event);

			OSMembershipHelper::callOverridableHelperMethod('Mail', 'sendReminderEmails', [$rows, $bccEmail, 2]);
		}

		// Send third reminder
		$rows = $this->getSubscriptionsToSendReminder(3, $numberEmailSendEachTime);

		if (count($rows) > 0)
		{
			$reminderType = 'third_reminder';

			$event = new BeforeSendingReminderEmails([
				'rows'         => $rows,
				'reminderType' => $reminderType,
			]);
			$this->app->triggerEvent($event->getName(), $event);

			OSMembershipHelper::callOverridableHelperMethod('Mail', 'sendReminderEmails', [$rows, $bccEmail, 3]);
		}

		// Send fourth reminder if available
		$fields = array_keys($this->db->getTableColumns('#__osmembership_plans'));

		if (in_array('send_fourth_reminder', $fields))
		{
			$rows = $this->getSubscriptionsToSendReminder(4, $numberEmailSendEachTime);

			if (count($rows) > 0)
			{
				$reminderType = 'fourth_reminder';

				$event = new BeforeSendingReminderEmails([
					'rows'         => $rows,
					'reminderType' => $reminderType,
				]);
				$this->app->triggerEvent($event->getName(), $event);

				OSMembershipHelper::callOverridableHelperMethod('Mail', 'sendReminderEmails', [$rows, $bccEmail, 4]);
			}
		}

		// Send fifth reminder if available
		if (in_array('send_fifth_reminder', $fields))
		{
			$rows = $this->getSubscriptionsToSendReminder(5, $numberEmailSendEachTime);

			if (count($rows) > 0)
			{
				$reminderType = 'fifth_reminder';

				$event = new BeforeSendingReminderEmails([
					'rows'         => $rows,
					'reminderType' => $reminderType,
				]);
				$this->app->triggerEvent($event->getName(), $event);

				OSMembershipHelper::callOverridableHelperMethod('Mail', 'sendReminderEmails', [$rows, $bccEmail, 5]);
			}
		}

		// Send sixth reminder if available
		if (in_array('send_sixth_reminder', $fields))
		{
			$rows = $this->getSubscriptionsToSendReminder(6, $numberEmailSendEachTime);

			if (count($rows) > 0)
			{
				$reminderType = 'sixth_reminder';

				$event = new BeforeSendingReminderEmails([
					'rows'         => $rows,
					'reminderType' => $reminderType,
				]);
				$this->app->triggerEvent($event->getName(), $event);

				OSMembershipHelper::callOverridableHelperMethod('Mail', 'sendReminderEmails', [$rows, $bccEmail, 6]);
			}
		}


		if (empty($message->subscription_end_email_subject))
		{
			return;
		}

		// Subscription end
		$query->clear()
			->select('a.*, b.title AS plan_title, b.recurring_subscription, b.number_payments, c.username')
			->select(
				'IF(b.send_subscription_end > 0, DATEDIFF(to_date, NOW()), DATEDIFF(NOW(), to_date)) AS number_days'
			)
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__osmembership_plans AS b ON a.plan_id = b.id')
			->leftJoin('#__users AS c  ON a.user_id = c.id')
			->where('b.send_subscription_end != 0')
			->where('b.recurring_subscription = 1')
			->where('b.number_payments > 0')
			->where('a.published IN (1, 2)')
			->where('a.subscription_end_sent = 0')
			->where('a.group_admin_id = 0')
			->where('a.payment_made = b.number_payments')
			->where(
				'IF(b.send_subscription_end > 0, b.send_subscription_end >= DATEDIFF(to_date, NOW()) AND DATEDIFF(to_date, NOW()) >= 0, DATEDIFF(NOW(), to_date) >= ABS(b.send_subscription_end) AND DATEDIFF(NOW(), to_date) <= 120 )'
			)
			->order('a.to_date');
		$db->setQuery($query, 0, $numberEmailSendEachTime);

		try
		{
			$rows = $db->loadObjectList();

			if (!empty($rows))
			{
				OSMembershipHelper::callOverridableHelperMethod('Mail', 'sendSubscriptionEndEmails', [$rows, $bccEmail]
				);
			}
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Get subscription records to send reminder
	 *
	 * @param   int  $reminderNumber
	 * @param   int  $numberEmailSendEachTime
	 *
	 * @return array|mixed
	 */
	private function getSubscriptionsToSendReminder($reminderNumber, $numberEmailSendEachTime)
	{
		if (!in_array($reminderNumber, [1, 2, 3, 4, 5, 6]))
		{
			return [];
		}

		switch ($reminderNumber)
		{
			case 1:
				$sendReminderField = 'b.send_first_reminder';
				$reminderSentField = 'a.first_reminder_sent';
				break;
			case 2:
				$sendReminderField = 'b.send_second_reminder';
				$reminderSentField = 'a.second_reminder_sent';
				break;
			case 3:
				$sendReminderField = 'b.send_third_reminder';
				$reminderSentField = 'a.third_reminder_sent';
				break;
			case 4:
				$sendReminderField = 'b.send_fourth_reminder';
				$reminderSentField = 'a.fourth_reminder_sent';
				break;
			case 5:
				$sendReminderField = 'b.send_fifth_reminder';
				$reminderSentField = 'a.fifth_reminder_sent';
				break;
			case 6:
				$sendReminderField = 'b.send_sixth_reminder';
				$reminderSentField = 'a.sixth_reminder_sent';
				break;
		}

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('a.*, b.title AS plan_title, b.recurring_subscription, b.number_payments, c.username')
			->select("IF($sendReminderField > 0, DATEDIFF(to_date, NOW()), DATEDIFF(NOW(), to_date)) AS number_days")
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__osmembership_plans AS b  ON a.plan_id = b.id')
			->leftJoin('#__users AS c  ON a.user_id = c.id')
			->where("$sendReminderField != 0")
			->where('b.lifetime_membership != 1')
			->where('a.published IN (1, 2)')
			->where("$reminderSentField = 0")
			->where('a.group_admin_id = 0')
			->where(
				"IF($sendReminderField > 0, $sendReminderField >= DATEDIFF(to_date, NOW()) AND DATEDIFF(to_date, NOW()) >= 0, DATEDIFF(NOW(), to_date) >= ABS($sendReminderField) AND DATEDIFF(NOW(), to_date) <= (ABS($sendReminderField) + 10))"
			)
			->order('a.to_date');
		$db->setQuery($query, 0, $numberEmailSendEachTime);

		try
		{
			return $db->loadObjectList();
		}
		catch (Exception $e)
		{
			return [];
		}
	}

	/**
	 * Method to check whether this plugin should be run
	 *
	 * @return bool
	 */
	private function canRun()
	{
		// If trigger reminder code is set, we will only process sending reminder from cron job
		if (trim($this->params->get('trigger_reminder_code', ''))
			&& trim($this->params->get('trigger_reminder_code', '')) != $this->app->input->getString(
				'trigger_reminder_code'
			))
		{
			return false;
		}

		// If time ranges is set and current time is not within these specified ranges, we won't process sending reminder
		if ($this->params->get('time_ranges'))
		{
			$withinTimeRage = false;
			$date           = Factory::getDate('Now', Factory::getApplication()->get('offset'));
			$currentHour    = $date->format('G', true);
			$timeRanges     = explode(';', $this->params->get('time_ranges'));// Time ranges format 6,10;14,20

			foreach ($timeRanges as $timeRange)
			{
				if (!str_contains($timeRange, ','))
				{
					continue;
				}

				[$fromHour, $toHour] = explode(',', $timeRange);

				if ($fromHour <= $currentHour && $toHour >= $currentHour)
				{
					$withinTimeRage = true;
					break;
				}
			}

			if (!$withinTimeRage)
			{
				return false;
			}
		}

		// We only need to check last run time if cron job is not used
		if (!trim($this->params->get('trigger_reminder_code', '')))
		{
			$lastRun   = (int) $this->params->get('last_run', 0);
			$now       = time();
			$cacheTime = (int) $this->params->get('cache_time', 2) * 3600;

			if (($now - $lastRun) < $cacheTime)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	private function clearCacheGroups(array $clearGroups, array $cacheClients = [0, 1])
	{
		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $clientId)
			{
				try
				{
					$options = [
						'defaultgroup' => $group,
						'cachebase'    => ($clientId) ? JPATH_ADMINISTRATOR . '/cache' :
							$this->app->get('cache_path', JPATH_SITE . '/cache'),
					];
					$cache   = Cache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// Ignore it
				}
			}
		}
	}
}
