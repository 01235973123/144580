<?php

/**
 * @version        5.4
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Archive\Archive;
use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;

class DonationModelPlugin extends OSFModelAdmin
{

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct($config = array())
	{
		$config['table'] = '#__jd_payment_plugins';
		parent::__construct($config);
	}

	/**
	 * Method to store a plugin
	 *
	 * @access    public
	 * @return    boolean    True on success
	 */
	function store($input, $ignore = array())
	{
		$params = $input->get('params', array(), 'array');
		if (is_array($params))
		{
			$params = json_encode($params);
		}
		else
		{
			$params = null;
		}
		$input->set('params', $params);
		parent::store($input, $ignore);
	}

	/**
	 * Install the payment plugin from uploaded package
	 *
	 * @param OSFInput $input
	 *
	 * @return bool
	 */
	function install($input)
	{
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.archive');
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);

		if (version_compare(JVERSION, '3.4.4', 'ge'))
		{
			$plugin = $input->files->get('plugin_package', null, 'raw');
		}
		else
		{
			$plugin = $input->files->get('plugin_package', null, 'none');
		}

		if ($plugin['error'] || $plugin['size'] < 1)
		{
			throw new Exception(Text::_('Upload plugin package error'));

			return false;
		}
		$config   = new JConfig();
		$dest     = $config->tmp_path . '/' . $plugin['name'];

		if (version_compare(JVERSION, '3.4.4', 'ge'))
		{
			$uploaded = File::upload($plugin['tmp_name'], $dest, false, true);
		}
		else
		{
			$uploaded = File::upload($plugin['tmp_name'], $dest);
		}

		if (!$uploaded)
		{
			throw new Exception(Text::_('Upload plugin package'));

			return false;
		}
		// Temporary folder to extract the archive into
		$tmpdir     = uniqid('install_');
		$extractdir = Path::clean(dirname($dest) . '/' . $tmpdir);
		//$result     = JArchive::extract($dest, $extractdir);
        if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
        {
            $archive = new Archive(array('tmp_path' => Factory::getConfig()->get('tmp_path')));
            $result  = $archive->extract($dest, $extractdir);
        }
        else
        {
            $result = JArchive::extract($dest, $extractdir);
        }
		if (!$result)
		{
			throw new Exception(Text::_('Could not extract the plugin package'));
			return false;
		}
		$dirList = array_merge(Folder::files($extractdir, ''), Folder::folders($extractdir, ''));
		if (count($dirList) == 1)
		{
			if (is_dir(Path::clean($extractdir . '/' . $dirList[0])))
			{
				$extractdir = Path::clean($extractdir . '/' . $dirList[0]);
			}
		}
		//Now, search for xml file
		$xmlfiles = Folder::files($extractdir, '.xml$', 1, true);
		if (empty($xmlfiles))
		{
			throw new Exception(Text::_('Could not find xml file in the package'));
			return false;
		}
		$file = $xmlfiles[0];
		$root = simplexml_load_file($file);
		if ($root->getName() !== 'install')
		{
			throw new Exception(Text::_('Invalid xml file for payment plugin installation function'));
			return false;
		}
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
		$row          = $this->getTable();
		$query->select('id')
			->from('#__jd_payment_plugins')
			->where('name=' . $db->quote($name));
		$db->setQuery($query);
		$pluginId = (int) $db->loadResult();
		if ($pluginId)
		{
			$row->load($pluginId);
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
		}
		else
		{
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
		$row->store();
		$pluginDir = JPATH_ROOT . '/components/com_jdonation/payments';
		File::move($file, $pluginDir . '/' . basename($file));
		$files = $root->files->children();
		for ($i = 0, $n = count($files); $i < $n; $i++)
		{
			$file = $files[$i];
			if ($file->getName() == 'filename')
			{
				$fileName = $file;
				//if (!JFile::exists($pluginDir . '/' . $fileName))
				//{
                File::copy($extractdir . '/' . $fileName, $pluginDir . '/' . $fileName);
				//}
			}
			elseif ($file->getName() == 'folder')
			{
				$folderName = $file;
				if (is_dir(Path::clean($extractdir . '/' . $folderName)))
				{
                    if(is_dir(Path::clean($pluginDir . '/' . $folderName)))
					{
                        Folder::delete($pluginDir . '/' . $folderName);
                    }
					Folder::move($extractdir . '/' . $folderName, $pluginDir . '/' . $folderName);
				}
			}
		}
		$languageFolder = JPATH_ROOT . '/language';
		$files          = (array)$root->languages->children();
		for ($i = 0, $n = count($files); $i < $n; $i++)
		{
			$fileName          = $files[$i];
			$pos               = strpos($fileName, '.');
			$languageSubFolder = substr($fileName, 0, $pos);
			if (!is_file(Path::clean($languageFolder . '/' . $languageSubFolder . '/' . $fileName)))
			{
				File::copy($extractdir . '/' . $fileName, $languageFolder . '/' . $languageSubFolder . '/' . $fileName);
			}
		}
		Folder::delete($extractdir);

		return true;
	}

	/**
	 * Uninstall a payment plugin
	 *
	 * @param int $id
	 *
	 * @return boolean
	 */
	function uninstall($id)
	{
		$row = $this->getTable();
		$row->load($id);
		$name         = $row->name;
		$pluginFolder = JPATH_ROOT . '/components/com_jdonation/payments';
		$file         = $pluginFolder . '/' . $name . '.xml';
		if (!File::exists($file))
		{
			$row->delete();

			return true;
		}
		//$root      = JFactory::getXML($file);
		$root	   = simplexml_load_file($file);
		$files     = $root->files->children();
		$pluginDir = JPATH_ROOT . '/components/com_jdonation/payments';
		for ($i = 0, $n = count($files); $i < $n; $i++)
		{
			$file = $files[$i];
			if ($file->getName() == 'filename')
			{
				$fileName = $file;
				if (File::exists($pluginDir . '/' . $fileName))
				{
					File::delete($pluginDir . '/' . $fileName);
				}
			}
			elseif ($file->getName() == 'folder')
			{
				$folderName = $file;
				if ($folderName)
				{
					if (is_dir(Path::clean($pluginDir . '/' . $folderName)))
					{
						Folder::delete($pluginDir . '/' . $folderName);
					}
				}
			}
		}
		$files          = (array)$root->languages->children();
		$languageFolder = JPATH_ROOT . '/language';
		for ($i = 0, $n = count($files); $i < $n; $i++)
		{
			$fileName          = $files[$i];
			$pos               = strpos($fileName, '.');
			$languageSubFolder = substr($fileName, 0, $pos);
			if (is_file(Path::clean($languageFolder . '/' . $languageSubFolder . '/' . $fileName)))
			{
				File::delete($languageFolder . '/' . $languageSubFolder . '/' . $fileName);
			}
		}

		File::delete($pluginFolder . '/' . $name . '.xml');
		$row->delete();

		return true;
	}
}

?>
