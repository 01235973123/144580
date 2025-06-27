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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core')
	->useScript('table.columns')
	->useScript('multiselect')
	->registerAndUseScript('com_eventbooking.admin-events-default', 'media/com_eventbooking/js/admin-events-default.min.js');

$this->loadDraggableLib();
$this->loadSearchTools();

Text::script('EB_CANCEL_EVENT_CONFIRM', true);