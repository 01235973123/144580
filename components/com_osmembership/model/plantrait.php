<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

trait OSMembershipModelPlantrait
{
	/**
	 * Process plan custom fields
	 *
	 * @param   OSMembershipTablePlan  $row
	 */
	protected function processCustomFields($row)
	{
		$registry = new Registry($row->custom_fields);

		$row->fieldsData = $registry;

		foreach ($registry->toArray() as $key => $value)
		{
			if (!property_exists($row, $key))
			{
				$row->{$key} = $value;
			}

			if (!is_string($value))
			{
				continue;
			}

			$row->short_description = str_replace('[' . strtoupper($key) . ']', $value, $row->short_description);
			$row->description       = str_replace('[' . strtoupper($key) . ']', $value, $row->description);
		}
	}
}
