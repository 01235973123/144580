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

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelManufacturer extends EShopModel
{

	public function __construct($config)
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
	}

	public function store(&$data)
	{
		$input     = Factory::getApplication()->input;
		$imagePath = JPATH_ROOT . '/media/com_eshop/manufacturers/';
		if ($input->getInt('remove_image') && $data['id'])
		{
			//Remove image first
			$row = new EShopTable('#__eshop_manufacturers', 'id', $this->getDbo());
			$row->load($data['id']);

			if (is_file($imagePath . $row->manufacturer_image))
			{
				File::delete($imagePath . $row->manufacturer_image);
			}

			if (is_file(
				$imagePath . 'resized/' . File::stripExt($row->manufacturer_image) . '-100x100.' . EShopHelper::getFileExt($row->manufacturer_image)
			))
			{
				File::delete(
					$imagePath . 'resized/' . File::stripExt($row->manufacturer_image) . '-100x100.' . EShopHelper::getFileExt($row->manufacturer_image)
				);
			}
			$data['manufacturer_image'] = '';
		}

		$manufacturerImage = $_FILES['manufacturer_image'];
		if ($manufacturerImage['name'])
		{
			$checkFileUpload = EShopFile::checkFileUpload($manufacturerImage);
			if (is_array($checkFileUpload))
			{
				$mainframe = Factory::getApplication();
				$mainframe->enqueueMessage(sprintf(Text::_('ESHOP_UPLOAD_IMAGE_ERROR'), implode(' / ', $checkFileUpload)), 'error');
				$mainframe->redirect('index.php?option=com_eshop&task=manufacturer.edit&cid[]=' . $data['id']);
			}
			else
			{
				if (is_uploaded_file($manufacturerImage['tmp_name']) && file_exists($manufacturerImage['tmp_name']))
				{
					if (is_file($imagePath . $manufacturerImage['name']))
					{
						$imageFileName = uniqid('image_') . '_' . File::makeSafe($manufacturerImage['name']);
					}
					else
					{
						$imageFileName = File::makeSafe($manufacturerImage['name']);
					}
					File::upload($manufacturerImage['tmp_name'], $imagePath . $imageFileName, false, true);
					// Resize images

					$data['manufacturer_image'] = $imageFileName;
					EShopHelper::resizeImage($imageFileName, JPATH_ROOT . '/media/com_eshop/manufacturers/', 100, 100);
				}
			}
		}
		if (isset($data['manufacturer_customergroups']))
		{
			$data['manufacturer_customergroups'] = implode(',', $data['manufacturer_customergroups']);
		}
		else
		{
			$data['manufacturer_customergroups'] = '';
		}
		parent::store($data);

		return true;
	}

	/**
	 * Method to remove manufacturers
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
				->from('#__eshop_manufacturers')
				->where('id IN (' . $cids . ')');
			$db->setQuery($query);
			$manufacturers = $db->loadColumn();
			if (count($manufacturers))
			{
				//Remove images
				for ($i = 0; $n = count($manufacturers), $i < $n; $i++)
				{
					EShopHelper::removeImages($manufacturers[$i], 'manufacturer');
				}

				$query->clear();
				$query->delete('#__eshop_manufacturers')
					->where('id IN (' . implode(',', $manufacturers) . ')');
				$db->setQuery($query);
				if (!$db->execute())
					//Removed error
				{
					return 0;
				}
				$numItemsDeleted = $db->getAffectedRows();
				//Delete details records
				$query->clear();
				$query->delete('#__eshop_manufacturerdetails')
					->where('manufacturer_id IN (' . implode(',', $manufacturers) . ')');
				$db->setQuery($query);
				if (!$db->execute())
					//Removed error
				{
					return 0;
				}
				//Remove SEF urls for categories
				for ($i = 0; $n = count($manufacturers), $i < $n; $i++)
				{
					$query->clear();
					$query->delete('#__eshop_urls')
						->where('query LIKE "view=manufacturer&id=' . $manufacturers[$i] . '"');
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