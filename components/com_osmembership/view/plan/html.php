<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class OSMembershipViewPlanHtml extends MPFViewHtml
{
	/**
	 * The plan
	 *
	 * @var stdClass
	 */
	protected $item;

	/**
	 * The component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Bootstrap helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * ID of the plans which are renewable
	 *
	 * @var array
	 */
	protected $planIds;

	/**
	 * Plans in the system
	 *
	 * @var array
	 */
	protected $plans;

	/**
	 * Renew options
	 *
	 * @var array
	 */
	public $renewOptions = [];

	/**
	 * Upgrade options
	 *
	 * @var array
	 */
	public $upgradeRules = [];

	/**
	 * Name of views which could be used to get menu item parameters for the current view
	 *
	 * @var array
	 */
	protected $paramsViews = ['categories', 'plans', 'plan'];

	/**
	 * Display plan
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display()
	{
		$app  = Factory::getApplication();
		$item = $this->getModel()->getData();

		if (!$item->id)
		{
			$app->enqueueMessage(Text::_('OSM_INVALID_SUBSCRIPTION_PLAN'));
			$app->redirect(Uri::root(), 404);
		}

		if (!in_array($item->access, Factory::getApplication()->getIdentity()->getAuthorisedViewLevels()))
		{
			$app->enqueueMessage(Text::_('OSM_NOT_ALLOWED_PLAN'));
			$app->redirect(Uri::root(), 403);
		}

		$taxRate = OSMembershipHelper::callOverridableHelperMethod('Helper', 'calculateTaxRate', [$item->id]);
		$config  = OSMembershipHelper::getConfig();

		if ($config->show_price_including_tax && !$config->setup_price_including_tax && $taxRate > 0)
		{
			$item->price        = $item->price * (1 + $taxRate / 100);
			$item->trial_amount = $item->trial_amount * (1 + $taxRate / 100);
			$item->setup_fee    = $item->setup_fee * (1 + $taxRate / 100);
		}

		$item->short_description = HTMLHelper::_('content.prepare', $item->short_description);
		$item->description       = HTMLHelper::_('content.prepare', $item->description);

		// Process page title and meta data
		$this->params->def('page_heading', $item->page_heading ?: $item->title);
		$this->params->def('page_title', $item->page_title ?: $item->title);
		$this->params->def('menu-meta_keywords', $item->meta_keywords);
		$this->params->def('menu-meta_description', $item->meta_description);

		$this->item            = $item;
		$this->config          = $config;
		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();

		if (Factory::getApplication()->getIdentity()->id
			&& ($config->show_upgrade_options_on_plan_details || $config->show_renew_options_on_plan_details))
		{
			$this->getRenewUpgradeOptions();
		}

		OSMembershipHelperRoute::addPlan($item);

		$this->prepareDocument();

		$this->setLayout('default');

		parent::display();
	}

	/**
	 * Get renew and upgrade options to show on plans details page
	 */
	protected function getRenewUpgradeOptions()
	{
		$user  = Factory::getApplication()->getIdentity();
		$db    = $this->model->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__osmembership_subscribers')
			->where('plan_id = ' . $this->item->id)
			->where('group_admin_id > 0')
			->where('user_id = ' . $user->id);
		$db->setQuery($query);
		$total = $db->loadResult();

		if ($total)
		{
			return;
		}

		if ($this->config->show_renew_options_on_plan_details)
		{
			[$planIds, $renewOptions] = OSMembershipHelper::callOverridableHelperMethod(
				'Subscription',
				'getRenewOptions',
				[$user->id, $this->item->id]
			);
			$this->planIds      = $planIds;
			$this->renewOptions = $renewOptions;
		}

		if ($this->config->show_upgrade_options_on_plan_details)
		{
			$this->upgradeRules = OSMembershipHelper::callOverridableHelperMethod(
				'Subscription',
				'getUpgradeRules',
				[$user->id, $this->item->id]
			);
		}

		$this->plans = OSMembershipHelperDatabase::getAllPlans('id');
	}

	/**
	 * Set document meta-data and handle breadcumb if required
	 *
	 * @throws Exception
	 */
	protected function prepareDocument()
	{
		if ($this->input->getInt('hmvc_call'))
		{
			return;
		}

		$active = Factory::getApplication()->getMenu()->getActive();

		if (!$active)
		{
			return;
		}

		$this->setDocumentMetadata($this->params);
		$this->handleBreadcrumb($active);
	}

	/**
	 * Add breadcrumb items
	 *
	 * @param   MenuItem  $active
	 */
	protected function handleBreadcrumb($active)
	{
		if (!isset($active->query['view']))
		{
			return;
		}

		$pathway = Factory::getApplication()->getPathway();

		if ($active->query['view'] === 'categories' && $this->item->category_id > 0)
		{
			$category = OSMembershipHelperDatabase::getCategory($this->item->category_id);

			if ($category)
			{
				$pathway->addItem(
					$category->title,
					Route::_(OSMembershipHelperRoute::getCategoryRoute($category->id, $this->Itemid))
				);
			}
		}

		if (in_array($active->query['view'], ['categories', 'plans']))
		{
			$pathway->addItem($this->item->title);
		}
	}
}
