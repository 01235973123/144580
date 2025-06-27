<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

JLoader::register('OSMembershipModelPlan', JPATH_ADMINISTRATOR . '/components/com_osmembership/model/plan.php');
JLoader::register(
	'OSMembershipModelOverridePlan',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/model/override/plan.php'
);

class OSMembershipModelMplan extends OSMembershipModelPlan
{
	public function __construct($config)
	{
		$config['name'] = 'plan';
		$config['table'] = '#__osmembership_plans';

		parent::__construct($config);
	}

	/**
	 * Pre-process data
	 *
	 * @param             $row
	 * @param   MPFInput  $input
	 * @param   bool      $isNew
	 */
	protected function beforeStore($row, $input, $isNew)
	{
		parent::beforeStore($row, $input, $isNew);

		if ($isNew)
		{
			$row->subscriptions_manage_user_id = Factory::getApplication()->getIdentity()->id;
		}

		$row->short_description = ComponentHelper::filterText($row->short_description);
		$row->description       = ComponentHelper::filterText($row->description);
	}
}
