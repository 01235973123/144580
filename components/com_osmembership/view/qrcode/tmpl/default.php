<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$config          = OSMembershipHelper::getConfig();
$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$rootUri         = Uri::root(true);
$interval        = (int) Factory::getApplication()->getParams()->get('checkin_interval', 15) ?: 15;

Factory::getApplication()
	->getDocument()
	->addScriptOptions('checkinUrl',
		$rootUri . '/index.php?option=com_osmembership&task=qrcode.check_subscription_status')
	->addScriptOptions('checkInInterval', $interval * 1000)
	->addScriptOptions('btn', $bootstrapHelper->getClassMapping('btn'))
	->addScriptOptions('btnPrimaryClass', $bootstrapHelper->getClassMapping('btn-primary'))
	->addScriptOptions('textSuccessClass', $bootstrapHelper->getClassMapping('text-success'))
	->addScriptOptions('textWarningClass', $bootstrapHelper->getClassMapping('text-warning'))
	->getWebAssetManager()
	->useScript('core')
	->registerAndUseScript('com_osmembership.html5-qrcode', 'media/com_osmembership/assets/js/html5-qrcode/html5-qrcode.min.js')
	->registerAndUseScript('com_osmembership.site-qrcode-default', 'media/com_osmembership/js/site-qrcode-default.min.js')
	->registerAndUseScript('com_osmembership.tingle', 'media/com_osmembership/assets/js/tingle/tingle.min.js')
	->registerAndUseStyle('com_osmembership.tingle', 'media/com_osmembership/assets/js/tingle/tingle.min.css');
?>
<div id="osm-qrcode-page" class="osm-container">
	<?php
	if ($this->params->get('show_page_heading', 1))
	{
		if ($this->input->getInt('hmvc_call'))
		{
			$hTag = 'h2';
		}
		else
		{
			$hTag = 'h1';
		}
	?>
		<<?php echo $hTag; ?> class="osm-page-title"><?php echo $this->escape(Text::_('OSM_CHECK_SUBSCRIPTION_STATUS')); ?></<?php echo $hTag; ?>>
	<?php
	}
	?>
    <div id="reader"></div>
</div>