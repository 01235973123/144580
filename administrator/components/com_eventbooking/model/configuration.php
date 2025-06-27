<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Filesystem\File;

class EventbookingModelConfiguration extends RADModel
{
	/**
	 * Store the configuration data
	 *
	 * @param   array  $data
	 *
	 * @return bool
	 */
	public function store($data)
	{
		$db = $this->getDbo();
		$db->truncateTable('#__eb_configs');
		$row = $this->getTable('Config');

		foreach ($data as $key => $value)
		{
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

			$row->id           = 0;
			$row->config_key   = $key;
			$row->config_value = $value;
			$row->store();
		}

		$customCss = isset($data['custom_css']) ? trim($data['custom_css']) : '';

		if ($customCss !== '')
		{
			File::write(JPATH_ROOT . '/media/com_eventbooking/assets/css/custom.css', $customCss);
		}

		$eventCustomFields = $data['event_custom_fields'] ? trim($data['event_custom_fields']) : '';

		if ($eventCustomFields !== '')
		{
			File::write(JPATH_ROOT . '/components/com_eventbooking/fields.xml', $eventCustomFields);
		}

		return true;
	}
}
