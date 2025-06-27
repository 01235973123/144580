<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;


trait EventbookingControllerView
{
	/**
	 * Override getView method to support getting layout from themes
	 *
	 * @param   string  $name
	 * @param   string  $type
	 * @param   string  $layout
	 * @param   array   $config
	 *
	 * @return RADView
	 */
	public function getView($name, $type = 'html', $layout = 'default', array $config = [])
	{
		$theme = EventbookingHelper::getDefaultTheme();

		$paths   = [];
		$paths[] = JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/com_eventbooking/' . $name;
		$paths[] = JPATH_ROOT . '/components/com_eventbooking/themes/' . $theme->name . '/' . $name;

		if ($theme->name != 'default')
		{
			$paths[] = JPATH_ROOT . '/components/com_eventbooking/themes/default/' . $name;
		}

		$config['paths'] = $paths;

		return parent::getView($name, $type, $layout, $config);
	}
}