<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

class OSMembershipModelMember extends MPFModel
{
	/**
	 * Model constructor.
	 *
	 * @param   array  $config
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		$this->state->insert('id', 'int', 0);
	}

	/**
	 * Get profile data
	 *
	 * @return mixed
	 */
	public function getData()
	{
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('tbl.*')
			->select('b.title' . $fieldSuffix . ' AS plan_title')
			->from('#__osmembership_subscribers AS tbl')
			->innerJoin('#__osmembership_plans AS b ON tbl.plan_id = b.id')
			->where('tbl.id = ' . (int) $this->state->get('id'));
		$db->setQuery($query);

		$row = $db->loadObject();

		if ($row->state)
		{
			$row->state = OSMembershipHelper::getStateName($row->country, $row->state);
		}

		return $row;
	}
}
