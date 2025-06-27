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
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelCustomer extends EShopModel
{
	public function __construct($config)
	{
		parent::__construct($config);
	}

	/**
	 * Load the data
	 *
	 */
	public function _loadData()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, b.name, b.username')
			->from($this->_tableName . ' AS a')
			->leftJoin('#__users AS b ON (a.customer_id = b.id)')
			->where('a.id = ' . intval($this->_id));
		$db->setQuery($query);
		$row         = $db->loadObject();
		$this->_data = $row;
	}

	/**
	 * Init Category data
	 *
	 */
	public function _initData()
	{
		$db          = $this->getDbo();
		$row         = new EShopTable($this->_tableName, 'id', $db);
		$this->_data = $row;
	}

	/**
	 * Function to store product
	 * @see EShopModel::store()
	 */
	public function store(&$data)
	{
		if (!$data['id'] && $data['username'] && $data['password'])
		{
			// Store this account into the system
			$params      = ComponentHelper::getParams('com_users');
			$newUserType = $params->get('new_usertype', 2);

			$data['groups']    = [];
			$data['groups'][]  = $newUserType;
			$data['block']     = 0;
			$data['name']      = $data['firstname'] . ' ' . $data['lastname'];
			$data['password1'] = $data['password2'] = $data['password'];
			$data['email1']    = $data['email2'] = $data['email'];
			$user              = new User();
			$user->bind($data);
			if (!$user->save())
			{
				Factory::getApplication()->enqueueMessage($user->getError(), 'error');
				Factory::getApplication()->redirect('index.php?option=com_eshop&view=customers');
			}
			$data['customer_id'] = $user->id;
		}
		// Check and store address
		$addresses     = $data['addresses'];
		$removeAddress = $data['remove_address'];
		$db            = Factory::getDbo();
		$query         = $db->getQuery(true);
		$query->delete('#__eshop_addresses')
			->where('customer_id = ' . intval($data['customer_id']))
			->order('id');

		if (isset($addresses))
		{
			$addressArr = [];

			for ($i = 0; $n = count($addresses), $i < $n; $i++)
			{
				if ($addresses[$i] > 0 && $removeAddress[$i] == 0)
				{
					$addressArr[] = $addresses[$i];
				}
			}
			if (count($addressArr))
			{
				$query->where('id NOT IN (' . implode(',', $addressArr) . ')');
			}
		}

		$db->setQuery($query);
		$db->execute();

		if (isset($addresses))
		{
			for ($i = 0; $n = count($addresses), $i < $n; $i++)
			{
				$fields      = EShopHelper::getFormFields();
				$addressData = [];

				foreach ($fields as $field)
				{
					if ($field->name == 'country_id' || $field->name == 'zone_id')
					{
						$addressData[$field->name] = $data['address_' . $field->name . '_' . ($i + 1)];
					}
					else
					{
						$addressData[$field->name] = $data['address_' . $field->name][$i];
					}
				}

				if (!isset($addressData['country_id']))
				{
					$addressData['country_id'] = EShopHelper::getConfigValue('country_id');
				}

				if (!isset($addressData['zone_id']))
				{
					$addressData['zone_id'] = EShopHelper::getConfigValue('zone_id');
				}

				$row = Table::getInstance('Eshop', 'Address');
				$row->load($addresses[$i]);
				$row->id = $addresses[$i];
				$row->bind($addressData);

				if (!$row->id)
				{
					$row->created_date = Factory::getDate()->toSql();
				}

				$row->modified_date = Factory::getDate()->toSql();
				$row->store();
			}
		}

		parent::store($data);

		return true;
	}

	/**
	 *
	 * Function to save new address for customer
	 */
	public function saveNewAddress($data)
	{
		$row = Table::getInstance('Eshop', 'Address');

		$fields      = EShopHelper::getFormFields();
		$addressData = [];

		foreach ($fields as $field)
		{
			$addressData[$field->name] = $data['new_address_' . $field->name];
		}

		if (!isset($addressData['country_id']))
		{
			$addressData['country_id'] = EShopHelper::getConfigValue('country_id');
		}

		if (!isset($addressData['zone_id']))
		{
			$addressData['zone_id'] = EShopHelper::getConfigValue('zone_id');
		}

		$row->bind($addressData);
		$row->customer_id   = $data['customer_id'];
		$row->created_date  = Factory::getDate()->toSql();
		$row->modified_date = Factory::getDate()->toSql();

		if ($row->store())
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
