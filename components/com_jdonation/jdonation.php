<?php

/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
use Joomla\CMS\Factory;

error_reporting(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE);


include JPATH_ADMINISTRATOR . '/components/com_jdonation/config.php';
require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/loader.php';
//Prepare controller input
if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
{
	$source = Factory::getApplication()->input;
    $active = Factory::getApplication()->getMenu()->getActive();
	$view	= $source->get('view');
    if ($active->component != 'com_jdonation' && strtoupper($source->getMethod()) === 'GET' && $view != "complete") 
	{
        $source->set('view', 'donation');
    }
}
else
{
	$source = null;
}

DonationHelper::prepareRequestData();
$input = new OSFInput($source);

$task  = $input->getCmd('task', '');
//Handle BC for existing payment plugins
if ($task == 'payment_confirm' || $task == 'recurring_donation_confirm')
{
	//Lets Donation controller handle these tasks
	$input->set('task', 'donation.' . $task);
}
OSFController::getInstance($input->getCmd('option', null), $input, $jdConfig)
	->execute()
	->redirect();
