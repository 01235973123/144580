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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use OSSolution\MembershipPro\Admin\Event\Profile\GetProfileData;

trait OSMembershipControllerData
{
	/**
	 * Get profile data of the subscriber, using for json format
	 */
	public function get_profile_data()
	{
		$app    = $this->app;
		$userId = $app->getInput()->getInt('user_id', 0);
		$planId = $app->getInput()->getInt('plan_id');
		$data   = [];

		if (OSMembershipHelper::canBrowseUsersList() && $userId && $planId)
		{
			$data = $this->getUserData($userId, $planId);
		}

		foreach ($data as $key => $value)
		{
			if (is_string($value) && is_array(json_decode($value)))
			{
				$data[$key] = json_decode($value);
			}
		}

		echo json_encode($data);

		$app->close();
	}

	/**
	 * Method to get existing user data base on existing username to populate group member form
	 *
	 * @throws Exception
	 */
	public function get_existing_user_data()
	{
		$app      = $this->app;
		$username = $app->getInput()->getString('username');
		$planId   = $app->getInput()->getInt('plan_id');
		$data     = [];

		// Get user_id from given username
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('id')
			->from('#__users')
			->where('username = ' . $db->quote($username));
		$db->setQuery($query);
		$userId = (int) $db->loadResult();

		if (OSMembershipHelper::getManageGroupMemberPermission() && $userId && $planId)
		{
			$data = $this->getUserData($userId, $planId);
		}

		echo json_encode($data);

		$app->close();
	}

	/**
	 * Get data for a given user, used for auto-completion
	 *
	 * @param   int  $userId
	 * @param   int  $planId
	 *
	 * @return array
	 */
	protected function getUserData($userId, $planId)
	{
		$config = OSMembershipHelper::getConfig();
		$data   = [];

		$rowFields = OSMembershipHelper::getProfileFields($planId, true);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId)
			->where('plan_id = ' . $planId);
		$db->setQuery($query);
		$rowProfile = $db->loadObject();

		if (!$rowProfile)
		{
			$query->clear()
				->select('*')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $userId . ' AND is_profile=1');
			$db->setQuery($query);
			$rowProfile = $db->loadObject();
		}

		if (!$rowProfile)
		{
			$query->clear()
				->select('*')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $userId)
				->order('id DESC');
			$db->setQuery($query);
			$rowProfile = $db->loadObject();
		}

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

		if (!count($data) && PluginHelper::isEnabled('osmembership', 'userprofile') && !$config->cb_integration)
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

		if ($userId && !isset($data['first_name']))
		{
			//Load the name from Joomla default name
			$user = Factory::getUser((int) $userId);
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
			$user          = Factory::getUser((int) $userId);
			$data['email'] = $user->email;
		}

		foreach ($rowFields as $rowField)
		{
			if (!$rowField->populate_from_previous_subscription)
			{
				unset($data[$rowField->name]);
			}
		}

		return $data;
	}
}
