<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewQuestions extends EShopViewList
{
	/**
	 *
	 * @var $nullDate
	 */
	protected $nullDate;

	public function _buildListArray(&$lists, $state)
	{
		$db             = Factory::getDbo();
		$nullDate       = $db->getNullDate();
		$this->nullDate = $nullDate;
	}

	/**
	 * Override Build Toolbar function, only need Delete, Edit and Download Invoice
	 */
	public function _buildToolbar()
	{
		$viewName   = $this->getName();
		$controller = EShopInflector::singularize($this->getName());
		ToolbarHelper::title(Text::_($this->lang_prefix . '_' . strtoupper($viewName)));
		ToolbarHelper::deleteList(Text::_($this->lang_prefix . '_DELETE_' . strtoupper($this->getName()) . '_CONFIRM'), $controller . '.remove');
	}
}