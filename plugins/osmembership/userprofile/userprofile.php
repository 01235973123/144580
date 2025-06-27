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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgOSMembershipUserprofile extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Database object.
	 *
	 * @var DatabaseDriver
	 */
	protected $db;

	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterStoreSubscription' => 'onAfterStoreSubscription',
			'onMembershipActive'       => 'onMembershipActive',
			'onMembershipUpdate'       => 'onMembershipUpdate',
			'onProfileUpdate'          => 'onProfileUpdate',
		];
	}

	/**
	 * Run when a membership stored
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterStoreSubscription(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$this->synchronizeProfileData($row, 'onAfterStoreSubscription');
	}

	/**
	 * Plugin triggered when user update his profile
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onProfileUpdate(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$this->synchronizeProfileData($row, 'onProfileUpdate');
	}

	/**
	 * Plugin triggered when membership active
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipActive(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$config = OSMembershipHelper::getConfig();

		if ($config->create_account_when_membership_active === '1')
		{
			$this->synchronizeProfileData($row, 'onMembershipActive');
		}
	}

	/**
	 * Plugin triggered when user update his profile
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipUpdate(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if ($this->params->get('update_profile_data_when_admin_update_subscription'))
		{
			$this->synchronizeProfileData($row, 'onMembershipUpdate');
		}
	}

	/**
	 * Method to synchronize subscription data from user profile
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   string                       $event
	 *
	 * @return void
	 */
	private function synchronizeProfileData($row, $event = ''): void
	{
		if (!$row->user_id)
		{
			return;
		}

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		$db    = $this->db;
		$query = $db->getQuery(true);

		$userId = $row->user_id;

		// Update Name of users based on first name and last name from profile
		if ($this->params->get('update_name', 1))
		{
			$user       = Factory::getUser((int) $userId);
			$user->name = rtrim($row->first_name . ' ' . $row->last_name);
			$user->save(true);
		}

		// Get subscribers data
		if ($event === 'onMembershipUpdate')
		{
			$rowFields = OSMembershipHelper::getProfileFields($row->plan_id);
		}
		else
		{
			$rowFields = OSMembershipHelper::getProfileFields($row->plan_id, true, null, $row->act);
		}

		$subscriberData = OSMembershipHelper::getProfileData($row, $row->plan_id, $rowFields);

		if (!empty($subscriberData['country']) && !empty($subscriberData['state']))
		{
			$subscriberData['state'] = OSMembershipHelper::getStateName(
				$subscriberData['country'],
				$subscriberData['state']
			);
		}

		$userProfilePluginEnabled = PluginHelper::isEnabled('user', 'profile');
		$profileFields            = [
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
			'dob',
		];
		$userFields               = OSMembershipHelper::getUserFields();
		$userFieldsName           = array_keys($userFields);
		$profileFieldsMapping     = [];
		$userFieldsMapping        = [];

		foreach ($rowFields as $rowField)
		{
			if (!$rowField->profile_field_mapping)
			{
				continue;
			}

			if ($userProfilePluginEnabled && in_array($rowField->profile_field_mapping, $profileFields))
			{
				$profileFieldsMapping[$rowField->profile_field_mapping] = $rowField->name;

				continue;
			}

			if (in_array($rowField->profile_field_mapping, $userFieldsName))
			{
				$userFieldsMapping[$rowField->profile_field_mapping] = $rowField->name;
			}
		}

		// Store user profile data
		if (count($profileFieldsMapping) > 0)
		{
			//Delete old profile data
			$fields = $keys = array_keys($profileFieldsMapping);

			for ($i = 0, $n = count($keys); $i < $n; $i++)
			{
				$keys[$i] = 'profile.' . $keys[$i];
			}

			$query->delete('#__user_profiles')
				->where('user_id = ' . $userId)
				->where('profile_key IN (' . implode(',', $db->quote($keys)) . ')');
			$db->setQuery($query);
			$db->execute();

			$order = 1;

			$query->clear()
				->insert('#__user_profiles');

			foreach ($fields as $field)
			{
				$fieldMapping = $profileFieldsMapping[$field];

				if (isset($subscriberData[$fieldMapping]))
				{
					$value = $subscriberData[$fieldMapping];
				}
				else
				{
					$value = '';
				}

				$query->values(implode(',', $db->quote([$userId, 'profile.' . $field, json_encode($value), $order++])));
			}

			$db->setQuery($query);
			$db->execute();
		}

		if (count($userFields) > 0)
		{
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');

			/* @var FieldsModelField $model */
			$model = BaseDatabaseModel::getInstance('Field', 'FieldsModel', ['ignore_request' => true]);

			foreach ($userFields as $field)
			{
				$fieldName = $field->name;

				if (isset($userFieldsMapping[$fieldName]))
				{
					$fieldMapping = $userFieldsMapping[$fieldName];

					$fieldValue = $subscriberData[$fieldMapping] ?? '';

					if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
					{
						$fieldValue = json_decode($fieldValue);
					}

					$model->setFieldValue($field->id, $userId, $fieldValue);
				}
			}
		}
	}
}
