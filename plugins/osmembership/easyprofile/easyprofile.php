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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgOSMembershipEasyprofile extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	public static function getSubscribedEvents(): array
	{
		return [
			'onGetFields'              => 'onGetFields',
			'onGetProfileData'         => 'onGetProfileData',
			'onAfterStoreSubscription' => 'onAfterStoreSubscription',
			'onMembershipActive'       => 'onMembershipActive',
			'onProfileUpdate'          => 'onProfileUpdate',
		];
	}

	/**
	 * Method to get data stored in EasyProfile of the given user
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onGetProfileData(Event $event): void
	{
		[$userId, $mappings] = array_values($event->getArguments());

		$synchronizer = new MPFSynchronizerEasyprofile();

		$this->addResult($event, $synchronizer->getData($userId, $mappings));
	}

	/**
	 * Method to get list of custom fields in Easyprofile used to map with fields in Membership Pro
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onGetFields(Event $event): void
	{
		$fields = array_keys($this->db->getTableColumns('#__jsn_users'));
		$fields = array_diff($fields, ['id', 'params']);

		$options = [];

		foreach ($fields as $field)
		{
			$options[] = HTMLHelper::_('select.option', $field, $field);
		}

		$this->addResult($event, $options);
	}

	/**
	 * Method to create a CB account for subscriber if it does not exist yet
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterStoreSubscription(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$this->storeEasyprofile($row);
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

		$this->storeEasyprofile($row);
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
			$this->storeEasyprofile($row);
		}
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_jsn'))
		{
			return;
		}

		parent::registerListeners();
	}

	/**
	 * Method to create or update easyprofile data
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	private function storeEasyprofile($row)
	{
		if (!$row->user_id)
		{
			return;
		}

		// Backup state data
		$state = $row->state;

		// Convert to state name because easy profile expects to receive name of the state
		$row->state = OSMembershipHelper::getStateName($row->country, $row->state);

		// Check if user exist
		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select('a.id')->from('#__jsn_users AS a')->where('a.id = ' . $row->user_id);
		$db->setQuery($query);
		$profileId = $db->loadResult();

		// Get list of fields in #__jsn_users table
		$columns = array_keys($db->getTableColumns('#__jsn_users'));

		// Get custom fields data (both from core and none-core fields
		$fieldValues = [];

		$query->clear()
			->select('name, field_mapping')
			->from('#__osmembership_fields')
			->where('field_mapping != ""')
			->where('field_mapping IS NOT NULL')
			->where('is_core = 1')
			->where('published = 1');
		$db->setQuery($query);
		$fields = $db->loadObjectList();

		if (count($fields))
		{
			foreach ($fields as $field)
			{
				$fieldName = $field->field_mapping;

				if ($fieldName && in_array($fieldName, $columns))
				{
					$fieldValues[$fieldName] = $row->{$field->name};
				}
			}
		}

		$query->clear()
			->select('a.field_mapping, b.field_value')
			->from('#__osmembership_fields AS a')
			->innerJoin('#__osmembership_field_value AS b ON a.id = b.field_id')
			->where('b.subscriber_id = ' . $row->id);
		$db->setQuery($query);
		$fields = $db->loadObjectList();

		if (count($fields))
		{
			foreach ($fields as $field)
			{
				if ($field->field_mapping)
				{
					$fieldValues[$field->field_mapping] = $field->field_value;
				}
			}
		}

		if (empty($fieldValues))
		{
			return;
		}

		// Write Jsn User
		if ($profileId)
		{
			// Update User
			$query = $db->getQuery(true);
			$query->update('#__jsn_users');

			foreach ($fieldValues as $key => $value)
			{
				$query->set($db->quoteName($key) . ' = ' . $db->quote($value));
			}

			$query->where('id = ' . $row->user_id);
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			// New User
			$fields = [];
			$values = [];

			foreach ($fieldValues as $key => $value)
			{
				$fields[] = $db->quoteName($key);
				$values[] = $db->quote($value);
			}

			$query = 'INSERT INTO #__jsn_users(id,' . implode(', ', $fields) . ') VALUES(' . $row->user_id . ', ' . implode(', ', $values) . ')';
			$db->setQuery($query);
			$db->execute();
		}

		// Restore original state data
		$row->state = $state;
	}
}
