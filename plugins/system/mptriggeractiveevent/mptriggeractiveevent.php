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
use Joomla\Registry\Registry;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipActive;

class plgSystemMPTriggerActiveEvent extends CMSPlugin implements SubscriberInterface
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
	 * Trigger active event for subscription has start date = today date
	 *
	 * @return void
	 */
	public function onAfterRespond(Event $event): void
	{
		if (!$this->canRun())
		{
			return;
		}

		//Store last run time
		$this->params->set('last_run', time());
		$params = $this->params->toString();
		$db     = $this->db;
		$query  = $db->getQuery(true)
			->update('#__extensions')
			->set('params = ' . $db->quote($params))
			->where('`element` = "mptriggeractiveevent"')
			->where('`folder` = "system"');

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

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$plans = $this->getPlansNeedEventTrigger();

		if (count($plans) == 0)
		{
			return;
		}

		$query->clear()
			->select('id')
			->from('#__osmembership_subscribers')
			->where('active_event_triggered = 0')
			->whereIn('plan_id', array_keys($plans))
			->where('published = 1')
			->where('DATE(from_date) = UTC_DATE()')
			->order('from_date');

		$db->setQuery($query, 0, 20);
		$ids = $db->loadColumn();

		if (count($ids))
		{
			//Load Plugin to trigger OnMembershipExpire event
			PluginHelper::importPlugin('osmembership');

			foreach ($ids as $id)
			{
				$row = new OSMembershipTableSubscriber($db);

				if ($row->load($id))
				{
					$row->active_event_triggered = 1;
					$row->store();

					//Trigger plugins

					$event = new MembershipActive(['row' => $row]);

					$this->app->triggerEvent($event->getName(), $event);
				}
			}
		}
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
		$cachePath = Factory::getApplication()->get('cache_path', JPATH_SITE . '/cache');

		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $client_id)
			{
				try
				{
					$options = [
						'defaultgroup' => $group,
						'cachebase'    => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' : $cachePath,
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

	/**
	 * Get plans need to trigger active event
	 *
	 * @return array
	 */
	private function getPlansNeedEventTrigger()
	{
		$plans = [];
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('id, params')
			->from('#__osmembership_plans')
			->where('published = 1');
		$db->setQuery($query);

		foreach ($db->loadObjectList() as $row)
		{
			$params = new Registry($row->params);

			if ($params->get('subscription_start_date_option', '0') == '0')
			{
				continue;
			}

			$plans[$row->id] = $row;
		}

		return $plans;
	}

	/**
	 * Method to check whether this plugin should be run
	 *
	 * @return bool
	 */
	private function canRun()
	{
		// If trigger reminder code is set, we will only process sending reminder from cron job
		if (trim($this->params->get('trigger_active_event_code', ''))
			&& trim($this->params->get('trigger_active_event_code', '')) == $this->app->input->getString('trigger_active_event_code'))
		{
			return true;
		}

		$lastRun   = (int) $this->params->get('last_run', 0);
		$now       = time();
		$cacheTime = (int) $this->params->get('cache_time', 1) * 3600;

		if (($now - $lastRun) < $cacheTime)
		{
			return false;
		}

		return true;
	}
}
