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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;

class OSMembershipViewUpgradeMembershipHtml extends MPFViewHtml
{
	/**
	 * The flag to mark that this view does not have associate model
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * The available upgrade options
	 *
	 * @var array
	 */
	protected $upgradeRules;

	/**
	 * The published plans
	 *
	 * @var array
	 */
	protected $plans;

	/**
	 * The component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Bootstrap Helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * Display the view
	 *
	 * @throws Exception
	 */
	public function display()
	{
		$this->requestLogin('OSM_LOGIN_TO_UPGRADE_MEMBERSHIP');

		$app    = Factory::getApplication();
		$user   = $app->getIdentity();
		$config = OSMembershipHelper::getConfig();
		$item   = OSMembershipHelperSubscription::getMembershipProfile($user->id);

		if (!$item)
		{
			// Fix Profile ID
			if (OSMembershipHelperSubscription::fixProfileId($user->id))
			{
				$app->redirect(Uri::getInstance()->toString());
			}
			else
			{
				$app->enqueueMessage(Text::_('OSM_DONOT_HAVE_SUBSCRIPTION_RECORD_TO_UPGRADE'));

				return;
			}
		}

		if ($item->id != $item->profile_id)
		{
			$item->profile_id = $item->id;
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->update('#__osmembership_subscribers')
				->set('profile_id = ' . $item->id)
				->where('id = ' . $item->id);
			$db->setQuery($query);
			$db->execute();
		}

		if ($item->group_admin_id > 0)
		{
			$app->enqueueMessage(Text::_('OSM_ONLY_GROUP_ADMIN_CAN_UPGRADE_MEMBERSHIP'));

			return;
		}

		// Need to get subscriptions information of the user
		$toPlanId     = $this->input->getInt('to_plan_id');
		$upgradeRules = OSMembershipHelper::callOverridableHelperMethod(
			'Subscription',
			'getUpgradeRules',
			[$item->user_id]
		);
		$n            = count($upgradeRules);

		if ($toPlanId > 0)
		{
			for ($i = 0; $i < $n; $i++)
			{
				$rule = $upgradeRules[$i];

				if ($rule->to_plan_id != $toPlanId)
				{
					unset($upgradeRules[$i]);
				}
			}

			$upgradeRules = array_values($upgradeRules);
		}

		$this->upgradeRules    = $upgradeRules;
		$this->config          = $config;
		$this->plans           = OSMembershipHelperDatabase::getAllPlans('id');
		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();

		$this->setLayout('default');

		parent::display();
	}
}
