<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2013 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class EShopCustomer
{
	/**
	 * Joomla User ID associated with the customer
	 *
	 * @var int
	 */
	protected $userId;

	/**
	 * Constructor
	 *
	 * @param $userId
	 */
	public function __construct($userId = 0)
	{
		$this->userId = $userId ?: Factory::getUser()->id;
	}

	/**
	 * Function to get customer group id of the user
	 *
	 * @return int
	 */
	public function getCustomerGroupId()
	{
		$customerGroupId = 0;

		if ($this->userId)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('customergroup_id')
				->from('#__eshop_customers')
				->where('customer_id = ' . (int) $this->userId);
			$db->setQuery($query);
			$customerGroupId = (int) $db->loadResult();
		}

		if (!$customerGroupId)
		{
			$customerGroupId = (int) EShopHelper::getConfigValue('customergroup_id');
		}

		return $customerGroupId;
	}

	/**
	 *
	 * Function to get Customer Group Name of a specific customer group
	 *
	 * @param   int     $customerGroupId
	 * @param   string  $langCode
	 *
	 * @return string
	 */
	public function getCustomerGroupName($customerGroupId, $langCode)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		if (!$langCode)
		{
			$langCode = Factory::getLanguage()->getTag();
		}

		$query->select('customergroup_name')
			->from('#__eshop_customergroupdetails')
			->where('customergroup_id = ' . intval($customerGroupId))
			->where('language = ' . $db->quote($langCode));
		$db->setQuery($query);
		$customerGroupName = $db->loadResult();

		return $customerGroupName;
	}
}