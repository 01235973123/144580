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
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\IpHelper;
use OSSolution\MembershipPro\Admin\Event\GroupMember\GroupMemberAfterSave;
use OSSolution\MembershipPro\Admin\Event\Subscription\AfterStoreSubscription;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipActive;

class OSMembershipModelGroup extends MPFModel
{
	use OSMembershipModelValidationtrait;

	/**
	 * Constructor
	 *
	 * @param   array  $config
	 *
	 * @throws Exception
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->state->insert('group_id', 'string', '');
	}

	/**
	 * Get group data base on given Group ID
	 */
	public function getData()
	{
		// If ID of the group is not passed, return null
		if (!$this->state->group_id)
		{
			return;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('a.*, b.title AS plan_title, b.number_group_members')
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__osmembership_plans AS b ON a.plan_id = b.id')
			->where('subscription_code = ' . $db->quote($this->state->group_id));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Validate to see if a new group member can still be added to this group
	 *
	 * @param   OSMembershipTableSubscriber  $group
	 *
	 * @return array
	 */
	public function validateAddingMembersToGroup($group)
	{
		$user = Factory::getApplication()->getIdentity();
		/* @var DatabaseDriver $db */
		$db     = Factory::getContainer()->get('db');
		$query  = $db->getQuery(true);
		$errors = [];

		if (!$group)
		{
			$errors[] = Text::_('OSM_INVALID_GROUP');

			return $errors;
		}

		// Check if this user is already member of the group
		if ($user->id)
		{
			$query->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('plan_id = ' . $group->plan_id)
				->where('user_id = ' . $user->id)
				->where('group_admin_id > 0');
			$db->setQuery($query);
			$total = $db->loadResult();

			if ($total > 0)
			{
				$errors[] = Text::sprintf('OSM_YOU_ARE_GROUP_MEMBER_OF_PLAN', $group->plan_title);
			}

			$query->clear()
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('plan_id = ' . $group->plan_id)
				->where('user_id = ' . $user->id)
				->where('group_admin_id = 0');
			$db->setQuery($query);
			$total = $db->loadResult();

			if ($total > 0)
			{
				$errors[] = Text::_('OSM_YOU_ARE_GROUP_ADMIN_OF_THIS_GROUP');
			}
		}

		// Get total members of the group
		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_subscribers')
			->where('plan_id = ' . $group->plan_id)
			->where('group_admin_id = ' . $group->user_id);
		$db->setQuery($query);
		$totalMembers = $db->loadResult();

		$rowPlan               = OSMembershipHelperDatabase::getPlan($group->plan_id);
		$maxNumberGroupMembers = OSMembershipHelperGroupmembership::getMaxNumberGroupMembers($rowPlan, $group->user_id);

		if ($totalMembers > $maxNumberGroupMembers)
		{
			$errors[] = Text::_('OSM_CANNOT_ADD_MORE_GROUP_MEMBERS');
		}

		if (count($errors))
		{
			$replaces = [
				'plan_title' => $group->plan_title,
				'first_name' => $group->first_name,
				'last_name'  => $group->last_name,
			];

			$groupAdmin = Factory::getUser($group->user_id);

			$replaces['group_admin_name']     = $groupAdmin->name;
			$replaces['group_admin_username'] = $groupAdmin->username;
			$replaces['group_admin_email']    = $groupAdmin->email;

			foreach ($errors as &$error)
			{
				foreach ($replaces as $key => $value)
				{
					$error = str_ireplace("[$key]", $value, $error);
				}
			}
		}

		return $errors;
	}

	/**
	 * Get custom fields used for group members, use on join group form
	 *
	 * @param   int  $planId
	 *
	 * @return array
	 */
	public function getGroupMemberFields($planId)
	{
		// Get custom fields
		$rowFields = OSMembershipHelper::getProfileFields($planId, true);

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
	 * Process Subscription
	 *
	 * @param   MPFInput  $input
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function addGroupMember($input)
	{
		$app    = Factory::getApplication();
		$user   = $app->getIdentity();
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);
		$config = OSMembershipHelper::getConfig();

		$data   = $input->post->getData();
		$userId = $user->id;
		$group  = $this->getData();
		$isNew  = true;

		/* @var $row OSMembershipTableSubscriber */
		$row = $this->getTable('Subscriber');

		if (!$userId)
		{
			$data['user_id'] = OSMembershipHelper::saveRegistration($data);
		}
		else
		{
			$data['user_id'] = $userId;
		}

		// Store IP Address of subscriber
		if ($config->get('collect_ip_address', 1))
		{
			$data['ip_address'] = IpHelper::getIp();
		}
		else
		{
			$data['ip_address'] = '';
		}

		$row->bind($data);

		$row->subscription_code = OSMembershipHelper::getUniqueCodeForField(
			'subscription_code',
			'#__osmembership_subscribers'
		);
		$row->user_id           = (int) $row->user_id;
		$row->published         = $this->getGroupMembershipStatus($group);
		$row->group_admin_id    = $group->user_id;
		$row->plan_id           = $group->plan_id;
		$row->created_date      = $row->from_date = Factory::getDate()->toSql();
		$row->is_profile        = 1;

		// Calculate to_date
		$query->select('MAX(to_date)')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $row->group_admin_id)
			->where('plan_id = ' . $row->plan_id)
			->where('published = 1');
		$db->setQuery($query);
		$row->to_date    = $db->loadResult();
		$row->profile_id = $row->id;
		$row->store();

		$rowFields = $this->getGroupMemberFields($row->plan_id);
		$form      = new MPFForm($rowFields);
		$form->storeData($row->id, $data);

		PluginHelper::importPlugin('osmembership');

		// Trigger events
		$event = new AfterStoreSubscription(['row' => $row]);
		$app->triggerEvent($event->getName(), $event);

		if ($row->published == 1)
		{
			$event = new MembershipActive(['row' => $row]);

			$app->triggerEvent($event->getName(), $event);
		}

		$event = new GroupMemberAfterSave([
			'row'   => $row,
			'data'  => $data,
			'isNew' => $isNew,
		]);

		$app->triggerEvent($event->getName(), $event);

		// Send email
		OSMembershipHelperMail::sendUserJoinGroupEmail($row, $config);

		// Store Subscription Code back to input to use on join group complete page
		$input->set('subscription_code', $row->subscription_code);
	}

	/**
	 * Perform validation to make sure the data is valid
	 *
	 * @param   MPFInput  $input
	 *
	 * @return array
	 */
	public function validate($input)
	{
		$group = $this->getData();

		$errors = $this->validateAddingMembersToGroup($group);

		if (count($errors))
		{
			// Return early if there is something wrong with the group data;
			return $errors;
		}

		$config            = OSMembershipHelper::getConfig();
		$rowFields         = $this->getGroupMemberFields($group->plan_id);
		$userId            = Factory::getApplication()->getIdentity()->id;
		$filterInput       = InputFilter::getInstance();
		$createUserAccount = $config->registration_integration && !$userId;

		$errors = [];
		$data   = $input->post->getData();

		// Validate username and password
		if ($createUserAccount)
		{
			$username = $data['username'] ?? '';
			$password = $data['password1'] ?? '';

			$errors = array_merge($errors, $this->validateUsername($username));
			$errors = array_merge($errors, $this->validatePassword($password));

			// Validate email
			$email  = $data['email'] ?? '';
			$errors = array_merge($errors, $this->validateEmail($email, $createUserAccount));

			// Validate name
			$name = trim($data['first_name'] . ' ' . $data['last_name']);

			if ($filterInput->clean($name, 'TRIM') == '')
			{
				$errors[] = Text::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_YOUR_NAME');
			}
		}

		// Validate form fields
		$form = new MPFForm($rowFields);
		$form->setData($data)->bindData();
		$form->buildFieldsDependency(false);

		// If there is error message, use it
		if ($formErrors = $form->validate())
		{
			$errors = array_merge($errors, $formErrors);
		}

		// Validate privacy policy
		if ($config->show_privacy_policy_checkbox && empty($data['agree_privacy_policy']))
		{
			$errors[] = Text::_('OSM_AGREE_PRIVACY_POLICY_ERROR');
		}

		return $errors;
	}

	/**
	 * Get status of the group membership
	 *
	 * @param   OSMembershipTableSubscriber  $group
	 *
	 * @return int
	 */
	private function getGroupMembershipStatus($group)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('plan_id = ' . $group->plan_id)
			->where('user_id = ' . $group->user_id)
			->where('(published = 1 OR payment_method LIKE "os_offline%")')
			->order('id DESC');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$isActive  = false;
		$isPending = false;

		foreach ($rows as $row)
		{
			if ($row->published == 1)
			{
				$isActive = true;
			}
			elseif ($row->published == 0)
			{
				$isPending = true;
			}
		}

		if ($isActive)
		{
			return 1;
		}
		elseif ($isPending)
		{
			return 0;
		}

		return 2;
	}
}
