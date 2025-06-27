<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;

error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);
//error_reporting(E_ALL);
if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_jdonation'))
{
	throw new Exception(403, Text::_('JERROR_ALERTNOAUTHOR'));
}
include JPATH_ADMINISTRATOR . '/components/com_jdonation/config.php';
require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/loader.php';

if (Multilanguage::isEnabled() && !DonationHelper::isSynchronized())
{
	DonationHelper::setupMultilingual();
}

$input = new OSFInput();
OSFController::getInstance($input->getCmd('option'), $input, $jdConfig)
	->execute()
	->redirect();
