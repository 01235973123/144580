<?php

use Joomla\CMS\Factory;

/**
 * @package     MPF
 * @subpackage  Synchronizer
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
class MPFSynchronizerEasyprofile
{
	public function getData($userId, $mappings)
	{
		$data  = [];
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__jsn_users')
			->where('id=' . $userId);
		$db->setQuery($query);
		$profile = $db->loadObject();
		if ($profile)
		{
			foreach ($mappings as $fieldName => $mappingFieldName)
			{
				if ($mappingFieldName && isset($profile->{$mappingFieldName}))
				{
					$data[$fieldName] = $profile->{$mappingFieldName};
				}
			}
		}

		return $data;
	}
}
