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
class EShopViewMessages extends EShopViewList
{
	/**
	 * Build the toolbar for view list
	 */
	public function _buildToolbar()
	{
		$controller = EShopInflector::singularize($this->getName());
		ToolbarHelper::editList($controller . '.edit');
		$viewName = $this->getName();
		ToolbarHelper::title(Text::_($this->lang_prefix . '_' . strtoupper($viewName)));
	}
}