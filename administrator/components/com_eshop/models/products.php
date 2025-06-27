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

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelProducts extends EShopModelList
{
	/**
	 *
	 * Constructor
	 *
	 * @param   array  $config
	 */
	public function __construct($config)
	{
		$config['search_fields']       = ['a.product_sku', 'b.product_name', 'b.product_short_desc', 'b.product_desc'];
		$config['translatable']        = true;
		$config['state_vars']          = [
			'category_id'  => ['', 'cmd', 1],
			'mf_id'        => ['', 'cmd', 1],
			'stock_status' => ['', 'cmd', 1],
		];
		$config['translatable_fields'] = [
			'product_name',
			'product_alias',
			'product_desc',
			'product_short_desc',
			'meta_key',
			'meta_desc',
		];
		parent::__construct($config);
	}

	/**
	 * Get total entities
	 *
	 * @return int
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$db    = $this->getDbo();
			$state = $this->getState();
			$where = $this->_buildContentWhereArray();
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');

			if ($this->translatable)
			{
				$query->from($this->mainTable . ' AS a ')
					->innerJoin(
						EShopInflector::singularize($this->mainTable) . 'details AS b ON (a.id = b.' . EShopInflector::singularize(
							$this->name
						) . '_id)'
					);
			}
			else
			{
				$query->from($this->mainTable . ' AS a ');
			}

			if ($state->category_id)
			{
				$query->innerJoin('#__eshop_productcategories AS c ON (a.id = c.product_id)');
			}

			if ($state->mf_id)
			{
				$where[] = 'a.manufacturer_id = ' . intval($state->mf_id);
			}

			if ($state->stock_status == '1')
			{
				$where[] = 'a.product_quantity > 0';
			}
			elseif ($state->stock_status == '2')
			{
				$where[] = 'a.product_quantity <= 0';
			}

			if (count($where))
			{
				$query->where($where);
			}

			$db->setQuery($query);
			$this->_total = $db->loadResult();
		}

		return $this->_total;
	}

	/**
	 * Basic build Query function.
	 * The child class must override it if it is necessary
	 *
	 * @return string
	 */
	public function _buildQuery()
	{
		$db    = $this->getDbo();
		$state = $this->getState();
		$query = $db->getQuery(true);
		if ($this->translatable)
		{
			$query->select('a.*, ' . implode(', ', $this->translatableFields))
				->from($this->mainTable . ' AS a ')
				->innerJoin(
					EShopInflector::singularize($this->mainTable) . 'details AS b ON (a.id = b.' . EShopInflector::singularize($this->name) . '_id)'
				);
		}
		else
		{
			$query->select('a.*')
				->from($this->mainTable . ' AS a ');
		}
		$where = $this->_buildContentWhereArray();
		if ($state->category_id)
		{
			$query->innerJoin('#__eshop_productcategories AS c ON (a.id = c.product_id)');
		}
		if (count($where))
		{
			$query->where($where);
		}
		$orderby = $this->_buildContentOrderBy();
		if ($orderby != '')
		{
			$query->order($orderby);
		}

		return $query;
	}

	/**
	 * Build an where clause array
	 *
	 * @return array
	 */
	public function _buildContentWhereArray()
	{
		$db    = $this->getDbo();
		$state = $this->getState();
		$where = [];
		if ($state->filter_state == 'P')
		{
			$where[] = ' a.published=1 ';
		}
		elseif ($state->filter_state == 'U')
		{
			$where[] = ' a.published = 0';
		}

		if ($state->search)
		{
			$search = $db->quote('%' . $db->escape($state->search, true) . '%', false);
			if (is_array($this->searchFields))
			{
				$whereOr = [];
				foreach ($this->searchFields as $titleField)
				{
					$whereOr[] = " LOWER($titleField) LIKE " . $search;
				}
				$where[] = ' (' . implode(' OR ', $whereOr) . ') ';
			}
			else
			{
				$where[] = 'LOWER(' . $this->searchFields . ') LIKE ' . $db->quote('%' . $db->escape($state->search, true) . '%', false);
			}
		}

		if ($this->translatable)
		{
			$where[] = 'b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"';
		}

		if ($state->category_id)
		{
			$where[] = 'c.category_id = ' . $state->category_id;
		}

		if ($state->mf_id)
		{
			$where[] = 'a.manufacturer_id = ' . intval($state->mf_id);
		}

		if ($state->stock_status == '1')
		{
			$where[] = 'a.product_quantity > 0';
		}
		elseif ($state->stock_status == '2')
		{
			$where[] = 'a.product_quantity <= 0';
		}

		return $where;
	}

	/**
	 *
	 * Function to process patch update products
	 *
	 * @param   array  $data
	 */
	public function batch($data)
	{
		$db       = $this->getDbo();
		$query    = $db->getQuery(true);
		$setBatch = '';

		//Process manufacturer
		if ($data['manufacturer_id'])
		{
			$setBatch .= 'manufacturer_id = ' . $data['manufacturer_id'] . ', ';
		}

		//Process customer groups
		$productCustomergroups = implode(',', $data['product_customergroups']);

		if (substr($productCustomergroups, 0, 1) != 0)
		{
			$setBatch .= 'product_customergroups = \'' . $productCustomergroups . '\', ';
		}

		//Process published
		if (isset($data['product_published']))
		{
			$setBatch .= 'published = ' . $data['product_published'] . ', ';
		}

		//Process manage stock
		if (isset($data['product_manage_stock']))
		{
			$setBatch .= 'product_manage_stock = ' . $data['product_manage_stock'] . ', ';
		}

		//Process
		if (isset($data['product_stock_display']))
		{
			$setBatch .= 'product_stock_display = ' . $data['product_stock_display'] . ', ';
		}

		//Process
		if (isset($data['product_stock_warning']))
		{
			$setBatch .= 'product_stock_warning = ' . $data['product_stock_warning'] . ', ';
		}

		//Process stock checkout
		if (isset($data['product_stock_checkout']))
		{
			$setBatch .= 'product_stock_checkout = ' . $data['product_stock_checkout'] . ', ';
		}

		//Process inventory global
		if (isset($data['product_inventory_global']))
		{
			$setBatch .= 'product_inventory_global = ' . $data['product_inventory_global'] . ', ';
		}

		//Process length unit
		if ($data['product_length_id'])
		{
			$setBatch .= 'product_length_id = ' . $data['product_length_id'] . ', ';
		}

		//Process weight unit
		if ($data['product_weight_id'])
		{
			$setBatch .= 'product_weight_id = ' . $data['product_weight_id'] . ', ';
		}

		//Process tax class
		if ($data['product_taxclass_id'])
		{
			$setBatch .= 'product_taxclass_id = ' . $data['product_taxclass_id'] . ', ';
		}

		//Process price
		if (isset($data['product_price']) && $data['product_price'] != null)
		{
			$productPrice = trim($data['product_price']);

			if (strpos($productPrice, '+') !== false || strpos($productPrice, '-') !== false)
			{
				$productPrice = str_replace('+', '', $productPrice);

				if (strpos($productPrice, '%') !== false)
				{
					$productPrice = str_replace('%', '', $productPrice);

					$setBatch .= 'product_price = product_price * ' . ($productPrice + 100) . ' / 100' . ', ';
				}
				else
				{
					$setBatch .= 'product_price = product_price + ' . $productPrice . ', ';
				}
			}
			else
			{
				$setBatch .= 'product_price = ' . $productPrice . ', ';
			}
		}

		//Process length
		if (isset($data['product_length']))
		{
			if ($data['product_length'] == null)
			{
				$data['product_length'] = 0;
			}
			$setBatch .= 'product_length = ' . $data['product_length'] . ', ';
		}

		//Process width
		if (isset($data['product_width']))
		{
			if ($data['product_width'] == null)
			{
				$data['product_width'] = 0;
			}
			$setBatch .= 'product_width = ' . $data['product_width'] . ', ';
		}

		//Process height
		if (isset($data['product_height']))
		{
			if ($data['product_height'] == null)
			{
				$data['product_height'] = 0;
			}
			$setBatch .= 'product_height = ' . $data['product_height'] . ', ';
		}

		//Process weight
		if (isset($data['product_weight']))
		{
			if ($data['product_weight'] == null)
			{
				$data['product_weight'] = 0;
			}
			$setBatch .= 'product_weight = ' . $data['product_weight'] . ', ';
		}

		//Process quantity
		if (isset($data['product_quantity']))
		{
			if ($data['product_quantity'] == null)
			{
				$data['product_quantity'] = 0;
			}
			$setBatch .= 'product_quantity = ' . $data['product_quantity'] . ', ';
		}

		//Process threshold
		if (isset($data['product_threshold']))
		{
			if ($data['product_threshold'] == null)
			{
				$data['product_threshold'] = 0;
			}
			$setBatch .= 'product_threshold = ' . $data['product_threshold'] . ', ';
		}

		//Process shipping cost
		if (isset($data['product_shipping_cost']))
		{
			if ($data['product_shipping_cost'] == null)
			{
				$data['product_shipping_cost'] = 0;
			}
			$setBatch .= 'product_shipping_cost = ' . $data['product_shipping_cost'] . ', ';
		}

		//Process shipping cost geo zones
		if (isset($data['product_shipping_cost_geozones']))
		{
			if ($data['product_shipping_cost_geozones'] == null)
			{
				$data['product_shipping_cost_geozones'] = '';
			}
			$setBatch .= 'product_shipping_cost_geozones = ' . $db->quote($data['product_shipping_cost_geozones']) . ', ';
		}

		//Process minimum quantity
		if (isset($data['product_minimum_quantity']))
		{
			if ($data['product_minimum_quantity'] == null)
			{
				$data['product_minimum_quantity'] = 0;
			}
			$setBatch .= 'product_minimum_quantity = ' . $data['product_minimum_quantity'] . ', ';
		}

		//Process maximum quantity
		if (isset($data['product_maximum_quantity']))
		{
			if ($data['product_maximum_quantity'] == null)
			{
				$data['product_maximum_quantity'] = 0;
			}
			$setBatch .= 'product_maximum_quantity = ' . $data['product_maximum_quantity'] . ', ';
		}

		//Process available date
		if (isset($data['product_available_date']))
		{
			if ($data['product_available_date'] == null)
			{
				$data['product_available_date'] = '0000-00-00 00:00:00';
			}
			$setBatch .= 'product_available_date = \'' . $data['product_available_date'] . '\', ';
		}

		//Process end date
		if (isset($data['product_end_date']))
		{
			if ($data['product_end_date'] == null)
			{
				$data['product_end_date'] = '0000-00-00 00:00:00';
			}
			$setBatch .= 'product_end_date = \'' . $data['product_end_date'] . '\', ';
		}

		//Process shipping
		if (isset($data['product_shipping']))
		{
			$setBatch .= 'product_shipping = ' . $data['product_shipping'] . ', ';
		}

		//Process call for price
		if (isset($data['product_call_for_price']))
		{
			$setBatch .= 'product_call_for_price = ' . $data['product_call_for_price'] . ', ';
		}

		//Process stock status
		if ($data['product_stock_status_id'])
		{
			$setBatch .= 'product_stock_status_id = ' . $data['product_stock_status_id'] . ', ';
		}

		//Process featured
		if (isset($data['product_featured']))
		{
			$setBatch .= 'product_featured = ' . $data['product_featured'] . ', ';
		}

		//Process quote mode
		if (isset($data['product_quote_mode']))
		{
			$setBatch .= 'product_quote_mode = ' . $data['product_quote_mode'] . ', ';
		}

		$setBatch = rtrim($setBatch, ', ');

		if ($setBatch != '')
		{
			$query->update('#__eshop_products')
				->set($setBatch)
				->where('id in (' . implode(',', $data['cid']) . ')');
			$db->setQuery($query);
			$db->execute();
		}

		//Process product categories
		if ($data['main_category_id'] > 0)
		{
			$query->clear();
			$query->update('#__eshop_productcategories')
				->set('category_id = ' . intval($data['main_category_id']))
				->where('main_category = 1')
				->where('product_id in (' . implode(',', $data['cid']) . ')');
			$db->setQuery($query);
			$db->execute();
		}

		//Process additional product categories
		$additionalCategoryId = $data['additional_category_id'];

		if (count($additionalCategoryId))
		{
			if (!in_array(0, $additionalCategoryId))
			{
				//Delete all of additional categories
				$query->clear();
				$query->delete('#__eshop_productcategories')
					->where('main_category = 0')
					->where('product_id in (' . implode(',', $data['cid']) . ')');
				$db->setQuery($query);
				$db->execute();
			}

			if (!in_array(0, $additionalCategoryId) || (in_array(0, $additionalCategoryId) && count($additionalCategoryId) > 1))
			{
				//Assign products to new additional categories
				$query->clear();
				$query->insert('#__eshop_productcategories')
					->columns('product_id, category_id, main_category');

				$subQuery = $db->getQuery(true);

				foreach ($additionalCategoryId as $categoryId)
				{
					foreach ($data['cid'] as $productId)
					{
						//Only assign product to category if it was not assigned before
						$subQuery->clear()
							->select('id')
							->from('#__eshop_productcategories')
							->where('product_id = ' . intval($productId))
							->where('category_id = ' . intval($categoryId));
						$db->setQuery($subQuery);

						if (!$db->loadResult())
						{
							$query->values("$productId, $categoryId, 0");
						}
					}
				}
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 *
	 * Function to save products inventory
	 */
	public function saveInventory($data)
	{
		$db               = $this->getDbo();
		$productId        = $data['product_id'];
		$productPrice     = $data['product_price_fi'];
		$productQuantity  = $data['product_quantity_fi'];
		$productThreshold = $data['product_threshold_fi'];

		$row = new EShopTable('#__eshop_products', 'id', $db);

		for ($i = 0; $n = count($productId), $i < $n; $i++)
		{
			$row->load($productId[$i]);
			$row->product_price        = $productPrice[$i];
			$row->product_quantity     = $productQuantity[$i];
			$row->product_threshold    = $productThreshold[$i];
			$row->product_manage_stock = $data['product_manage_stock_' . $productId[$i]];

			$row->store();
		}

		return true;
	}
}