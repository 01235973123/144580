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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgOSMembershipCB extends CMSPlugin implements SubscriberInterface
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
			'onMembershipExpire'       => 'onMembershipExpire',
			'onProfileUpdate'          => 'onProfileUpdate',
		];
	}

	/**
	 * Method to get list of custom fields in Community builder used to map with fields in Membership Pro
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onGetFields(Event $event): void
	{
		$db  = $this->db;
		$sql = 'SELECT name AS `value`, name AS `text` FROM #__comprofiler_fields WHERE `table`="#__comprofiler"';
		$db->setQuery($sql);

		$this->addResult($event, $db->loadObjectList());
	}

	/**
	 * Method to get data stored in CB profile of the given user
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onGetProfileData(Event $event): void
	{
		[$userId, $mappings] = array_values($event->getArguments());

		$synchronizer = new MPFSynchronizerCommunitybuilder();

		$this->addResult($event, $synchronizer->getData($userId, $mappings));
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

		if (!$row->user_id)
		{
			return;
		}

		if ($this->params->get('synchronize_data_for_pending_subscription') && str_contains($row->payment_method, 'os_offline'))
		{
			$this->createOrUpdateCBAccount($row);
		}
	}

	/**
	 * Method to create a CB account for subscriber if it does not exist yet
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipActive(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!$row->user_id)
		{
			return;
		}

		$this->createOrUpdateCBAccount($row);

		// Update the block field in users table
		$config = OSMembershipHelper::getConfig();

		if (!$config->send_activation_email)
		{
			$db  = $this->db;
			$sql = 'UPDATE  #__users SET `block` = 0 WHERE id = ' . $row->user_id;
			$db->setQuery($sql);
			$db->execute();

			$this->setCBAuth($row->user_id, 1);
		}
	}

	/**
	 * Method to block the CB account when the subscription record is expired
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipExpire(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$blockUser = $this->params->get('block_user_on_membership_expire', 0);

		if ($row->user_id && $blockUser)
		{
			$activePlanIds = OSMembershipHelperSubscription::getActiveMembershipPlans($row->user_id);

			if (count($activePlanIds) == 2 && $activePlanIds[1] == $row->plan_id)
			{
				$this->setCBAuth($row->user_id, 0);
			}
		}
	}

	/**
	 * Method to update CB profile when subscriber update his profile in Membership Pro
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onProfileUpdate(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!$row->user_id)
		{
			return;
		}

		$this->createOrUpdateCBAccount($row);
	}

	/**
	 * Method to create / update a CB account for subscriber
	 *
	 * @param   OSMembershipTableSubscriber  $row  The subscription record
	 */
	private function createOrUpdateCBAccount($row)
	{
		$config = OSMembershipHelper::getConfig();
		$db     = $this->db;
		$sql    = 'SELECT count(*) FROM `#__comprofiler` WHERE `user_id` = ' . $db->quote($row->user_id);
		$db->setQuery($sql);
		$count = $db->loadResult();
		$sql   = ' SHOW FIELDS FROM #__comprofiler ';
		$db->setQuery($sql);
		$fields    = $db->loadObjectList();
		$fieldList = [];

		for ($i = 0, $n = count($fields); $i < $n; $i++)
		{
			$field       = $fields[$i];
			$fieldList[] = $field->Field;
		}

		//Get list of fields belong to table
		$sql = 'SELECT name, field_mapping FROM #__osmembership_fields WHERE field_mapping != "" AND field_mapping IS NOT NULL AND is_core = 1';
		$db->setQuery($sql);
		$fields      = $db->loadObjectList();
		$fieldValues = [];

		if (count($fields))
		{
			foreach ($fields as $field)
			{
				$fieldName = $field->field_mapping;

				if ($fieldName && in_array($fieldName, $fieldList))
				{
					$fieldValues[$fieldName] = $row->{$field->name};
				}
			}
		}
		$sql = 'SELECT a.field_mapping, b.field_value FROM #__osmembership_fields AS a ' . ' INNER JOIN #__osmembership_field_value AS b ' .
			' ON a.id = b.field_id ' . ' WHERE b.subscriber_id=' . $row->id;

		$db->setQuery($sql);
		$fields = $db->loadObjectList();

		if (count($fields))
		{
			foreach ($fields as $field)
			{
				if ($field->field_mapping && in_array($field->field_mapping, $fieldList))
				{
					//Check if this is a json value
					if (is_string($field->field_value) && is_array(json_decode($field->field_value)))
					{
						$fieldValues[$field->field_mapping] = implode('|*|', json_decode($field->field_value));
					}
					else
					{
						$fieldValues[$field->field_mapping] = $field->field_value;
					}
				}
			}
		}

		$profile            = new stdClass();
		$profile->id        = $row->user_id;
		$profile->user_id   = $row->user_id;
		$profile->firstname = $row->first_name;
		$profile->lastname  = $row->last_name;

		if (!$config->use_cb_api)
		{
			$profile->avatarapproved = 1;
			$profile->confirmed      = 1;
			$profile->registeripaddr = htmlspecialchars($_SERVER['REMOTE_ADDR']);
			$profile->banned         = 0;
			$profile->acceptedterms  = 1;
		}

		foreach ($fieldValues as $fieldName => $value)
		{
			$profile->{$fieldName} = $value;
		}

		if ($count)
		{
			$db->updateObject('#__comprofiler', $profile, 'id');
		}
		else
		{
			$db->insertObject('#__comprofiler', $profile);
		}
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_comprofiler'))
		{
			return;
		}

		$option = $this->app->getInput()->getCmd('option', '');

		if ($option === 'com_comprofiler')
		{
			return;
		}

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		parent::registerListeners();
	}

	/**
	 * Method to block / unblock a CB account
	 *
	 * @param   int  $userId
	 * @param   int  $auth
	 */
	private function setCBAuth($userId, $auth)
	{
		$auth   = $auth ? 1 : 0;
		$userId = (int) $userId;
		$sql    = "UPDATE `#__comprofiler` SET `approved` = $auth, `confirmed` = $auth, `acceptedterms` = $auth WHERE `user_id` = $userId";
		$db     = $this->db;
		$db->setQuery($sql);
		$db->execute();
	}
}
