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
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * EShop Component Category Model
 *
 * @package    Joomla
 * @subpackage EShop
 * @since      1.5
 */
class EShopModelCategory extends EShopModel
{

	public function __construct($config)
	{
		$config['translatable']        = true;
		$config['translatable_fields'] = [
			'category_name',
			'category_alias',
			'category_desc',
			'category_page_title',
			'category_page_heading',
			'category_alt_image',
			'category_canoncial_link',
			'meta_key',
			'meta_desc',
		];

		parent::__construct($config);
	}

	public function store(&$data)
	{
		$input     = Factory::getApplication()->input;
		$imagePath = JPATH_ROOT . '/media/com_eshop/categories/';
		if ($input->getInt('remove_image') && $data['id'])
		{
			//Remove image first
			$row = new EShopTable('#__eshop_categories', 'id', $this->getDbo());
			$row->load($data['id']);
			if (is_file($imagePath . $row->category_image))
			{
				File::delete($imagePath . $row->category_image);
			}

			if (is_file($imagePath . 'resized/' . File::stripExt($row->category_image) . '-100x100.' . EShopHelper::getFileExt($row->category_image)))
			{
				File::delete($imagePath . 'resized/' . File::stripExt($row->category_image) . '-100x100.' . EShopHelper::getFileExt($row->category_image));
			}
			$data['category_image'] = '';
		}

		$categoryImage = $_FILES['category_image'];
		if ($categoryImage['name'])
		{
			$checkFileUpload = EShopFile::checkFileUpload($categoryImage);
			if (is_array($checkFileUpload))
			{
				$mainframe = Factory::getApplication();
				$mainframe->enqueueMessage(sprintf(Text::_('ESHOP_UPLOAD_IMAGE_ERROR'), implode(' / ', $checkFileUpload)), 'error');
				$mainframe->redirect('index.php?option=com_eshop&task=category.edit&cid[]=' . $data['id']);
			}
			else
			{
				if (is_uploaded_file($categoryImage['tmp_name']) && file_exists($categoryImage['tmp_name']))
				{
					if (is_file($imagePath . $categoryImage['name']))
					{
						$imageFileName = uniqid('image_') . '_' . File::makeSafe($categoryImage['name']);
					}
					else
					{
						$imageFileName = File::makeSafe($categoryImage['name']);
					}
					File::upload($categoryImage['tmp_name'], $imagePath . $imageFileName, false, true);
					// Resize image
					EShopHelper::resizeImage($imageFileName, JPATH_ROOT . '/media/com_eshop/categories/', 100, 100);
					$data['category_image'] = $imageFileName;
				}
			}
		}

		if (isset($data['category_customergroups']))
		{
			$data['category_customergroups'] = implode(',', $data['category_customergroups']);
		}
		else
		{
			$data['category_customergroups'] = '';
		}

		if (isset($data['category_cart_mode_customergroups']))
		{
			$data['category_cart_mode_customergroups'] = implode(',', $data['category_cart_mode_customergroups']);
		}
		else
		{
			$data['category_cart_mode_customergroups'] = '';
		}

		// Calculate category level
		if ($data['category_parent_id'] > 0)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			// Calculate level
			$query->clear();
			$query->select('`level`')
				->from('#__eshop_categories')
				->where('id = ' . (int) $data['category_parent_id']);
			$db->setQuery($query);
			$data['level'] = (int) $db->loadResult() + 1;
		}
		else
		{
			$data['level'] = 1;
		}

		parent::store($data);

		$row = new EShopTable('#__eshop_categories', 'id', $this->getDbo());
		$row->load($data['id']);

		$params = new Registry($row->params);
		$params->set('default_sorting', $data['default_sorting']);
		$params->set('quantity_box', $data['quantity_box']);
		$row->params = $params->toString();

		$row->store();

		return true;
	}

	/**
	 * Method to remove categories
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
			$query->select('id')
				->from('#__eshop_categories')
				->where('id IN (' . $cids . ')')
				->where('id NOT IN (SELECT  DISTINCT(category_id) FROM #__eshop_productcategories)')
				->where('id NOT IN (SELECT DISTINCT(category_parent_id) FROM #__eshop_categories WHERE category_parent_id > 0)');
			$db->setQuery($query);
			$categories = $db->loadColumn();
			if (count($categories))
			{
				//Remove images
				for ($i = 0; $n = count($categories), $i < $n; $i++)
				{
					EShopHelper::removeImages($categories[$i], 'category');
				}

				$query->clear();
				$query->delete('#__eshop_categories')
					->where('id IN (' . implode(',', $categories) . ')');
				$db->setQuery($query);
				if (!$db->execute())
					//Removed error
				{
					return 0;
				}
				$numItemsDeleted = $db->getAffectedRows();
				//Delete details records
				$query->clear();
				$query->delete('#__eshop_categorydetails')
					->where('category_id IN (' . implode(',', $categories) . ')');
				$db->setQuery($query);
				if (!$db->execute())
					//Removed error
				{
					return 0;
				}
				//Remove SEF urls for categories
				for ($i = 0; $n = count($categories), $i < $n; $i++)
				{
					$query->clear();
					$query->delete('#__eshop_urls')
						->where('query LIKE "view=category&id=' . $categories[$i] . '"');
					$db->setQuery($query);
					$db->execute();
				}
				if ($numItemsDeleted < count($cid))
				{
					//Removed warning
					return 2;
				}
			}
			else
			{
				return 2;
			}
		}

		//Removed success
		return 1;
	}
}