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
class EShopViewZones extends EShopViewList
{
	public function _buildListArray(&$lists, $state)
	{
		$db = Factory::getDbo();
		//Build countries list
		$query = $db->getQuery(true);
		$query->select('id AS value, country_name AS text')
			->from('#__eshop_countries')
			->where('published = 1')
			->order('country_name');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_SELECT_A_COUNTRY'), 'value', 'text');
		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}
		$lists['countries'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'country_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" onchange = "this.form.submit();" ',
				'list.select'        => $state->country_id,
			]
		);

		return true;
	}
}