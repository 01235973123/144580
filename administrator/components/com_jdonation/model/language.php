<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\Registry\Registry;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

class DonationModelLanguage extends OSFModel
{

	/**
	 * Instantiate the model.
	 *
	 * @param   array $config The configuration data for the model
	 *
	 */
	public function __construct($config)
	{
		parent::__construct($config);
		$this->state->insert('filter_search', 'string')
			->insert('filter_item', 'string', 'com_jdonation')
			->insert('filter_language', 'string', 'en-GB')
			->insert('limit', 'int', 100)
			->insert('limitstart', 'int', 0);
	}

    function getTotalItems($item,$search,$site){
        jimport('joomla.filesystem.file');
        $search = strtolower($search);
        $registry = new Registry();
        if($site){
            $languageFolder= JPATH_ADMINISTRATOR . '/language/';
        }else{
            $languageFolder=JPATH_ROOT . '/language/';
        }
        $path = $languageFolder . 'en-GB/en-GB.' . $item . '.ini';
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
        return count($enGbItems);
    }

    /**
     * Get pagination object
     *
     * @return JPagination
     */
    function getPagination($item,$search,$limitstart,$limit,$site)
    {
        // Lets load the content if it doesn't already exist
        if (empty($pagination))
        {

            jimport('joomla.html.pagination');
            $pagination = new Pagination($this->getTotalItems($item,$search,$site), $limitstart, $limit);
        }
        return $pagination;
    }
	/**
	 * Get language items and store them in an array
	 */
	function getTrans($lang, $item,$search,$limitstart,$limit,$site)
	{
		$registry  = new Registry();
		$languages = array();
        if($site){
            $floder=JPATH_ADMINISTRATOR;
        }else{
            $floder=JPATH_ROOT;
        }
		$path      = $floder . '/language/en-GB/en-GB.' . $item . '.ini';
		$registry->loadFile($path, 'INI');
        $enGbItems = $registry->toArray();
		$languages['en-GB'][$item] = $registry->toArray();
		$path                      = $floder . '/language/' . $lang . '/' . $lang . '.' . $item . '.ini';
		if (is_file(Path::clean($path)))
		{
			$registry->loadFile($path, 'INI');
			$languages[$lang][$item] = $registry->toArray();
		}
		else
		{
			$languages[$lang][$item] = array();
		}
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
        $languages['en-GB'][$item] = array_slice($enGbItems, $limitstart,$limit);
		return $languages;
	}

	/**
	 * Get site languages
	 */
	function getSiteLanguages()
	{
		$path    = JPATH_ROOT . '/language';
		$folders = Folder::folders($path);
		$rets    = array();
		foreach ($folders as $folder)
		{
			if ($folder != 'pdf_fonts')
			{
				$rets[] = $folder;
			}
		}

		return $rets;
	}

	/**
	 * Save translation data
	 *
	 * @param array $data
	 */
    function save($data)
    {
        $registry = new Registry();
        jimport('joomla.filesystem.file');
        $lang     = $data['filter_language'];
        $item     = $data['filter_item'];
        $site     = $data['site'];
        if($site){
            $folder = JPATH_ADMINISTRATOR;
        }else{
            $folder =JPATH_ROOT;
        }
        $filePath = $folder . '/language/' . $lang . '/' . $lang . '.' . $item . '.ini';
        $registry->loadFile($filePath,'INI');
        //echo count(($registry));
        //die();
        $keys = $data['keys'];
        $items = $data['items'];
        $content  = "";
        foreach ($items as $item1)
        {
            $item1 = trim($item1);
            $value = trim($data['item_'.$item1]);
            $registry->set($keys[$item1], $value);
        }
        //echo count($registry);
        File::write($filePath, $registry->toString('INI'));

        return true;
    }
}
