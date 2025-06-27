<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class OSMembershipViewGroupmembersHtml extends MPFViewList
{
	/**
	 * Group members custom field data
	 *
	 * @var array
	 */
	protected $fieldsData;

	/**
	 * Custom Fields
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Prepare view data
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$db    = $this->model->getDbo();
		$query = $db->getQuery(true)
			->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->where('(number_group_members > 0 OR number_members_field > 0)')
			->order('title');
		$db->setQuery($query);

		$options                       = [];
		$options[]                     = HTMLHelper::_('select.option', 0, Text::_('OSM_SELECT_PLAN'), 'id', 'title');
		$options                       = array_merge($options, $db->loadObjectList());
		$this->lists['filter_plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_plan_id',
			' class="form-select" onchange="submit();" ',
			'id',
			'title',
			$this->state->filter_plan_id
		);
		$this->lists['filter_plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
			$this->lists['filter_plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_ONE_PLAN')
		);

		if ($this->state->filter_plan_id > 0)
		{
			$query->clear();
			$query->select('DISTINCT user_id, CONCAT(first_name, " ", last_name) AS name')
				->from('#__osmembership_subscribers AS a')
				->where('plan_id = ' . $this->state->filter_plan_id)
				->where(
					'user_id IN  (SELECT DISTINCT group_admin_id FROM #__osmembership_subscribers WHERE plan_id = ' . $this->state->filter_plan_id . ' AND group_admin_id > 0)'
				)
				->group('user_id')
				->order('name');
			$db->setQuery($query);
			$groupAdmins = $db->loadObjectList();

			if (count($groupAdmins))
			{
				$options                              = [];
				$options[]                            = HTMLHelper::_(
					'select.option',
					0,
					Text::_('OSM_SELECT_GROUP'),
					'user_id',
					'name'
				);
				$options                              = array_merge($options, $groupAdmins);
				$this->lists['filter_group_admin_id'] = HTMLHelper::_(
					'select.genericlist',
					$options,
					'filter_group_admin_id',
					' class="form-select" onchange="submit();" ',
					'user_id',
					'name',
					$this->state->filter_group_admin_id
				);
			}
		}

		$options                         = [];
		$options[]                       = HTMLHelper::_('select.option', 0, Text::_('OSM_ALL'));
		$options[]                       = HTMLHelper::_('select.option', 1, Text::_('OSM_ACTIVE'));
		$options[]                       = HTMLHelper::_('select.option', 2, Text::_('OSM_EXPIRED'));
		$this->lists['filter_published'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_published',
			' class="form-select" onchange="submit();" ',
			'value',
			'text',
			$this->state->filter_published
		);

		$fields   = OSMembershipHelper::getProfileFields((int) $this->state->filter_plan_id, true);
		$fieldIds = [];

		for ($i = 0, $n = count($fields); $i < $n; $i++)
		{
			if (!$fields[$i]->show_on_subscriptions)
			{
				unset($fields[$i]);
			}
		}

		$fields = array_values($fields);

		foreach ($fields as $field)
		{
			if ($field->is_core)
			{
				continue;
			}

			$fieldIds[] = $field->id;
		}

		/* @var OSMembershipModelGroupmembers $model */
		$model            = $this->getModel();
		$this->fieldsData = $model->getFieldsData($fieldIds);
		$this->fields     = $fields;

		$this->config = OSMembershipHelper::getConfig();
	}

	/**
	 * Add custom toolbar buttons
	 *
	 * @return void
	 */
	protected function addCustomToolbarButtons()
	{
		ToolbarHelper::custom('export', 'download', 'download', 'OSM_EXPORT_MEMBERS', false);
		ToolbarHelper::custom('set_group_admin', 'publish', 'publish', 'OSM_GROUP_ADMIN', false);
	}
}
