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
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * OSMembership Plugin controller
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipControllerGroupmember extends OSMembershipController
{
	use MPFControllerDownload;

	/**
	 * Export registrants into a CSV file
	 */
	public function export()
	{
		set_time_limit(0);

		/* @var OSMembershipModelSubscriptions $model */
		$model = $this->getModel('groupmembers');
		$model->set('limitstart', 0)
			->set('limit', 0);

		$rows = $model->getData();

		if (count($rows) == 0)
		{
			$this->setMessage(Text::_('There are no subscription records to export'));
			$this->setRedirect('index.php?option=com_osmembership&view=subscriptions');

			return;
		}

		$planId = (int) $model->get('filter_plan_id');

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('id, name, is_core')
			->from('#__osmembership_fields')
			->where('published = 1')
			->where('hide_on_export = 0')
			->where('show_on_group_member_form = 1')
			->order('ordering');

		if ($planId > 0)
		{
			$negPlanId = -1 * $planId;
			$query->where(
				'(plan_id = 0 OR id IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $planId . ' OR (plan_id < 0 AND plan_id != ' . $negPlanId . ')))'
			);
		}

		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		$fieldIds = [];

		foreach ($rowFields as $rowField)
		{
			if ($rowField->is_core)
			{
				continue;
			}

			$fieldIds[] = $rowField->id;
		}

		$fieldValues = $model->getFieldsData($fieldIds);

		$fields = [
			'id',
			'plan',
			'username',
			'group_admin',
		];

		$i = 0;

		foreach ($rowFields as $rowField)
		{
			$fields[] = $rowField->name;

			if ($rowField->is_core)
			{
				unset($rowFields[$i]);
			}

			$i++;
		}

		$fields[] = 'created_date';
		$fields[] = 'from_date';
		$fields[] = 'to_date';
		$fields[] = 'published';
		$fields[] = 'membership_id';

		$dateFields = ['created_date', 'from_date', 'to_date'];

		foreach ($rows as $row)
		{
			$row->plan = $row->plan_title;

			foreach ($dateFields as $dateField)
			{
				if ((int) $row->{$dateField})
				{
					$row->{$dateField} = HTMLHelper::_('date', $row->{$dateField}, 'Y-m-d');
				}
				else
				{
					$row->{$dateField} = '';
				}
			}

			foreach ($rowFields as $rowField)
			{
				if (!$rowField->is_core)
				{
					$fieldValue             = $fieldValues[$row->id][$rowField->id] ?? '';
					$row->{$rowField->name} = $fieldValue;
				}
			}
		}

		$filePath = OSMembershipHelper::callOverridableHelperMethod(
			'Data',
			'excelExport',
			[$fields, $rows, 'group_members_list']
		);

		if ($filePath)
		{
			$this->processDownloadFile($filePath);
		}
	}

	/***
	 * Make the selected group members become admin of their group
	 */
	public function set_group_admin()
	{
		$cid = $this->input->post->get('cid', [], 'array');
		$db  = Factory::getContainer()->get('db');

		if (count($cid))
		{
			$id = $cid[0];

			$row = new OSMembershipTableSubscriber($db);

			if ($row->load($id))
			{
				$currentGroupAdminId = $row->group_admin_id;
				$row->group_admin_id = 0;
				$row->store();

				/* @var DatabaseDriver $db */
				$db    = Factory::getContainer()->get('db');
				$query = $db->getQuery(true);

				// Make current group admin become group member
				$query->update('#__osmembership_subscribers')
					->set('group_admin_id = ' . $row->user_id)
					->where('user_id = ' . $currentGroupAdminId)
					->where('plan_id = ' . $row->plan_id);
				$db->setQuery($query)
					->execute();

				// Exclude old group admin from pre-configured user groups
				$oldGroupAdmin = Factory::getUser((int) $currentGroupAdminId);

				// Avoid group admin loosing permission because of wrong settings
				if (!$oldGroupAdmin->authorise('core.admin'))
				{
					$plugin = PluginHelper::getPlugin('osmembership', 'groupmembership');

					if ($plugin)
					{
						$params          = new Registry($plugin->params);
						$excludeGroupIds = $params->get('exclude_group_ids', [7, 8]);

						if ($excludeGroupIds)
						{
							$currentGroups = $oldGroupAdmin->get('groups');

							if (is_string($excludeGroupIds))
							{
								$excludeGroupIds = explode(',', $excludeGroupIds);
							}

							$excludeGroupIds = ArrayHelper::toInteger($excludeGroupIds);
							$currentGroups   = array_diff($currentGroups, $excludeGroupIds);

							$oldGroupAdmin->set('groups', $currentGroups);
							$oldGroupAdmin->save(true);
						}
					}
				}

				// Change group admin for all current group members to new group admin
				$query->clear()
					->update('#__osmembership_subscribers')
					->set('group_admin_id = ' . $row->user_id)
					->where('group_admin_id = ' . $currentGroupAdminId)
					->where('plan_id = ' . $row->plan_id);
				$db->setQuery($query)
					->execute();

				// Set correct user groups for new group admin
				$user          = Factory::getUser((int) $row->user_id);
				$currentGroups = $user->groups;
				$plan          = new OSMembershipTablePlan($db);
				$plan->load($row->plan_id);
				$params        = new Registry($plan->params);
				$groups        = explode(',', $params->get('joomla_group_ids'));
				$currentGroups = array_unique(array_merge($currentGroups, $groups));
				$user->groups  = $currentGroups;
				$user->save(true);

				$this->setMessage(Text::_('OSM_GROUP_ADMIN_SUCCESSFULLY_CHANGED'));
			}
			else
			{
				$this->app->enqueueMessage(sprintf('Invalid Group Member %s', $id));
			}
		}

		$this->setRedirect(
			'index.php?option=com_osmembership&view=groupmembers&filter_group_admin_id=' . $row->user_id
		);
	}
}
