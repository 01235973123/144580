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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipExpire;

class plgSystemOSMembershipUpdateStatus extends CMSPlugin implements SubscriberInterface
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
	 * Update status of subscriptions to expired if today date is greater than expired date
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
		$db    = $this->db;
		$query = $db->getQuery(true);

		if (!trim($this->params->get('secret_code', '')))
		{
			$this->params->set('last_run', time());
			$params = $this->params->toString();

			$query->update('#__extensions')
				->set('params = ' . $db->quote($params))
				->where('`element` = "osmembershipupdatestatus"')
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
		}

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$config = OSMembershipHelper::getConfig();

		$query->clear()
			->select('a.id')
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__osmembership_plans AS b ON a.plan_id = b.id')
			->where('b.lifetime_membership != 1')
			->where('a.published = 1')
			->order('a.id');

		$gracePeriod = (int) $config->get('grace_period');

		if ($gracePeriod > 0)
		{
			$gracePeriodUnit = $config->get('grace_period_unit', 'd');

			switch ($gracePeriodUnit)
			{
				case 'm':
					$query->where('DATE_ADD(a.to_date, INTERVAL ' . $gracePeriod . ' MINUTE) < UTC_TIMESTAMP()');
					break;
				case 'h':
					$query->where('DATE_ADD(a.to_date, INTERVAL ' . $gracePeriod . ' HOUR) < UTC_TIMESTAMP()');
					break;
				default:
					$query->where('DATE_ADD(a.to_date, INTERVAL ' . $gracePeriod . ' DAY) < UTC_TIMESTAMP()');
					break;
			}
		}
		else
		{
			$query->where('a.to_date < UTC_TIMESTAMP()');
		}

		$db->setQuery($query, 0, 100);
		$ids = $db->loadColumn();

		if (count($ids) == 0)
		{
			return;
		}

		//Load Plugin to trigger OnMembershipExpire event
		PluginHelper::importPlugin('osmembership');

		foreach ($ids as $id)
		{
			$row  = new OSMembershipTableSubscriber($db);

			if ($row->load($id))
			{
				$row->published = 2;
				$row->store();

				//Trigger plugins
				$event = new MembershipExpire(['row' => $row]);
				$this->app->triggerEvent($event->getName(), $event);
			}
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
		if (trim($this->params->get('secret_code', ''))
			&& trim($this->params->get('secret_code', '')) != $this->app->input->getString('secret_code'))
		{
			return false;
		}

		// We only need to check last run time if cron job is not used
		if (!trim($this->params->get('secret_code', '')))
		{
			$lastRun   = (int) $this->params->get('last_run', 0);
			$now       = time();
			$cacheTime = (int) $this->params->get('cache_time', 1) * 3600;

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
