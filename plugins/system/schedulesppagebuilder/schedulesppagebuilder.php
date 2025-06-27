<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php'))
{
	return;
}

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

class plgSystemScheduleSPPageBuilder extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

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

	/**
	 * Make language files will be loaded automatically.
	 *
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterRoute'                => 'onAfterRoute',
			'onEditSubscriptionPlan'      => 'onEditSubscriptionPlan',
			'onAfterSaveSubscriptionPlan' => 'onAfterSaveSubscriptionPlan',
			'onProfileDisplay'            => 'onProfileDisplay',
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

		if (!ComponentHelper::isEnabled('com_sppagebuilder'))
		{
			return;
		}

		parent::registerListeners();
	}

	/**
	 * Render setting form
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

		$result = [
			'title' => Text::_('OSM_SCHEDULE_SP_PAGE_BUILDER_MANAGER'),
			'form'  => ob_get_clean(),
		];

		$this->addResult($event, $result);
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
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

		$scheduleSPPages   = $data['schedule_sp_page_builder_pages'] ?? [];
		$scheduleSPPageIds = [];
		$ordering          = 1;

		foreach ($scheduleSPPages as $scheduleSPPage)
		{
			if (empty($scheduleSPPage['page_id']))
			{
				continue;
			}

			$rowScheduleSPPageBuilder = new OSMembershipTableSchedulesppagebuilder($this->db);

			$rowScheduleSPPageBuilder->bind($scheduleSPPage);

			// Prevent item being moved to new plan on save as copy
			if ($isNew)
			{
				$rowScheduleSPPageBuilder->id = 0;
			}

			$rowScheduleSPPageBuilder->plan_id  = $row->id;
			$rowScheduleSPPageBuilder->ordering = $ordering++;
			$rowScheduleSPPageBuilder->store();
			$scheduleSPPageIds[] = $rowScheduleSPPageBuilder->id;
		}

		if (!$isNew)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->delete('#__osmembership_schedule_sppagebuilder_pages')
				->where('plan_id = ' . $row->id);

			if (count($scheduleSPPageIds))
			{
				$query->whereNotIn('id', $scheduleSPPageIds);
			}

			$db->setQuery($query)
				->execute();
		}
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
		$this->drawSchedulePages($row);

		$result = [
			'title' => Text::_('OSM_MY_SCHEDULE_SP_PAGE_BUILDER_PAGES'),
			'form'  => ob_get_clean(),
		];

		$this->addResult($event, $result);
	}

	/**
	 * Protect access to articles
	 *
	 * @return void
	 * @throws Exception
	 */
	public function onAfterRoute(Event $event): void
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$user = $this->app->getIdentity();

		if ($user->authorise('core.admin'))
		{
			return;
		}

		$option = $this->app->input->getCmd('option');
		$view   = $this->app->input->getCmd('view');

		if ($option != 'com_sppagebuilder' || $view != 'page')
		{
			return;
		}

		$db     = $this->db;
		$query  = $db->getQuery(true);
		$pageId = $this->app->input->getInt('id', 0);

		$query->select('*')
			->from('#__osmembership_schedule_sppagebuilder_pages')
			->where('page_id = ' . $pageId);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (empty($rows))
		{
			return;
		}

		$releasePageOlderThanXDays = (int) $this->params->get('release_pages_older_than_x_days', 0);

		if ($releasePageOlderThanXDays > 0)
		{
			$query->select('*')
				->from('#__sppagebuilder')
				->where('id = ' . $pageId);
			$db->setQuery($query);
			$rowPage = $db->loadObject();

			if ($rowPage->created_on && $rowPage->created_on != $db->getNullDate())
			{
				$publishedDate = $rowPage->created_on;

				$today         = Factory::getDate();
				$publishedDate = Factory::getDate($publishedDate);
				$numberDays    = $publishedDate->diff($today)->days;

				// This article is older than configured number of days, it can be accessed for free
				if ($today >= $publishedDate && $numberDays >= $releasePageOlderThanXDays)
				{
					return;
				}
			}
		}

		$canAccess     = false;
		$subscriptions = OSMembershipHelper::callOverridableHelperMethod('Subscription', 'getUserSubscriptionsInfo');

		foreach ($rows as $row)
		{
			if (isset($subscriptions[$row->plan_id]))
			{
				$subscription = $subscriptions[$row->plan_id];

				if ($subscription->active_in_number_days >= $row->number_days)
				{
					$canAccess = true;
					break;
				}
			}
		}

		if (!$canAccess)
		{
			if (!$user->id)
			{
				// Redirect user to login page
				$this->app->redirect(Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString())));
			}
			else
			{
				OSMembershipHelper::loadLanguage();

				$this->app->enqueueMessage(Text::_('OSM_SCHEDULE_PAGE_LOCKED'), 'error');
				$this->app->redirect(Uri::root(), 403);
			}
		}
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		$numberPagesEachTime                        = $this->params->get('number_new_pages_each_time', 10);
		$form                                       = Form::getInstance(
			'scheduleSPPageBuilder',
			JPATH_ROOT . '/plugins/system/schedulesppagebuilder/form/schedulesppagebuilder.xml'
		);
		$formData['schedule_sp_page_builder_pages'] = [];

		if ($row->id)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('*')
				->from('#__osmembership_schedule_sppagebuilder_pages')
				->where('plan_id = ' . $row->id)
				->order('ordering');
			$db->setQuery($query);

			foreach ($db->loadObjectList() as $schedulePage)
			{
				$formData['schedule_sp_page_builder_pages'][] = [
					'id'          => $schedulePage->id,
					'page_id'     => $schedulePage->page_id,
					'number_days' => $schedulePage->number_days,
				];
			}
		}

		for ($i = 0; $i < $numberPagesEachTime; $i++)
		{
			$formData['schedule_sp_page_builder_pages'][] = [
				'id '         => 0,
				'page_id'     => 0,
				'number_days' => '',
			];
		}

		$form->bind($formData);

		foreach ($form->getFieldset() as $field)
		{
			echo $field->input;
		}
	}

	/**
	 * Display Display List of Documents which the current subscriber can download from his subscription
	 *
	 * @param   object  $row
	 */
	private function drawSchedulePages($row)
	{
		$config = OSMembershipHelper::getConfig();

		$subscriptions = OSMembershipHelper::callOverridableHelperMethod('Subscription', 'getUserSubscriptionsInfo');

		if (empty($subscriptions))
		{
			return;
		}

		$accessiblePlanIds = array_keys($subscriptions);

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.hits, a.created_on, b.plan_id, b.number_days')
			->from('#__sppagebuilder AS a')
			->innerJoin('#__osmembership_schedule_sppagebuilder_pages AS b ON a.id = b.page_id')
			->whereIn('b.plan_id', $accessiblePlanIds)
			->order('b.plan_id')
			->order('b.number_days');
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (empty($items))
		{
			return;
		}

		foreach ($items as $item)
		{
			$item->isReleased = $this->isItemReleased($item);
		}

		echo OSMembershipHelperHtml::loadCommonLayout(
			'plugins/tmpl/schedulesppagebuilder.php',
			['items' => $items, 'subscriptions' => $subscriptions, 'params' => $this->params]
		);
	}

	/**
	 * Check if the K2 items released
	 *
	 * @param   stdClass  $item
	 *
	 * @return bool
	 */
	private function isItemReleased($item)
	{
		if (!$this->params->get('release_pages_older_than_x_days', 0))
		{
			return false;
		}

		$db = $this->db;

		if ($item->created_on && $item->created_on != $db->getNullDate())
		{
			$publishedDate = $item->created_on;
			$today         = Factory::getDate();
			$publishedDate = Factory::getDate($publishedDate);
			$numberDays    = $publishedDate->diff($today)->days;

			// This article is older than configured number of days, it can be accessed for free
			if ($today >= $publishedDate
				&& $numberDays >= $this->params->get('release_pages_older_than_x_days', 0))
			{
				return true;
			}
		}

		return false;
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
}
