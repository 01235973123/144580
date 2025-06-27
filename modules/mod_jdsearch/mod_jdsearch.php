<?php
/**
 * @version        5.10.0
 * @package        Joomla
 * @subpackage     Edocman
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2011 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
error_reporting(0);
$moduleclass_sfx	= htmlspecialchars($params->get('moduleclass_sfx'));
$itemId				= $params->get('item_id');
$input_style		= $params->get('input_style','input-medium');
require_once JPATH_ROOT .'/components/com_jdonation/helper/helper.php';
DonationHelper::loadLanguage();

$input				= Factory::getApplication()->input;
$text				= $input->getString('filter_search');
$defaultText		= JText::_('JD_SEARCH_WORD');
if (!$text)
{
	$text			= $defaultText;
}
$text				= htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
require(JModuleHelper::getLayoutPath('mod_jdsearch'));