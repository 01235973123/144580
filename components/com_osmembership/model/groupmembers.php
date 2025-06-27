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

class OSMembershipModelGroupmembers extends MPFModelList
{
	/**
	 * Joomla User ID of group admin
	 *
	 * @var int
	 */
	protected $groupAdminUserId;

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['table']         = '#__osmembership_subscribers';
		$config['search_fields'] = ['tbl.first_name', 'tbl.last_name', 'tbl.email'];

		parent::__construct($config);

		$this->state->setDefault('filter_order', 'tbl.created_date')
			->setDefault('filter_order_Dir', 'DESC');
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
			->select('b.title' . $fieldSuffix . ' AS plan_title, b.lifetime_membership')
			->select('u.username AS username');

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
			->leftJoin('#__users AS u ON tbl.user_id = u.id');

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

		if ($this->groupAdminUserId)
		{
			$query->where('tbl.group_admin_id = ' . $this->groupAdminUserId);
		}
		else
		{
			$query->where('tbl.group_admin_id = ' . Factory::getApplication()->getIdentity()->id);
		}

		return $this;
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

		if (count($rows))
		{
			$ids = [];

			foreach ($rows as $row)
			{
				$ids[] = $row->id;
			}

			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select('*')
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
		}

		return $fieldsData;
	}

	/**
	 * Set group admin user ID
	 *
	 * @param   int  $userId
	 *
	 * @return void
	 */
	public function setGroupAdminUserId($userId)
	{
		$this->groupAdminUserId = $userId;
	}
}
