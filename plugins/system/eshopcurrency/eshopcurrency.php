<?php

/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Eshop Currency Plugin
 *
 * @package        Joomla
 * @subpackage     EShop
 */
class plgSystemEshopCurrency extends CMSPlugin
{

	public function onAfterInitialise()
	{
		require_once JPATH_ROOT . '/components/com_eshop/helpers/helper.php';

		if (EShopHelper::getConfigValue('auto_update_currency') && is_file(JPATH_ROOT . '/components/com_eshop/eshop.php'))
		{
			$lastRun    = (int) $this->params->get('last_run', 0);
			$timePeriod = $this->params->get('time_period', 1);
			$timeUnit   = $this->params->get('time_unit', 'day');
			$now        = time();

			if ($timeUnit == 'min')
			{
				$cacheTime = $timePeriod * 60;
			}
			else
			{
				if ($timeUnit == 'hour')
				{
					$cacheTime = $timePeriod * 60 * 60;
				}
				else
				{
					if ($timeUnit == 'day')
					{
						$cacheTime = $timePeriod * 60 * 60 * 24;
					}
					else
					{
						if ($timeUnit == 'week')
						{
							$cacheTime = $timePeriod * 60 * 60 * 24 * 7;
						}
					}
				}
			}

			if (($now - $lastRun) < $cacheTime)
			{
				return;
			}

			// Store last run time
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$this->params->set('last_run', $now);
			$params = $this->params->toString();
			$query->clear();
			$query->update('#__extensions')
				->set('params = ' . $db->quote($params))
				->where('`element` = "eshopcurrency"')
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

			EShopHelper::updateCurrencies(false, $timePeriod, $timeUnit);
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
	 * @since   2.0.4
	 */
	private function clearCacheGroups(array $clearGroups, array $cacheClients = [0, 1])
	{
		$conf = Factory::getConfig();
		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $client_id)
			{
				try
				{
					$options = [
						'defaultgroup' => $group,
						'cachebase'    => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' :
							$conf->get('cache_path', JPATH_SITE . '/cache'),
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
