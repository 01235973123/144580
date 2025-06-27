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

$this->loadDraggableLib();
$this->loadSearchTools();

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core')
	->useScript('table.columns')
	->useScript('multiselect')
	->registerAndUseScript('com_eventbooking.admin-plugins-default', 'media/com_eventbooking/js/admin-plugins-default.min.js');

Text::script('EB_CHOOSE_PLUGIN', true);