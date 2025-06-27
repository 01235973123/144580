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
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use OSL\Container\Container;
use OSSolution\HelpdeskPro\Site\Helper\Database as HelpdeskproHelperDatabase;
use OSSolution\HelpdeskPro\Site\Helper\Helper as HelpdeskproHelper;

class plgOSMembershipTicketsHistory extends CMSPlugin implements SubscriberInterface
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

		ob_start();
		$this->drawTicketsHistory($row);

		$result = [
			'title' => Text::_('OSM_HDP_TICKETS_HISTORY'),
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
		if (!ComponentHelper::isEnabled('com_helpdeskpro'))
		{
			return;
		}

		if ($this->app->isClient('administrator'))
		{
			return;
		}

		parent::registerListeners();
	}

	/**
	 * Display tickets history of the current logged in user
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	private function drawTicketsHistory($row)
	{
		// Bootstrap the component
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('*')
			->from('#__helpdeskpro_tickets')
			->where('user_id = ' . $this->app->getIdentity()->id)
			->order('id DESC');
		$db->setQuery($query, 0, $this->params->get('number_tickets') ?: 20);
		$rows = $db->loadObjectList();

		if (count($rows) === 0)
		{
			return;
		}

		// Bootstrap the component
		require_once JPATH_ADMINISTRATOR . '/components/com_helpdeskpro/init.php';

		// Get component config data
		$config = require JPATH_ADMINISTRATOR . '/components/com_helpdeskpro/config.php';

		// Creating component container, register auto-loader
		$container = Container::getInstance('com_helpdeskpro', $config);

		$fieldSuffix = HelpdeskproHelper::getFieldSuffix();

		// Ticket status filter
		$rowStatuses = HelpdeskproHelperDatabase::getAllStatuses('ordering', $fieldSuffix);

		$statusList = [];

		foreach ($rowStatuses as $status)
		{
			$statusList[$status->id] = $status->title;
		}

		// Ticket priority filter
		$rowPriorities = HelpdeskproHelperDatabase::getAllPriorities('ordering', $fieldSuffix);

		$priorityList = [];

		foreach ($rowPriorities as $priority)
		{
			$priorityList[$priority->id] = $priority->title;
		}

		require PluginHelper::getLayoutPath('osmembership', 'ticketshistory', 'default');
	}
}
