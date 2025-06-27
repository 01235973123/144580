<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

Factory::getApplication()->getDocument()
	->addScriptOptions('siteUrl', Uri::base(true))
	->getWebAssetManager()
	->useScript('core')
	->useScript('table.columns')
	->useScript('multiselect')
	->registerAndUseScript('com_eventbooking.admin-registrants-default', 'media/com_eventbooking/js/admin-registrants-default.min.js');

Text::script('EB_SELECT_EVENT_TO_ADD_REGISTRANT', true);