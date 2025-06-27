<?php
/**
 * @version        1.0
 * @package        Joomla
 * @subpackage     OSFramework
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EShopViewList extends HtmlView
{
	/**
	 *
	 * @var $state
	 */
	protected $state;

	/**
	 *
	 * @var $lists
	 */
	protected $lists;

	/**
	 *
	 * @var $items
	 */
	protected $items;

	/**
	 *
	 * @var $pagination
	 */
	protected $pagination;

	/**
	 *
	 * @var $dropDownList
	 */
	protected static $dropDownList = [];

	/**
	 *
	 * @var $lang_prefix
	 */
	public $lang_prefix = ESHOP_LANG_PREFIX;

	public function display($tpl = null)
	{
		// Check access first
		$mainframe   = Factory::getApplication();
		$accessViews = ['orders', 'reviews', 'payments', 'shippings', 'themes', 'customers', 'customergroups', 'coupons', 'taxclasses', 'taxrates'];
		foreach ($accessViews as $view)
		{
			if ($view == $this->getName() && !Factory::getUser()->authorise('eshop.' . $view, 'com_eshop'))
			{
				$mainframe->enqueueMessage(Text::_('ESHOP_ACCESS_NOT_ALLOW'), 'error');
				$mainframe->redirect('index.php?option=com_eshop&view=dashboard');
			}
		}
		$state      = $this->get('State');
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		$this->state = $state;

		$lists                 = [];
		$lists['order_Dir']    = $state->filter_order_Dir;
		$lists['order']        = $state->filter_order;
		$lists['filter_state'] = HTMLHelper::_('grid.state', $state->filter_state, Text::_('ESHOP_PUBLISHED'), Text::_('ESHOP_UNPUBLISHED'));
		$this->_buildListArray($lists, $state);

		$this->lists      = $lists;
		$this->items      = $items;
		$this->pagination = $pagination;

		$this->_buildToolbar();

		parent::display($tpl);
	}

	/**
	 * Build all the lists items used in the form and store it into the array
	 *
	 * @param  $lists
	 *
	 * @return boolean
	 */
	public function _buildListArray(&$lists, $state)
	{
		return true;
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

		if ($canDo->get('core.manage'))
		{
			ToolbarHelper::deleteList(Text::_($this->lang_prefix . '_DELETE_' . strtoupper($this->getName()) . '_CONFIRM'), $controller . '.remove');
			ToolbarHelper::editList($controller . '.edit');
			ToolbarHelper::addNew($controller . '.add');
			ToolbarHelper::custom($controller . '.copy', 'copy.png', 'copy_f2.png', Text::_('ESHOP_COPY'), true);
			ToolbarHelper::publishList($controller . '.publish');
			ToolbarHelper::unpublishList($controller . '.unpublish');
		}
	}

	public static function renderDropdownList($item = '')
	{
		$html   = [];
		$html[] = '<button data-toggle="dropdown" class="dropdown-toggle btn btn-micro">';
		$html[] = '<span class="caret"></span>';
		if ($item)
		{
			$html[] = '<span class="element-invisible">' . Text::sprintf('JACTIONS', $item) . '</span>';
		}
		$html[]             = '</button>';
		$html[]             = '<ul class="dropdown-menu">';
		$html[]             = implode('', self::$dropDownList);
		$html[]             = '</ul>';
		self::$dropDownList = null;

		return implode('', $html);
	}

	public static function addDropdownList($label, $icon = '', $i = '', $task = '')
	{
		self::$dropDownList[] = '<li><a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $task . '\')">'
			. ($icon ? '<span class="icon-' . $icon . '"></span> ' : '')
			. $label
			. '</a>'
			. '</li>';
	}
}
