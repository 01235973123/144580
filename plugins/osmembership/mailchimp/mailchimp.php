<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use DrewM\MailChimp\MailChimp;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

class plgOSMembershipMailchimp extends CMSPlugin implements SubscriberInterface
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

		if (!class_exists('DrewM\\MailChimp\\MailChimp'))
		{
			require_once dirname(__FILE__) . '/api/MailChimp.php';
		}

		ob_start();
		$this->drawSettingForm($row);
		$form = ob_get_contents();
		ob_end_clean();

		$result = [
			'title' => Text::_('PLG_OSMEMBERSHIP_MAILCHIMP_SETTINGS'),
			'form'  => $form,
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

		$params = new Registry($row->params);

		foreach (['mailchimp_list_ids', 'remove_mailchimp_list_ids', 'mailchimp_group_ids', 'remove_mailchimp_group_ids'] as $key)
		{
			$params->set($key, implode(',', $data[$key] ?? []));
		}

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

		if (!class_exists('DrewM\\MailChimp\\MailChimp'))
		{
			require_once dirname(__FILE__) . '/api/MailChimp.php';
		}

		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		if ($row->group_admin_id > 0 && $this->params->get('subscribe_group_members', '1') == '0')
		{
			return;
		}

		$config = OSMembershipHelper::getConfig();

		// In case subscriber doesn't want to subscribe to newsleter, stop
		if ($config->show_subscribe_newsletter_checkbox && empty($row->subscribe_newsletter))
		{
			return;
		}

		$plan = new OSMembershipTablePlan($this->db);
		$plan->load($row->plan_id);
		$params       = new Registry($plan->params);
		$listIds      = array_filter(explode(',', $params->get('mailchimp_list_ids', '')));
		$groupIds     = array_filter(explode(',', $params->get('mailchimp_group_ids', '')));
		$listGroupMap = [];

		foreach ($groupIds as $groupId)
		{
			[$groupListId, $id] = explode('-', $groupId);
			$listGroupMap[$groupListId][] = $id;
		}

		if (empty($listIds))
		{
			return;
		}

		try
		{
			$mailchimp = new MailChimp($this->params->get('api_key', ''));
		}
		catch (Exception $e)
		{
			return;
		}

		if ($this->params->get('double_optin'))
		{
			$status = 'pending';
		}
		else
		{
			$status = 'subscribed';
		}

		foreach ($listIds as $listId)
		{
			$data = [
				'skip_merge_validation' => true,
				'id'                    => $listId,
				'email_address'         => $row->email,
				'merge_fields'          => [],
				'status'                => $status,
				'update_existing'       => true,
			];

			if ($row->first_name)
			{
				$data['merge_fields']['FNAME'] = $row->first_name;
			}

			if ($row->last_name)
			{
				$data['merge_fields']['LNAME'] = $row->last_name;
			}

			if ($row->address && $row->address2 && $row->city && $row->state && $row->zip)
			{
				$data['merge_fields']['ADDRESS'] = [
					'addr1'   => $row->address,
					'addr2'   => $row->address2,
					'city'    => $row->city,
					'state'   => $row->state,
					'zip'     => $row->zip,
					'country' => $row->country ?: $config->get('default_country'),
				];
			}

			if ($row->phone)
			{
				$data['merge_fields']['PHONE'] = $row->phone;
			}

			if (!empty($listGroupMap[$listId]))
			{
				$data['interests'] = [];

				foreach ($listGroupMap[$listId] as $interestId)
				{
					$data['interests'][$interestId] = true;
				}
			}

			$result = $mailchimp->post("lists/$listId/members", $data);

			if ($result === false)
			{
				$this->logError($data, $mailchimp->getLastError());
			}
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

		if (!class_exists('DrewM\\MailChimp\\MailChimp'))
		{
			require_once dirname(__FILE__) . '/api/MailChimp.php';
		}

		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		if ($row->group_admin_id > 0 && $this->params->get('subscribe_group_members', '1') == '0')
		{
			return;
		}

		$plan = new OSMembershipTablePlan($this->db);
		$plan->load($row->plan_id);
		$params = new Registry($plan->params);

		$listIds  = array_filter(explode(',', $params->get('remove_mailchimp_list_ids', '')));
		$groupIds = array_filter(explode(',', $params->get('remove_mailchimp_group_ids', '')));

		if (empty($listIds) && empty($groupIds))
		{
			return;
		}

		$activePlans = OSMembershipHelperSubscription::getActiveMembershipPlans($row->user_id, [$row->id]);

		// Subscription was already renewed, no need to process further
		if (in_array($row->plan_id, $activePlans))
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('params')
			->from('#__osmembership_plans')
			->where('id IN  (' . implode(',', $activePlans) . ')');
		$db->setQuery($query);
		$rowPlans = $db->loadObjectList();

		foreach ($rowPlans as $rowPlan)
		{
			$planParams   = new Registry($rowPlan->params);
			$planListIds  = array_filter(explode(',', $planParams->get('mailchimp_list_ids')));
			$planGroupIds = array_filter(explode(',', $planParams->get('mailchimp_group_ids')));
			$listIds      = array_diff($listIds, $planListIds);
			$groupIds     = array_diff($groupIds, $planGroupIds);
		}

		if (empty($listIds) && empty($groupIds))
		{
			return;
		}

		try
		{
			$mailchimp = new MailChimp($this->params->get('api_key', ''));
		}
		catch (Exception $e)
		{
			return;
		}

		$hash = $mailchimp->subscriberHash($row->email);

		foreach ($listIds as $listId)
		{
			$result = $mailchimp->delete("lists/$listId/members/$hash");

			if ($result === false)
			{
				$this->logError(['listId' => $listId, 'email' => $row->email], $mailchimp->getLastError());
			}
		}

		if (count($groupIds))
		{
			$listGroupMap = [];

			foreach ($groupIds as $groupId)
			{
				[$groupListId, $id] = explode('-', $groupId);
				$listGroupMap[$groupListId][] = $id;
			}

			foreach ($listGroupMap as $listId => $groups)
			{
				$data = ['email_address' => $row->email];

				foreach ($groups as $group)
				{
					$data['interests'][$group] = false;
				}

				$result = $mailchimp->patch("lists/$listId/members/$hash?skip_merge_validation=true", $data);

				if ($result === false)
				{
					$this->logError($data, $mailchimp->getLastError());
				}
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
		try
		{
			$mailchimp = new MailChimp($this->params->get('api_key', ''));
		}
		catch (Exception $e)
		{
			return;
		}

		$lists = $mailchimp->get('lists', ['count' => 1000]);

		if ($lists === false)
		{
			$this->app->enqueueMessage('No Mailing Lists Found', 'warning');

			return;
		}

		$options    = [];
		$allListIds = [];

		foreach ($lists['lists'] as $list)
		{
			$options[]    = HTMLHelper::_('select.option', $list['id'], $list['name']);
			$allListIds[] = $list['id'];
		}

		$groupOptions = [];

		foreach ($allListIds as $listId)
		{
			$interestCategoriesResponse = $mailchimp->get('lists/' . $listId . '/interest-categories', ['count' => 1000]);

			foreach ($interestCategoriesResponse['categories'] as $category)
			{
				$interestsResponse = $mailchimp->get(
					'lists/' . $listId . '/interest-categories/' . $category['id'] . '/interests',
					['count' => 1000]
				);

				foreach ($interestsResponse['interests'] as $interest)
				{
					$groupOptions[] = HTMLHelper::_('select.option', $listId . '-' . $interest['id'], $category['title'] . '-' . $interest['name']);
				}
			}
		}

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'form');
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
	 * Log the error from API call
	 *
	 * @param   array   $data
	 * @param   string  $error
	 */
	protected function logError($data, $error)
	{
		$text = '[' . date('m/d/Y g:i A') . '] - ';

		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $keyValue => $valueValue)
				{
					$text .= "$keyValue=$valueValue, ";
				}
			}
			else
			{
				$text .= "$key=$value, ";
			}
		}

		$text .= $error;

		$ipnLogFile = JPATH_ROOT . '/components/com_osmemership/mailchimp_api_errors.txt';
		$fp         = fopen($ipnLogFile, 'a');
		fwrite($fp, $text . "\n\n");
		fclose($fp);
	}
}
