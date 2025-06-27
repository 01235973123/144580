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
class EShopViewTaxrates extends EShopViewList
{
	public function _buildListArray(&$lists, $state)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, geozone_name')
			->from('#__eshop_geozones')
			->where('published=1')
			->order('geozone_name');
		$db->setQuery($query);
		$options             = [];
		$options[]           = HTMLHelper::_('select.option', 0, Text::_('ESHOP_GEOZONE'), 'id', 'geozone_name');
		$options             = array_merge($options, $db->loadObjectList());
		$lists['geozone_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'geozone_id',
			' class="input-xlarge form-select" onchange="submit();" ',
			'id',
			'geozone_name',
			$state->geozone_id
		);

		return true;
	}
}