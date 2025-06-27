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

class EShopModelManufacturers extends EshopRADModelList
{

	public function __construct($config = [])
	{
		$config['translatable']        = true;
		$config['translatable_fields'] = [
			'manufacturer_name',
			'manufacturer_alias',
			'manufacturer_desc',
			'manufacturer_page_title',
			'manufacturer_page_heading',
			'manufacturer_alt_image',
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

		if ($app->input->getCmd('view') == 'manufacturers')
		{
			$app->setUserState('limit', $this->state->limit);
		}
	}

	/**
	 * Method to get manufacturers data
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$rows                    = parent::getData();
			$imageSizeFunction       = EShopHelper::getConfigValue('manufacturer_image_size_function', 'resizeImage');
			$imageManufacturerWidth  = EShopHelper::getConfigValue('image_manufacturer_width');
			$imageManufacturerHeight = EShopHelper::getConfigValue('image_manufacturer_height');
			$baseUri                 = Uri::base(true);

			for ($i = 0; $n = count($rows), $i < $n; $i++)
			{
				$row = $rows[$i];

				if ($row->manufacturer_image && is_file(JPATH_ROOT . '/media/com_eshop/manufacturers/' . $row->manufacturer_image))
				{
					if (EShopHelper::getConfigValue('manufacturer_use_image_watermarks'))
					{
						$watermarkImage    = EShopHelper::generateWatermarkImage(
							JPATH_ROOT . '/media/com_eshop/manufacturers/' . $row->manufacturer_image
						);
						$manufacturerImage = $watermarkImage;
					}
					else
					{
						$manufacturerImage = $row->manufacturer_image;
					}

					$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
						[$manufacturerImage, JPATH_ROOT . '/media/com_eshop/manufacturers/', $imageManufacturerWidth, $imageManufacturerHeight]);
				}
				else
				{
					$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
						[
							EShopHelper::getConfigValue('default_manufacturer_image', 'no-image.png'),
							JPATH_ROOT . '/media/com_eshop/manufacturers/',
							$imageManufacturerWidth,
							$imageManufacturerHeight,
						]);
				}

				if ($imageSizeFunction == 'notResizeImage')
				{
					$row->image = $baseUri . '/media/com_eshop/manufacturers/' . $image;
				}
				else
				{
					$row->image = $baseUri . '/media/com_eshop/manufacturers/resized/' . $image;
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

		//Check viewable of customer groups
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$query->where(
			'((a.manufacturer_customergroups = "") OR (a.manufacturer_customergroups IS NULL) OR (a.manufacturer_customergroups = "' . $customerGroupId . '") OR (a.manufacturer_customergroups LIKE "' . $customerGroupId . ',%") OR (a.manufacturer_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.manufacturer_customergroups LIKE "%,' . $customerGroupId . '"))'
		);

		return $this;
	}

	/**
	 * Override buildQueryOrder method
	 * @see EshopRADModelList::_buildQueryOrder()
	 */
	protected function _buildQueryOrder(JDatabaseQuery $query)
	{
		$manufacturerDefaultSorting = EShopHelper::getConfigValue('manufacturer_default_sorting', 'name-asc');

		switch ($manufacturerDefaultSorting)
		{
			case 'name-desc':
				$query->order('b.manufacturer_name DESC');
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
				$query->order('b.manufacturer_name ASC');
				break;
		}
	}
}