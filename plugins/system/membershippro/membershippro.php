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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\Database\ParameterType;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipExpire;

class plgSystemMembershipPro extends CMSPlugin implements SubscriberInterface
{
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
	 * Flag to see whether the plan subscription status for this record has been processed or not
	 *
	 * @var bool
	 */
	private $subscriptionProcessed = false;

	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterStoreSubscription'  => 'onAfterStoreSubscription',
			'onMembershipActive'        => 'onMembershipActive',
			'onMembershipExpire'        => 'onMembershipExpire',
			'onMembershipUpdate'        => 'onMembershipUpdate',
			'onSubscriptionAfterDelete' => 'onSubscriptionAfterDelete',
			'onUserAfterDelete'         => 'onUserAfterDelete',
			'onUserAfterLogin'          => 'onUserAfterLogin',
			'onUserAfterSave'           => 'onUserAfterSave',
		];
	}

	/**
	 * This method is run after subscription record is successfully stored in database
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterStoreSubscription(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		// Set profile data (is_profile, profile_id) for the subscription
		$this->setSubscriptionProfileData($row);

		// Set plan main record data for the subscription
		$this->setPlanMainRecordData($row);

		// Set avatar for subscription
		$this->setAvatarForSubscription($row);

		$row->payment_method = (string) $row->payment_method;

		if (str_contains($row->payment_method, 'os_offline'))
		{
			$config = OSMembershipHelper::getConfig();

			// Generate invoice for offline payment
			if ($config->activate_invoice_feature && !$row->group_admin_id && !$row->invoice_number)
			{
				$this->generateInvoiceNumber($row);
			}

			// Generate Membership ID for offline payment subscription
			if ($config->auto_generate_membership_id)
			{
				$this->generateMembershipId($row);
			}

			// Update coupon usage statistic
			$this->updateCouponUsage($row);
		}

		// Move data of the fields which are not being shown on renewal form to renewal subscription
		if ($row->act == 'renew' && $row->user_id > 0)
		{
			$this->moveHideOnMembershipRenewalData($row);
		}

		// Store the modified data for the subscription back to database
		$row->store();
	}

	/**
	 * This method is run after subscription become active, ie after user complete payment or admin approve the subscription
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function onMembershipActive(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$config = OSMembershipHelper::getConfig();

		// Create user account (in case the system is configured to generate user account when subscription is active)
		if (!$row->user_id && $row->username && $row->user_password)
		{
			$this->createUserAccount($row);
		}

		// Activate user account when subscription active (in case the system is configured to not send activation email)
		if (!$config->send_activation_email)
		{
			$this->activateUserAccount($row);
		}

		/*
		 * Generate invoice for the subscription if it was not generated before (For example, when admin approve the
		 * offline payment subscription
		 */
		if ($config->activate_invoice_feature && !$row->group_admin_id && !$row->invoice_number)
		{
			$this->generateInvoiceNumber($row);
		}

		// In case system is configured to only has one subscription record for each plan, update the subscription
		if ($row->act == 'renew' && $config->subscription_renew_behavior == 'update_subscription' && $row->user_id > 0)
		{
			$this->updateSubscriptionOnRenew($row);
		}

		// Update coupon usage statistic
		$this->updateCouponUsage($row);

		/*
		 * Generate Membership ID for the subscription if it was not generated before (For example, when admin approve
		 * the offline payment subscription
		 */
		if ($config->auto_generate_membership_id && !$row->membership_id)
		{
			$this->generateMembershipId($row);
		}

		// Disable reminder for appropriate records
		if ($row->user_id > 0)
		{
			$groupingPlans = OSMembershipHelperSubscription::getGroupingPlans($row);

			if (empty($groupingPlans))
			{
				$groupingPlans[] = $row->plan_id;
			}

			$db    = $this->db;
			$query = $db->getQuery(true)
				->update('#__osmembership_subscribers')
				->set('first_reminder_sent = 1')
				->set('second_reminder_sent = 1')
				->set('third_reminder_sent = 1')
				->where('user_id = ' . $row->user_id)
				->whereIn('plan_id', $groupingPlans)
				->where('id != ' . $row->id);


			$extraReminderSentFields = [
				'fourth_reminder_sent',
				'fifth_reminder_sent',
				'sixth_reminder_sent',
			];

			foreach ($extraReminderSentFields as $field)
			{
				if (property_exists($row, $field))
				{
					$query->set($field . ' = 1');
				}
			}

			$db->setQuery($query)
				->execute();
		}

		// Store modified subscription data back to database
		$row->store();

		$this->subscriptionProcessed = true;

		if ($row->group_admin_id == 0)
		{
			$this->updateSubscriptionExpiredDate($row);

			$this->updatePlanSubscriptionStatus($row);

			$this->updateSendingReminderStatus($row);
		}
	}

	/**
	 * Block the user account when membership is expired
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipExpire(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if ($row->user_id && $this->params->get('block_account_when_expired', 0))
		{
			$user = Factory::getUser((int) $row->user_id);

			// Only block account if the subscriber does not have any active subscription left
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('id != ' . $row->id)
				->where('user_id = ' . $row->user_id)
				->where('published = 1');
			$db->setQuery($query);

			if (!$db->loadResult() && !$user->authorise('core.admin'))
			{
				$user->block = 1;
				$user->save(true);
			}
		}

		$this->subscriptionProcessed = true;

		if (!$row->group_admin_id)
		{
			$this->updatePlanSubscriptionStatus($row);
		}
	}

	/**
	 * Recalculate some important subscription information when a subscription record is being deleted
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onSubscriptionAfterDelete(Event $event): void
	{
		/**
		 * @var string                      $context
		 * @var OSMembershipTableSubscriber $row
		 */
		[$context, $row] = array_values($event->getArguments());

		if ($row->profile_id > 0 && $row->plan_id > 0)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('id, profile_id, plan_id, published, from_date, to_date, plan_main_record')
				->from('#__osmembership_subscribers')
				->where('plan_id = ' . $row->plan_id)
				->where('profile_id = ' . $row->profile_id)
				->where('(published >= 1 OR payment_method LIKE "os_offline%")')
				->order('id');
			$db->setQuery($query);
			$subscriptions = $db->loadObjectList();

			if (!empty($subscriptions))
			{
				$isActive         = false;
				$isPending        = false;
				$isExpired        = false;
				$lastActiveDate   = null;
				$lastExpiredDate  = null;
				$planMainRecordId = 0;
				$planFromDate     = $subscriptions[0]->from_date;

				foreach ($subscriptions as $subscription)
				{
					if ($subscription->plan_main_record)
					{
						$planMainRecordId = $subscription->id;
					}

					if ($subscription->published == 1)
					{
						$isActive       = true;
						$lastActiveDate = $subscription->to_date;
					}
					elseif ($subscription->published == 0)
					{
						$isPending = true;
					}
					elseif ($subscription->published == 2)
					{
						$isExpired       = true;
						$lastExpiredDate = $subscription->to_date;
					}
				}

				if ($isActive)
				{
					$published  = 1;
					$planToDate = $lastActiveDate;
				}
				elseif ($isPending)
				{
					$published = 0;
				}
				elseif ($isExpired)
				{
					$published  = 2;
					$planToDate = $lastExpiredDate;
				}
				else
				{
					$published  = 3;
					$planToDate = $subscriptions[count($subscriptions) - 1]->to_date;
				}

				$query->clear()
					->update('#__osmembership_subscribers')
					->set('plan_subscription_status = ' . (int) $published)
					->set('plan_subscription_from_date = ' . $db->quote($planFromDate))
					->set('plan_subscription_to_date = ' . $db->quote($planToDate))
					->where('plan_id = ' . $row->plan_id)
					->where('profile_id = ' . $row->profile_id);
				$db->setQuery($query);
				$db->execute();

				if (empty($planMainRecordId))
				{
					$planMainRecordId = $subscriptions[0]->id;
					$query->clear()
						->update('#__osmembership_subscribers')
						->set('plan_main_record = 1')
						->where('id = ' . $planMainRecordId);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		if ($row->is_profile == 1 && $row->user_id > 0)
		{
			// We need to fix the profile record
			OSMembershipHelperSubscription::fixProfileId($row->user_id);
		}
	}

	/**
	 * Update plan subscription status when subscription record updated
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipUpdate(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!$this->subscriptionProcessed && Factory::getApplication()->isClient('administrator'))
		{
			$this->updateSubscriptionExpiredDate($row);

			$this->updatePlanSubscriptionStatus($row);
		}
	}

	/**
	 * Handle Login redirect
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function onUserAfterLogin(Event $event): void
	{
		[$options] = array_values($event->getArguments());

		$app = $this->app;

		if ($app->isClient('administrator'))
		{
			return;
		}

		$session                = $app->getSession();
		$sessionReturnUrl       = $session->get('osm_return_url');
		$sessionRequiredPlanIds = $session->get('required_plan_ids');

		if (!empty($sessionReturnUrl) && !empty($sessionRequiredPlanIds))
		{
			$activePlans = OSMembershipHelperSubscription::getActiveMembershipPlans();

			if (count(array_intersect($activePlans, $sessionRequiredPlanIds)) > 0)
			{
				// Clear the old session data
				$session->clear('osm_return_url');
				$session->clear('required_plan_ids');

				$app->setUserState('users.login.form.return', $sessionReturnUrl);

				return;
			}
		}

		if (!$app->getInput()->post->getInt(
				'login_from_mp_subscription_form'
			) && $loginRedirectUrl = OSMembershipHelper::getLoginRedirectUrl())
		{
			$app->setUserState('users.login.form.return', $loginRedirectUrl);
		}
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method creates a subscription record for the saved user
	 *
	 * @param   Event  $event
	 *
	 * @return  void
	 */
	public function onUserAfterSave(Event $event)
	{
		[$user, $isnew, $success, $msg] = array_values($event->getArguments());

		// If the user wasn't stored we don't resync
		if (!$success)
		{
			return;
		}

		// If the user isn't new we don't sync
		if ($isnew)
		{
			return;
		}

		// Ensure the user id is really an int
		$userId = (int) $user['id'];

		// If the user id appears invalid then bail out just in case
		if (empty($userId))
		{
			return;
		}

		$config = OSMembershipHelper::getConfig();
		$app    = Factory::getApplication();

		$option = $app->getInput()->getCmd('option');
		$task   = $app->getInput()->getCmd('task');

		if (!empty($config->synchronize_email) && in_array($option, ['com_users', 'com_comprofiler']))
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->update('#__osmembership_subscribers')
				->set('email = ' . $db->quote($user['email']))
				->where('user_id = ' . $userId);
			$db->setQuery($query);
			$db->execute();
		}

		// Assign subscription records to user account in case they have existing subscription records before registering for new user account
		if (in_array($option, ['com_users', 'com_comprofiler']))
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->update('#__osmembership_subscribers')
				->set('user_id = ' . $userId)
				->where('user_id = 0')
				->where('email = ' . $db->quote($user['email']));
			
			$db->setQuery($query);
			$db->execute();
		}

		// Only synchronize data to subscriptions if it's enabled
		if (!$config->synchronize_profile_data_to_subscriptions)
		{
			return;
		}

		// Only update data if data is updated via com_users
		if ($option != 'com_users')
		{
			return;
		}

		if ($app->isClient('administrator') && !in_array($task, ['save', 'apply', 'save2new']))
		{
			return;
		}

		if ($app->isClient('site') && $task != 'save')
		{
			return;
		}

		$mpUserProfilePluginEnabled = PluginHelper::isEnabled('osmembership', 'userprofile');

		if (!$mpUserProfilePluginEnabled)
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('id')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId);
		$db->setQuery($query);
		$subscriptionIds = $db->loadColumn();

		if (!count($subscriptionIds))
		{
			return;
		}

		$userProfilePluginEnabled = PluginHelper::isEnabled('user', 'profile');
		$userFields               = OSMembershipHelper::getUserFields();

		// Update user's profile data to subscription records of the user
		if ($userProfilePluginEnabled)
		{
			$this->updateSubscriptionsFromUserProfile($user, $subscriptionIds);
		}

		// Update user's custom fields data to subscription records of the suer
		if (count($userFields))
		{
			$this->updateSubscriptionsFromUserCustomFields($user, $subscriptionIds, $userFields);
		}
	}

	/**
	 * Remove all subscriptions for the user if configured
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   Event  $event
	 *
	 * @return  void
	 */
	public function onUserAfterDelete(Event $event)
	{
		/**
		 * @var array  $user
		 * @var bool   $success
		 * @var string $msg
		 */
		[$user, $success, $msg] = array_values($event->getArguments());

		$config = OSMembershipHelper::getConfig();

		if ($config->delete_subscriptions_when_account_deleted)
		{
			$db  = $this->db;
			$row = new OSMembershipTableSubscriber($db);

			$query = $db->getQuery(true)
				->select('id')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . (int) $user['id']);
			$db->setQuery($query);
			$cid = $db->loadColumn();

			if (count($cid))
			{
				$query->clear()
					->delete('#__osmembership_field_value')
					->whereIn('subscriber_id', $cid);
				$db->setQuery($query);
				$db->execute();

				PluginHelper::importPlugin('osmembership');
				$app = $this->app;

				foreach ($cid as $id)
				{
					$row->load($id);

					$event = new MembershipExpire(['row' => $row]);

					$app->triggerEvent($event->getName(), $event);
				}

				$query->clear()
					->delete('#__osmembership_subscribers')
					->where('user_id = ' . (int) $user['id']);
				$db->setQuery($query);
				$db->execute();
			}
		}
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

		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		parent::registerListeners();
	}

	/**
	 * Move data for custom fields which is hide on membership renewal from original subscription to renewal subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	protected function moveHideOnMembershipRenewalData($row)
	{
		$db        = $this->db;
		$negPlanId = -1 * $row->plan_id;

		$user = Factory::getUser((int) $row->user_id);

		// Prevent fatal error in case the user_id is invalid
		if ($row->user_id > 0 && $user->id == null)
		{
			$user = Factory::getUser(0);
		}

		$viewLevels = $user->getAuthorisedViewLevels();

		// Get the previous subscription
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $row->user_id)
			->where('id < ' . $row->id)
			->where('(published >= 1 OR (published = 0 AND payment_method LIKE "os_offline%"))')
			->order('id DESC');
		$db->setQuery($query);
		$rowSubscriber = $db->loadObject();

		if (!$rowSubscriber)
		{
			return;
		}

		$query->clear()
			->select($db->quoteName(['id', 'name', 'is_core', 'access', 'hide_on_membership_renewal']))
			->from('#__osmembership_fields')
			->where('published = 1')
			->where(
				'(plan_id = 0 OR id IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $row->plan_id . ' OR plan_id < 0))'
			)
			->where('id NOT IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $negPlanId . ')');
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		// Get fields which already inserted
		$query->clear()
			->select('field_id')
			->from('#__osmembership_field_value')
			->where('subscriber_id = ' . $row->id);
		$db->setQuery($query);
		$insertedFieldIds = $db->loadColumn();

		$updateRowRecord = false;
		$moveFieldIds    = [];

		foreach ($rowFields as $rowField)
		{
			// The field is show on renewal form, no need to process it further
			if (!$rowField->hide_on_membership_renewal && (in_array(
						$rowField->access,
						$viewLevels
					) || $this->app->isClient('administrator')))
			{
				continue;
			}

			if ($rowField->is_core)
			{
				$fieldName = $rowField->name;

				if (!$row->{$fieldName} && $rowSubscriber->{$fieldName})
				{
					$row->{$fieldName} = $rowSubscriber->{$fieldName};
				}

				$updateRowRecord = true;
			}
			elseif (!in_array($rowField->id, $insertedFieldIds))
			{
				$moveFieldIds[] = $rowField->id;
			}
		}

		if ($updateRowRecord)
		{
			$row->store();
		}

		if (count($moveFieldIds) > 0)
		{
			$sql = 'INSERT INTO #__osmembership_field_value (subscriber_id, field_id, field_value)'
				. " SELECT $row->id, field_id, field_value FROM #__osmembership_field_value WHERE subscriber_id = $rowSubscriber->id AND field_id IN (" . implode(
					',',
					$moveFieldIds
				) . ')';
			$db->setQuery($sql)
				->execute();
		}
	}

	/**
	 * Method to set profile data (is_profile, profile_id) for the subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	protected function setSubscriptionProfileData($row)
	{
		$row->is_profile = 1;

		if ($row->user_id > 0)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $row->user_id)
				->where('(published >= 1 OR payment_method LIKE "os_offline%")')
				->where('is_profile = 1');
			$db->setQuery($query);
			$profileId = $db->loadResult();

			if ($profileId && $profileId != $row->id)
			{
				$row->is_profile = 0;
				$row->profile_id = $profileId;
			}
		}

		if ($row->is_profile == 1)
		{
			$row->profile_id = $row->id;
		}
	}

	/**
	 * Method to set plan main record data (plan_main_record, plan_subscription_from_date) for the subscription.
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	protected function setPlanMainRecordData($row)
	{
		$row->plan_main_record = 1;

		if ($row->user_id > 0)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->select('plan_subscription_from_date')
				->from('#__osmembership_subscribers')
				->where('plan_main_record = 1')
				->where('user_id = ' . $row->user_id)
				->where('plan_id = ' . $row->plan_id)
				->where('id != ' . $row->id);
			$db->setQuery($query);

			if ($planMainRecord = $db->loadObject())
			{
				$row->plan_main_record            = 0;
				$row->plan_subscription_from_date = $planMainRecord->plan_subscription_from_date;
			}
		}

		if ($row->plan_main_record == 1)
		{
			$row->plan_subscription_status    = $row->published;
			$row->plan_subscription_from_date = $row->from_date;
			$row->plan_subscription_to_date   = $row->to_date;
		}
	}

	/**
	 * Method to generate invoice number for the subscription record
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	protected function generateInvoiceNumber($row)
	{
		if (OSMembershipHelper::callOverridableHelperMethod('Helper', 'needToCreateInvoice', [$row]))
		{
			$row->invoice_number = OSMembershipHelper::getInvoiceNumber($row);

			if (property_exists($row, 'formatted_invoice_number'))
			{
				$config                        = OSMembershipHelper::getConfig();
				$row->formatted_invoice_number = OSMembershipHelper::formatInvoiceNumber($row, $config);
			}
		}
	}

	/**
	 * Create user account for subscriber after subscription being active
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @throws Exception
	 */
	protected function createUserAccount($row)
	{
		$data['username']   = $row->username;
		$data['first_name'] = $row->first_name;
		$data['last_name']  = $row->last_name;
		$data['email']      = $row->email;

		//Password
		$data['password1'] = OSMembershipHelperCryptor::decrypt($row->user_password);

		try
		{
			$row->user_id = (int) OSMembershipHelper::saveRegistration($data);

			if ($row->user_id > 0 && $row->language && $row->language !== '*')
			{
				$user = User::getInstance($row->user_id);
				$user->setParam('language', $row->language);
				$user->save();
			}

			$config = OSMembershipHelper::getConfig();

			if (PluginHelper::isEnabled('system', 'privacyconsent') && $config->show_privacy_policy_checkbox)
			{
				OSMembershipHelperSubscription::acceptPrivacyConsent($row);
			}
		}
		catch (Exception $e)
		{
			OSMembershipHelper::logData(__DIR__ . '/create_user_error.txt', $data, $e->getMessage());
		}
	}

	/**
	 * Active user account automatically after subscription active
	 *
	 * @param $row
	 */
	protected function activateUserAccount($row)
	{
		if (ComponentHelper::getParams('com_users')->get('useractivation') != 2)
		{
			$user = Factory::getUser((int) $row->user_id);

			if ($user->block)
			{
				$user->block      = 0;
				$user->activation = '';
				$user->save(true);
			}
		}
	}

	/**
	 * Generate Membership ID for a subscription record
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	protected function generateMembershipId($row)
	{
		if ($row->user_id)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->select('MAX(membership_id)')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $row->user_id);
			$db->setQuery($query);
			$row->membership_id = (int) $db->loadResult();
		}

		if (!$row->membership_id)
		{
			$row->membership_id = OSMembershipHelper::callOverridableHelperMethod('Helper', 'getMembershipId', [$row]);
		}

		if ($row->membership_id > 0 && property_exists($row, 'formatted_membership_id'))
		{
			$config                       = OSMembershipHelper::getConfig();
			$row->formatted_membership_id = OSMembershipHelper::formatMembershipId($row, $config);
		}
	}

	/**
	 * Calculate and store subscription expired date of the user for the plan he just processed subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	protected function updateSubscriptionExpiredDate($row)
	{
		if (!$row->plan_id)
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select('MAX(to_date)')
			->from('#__osmembership_subscribers')
			->where('published = 1')
			->where('profile_id = ' . $row->profile_id)
			->where('plan_id = ' . $row->plan_id);
		$db->setQuery($query);
		$subscriptionExpiredDate = $db->loadResult();

		if ($subscriptionExpiredDate)
		{
			$query->clear()
				->update('#__osmembership_subscribers')
				->set('plan_subscription_to_date = ' . $db->quote($subscriptionExpiredDate))
				->where('profile_id = ' . $row->profile_id)
				->where('plan_id = ' . $row->plan_id);
			$db->setQuery($query)
				->execute();

			// Store plan_subscription_to_date back to $row object so that it can be used on other process
			$row->plan_subscription_to_date = $subscriptionExpiredDate;
		}
	}

	/**
	 * Update status of the plan for the user when subscription status change
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	protected function updatePlanSubscriptionStatus($row)
	{
		if (!$row->plan_id)
		{
			return;
		}

		$subscriptionStatus = OSMembershipHelperSubscription::getPlanSubscriptionStatusForUser(
			$row->profile_id,
			$row->plan_id
		);
		$db                 = $this->db;
		$query              = $db->getQuery(true);
		$query->update('#__osmembership_subscribers')
			->set('plan_subscription_status = ' . $subscriptionStatus)
			->where('profile_id = ' . $row->profile_id)
			->where('plan_id = ' . $row->plan_id);
		$db->setQuery($query);
		$db->execute();

		// Store plan_subscription_status for this record to avoid it's changed by other plugin later
		$row->plan_subscription_status = $subscriptionStatus;
	}

	/**
	 * Clear subscription expired reminder status
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	protected function updateSendingReminderStatus($row)
	{
		if (!$row->plan_id || !$row->user_id)
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);
		$now   = $db->quote(Factory::getDate()->toSql());
		$query->update('#__osmembership_subscribers')
			->set('first_reminder_sent = 1')
			->set('second_reminder_sent = 1')
			->set('third_reminder_sent = 1')
			->set('first_reminder_sent_at = ' . $now)
			->set('second_reminder_sent_at = ' . $now)
			->set('third_reminder_sent_at = ' . $now)
			->where('user_id = ' . $row->user_id)
			->where('plan_id = ' . $row->plan_id)
			->where('id != ' . $row->id);


		$extraReminderSentFields = [
			'fourth_reminder_sent',
			'fifth_reminder_sent',
			'sixth_reminder_sent',
		];

		foreach ($extraReminderSentFields as $extraField)
		{
			if (property_exists($row, $extraField))
			{
				$query->set($extraField . ' = 1')
					->set($extraField . '_at = ' . $now);
			}
		}

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Update subscription duration when membership is renewed.
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	protected function updateSubscriptionOnRenew($row)
	{
		if (!$row->plan_id)
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		// Find the first subscription record of the user of this plan
		$query->select('*')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $row->user_id)
			->where('plan_id = ' . $row->plan_id)
			->where('published IN (1, 2)')
			->order('id');
		$db->setQuery($query, 0, 1);
		$rowSubscriber = $db->loadObject();

		if (!$rowSubscriber)
		{
			return;
		}

		// Get subscription_id from the new subscription and set it for new subscription
		if (!$row->subscription_id)
		{
			if ($rowSubscriber->subscription_id)
			{
				$row->subscription_id = $rowSubscriber->subscription_id;
			}
			else
			{
				$query->clear()
					->select('subscription_id')
					->from('#__osmembership_subscribers')
					->where('user_id = ' . $row->user_id)
					->where('plan_id = ' . $row->plan_id)
					->where('published IN (1, 2)')
					->where('LENGTH(subscription_id) > 0');
				$db->setQuery($query);
				$row->subscription_id = $db->loadResult();
			}
		}

		// Keep payment_made parameter from original subscription
		if ($rowSubscriber->payment_made > 0)
		{
			$row->payment_made = $rowSubscriber->payment_made;
		}

		if ($rowSubscriber->membership_id)
		{
			$row->membership_id = $rowSubscriber->membership_id;
		}

		// Delete all other subscription records to keep the management clean
		$query->clear()
			->delete('#__osmembership_subscribers')
			->where('user_id = ' . $row->user_id)
			->where('plan_id = ' . $row->plan_id)
			->where('id != ' . $row->id);
		$db->setQuery($query);
		$db->execute();

		// Set from_date is the date of the first_subscription record
		$row->from_date = $rowSubscriber->from_date;

		// Set profile data for the record
		$this->setSubscriptionProfileData($row);

		$row->plan_main_record            = 1;
		$row->plan_subscription_status    = 1;
		$row->plan_subscription_from_date = $row->from_date;
		$row->plan_subscription_to_date   = $row->to_date;
		$row->store();

		// Update profile_id for other subscription records from this user
		if ($row->is_profile && $row->user_id)
		{
			$query->clear()
				->update('#__osmembership_subscribers')
				->set('profile_id = ' . $row->id)
				->where('user_id = ' . $row->user_id)
				->where('id != ' . $row->id);
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Update subscription data from user profile data
	 *
	 * @param   array  $user
	 * @param   array  $subscriptionIds
	 */
	protected function updateSubscriptionsFromUserProfile($user, $subscriptionIds)
	{
		$profileFields = [
			'address1',
			'address2',
			'city',
			'region',
			'country',
			'postal_code',
			'phone',
			'website',
			'favoritebook',
			'aboutme',
			'dob'
		];

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('id, name, profile_field_mapping, is_core')
			->from('#__osmembership_fields')
			->whereIn('profile_field_mapping', $profileFields, ParameterType::STRING);
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		// No fields are mapped, don't process further
		if (!count($rowFields))
		{
			return;
		}

		$name = $user['name'];

		if ($name)
		{
			$pos = strpos($name, ' ');

			if ($pos !== false)
			{
				$firstName = substr($name, 0, $pos);
				$lastName  = substr($name, $pos + 1);
			}
			else
			{
				$firstName = $name;
				$lastName  = '';
			}
		}

		foreach ($subscriptionIds as $subscriptionId)
		{
			$rowSubscription = new OSMembershipTableSubscriber($db);
			$rowSubscription->load($subscriptionId);
			$coreFieldsChange = false;

			if (!empty($name))
			{
				$rowSubscription->first_name = $firstName;
				$rowSubscription->last_name  = $lastName;
				$coreFieldsChange            = true;
			}

			$query->clear()
				->select('*')
				->from('#__osmembership_field_value')
				->where('subscriber_id = ' . $subscriptionId);
			$db->setQuery($query);
			$fieldValues = $db->loadObjectList('field_id');

			foreach ($rowFields as $rowField)
			{
				if (isset($user['profile'][$rowField->profile_field_mapping]))
				{
					$userFieldValue = $user['profile'][$rowField->profile_field_mapping];
				}
				else
				{
					$userFieldValue = '';
				}

				if (is_array($userFieldValue))
				{
					$userFieldValue = json_encode($userFieldValue);
				}
				if ($rowField->is_core)
				{
					$rowSubscription->{$rowField->name} = $userFieldValue;
					$coreFieldsChange                   = true;
				}
				elseif (array_key_exists($rowField->id, $fieldValues))
				{
					// Field is already exist, update
					$query->clear()
						->update('#__osmembership_field_value')
						->set('field_value = ' . $db->quote($userFieldValue))
						->where('id = ' . $fieldValues[$rowField->id]->id);
					$db->setQuery($query)
						->execute();
				}
				else
				{
					// Field is not existed, insert new record
					$query->clear()
						->insert('#__osmembership_field_value')
						->columns($db->quoteName(['subscriber_id', 'field_id', 'field_value']))
						->values(implode(',', $db->quote([$subscriptionId, $rowField->id, $userFieldValue])));
					$db->setQuery($query)
						->execute();
				}
			}

			if ($coreFieldsChange)
			{
				$rowSubscription->store();
			}
		}
	}

	/***
	 * Update subscription data from user custom fields data
	 *
	 * @param   array  $user
	 * @param   array  $subscriptionIds
	 * @param   array  $userFields
	 */
	protected function updateSubscriptionsFromUserCustomFields($user, $subscriptionIds, $userFields)
	{
		$userFieldIds           = [];
		$userFieldNames         = [];
		$userFieldNameIdMapping = [];

		foreach ($userFields as $field)
		{
			$userFieldIds[]                       = $field->id;
			$userFieldNames[]                     = $field->name;
			$userFieldNameIdMapping[$field->name] = $field->id;
		}

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select('id, name, profile_field_mapping, is_core')
			->from('#__osmembership_fields')
			->whereIn('profile_field_mapping', $userFieldNames, ParameterType::STRING);
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		// No fields are mapped, don't process further
		if (!count($rowFields))
		{
			return;
		}

		$userId = (int) $user['id'];

		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');

		/* @var FieldsModelField $model */
		$model = BaseDatabaseModel::getInstance('Field', 'FieldsModel', ['ignore_request' => true]);

		$userFieldValues = $model->getFieldValues($userFieldIds, $userId);

		foreach ($subscriptionIds as $subscriptionId)
		{
			$rowSubscription = new OSMembershipTableSubscriber($db);
			$rowSubscription->load($subscriptionId);
			$coreFieldsChange = false;

			$query->clear()
				->select('*')
				->from('#__osmembership_field_value')
				->where('subscriber_id = ' . $subscriptionId);
			$db->setQuery($query);
			$fieldValues = $db->loadObjectList('field_id');

			foreach ($rowFields as $rowField)
			{
				if (!isset($userFieldNameIdMapping[$rowField->profile_field_mapping]))
				{
					continue;
				}

				$userFieldId = $userFieldNameIdMapping[$rowField->profile_field_mapping];

				if (array_key_exists($userFieldId, $userFieldValues))
				{
					$userFieldValue = $userFieldValues[$userFieldId];
				}
				else
				{
					$userFieldValue = '';
				}

				if (is_array($userFieldValue))
				{
					$userFieldValue = json_encode($userFieldValue);
				}

				if ($rowField->is_core)
				{
					$rowSubscription->{$rowField->name} = $userFieldValue;
					$coreFieldsChange                   = true;
				}
				elseif (array_key_exists($rowField->id, $fieldValues))
				{
					// Field is already exist, update
					$query->clear()
						->update('#__osmembership_field_value')
						->set('field_value = ' . $db->quote($userFieldValue))
						->where('id = ' . $fieldValues[$rowField->id]->id);
					$db->setQuery($query)
						->execute();
				}
				else
				{
					// Field is not existed, insert new record
					$query->clear()
						->insert('#__osmembership_field_value')
						->columns($db->quoteName(['subscriber_id', 'field_id', 'field_value']))
						->values(implode(',', $db->quote([$subscriptionId, $rowField->id, $userFieldValue])));
					$db->setQuery($query)
						->execute();
				}
			}

			if ($coreFieldsChange)
			{
				$rowSubscription->store();
			}
		}
	}

	/**
	 * Log account creation error
	 *
	 * @param   array   $data
	 * @param   string  $errorMsg
	 */
	private function logAccountCreationError($data, $errorMsg)
	{
		$text = '[' . gmdate('m/d/Y g:i A') . '] - ';
		$text .= "Account Creation Error \n";

		foreach ($data as $key => $value)
		{
			$text .= "$key=$value, ";
		}

		$text .= $errorMsg;

		$ipnLogFile = JPATH_COMPONENT . '/account_creation_error.txt';
		$fp         = fopen($ipnLogFile, 'a');
		fwrite($fp, $text . "\n\n");
		fclose($fp);
	}

	/**
	 *  Set avatar for subscription if it's not set and there is avatar from user account
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return void
	 */
	protected function setAvatarForSubscription($row)
	{
		$config = OSMembershipHelper::getConfig();

		if ($config->enable_avatar && !$row->avatar && $row->user_id)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('avatar')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $row->user_id)
				->where('LENGTH(avatar) > 0');
			$db->setQuery($query);
			$avatar = $db->loadResult();

			if ($avatar)
			{
				$row->avatar = $avatar;
			}
		}
	}

	/**
	 * Update coupon used statistic
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return void
	 */
	protected function updateCouponUsage($row): void
	{
		// Early return if there is no coupon used for the coupon
		if (!$row->coupon_id)
		{
			return;
		}

		$params = new Registry($row->params ?? '{}');

		// Early return if the usage is already calculated for the coupon
		if ($params->get('coupon_usage_calculated'))
		{
			return;
		}

		// Update the usage
		$query = $this->db->getQuery(true)
			->update('#__osmembership_coupons')
			->set('used = used + 1')
			->where('id = ' . $row->coupon_id);
		$this->db->setQuery($query);
		$this->db->execute();

		$params->set('coupon_usage_calculated', 1);
		$row->params = $params->toString();
		$row->store();
	}
}
