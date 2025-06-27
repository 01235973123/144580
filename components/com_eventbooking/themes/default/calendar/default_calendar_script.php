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
 * Layout variables
 * -----------------
 * @var   int $w
 */

$wa = Factory::getApplication()
	->getDocument()
	->getWebAssetManager();

$wa->registerAndUseScript(
	'com_eventbooking.responsive-auto-height',
	'media/com_eventbooking/assets/js/responsive-auto-height.min.js',
	[],
	['defer' => true]
);

if ($this->config->show_thumb_in_calendar)
{
	$equalHeightScript[] = 'window.addEventListener("load", function() {';
}
else
{
	$equalHeightScript[] = 'document.addEventListener("DOMContentLoaded", function() {';
}

for ($i = 0; $i < $w; $i++)
{
	$equalHeightScript[] = 'new ResponsiveAutoHeight("li.eb-calendar-row-' . $i . '");';
}

$equalHeightScript[] = '});';

$wa->addInlineScript(implode("\r\n", $equalHeightScript));

if ($this->config->display_event_in_tooltip)
{
	$wa->addInlineScript(
		"
		document.addEventListener('DOMContentLoaded', function () {
            var tooltipOptions = {'html' : true, 'sanitize': false};      
                if (window.jQuery && window.jQuery().tooltip){
                    window.jQuery('#eb-calendar-page').find('.eb-calendar-tooltip').tooltip(tooltipOptions);
                } else if (bootstrap.Tooltip) {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('.eb-calendar-tooltip'));
					var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
					  return new bootstrap.Tooltip(tooltipTriggerEl, tooltipOptions);
					});                                     
                }     
        });      
    "
	);
}
