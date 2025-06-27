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

class OSMembershipViewRenewmembershipHtml extends MPFViewHtml
{
	/**
	 * The flag to mark that this view does not have associate model
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * ID of plans which are having renew options
	 *
	 * @var array
	 */
	protected $planIds;

	/**
	 * The avalable renew options
	 *
	 * @var array
	 */
	protected $renewOptions;

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
		$this->requestLogin('OSM_LOGIN_TO_RENEW_MEMBERSHIP');

		$app    = Factory::getApplication();
		$user   = $app->getIdentity();
		$config = OSMembershipHelper::getConfig();
		$item   = OSMembershipHelperSubscription::getMembershipProfile($user->id);

		if (!$item)
		{
			// Try to fix the profile id field
			if (OSMembershipHelperSubscription::fixProfileId($user->id))
			{
				$app->redirect(Uri::getInstance()->toString());
			}
			else
			{
				$app->enqueueMessage(Text::_('OSM_DONOT_HAVE_SUBSCRIPTION_RECORD_TO_RENEW'));

				return;
			}
		}

		if ($item->id != $item->profile_id)
		{
			/* @var DatabaseDriver $db */
			$db               = Factory::getContainer()->get('db');
			$query            = $db->getQuery(true);
			$item->profile_id = $item->id;
			$query->clear()
				->update('#__osmembership_subscribers')
				->set('profile_id = ' . $item->id)
				->where('id = ' . $item->id);
			$db->setQuery($query);
			$db->execute();
		}

		if ($item->group_admin_id > 0)
		{
			if (!$this->input->getInt('hmvc_call'))
			{
				$app->enqueueMessage(Text::_('OSM_ONLY_GROUP_ADMIN_CAN_RENEW_MEMBERSHIP'));
			}

			return;
		}

		[$planIds, $renewOptions] = OSMembershipHelper::callOverridableHelperMethod(
			'Subscription',
			'getRenewOptions',
			[$user->id]
		);

		if (empty($planIds))
		{
			if (!$this->input->getInt('hmvc_call'))
			{
				$this->setLayout('empty');

				parent::display();
			}

			return;
		}

		// Need to get subscriptions information of the user
		$this->planIds         = $planIds;
		$this->renewOptions    = $renewOptions;
		$this->plans           = OSMembershipHelperDatabase::getAllPlans('id');
		$this->config          = $config;
		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();

		$this->setLayout('default');

		parent::display();
	}
}
