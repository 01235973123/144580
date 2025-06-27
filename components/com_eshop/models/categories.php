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
use Joomla\Filesystem\File;
use Joomla\CMS\Uri\Uri;

class EShopModelCategories extends EshopRADModelList
{

	public function __construct($config = [])
	{
		$config['translatable']        = true;
		$config['translatable_fields'] = [
			'category_name',
			'category_alias',
			'category_page_title',
			'category_page_heading',
			'category_alt_image',
			'category_desc',
			'meta_key',
			'meta_desc',
		];

		parent::__construct($config);

		$app        = Factory::getApplication();
		$listLength = EShopHelper::getConfigValue('catalog_limit');

		if (!$listLength)
		{
			$listLength = $app->get('list_limit');
		}

		$this->state->insert('id', 'int', 0)
			->insert('limit', 'int', $listLength);

		$request = EShopHelper::getRequestData();
		$this->state->setData($request);

		if ($app->input->getCmd('view') == 'categories')
		{
			$app->setUserState('limit', $this->state->limit);
		}
	}

	/**
	 * Method to get categories data
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$rows                = parent::getData();
			$imageSizeFunction   = EShopHelper::getConfigValue('category_image_size_function', 'resizeImage');
			$imageCategoryWidth  = EShopHelper::getConfigValue('image_category_width');
			$imageCategoryHeight = EShopHelper::getConfigValue('image_category_height');
			$baseUri             = Uri::base(true);

			for ($i = 0; $n = count($rows), $i < $n; $i++)
			{
				$row = $rows[$i];

				if ($row->category_image && is_file(JPATH_ROOT . '/media/com_eshop/categories/' . $row->category_image))
				{
					if (EShopHelper::getConfigValue('category_use_image_watermarks'))
					{
						$watermarkImage = EShopHelper::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/categories/' . $row->category_image);
						$categoryImage  = $watermarkImage;
					}
					else
					{
						$categoryImage = $row->category_image;
					}

					$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
						[$categoryImage, JPATH_ROOT . '/media/com_eshop/categories/', $imageCategoryWidth, $imageCategoryHeight]);
				}
				else
				{
					$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
						[
							EShopHelper::getConfigValue('default_category_image', 'no-image.png'),
							JPATH_ROOT . '/media/com_eshop/categories/',
							$imageCategoryWidth,
							$imageCategoryHeight,
						]);
				}

				if ($imageSizeFunction == 'notResizeImage')
				{
					$row->image = $baseUri . '/media/com_eshop/categories/' . $image;
				}
				else
				{
					$row->image = $baseUri . '/media/com_eshop/categories/resized/' . $image;
				}
			}

			$this->data = $rows;
		}

		return $this->data;
	}

	/**
	 * Override BuildQueryWhere method
	 * @see EshopRADModelList::_buildQueryWhere()
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		parent::_buildQueryWhere($query);

		$params      = Factory::getApplication()->getParams();
		$categoryIds = $params->get('category_ids');

		if (!empty($categoryIds))
		{
			$query->where('a.id IN (' . implode(',', $categoryIds) . ')');
		}
		else
		{
			$query->where('a.category_parent_id=' . $this->state->id);
		}

		//Check viewable of customer groups
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$query->where(
			'((a.category_customergroups = "") OR (a.category_customergroups IS NULL) OR (a.category_customergroups = "' . $customerGroupId . '") OR (a.category_customergroups LIKE "' . $customerGroupId . ',%") OR (a.category_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.category_customergroups LIKE "%,' . $customerGroupId . '"))'
		);

		return $this;
	}

	/**
	 * Override buildQueryOrder method
	 * @see EshopRADModelList::_buildQueryOrder()
	 */
	protected function _buildQueryOrder(JDatabaseQuery $query)
	{
		$categoryDefaultSorting = EShopHelper::getConfigValue('category_default_sorting', 'name-asc');

		switch ($categoryDefaultSorting)
		{
			case 'name-desc':
				$query->order('b.category_name DESC');
				break;
			case 'ordering-asc':
				$query->order('a.ordering ASC');
				break;
			case 'ordering-desc':
				$query->order('a.ordering DESC');
				break;
			case 'id-asc':
				$query->order('a.id ASC');
				break;
			case 'id-desc':
				$query->order('a.id DESC');
				break;
			case 'random':
				$query->order('RAND()');
				break;
			case 'name-asc':
			default:
				$query->order('b.category_name ASC');
				break;
		}
	}
}