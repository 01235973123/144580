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
use Joomla\Database\DatabaseQuery;
use Joomla\Utilities\ArrayHelper;

class OSMembershipModelMembers extends MPFModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		if (!isset($config['search_fields']))
		{
			$config['search_fields'] = [
				'tbl.first_name',
				'tbl.last_name',
				'tbl.membership_id',
				'tbl.email',
				'b.title',
				'c.username',
				'c.name'
			];
		}

		$config['table']      = '#__osmembership_subscribers';
		$config['clear_join'] = false;

		parent::__construct($config);

		// Dynamic searchable fields
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('name')
			->from('#__osmembership_fields')
			->where('published = 1')
			->where('is_searchable = 1');
		$db->setQuery($query);
		$searchableFields = $db->loadColumn();

		foreach ($searchableFields as $field)
		{
			$field = 'tbl.' . $field;

			if (!in_array($field, $this->searchFields))
			{
				$this->searchFields[] = $field;
			}
		}

		$this->state->insert('id', 'int', 0)
			->insert('filter_fields', 'array', []);

		$params = $this->getParams();

		$listLimit = $params->get('list_limit') ?: Factory::getApplication()->get('list_limit');

		$this->state->setDefault('filter_order', $params->get('sort_by', 'tbl.created_date'))
			->setDefault('filter_order_Dir', $params->get('sort_direction', 'DESC'))
			->setDefault('limit', (int) $listLimit);
	}

	/**
	 * Builds SELECT columns list for the query
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryColumns(DatabaseQuery $query)
	{
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		$query->select('tbl.*')
			->select('b.title' . $fieldSuffix . ' AS plan_title')
			->select('c.username');

		return $this;
	}

	/**
	 * Builds JOINS clauses for the query
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryJoins(DatabaseQuery $query)
	{
		$query->leftJoin('#__osmembership_plans AS b  ON tbl.plan_id = b.id')
			->leftJoin('#__users AS c ON tbl.user_id = c.id');

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(DatabaseQuery $query)
	{
		parent::buildQueryWhere($query);

		$query->where('tbl.plan_main_record = 1');

		if ($this->state->id)
		{
			$query->where('tbl.plan_id = ' . (int) $this->state->id);
		}

		$params = $this->getParams();

		if (is_numeric($params->get('subscription_status')))
		{
			$query->where('tbl.plan_subscription_status = ' . (int) $params->get('subscription_status'));
		}

		$planIds        = $params->get('plan_ids', []);
		$excludePlanIds = $params->get('exclude_plan_ids', []);

		// Backward compatible, in the older version, exclude_plan_ids is a comma separated string
		if (is_string($excludePlanIds))
		{
			$excludePlanIds = explode(',', $excludePlanIds);
		}

		$planIds        = array_filter(ArrayHelper::toInteger($planIds));
		$excludePlanIds = array_filter(ArrayHelper::toInteger($excludePlanIds));

		if (count($planIds) > 0)
		{
			$query->whereIn('tbl.plan_id', $planIds);
		}

		if (count($excludePlanIds) > 0)
		{
			$query->whereNotIn('tbl.plan_id', $excludePlanIds);
		}

		$memberType = $params->get('member_type', 0);

		if ($memberType == 1)
		{
			$query->where('tbl.group_admin_id = 0');
		}
		elseif ($memberType == 2)
		{
			$query->where('tbl.group_admin_id > 0');
		}

		$memberFilterSql = trim($params->get('members_filter_sql', ''));

		if ($memberFilterSql)
		{
			$query->where($memberFilterSql);
		}

		$config = OSMembershipHelper::getConfig();

		if ($config->get('enable_select_show_hide_members_list'))
		{
			$query->where('tbl.show_on_members_list = 1');
		}


		$filterFields = array_filter($this->state->get('filter_fields', []));

		foreach ($filterFields as $fieldName => $fieldValue)
		{
			$db         = $this->getDbo();
			$pos        = strrpos($fieldName, '_');
			$fieldId    = (int) substr($fieldName, $pos + 1);
			$fieldValue = $db->quote('%' . $db->escape($fieldValue, true) . '%', false);

			// This is a core field, so we need to query directly from #__subscribers table
			if ($fieldId <= 13)
			{
				$coreFieldQuery = $db->getQuery(true)
					->select('name')
					->from('#__osmembership_fields')
					->where('id = ' . $fieldId);
				$db->setQuery($coreFieldQuery);
				$coreFieldName = $db->loadResult();

				if ($coreFieldName)
				{
					$query->where($db->quoteName('tbl.' . $coreFieldName) . ' LIKE ' . $fieldValue);
				}
			}
			else
			{
				$query->where(
					'tbl.id IN (SELECT subscriber_id FROM #__osmembership_field_value WHERE field_id = ' . $fieldId . ' AND field_value LIKE ' . $fieldValue . ')'
				);
			}
		}

		return $this;
	}

	/**
	 * Prepare the data before it is being displayed
	 *
	 * @param   array  $rows
	 */
	protected function beforeReturnData($rows)
	{
		parent::beforeReturnData($rows);

		foreach ($rows as $row)
		{
			if (!$row->state)
			{
				continue;
			}

			$row->state = OSMembershipHelper::getStateName($row->country, $row->state);
		}
	}

	/**
	 * Get profile custom fields data
	 *
	 * @return array
	 */
	public function getFieldsData()
	{
		$fieldsData = [];
		$rows       = $this->data;
		$fields     = OSMembershipHelper::getCustomFieldsForPlans(
			$this->state->id,
			false,
			['show_on_members_list = 1']
		);

		if (count($rows) && count($fields))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$ids   = [];

			foreach ($rows as $row)
			{
				$ids[] = $row->id;
			}

			$query->select('*')
				->from('#__osmembership_field_value')
				->whereIn('subscriber_id', $ids);
			$db->setQuery($query);
			$fieldValues = $db->loadObjectList();

			if (count($fieldValues))
			{
				foreach ($fieldValues as $fieldValue)
				{
					$fieldsData[$fieldValue->subscriber_id][$fieldValue->field_id] = $fieldValue->field_value;
				}
			}

			// Allow easier accessing to custom field data
			foreach ($rows as $row)
			{
				foreach ($fields as $field)
				{
					if (isset($fieldsData[$row->id][$field->id]))
					{
						$row->{$field->name} = $fieldsData[$row->id][$field->id];
					}
					else
					{
						$row->{$field->name} = '';
					}
				}
			}
		}

		return $fieldsData;
	}

	protected function buildQueryOrder(DatabaseQuery $query)
	{
		$sort      = $this->state->filter_order;
		$direction = strtoupper($this->state->filter_order_Dir);

		if ($sort == 'tbl.name')
		{
			$query->order('CONCAT(tbl.first_name, " ", tbl.last_name)' . ' ' . $direction);

			return $this;
		}

		if ($sort == 'rand')
		{
			$query->order('RAND()');

			return $this;
		}

		return parent::buildQueryOrder($query);
	}
}
