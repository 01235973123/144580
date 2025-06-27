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

$app             = Factory::getApplication();
$config          = EventbookingHelper::getConfig();
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$rootUri         = Uri::root(true);
$siteUrl         = Uri::root();
$interval        = (int) $app->getParams()->get('checkin_interval', 15) ?: 15;

$app->getDocument()
	->addScriptOptions(
	'checkinUrl',
	$rootUri . '/index.php?option=com_eventbooking&task=scan.qr_code_checkin&api_key=' . $config->get('checkin_api_key')
)
->addScriptOptions('checkInInterval', $interval*1000)
->addScriptOptions('btn', $bootstrapHelper->getClassMapping('btn'))
->addScriptOptions('btnPrimaryClass', $bootstrapHelper->getClassMapping('btn-primary'))
->addScriptOptions('textSuccessClass', $bootstrapHelper->getClassMapping('text-success'))
->addScriptOptions('textWarningClass', $bootstrapHelper->getClassMapping('text-warning'))
->addScriptOptions('successAudioUrl', $siteUrl . 'media/com_eventbooking/audios/success.mp3')
->addScriptOptions('failAudioUrl', $siteUrl . 'media/com_eventbooking/audios/fail.mp3')
->getWebAssetManager()
->useScript('core')
->registerAndUseScript('com_eventbooking.html5-qrcode', 'media/com_eventbooking/assets/js/html5-qrcode/html5-qrcode.min.js')
->registerAndUseScript('com_eventbooking.site-checkin-default', 'media/com_eventbooking/js/site-checkin-default.min.js')
->registerAndUseScript('com_eventbooking.tingle', 'media/com_eventbooking/assets/js/tingle/tingle.min.js')
->registerAndUseStyle('com_eventbooking.tingle', 'media/com_eventbooking/assets/js/tingle/tingle.min.css');
?>
<div id="eb-checkin-page" class="eb-container">
	<?php
	if ($this->params->get('show_page_heading', 1))
	{
	?>
		<h1 class="eb-page-heading"><?php echo $this->escape(Text::_('EB_CHECKIN_REGISTRANT'));?></h1>
	<?php
	}
	?>
    <div id="reader"></div>
</div>