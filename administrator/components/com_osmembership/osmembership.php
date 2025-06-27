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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

//Require the controller
if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_osmembership'))
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

$language = Factory::getApplication()->getLanguage();

$language->load('com_osmembershipcommon', JPATH_ADMINISTRATOR);
$language->load('com_osmembership', JPATH_ADMINISTRATOR, null, true);

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

// Setup database to work with multilingual site if needed
if (Multilanguage::isEnabled() && !OSMembershipHelper::isSyncronized())
{
	OSMembershipHelper::setupMultilingual();
}

if (PluginHelper::isEnabled('osmembership', 'mpdf')
	&& !is_dir(JPATH_ROOT . '/plugins/osmembership/mpdf/mpdf/ttfonts'))
{
	Factory::getApplication()->enqueueMessage(
		'Please access to Tools -> Download MPDF Fonts to have PDF files generated properly',
		'warning'
	);
}

$config = include JPATH_ADMINISTRATOR . '/components/com_osmembership/config.php';

$input = new MPFInput();
MPFController::getInstance($input->getCmd('option'), $input, $config)
	->execute()
	->redirect();
