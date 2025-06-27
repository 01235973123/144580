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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

/**
 * @var string $selectedState
 */

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

OSMembershipHelper::addLangLinkForAjax();
OSMembershipHelperJquery::validateForm();
Factory::getApplication()->getDocument()
	->addScriptOptions('selectedState', $selectedState)
	->addScriptOptions('maxErrorsPerField', (int) $this->config->max_errors_per_field)
	->getWebAssetManager()
	->useScript('core')
	->addInlineScript('var siteUrl = "' . Uri::root(true) . '/";')
	->registerAndUseScript('com_osmembership.paymentmethods', 'media/com_osmembership/assets/js/paymentmethods.min.js')
	->registerAndUseScript('com_osmembership.site-group-default', 'media/com_osmembership/js/site-group-default.min.js');