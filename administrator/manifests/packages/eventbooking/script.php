<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class Pkg_EventbookingInstallerScript
{
	/**
	 * Minimum PHP version
	 */
	private const MIN_PHP_VERSION = '7.2.0';

	/**
	 * Minimum Joomla version
	 */
	private const MIN_JOOMLA_VERSION = '4.2.0';

	/**
	 * Minimum Events Booking version to allow update
	 */
	private const MIN_EVENTS_BOOKING_VERSION = '3.7.0';

	/**
	 * The original version, use for update process
	 *
	 * @var string
	 */
	protected $installedVersion = '3.7.0';

	/**
	 * Perform basic system requirements check before installing the package
	 *
	 * @param   string    $type
	 * @param   JAdapter  $parent
	 *
	 * @return bool
	 */
	public function preflight($type, $parent)
	{
		if (version_compare(JVERSION, self::MIN_JOOMLA_VERSION, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				'Cannot install Events Booking in a Joomla! release prior to ' . self::MIN_JOOMLA_VERSION,
				'error'
			);

			return false;
		}

		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				'Events Booking requires PHP ' . self::MIN_PHP_VERSION . '+ to work. Please contact your hosting provider, ask them to update PHP version for your hosting account.',
				'error'
			);

			return false;
		}

		if ($type === 'update')
		{
			$this->deleteOldUpdateSite();
		}

		$this->getInstalledVersion();

		if (version_compare($this->installedVersion, self::MIN_EVENTS_BOOKING_VERSION, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				'Update from older version than ' . self::MIN_EVENTS_BOOKING_VERSION . ' is not supported! You need to update to version 3.17.6 first. Please contact support to get that old Events Booking version.',
				'error'
			);

			return false;
		}

		if (version_compare($this->installedVersion, '4.0.0', '<'))
		{
			$this->uninstallPlugin('eventbooking', 'spout');
		}
	}

	/**
	 * Finalize package installation
	 *
	 * @param   string    $type
	 * @param   JAdapter  $parent
	 *
	 * @return bool
	 */
	public function postflight($type, $parent)
	{
		// Migrate existing tasks to new schedule plugin
		if ($type === 'update' && version_compare($this->installedVersion, '5.1.0', '<'))
		{
			$this->renameEventsBookingSchedulerTaskPlugin();
			$this->migrateSystemEBCleanEmailsLogPlugin();
			$this->migrateSystemSendDepositPaymentReminderPlugin();
			$this->migrateSystemOfflinePaymentHandlePlugin();
			$this->migrateSystemEmailRegistrantsPlugin();
		}

		// Do not perform redirection anymore if installed version is greater than or equal 3.8.3
		if (strtolower($type) == 'install' || version_compare($this->installedVersion, '3.8.3', '>='))
		{
			return true;
		}

		$app = Factory::getApplication();
		$app->setUserState(
			'com_installer.redirect_url',
			'index.php?option=com_eventbooking&task=update.update&install_type=' . strtolower($type)
		);
		$app->getInput()->set(
			'return',
			base64_encode('index.php?option=com_eventbooking&task=update.update&install_type=' . strtolower($type))
		);
	}

	/**
	 * Get installed version of the component
	 *
	 * @return void
	 */
	private function getInstalledVersion()
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('manifest_cache')
			->from('#__extensions')
			->where($db->quoteName('element') . ' = ' . $db->quote('com_eventbooking'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'));
		$db->setQuery($query);
		$manifestCache = $db->loadResult();

		if ($manifestCache)
		{
			$manifest = json_decode($manifestCache);

			$this->installedVersion = $manifest->version;
		}
	}

	/**
	 * Delete old update site
	 *
	 * @return void
	 */
	private function deleteOldUpdateSite(): void
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->delete('#__update_sites')
			->where(
				$db->quoteName('location') . ' = ' . $db->quote('https://joomdonation.com/updates/eventsbooking.xml')
			);
		$db->setQuery($query)
			->execute();
	}

	/**
	 * Delete old update site
	 *
	 * @return void
	 */
	private function renameEventsBookingSchedulerTaskPlugin(): void
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$field = $db->quoteName('type');
		$query = $db->getQuery(true)
			->update('#__scheduler_tasks')
			->set(
				"$field = REPLACE($field, " . $db->quote('ebhoousekeeping.') . ', ' . $db->quote('eventbooking.') . ')'
			)
			->where("$field LIKE " . $db->quote('ebhoousekeeping.%'));
		$db->setQuery($query)
			->execute();

		$query->clear()
			->select('COUNT(*)')
			->from('#__scheduler_tasks')
			->where($db->quoteName('type') . ' LIKE ' . $db->quote('eventbooking.%'));
		$db->setQuery($query);

		if ($db->loadResult() > 0)
		{
			// Enable Task - Events Booking plugin
			$this->ensureTaskEventBookingPluginIsEnabled();
		}

		$this->uninstallPlugin('task', 'ebhousekeeping');
	}

	/**
	 * Migrate System - Events Booking Clean Email Log plugin to a task scheduler
	 *
	 * @return void
	 */
	private function migrateSystemEBCleanEmailsLogPlugin(): void
	{
		$plugin = PluginHelper::getPlugin('system', 'ebcleanemailslog');

		if ($plugin)
		{
			$this->ensureTaskEventBookingPluginIsEnabled();

			if (!$this->isTaskExists('eventbooking.cleanEmailsLog'))
			{
				$params    = new Registry($plugin->params);
				$cacheTime = (int) $params->get('cache_time', 24);
				$lastRun   = (int) $params->get('last_run', time());

				$task = [
					'title'           => 'Events Booking - Clean Emails Log',
					'type'            => 'eventbooking.cleanEmailsLog',
					'execution_rules' => [
						'rule-type'      => 'interval-hours',
						'interval-hours' => $cacheTime,
						'exec-time'      => gmdate('H:i', $lastRun),
						'exec-day'       => gmdate('d'),
					],
					'state'           => 1,
					'params'          => [
						'delay' => $params->get('number_days', 30),
					],
				];

				$this->createSchedulerTask($task);
			}
		}

		// Uninstall the plugin
		$this->uninstallPlugin('system', 'ebcleanemailslog');
	}

	/**
	 * Migrate System - Events Booking Clean Email Log plugin to a task scheduler
	 *
	 * @return void
	 */
	private function migrateSystemSendDepositPaymentReminderPlugin(): void
	{
		$plugin = PluginHelper::getPlugin('system', 'ebdepositreminder');

		if ($plugin)
		{
			$this->ensureTaskEventBookingPluginIsEnabled();

			if (!$this->isTaskExists('eventbooking.sendDepositPaymentReminderEmails'))
			{
				$params    = new Registry($plugin->params);
				$cacheTime = (int) $params->get('cache_time', 20);
				$lastRun   = (int) $params->get('last_run', time());

				$task = [
					'title'           => 'Events Booking - Send Deposit Payment Reminder',
					'type'            => 'eventbooking.sendDepositPaymentReminderEmails',
					'execution_rules' => [
						'rule-type'        => 'interval-minutes',
						'interval-minutes' => $cacheTime,
						'exec-time'        => gmdate('H:i', $lastRun),
						'exec-day'         => gmdate('d'),
					],
					'state'           => 1,
					'params'          => [
						'number_days'        => $params->get('number_days', 7),
						'number_registrants' => $params->get('number_registrants', 15),
						'bcc_email'          => $params->get('bcc_email', ''),
					],
				];

				$this->createSchedulerTask($task);
			}
		}

		// Uninstall the plugin
		$this->uninstallPlugin('system', 'ebdepositreminder');
	}

	/**
	 * Migrate System - Events Booking Clean Email Log plugin to a task scheduler
	 *
	 * @return void
	 */
	private function migrateSystemOfflinePaymentHandlePlugin(): void
	{
		$plugin = PluginHelper::getPlugin('system', 'ebofflinepaymenthandle');

		if ($plugin)
		{
			$this->ensureTaskEventBookingPluginIsEnabled();

			if (!$this->isTaskExists('eventbooking.offlinePaymentHandle'))
			{
				$params    = new Registry($plugin->params);
				$cacheTime = (int) $params->get('cache_time', 20);
				$lastRun   = (int) $params->get('last_run', time());

				$task = [
					'title'           => 'Events Booking - Offline Payment Handle',
					'type'            => 'eventbooking.offlinePaymentHandle',
					'execution_rules' => [
						'rule-type'        => 'interval-minutes',
						'interval-minutes' => $cacheTime,
						'exec-time'        => gmdate('H:i', $lastRun),
						'exec-day'         => gmdate('d'),
					],
					'state'           => 1,
					'params'          => [
						'number_days_to_send_reminders' => $params->get('number_days_to_send_reminders', 7),
						'number_days_to_cancel'         => $params->get('number_days_to_cancel', 0),
						'number_registrants'            => $params->get('number_registrants', 15),
						'event_ids'                     => $params->get('event_ids', []),
						'base_on'                       => $params->get('base_on', 0),
					],
				];

				$this->createSchedulerTask($task);
			}
		}

		// Uninstall the plugin
		$this->uninstallPlugin('system', 'ebofflinepaymenthandle');
	}

	/**
	 * Migrate System - Email Registrants to use task scheduler
	 *
	 * @return void
	 * @throws Exception
	 */
	private function migrateSystemEmailRegistrantsPlugin(): void
	{
		$plugin = PluginHelper::getPlugin('system', 'ebregistrants');

		if ($plugin)
		{
			$this->ensureTaskEventBookingPluginIsEnabled();

			if (!$this->isTaskExists('eventbooking.emailRegistrantsList'))
			{
				$params    = new Registry($plugin->params);
				$cacheTime = (int) $params->get('cache_time', 20);
				$lastRun   = (int) $params->get('last_run', time());

				$task = [
					'title'           => 'Events Booking - Email Registrants List',
					'type'            => 'eventbooking.emailRegistrantsList',
					'execution_rules' => [
						'rule-type'        => 'interval-minutes',
						'interval-minutes' => $cacheTime,
						'exec-time'        => gmdate('H:i', $lastRun),
						'exec-day'         => gmdate('d'),
					],
					'state'           => 1,
					'params'          => [
						'time_to_send'      => $params->get('time_to_send', 1),
						'time_to_send_unit' => $params->get('time_to_send_unit', 'd'),
					],
				];

				$this->createSchedulerTask($task);
			}
		}

		// Uninstall the plugin
		$this->uninstallPlugin('system', 'ebregistrants');
	}

	/**
	 * Migrate System - Email Registrants to use task scheduler
	 *
	 * @return void
	 * @throws Exception
	 */
	private function migrateSystemInCompletePaymentNotificationPlugin(): void
	{
		$plugin = PluginHelper::getPlugin('system', 'icprnotify');

		if ($plugin)
		{
			$this->ensureTaskEventBookingPluginIsEnabled();

			if (!$this->isTaskExists('eventbooking.icprnotify'))
			{
				$params    = new Registry($plugin->params);
				$cacheTime = (int) $params->get('cache_time', 12);
				$lastRun   = (int) $params->get('last_run', time());

				$task = [
					'title'           => 'Events Booking - Email Registrants List',
					'type'            => 'eventbooking.icprnotify',
					'execution_rules' => [
						'rule-type'      => 'interval-hours',
						'interval-hours' => $cacheTime,
						'exec-time'      => gmdate('H:i', $lastRun),
						'exec-day'       => gmdate('d'),
					],
					'state'           => 1,
					'params'          => [
						'notification_emails' => $params->get('notification_emails', ''),
						'subject'             => $params->get('subject', ''),
						'message'             => $params->get('message', ''),
					],
				];

				$this->createSchedulerTask($task);
			}
		}

		// Uninstall the plugin
		$this->uninstallPlugin('system', 'icprnotify');
	}

	/**
	 * Ensure Task - Events Booking plugin is enabled
	 *
	 * @return void
	 */
	private function ensureTaskEventBookingPluginIsEnabled(): void
	{
		$plugin = PluginHelper::getPlugin('task', 'eventbooking');

		// Plugin is already enabled
		if ($plugin)
		{
			return;
		}

		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->update('#__extensions')
			->set('enabled = 1')
			->where('element = ' . $db->quote('eventbooking'))
			->where('folder = ' . $db->quote('task'));
		$db->setQuery($query)
			->execute();
	}

	/**
	 * Method to check if there is a task schedulers exists for a task type
	 *
	 * @param   string  $type
	 *
	 * @return bool
	 */
	private function isTaskExists(string $type): bool
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__scheduler_tasks')
			->where($db->quoteName('type') . ' = ' . $db->quote($type))
			->where($db->quoteName('state') . ' = 1');
		$db->setQuery($query);

		return $db->loadResult() > 0;
	}

	/**
	 * Create scheduler task
	 *
	 * @param   array  $task
	 *
	 * @return void
	 * @throws Exception
	 */
	private function createSchedulerTask(array $task): void
	{
		/** @var \Joomla\Component\Scheduler\Administrator\Extension\SchedulerComponent $component */
		$component = Factory::getApplication()->bootComponent('com_scheduler');

		/** @var \Joomla\Component\Scheduler\Administrator\Model\TaskModel $model */
		$model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);

		$model->save($task);
	}

	/**
	 * Uninstall the given plugin
	 *
	 * @param   string  $type
	 * @param   string  $name
	 *
	 * @return void
	 */
	private function uninstallPlugin(string $type, string $name): void
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('extension_id')
			->from('#__extensions')
			->where($db->quoteName('folder') . ' = ' . $db->quote($type))
			->where($db->quoteName('element') . ' = ' . $db->quote($name));
		$db->setQuery($query);
		$id = $db->loadResult();

		if ($id)
		{
			$installer = new Installer();

			try
			{
				$installer->uninstall('plugin', $id, 0);
			}
			catch (Exception $e)
			{
			}
		}
	}
}