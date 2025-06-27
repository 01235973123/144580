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

use Joomla\Filesystem\File;

/**
 * EShop Component Category Model
 *
 * @package    Joomla
 * @subpackage EShop
 * @since      1.5
 */
class EShopModelField extends EShopModel
{

	public static $protectedFields = ['firstname', 'email', 'address_1'];

	public function __construct($config)
	{
		$config['translatable']        = true;
		$config['translatable_fields'] = ['title', 'description', 'place_holder', 'values', 'default_values', 'validation_error_message'];
		parent::__construct($config);
	}

	public function store(&$data)
	{
		$db = $this->getDbo();
		if (is_file(JPATH_ADMINISTRATOR . '/components/com_eshop/tables/' . $this->name . '.php'))
		{
			$row = $this->getTable($this->name, $this->_component . 'Table');
		}
		else
		{
			$row = new EShopTable($this->_tableName, 'id', $db);
		}
		if (isset($data['validation_rule']))
		{
			$data['validation_rule'] = implode('|', $data['validation_rule']);
		}
		if ($data['id'])
		{
			$row->load($data['id']);
			if ($row->is_core)
			{
				unset($data['fieldtype']);
			}
			if (in_array($row->name, self::$protectedFields))
			{
				unset($data['name']);
				unset($data['required']);
				unset($data['published']);
				unset($data['address_type']);
			}
		}
		parent::store($data);
		//We need to
		$row->load($data['id']);
		if (!$row->is_code)
		{
			//Alter table
			$addressFields = array_keys($db->getTableColumns('#__eshop_addresses'));
			$orderFields   = array_keys($db->getTableColumns('#__eshop_orders'));
			if (!in_array($row->name, $addressFields))
			{
				$sql = 'ALTER TABLE  `#__eshop_addresses` ADD  `' . $row->name . '` TEXT NULL DEFAULT NULL;';
				$db->setQuery($sql);
				$db->execute();
			}
			if ($row->address_type == 'A' || $row->address_type == 'B')
			{
				$fieldName = 'payment_' . $row->name;
				if (!in_array($fieldName, $orderFields))
				{
					$sql = 'ALTER TABLE  `#__eshop_orders` ADD  `' . $fieldName . '` TEXT NULL DEFAULT NULL;';
					$db->setQuery($sql);
					$db->execute();
				}
			}
			if ($row->address_type == 'A' || $row->address_type == 'S')
			{
				$fieldName = 'shipping_' . $row->name;
				if (!in_array($fieldName, $orderFields))
				{
					$sql = 'ALTER TABLE  `#__eshop_orders` ADD  `' . $fieldName . '` TEXT NULL DEFAULT NULL;';
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}

		return true;
	}

	/**
	 * Delete custom fields, we need to prevent users from deleting core fields
	 *
	 * @see EShopModel::delete()
	 */
	public function delete($cid = [])
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__eshop_fields')
			->where('is_core=1');
		$db->setQuery($query);
		$coreFieldIds      = $db->loadColumn();
		$deletableFieldIds = array_diff($cid, $coreFieldIds);
		if (count($deletableFieldIds))
		{
			if (is_file(JPATH_ADMINISTRATOR . '/components/com_eshop/tables/' . $this->name . '.php'))
			{
				$row = $this->getTable($this->name, $this->_component . 'Table');
			}
			else
			{
				$row = new EShopTable($this->_tableName, 'id', $db);
			}
			$addressFields = array_keys($db->getTableColumns('#__eshop_addresses'));
			$orderFields   = array_keys($db->getTableColumns('#__eshop_orders'));
			foreach ($deletableFieldIds as $fieldId)
			{
				$row->load($fieldId);
				$fieldName = $row->name;
				if (in_array($fieldName, $addressFields))
				{
					$sql = 'ALTER TABLE #__eshop_addresses DROP COLUMN `' . $fieldName . '`';
					$db->setQuery($sql);
					$db->execute();
				}
				if ($row->address_type == 'A' || $row->address_type == 'B')
				{
					$fieldName = 'payment_' . $row->name;
					if (in_array($fieldName, $orderFields))
					{
						$sql = 'ALTER TABLE #__eshop_orders DROP COLUMN `' . $fieldName . '`';
						$db->setQuery($sql);
						$db->execute();
					}
				}
				if ($row->address_type == 'A' || $row->address_type == 'S')
				{
					$fieldName = 'shipping_' . $row->name;
					if (in_array($fieldName, $orderFields))
					{
						$sql = 'ALTER TABLE #__eshop_orders DROP COLUMN `' . $fieldName . '`';
						$db->setQuery($sql);
						$db->execute();
					}
				}
			}
			parent::delete($deletableFieldIds);

			return true;
		}
		else
		{
			return false;
		}
	}

	public function publish($cid, $state)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__eshop_fields')
			->where('name IN ("' . implode('","', self::$protectedFields) . '")');
		$db->setQuery($query);
		$coreFieldIds = $db->loadColumn();
		$cid          = array_diff($cid, $coreFieldIds);

		return parent::publish($cid, $state);
	}

	public function required($cid, $state)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__eshop_fields')
			->where('name IN ("' . implode('","', self::$protectedFields) . '")');
		$db->setQuery($query);
		$coreFieldIds = $db->loadColumn();
		$cid          = array_diff($cid, $coreFieldIds);
		if (count($cid))
		{
			if (is_file(JPATH_ADMINISTRATOR . '/components/com_eshop/tables/' . $this->name . '.php'))
			{
				$row = $this->getTable($this->name, $this->_component . 'Table');
			}
			else
			{
				$row = new EShopTable($this->_tableName, 'id', $db);
			}
			foreach ($cid as $fieldId)
			{
				$validationString = $row->validation_rules_string;
				$row->load($fieldId);
				$row->required = $state;
				if ($state)
				{
					if (strpos($validationString, 'required') === false)
					{
						if ($validationString)
						{
							$validationString = 'required|' . $validationString;
						}
						else
						{
							$validationString = 'required';
						}
					}
				}
				else
				{
					$validationString = str_replace('required|', '', $validationString);
				}
				$row->validation_rules_string = $validationString;
				$row->store();
			}
		}

		return true;
	}
}