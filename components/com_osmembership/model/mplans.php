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

JLoader::register(
	'OSMembershipModelOverridePlans',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/model/override/plans.php'
);
JLoader::register('OSMembershipModelPlans', JPATH_ADMINISTRATOR . '/components/com_osmembership/model/plans.php');

class OSMembershipModelMplans extends OSMembershipModelPlans
{
	public function __construct($config = [])
	{
		$config['table'] = '#__osmembership_plans';

		parent::__construct($config);
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
		$user = Factory::getApplication()->getIdentity();

		if (!$user->authorise('core.admin', 'com_osmembership'))
		{
			$query->where('tbl.created_by = ' . $user->id);
		}

		return parent::buildQueryWhere($query);
	}
}
