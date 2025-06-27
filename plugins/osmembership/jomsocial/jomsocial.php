<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class plgOSMembershipJomSocial extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

	/**
	 * Application object.
	 *
	 * @var    CMSApplication
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var DatabaseDriver
	 */
	protected $db;

	/**
	 * Make language files will be loaded automatically.
	 *
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	public static function getSubscribedEvents(): array
	{
		return [
			'onEditSubscriptionPlan'      => 'onEditSubscriptionPlan',
			'onAfterSaveSubscriptionPlan' => 'onAfterSaveSubscriptionPlan',
			'onGetFields'                 => 'onGetFields',
			'onGetProfileData'            => 'onGetProfileData',
			'onMembershipActive'          => 'onMembershipActive',
			'onMembershipExpire'          => 'onMembershipExpire',
			'onProfileUpdate'             => 'onProfileUpdate',
		];
	}

	/**
	 * Method to get list of custom fields in Jomsocial used to map with fields in Membership Pro
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onGetFields(Event $event): void
	{
		$db  = $this->db;
		$sql = 'SELECT fieldcode AS `value`, fieldcode AS `text` FROM #__community_fields WHERE published=1 AND fieldcode != ""';
		$db->setQuery($sql);

		$this->addResult($event, $db->loadObjectList());
	}

	/**
	 * Method to get data stored in Jomsocial profile of the given user
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onGetProfileData(Event $event): void
	{
		[$userId, $mappings] = array_values($event->getArguments());

		$synchronizer = new MPFSynchronizerJomsocial();

		$this->addResult($event, $synchronizer->getData($userId, $mappings));
	}

	/**
	 * Render settings form allows admin to choose what Jomsocial groups subscribers will be assigned to when they sign up for this plan
	 *
	 * Method is called on plan add/edit page
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onEditSubscriptionPlan(Event $event): void
	{
		/* @var OSMembershipTablePlan $row */
		[$row] = array_values($event->getArguments());

		if (!$this->isExecutable())
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);
		$form = ob_get_contents();
		ob_end_clean();

		$result = [
			'title' => Text::_('PLG_OSMEMBERSHIP_JOMSOCIAL_SETTINGS'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * Method to store settings into database
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterSaveSubscriptionPlan(Event $event): void
	{
		/**
		 * @var string                $context
		 * @var OSMembershipTablePlan $row
		 * @var array                 $data
		 * @var                       $isNew
		 */
		[$context, $row, $data, $isNew] = array_values($event->getArguments());

		if (!$this->isExecutable())
		{
			return;
		}

		$params = new Registry($row->params);

		$params->set('jomsocial_group_ids', implode(',', $data['jomsocial_group_ids'] ?? []));
		$params->set('jomsocial_expried_group_ids', implode(',', $data['jomsocial_expried_group_ids'] ?? []));
		$row->params = $params->toString();

		$row->store();
	}

	/**
	 * Method to create Jomsocial account for subscriber and assign him to selected Jomsocial groups when subscription is active
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

		$db  = $this->db;
		$sql = 'SELECT COUNT(*) FROM #__community_users WHERE userid = ' . $row->user_id;
		$db->setQuery($sql);
		$count = $db->loadResult();

		if (!$count)
		{
			$sql = 'INSERT INTO #__community_users(userid) VALUES(' . $row->user_id . ')';
			$db->setQuery($sql);
			$db->execute();
		}

		$sql = 'SELECT id, fieldcode FROM #__community_fields WHERE published=1 AND fieldcode != ""';
		$db->setQuery($sql);
		$rowFields = $db->loadObjectList();
		$fieldList = [];

		foreach ($rowFields as $rowField)
		{
			$fieldList[$rowField->fieldcode] = $rowField->id;
		}

		$sql = 'SELECT name, field_mapping FROM #__osmembership_fields WHERE field_mapping != "" AND field_mapping IS NOT NULL AND is_core = 1';
		$db->setQuery($sql);
		$fields = $db->loadObjectList();

		$fieldValues = [];

		foreach ($fields as $field)
		{
			$fieldName = $field->field_mapping;

			if ($fieldName)
			{
				$fieldValues[$fieldName] = $row->{$field->name};
			}
		}

		$sql = 'SELECT a.field_mapping, b.field_value FROM #__osmembership_fields AS a '
			. ' INNER JOIN #__osmembership_field_value AS b '
			. ' ON a.id = b.field_id '
			. ' WHERE b.subscriber_id=' . $row->id;
		$db->setQuery($sql);
		$fields = $db->loadObjectList();

		foreach ($fields as $field)
		{
			if ($field->field_mapping)
			{
				$fieldValues[$field->field_mapping] = $field->field_value;
			}
		}

		$query = $db->getQuery(true);

		if (count($fieldValues))
		{
			foreach ($fieldValues as $fieldCode => $fieldValue)
			{
				if (isset($fieldList[$fieldCode]))
				{
					$fieldId = $fieldList[$fieldCode];

					if ($fieldId)
					{
						// Delete old data of exists
						$query->clear()
							->delete('#__community_fields_values')
							->where('user_id = ' . $row->user_id)
							->where('field_id = ' . (int) $fieldId);
						$db->setQuery($query)
							->execute();

						if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
						{
							$fieldValue = implode(',', json_decode($fieldValue));
						}

						$fieldValue = $db->quote($fieldValue);

						$sql = "INSERT INTO #__community_fields_values(user_id, field_id, `value`, `access`) VALUES($row->user_id, $fieldId, $fieldValue, 1)";
						$db->setQuery($sql);
						$db->execute();
					}
				}
			}
		}

		$plan = new OSMembershipTablePlan($db);
		$plan->load($row->plan_id);
		$params = new Registry($plan->params);
		$groups = explode(',', $params->get('jomsocial_group_ids'));
		$groups = array_filter(ArrayHelper::toInteger($groups));

		if (count($groups))
		{
			$sql = 'REPLACE INTO `#__community_groups_members` (`memberid`,`groupid`,`approved`,`permissions`) VALUES ';

			$values = [];

			foreach ($groups as $group)
			{
				$values[] = '(' . $db->Quote($row->user_id) . ', ' . $db->Quote($group) . ', 1, 0)';
			}

			$sql .= implode(', ', $values);

			$db->setQuery($sql);
			$db->execute();
		}
	}

	/**
	 * Run when a membership activated
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

		$db  = $this->db;
		$sql = 'SELECT COUNT(*) FROM #__community_users WHERE userid=' . $row->user_id;
		$db->setQuery($sql);
		$count = $db->loadResult();

		if (!$count)
		{
			$sql = 'INSERT INTO #__community_users(userid) VALUES(' . $row->user_id . ')';
			$db->setQuery($sql);
			$db->execute();
		}

		$sql = 'SELECT id, fieldcode FROM #__community_fields WHERE published=1 AND fieldcode != ""';
		$db->setQuery($sql);
		$rowFields = $db->loadObjectList();
		$fieldList = [];

		foreach ($rowFields as $rowField)
		{
			$fieldList[$rowField->fieldcode] = $rowField->id;
		}

		$sql = 'SELECT name, field_mapping FROM #__osmembership_fields WHERE field_mapping != "" AND field_mapping IS NOT NULL AND is_core = 1';
		$db->setQuery($sql);
		$fields      = $db->loadObjectList();
		$fieldValues = [];

		foreach ($fields as $field)
		{
			$fieldName = $field->field_mapping;

			if ($fieldName)
			{
				$fieldValues[$fieldName] = $row->{$field->name};
			}
		}

		$sql = 'SELECT a.field_mapping, b.field_value FROM #__osmembership_fields AS a '
			. ' INNER JOIN #__osmembership_field_value AS b '
			. ' ON a.id = b.field_id '
			. ' WHERE b.subscriber_id=' . $row->id;
		$db->setQuery($sql);
		$fields = $db->loadObjectList();

		foreach ($fields as $field)
		{
			if ($field->field_mapping)
			{
				$fieldValues[$field->field_mapping] = $field->field_value;
			}
		}

		if (count($fieldValues))
		{
			$query = $db->getQuery(true);

			foreach ($fieldValues as $fieldCode => $fieldValue)
			{
				if (isset($fieldList[$fieldCode]))
				{
					$fieldId = $fieldList[$fieldCode];

					if ($fieldId)
					{
						// Delete old data of exists
						$query->clear()
							->delete('#__community_fields_values')
							->where('user_id = ' . $row->user_id)
							->where('field_id = ' . (int) $fieldId);
						$db->setQuery($query)
							->execute();

						if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
						{
							$fieldValue = implode(',', json_decode($fieldValue));
						}

						$fieldValue = $db->quote($fieldValue);
						$sql        = "REPLACE INTO #__community_fields_values(user_id, field_id, `value`, `access`) VALUES($row->user_id, $fieldId, $fieldValue, 1)";
						$db->setQuery($sql);
						$db->execute();
					}
				}
			}
		}

		$plan = new OSMembershipTablePlan($db);
		$plan->load($row->plan_id);
		$params = new Registry($plan->params);
		$groups = explode(',', $params->get('jomsocial_group_ids'));
		$groups = array_filter(ArrayHelper::toInteger($groups));

		if (count($groups))
		{
			$sql = 'REPLACE INTO `#__community_groups_members` (`memberid`,`groupid`,`approved`,`permissions`) VALUES ';

			$values = [];

			foreach ($groups as $group)
			{
				$values[] = '(' . $db->Quote($row->user_id) . ', ' . $db->Quote($group) . ', 1, 0)';
			}

			$sql .= implode(', ', $values);

			$db->setQuery($sql);
			$db->execute();
		}
	}

	/**
	 * Run when a membership expiried die
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipExpire(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!$row->user_id)
		{
			return;
		}

		$plan = new OSMembershipTablePlan($this->db);
		$plan->load($row->plan_id);
		$params = new Registry($plan->params);
		$groups = explode(',', $params->get('jomsocial_expried_group_ids'));
		$groups = array_filter(ArrayHelper::toInteger($groups));

		$db = $this->db;

		foreach ($groups as $group)
		{
			$sql = 'DELETE FROM #__community_groups_members WHERE groupid=' . $group . ' AND memberid=' . $row->user_id;
			$db->setQuery($sql);
			$db->execute();
		}
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_community'))
		{
			return;
		}

		parent::registerListeners();
	}

	/**
	 * Method to check if the plugin is executable
	 *
	 * @return bool
	 */
	private function isExecutable()
	{
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Display form allows users to change setting for this subscription plan
	 *
	 * @param   OSMembershipTablePlan  $row
	 */
	private function drawSettingForm($row)
	{
		$sql = 'SELECT id, name FROM #__community_groups WHERE published = 1 ORDER BY name ';
		$this->db->setQuery($sql);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('Choose Groups'), 'id', 'name');
		$options   = array_merge($options, $this->db->loadObjectList());

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'form');
	}
}
