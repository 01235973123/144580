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
class EShopViewAttribute extends EShopViewForm
{

	public function _buildListArray(&$lists, $item)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id AS value, b.attributegroup_name  AS text')
			->from('#__eshop_attributegroups AS a')
			->innerJoin('#__eshop_attributegroupdetails AS b ON (a.id = b.attributegroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'value', 'text');

		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}

		$lists['attributegroups'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'attributegroup_id',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select required"',
				'list.select'        => $item->attributegroup_id,
			]
		);
	}
}