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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

class plgOSMembershipScript extends CMSPlugin implements SubscriberInterface
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
			'onAfterStoreSubscription'    => 'onAfterStoreSubscription',
			'onMembershipActive'          => 'onMembershipActive',
			'onMembershipExpire'          => 'onMembershipExpire',
			'onSubscriptionAfterSave'     => 'onSubscriptionAfterSave',
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
			'title' => Text::_('PLG_OSMEMBERSHIP_SCRIPTS'),
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
		$params->set('subscription_store_script', $data['subscription_store_script']);
		$params->set('subscription_active_script', $data['subscription_active_script']);
		$params->set('subscription_expired_script', $data['subscription_expired_script']);
		$params->set('subscription_update_script', $data['subscription_update_script']);
		$row->params = $params->toString();

		$row->store();
	}

	/**
	 * Run the PHP script when subscription is stored in database
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterStoreSubscription(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$params = $this->getPlanParams($row->plan_id);
		$script = trim($params->get('subscription_store_script', ''));

		if ($script)
		{
			try
			{
				eval($script);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage(Text::_('The PHP script is wrong. Please contact Administrator'), 'error');
			}
		}
	}

	/**
	 * Run the PHP script when membership is activated
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipActive(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$params = $this->getPlanParams($row->plan_id);
		$script = trim($params->get('subscription_active_script', ''));

		if ($script)
		{
			try
			{
				eval($script);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage(Text::_('The PHP script is wrong. Please contact Administrator'), 'error');
			}
		}
	}

	/**
	 * Run the PHP script when membership expired
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipExpire(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$params = $this->getPlanParams($row->plan_id);
		$script = trim($params->get('subscription_expired_script', ''));

		if ($script)
		{
			try
			{
				eval($script);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage(Text::_('The PHP script is wrong. Please contact Administrator'), 'error');
			}
		}
	}

	/**
	 * Run the PHP script when membership expired
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onSubscriptionAfterSave(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$context, $row, $data, $isNew] = array_values($event->getArguments());

		$params = $this->getPlanParams($row->plan_id);
		$script = trim($params->get('subscription_update_script', ''));

		if ($script)
		{
			try
			{
				eval($script);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage(Text::_('The PHP script is wrong. Please contact Administrator'), 'error');
			}
		}
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
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'form');
	}

	/**
	 * The params of the subscription plan
	 *
	 * @param $planId
	 *
	 * @return Registry
	 */
	private function getPlanParams($planId)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select('params')
			->from('#__osmembership_plans')
			->where('id = ' . $planId);
		$db->setQuery($query);

		return new Registry($db->loadResult());
	}
}
