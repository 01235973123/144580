<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

class OSMembershipModelCategory extends MPFModelAdmin
{
	/**
	 * This model process events, so we need to set triggerEvents to true
	 *
	 * @var bool
	 */
	protected $triggerEvents = true;

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   Table  $row  A reference to a JTable object.
	 *
	 * @return void
	 */
	protected function prepareTable($row, $task)
	{
		if ($row->parent_id > 0)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			// Calculate level
			$query->select('`level`')
				->from('#__osmembership_categories')
				->where('id = ' . (int) $row->parent_id);
			$db->setQuery($query);
			$row->level = (int) $db->loadResult() + 1;
		}
		else
		{
			$row->level = 1;
		}

		// Prevent choosing itself as parent category
		if ($row->parent_id == $row->id)
		{
			$row->parent_id = 0;
		}

		parent::prepareTable($row, $task);
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   Table  $row  A JTable object.
	 *
	 * @return array An array of conditions to add to ordering queries.
	 */
	protected function getReorderConditions($row)
	{
		return ['`parent_id` = ' . (int) $row->parent_id];
	}
}
