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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use OSSolution\MembershipPro\Admin\Event\Profile\GetProfileData;

class OSMembershipViewGroupHtml extends MPFViewHtml
{
	use OSMembershipViewRegister;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * The plan which the group belong to
	 *
	 * @var stdClass
	 */
	protected $plan;

	/**
	 * The group admin subscription record
	 *
	 * @var stdClass
	 */
	protected $group;

	/**
	 * The current logged in user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The ID of the current logged in user
	 *
	 * @var int
	 */
	protected $userId;

	/**
	 * The join group form
	 *
	 * @var MPFForm
	 */
	protected $form;

	/**
	 * The message displayed above join group form
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Bootstrap Helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * Extra login buttons. This is not supported on this view, but defineded here to prevent fatal error
	 *
	 * @var array
	 */
	protected $extraLoginButtons = [];

	/**
	 * Display the view
	 *
	 * @throws Exception
	 */
	public function display()
	{
		if ($this->getLayout() == 'complete')
		{
			$this->displayJoinGroupComplete();

			return;
		}

		$config = OSMembershipHelper::getConfig();
		$user   = Factory::getApplication()->getIdentity();

		/* @var OSMembershipModelGroup $model */
		$model = $this->getModel();

		$group = $model->getData();

		// Validate and make user users are still allowed to join this plan
		$errors = $model->validateAddingMembersToGroup($group);

		if (count($errors))
		{
			foreach ($errors as $error)
			{
				Factory::getApplication()->enqueueMessage($error);
			}

			return;
		}

		$plan = OSMembershipHelperDatabase::getPlan($group->plan_id);

		// Get custom fields
		$rowFields = $model->getGroupMemberFields($group->plan_id);

		$data = $this->getFormData($this->input, $user, $group->plan_id, $rowFields, $config);
		$form = new MPFForm($rowFields);
		$form->setData($data)->bindData(true);
		$form->buildFieldsDependency();

		$this->loadCaptcha($config, $user);

		$messageObj  = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if ($fieldSuffix && OSMembershipHelper::isValidMessage($messageObj->{'join_group_form_message' . $fieldSuffix}))
		{
			$message = $messageObj->{'join_group_form_message' . $fieldSuffix};
		}
		else
		{
			$message = $messageObj->join_group_form_message;
		}

		$groupAdmin = Factory::getUser($group->user_id);

		$replaces                     = [];
		$replaces['plan_title']       = $plan->title;
		$replaces['group_admin_name'] = $groupAdmin->name;

		$message = OSMembershipHelper::replaceUpperCaseTags($message, $replaces);

		$message = HTMLHelper::_('content.prepare', $message);

		// Assign variables to template
		$this->config          = $config;
		$this->plan            = $plan;
		$this->group           = $group;
		$this->user            = $user;
		$this->userId          = $user->id;
		$this->form            = $form;
		$this->message         = $message;
		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();

		$this->setLayout('default');

		parent::display();
	}

	/**
	 * Display Join Group Complete Form
	 */
	protected function displayJoinGroupComplete()
	{
		$app              = Factory::getApplication();
		$config           = OSMembershipHelper::getConfig();
		$subscriptionCode = $this->input->getString('subscription_code');

		if (!$subscriptionCode)
		{
			$app->enqueueMessage(Text::_('Invalid subscription code'));

			return;
		}

		// Get subscriber information
		$db    = $this->model->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('subscription_code = ' . $db->quote($subscriptionCode));
		$db->setQuery($query);
		$rowSubscriber = $db->loadObject();

		if (!$rowSubscriber)
		{
			$app->enqueueMessage(Text::_('Invalid subscription code'));

			return;
		}

		$messageObj  = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$messageObj->{'join_group_group_admin_email_body' . $fieldSuffix}
			))
		{
			$message = $messageObj->{'join_group_complete_message' . $fieldSuffix};
		}
		else
		{
			$message = $messageObj->join_group_complete_message;
		}

		$subscriptionDetail = OSMembershipHelper::getEmailContent($config, $rowSubscriber);
		$message            = str_replace('[SUBSCRIPTION_DETAIL]', $subscriptionDetail, $message);
		$replaces           = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'buildTags',
			[$rowSubscriber, $config]
		);

		// Group Admin
		if ($rowSubscriber->group_admin_id > 0)
		{
			$groupAdmin                       = Factory::getUser($rowSubscriber->group_admin_id);
			$replaces['group_admin_name']     = $groupAdmin->name;
			$replaces['group_admin_username'] = $groupAdmin->username;
			$replaces['group_admin_email']    = $groupAdmin->email;
		}
		else
		{
			$replaces['group_admin_name']     = '';
			$replaces['group_admin_username'] = '';
			$replaces['group_admin_email']    = '';
		}

		$message = OSMembershipHelper::replaceUpperCaseTags($message, $replaces);

		$this->message = HTMLHelper::_('content.prepare', $message);

		parent::display();
	}

	/**
	 * Get data using for subscription form
	 *
	 * @param   MPFInput  $input
	 * @param   User      $user
	 * @param   int       $planId
	 * @param   array     $rowFields
	 * @param   stdClass  $config
	 *
	 * @return array
	 */
	protected function getFormData($input, $user, $planId, $rowFields, $config)
	{
		$userId = $user->id;

		if ($input->getInt('validation_error', 0))
		{
			$data = $input->getData();
		}
		else
		{
			$data = [];

			if ($userId)
			{
				// Check to see if this user has profile data already
				$db    = $this->model->getDbo();
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__osmembership_subscribers')
					->where('user_id=' . $userId . ' AND is_profile=1');
				$db->setQuery($query);
				$rowProfile = $db->loadObject();

				if ($rowProfile)
				{
					$data = OSMembershipHelper::getProfileData($rowProfile, $planId, $rowFields);
				}
				else
				{
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

					$results = Factory::getApplication()->triggerEvent($event->getName(), $event);

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

					// Convert from state name to start 2 code
					if (!empty($data['country']) && !empty($data['state']) && strlen($data['state']) > 2)
					{
						$data['state'] = OSMembershipHelper::getStateCode($data['country'], $data['state']);
					}
				}
			}
			else
			{
				$data = $input->getData();
			}
		}

		if ($userId && !isset($data['first_name']))
		{
			// Load the name from Joomla default name
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
			$data['email'] = $user->email;
		}

		if (!isset($data['country']) || !$data['country'])
		{
			$data['country'] = $config->default_country;
		}

		// Handle Populate Data From Previous Subscription from custom field settings
		foreach ($rowFields as $rowField)
		{
			if (!$rowField->populate_from_previous_subscription && isset($data[$rowField->name]))
			{
				unset($data[$rowField->name]);
			}
		}

		$data += $input->get->getData();

		return $data;
	}
}
