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

/**
 * @var string $selectedState
 */

EventbookingHelperPayments::writeJavascriptObjects();

Factory::getApplication()
	->getDocument()
	->addScriptOptions('selectedState', $selectedState);

EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-payment-default.min.js');
