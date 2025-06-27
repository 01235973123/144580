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

/**
 * OSMembership Component Country Model
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipModelCountry extends MPFModelAdmin
{
	/**
	 * Update country_id make it the same with id
	 *
	 * @param   Table     $row
	 * @param   MPFInput  $input
	 * @param   bool      $isNew
	 */
	protected function afterStore($row, $input, $isNew)
	{
		if ($isNew)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->update('#__osmembership_countries')
				->set('country_id=id')
				->where('id=' . $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}
}
