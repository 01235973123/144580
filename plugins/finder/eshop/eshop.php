<?php
/**
 * @package            Joomla
 * @subpackage         EShop
 * @author             Giang Dinh Truong
 * @copyright          Copyright (C) 2010 - 2022 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

if (version_compare(JVERSION, '4.0.0', 'ge'))
{
	require_once __DIR__ . '/eshop.j4.php';
}
else
{
	require_once __DIR__ . '/eshop.j3.php';
}