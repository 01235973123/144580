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
use Joomla\Database\DatabaseDriver;

/**
 * OSMembership Plugin controller
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipControllerReport extends OSMembershipController
{
	use MPFControllerDownload;

	/**
	 * Export subscribers
	 */
	public function export()
	{
		$this->checkAccessPermission('subscriptions');

		/* @var OSMembershipModelReports $model */
		$model = $this->getModel('reports');
		$model->set('limitstart', 0)
			->set('limit', 0)
			->set('filter_order', 'tbl.id')
			->set('filter_order_Dir', 'ASC');

		$rows = $model->getData();

		if (!count($rows))
		{
			return;
		}

		$config = OSMembershipHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');

		$ids = [];

		foreach ($rows as $row)
		{
			$ids[] = $row->id;
		}

		$query = $db->getQuery(true)
			->select('name, title')
			->from('#__osmembership_plugins');
		$db->setQuery($query);
		$plugins      = $db->loadObjectList();
		$pluginTitles = [];

		foreach ($plugins as $plugin)
		{
			$pluginTitles[$plugin->name] = $plugin->title;
		}

		//Get list of custom fields
		$query->clear()
			->select('id, name, title, is_core')
			->from('#__osmembership_fields')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		$customFieldDatas = [];
		$query->clear()
			->select('*')
			->from('#__osmembership_field_value')
			->whereIn('subscriber_id', $ids);
		$db->setQuery($query);
		$fieldDatas = $db->loadObjectList();

		foreach ($fieldDatas as $fieldData)
		{
			$customFieldDatas[$fieldData->subscriber_id][$fieldData->field_id] = $fieldData->field_value;
		}

		$fields  = ['plan_title', 'username'];
		$headers = [Text::_('OSM_PLAN'), Text::_('Username')];

		foreach ($rowFields as $rowField)
		{
			$fields[]  = $rowField->name;
			$headers[] = $rowField->title;
		}

		$headers[] = Text::_('OSM_SUBSCRIPTION_START_DATE');
		$headers[] = Text::_('OSM_SUBSCRIPTION_END_DATE');
		$headers[] = Text::_('OSM_SUBSCRIPTION_STATUS');
		$headers[] = Text::_('OSM_MEMBERSHIP_ID');

		$fields[] = 'plan_subscription_from_date';
		$fields[] = 'plan_subscription_to_date';
		$fields[] = 'subscription_status';
		$fields[] = 'membership_id';

		foreach ($rows as $row)
		{
			switch ($row->plan_subscription_status)
			{
				case 0:
					$row->subscription_status = Text::_('OSM_PENDING');
					break;
				case 1:
					$row->subscription_status = Text::_('OSM_ACTIVE');
					break;
				case 2:
					$row->subscription_status = Text::_('OSM_EXPIRED');
					break;
				case 3:
					$row->subscription_status = Text::_('OSM_CANCELLED_PENDING');
					break;
				case 4:
					$row->subscription_status = Text::_('OSM_CANCELLED_REFUNDED');
					break;
				default:
					$row->subscription_status = '';
					break;
			}

			$row->plan_subscription_from_date = HTMLHelper::_(
				'date',
				$row->plan_subscription_from_date,
				$config->date_format
			);
			$row->plan_subscription_to_date   = HTMLHelper::_(
				'date',
				$row->plan_subscription_to_date,
				$config->date_format
			);

			if ($row->membership_id)
			{
				$row->membership_id = OSMembershipHelper::formatMembershipId($row, $config);
			}

			foreach ($rowFields as $rowField)
			{
				if ($rowField->is_core)
				{
					continue;
				}

				$fieldValue = $customFieldDatas[$row->id][$rowField->id] ?? '';

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				$row->{$rowField->name} = $fieldValue;
			}
		}

		$filename = 'subscribers_report_' . Factory::getDate('now', $this->app->get('offset'))->format('Y_m_d_H_i_s');

		$filePath = OSMembershipHelper::callOverridableHelperMethod(
			'Data',
			'excelExport',
			[$fields, $rows, $filename, $headers]
		);

		if ($filePath)
		{
			$this->processDownloadFile($filePath);
		}
	}
}
