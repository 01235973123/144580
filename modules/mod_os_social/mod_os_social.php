<?php
/**
 * @subpackage  mod_os_social
 * @author      Dang Thuc Dam
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';
$doc =  JFactory::getDocument();

$module_name             = basename(dirname(__FILE__));
$url = JURI::base(true) . '/modules/' . $module_name . '/asset/';
$doc->addStyleSheet($url . 'css.css');

require JModuleHelper::getLayoutPath('mod_os_social');


//echo $id;
?>
