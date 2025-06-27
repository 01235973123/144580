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
class EShopModelLabel extends EShopModel
{

	public function __construct($config)
	{
		$config['translatable']        = true;
		$config['translatable_fields'] = ['label_name'];

		parent::__construct($config);
	}

	public function store(&$data)
	{
		$input     = Factory::getApplication()->input;
		$imagePath = JPATH_ROOT . '/media/com_eshop/labels/';

		$imageWidth = $data['label_image_width'] > 0 ? $data['label_image_width'] : EShopHelper::getConfigValue('label_image_width');

		if (!$imageWidth)
		{
			$imageWidth = 50;
		}

		$imageHeight = $data['label_image_height'] > 0 ? $data['label_image_height'] : EShopHelper::getConfigValue('label_image_height');

		if (!$imageHeight)
		{
			$imageHeight = 50;
		}

		if ($input->getInt('remove_image') && $data['id'])
		{
			//Remove image first
			$row = new EShopTable('#__eshop_labels', 'id', $this->getDbo());
			$row->load($data['id']);

			if (is_file($imagePath . $row->label_image))
			{
				File::delete($imagePath . $row->label_image);
			}

			if (is_file(
				$imagePath . 'resized/' . File::stripExt($row->label_image) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt(
					$row->label_image
				)
			))
			{
				File::delete(
					$imagePath . 'resized/' . File::stripExt($row->label_image) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt(
						$row->label_image
					)
				);
			}

			$data['label_image'] = '';
		}

		$labelImage = $_FILES['label_image'];

		if ($labelImage['name'])
		{
			$checkFileUpload = EShopFile::checkFileUpload($labelImage);

			if (is_array($checkFileUpload))
			{
				$mainframe = Factory::getApplication();
				$mainframe->enqueueMessage(sprintf(Text::_('ESHOP_UPLOAD_IMAGE_ERROR'), implode(' / ', $checkFileUpload)), 'error');
				$mainframe->redirect('index.php?option=com_eshop&task=label.edit&cid[]=' . $data['id']);
			}
			else
			{
				if (is_uploaded_file($labelImage['tmp_name']) && file_exists($labelImage['tmp_name']))
				{
					if ($data['id'])
					{
						// Delete the old image
						$row = new EShopTable('#__eshop_labels', 'id', $this->getDbo());
						$row->load($data['id']);

						if (is_file($imagePath . $row->label_image))
						{
							File::delete($imagePath . $row->label_image);
						}

						if (is_file(
							$imagePath . 'resized/' . File::stripExt($row->label_image) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt(
								$row->label_image
							)
						))
						{
							File::delete(
								$imagePath . 'resized/' . File::stripExt(
									$row->label_image
								) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt($row->label_image)
							);
						}
					}

					if (is_file($imagePath . $labelImage['name']))
					{
						$imageFileName = uniqid('image_') . '_' . $labelImage['name'];
					}
					else
					{
						$imageFileName = $labelImage['name'];
					}

					File::upload($labelImage['tmp_name'], $imagePath . $imageFileName, false, true);
					$data['label_image'] = $imageFileName;
				}
			}
		}

		//Delete label elements first
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		if ($data['id'])
		{
			$query->delete('#__eshop_labelelements')
				->where('label_id = ' . intval($data['id']));
			$db->setQuery($query);
			$db->execute();
		}

		if ($data['label_start_date'] == '')
		{
			$data['label_start_date'] = '0000-00-00 00:00:00';
		}

		if ($data['label_end_date'] == '')
		{
			$data['label_end_date'] = '0000-00-00 00:00:00';
		}

		parent::store($data);

		$row = new EShopTable('#__eshop_labels', 'id', $this->getDbo());
		$row->load($data['id']);

		if ($row->label_image && !is_file(
				$imagePath . 'resized/' . File::stripExt($row->label_image) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt(
					$row->label_image
				)
			))
		{
			$imageSizeFunction = EShopHelper::getConfigValue('image_size_function', 'resizeImage');
			call_user_func_array(['EShopHelper', $imageSizeFunction],
				[$row->label_image, JPATH_ROOT . '/media/com_eshop/labels/', $imageWidth, $imageHeight]);
		}

		//Label for products
		if (isset($data['product_id']))
		{
			$productIds = $data['product_id'];

			if (count($productIds))
			{
				$query->clear();
				$query->insert('#__eshop_labelelements')
					->columns('label_id, element_id, element_type');
				$labelId = $data['id'];

				for ($i = 0; $i < count($productIds); $i++)
				{
					$productId = $productIds[$i];
					$query->values("$labelId, $productId, 'product'");
				}

				$db->setQuery($query);
				$db->execute();
			}
		}

		//Label for manufacturers
		if (isset($data['manufacturer_id']))
		{
			$manufacturerIds = $data['manufacturer_id'];

			if (count($manufacturerIds))
			{
				$query->clear();
				$query->insert('#__eshop_labelelements')
					->columns('label_id, element_id, element_type');
				$labelId = $data['id'];

				for ($i = 0; $i < count($manufacturerIds); $i++)
				{
					$manufacturerId = $manufacturerIds[$i];
					$query->values("$labelId, $manufacturerId, 'manufacturer'");
				}

				$db->setQuery($query);
				$db->execute();
			}
		}

		//Label for manufacturers
		if (isset($data['category_id']))
		{
			$categoryIds = $data['category_id'];

			if (count($categoryIds))
			{
				$query->clear();
				$query->insert('#__eshop_labelelements')
					->columns('label_id, element_id, element_type');
				$labelId = $data['id'];

				for ($i = 0; $i < count($categoryIds); $i++)
				{
					$categoryId = $categoryIds[$i];
					$query->values("$labelId, $categoryId, 'category'");
				}

				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Method to remove labels
	 *
	 * @access    public
	 * @return boolean True on success
	 * @since     1.5
	 */
	public function delete($cid = [])
	{
		//Remove label elements
		if (count($cid))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->delete('#__eshop_labelelements')
				->where('label_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);
			$db->execute();
		}

		parent::delete($cid);
	}
}