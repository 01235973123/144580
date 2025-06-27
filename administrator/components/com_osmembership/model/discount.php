<?php
/**
 * @package        Joomla
 * @subpackage     OS Membership
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

class OSMembershipModelDiscount extends MPFModelAdmin
{
	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['table'] = '#__osmembership_renewaldiscounts';

		parent::__construct($config);
	}

	/**
	 * Generate batch discounts
	 *
	 * @param   MPFInput  $input
	 */
	public function batch($input)
	{
		$data = $input->post->getData();

		unset($data['plan_id']);

		$planIds = array_filter(ArrayHelper::toInteger($input->get('plan_id', [], 'array')));

		foreach ($planIds as $planId)
		{
			$row = $this->getTable();
			$row->bind($data);
			$row->plan_id = $planId;

			$row->store();
		}
	}
}
