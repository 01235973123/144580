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

use Joomla\CMS\Language\Text;

class EShopFile
{

	/**
	 *
	 * Function to check file before uploading
	 *
	 * @param   array  $file
	 *
	 * @return mixed
	 */
	public static function checkFileUpload($file)
	{
		$error = [];

		if (is_array($file['name']))
		{
			for ($i = 0; $n = count($file['name']), $i < $n; $i++)
			{
				if ($file['name'][$i] != '')
				{
					$fileName = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($file['name'][$i], ENT_QUOTES, 'UTF-8')));

					//Allowed file extension types
					$allowed   = [];
					$fileTypes = explode("\n", EShopHelper::getConfigValue('file_extensions_allowed'));

					foreach ($fileTypes as $fileType)
					{
						$allowed[] = strtolower(trim($fileType));
					}

					if (!in_array(strtolower(substr(strrchr($fileName, '.'), 1)), $allowed))
					{
						$error[] = Text::_('ESHOP_UPLOAD_ERROR_FILETYPE');
					}

					// Allowed file mime types
					$allowed   = [];
					$fileTypes = explode("\n", EShopHelper::getConfigValue('file_mime_types_allowed'));

					foreach ($fileTypes as $fileType)
					{
						$allowed[] = strtolower(trim($fileType));
					}

					if (!in_array(strtolower($file['type'][$i]), $allowed))
					{
						$error[] = Text::_('ESHOP_UPLOAD_ERROR_FILE_MIME_TYPE');
					}

					if ($file['error'][$i] != UPLOAD_ERR_OK)
					{
						$error[] = Text::_('ESHOP_ERROR_UPLOAD_' . $file['error'][$i]);
					}

					if (count($error))
					{
						break;
					}
				}
			}
		}
		else
		{
			if ($file['name'] != '')
			{
				$fileName = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8')));

				//Allowed file extension types
				$allowed   = [];
				$fileTypes = explode("\n", EShopHelper::getConfigValue('file_extensions_allowed'));

				foreach ($fileTypes as $fileType)
				{
					$allowed[] = strtolower(trim($fileType));
				}

				if (!in_array(strtolower(substr(strrchr($fileName, '.'), 1)), $allowed))
				{
					$error[] = Text::_('ESHOP_UPLOAD_ERROR_FILETYPE');
				}

				// Allowed file mime types
				$allowed   = [];
				$fileTypes = explode("\n", EShopHelper::getConfigValue('file_mime_types_allowed'));

				foreach ($fileTypes as $fileType)
				{
					$allowed[] = strtolower(trim($fileType));
				}

				if (!in_array(strtolower($file['type']), $allowed))
				{
					$error[] = Text::_('ESHOP_UPLOAD_ERROR_FILE_MIME_TYPE');
				}

				if ($file['error'] != UPLOAD_ERR_OK)
				{
					$error[] = Text::_('ESHOP_ERROR_UPLOAD_' . $file['error']);
				}
			}
		}

		if (count($error) > 0)
		{
			return $error;
		}
		else
		{
			return true;
		}
	}
}