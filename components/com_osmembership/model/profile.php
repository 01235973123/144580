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
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use OSSolution\MembershipPro\Admin\Event\Profile\ProfileUpdate;

class OSMembershipModelProfile extends MPFModel
{
	use OSMembershipModelSubscriptiontrait;
	use OSMembershipModelValidationtrait;

	/**
	 * Get profile data of the users
	 */
	public function getData()
	{
		$user   = Factory::getApplication()->getIdentity();
		$config = OSMembershipHelper::getConfig();
		$db     = $this->getDbo();
		$query  = $db->getQuery(true)
			->select('a.*, b.username')
			->from('#__osmembership_subscribers AS a ')
			->leftJoin('#__users AS b ON a.user_id = b.id')
			->where('is_profile=1')
			->where('(a.email = ' . $db->quote($user->email) . ' OR user_id = ' . $user->id . ')')
			->order('id DESC');

		if (!$config->show_incomplete_payment_subscriptions)
		{
			$query->where('(a.published != 0 OR gross_amount = 0 OR a.payment_method LIKE "os_offline%")');
		}

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to validate user profile data
	 *
	 * @param   MPFInput  $input
	 *
	 * @return array
	 */
	public function validateProfileData($input)
	{
		$userId = Factory::getApplication()->getIdentity()->id;
		$errors = [];

		$avatar = $input->files->get('profile_avatar');

		if ($avatar && $avatar['name'])
		{
			$avatarErrors = $this->validateAvatar($avatar);

			if (count($avatarErrors))
			{
				$errors = array_merge($errors, $avatarErrors);
			}
		}

		// Validate username
		$params = ComponentHelper::getParams('com_users');

		if ($params->get('change_login_name'))
		{
			$username = $input->getString('username');

			if ($usernameErrors = $this->validateUsername($username, $userId))
			{
				$errors = array_merge($errors, $usernameErrors);
			}
		}

		// Validate password
		$password = $input->post->getString('password');

		if ($password)
		{
			// Make sure confirm password is valid
			$password2 = $input->post->getString('password2');

			if ($password !== $password2)
			{
				$errors[] = Text::_('OSM_PASSWORD_DOES_NOT_MATCH');
			}
			elseif ($passwordErrors = $this->validatePassword($password))
			{
				$errors = array_merge($errors, $passwordErrors);
			}
		}

		// Only validate email if email is editable
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_fields')
			->where('name = ' . $db->quote('email'));
		$db->setQuery($query);
		$emailField = $db->loadObject();

		if ($emailField && $emailField->can_edit_on_profile)
		{
			// Validate email
			$email = $input->post->getString('email');

			if (count($emailErrors = $this->validateEmail($email, true, $userId)))
			{
				$errors = array_merge($errors, $emailErrors);
			}
		}

		return $errors;
	}

	/**
	 * Update profile of the user
	 *
	 * @param   array     $data
	 * @param   MPFInput  $input
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function updateProfile($data, $input)
	{
		$db     = $this->getDbo();
		$user   = Factory::getApplication()->getIdentity();
		$config = OSMembershipHelper::getConfig();

		/* @var OSMembershipTableSubscriber $row */
		$row = $this->getTable('Subscriber');

		if (!$row->load($data['id']))
		{
			throw new Exception('Invalid Subscription Record', 404);
		}

		if ($row->user_id != $user->id)
		{
			throw new Exception('You cannot update profile data of other user', 403);
		}

		// Delete user avatar if user choose to do so
		if ($input->exists('delete_avatar'))
		{
			$this->deleteUserAvatar($row);
		}

		//Store custom field data for this profile record
		if (OSMembershipHelper::isUniquePlan($user->id))
		{
			$planId = $row->plan_id;
		}
		else
		{
			$planId = 0;
		}

		// Dis-allow update important data
		unset($data['from_date'], $data['to_date']);

		[$rowFields, $formFields] = $this->getFields($planId);

		// In case user is a group member, only show fields which are being available on group members form
		if ($row->group_admin_id > 0)
		{
			$rowFields = $this->filterGroupMemberFields($rowFields);
		}

		// Apply data filtering
		$data = $this->filterFormData($rowFields, $data);

		$form = new MPFForm($formFields);
		$form->setData($data)
			->bindData()
			->buildFieldsDependency();

		foreach ($form->getFields() as $field)
		{
			if (!$field->visible)
			{
				unset($data[$field->name]);
			}
		}

		$oldData = OSMembershipHelper::getProfileData($row, $planId, $rowFields);

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__users')
			->where('email = ' . $db->quote($data['email']))
			->where('id != ' . $row->user_id);
		$db->setQuery($query);
		$total = $db->loadResult();

		// Upload avatar image
		$avatar = $input->files->get('profile_avatar');

		if ($avatar && $avatar['name'])
		{
			$this->uploadAvatar($avatar, $row);
		}

		$userData         = [];
		$userData['name'] = trim($data['first_name'] . ' ' . $data['last_name']);

		if (!$total)
		{
			$userData['email'] = $data['email'];
		}

		if ($data['password'])
		{
			$userData['password2'] = $userData['password'] = $data['password'];
		}

		$params = ComponentHelper::getParams('com_users');

		if ($params->get('change_login_name'))
		{
			$username    = $data['username'];
			$filterInput = InputFilter::getInstance();
			$username    = $filterInput->clean($username, 'USERNAME');

			$isUsernameCompliant = !(preg_match('#[<>"\'%;()&\\\\]|\\.\\./#', $username) || strlen(
					utf8_decode($username)
				) < 2
				|| trim($username) != $username);

			if ($isUsernameCompliant)
			{
				// Check and make sure the username doesn't exist
				$query->clear()
					->select('COUNT(*)')
					->from('#__users')
					->where('username = ' . $db->quote($username))
					->where('id != ' . $row->user_id);
				$db->setQuery($query);
				$total = $db->loadResult();

				if (!$total)
				{
					$userData['username'] = $username;
				}
			}
		}
		elseif ($config->use_email_as_username && !$total)
		{
			$userData['username'] = $data['email'];
		}

		// Remove empty values to avoid saving user data error
		$userData = array_filter($userData, function ($value) {
			return strlen($value) > 0;
		});

		$row->bind($data);

		if (!$row->check())
		{
			throw new Exception($row->getError());
		}

		if (!$row->store())
		{
			throw new Exception($row->getError());
		}

		$form->storeFormData($row->id, $data);

		//Synchronize profile data of other subscription records from this subscriber
		$config = OSMembershipHelper::getConfig();

		// Synchronize data from this subscription record to other subscriptions
		OSMembershipHelperSubscription::synchronizeProfileData($row, $rowFields);

		//Trigger event	onProfileUpdate event
		PluginHelper::importPlugin('osmembership');

		$event = new ProfileUpdate(['row' => $row]);

		Factory::getApplication()->triggerEvent($event->getName(), $event);

		// Saving user data
		$user->bind($userData);

		if (!$user->save(true))
		{
			Factory::getApplication()->enqueueMessage($user->getError(), 'warning');
		}

		$newData = OSMembershipHelper::getProfileData($row, $planId, $rowFields);

		// Update user groups base on custom field settings
		$this->updateUserGroups($user, $rowFields, $oldData, $newData);

		reset($rowFields);

		$updatedFields = [];

		foreach ($rowFields as $rowField)
		{
			if (isset($oldData[$rowField->name], $newData[$rowField->name]) && $oldData[$rowField->name] != $newData[$rowField->name])
			{
				$updatedField            = new stdClass();
				$updatedField->title     = $rowField->title;
				$updatedField->old_value = $oldData[$rowField->name];
				$updatedField->new_value = $newData[$rowField->name];

				$updatedFields[] = $updatedField;
			}
		}

		OSMembershipHelperMail::sendProfileUpdateEmail($row, $config, $updatedFields);

		if ($config->get('enable_select_show_hide_members_list') && isset($data['show_on_members_list']))
		{
			$this->updateShowOnMembersList($row);
		}

		return true;
	}

	/**
	 * Remove fields which are not available for group members
	 *
	 * @param   array  $rowFields
	 *
	 * @return array
	 */
	public function filterGroupMemberFields($rowFields)
	{
		for ($i = 0, $n = count($rowFields); $i < $n; $i++)
		{
			$rowField = $rowFields[$i];

			if (!$rowField->show_on_group_member_form)
			{
				unset($rowFields[$i]);
			}
		}

		return array_values($rowFields);
	}

	/**
	 * Method to update subscription credit card
	 *
	 * @param   array  $data
	 *
	 * @throws Exception
	 */
	public function updateCard($data)
	{
		$subscription = OSMembershipHelperSubscription::getSubscription($data['subscription_id']);

		if (!$subscription)
		{
			throw new Exception(Text::sprintf('Subscription ID %s not found', $data['subscription_id']));
		}

		$method = OSMembershipHelperPayments::getPaymentMethod($subscription->payment_method);

		if (method_exists($method, 'updateCard'))
		{
			$method->updateCard($data, $subscription);
		}
		else
		{
			throw new Exception(
				Text::sprintf('Payment method %s does not support update credit card', $subscription->payment_method)
			);
		}
	}

	/**
	 * Get custom fields for the subscription
	 *
	 * @param   int     $planId
	 * @param   bool    $loadCoreFields
	 * @param   string  $language
	 * @param   string  $action
	 *
	 * @return array
	 */
	protected function getFields($planId, $loadCoreFields = true, $language = null, $action = null)
	{
		$rowFields  = OSMembershipHelper::getProfileFields($planId, $loadCoreFields, $language, $action);
		$formFields = [];

		// Remove message and heating custom fields type as it is not needed for calculation and storing data
		for ($i = 0, $n = count($rowFields); $i < $n; $i++)
		{
			$rowField = $rowFields[$i];

			// These fields could not be changed (fee field, field not being editable on profile), thus need to be removed
			if (in_array($rowField->fieldtype, ['Heading', 'Message'])
				|| !$rowField->can_edit_on_profile
				|| $rowField->fee_field)
			{
				unset($rowFields[$i]);

				continue;
			}

			if (!$rowField->is_core)
			{
				$formFields[] = $rowField;
			}
		}

		reset($rowFields);

		return [$rowFields, $formFields];
	}
}
