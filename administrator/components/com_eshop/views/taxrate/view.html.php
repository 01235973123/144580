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
class EShopViewTaxrate extends EShopViewForm
{
	public function _buildListArray(&$lists, $item)
	{
		$db                = Factory::getDbo();
		$options           = [];
		$options[]         = HTMLHelper::_('select.option', 'P', Text::_('ESHOP_PERCENTAGE'));
		$options[]         = HTMLHelper::_('select.option', 'F', Text::_('ESHOP_FIXED_AMOUNT'));
		$lists['tax_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'tax_type',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$item->tax_type
		);
		//get list geozone
		$query = $db->getQuery(true);
		$query->select('id, geozone_name')
			->from('#__eshop_geozones')
			->where('published = 1')
			->order('geozone_name');
		$db->setQuery($query);
		$rows                = $db->loadObjectList();
		$lists['geozone_id'] = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'geozone_id',
			'class="input-xlarge form-select"',
			'id',
			'geozone_name',
			$item->geozone_id
		);
		//get list customer group
		$query->clear();
		$query->select('a.id AS value, b.customergroup_name AS text')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.customergroup_name');
		$db->setQuery($query);
		$options = $db->loadObjectList();
		if ($item->id)
		{
			$query->clear();
			$query->select('customergroup_id')
				->from('#__eshop_taxcustomergroups')
				->where('tax_id = ' . intval($item->id));
			$db->setQuery($query);
			$selectedItems = $db->loadColumn();
		}
		else
		{
			$selectedItems = [];
		}
		$lists['customergroup_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'customergroup_id[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
				'list.select'        => $selectedItems,
			]
		);

		return true;
	}
}