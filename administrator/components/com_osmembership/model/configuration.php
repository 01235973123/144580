<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\Filesystem\File;

/**
 * Membership Pro Component Configuration Model
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipModelConfiguration extends MPFModel
{
	/**
	 * Store the configuration data
	 *
	 * @param   array  $data
	 */
	public function store($data)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$db->truncateTable('#__osmembership_configs');
		$row = $this->getTable('Config');

		$excludes = ['custom_fields', 'custom_css'];

		foreach ($data as $key => $value)
		{
			if (in_array($key, $excludes))
			{
				continue;
			}

			$row->id = 0;

			if (is_array($value))
			{
				if (in_array($key, ['common_tags', 'css_classes_map']))
				{
					$value = json_encode($value);
				}
				else
				{
					$value = implode(',', $value);
				}
			}

			$row->config_key   = $key;
			$row->config_value = $value;
			$row->store();
		}

		if ($data['create_account_when_membership_active'])
		{
			//Need to activate the account creation plugin
			$query->update('#__extensions')
				->set('`enabled` = 1')
				->set('`ordering` = -1')
				->where('`element`="account" AND `folder`="osmembership"');
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			//We should disable this plugin
			$query->update('#__extensions')
				->set('`enabled` = 0')
				->where('`element`="account" AND `folder`="osmembership"');
			$db->setQuery($query);
			$db->execute();
		}

		if (isset($data['custom_css']))
		{
			File::write(JPATH_ROOT . '/media/com_osmembership/assets/css/custom.css', trim($data['custom_css']));
		}

		if (isset($data['custom_fields']))
		{
			File::write(JPATH_ROOT . '/components/com_osmembership/fields.xml', trim($data['custom_fields']));
		}
	}
}
