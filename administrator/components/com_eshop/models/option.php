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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelOption extends EShopModel
{

	public function __construct($config)
	{
		$config['translatable']        = true;
		$config['translatable_fields'] = ['option_name', 'option_desc'];
		parent::__construct($config);
	}

	public function store(&$data)
	{
		$input     = Factory::getApplication()->input;
		$imagePath = JPATH_ROOT . '/media/com_eshop/options/';
		if ($input->getInt('remove_image') && $data['id'])
		{
			//Remove image first
			$row = new EShopTable('#__eshop_options', 'id', $this->getDbo());
			$row->load($data['id']);

			if (is_file($imagePath . $row->option_image))
			{
				File::delete($imagePath . $row->option_image);
			}

			if (is_file($imagePath . 'resized/' . File::stripExt($row->option_image) . '-100x100.' . EShopHelper::getFileExt($row->option_image)))
			{
				File::delete($imagePath . 'resized/' . File::stripExt($row->option_image) . '-100x100.' . EShopHelper::getFileExt($row->option_image));
			}
			$data['option_image'] = '';
		}

		$optionImage = $_FILES['option_image'];
		if ($optionImage['name'])
		{
			$checkFileUpload = EShopFile::checkFileUpload($optionImage);
			if (is_array($checkFileUpload))
			{
				$mainframe = Factory::getApplication();
				$mainframe->enqueueMessage(sprintf(Text::_('ESHOP_UPLOAD_IMAGE_ERROR'), implode(' / ', $checkFileUpload)), 'error');
				$mainframe->redirect('index.php?option=com_eshop&task=option.edit&cid[]=' . $data['id']);
			}
			else
			{
				if (is_uploaded_file($optionImage['tmp_name']) && file_exists($optionImage['tmp_name']))
				{
					if (is_file($imagePath . $optionImage['name']))
					{
						$imageFileName = uniqid('image_') . '_' . File::makeSafe($optionImage['name']);
					}
					else
					{
						$imageFileName = File::makeSafe($optionImage['name']);
					}
					File::upload($optionImage['tmp_name'], $imagePath . $imageFileName, false, true);
					// Resize images

					$data['option_image'] = $imageFileName;
					EShopHelper::resizeImage($imageFileName, JPATH_ROOT . '/media/com_eshop/options/', 100, 100);
				}
			}
		}
		parent::store($data);
		// Store option values
		$db            = $this->getDbo();
		$query         = $db->getQuery(true);
		$optionId      = $data['id'];
		$optionValueId = $input->getInt('optionvalue_id', []);
		$published     = $input->getInt('optionvalue_published', []);
		$ordering      = $input->getInt('ordering', []);

		// Delete in option values table first
		$query->clear();
		$query->delete('#__eshop_optionvalues')
			->where('option_id = ' . intval($optionId));
		if (count($optionValueId))
		{
			$query->where('id NOT IN (' . implode(',', $optionValueId) . ')');
		}
		$db->setQuery($query);
		$db->execute();
		// Delete in option values details
		$query->clear();
		$query->delete('#__eshop_optionvaluedetails')
			->where('option_id = ' . intval($optionId));
		if (count($optionValueId))
		{
			$query->where('optionvalue_id NOT IN (' . implode(',', $optionValueId) . ')');
		}
		$db->setQuery($query);
		$db->execute();

		$languages = EShopHelper::getLanguages();
		if (Multilanguage::isEnabled() && count($languages) > 1)
		{
			for ($i = 0; $n = count($ordering), $i < $n; $i++)
			{
				$row            = new EShopTable('#__eshop_optionvalues', 'id', $db);
				$row->id        = $optionValueId[$i] ?? 0;
				$row->option_id = $optionId;
				$row->published = $published[$i];
				$row->ordering  = $ordering[$i];
				$row->store();
				foreach ($languages as $language)
				{
					$langCode                   = $language->lang_code;
					$optionValueDetailsId       = $input->getInt('optionvaluedetails_id_' . $langCode);
					$value                      = $input->getString('value_' . $langCode);
					$detailsRow                 = new EShopTable('#__eshop_optionvaluedetails', 'id', $db);
					$detailsRow->id             = $optionValueDetailsId[$i] ?? 0;
					$detailsRow->optionvalue_id = $row->id;
					$detailsRow->option_id      = $optionId;
					$detailsRow->value          = $value[$i];
					$detailsRow->language       = $langCode;
					$detailsRow->store();
				}
			}
		}
		else
		{
			$optionValueDetailsId = $input->getInt('optionvaluedetails_id');
			$value                = $input->getString('value');
			for ($i = 0; $n = count($ordering), $i < $n; $i++)
			{
				$row            = new EShopTable('#__eshop_optionvalues', 'id', $db);
				$row->id        = $optionValueId[$i] ?? 0;
				$row->option_id = $optionId;
				$row->published = $published[$i];
				$row->ordering  = $ordering[$i];
				$row->store();
				$detailsRow                 = new EShopTable('#__eshop_optionvaluedetails', 'id', $db);
				$detailsRow->id             = $optionValueDetailsId[$i] ?? 0;
				$detailsRow->optionvalue_id = $row->id;
				$detailsRow->option_id      = $optionId;
				$detailsRow->value          = $value[$i];
				$detailsRow->language       = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				$detailsRow->store();
			}
		}

		return true;
	}

	/**
	 * Method to remove options
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
			$query->delete('#__eshop_options')
				->where('id IN (' . $cids . ')')
				->where('id NOT IN (SELECT  DISTINCT(option_id) FROM #__eshop_optionvalues)');
			$db->setQuery($query);
			if (!$db->execute())
				//Removed error
			{
				return 0;
			}
			$numItemsDeleted = $db->getAffectedRows();
			//Delete option values records
			$query->clear();
			$query->delete('#__eshop_optiondetails')
				->where('option_id IN (' . $cids . ')')
				->where('option_id NOT IN (SELECT  DISTINCT(option_id) FROM #__eshop_optionvalues)');
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

	/**
	 * Function to copy option and option values
	 * @see EShopModel::copy()
	 */
	public function copy($id)
	{
		$copiedOptionId = parent::copy($id);
		$db             = $this->getDbo();
		$query          = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_optionvalues')
			->where('option_id = ' . intval($id));
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		for ($i = 0; $n = count($rows), $i < $n; $i++)
		{
			$row                        = $rows[$i];
			$optionValuesRow            = new EShopTable('#__eshop_optionvalues', 'id', $db);
			$optionValuesRow->id        = 0;
			$optionValuesRow->option_id = $copiedOptionId;
			$optionValuesRow->published = $row->published;
			$optionValuesRow->ordering  = $row->ordering;
			$optionValuesRow->store();
			$optionValuesId = $optionValuesRow->id;
			$sql            = 'INSERT INTO #__eshop_optionvaluedetails'
				. ' (optionvalue_id, option_id, value, language)'
				. ' SELECT ' . $optionValuesId . ',' . $copiedOptionId . ', value, language'
				. ' FROM #__eshop_optionvaluedetails'
				. ' WHERE optionvalue_id = ' . intval($row->id);
			$db->setQuery($sql);
			$db->execute();
		}

		return $copiedOptionId;
	}
}