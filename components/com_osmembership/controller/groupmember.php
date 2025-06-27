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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;
use OSSolution\MembershipPro\Admin\Event\Profile\GetProfileData;

class OSMembershipControllerGroupmember extends OSMembershipController
{
	use OSMembershipControllerData;

	public function __construct(MPFInput $input = null, array $config = [])
	{
		parent::__construct($input, $config);

		$this->registerTask('apply', 'save');
	}

	/**
	 * Display form to allow adding new group member
	 *
	 * @return void
	 */
	public function add()
	{
		$this->input->set('view', 'groupmember');
		$this->input->set('layout', 'default');

		$this->display();
	}

	/**
	 * Display form to allow editing group member
	 *
	 * @return void
	 */
	public function edit()
	{
		$this->csrfProtection();

		$cid = $this->input->get('cid', [], 'array');
		$cid = ArrayHelper::toInteger($cid);

		$this->input->set('id', $cid[0]);
		$this->input->set('view', 'groupmember');
		$this->input->set('layout', 'default');

		$this->display();
	}

	/**
	 * Method to allow adding new member to a group
	 */
	public function save()
	{
		$this->csrfProtection();

		$config = OSMembershipHelper::getConfig();

		$task     = $this->getTask();
		$cid      = $this->input->post->get('cid', [], 'array');
		$cid      = ArrayHelper::toInteger($cid);
		$memberId = $cid[0];

		$this->input->post->set('id', $memberId);

		if ($config->use_email_as_username && !$memberId)
		{
			$this->input->post->set('username', $this->input->post->getString('email'));
		}

		$canManage = OSMembershipHelper::getManageGroupMemberPermission();

		if (($memberId && $canManage >= 1) || ($canManage == 2))
		{
			/* @var OSMembershipModelGroupmember $model */
			$model = $this->getModel('groupmember');

			$errors = $model->validate($this->input);

			if (count($errors))
			{
				foreach ($errors as $error)
				{
					$this->app->enqueueMessage($error, 'error');
				}

				$this->input->set('view', 'groupmember');
				$this->input->set('validate_error', 1);

				$this->display();

				return;
			}

			$post = $this->input->post->getData();

			if (empty($post['password1']))
			{
				$post['password'] = $post['password1'] = UserHelper::genRandomPassword();
			}
			else
			{
				$post['password'] = $post['password1'];
			}

			$model->store($post);
			$Itemid = OSMembershipHelperRoute::findView('groupmembers', $this->input->getInt('Itemid', 0));

			if ($task === 'apply' && !empty($post['id']))
			{
				$this->setRedirect(
					Route::_(
						'index.php?option=com_osmembership&view=groupmember&id=' . $post['id'] . '&Itemid=' . $Itemid,
						false
					),
					Text::_('OSM_GROUP_MEMBER_WAS_SUCCESSFULL_CREATED')
				);
			}
			else
			{
				$this->setRedirect(
					Route::_('index.php?option=com_osmembership&view=groupmembers&Itemid=' . $Itemid, false),
					Text::_('OSM_GROUP_MEMBER_WAS_SUCCESSFULL_CREATED')
				);
			}
		}
		else
		{
			$this->setRedirect('index.php', Text::_('OSM_NOT_ALLOW_TO_MANAGE_GROUP_MEMBERS'));
		}
	}

	/**
	 * Cancel add/edit group member, redirect back to group members management page
	 *
	 * @return void
	 */
	public function cancel()
	{
		$Itemid = OSMembershipHelperRoute::findView('groupmembers', $this->input->getInt('Itemid', 0));
		$this->setRedirect(Route::_('index.php?option=com_osmembership&view=groupmembers&Itemid=' . $Itemid, false));
	}

	/**
	 * Delete a member from group
	 */
	public function delete()
	{
		$this->csrfProtection();
		$canManage = OSMembershipHelper::getManageGroupMemberPermission();

		if ($canManage < 1)
		{
			$this->setRedirect('index.php', Text::_('OSM_NOT_ALLOW_TO_MANAGE_GROUP_MEMBERS'));

			return;
		}

		$cid = $this->input->get('cid', [], 'array');
		$id  = $this->input->getInt('member_id', 0);

		// This code is added for backward compatible purpose
		if (!count($cid))
		{
			$cid = [$id];
		}

		$Itemid = $this->input->getInt('Itemid', 0);

		/* @var OSMembershipModelGroupmember $model */
		$model = $this->getModel('groupmember');

		foreach ($cid as $id)
		{
			$model->deleteMember($id);
		}

		$this->setRedirect(
			Route::_('index.php?option=com_osmembership&view=groupmembers&Itemid=' . $Itemid, false),
			Text::_('OSM_GROUP_MEMBER_WAS_SUCCESSFULL_DELETED')
		);
	}

	/**
	 * Get profile data of the subscriber, using for json format
	 */
	public function get_member_data()
	{
		// Check permission
		$canManage = OSMembershipHelper::getManageGroupMemberPermission();

		if ($canManage >= 1)
		{
			$input  = $this->input;
			$userId = $input->getInt('user_id', 0);
			$planId = $input->getInt('plan_id');
			$data   = [];

			if ($userId)
			{
				$rowFields = OSMembershipHelper::getProfileFields($planId, true);

				/* @var DatabaseDriver $db */
				$db    = Factory::getContainer()->get('db');
				$query = $db->getQuery(true);
				$query->clear();
				$query->select('*')
					->from('#__osmembership_subscribers')
					->where('user_id=' . $userId);
				$db->setQuery($query);
				$rowProfile = $db->loadObject();
				$data       = [];

				if ($rowProfile)
				{
					$data = OSMembershipHelper::getProfileData($rowProfile, $planId, $rowFields);
				}
				else
				{
					// Trigger plugin to get data
					$mappings = [];

					foreach ($rowFields as $rowField)
					{
						if ($rowField->field_mapping)
						{
							$mappings[$rowField->name] = $rowField->field_mapping;
						}
					}

					PluginHelper::importPlugin('osmembership');

					$event = new GetProfileData(['userId' => $userId, 'mappings' => $mappings]);

					$results = $this->app->triggerEvent($event->getName(), $event);

					if (count($results))
					{
						foreach ($results as $res)
						{
							if (is_array($res) && count($res))
							{
								$data = $res;
								break;
							}
						}
					}
				}

				if (!count($data) && PluginHelper::isEnabled('user', 'profile'))
				{
					$synchronizer = new MPFSynchronizerJoomla();
					$mappings     = [];

					foreach ($rowFields as $rowField)
					{
						if ($rowField->profile_field_mapping)
						{
							$mappings[$rowField->name] = $rowField->profile_field_mapping;
						}
					}

					$data = $synchronizer->getData($userId, $mappings);
				}
			}

			if ($userId && !isset($data['first_name']))
			{
				//Load the name from Joomla default name
				$user = Factory::getUser($userId);
				$name = $user->name;

				if ($name)
				{
					$pos = strpos($name, ' ');

					if ($pos !== false)
					{
						$data['first_name'] = substr($name, 0, $pos);
						$data['last_name']  = substr($name, $pos + 1);
					}
					else
					{
						$data['first_name'] = $name;
						$data['last_name']  = '';
					}
				}
			}

			if ($userId && !isset($data['email']))
			{
				$user          = Factory::getUser($userId);
				$data['email'] = $user->email;
			}

			echo json_encode($data);

			$this->app->close();
		}
	}

	/**
	 * Display form to allow invite users to join group
	 *
	 * @return void
	 */
	public function invite_members()
	{
		$this->input->set('view', 'invite');
		$this->input->set('layout', 'default');
		$this->display();
	}

	/**
	 * Send invitation to members
	 *
	 * @return void
	 */
	public function send_invite()
	{
		$this->csrfProtection();

		$planId      = $this->input->getInt('plan_id', 0);
		$emailsInput = $this->input->getString('emails', '');
		$subject     = $this->input->getString('subject');
		$message     = $this->input->get('message', '', 'raw');

		$emails     = explode("\r\n", $emailsInput);
		$emails     = array_map('trim', $emails);
		$emails     = array_filter($emails, 'strlen');
		$emails     = array_filter($emails, 'Joomla\\CMS\\Mail\\MailHelper::isEmailAddress');
		$canProcess = true;

		if (!$planId)
		{
			$this->app->enqueueMessage(Text::_('OSM_SELECT_PLAN_TO_INVITE'), 'error');

			$canProcess = false;
		}

		if (count($emails) == 0)
		{
			$this->app->enqueueMessage(Text::_('OSM_ENTER_VALID_EMAILS_TO_INVITE'), 'error');
			$canProcess = false;
		}

		if (!$message)
		{
			$this->app->enqueueMessage(Text::_('OSM_ENTER_VALID_EMAIL_MESSAGE'), 'error');

			$canProcess = false;
		}

		if ($canProcess)
		{
			OSMembershipHelper::callOverridableHelperMethod(
				'Mail',
				'sendJoinGroupMembershipInvite',
				[$planId, $emails, $subject, $message]
			);

			$this->setRedirect(
				Route::_(
					'index.php?option=com_osmembership&view=groupmembers&Itemid=' . $this->input->getInt('Itemid', 0),
					false
				),
				Text::_('OSM_INVITE_SENT_TO_USERS_SUCCESSFULLY')
			);
		}
		else
		{
			// Display the form
			$this->display();
		}
	}
}
