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

use Joomla\Archive\Archive;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\Language\Text;

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelTheme extends EShopModel
{

	/**
	 * Save theme parameter
	 * @see EShopModel::store()
	 */
	public function store(&$data)
	{
		$input = Factory::getApplication()->input;

		$db  = $this->getDbo();
		$row = new EShopTable('#__eshop_themes', 'id', $db);

		if ($data['id'])
		{
			$row->load($data['id']);
		}

		if (!$row->bind($data))
		{
			return false;
		}

		$params = $input->get('params', [], 'array');

		if (is_array($params))
		{
			for ($i = 0, $n = count($params); $i < $n; $i++)
			{
				if (!is_string($params[$i]))
				{
					continue;
				}

				$params[$i] = trim($params[$i]);
			}

			$params = json_encode($params);
		}
		else
		{
			$params = null;
		}

		$row->params = $params;

		if (!$row->store())
		{
			throw new Exception($row->getError());
		}

		$data['id'] = $row->id;

		return true;
	}

	/**
	 * Install a theme from given package
	 *
	 * @param $theme
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function install($theme)
	{
		$app = Factory::getApplication();
		$db  = $this->getDbo();

		if ($theme['error'] || $theme['size'] < 1)
		{
			throw new Exception(Text::_('ESHOP_UPLOAD_THEME_ERROR'));
		}

		$tmpPath = $app->get('tmp_path');

		if (!is_dir($tmpPath))
		{
			$tmpPath = JPATH_ROOT . '/tmp';
		}

		$destinationDir = $tmpPath . '/' . $theme['name'];

		$uploaded = File::upload($theme['tmp_name'], $destinationDir, false, true);

		if (!$uploaded)
		{
			throw new Exception(Text::_('ESHOP_UPLOAD_THEME_FAILED'));
		}

		// Temporary folder to extract the archive into
		$tmpDir     = uniqid('install_');
		$extractDir = Path::clean(dirname($destinationDir) . '/' . $tmpDir);

		if (EShopHelper::isJoomla4())
		{
			$archive = new Archive(['tmp_path' => $app->get('tmp_path')]);
			$result  = $archive->extract($destinationDir, $extractDir);
		}
		else
		{
			$result = JArchive::extract($destinationDir, $extractDir);
		}

		if (!$result)
		{
			throw new Exception(Text::_('ESHOP_EXTRACT_THEME_ERROR'));
		}

		$dirList = array_merge(Folder::files($extractDir, ''), Folder::folders($extractDir, ''));

		if (isset($dirList) && count($dirList) == 1 && is_dir($extractDir . '/' . $dirList[0]))
		{
			$extractDir = Path::clean($extractDir . '/' . $dirList[0]);
		}

		//Now, search for xml file
		$xmlFiles = Folder::files($extractDir, '.xml$', 1, true);

		if (empty($xmlFiles))
		{
			throw new Exception(Text::_('ESHOP_COULD_NOT_FIND_XML_FILE'));
		}

		$file = $xmlFiles[0];
		$root = simplexml_load_file($file);

		if ($root->getName() !== 'install')
		{
			throw new Exception(Text::_('ESHOP_INVALID_XML_FILE'));
		}

		$themeType = $root->attributes()->type;

		if ($themeType != 'eshoptheme')
		{
			throw new Exception(Text::_('ESHOP_INVALID_ESHOP_THEME'));
		}

		$row          = new EShopTable('#__eshop_themes', 'id', $db);
		$name         = (string) $root->name;
		$title        = (string) $root->title;
		$author       = (string) $root->author;
		$creationDate = (string) $root->creationDate;
		$copyright    = (string) $root->copyright;
		$license      = (string) $root->license;
		$authorEmail  = (string) $root->authorEmail;
		$authorUrl    = (string) $root->authorUrl;
		$version      = (string) $root->version;
		$description  = (string) $root->description;

		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__eshop_themes')
			->where('name = "' . $name . '"');
		$db->setQuery($query);
		$themeId = (int) $db->loadResult();

		if ($themeId)
		{
			$row->load($themeId);
			$row->name          = $name;
			$row->author        = $author;
			$row->creation_date = $creationDate;
			$row->copyright     = $copyright;
			$row->license       = $license;
			$row->author_email  = $authorEmail;
			$row->author_url    = $authorUrl;
			$row->version       = $version;
			$row->description   = $description;
		}
		else
		{
			$row->id            = '';
			$row->name          = $name;
			$row->title         = $title;
			$row->author        = $author;
			$row->creation_date = $creationDate;
			$row->copyright     = $copyright;
			$row->license       = $license;
			$row->author_email  = $authorEmail;
			$row->author_url    = $authorUrl;
			$row->version       = $version;
			$row->description   = $description;
			$row->published     = 0;
			$row->ordering      = $row->getNextOrder('published=1');
		}

		if (!$row->store())
		{
			throw new Exception($row->getError());
		}

		Folder::create(JPATH_ROOT . '/components/com_eshop/themes/' . $name);
		$themeDir = JPATH_ROOT . '/components/com_eshop/themes/' . $name;
		File::move($file, $themeDir . '/' . basename($file));
		$files = $root->files->children();

		if (isset($files))
		{
			for ($i = 0, $n = count($files); $i < $n; $i++)
			{
				$file = $files[$i];

				if ($file->getName() == 'filename')
				{
					$fileName = $file;
					File::copy($extractDir . '/' . $fileName, $themeDir . '/' . $fileName);
				}
				elseif ($file->getName() == 'folder')
				{
					$folderName = $file;

					if (is_dir($extractDir . '/' . $folderName))
					{
						if (is_dir($themeDir . '/' . $folderName))
						{
							Folder::delete($themeDir . '/' . $folderName);
						}

						Folder::move($extractDir . '/' . $folderName, $themeDir . '/' . $folderName);
					}
				}
			}
		}

		Folder::delete($extractDir);

		return true;
	}

	/**
	 * Remove the selected theme
	 * @see EShopModel::delete()
	 */
	public function delete($cid = [])
	{
		$db       = $this->getDbo();
		$row      = new EShopTable('#__eshop_themes', 'id', $db);
		$themeDir = JPATH_ROOT . '/components/com_eshop/themes';

		foreach ($cid as $id)
		{
			$row->load($id);
			$name = $row->name;
			$file = $themeDir . '/' . $name . '/' . $name . '.xml';

			if (!is_file($file))
			{
				//Simply delete the record
				$row->delete();

				return 1;
			}
			else
			{
				$root  = simplexml_load_file($file);
				$files = $root->files->children();

				if (isset($files))
				{
					for ($i = 0, $n = count($files); $i < $n; $i++)
					{
						$file = $files[$i];

						if ($file->getName() == 'filename')
						{
							$fileName = $file;

							if (is_file($themeDir . '/' . $name . '/' . $fileName))
							{
								File::delete($themeDir . '/' . $name . '/' . $fileName);
							}
						}
						elseif ($file->getName() == 'folder')
						{
							$folderName = $file;

							if ($folderName && is_dir($themeDir . '/' . $name . '/' . $folderName))
							{
								Folder::delete($themeDir . '/' . $name . '/' . $folderName);
							}
						}
					}
				}

				File::delete($themeDir . '/' . $name . '/' . $name . '.xml');

				if (is_file($themeDir . '/' . $name . '/index.html'))
				{
					File::delete($themeDir . '/' . $name . '/index.html');
				}

				Folder::delete($themeDir . '/' . $name);
				$row->delete();
			}
		}

		return 1;
	}
}
