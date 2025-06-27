<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class Pkg_OsmembershipInstallerScript
{
	/**
	 * Minimum PHP version
	 */
	const MIN_PHP_VERSION = '7.4.0';

	/**
	 * Minimum Joomla version
	 */
	const MIN_JOOMLA_VERSION = '4.2.0';

	/**
	 * Minimum Membership Pro version to allow update
	 */
	const MIN_MEMBERSHIP_PRO_VERSION = '2.7.0';

	/**
	 * The original version, use for update process
	 *
	 * @var string
	 */
	protected $installedVersion = '2.7.0';

	/**
	 * Perform some check to see if the extension could be installed/updated
	 *
	 * @param   string                                        $type
	 * @param   \Joomla\CMS\Installer\Adapter\PackageAdapter  $parent
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function preflight($type, $parent)
	{
		if (!version_compare(JVERSION, self::MIN_JOOMLA_VERSION, 'ge'))
		{
			Factory::getApplication()->enqueueMessage(
				'Cannot install Membership Pro in a Joomla release prior to ' . self::MIN_JOOMLA_VERSION,
				'error'
			);

			return false;
		}

		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				'Membership Pro requires PHP ' . self::MIN_PHP_VERSION . '+ to work. Please contact your hosting provider, ask them to update PHP version for your hosting account.',
				'error'
			);

			return false;
		}

		$this->getInstalledVersion();

		if (version_compare($this->installedVersion, self::MIN_MEMBERSHIP_PRO_VERSION, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				'Update from older version than ' . self::MIN_MEMBERSHIP_PRO_VERSION . ' is not supported! You need to update to version 2.26.0 first. Please contact support to get that old Membership Pro version',
				'error'
			);

			return false;
		}

		if (version_compare($this->installedVersion, '3.0.0', '<'))
		{
			$this->uninstallPlugin('osmembership', 'spout');
		}

		if ($type === 'update')
		{
			$this->deleteOldUpdateSite();
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
		// Migrate existing tasks and system plugins to new scheduled tasks
		if ($type === 'update' && version_compare($this->installedVersion, '4.2.0', '<='))
		{
			$this->renameTaskMPHouseKeepingPlugin();
			$this->migrateTaskOfflinePaymentReminderPlugin();
			$this->migrateTaskOfflineRecurringInvoicePlugin();
			$this->migrateSystemOfflineRecurringInvoicePlugin();
			$this->migrateSystemICPSNotifyPlugin();
			$this->migrateSystemCleanEmailsLogPlugin();
			$this->migrateSystemMembershipProCleaner();
		}
	}

	/**
	 * Get installed version of the component
	 *
	 * @return void
	 */
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
			->where($db->quoteName('element') . ' = ' . $db->quote('com_osmembership'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'));
		$db->setQuery($query);
		$manifestCache = $db->loadResult();

		if ($manifestCache)
		{
			$manifest               = json_decode($manifestCache);
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
				$db->quoteName('location') . ' = ' . $db->quote('https://joomdonation.com/updates/membershippro.xml')
			);
		$db->setQuery($query)
			->execute();
	}

	/**
	 * Rename Task - MPHouseKeeping plugin to Task - Membership Pro plugin
	 *
	 * @return void
	 */
	private function renameTaskMPHouseKeepingPlugin(): void
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$field = $db->quoteName('type');
		$query = $db->getQuery(true)
			->update('#__scheduler_tasks')
			->set(
				"$field = REPLACE($field, " . $db->quote('mphoousekeeping.') . ', ' . $db->quote('membershippro.') . ')'
			)
			->where("$field LIKE " . $db->quote('mphoousekeeping.%'));
		$db->setQuery($query)
			->execute();

		$this->enableTaskMembershipProPluginIfRequired();

		$this->uninstallPlugin('task', 'mphousekeeping');
	}

	/**
	 * Move Task - Offline Payment Reminder plugin
	 *
	 * @return void
	 */
	private function migrateTaskOfflinePaymentReminderPlugin(): void
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->update('#__scheduler_tasks')
			->set($db->quoteName('type') . ' = ' . $db->quote('membershippro.offlinePaymentReminder'))
			->where($db->quoteName('type') . ' = ' . $db->quote('mpofflinepaymentreminder.sendOfflinePaymentReminder'));
		$db->setQuery($query)
			->execute();

		$this->enableTaskMembershipProPluginIfRequired();

		$this->uninstallPlugin('task', 'mpofflinepaymentreminder');
	}

	/**
	 * Move Task - Offline Payment Reminder plugin
	 *
	 * @return void
	 */
	private function migrateTaskOfflineRecurringInvoicePlugin(): void
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->update('#__scheduler_tasks')
			->set($db->quoteName('type') . ' = ' . $db->quote('membershippro.sendOfflineRecurringInvoice'))
			->where(
				$db->quoteName('type') . ' = ' . $db->quote('mpofflinerecurringinvoice.sendOfflineRecurringInvoice')
			);
		$db->setQuery($query)
			->execute();

		$this->enableTaskMembershipProPluginIfRequired();

		$this->uninstallPlugin('task', 'mpofflinerecurringinvoice');
	}

	/**
	 * Move System - Offline Recurring Invoice plugin
	 *
	 * @return void
	 */
	private function migrateSystemOfflineRecurringInvoicePlugin(): void
	{
		$plugin = PluginHelper::getPlugin('system', 'mpofflinerecurringinvoice');

		if ($plugin)
		{
			$this->ensureTaskMembershipProPluginIsEnabled();
		}

		if ($plugin && !$this->isTaskExists('membershippro.sendOfflineRecurringInvoice'))
		{
			$params    = new Registry($plugin->params);
			$cacheTime = (int) $params->get('cache_time', 1);
			$lastRun   = (int) $params->get('last_run', time());

			$task = [
				'title'           => 'Membership Pro - Offline Recurring Invoice',
				'type'            => 'membershippro.sendOfflineRecurringInvoice',
				'execution_rules' => [
					'rule-type'      => 'interval-hours',
					'interval-hours' => $cacheTime,
					'exec-time'      => gmdate('H:i', $lastRun),
					'exec-day'       => gmdate('d'),
				],
				'state'           => 1,
				'params'          => [
					'number_subscribers' => $params->get('number_subscribers', 10),
					'number_days'        => $params->get('number_days', 10),
					'published'          => $params->get('published', 0),
				],
			];

			$this->createSchedulerTask($task);
		}

		// Uninstall the plugin
		$this->uninstallPlugin('system', 'mpofflinerecurringinvoice');
	}

	/**
	 * Migrate System - ICPSNotify Plugin
	 *
	 * @return void
	 * @throws Exception
	 */
	private function migrateSystemICPSNotifyPlugin(): void
	{
		$plugin = PluginHelper::getPlugin('system', 'icpsnotify');

		if ($plugin)
		{
			$this->ensureTaskMembershipProPluginIsEnabled();

			// Create scheduled task to replace the system plugin
			if (!$this->isTaskExists('membershippro.sendICPSNotification'))
			{
				$params    = new Registry($plugin->params);
				$cacheTime = (int) $params->get('cache_time', 3);
				$lastRun   = (int) $params->get('last_run', time());

				$task = [
					'title'           => 'Membership Pro - Incomplete Payment Subscriptions Notification',
					'type'            => 'membershippro.sendICPSNotification',
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
		$this->uninstallPlugin('system', 'icpsnotify');
	}


	/**
	 * Move System - Offline Recurring Invoice plugin
	 *
	 * @return void
	 */
	private function migrateSystemCleanEmailsLogPlugin(): void
	{
		$plugin = PluginHelper::getPlugin('system', 'osmembershipcleanemailslog');

		if ($plugin)
		{
			$this->ensureTaskMembershipProPluginIsEnabled();

			if (!$this->isTaskExists('membershippro.cleanEmailLogs'))
			{
				$params    = new Registry($plugin->params);
				$cacheTime = (int) $params->get('cache_time', 24);
				$lastRun   = (int) $params->get('last_run', time());

				$task = [
					'title'           => 'Membership Pro - Clean Emails Log',
					'type'            => 'membershippro.cleanEmailLogs',
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
		$this->uninstallPlugin('system', 'osmembershipcleanemailslog');
	}

	/**
	 * Move System - Offline Recurring Invoice plugin
	 *
	 * @return void
	 */
	private function migrateSystemMembershipProCleaner(): void
	{
		$plugin = PluginHelper::getPlugin('system', 'osmembershipcleaner');

		if ($plugin)
		{
			$this->ensureTaskMembershipProPluginIsEnabled();

			if (!$this->isTaskExists('membershippro.deleteIncompletePaymentSubscriptions'))
			{
				$params    = new Registry($plugin->params);
				$cacheTime = (int) $params->get('cache_time', 24);
				$lastRun   = (int) $params->get('last_run', time());

				$task = [
					'title'           => 'Membership Pro - Delete Incomplete Payment Subscriptions',
					'type'            => 'membershippro.deleteIncompletePaymentSubscriptions',
					'execution_rules' => [
						'rule-type'      => 'interval-hours',
						'interval-hours' => $cacheTime,
						'exec-time'      => gmdate('H:i', $lastRun),
						'exec-day'       => gmdate('d'),
					],
					'state'           => 1,
					'params'          => [
						'delay' => $params->get('number_days', 20),
					],
				];

				$this->createSchedulerTask($task);
			}
		}

		// Uninstall the plugin
		$this->uninstallPlugin('system', 'osmembershipcleaner');
	}

	/**
	 * Method to check
	 *
	 * @return void
	 */
	private function enableTaskMembershipProPluginIfRequired(): void
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__scheduler_tasks')
			->where($db->quoteName('type') . ' LIKE ' . $db->quote('membershippro.%'));
		$db->setQuery($query);

		if ($db->loadResult() > 0)
		{
			// Enable Task - Events Booking plugin
			$this->ensureTaskMembershipProPluginIsEnabled();
		}
	}

	/**
	 * Ensure Task - Events Booking plugin is enabled
	 *
	 * @return void
	 */
	private function ensureTaskMembershipProPluginIsEnabled(): void
	{
		$plugin = PluginHelper::getPlugin('task', 'membershippro');

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
			->where('element = ' . $db->quote('membershippro'))
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