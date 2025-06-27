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
use Joomla\CMS\Mail\MailHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\String\StringHelper;

class OSMembershipModelImport extends MPFModel
{
	use OSMembershipModelSubscriptiontrait;

	/**
	 * @param $file
	 * @param $fileName
	 *
	 * @return []
	 *
	 * @throws Exception
	 */
	public function store($file, $fileName = '')
	{
		$app = Factory::getApplication();
		/* @var DatabaseDriver $db */
		$db     = Factory::getContainer()->get('db');
		$config = OSMembershipHelper::getConfig();
		$model  = new OSMembershipModelApi();

		// Get data from imported files
		$subscribers = OSMembershipHelperData::getDataFromFile($file, $fileName);

		// Get list of plans
		$query = $db->getQuery(true)
			->select('id, title')
			->from('#__osmembership_plans');
		$db->setQuery($query);
		$rows  = $db->loadObjectList();
		$plans = [];

		foreach ($rows as $row)
		{
			$plans[StringHelper::strtolower(trim($row->title))] = $row->id;
		}

		// Get list of custom fields and it's field type
		$query->clear()
			->select('name')
			->from('#__osmembership_fields')
			->where('(fieldtype = "Checkboxes" OR (fieldtype="List" AND multiple = 1))')
			->where('published = 1');
		$db->setQuery($query);
		$checkboxesFields = $db->loadColumn();

		$timezone      = Factory::getApplication()->get('offset');
		$dateFields    = ['created_date', 'payment_date', 'from_date', 'to_date'];
		$ids           = [];
		$groupAdminIds = [];

		foreach ($subscribers as $subscriber)
		{
			foreach ($subscriber as $key => $value)
			{
				if (is_string($value))
				{
					$subscriber[$key] = trim($value);
				}
			}

			if (empty($subscriber['plan']))
			{
				continue;
			}

			if (empty($subscriber['email']) || !MailHelper::isEmailAddress($subscriber['email']))
			{
				continue;
			}

			if (empty($subscriber['username']) && $config->use_email_as_username && $config->registration_integration)
			{
				$subscriber['username'] = $subscriber['email'];
			}

			if (!empty($subscriber['group_admin']))
			{
				if (isset($groupAdminIds[$subscriber['group_admin']]))
				{
					$subscriber['group_admin_id'] = $groupAdminIds[$subscriber['group_admin']];
				}
				else
				{
					$query->clear()
						->select('id')
						->from('#__users')
						->where('username = ' . $db->quote($subscriber['group_admin']));
					$db->setQuery($query);
					$groupAdminId = $db->loadResult();

					if ($groupAdminId)
					{
						$subscriber['group_admin_id']              = $groupAdminId;
						$groupAdminIds[$subscriber['group_admin']] = $groupAdminId;
					}
				}
			}

			// Convert date fields to Y-m-d H:i:s format
			foreach ($dateFields as $field)
			{
				if (!empty($subscriber[$field]))
				{
					try
					{
						$date = Factory::getDate($subscriber[$field], $timezone);

						if (in_array($field, ['created_date', 'from_date']))
						{
							// Data is not in YYYY-MM-DD HH:MM:SS format
							if (strlen($subscriber[$field]) <= 13)
							{
								$date->setTime(0, 0, 0);
							}
						}
						else
						{
							// Data is not in YYYY-MM-DD HH:MM:SS format
							if (strlen($subscriber[$field]) <= 13)
							{
								$date->setTime(23, 59, 59);
							}
						}

						$subscriber[$field] = $date->toSql();
					}
					catch (Exception $e)
					{
						$app->enqueueMessage(
							$subscriber[$field] . ' for field ' . $field . ' is not a correct date value'
						);
					}
				}
			}

			if (is_numeric($subscriber['plan']))
			{
				$planId = (int) $subscriber['plan'];
			}
			else
			{
				// Get plan Id from plan title
				$planTitle = StringHelper::strtolower($subscriber['plan']);
				$planId    = $plans[$planTitle] ?? 0;
			}

			$subscriber['plan_id'] = $planId;

			if (empty($subscriber['user_id']))
			{
				$subscriber['user_id'] = 0;
			}

			// Get user_id from username of username is given
			if (empty($subscriber['user_id']) && !empty($subscriber['username']))
			{
				$username = $db->quote($subscriber['username']);
				$email    = $db->quote($subscriber['email']);
				$query->clear()
					->select('id')
					->from('#__users')
					->where("(username = $username OR email = $email)");
				$db->setQuery($query);
				$subscriber['user_id'] = (int) $db->loadResult();
			}

			if (empty($subscriber['user_id']) && !empty($subscriber['email']))
			{
				// Try to get user_id from email
				$query->clear()
					->select('id')
					->from('#__users')
					->where('email = ' . $db->quote($subscriber['email']));
				$db->setQuery($query);
				$subscriber['user_id'] = (int) $db->loadResult();
			}

			// Support importing data from Checkboxes using comma separated value
			foreach ($checkboxesFields as $field)
			{
				if (empty($subscriber[$field]))
				{
					continue;
				}

				$fieldValue = $subscriber[$field];

				// Already in JSON format, continue
				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					continue;
				}

				// Convert data to json format before importing into database
				$subscriber[$field] = json_encode(array_map('trim', explode(',', $fieldValue)));
			}

			// Call API model to save the subscription
			$errors = $model->store($subscriber);

			if (is_array($errors))
			{
				foreach ($errors as $error)
				{
					$app->enqueueMessage($error, 'warning');
				}

				continue;
			}
			$ids[] = $errors->id;
		}

		return $ids;
	}

	/**
	 * Import subscribers from Joomla core users
	 *
	 * @param   int  $planId
	 * @param   int  $start
	 * @param   int  $limit
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	public function importFromJoomla($planId, $start = 0, $limit = 0)
	{
		$app = Factory::getApplication();
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$model = new OSMembershipModelApi();

		$query = $db->getQuery(true)
			->clear()
			->select('id, name, email')
			->from('#__users')
			->order('id');

		$groupId = $app->getInput()->getInt('group_id');

		if ($groupId)
		{
			$query->where('id IN (SELECT user_id FROM #__user_usergroup_map WHERE group_id = ' . $groupId . ')');
		}
		else
		{
			$query->where('id IN (SELECT user_id FROM #__user_usergroup_map WHERE group_id NOT IN (7, 8))');
		}

		if ($limit)
		{
			$db->setQuery($query, $start, $limit);
		}
		else
		{
			$db->setQuery($query);
		}

		$users = $db->loadObjectList();

		$imported = 0;

		foreach ($users as $user)
		{
			$query->clear()
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('plan_id = ' . $planId)
				->where('user_id = ' . $user->id);
			$db->setQuery($query);
			$total = $db->loadResult();

			if ($total)
			{
				continue;
			}

			$data = [];

			$data['plan_id'] = $planId;
			$data['user_id'] = $user->id;

			// Detect first name and last name
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

			$data['email'] = $user->email;

			$errors = $model->store($data);

			if (is_array($errors))
			{
				foreach ($errors as $error)
				{
					$app->enqueueMessage($error, 'warning');
				}

				continue;
			}

			$imported++;
		}

		return $imported;
	}
}
