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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
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
class EShopViewCustomers extends EShopViewList
{
	/**
	 * Build all the lists items used in the form and store it into the array
	 *
	 * @param  $lists
	 *
	 * @return boolean
	 */
	public function _buildListArray(&$lists, $state)
	{
		$input = Factory::getApplication()->input;
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, b.customergroup_name AS text')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.customergroup_name');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_SELECT_A_CUSTOMER_GROUP'), 'value', 'text');

		if (isset($rows) && count($rows))
		{
			$options = array_merge($options, $rows);
		}

		$lists['customergroup_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'customergroup_id',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => ' class="input-xlarge form-select" onchange="Joomla.submitform();" ',
				'list.select'        => $input->get('customergroup_id'),
			]
		);
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