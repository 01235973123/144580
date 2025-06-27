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

class OSMembershipViewGroupmemberRaw extends MPFViewHtml
{
	/**
	 * List of select list
	 *
	 * @var array
	 */
	protected $lists;

	/**
	 * Set view data and render the view
	 *
	 * @return void
	 */
	public function display()
	{
		$this->setLayout('groupadmins');

		$planId       = $this->input->getInt('plan_id');
		$groupAdminId = $this->input->getInt('group_admin_id');

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_SELECT_GROUP'), 'user_id', 'name');

		if ($planId)
		{
			$db    = $this->model->getDbo();
			$query = $db->getQuery(true)
				->select('DISTINCT user_id, CONCAT(first_name, " ", last_name) AS name, email')
				->from('#__osmembership_subscribers AS a')
				->where('plan_id = ' . $planId)
				->where('group_admin_id = 0')
				->group('user_id')
				->order('name');
			$db->setQuery($query);
			$groupAdmins = $db->loadObjectList();

			foreach ($groupAdmins as $groupAdmin)
			{
				if (!$groupAdmin->name)
				{
					$groupAdmin->name = $groupAdmin->email;
				}
			}

			if (count($groupAdmins))
			{
				$options = array_merge($options, $groupAdmins);
			}
		}

		$this->lists['group_admin_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'group_admin_id',
			' class="form-select"',
			'user_id',
			'name',
			$groupAdminId
		);

		parent::display();
	}
}
