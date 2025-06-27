<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Uri\Uri;

abstract class modEShopCategoryHelper
{
	/**
	 *
	 * Function to get Categories
	 * @return categories list
	 */
	public static function getCategories($params)
	{
		$categoryIds = $params->get('category_ids', array());
		$categories = EShopHelper::getCategories(0, Factory::getLanguage()->getTag(), true, $categoryIds);

		$imageSizeFunction   = $params->get('image_resize_function', 'resizeImage');
		$imageCategoryWidth  = $params->get('image_width', 100);
		$imageCategoryHeight = $params->get('image_height', 100);
		$baseUri             = Uri::base(true);

		for ($i = 0; $n = count($categories), $i < $n; $i++)
		{
			$row = $categories[$i];

			if ($row->category_image && is_file((JPATH_ROOT . '/media/com_eshop/categories/' . $row->category_image)))
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

			$categories[$i]->childCategories = EShopHelper::getCategories($categories[$i]->id, Factory::getLanguage()->getTag(), true);
		}

		return $categories;
	}

	/**
	 *
	 * Function to get id of parent category
	 *
	 * @param   int  $categoryId
	 *
	 * @return int id of parent category
	 */
	public static function getParentCategoryId($categoryId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query = $query->select('category_parent_id')
			->from('#__eshop_categories')
			->where('id = ' . $categoryId);
		$db->setQuery($query);

		return $db->loadResult() ?: $categoryId;
	}
}
