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

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelWeight extends EShopModel
{
	public function __construct($config)
	{
		$config['translatable']        = true;
		$config['translatable_fields'] = ['weight_name', 'weight_unit'];
		parent::__construct($config);
	}

	/**
	 * Method to remove weights
	 *
	 * @access    public
	 * @return boolean True on success
	 * @since     1.5
	 */
	public function delete($cid = [])
	{
		if (count($cid))
		{
			$db    = $this->getDbo();
			$cids  = implode(',', $cid);
			$query = $db->getQuery(true);
			$query->delete('#__eshop_weights')
				->where('id IN (' . $cids . ')')
				->where('id NOT IN (SELECT  DISTINCT(product_weight_id) FROM #__eshop_products)');
			$db->setQuery($query);
			if (!$db->execute())
				//Removed error
			{
				return 0;
			}
			$numItemsDeleted = $db->getAffectedRows();
			//Delete details records
			$query->clear();
			$query->delete('#__eshop_weightdetails')
				->where('weight_id IN (' . $cids . ')')
				->where('weight_id NOT IN (SELECT  DISTINCT(product_weight_id) FROM #__eshop_products)');
			$db->setQuery($query);
			if (!$db->execute())
				//Removed error
			{
				return 0;
			}
			if ($numItemsDeleted < count($cid))
			{
				//Removed warning
				return 2;
			}
		}

		//Removed success
		return 1;
	}
}