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

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewAttributes extends EShopViewList
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

		//Build attribute group list
		$query->clear();
		$query->select('a.id AS value, b.attributegroup_name AS text')
			->from('#__eshop_attributegroups AS a')
			->innerJoin('#__eshop_attributegroupdetails AS b ON (a.id = b.attributegroup_id)')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.attributegroup_name');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$options = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_SELECT_AN_ATTRIBUTE_GROUP'), 'value', 'text');

		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}

		$lists['attributegroup_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'attributegroup_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" onchange="Joomla.submitform();"',
				'list.select'        => $input->get('attributegroup_id'),
			]
		);
	}
}