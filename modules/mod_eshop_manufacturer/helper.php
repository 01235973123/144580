<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2011 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

class modEShopManufacturerHelper
{
	/**
	 *
	 * Function to get manufacturers
	 *
	 * @param   object  $params
	 *
	 * @return manufacturers object list
	 */
	static public function getItems($params)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.manufacturer_image, b.manufacturer_name')
			->from('#__eshop_manufacturers AS a')
			->innerJoin('#__eshop_manufacturerdetails AS b ON (a.id = b.manufacturer_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		
		$manufacturers = $params->get('manufacturers');
		
		if (!empty($manufacturers))
		{
			$query->where('a.id IN (' . implode(',', $manufacturers) . ')');
		}

		$manufacturerDefaultSorting = EShopHelper::getConfigValue('manufacturer_default_sorting', 'name-asc');

		switch ($manufacturerDefaultSorting)
		{
			case 'name-desc':
				$query->order('b.manufacturer_name DESC LIMIT 0, ' . $params->get('manufacturers_total', 10));
				break;
			case 'ordering-asc':
				$query->order('a.ordering ASC LIMIT 0, ' . $params->get('manufacturers_total', 10));
				break;
			case 'ordering-desc':
				$query->order('a.ordering DESC LIMIT 0, ' . $params->get('manufacturers_total', 10));
				break;
			case 'id-asc':
				$query->order('a.id ASC LIMIT 0, ' . $params->get('manufacturers_total', 10));
				break;
			case 'id-desc':
				$query->order('a.id DESC LIMIT 0, ' . $params->get('manufacturers_total', 10));
				break;
			case 'random':
				$query->order('RAND() LIMIT 0, ' . $params->get('manufacturers_total', 10));
				break;
			case 'name-asc':
			default:
				$query->order('b.manufacturer_name ASC LIMIT 0, ' . $params->get('manufacturers_total', 10));
				break;
		}

		//Check viewable of customer groups
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$query->where(
			'((a.manufacturer_customergroups = "") OR (a.manufacturer_customergroups IS NULL) OR (a.manufacturer_customergroups = "' . $customerGroupId . '") OR (a.manufacturer_customergroups LIKE "' . $customerGroupId . ',%") OR (a.manufacturer_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.manufacturer_customergroups LIKE "%,' . $customerGroupId . '"))'
		);
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}