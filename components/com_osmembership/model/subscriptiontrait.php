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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Image\Image;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipExpire;

trait OSMembershipModelSubscriptiontrait
{
	/**
	 * Refund a subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @throws Exception
	 */
	public function refund($row)
	{
		$method = OSMembershipHelper::loadPaymentMethod($row->payment_method);

		$success = $method->refund($row);

		if ($success !== false)
		{
			/* @var OSMembershipTableSubscriber $rowSubscription */
			$rowSubscription = $this->getTable('Subscriber');
			$rowSubscription->load($row->id);
			$rowSubscription->published = 4;
			$rowSubscription->refunded  = 1;
			$rowSubscription->store();

			// Since plan change, we need to trigger onMembershipExpire for the current subscription
			$event = new MembershipExpire(['row' => $rowSubscription]);

			Factory::getApplication()->triggerEvent($event->getName(), $event);
		}
	}

	/**
	 * Method to create user account based on given data. Account will be enabled automatically
	 *
	 * This is called while creating subscription record from administrator area (import subscription or creating
	 * new subscription record)
	 *
	 * @param $data
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	protected function createUserAccount($data)
	{
		//Store this account into the system and get the username
		$params      = ComponentHelper::getParams('com_users');
		$newUserType = $params->get('new_usertype', 2);

		$data['groups']    = [];
		$data['groups'][]  = $newUserType;
		$data['block']     = 0;
		$data['name']      = rtrim($data['first_name'] . ' ' . $data['last_name']);
		$data['password1'] = $data['password2'] = $data['password'];
		$data['email1']    = $data['email2'] = $data['email'];
		$user              = new User();
		$user->bind($data);

		if (!$user->save())
		{
			throw new Exception($user->getError());
		}

		return $user->id;
	}

	/**
	 * Process upload avatar
	 *
	 * @param   array                        $avatar
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return void
	 */
	protected function uploadAvatar($avatar, $row)
	{
		$config   = OSMembershipHelper::getConfig();
		$fileName = File::makeSafe($avatar['name']);
		$fileExt  = StringHelper::strtoupper(OSMembershipHelper::getFileExt($fileName));

		if (is_file(JPATH_ROOT . '/media/com_osmembership/avatars/' . $fileName) && $fileName != $row->avatar)
		{
			$fileName = uniqid('avatar_') . $fileName;
		}

		$avatarPath = JPATH_ROOT . '/media/com_osmembership/avatars/' . $fileName;

		if ($fileExt == 'PNG')
		{
			$imageType = IMAGETYPE_PNG;
		}
		elseif ($fileExt == 'GIF')
		{
			$imageType = IMAGETYPE_GIF;
		}
		elseif (in_array($fileExt, ['JPG', 'JPEG']))
		{
			$imageType = IMAGETYPE_JPEG;
		}
		else
		{
			$imageType = '';
		}

		$image  = new Image($avatar['tmp_name']);
		$width  = $config->avatar_width ?: 80;
		$height = $config->avatar_height ?: 80;

		if ($config->get('resize_avatar_method', 'crop_resize') == 'crop_resize')
		{
			$image->cropResize($width, $height, false)
				->toFile($avatarPath, $imageType);
		}
		else
		{
			$image->resize($width, $height, false)
				->toFile($avatarPath, $imageType);
		}

		// Update avatar of existing subscription records from this user
		if ($row->user_id > 0)
		{
			/* @var JDatabaseDriver $db */
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->update('#__osmembership_subscribers')
				->set('avatar = ' . $db->quote($fileName))
				->where('user_id = ' . $row->user_id);
			$db->setQuery($query);
			$db->execute();
		}

		$row->avatar = $fileName;
	}

	/**
	 * Get custom fields for the subscription
	 *
	 * @param   int     $planId
	 * @param   bool    $loadCoreFields
	 * @param   string  $language
	 * @param   string  $action
	 * @param   string  $view
	 *
	 * @return array
	 */
	protected function getFields($planId, $loadCoreFields = true, $language = null, $action = null, $view = null)
	{
		$rowFields = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'getProfileFields',
			[$planId, $loadCoreFields, $language, $action, $view]
		);

		$formFields = [];

		// Remove message and heating custom fields type as it is not needed for calculation and storing data
		for ($i = 0, $n = count($rowFields); $i < $n; $i++)
		{
			$rowField = $rowFields[$i];

			if (in_array($rowField->fieldtype, ['Heading', 'Message']))
			{
				unset($rowFields[$i]);

				continue;
			}

			if (!$rowField->is_core)
			{
				$formFields[] = $rowField;
			}
		}

		$rowFields = array_values($rowFields);

		return [$rowFields, $formFields];
	}

	/**
	 * Method to calculate subscription from date
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 * @param   array                        $data
	 *
	 * @return  Date
	 */
	protected function calculateSubscriptionFromDate($row, $rowPlan, $data = [])
	{
		$params                      = new Registry($rowPlan->params);
		$subscriptionStartDateOption = $params->get('subscription_start_date_option', '0');
		$subscriptionStartDateField  = $params->get('subscription_start_date_field');
		$planSubscriptionStartDate   = $params->get('subscription_start_date');

		// Early return in case user select date on subscription form
		if ($subscriptionStartDateOption == 2
			&& $date = $this->getUserSelectedSubscriptionStartDate($subscriptionStartDateField, $data))
		{
			$row->from_date = $date->toSql();

			return $date;
		}

		$maxDate = null;

		if ($row->user_id > 0 && !$rowPlan->lifetime_membership)
		{
			$config = OSMembershipHelper::getConfig();

			/* @var JDatabaseDriver $db */
			$groupingPlans = OSMembershipHelperSubscription::getGroupingPlans($row);
			$db            = $this->getDbo();
			$query         = $db->getQuery(true)
				->select('MAX(to_date)')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $row->user_id);

			if (!empty($groupingPlans))
			{
				$query->whereIn('plan_id', $groupingPlans);
			}
			else
			{
				$query->where('plan_id = ' . $row->plan_id);
			}

			if ($config->use_expired_date_as_start_date)
			{
				$query->where('published IN (1,2)');
			}
			else
			{
				$query->where('published = 1');
			}

			$db->setQuery($query);
			$maxDate = $db->loadResult();
		}

		if ($maxDate)
		{
			$date = Factory::getDate($maxDate);

			// Add one more second to from date of renewal subscription if this is fixed expiration date
			if (!(int) $rowPlan->expired_date)
			{
				$date->add(new DateInterval('PT1S'));
			}

			$row->from_date = $date->toSql();
		}
		elseif ($subscriptionStartDateOption == 1 && $planSubscriptionStartDate)
		{
			$date           = Factory::getDate($planSubscriptionStartDate, Factory::getApplication()->get('offset'));
			$row->from_date = $date->toSql();
		}
		else
		{
			// Fall back to current date
			$date           = Factory::getDate();
			$row->from_date = $date->toSql();
		}

		return $date;
	}

	/**
	 * Method to calculate subscription from date
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 * @param   Date                         $date
	 * @param   array                        $rowFields
	 * @param   array                        $data
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	protected function calculateSubscriptionEndDate($row, $rowPlan, $date, $rowFields, $data)
	{
		/* @var JDatabaseDriver $db */
		$db = $this->getDbo();

		// In case plan is a lifetime membership, the subscription will be lifetime subscription
		if ($rowPlan->lifetime_membership)
		{
			$row->to_date = '2099-12-31 23:59:59';

			return;
		}

		// Handle the case the upgrade rule requires keep original subscription duration
		if ($row->act == 'upgrade')
		{
			$upgradeRule = OSMembershipHelperDatabase::getUpgradeRule($row->upgrade_option_id);

			if (in_array($upgradeRule->upgrade_prorated, [3, 4, 5]))
			{
				$query = $db->getQuery(true)
					->select('*')
					->from('#__osmembership_subscribers')
					->where('user_id = ' . $row->user_id)
					->where('plan_id = ' . $upgradeRule->from_plan_id)
					->where('published = 1')
					->order('to_date DESC');
				$db->setQuery($query);
				$fromSubscription = $db->loadObject();

				if ($fromSubscription)
				{
					$row->from_date = $fromSubscription->from_date;
					$row->to_date   = $fromSubscription->to_date;

					return;
				}
			}
		}

		// In case plan has fixed expiration date, call a separate method to calculate the date
		if ((int) $rowPlan->expired_date)
		{
			$this->calculateSubscriptionFixedExpirationDate($row, $rowPlan, $date);

			return;
		}

		[$dateIntervalSpec, $upgradeProratedInterval] = $this->calculateDateModify($row, $rowPlan);

		$date->add(new DateInterval($dateIntervalSpec));

		if (!empty($upgradeProratedInterval))
		{
			$date->add($upgradeProratedInterval);
			$date->modify('+1 day');
		}

		$this->modifySubscriptionDuration($date, $rowFields, $data);

		$row->to_date = $date->toSql();
	}

	/**
	 * Method to calculate subscription end date in case plan is a fixed expiration date plan
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 * @param   Date                         $date
	 *
	 * @return  void
	 */
	protected function calculateSubscriptionFixedExpirationDate($row, $rowPlan, $date)
	{
		$timezone    = Factory::getApplication()->get('offset');
		$expiredDate = Factory::getDate($rowPlan->expired_date, $timezone);

		$fromDate = clone $date;

		$fromDate->setTimezone(new DateTimeZone($timezone));

		$toDate = clone $fromDate;
		$toDate->setDate($toDate->year, $expiredDate->month, $expiredDate->day);

		$fromDate->setTime(23, 59, 59);
		$toDate->setTime(23, 59, 59);

		$numberYears = $this->calculateNumberYearsForFixedExpirationDate($row, $rowPlan);

		/**
		 * Normally, the subscription should be in same year, so no extra year should be added to end date,
		 * that's the reason we reduce one year here
		 */
		$numberYears--;

		// In case users subscribed after expiration date, one extra year should be added
		if ($fromDate >= $toDate)
		{
			$numberYears++;
		}
		elseif ($rowPlan->grace_period > 0)
		{
			/**
			 * This is the case users subscribe nearly expiration date,
			 * within the grace period,
			 * so one extra year will be added to the subscription
			 */
			$dateDifferenceInDays = $fromDate->diff($toDate)->days;

			if ($rowPlan->grace_period >= $dateDifferenceInDays)
			{
				$numberYears++;
			}
		}

		if ($numberYears > 0)
		{
			$toDate->modify(sprintf('+%d years', $numberYears));
		}

		// Handle the case where the calculated end date is smaller than expiration date
		$expiredDate->setTime(23, 59, 59);

		if ($toDate < $expiredDate)
		{
			$toDate = $expiredDate;
		}
		
		$row->to_date = $toDate->toSql();
	}

	/**
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 *
	 * @return int
	 */
	protected function calculateNumberYearsForFixedExpirationDate($row, $rowPlan)
	{
		$numberYears = 1;

		if ($row->act == 'renew')
		{
			if ($row->renew_option_id == 0 || ($row->renew_option_id == OSM_DEFAULT_RENEW_OPTION_ID))
			{
				if ($rowPlan->subscription_length_unit == 'Y')
				{
					$numberYears = $rowPlan->subscription_length;
				}
			}
			else
			{
				/* @var DatabaseDriver $db */
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('*')
					->from('#__osmembership_renewrates')
					->where('id = ' . $row->renew_option_id);
				$db->setQuery($query);
				$renewOption = $db->loadObject();

				if ($renewOption && $renewOption->renew_option_length_unit == 'Y' && $renewOption->renew_option_length > 1)
				{
					$numberYears = $renewOption->renew_option_length;
				}
			}
		}
		elseif ($rowPlan->subscription_length_unit == 'Y')
		{
			$numberYears = $rowPlan->subscription_length;
		}

		return $numberYears;
	}

	/**
	 * Calculate date modify string for subscription end date
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 *
	 * @return array
	 */
	protected function calculateDateModify($row, $rowPlan)
	{
		/* @var JDatabaseDriver $db */
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$upgradeProratedInterval = '';

		if ($row->act == 'renew')
		{
			$renewOptionId = (int) $row->renew_option_id;

			if ($renewOptionId == 0 || $renewOptionId == OSM_DEFAULT_RENEW_OPTION_ID)
			{
				$dateIntervalSpec = OSMembershipHelperSubscription::getDateIntervalString(
					$rowPlan->subscription_length,
					$rowPlan->subscription_length_unit
				);
			}
			else
			{
				$query->select('*')
					->from('#__osmembership_renewrates')
					->where('id = ' . $renewOptionId);
				$db->setQuery($query);
				$renewOption      = $db->loadObject();
				$dateIntervalSpec = OSMembershipHelperSubscription::getDateIntervalString(
					$renewOption->renew_option_length,
					$renewOption->renew_option_length_unit
				);
			}
		}
		elseif ($row->act == 'upgrade')
		{
			$dateIntervalSpec = OSMembershipHelperSubscription::getDateIntervalString(
				$rowPlan->subscription_length,
				$rowPlan->subscription_length_unit
			);
			$query->select('*')
				->from('#__osmembership_upgraderules')
				->where('id = ' . $row->upgrade_option_id);
			$db->setQuery($query);
			$upgradeOption = $db->loadObject();

			if ($upgradeOption->upgrade_prorated == 1)
			{
				// Check to see how many days left from his current plan subscription
				$query->clear()
					->select('MAX(to_date)')
					->from('#__osmembership_subscribers')
					->where('published = 1')
					->where('plan_id = ' . $upgradeOption->from_plan_id)
					->where('user_id = ' . $row->user_id);
				$db->setQuery($query);
				$fromPlanSubscriptionEndDate = $db->loadResult();

				if ($fromPlanSubscriptionEndDate)
				{
					$fromPlanSubscriptionEndDate = Factory::getDate($fromPlanSubscriptionEndDate);
					$todayDate                   = Factory::getDate('now');

					if ($fromPlanSubscriptionEndDate > $todayDate)
					{
						$upgradeProratedInterval = $todayDate->diff($fromPlanSubscriptionEndDate);
					}
				}
			}
		}
		elseif ($rowPlan->recurring_subscription && $rowPlan->trial_duration)
		{
			$dateIntervalSpec = 'P' . $rowPlan->trial_duration . $rowPlan->trial_duration_unit;
		}
		else
		{
			$dateIntervalSpec = OSMembershipHelperSubscription::getDateIntervalString(
				$rowPlan->subscription_length,
				$rowPlan->subscription_length_unit
			);
		}

		return [$dateIntervalSpec, $upgradeProratedInterval];
	}

	/**
	 * Modify subscription duration based on the option which subscriber choose on form
	 *
	 * @param   Date   $date
	 * @param   array  $rowFields
	 * @param   array  $data
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	protected function modifySubscriptionDuration($date, $rowFields, $data)
	{
		// Check to see whether there are any fields which can modify subscription end date
		foreach ($rowFields as $rowField)
		{
			if (empty($rowField->modify_subscription_duration) || empty($data[$rowField->name]))
			{
				continue;
			}

			$durationValues = explode("\r\n", $rowField->modify_subscription_duration);
			$values         = explode("\r\n", $rowField->values);
			$values         = array_map('trim', $values);
			$fieldValue     = $data[$rowField->name];

			$fieldValueIndex = array_search($fieldValue, $values);

			if ($fieldValueIndex !== false && !empty($durationValues[$fieldValueIndex]))
			{
				$modifyDurationString = $durationValues[$fieldValueIndex];

				if (!$date->modify($modifyDurationString))
				{
					Factory::getApplication()->enqueueMessage(
						sprintf('Modify duration string %s is invalid', $modifyDurationString),
						'warning'
					);
				}
			}
		}
	}

	/**
	 * Get unique Transaction ID for the subscriotion
	 *
	 * @return string
	 */
	protected function getUniqueTransactionId()
	{
		$db = $this->getDbo();

		while (true)
		{
			$transactionId = strtoupper(UserHelper::genRandomPassword(16));
			$query         = $db->getQuery(true)
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where($db->quoteName('transaction_id') . ' = ' . $db->quote($transactionId));
			$db->setQuery($query);
			$total = $db->loadResult();

			if (!$total)
			{
				break;
			}
		}

		return $transactionId;
	}

	/**
	 * Get Joomla groups from custom fields which subscriber select for their subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   array                        $rowFields
	 *
	 * @return array
	 */
	protected function getJoomlaGroupsFromFields($row, $rowFields = [])
	{
		$groups = [];

		if (!$rowFields)
		{
			$rowFields = OSMembershipHelper::getProfileFields($row->plan_id, true, $row->language, $row->act);
		}

		$subscriptionData = OSMembershipHelper::getProfileData($row, $row->plan_id, $rowFields);

		foreach ($rowFields as $field)
		{
			if (empty($field->joomla_group_ids) || empty($field->values) || empty($subscriptionData[$field->name]))
			{
				continue;
			}

			$fieldValue = $subscriptionData[$field->name];

			$groups = array_merge(
				$groups,
				OSMembershipHelperSubscription::getUserGroupsFromFieldValue($field, $fieldValue)
			);
		}

		return $groups;
	}

	/**
	 * Delete user avatar
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return void
	 */
	public function deleteUserAvatar($row)
	{
		if (!$row->avatar || !file_exists(JPATH_ROOT . '/media/com_osmembership/avatars/' . $row->avatar))
		{
			return;
		}

		File::delete(JPATH_ROOT . '/media/com_osmembership/avatars/' . $row->avatar);

		$row->avatar = '';

		if ($row->user_id > 0)
		{
			/* @var JDatabaseDriver $db */
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->update('#__osmembership_subscribers')
				->set('avatar = ""')
				->where('user_id = ' . $row->user_id);
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Update show_on_members_list setting for the given subscriber
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	public function updateShowOnMembersList($row)
	{
		if (!$row->user_id)
		{
			return;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->update('#__osmembership_subscribers')
			->set('show_on_members_list = ' . (int) $row->show_on_members_list)
			->where('user_id = ' . $row->user_id);
		$db->setQuery($query)
			->execute();
	}

	/**
	 * Method to allow filtering form data
	 *
	 * @param   array  $rowFields
	 * @param   array  $data
	 *
	 * @return  array
	 */
	public function filterFormData($rowFields, $data)
	{
		$inputFilter = InputFilter::getInstance();

		foreach ($rowFields as $rowField)
		{
			if (!$rowField->filter || !isset($data[$rowField->name]))
			{
				continue;
			}

			switch ($rowField->filter)
			{
				case 'UPPERCASE':
					$data[$rowField->name] = $inputFilter->clean($data[$rowField->name], 'STRING');
					$data[$rowField->name] = StringHelper::strtoupper($data[$rowField->name]);
					break;
				case 'LOWERCASE':
					$data[$rowField->name] = $inputFilter->clean($data[$rowField->name], 'STRING');
					$data[$rowField->name] = StringHelper::strtolower($data[$rowField->name]);
					break;
				case 'TRIM':
					$data[$rowField->name] = $inputFilter->clean($data[$rowField->name], 'STRING');
					$data[$rowField->name] = StringHelper::rtrim($data[$rowField->name]);
					break;
				case 'LTRIM':
					$data[$rowField->name] = $inputFilter->clean($data[$rowField->name], 'STRING');
					$data[$rowField->name] = StringHelper::ltrim($data[$rowField->name]);
					break;
				case 'RTRIM':
					$data[$rowField->name] = $inputFilter->clean($data[$rowField->name], 'STRING');
					$data[$rowField->name] = StringHelper::trim($data[$rowField->name]);
					break;
				case 'UCFIRST':
					$data[$rowField->name] = $inputFilter->clean($data[$rowField->name], 'STRING');
					$data[$rowField->name] = StringHelper::ucfirst($data[$rowField->name]);
					break;
				case 'UCWORDS':
					$data[$rowField->name] = $inputFilter->clean($data[$rowField->name], 'STRING');
					$data[$rowField->name] = StringHelper::ucwords($data[$rowField->name]);
					break;
				default:
					$data[$rowField->name] = $inputFilter->clean($data[$rowField->name], $rowField->name);
					break;
			}
		}

		return $data;
	}

	/**
	 * Get subscription start date in case user select a valid start date for their own subscription
	 *
	 * @param   string  $subscriptionStartDateField
	 * @param   array   $data
	 *
	 * @return false|Date
	 */
	protected function getUserSelectedSubscriptionStartDate($subscriptionStartDateField, $data)
	{
		// No date selected, use default behavior
		if (empty($data[$subscriptionStartDateField]))
		{
			return false;
		}

		$config     = OSMembershipHelper::getConfig();
		$dateFormat = $config->date_field_format ?: '%Y-%m-%d';
		$dateFormat = str_replace('%', '', $dateFormat);

		$selectedDate = DateTime::createFromFormat($dateFormat, $data[$subscriptionStartDateField]);

		if (!$selectedDate)
		{
			return false;
		}

		try
		{
			$date = Factory::getDate($selectedDate->format('Y-m-d'), Factory::getApplication()->get('offset'));

			// Use current time for subscription start date
			$currentDate = Factory::getDate('Now', Factory::getApplication()->get('offset'));
			$date->setTime($currentDate->hour, $currentDate->minute, $currentDate->second);

			return $date;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Method to update user groups from custom field when subscribers update their profile
	 *
	 * @param   Joomla\CMS\User\User  $user
	 * @param   array                 $rowFields
	 * @param   array                 $oldData
	 * @param   array                 $newData
	 */
	protected function updateUserGroups($user, $rowFields, $oldData, $newData)
	{
		$assignUserGroups = [];
		$removeUserGroups = [];

		// Handle user group on profile update
		foreach ($rowFields as $rowField)
		{
			if (empty($rowField->joomla_group_ids) || empty($rowField->values))
			{
				continue;
			}

			$oldFieldValue = $oldData[$rowField->name] ?? '';
			$newFieldValue = $newData[$rowField->name] ?? '';

			if ($oldFieldValue)
			{
				$removeUserGroups = array_merge(
					$removeUserGroups,
					OSMembershipHelperSubscription::getUserGroupsFromFieldValue($rowField, $oldFieldValue)
				);
			}

			if ($newFieldValue)
			{
				$assignUserGroups = array_merge(
					$assignUserGroups,
					OSMembershipHelperSubscription::getUserGroupsFromFieldValue($rowField, $newFieldValue)
				);
			}
		}

		$groups = $user->groups;

		if (count($removeUserGroups))
		{
			$groups = array_diff($groups, $removeUserGroups);
		}

		if (count($assignUserGroups))
		{
			$groups = array_merge($groups, $assignUserGroups);
		}

		$user->groups = $groups;
		$user->save(true);
	}
}
