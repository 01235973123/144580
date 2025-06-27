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
class EShopViewReviews extends EShopViewList
{
	/**
	 *
	 * @var $nullDate
	 */
	protected $nullDate;

	public function __construct($config)
	{
		$config['name'] = 'reviews';
		parent::__construct($config);
	}

	public function _buildListArray(&$lists, $state)
	{
		$db             = Factory::getDbo();
		$nullDate       = $db->getNullDate();
		$this->nullDate = $nullDate;
	}

	/**
	 * Build the toolbar for view list
	 */
	public function _buildToolbar()
	{
		$viewName   = $this->getName();
		$controller = EShopInflector::singularize($this->getName());
		ToolbarHelper::title(Text::_($this->lang_prefix . '_' . strtoupper($viewName)));

		$canDo = EShopHelper::getActions($viewName);

		if ($canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList(Text::_($this->lang_prefix . '_DELETE_' . strtoupper($this->getName()) . '_CONFIRM'), $controller . '.remove');
		}
		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::editList($controller . '.edit');
		}
		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew($controller . '.add');
		}
		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publishList($controller . '.publish');
			ToolbarHelper::unpublishList($controller . '.unpublish');
		}
	}
}