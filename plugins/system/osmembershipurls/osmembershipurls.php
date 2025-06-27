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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php'))
{
	return;
}

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

class plgSystemOSMembershipUrls extends CMSPlugin implements SubscriberInterface
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
			'onAfterInitialise'           => 'onAfterInitialise',
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

		parent::registerListeners();
	}

	/**
	 * Render settings from
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
		$form = ob_get_contents();
		ob_end_clean();

		$result = [
			'title' => Text::_('PLG_OSMEMBERSHIP_JOOMLA_URLS_SETTINGS'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * Store setting into database
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

		$db     = $this->db;
		$query  = $db->getQuery(true);
		$urls   = array_filter(explode("\r\n", $data['urls']));
		$titles = array_filter(explode("\r\n", $data['titles']));

		if (!$isNew)
		{
			$query->delete('#__osmembership_urls')
				->where('plan_id = ' . $row->id);
			$db->setQuery($query);
			$db->execute();

			$query->clear();
		}

		if (count($urls))
		{
			$query->insert('#__osmembership_urls')
				->columns('plan_id, url, title');

			foreach ($urls as $i => $url)
			{
				$url = trim($url);
				if ($url)
				{
					$title = !empty($titles[$i]) ? $titles[$i] : '';
					$url   = $db->quote($url);
					$title = $db->quote($title);
					$query->values("$row->id, $url, $title");
				}
			}

			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Display form allows users to change setting for this subscription plan
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		$urls   = [];
		$titles = [];

		if ($row->id > 0)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->select('title, url')
				->from('#__osmembership_urls')
				->where('plan_id = ' . $row->id);
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			foreach ($rows as $row)
			{
				$urls[]   = $row->url;
				$titles[] = $row->title;
			}
		}

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'form');
	}

	/**
	 * Restrict access to the current URL if it is needed
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 * @throws Exception
	 */
	public function onAfterInitialise(Event $event): void
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		if ($this->app->getIdentity()->authorise('core.admin'))
		{
			return;
		}

		$currentUrl = trim(Uri::getInstance()->toString());

		//remove www in the url
		$currentUrl = str_replace('www.', '', $currentUrl);
		$siteUrl    = Uri::root();
		$siteUrl    = str_replace('www.', '', $siteUrl);

		if ($siteUrl == $currentUrl)
		{
			//Don't prevent access to homepage
			return;
		}

		$planIds = $this->getRequiredPlanIds($currentUrl);

		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__osmembership_plans')
			->where('published = 0');
		$db->setQuery($query);
		$unpublishedPlanIds = $db->loadColumn();
		$planIds            = array_diff($planIds, $unpublishedPlanIds);

		if (count($planIds))
		{
			//Check to see the current user has an active subscription plans
			$activePlans = OSMembershipHelperSubscription::getActiveMembershipPlans();

			if (!count(array_intersect($planIds, $activePlans)))
			{
				//Load language file
				OSMembershipHelper::loadLanguage();

				//Get title of these subscription plans
				$query->clear()
					->select('title')
					->from('#__osmembership_plans')
					->whereIn('id', $planIds)
					->where('published = 1')
					->order('ordering');
				$db->setQuery($query);

				$planTitles = implode(' ' . Text::_('OSM_OR') . ' ', $db->loadColumn());
				$msg        = Text::_('OS_MEMBERSHIP_URL_ACCESS_RESITRICTED');
				$msg        = str_replace('[PLAN_TITLES]', $planTitles, $msg);

				// Try to find the best redirect URL
				$redirectUrl = OSMembershipHelper::callOverridableHelperMethod(
					'Helper',
					'getPluginRestrictionRedirectUrl',
					[$this->params, $planIds]
				);

				// Store URL of this page to redirect user back after user logged in if they have active subscription of this plan
				$session = $this->app->getSession();
				$session->set('osm_return_url', Uri::getInstance()->toString());
				$session->set('required_plan_ids', $planIds);

				$this->app->enqueueMessage($msg);
				$this->app->redirect($redirectUrl);
			}
		}
	}

	/**
	 * Display list of accessible URLs on profile page
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onProfileDisplay(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!$this->params->get('display_urls_in_profile'))
		{
			return;
		}

		ob_start();
		$this->displayUrls($row);
		$form = ob_get_clean();

		$result = [
			'title' => Text::_('OSM_MY_PAGES'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * Method to get the required plan Ids to access to the given URLs
	 *
	 * @param   string  $url
	 *
	 * @return array
	 */
	protected function getRequiredPlanIds($url)
	{
		$db      = $this->db;
		$query   = $db->getQuery(true);
		$planIds = [];

		switch ($this->params->get('compare_method', 0))
		{
			case 0:
				$query->select('a.id')
					->from('#__osmembership_plans As a')
					->innerJoin('#__osmembership_urls AS b ON a.id = b.plan_id')
					->where('a.published = 1')
					->where($db->quoteName('b.url') . ' = ' . $db->quote($url));
				$db->setQuery($query);

				return $db->loadColumn();
			case 1:
				$query->select('a.id, b.url')
					->from('#__osmembership_plans As a')
					->innerJoin('#__osmembership_urls AS b ON a.id = b.plan_id')
					->where('a.published = 1');
				$db->setQuery($query);
				$rows = $db->loadObjectList();

				foreach ($rows as $row)
				{
					$matches = [];

					if (preg_match('~' . preg_quote($row->url) . '~', $url, $matches))
					{
						$planIds[] = $row->id;
					}
				}
				break;
			case 2:
				$query->select('a.id, b.url')
					->from('#__osmembership_plans As a')
					->innerJoin('#__osmembership_urls AS b ON a.id = b.plan_id')
					->where('a.published = 1');
				$db->setQuery($query);
				$rows = $db->loadObjectList();

				foreach ($rows as $row)
				{
					$matches = [];

					if (preg_match('~' . $row->url . '~', $url, $matches))
					{
						$planIds[] = $row->id;
					}
				}
				break;
		}

		return $planIds;
	}

	/**
	 * Display pages which subscriber can access to
	 *
	 * @throws Exception
	 */
	protected function displayUrls()
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$activePlanIds = OSMembershipHelperSubscription::getActiveMembershipPlans();

		$query->select('title, url')
			->from('#__osmembership_urls')
			->whereIn('plan_id', $activePlanIds)
			->order('id');
		$db->setQuery($query);

		$urls = $db->loadObjectList();

		if (empty($urls))
		{
			return;
		}

		echo OSMembershipHelperHtml::loadCommonLayout('plugins/tmpl/osmembershipurls.php', ['urls' => $urls]);
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
