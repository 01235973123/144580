<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

//defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

class EShopAPI
{
	/**
	 * Method to check if a customer exist in Eshop or not
	 *
	 * @param   int  $userId
	 *
	 * @return bool
	 */
	public static function customerExist($userId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__eshop_customers')
			->where('customer_id = ' . (int) $userId);
		$db->setQuery($query);
		$total = $db->loadResult();

		if ($total)
		{
			return true;
		}

		return false;
	}

	/**
	 *
	 * Function to add a customer
	 *
	 * @param   int    $userId
	 * @param   array  $data  (customergroup_id, firstname, lastname, email, telephone, tax, company, company_id, address_1, address_2, city, postcode, country_code (iso code 3), zone_code)
	 */
	public static function addCustomer($userId, $data)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__eshop_customers')
			->where('customer_id = ' . intval($userId));
		$db->setQuery($query);
		$id  = $db->loadResult();
		$row = Table::getInstance('Eshop', 'Customer');

		if ($id)
		{
			if (isset($data['customergroup_id']) && $data['customergroup_id'] > 0)
			{
				$row->load($id);
				$row->customergroup_id = $data['customergroup_id'];
				$row->store();
			}
		}
		else
		{
			// Store address for customer first
			$query->clear()
				->delete('#__eshop_addresses')
				->where('customer_id = ' . intval($userId));
			$db->setQuery($query);
			$db->execute();
			$addressRow = Table::getInstance('Eshop', 'Address');
			$addressRow->bind($data);
			$addressRow->customer_id = $userId;

			if (isset($data['country_code']) && $data['country_code'] != '')
			{
				$query->clear()
					->select('id')
					->from('#__eshop_countries')
					->where('iso_code_3 = "' . $data['country_code'] . '"');
				$db->setQuery($query);
				$countryId = $db->loadResult();

				if ($countryId > 0)
				{
					$addressRow->country_id = $countryId;
				}
			}

			if (isset($data['zone_code']) && $data['zone_code'] != '')
			{
				$query->clear()
					->select('id')
					->from('#__eshop_zones')
					->where('zone_code = "' . $data['zone_code'] . '"');
				if ($countryId)
				{
					$query->where('country_id = ' . intval($countryId));
				}
				$db->setQuery($query);
				$zoneId = $db->loadResult();

				if ($zoneId > 0)
				{
					$addressRow->zone_id = $zoneId;
				}
			}

			$addressRow->created_date  = gmdate('Y-m-d H:i:s');
			$addressRow->modified_date = gmdate('Y-m-d H:i:s');
			$addressRow->store();
			$addressId = $addressRow->id;
			// Store customer
			$row->bind($data);
			$row->customer_id = $userId;

			if (!$row->customergroup_id)
			{
				$row->customergroup_id = EShopHelper::getConfigValue('customergroup_id');
			}

			$row->address_id    = $addressId;
			$row->created_date  = gmdate('Y-m-d H:i:s');
			$row->modified_date = gmdate('Y-m-d H:i:s');
			$row->published     = 1;
			$row->store();
		}
	}

	/**
	 * Function to get customer groups in EShop
	 *
	 * @return array customer groups object list
	 */
	public static function getCustomerGroups()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, b.customergroup_name')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);

		return $db->loadObjectList();
	}


	/**
	 *
	 * Function to set customer group for a specific customer
	 *
	 * @param   int  $userId
	 * @param   int  $customerGroupId
	 */
	public static function setCustomerGroup($userId, $customerGroupId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__eshop_customers')
			->set('customergroup_id = ' . intval($customerGroupId))
			->where('customer_id = ' . intval($userId));
		$db->setQuery($query);
		$db->execute();
	}
}