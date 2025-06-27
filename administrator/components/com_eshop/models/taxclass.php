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
class EShopModelTaxclass extends EShopModel
{

	public function store(&$data)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		if ($data['id'])
		{
			$query->delete('#__eshop_taxrules')
				->where('taxclass_id = ' . (int) $data['id']);
			$db->setQuery($query);
			$db->execute();
		}
		parent::store($data);
		//save new data
		if (isset($data['tax_id']))
		{
			$taxGroupId  = $data['id'];
			$taxIds      = $data['tax_id'];
			$baseonIds   = $data['based_on'];
			$priorityIds = $data['priority'];
			$query->clear();
			$query->insert('#__eshop_taxrules')->columns('taxclass_id, tax_id, based_on, priority');
			foreach ($taxIds as $key => $taxId)
			{
				$baseonId   = $db->quote($baseonIds[$key]);
				$priorityId = $db->quote($priorityIds[$key]);
				$query->values("$taxGroupId, $taxId, $baseonId, $priorityId");
			}
			$db->setQuery($query);
			$db->execute();
		}

		//Process assign products to current tax class
		if (isset($data['products_list']) && $data['products_list'][0] == '-1')
		{
			$query->clear();
			$query->update('#__eshop_products')
				->set('product_taxclass_id = ' . (int) $data['id']);
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			if (isset($data['products_no_tax']) && $data['products_no_tax'][0] == '-1')
			{
				$query->clear();
				$query->select('a.id AS value')
					->from('#__eshop_products AS a')
					->where('a.published = 1')
					->where('product_taxclass_id = 0');
				$db->setQuery($query);
				$data['products_no_tax'] = $db->loadColumn();
			}
			$productsList   = $data['products_list'] ?? [];
			$productsNoTax  = $data['products_no_tax'] ?? [];
			$productsSelect = array_merge($productsList, $productsNoTax);
			$query->clear();
			$query->update('#__eshop_products')
				->set('product_taxclass_id = 0')
				->where('product_taxclass_id = ' . (int) $data['id']);
			$db->setQuery($query);
			$db->execute();
			if (count($productsSelect))
			{
				$query->clear();
				$query->update('#__eshop_products')
					->set('product_taxclass_id = ' . (int) $data['id'])
					->where('id in (' . implode(',', $productsSelect) . ')');
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Function to copy taxclass
	 * @see EShopModel::copy()
	 */
	public function copy($id)
	{
		$copiedTaxclassId = parent::copy($id);
		$db               = $this->getDbo();
		$sql              = 'INSERT INTO #__eshop_taxrules'
			. ' (taxclass_id, tax_id, based_on, priority)'
			. ' SELECT ' . $copiedTaxclassId . ', tax_id, based_on, priority'
			. ' FROM #__eshop_taxrules'
			. ' WHERE taxclass_id = ' . intval($id);
		$db->setQuery($sql);
		$db->execute();

		return $copiedTaxclassId;
	}

}