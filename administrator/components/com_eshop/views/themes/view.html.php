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
class EShopViewThemes extends EShopViewList
{

	/**
	 * Override Build Toolbar function, only need Publish, Unpublish and Delete button
	 */
	public function _buildToolbar()
	{
		$viewName   = $this->getName();
		$controller = EShopInflector::singularize($this->getName());
		ToolbarHelper::title(Text::_($this->lang_prefix . '_' . strtoupper($viewName)));
		ToolbarHelper::publishList($controller . '.publish');
		ToolbarHelper::unpublishList($controller . '.unpublish');
		ToolbarHelper::deleteList(Text::_($this->lang_prefix . '_DELETE_' . strtoupper($this->getName()) . '_CONFIRM'), $controller . '.remove');
	}
}