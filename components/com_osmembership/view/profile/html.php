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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use OSSolution\MembershipPro\Admin\Event\Profile\ProfileDisplay;

class OSMembershipViewProfileHtml extends MPFViewHtml
{
	/**
	 * The available upgrade rules
	 *
	 * @var array
	 */
	protected $upgradeRules;

	/**
	 * The plan Ids
	 *
	 * @var array
	 */
	protected $planIds;

	/**
	 * The available renew options
	 *
	 * @var array
	 */
	protected $renewOptions;

	/**
	 * Plans
	 *
	 * @var array
	 */
	protected $plans;

	/**
	 * Profile data
	 *
	 * @var stdClass
	 */
	protected $item;

	/**
	 * Subscription History
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Profile Form
	 *
	 * @var MPFForm
	 */
	protected $form;

	/**
	 * Plugins output
	 *
	 * @var array
	 */
	protected $plugins;

	/**
	 * Contain select dropdowns use on the form
	 *
	 * @var array
	 */
	protected $lists = [];

	/**
	 * Subscriptions data
	 *
	 * @var array
	 */
	protected $subscriptions;

	/**
	 * Whether we need to show download member card column
	 *
	 * @var bool
	 */
	protected $showDownloadMemberCard;

	/**
	 * Bootstrap Helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Additional tabs
	 *
	 * @var array
	 */
	protected $additionalTabs = [];

	/**
	 * The selected state
	 *
	 * @var string
	 */
	protected $selectedState = '';

	/**
	 * Prepare view data
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$this->requestLogin('OSM_LOGIN_TO_EDIT_PROFILE');

		/* @var JApplicationSite $app */
		$app    = Factory::getApplication();
		$user   = $app->getIdentity();
		$config = OSMembershipHelper::getConfig();
		$item   = OSMembershipHelperSubscription::getMembershipProfile($user->id);

		if (!$item)
		{
			if (OSMembershipHelperSubscription::fixProfileId($user->id))
			{
				// Redirect to current page after fixing the data
				$app->redirect(Uri::getInstance()->toString());
			}
			else
			{
				if ($this->params->get('no_subscription_behavior'))
				{
					$this->setLayout('no_profile');

					return;
				}
				$this->redirectToUserProfilePage();
			}
		}

		// Fix wrong data for profile record
		if ($item->id != $item->profile_id)
		{
			$item->profile_id = $item->id;
			$db               = $this->model->getDbo();
			$query            = $db->getQuery(true)
				->update('#__osmembership_subscribers')
				->set('profile_id = ' . $item->id)
				->where('id = ' . $item->id);
			$db->setQuery($query)
				->execute();
		}

		// Get subscriptions history
		/* @var OSMembershipModelSubscriptions $model */
		$model = MPFModel::getTempInstance('Subscriptions', 'OSMembershipModel');

		// Display 100 records at a time by default
		$model->set('limit', 100);
		$items = $model->getData();

		if (OSMembershipHelper::isUniquePlan($item->user_id))
		{
			$planId = $item->plan_id;
		}
		else
		{
			$planId = 0;
		}

		// Form
		$rowFields = OSMembershipHelper::getProfileFields($planId);

		// In case user is a group member, only show fields which are being available on group members form
		if ($item->group_admin_id > 0)
		{
			$rowFields = $this->model->filterGroupMemberFields($rowFields);
		}

		$data = OSMembershipHelper::getProfileData($item, $planId, $rowFields);
		$form = new MPFForm($rowFields);

		if (!isset($data['country']) || !$data['country'])
		{
			$data['country'] = $config->default_country;
		}

		$form->setData($data)->bindData();
		$form->buildFieldsDependency();

		// Trigger third party add-on
		PluginHelper::importPlugin('osmembership');

		$event = new ProfileDisplay(['row' => $item]);

		$results = array_filter($app->triggerEvent($event->getName(), $event));

		if ($item->group_admin_id == 0)
		{
			[$planIds, $renewOptions] = OSMembershipHelper::callOverridableHelperMethod(
				'Subscription',
				'getRenewOptions',
				[$user->id]
			);

			$this->upgradeRules = OSMembershipHelper::callOverridableHelperMethod(
				'Subscription',
				'getUpgradeRules',
				[$item->user_id]
			);

			$this->planIds      = $planIds;
			$this->renewOptions = $renewOptions;
			$this->plans        = OSMembershipHelperDatabase::getAllPlans('id');
		}

		// Load js file to support state field dropdown
		OSMembershipHelper::addLangLinkForAjax();

		OSMembershipHelperJquery::loadjQuery();

		$wa = Factory::getApplication()
			->getDocument()
			->getWebAssetManager()
			->registerAndUseScript(
				'com_osmembership.paymentmethods',
				'media/com_osmembership/assets/js/paymentmethods.min.js'
			);

		$customJSFile = JPATH_ROOT . '/media/com_osmembership/assets/js/custom.js';

		if (file_exists($customJSFile) && filesize($customJSFile) > 0)
		{
			$wa->registerAndUseScript('com_osmembership.custom', 'media/com_osmembership/assets/js/custom.js');
		}

		if ($config->get('enable_select_show_hide_members_list'))
		{
			$options   = [];
			$options[] = HTMLHelper::_('select.option', 1, Text::_('JYES'));
			$options[] = HTMLHelper::_('select.option', 0, Text::_('JNO'));

			$this->lists['show_on_members_list'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'show_on_members_list',
				'',
				'value',
				'text',
				$item->show_on_members_list
			);
		}

		$subscriptions = OSMembershipHelperSubscription::getSubscriptionsForUser($item->user_id);

		$showDownloadMemberCard = false;

		if ($config->activate_member_card_feature)
		{
			foreach ($subscriptions as $subscription)
			{
				if ($subscription->activate_member_card_feature && in_array($subscription->subscription_status, [1, 2]))
				{
					$showDownloadMemberCard = true;

					$subscription->show_download_member_card = true;
				}
				else
				{
					$subscription->show_download_member_card = false;
				}
			}
		}

		foreach ($this->params->get('tabs', []) as $tab)
		{
			if (is_string($tab['title']) && strlen(trim($tab['title'])) > 0)
			{
				$this->additionalTabs[$tab['title']] = $tab['content'];
			}
		}

		$fields = $form->getFields();

		if (isset($fields['state']))
		{
			$this->selectedState = $fields['state']->value;
		}

		// Need to get subscriptions information of the user
		$this->item                   = $item;
		$this->config                 = $config;
		$this->items                  = $items;
		$this->form                   = $form;
		$this->plugins                = $results;
		$this->subscriptions          = $subscriptions;
		$this->bootstrapHelper        = OSMembershipHelperBootstrap::getInstance();
		$this->showDownloadMemberCard = $showDownloadMemberCard;
	}

	/**
	 * Method to redirect user to Joomla user profile page
	 * @throws Exception
	 */
	protected function redirectToUserProfilePage()
	{
		$app = Factory::getApplication();

		// User don't have any active subscription, redirect to user profile page
		$app->enqueueMessage(Text::_('OSM_DONOT_HAVE_SUBSCRIPTION_RECORD'));
		$app->redirect(Route::_('index.php?option=com_users&view=profile', false));
	}
}
