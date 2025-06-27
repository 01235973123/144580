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
class EShopModelDownload extends EShopModel
{

	public function __construct($config)
	{
		$config['translatable']        = true;
		$config['translatable_fields'] = ['download_name'];

		parent::__construct($config);
	}

	public function store(&$data)
	{
		$file    = $_FILES['file'];
		$message = '';
		if (!empty($file['name']))
		{
			$fileName = File::makeSafe($file['name']);
			//Allowed file extension types
			$allowed   = [];
			$fileTypes = explode("\n", EShopHelper::getConfigValue('file_extensions_allowed'));
			foreach ($fileTypes as $fileType)
			{
				$allowed[] = trim($fileType);
			}
			if (!in_array(substr(strrchr($fileName, '.'), 1), $allowed))
			{
				$message = Text::_('ESHOP_UPLOAD_ERROR_FILETYPE');
			}
			// Allowed file mime types
			$allowed   = [];
			$fileTypes = explode("\n", EShopHelper::getConfigValue('file_mime_types_allowed'));
			foreach ($fileTypes as $fileType)
			{
				$allowed[] = trim($fileType);
			}
			if (!in_array($file['type'], $allowed))
			{
				$message = Text::_('ESHOP_UPLOAD_ERROR_FILE_MIME_TYPE');
			}
			if ($file['error'] != UPLOAD_ERR_OK)
			{
				$message = Text::_('ESHOP_ERROR_UPLOAD_' . $file['error']);
			}
			if (is_file(JPATH_ROOT . '/media/com_eshop/downloads/' . $fileName) && !isset($_POST['overwrite']))
			{
				$message = Text::_('ESHOP_FILE_EXISTED');
			}
			if ($message == '')
			{
				File::upload($file['tmp_name'], JPATH_ROOT . '/media/com_eshop/downloads/' . $fileName, false, true);
				$data['filename'] = $fileName;
			}
			else
			{
				$mainframe = Factory::getApplication();
				$mainframe->enqueueMessage($message, 'error');
				$mainframe->redirect('index.php?option=com_eshop&view=download&cid[]=' . $data['id']);
			}
		}
		else
		{
			if ($data['existed_file'] != '')
			{
				$data['filename'] = $data['existed_file'];
			}
		}
		parent::store($data);

		return true;
	}

	/**
	 * Method to remove downloads
	 *
	 * @access    public
	 * @return boolean True on success
	 * @since     1.5
	 */
	public function delete($cid = [])
	{
		//Remove download elements
		if (count($cid))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->delete('#__eshop_productdownloads')
				->where('download_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);
			$db->execute();
		}
		parent::delete($cid);
	}
}