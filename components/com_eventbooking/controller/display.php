<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

trait EventbookingControllerDisplay
{
	protected function loadAssets()
	{
		$wa            = $this->app->getDocument()->getWebAssetManager();
		$config        = EventbookingHelper::getConfig();
		$calendarTheme = $config->get('calendar_theme', 'default');

		// Add base AjaxURL to use in JS, it is only used in shopping cart
		if ($config->multiple_booking)
		{
			$baseAjaxUrl = Uri::root(true) . '/index.php?option=com_eventbooking' . EventbookingHelper::getLangLink(
				) . '&time=' . time();
			$wa->addInlineScript('var EBBaseAjaxUrl = "' . $baseAjaxUrl . '";');
		}

		// Twitter Bootrap CSS
		if ($config->load_bootstrap_css_in_frontend && in_array($config->get('twitter_bootstrap_version', 5), [5]))
		{
			$wa->useStyle('bootstrap.css');
		}

		if ($config->get('load_font_awesome', '1'))
		{
			$wa->registerAndUseStyle(
				'com_eventbooking.font-awesome',
				'media/com_eventbooking/assets/css/font-awesome.min.css'
			);
		}

		$wa->registerAndUseStyle(
			'com_eventbooking.style',
			'media/com_eventbooking/assets/css/style.min.css',
			['version' => EventbookingHelper::getInstalledVersion()]
		)
			->registerAndUseStyle(
				'com_eventbooking.calendar.theme',
				'media/com_eventbooking/assets/css/themes/' . $calendarTheme . '.css',
				['version' => EventbookingHelper::getInstalledVersion()]
			);

		$theme = EventbookingHelper::getDefaultTheme();

		// Call init script of theme to allow it to load it's own javascript + css files if needed
		if (file_exists(JPATH_ROOT . '/components/com_eventbooking/themes/' . $theme->name . '/init.php'))
		{
			require_once JPATH_ROOT . '/components/com_eventbooking/themes/' . $theme->name . '/init.php';
		}

		$customCssFile = JPATH_ROOT . '/media/com_eventbooking/assets/css/custom.css';

		if (file_exists($customCssFile) && filesize($customCssFile) > 0)
		{
			$wa->registerAndUseStyle(
				'com_eventbooking.style.custom',
				'media/com_eventbooking/assets/css/custom.css',
				['version' => filemtime($customCssFile)]
			);
		}
	}
}
