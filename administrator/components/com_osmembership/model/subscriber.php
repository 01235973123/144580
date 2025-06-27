<?php

/**
 * @package        Joomla
 * @subpackage     OS Membership
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use OSSolution\MembershipPro\Admin\Event\Profile\ProfileUpdate;

class OSMembershipModelSubscriber extends MPFModelAdmin
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['table'] = '#__osmembership_subscribers';

		parent::__construct($config);
	}

	/**
	 * Load profile data
	 *
	 * @return mixed
	 */
	public function loadData()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, b.username')
			->from('#__osmembership_subscribers AS a ')
			->leftJoin('#__users AS b ON a.user_id=b.id')
			->where('a.id=' . (int) $this->state->id);
		$db->setQuery($query);

		$this->data = $db->loadObject();
	}

	/**
	 * Method to store a subscription record
	 *
	 * @param   MPFInput  $input
	 * @param   array     $ignore
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function store($input, $ignore = [])
	{
		$db   = $this->getDbo();
		$row  = $this->getTable('Subscriber');
		$data = $input->getData();
		$row->load($data['id']);

		if (isset($data['password']))
		{
			$userData = [];
			$query    = $db->getQuery(true);
			$query->select('COUNT(*)')
				->from('#__users')
				->where('email=' . $db->quote($data['email']))
				->where('id!=' . (int) $row->user_id);
			$db->setQuery($query);
			$total = $db->loadResult();
			if (!$total)
			{
				$userData['email'] = $data['email'];
			}
			if ($data['password'])
			{
				$userData['password2'] = $userData['password'] = $data['password'];
			}
			if (count($userData))
			{
				$user = Factory::getUser((int) $row->user_id);
				$user->bind($userData);
				$user->save(true);
			}
		}

		$row->bind($data);

		if (!$row->check())
		{
			throw new Exception($row->getError());
		}

		if (!$row->store())
		{
			throw new Exception($row->getError());
		}

		//Store custom field data for this profile record
		if (OSMembershipHelper::isUniquePlan($row->user_id))
		{
			$planId = $row->plan_id;
		}
		else
		{
			$planId = 0;
		}

		$rowFields  = OSMembershipHelper::getProfileFields($planId, true);
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

		reset($rowFields);

		$form = new MPFForm($formFields);
		$form->storeFormData($row->id, $data);

		//Synchronize profile data of other subscription records from this subscriber
		OSMembershipHelperSubscription::synchronizeProfileData($row, $rowFields);

		//Trigger event	onProfileUpdate event
		PluginHelper::importPlugin('osmembership');

		$event = new ProfileUpdate(['row' => $row]);

		Factory::getApplication()->triggerEvent($event->getName(), $event);

		return true;
	}
}
