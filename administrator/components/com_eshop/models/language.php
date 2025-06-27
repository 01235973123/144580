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
use Joomla\Filesystem\Folder;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Registry\Registry;

/**
 * Eshop Component Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelLanguage extends BaseDatabaseModel
{

	protected $pagination;

	protected $total;

	/**
	 * Constructor function
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		$app        = Factory::getApplication();
		$limit      = $app->getUserStateFromRequest('global.list.limit', 'limit', 100, 'int');
		$limitstart = $app->getUserStateFromRequest('com_eshop.language.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Get pagination object
	 *
	 * @return Pagination
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			$this->pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
	}

	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Get language items and store them in an array
	 *
	 */
	public function getTrans($language, $languageFile)
	{
		$app      = Factory::getApplication();
		$search   = $app->getUserStateFromRequest('com_eshop.language.search', 'search', '', 'string');
		$search   = strtolower($search);
		$registry = new Registry();
		if (strpos($languageFile, 'admin') !== false)
		{
			$languageFolder = JPATH_ROOT . '/administrator/language/';
			$languageFile   = substr($languageFile, 6);
		}
		else
		{
			$languageFolder = JPATH_ROOT . '/language/';
		}
		$path = $languageFolder . 'en-GB/en-GB.' . $languageFile . '.ini';
		$registry->loadFile($path, 'INI');
		$enGbItems = $registry->toArray();
		if ($search)
		{
			$search = strtolower($search);
			foreach ($enGbItems as $key => $value)
			{
				if (strpos(strtolower($key), $search) === false && strpos(strtolower($value), $search) === false)
				{
					unset($enGbItems[$key]);
				}
			}
		}
		$this->total                  = count($enGbItems);
		$data['en-GB'][$languageFile] = array_slice($enGbItems, $this->getState('limitstart'), $this->getState('limit'));
		if ($language != 'en-GB')
		{
			$path = $languageFolder . $language . '/' . $language . '.' . $languageFile . '.ini';
			if (is_file($path))
			{
				$registry->loadFile($path);
				$languageItems = $registry->toArray();
				if ($search)
				{
					$search = strtolower($search);
					foreach ($languageItems as $key => $value)
					{
						if (strpos(strtolower($key), $search) === false && strpos(strtolower($value), $search) === false)
						{
							unset($languageItems[$key]);
						}
					}
				}
				$data[$language][$languageFile] = array_slice($languageItems, $this->getState('limitstart'), $this->getState('limit'));
			}
			else
			{
				$data[$language][$languageFile] = [];
			}
		}

		return $data;
	}

	/**
	 *  Get site languages
	 *
	 */
	public function getSiteLanguages()
	{
		$path    = JPATH_ROOT . '/language';
		$folders = Folder::folders($path);
		$rets    = [];
		foreach ($folders as $folder)
		{
			if ($folder != 'pdf_fonts' && $folder != 'overrides')
			{
				$rets[] = $folder;
			}
		}

		return $rets;
	}

	/**
	 * Save translation data
	 *
	 * @param   array  $data
	 */
	public function save($data)
	{
		$language     = $data['lang'];
		$languageFile = $data['item'];
		if (strpos($languageFile, 'admin') !== false)
		{
			$languageFolder = JPATH_ROOT . '/administrator/language/';
			$languageFile   = substr($languageFile, 6);
		}
		else
		{
			$languageFolder = JPATH_ROOT . '/language/';
		}
		$registry = new Registry();
		$filePath = $languageFolder . $language . '/' . $language . '.' . $languageFile . '.ini';
		if (is_file($filePath))
		{
			$registry->loadFile($filePath, 'INI');
		}
		else
		{
			$registry->loadFile($languageFolder . 'en-GB/en-GB.' . $languageFile . '.ini', 'INI');
		}
		//Get the current language file and store it to array
		$keys    = $data['keys'];
		$content = "";
		foreach ($keys as $key)
		{
			$key   = trim($key);
			$value = trim($data[$key]);
			$registry->set($key, $value);
		}
		if (isset($data['extra_keys']))
		{
			$keys   = $data['extra_keys'];
			$values = $data['extra_values'];
			for ($i = 0, $n = count($keys); $i < $n; $i++)
			{
				$key   = trim($keys[$i]);
				$value = trim($values[$i]);
				$registry->set($key, $value);
			}
		}

		if ($language != 'en-GB')
		{
			//We need to add new language items which are not existing in the current language
			$enRegistry = new Registry();
			$enRegistry->loadFile($languageFolder . 'en-GB/en-GB.' . $languageFile . '.ini', 'INI');
			$enLanguageItems      = $enRegistry->toArray();
			$currentLanguageItems = $registry->toArray();
			foreach ($enLanguageItems as $key => $value)
			{
				$currentLanguageKeys = array_keys($currentLanguageItems);
				if (!in_array($key, $currentLanguageKeys))
				{
					$registry->set($key, $value);
				}
			}
		}
		File::write($filePath, $registry->toString('INI'));

		return true;
	}
}