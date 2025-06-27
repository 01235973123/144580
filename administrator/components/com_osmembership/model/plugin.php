<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

class OSMembershipModelPlugin extends MPFModelAdmin
{
	/**
	 * Pre-process data, store plugins param in JSON format
	 *
	 * @param   OSMembershipTablePlugin  $row
	 * @param   MPFInput                 $input
	 * @param   bool                     $isNew
	 */
	protected function beforeStore($row, $input, $isNew)
	{
		$params = $input->get('params', [], 'array');

		// Trim space character from beginning and the end
		foreach ($params as $key => $value)
		{
			if (is_string($value))
			{
				$params[$key] = trim($value);
			}
		}

		if (is_array($params))
		{
			$params = json_encode($params);
		}
		else
		{
			$params = null;
		}

		$input->set('params', $params);
	}

	/**
	 * Method to install a payment plugin
	 *
	 * @param   MPFInput  $input
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function install($input)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$plugin = $input->files->get('plugin_package', null, 'raw');

		if ($plugin['error'] || $plugin['size'] < 1)
		{
			throw new Exception(Text::_('Upload plugin package error'));
		}

		$dest = Factory::getApplication()->get('tmp_path') . '/' . $plugin['name'];

		try
		{
			File::upload($plugin['tmp_name'], $dest);
		}
		catch (FileSystemException $e)
		{
			throw new Exception(Text::_('OSM_PLUGIN_UPLOAD_FAILED'));
		}

		// Temporary folder to extract the archive into
		$tmpDir     = uniqid('install_');
		$extractDir = Path::clean(dirname($dest) . '/' . $tmpDir);

		$archive = new Archive(['tmp_path' => Factory::getApplication()->get('tmp_path')]);
		$result  = $archive->extract($dest, $extractDir);

		if (!$result)
		{
			throw new Exception(Text::_('OSM_EXTRACT_PLUGIN_ERROR'));
		}

		$dirList = array_merge(Folder::files($extractDir, ''), Folder::folders($extractDir, ''));

		if (count($dirList) == 1 && is_dir(Path::clean($extractDir . '/' . $dirList[0])))
		{
			$extractDir = Path::clean($extractDir . '/' . $dirList[0]);
		}

		//Now, search for xml file
		$xmlFiles = Folder::files($extractDir, '.xml$', 1, true);

		if (empty($xmlFiles))
		{
			throw new Exception(Text::_('OSM_COULD_NOT_FIND_XML_FILE'));
		}

		$file       = $xmlFiles[0];
		$root       = simplexml_load_file($file);
		$pluginType = $root->attributes()->type;

		if ($root->getName() !== 'install')
		{
			throw new Exception(Text::_('OSM_INVALID_OSM_PLUGIN'));
		}

		if ($pluginType != 'osmplugin')
		{
			throw new Exception(Text::_('OSM_INVALID_OSM_PLUGIN'));
		}

		$name             = (string) $root->name;
		$title            = (string) $root->title;
		$author           = (string) $root->author;
		$creationDate     = (string) $root->creationDate;
		$copyright        = (string) $root->copyright;
		$license          = (string) $root->license;
		$authorEmail      = (string) $root->authorEmail;
		$authorUrl        = (string) $root->authorUrl;
		$version          = (string) $root->version;
		$description      = (string) $root->description;
		$supportRecurring = (string) $root->supportRecurring;
		$row              = $this->getTable();

		$query->select('id')
			->from('#__osmembership_plugins')
			->where('name=' . $db->quote($name));
		$db->setQuery($query);
		$pluginId = (int) $db->loadResult();

		if ($pluginId)
		{
			$row->load($pluginId);
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

		if ($supportRecurring === '1')
		{
			$row->support_recurring_subscription = 1;
		}

		$row->store();

		// Update plugins which support recurring payments
		$recurringPlugins = [
			'os_paypal',
			'os_authnet',
			'os_paypal_pro',
			'os_stripe',
			'os_ideal',
			'os_stripecheckout',
			'os_payfast',
		];

		if (in_array($row->name, $recurringPlugins))
		{
			$query->clear();
			$query->update('#__osmembership_plugins')
				->set('support_recurring_subscription = 1')
				->where('name IN ("' . implode('","', $recurringPlugins) . '")');
			$db->setQuery($query);
			$db->execute();
		}

		$pluginDir = JPATH_ROOT . '/components/com_osmembership/plugins';
		File::move($file, $pluginDir . '/' . basename($file));
		$files = $root->files->children() ?: [];

		foreach ($files as $file)
		{
			if ($file->getName() == 'filename')
			{
				$fileName = $file;
				File::copy($extractDir . '/' . $fileName, $pluginDir . '/' . $fileName);
			}
			elseif ($file->getName() == 'folder')
			{
				$folderName = $file;

				if (is_dir(Path::clean($extractDir . '/' . $folderName)))
				{
					if (is_dir(Path::clean($pluginDir . '/' . $folderName)))
					{
						Folder::delete($pluginDir . '/' . $folderName);
					}

					Folder::move($extractDir . '/' . $folderName, $pluginDir . '/' . $folderName);
				}
			}
		}

		// Delete the uploaded file
		File::delete($dest);

		// Delete the folder which the file is moved to and extracted

		Folder::delete(Factory::getApplication()->get('tmp_path') . '/' . $tmpDir);
	}

	/**
	 * Uninstall a payment plugin
	 *
	 * @param   int  $id
	 *
	 * @return bool
	 */
	public function uninstall($id)
	{
		$row = $this->getTable();
		$row->load($id);
		$name      = $row->name;
		$pluginDir = JPATH_ROOT . '/components/com_osmembership/plugins';
		$file      = $pluginDir . '/' . $name . '.xml';

		if (!is_file($file))
		{
			$row->delete();

			return true;
		}

		$root  = simplexml_load_file($file);
		$files = $root->files->children() ?: [];

		foreach ($files as $file)
		{
			if ($file->getName() == 'filename')
			{
				$fileName = $file;

				if (is_file($pluginDir . '/' . $fileName))
				{
					File::delete($pluginDir . '/' . $fileName);
				}
			}
			elseif ($file->getName() == 'folder')
			{
				$folderName = $file;

				if (is_dir($pluginDir . '/' . $folderName))
				{
					Folder::delete($pluginDir . '/' . $folderName);
				}
			}
		}

		File::delete($pluginDir . '/' . $name . '.xml');
		$row->delete();

		return true;
	}
}
