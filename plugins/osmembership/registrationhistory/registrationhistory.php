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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgOSMembershipRegistrationhistory extends CMSPlugin implements SubscriberInterface
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

	public static function getSubscribedEvents(): array
	{
		return [
			'onProfileDisplay' => 'onProfileDisplay',
		];
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
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if ($this->app->isClient('administrator'))
		{
			return;
		}

		ob_start();
		$this->drawRegistrationHistory($row);

		$result = [
			'title' => Text::_('EB_REGISTRATION_HISTORY'),
			'form'  => ob_get_clean(),
		];

		$this->addResult($event, $result);
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_eventbooking'))
		{
			return;
		}

		parent::registerListeners();
	}

	/**
	 * Display registration history of the current logged in user
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	private function drawRegistrationHistory($row)
	{
		// Require libraries
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		EventbookingHelper::loadLanguage();

		JLoader::register('EventbookingModelHistory', JPATH_ROOT . '/components/com_eventbooking/model/history.php');

		/* @var EventbookingModelHistory $model */
		$model = RADModel::getInstance('History', 'EventbookingModel', [
			'table_prefix'    => '#__eb_',
			'remember_states' => false,
			'ignore_request'  => true,
		]);

		$model->setUserId($row->user_id);

		$items = $model->setState('limitstart', 0)
			->setState('limit', 0)
			->getData();

		if (empty($items))
		{
			return;
		}

		$config = EventbookingHelper::getConfig();

		$showDownloadCertificate = false;
		$showDownloadTicket      = false;
		$showDueAmountColumn     = false;

		$numberPaymentMethods = EventbookingHelper::getNumberNoneOfflinePaymentMethods();

		if ($numberPaymentMethods > 0)
		{
			foreach ($items as $item)
			{
				if ($item->payment_status != 1)
				{
					$showDueAmountColumn = true;
					break;
				}
			}
		}

		foreach ($items as $item)
		{
			$item->show_download_certificate = false;

			if ($item->published == 1 && $item->activate_certificate_feature == 1
				&& $item->event_end_date_minutes >= 0
				&& (!$config->download_certificate_if_checked_in || $item->checked_in)
			)
			{
				$showDownloadCertificate         = true;
				$item->show_download_certificate = true;
			}

			if ($item->ticket_code && $item->payment_status == 1)
			{
				$showDownloadTicket = true;
			}
		}

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('id')
			->from('#__eb_payment_plugins')
			->where('published = 1')
			->where('name NOT LIKE "os_offline%"');
		$db->setQuery($query);
		$onlinePaymentPlugins = $db->loadColumn();

		if (in_array('last_name', EventbookingHelper::getPublishedCoreFields()))
		{
			$showLastName = true;
		}
		else
		{
			$showLastName = false;
		}

		$return = base64_encode(Uri::getInstance()->toString());

		require PluginHelper::getLayoutPath('osmembership', 'registrationhistory', 'default');
	}
}
