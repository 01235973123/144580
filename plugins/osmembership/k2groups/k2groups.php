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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

class plgOSMembershipK2groups extends CMSPlugin implements SubscriberInterface
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

	/**
	 * Make language files will be loaded automatically.
	 *
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	public static function getSubscribedEvents(): array
	{
		return [
			'onEditSubscriptionPlan'      => 'onEditSubscriptionPlan',
			'onAfterSaveSubscriptionPlan' => 'onAfterSaveSubscriptionPlan',
			'onMembershipActive'          => 'onMembershipActive',
			'onMembershipExpire'          => 'onMembershipExpire',
		];
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
			'title' => Text::_('PLG_OSMEMBERSHIP_K2_GROUPS_SETTINGS'),
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

		$params = new Registry($row->params);
		$params->set('k2_group_id', $data['k2_group_id']);
		$params->set('k2_expired_group_id', $data['k2_expired_group_id']);
		$row->params = $params->toString();
		$row->store();
	}

	/**
	 * Run when a membership activated
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipActive(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!$row->user_id)
		{
			return;
		}

		$params = $this->getPlanParams($row->plan_id);

		if ($k2GroupId = (int) $params->get('k2_group_id', '0'))
		{
			$this->assignUserToK2Group($row->user_id, $k2GroupId);
		}
	}

	/**
	 * Run when a membership expired
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipExpire(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!$row->user_id)
		{
			return;
		}

		$activePlans = OSMembershipHelperSubscription::getActiveMembershipPlans($row->user_id, [$row->id]);

		// Users has renewed their subscription before, don't process further
		if (in_array($row->plan_id, $activePlans))
		{
			return;
		}

		$params = $this->getPlanParams($row->plan_id);

		if ($k2ExpiredGroupId = (int) $params->get('k2_expired_group_id', '0'))
		{
			$this->assignUserToK2Group($row->user_id, $k2ExpiredGroupId);
		}
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_k2'))
		{
			return;
		}

		parent::registerListeners();
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

	/**
	 * Display form allows users to change setting for this subscription plan
	 *
	 * @param   OSMembershipTablePlan  $row
	 */
	private function drawSettingForm($row)
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('id AS value, name AS text')->from('#__k2_user_groups');
		$db->setQuery($query);
		$options = $db->loadObjectList();
		array_unshift($options, HTMLHelper::_('select.option', '', Text::_('PLG_OSMEMBERSHIP_SELECT_K2_GROUP')));

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'form');
	}

	/**
	 * Assign a user to selected K2 Group
	 *
	 * @param $userId
	 * @param $k2GroupId
	 */
	private function assignUserToK2Group($userId, $k2GroupId)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__k2_users')
			->where('userID =' . $userId);
		$db->setQuery($query);
		$k2UserId = $db->loadResult();

		if ($k2UserId)
		{
			$query->clear()->update('#__k2_users')->set('`group`=' . $k2GroupId)->where('id =' . $k2UserId);
		}
		else
		{
			$query->clear()->insert('#__k2_users')->set('`group`=' . $k2GroupId)->set('`userID`=' . $userId);
		}

		$db->setQuery($query)
			->execute();
	}

	/**
	 * Method to get the plan params
	 *
	 * @param   int  $planId
	 *
	 * @return Registry
	 */
	private function getPlanParams($planId)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select('`params`')
			->from('#__osmembership_plans')
			->where('id = ' . (int) $planId);
		$db->setQuery($query);

		return new Registry($db->loadResult());
	}
}
