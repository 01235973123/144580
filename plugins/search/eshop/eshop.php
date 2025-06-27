<?php

/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

require_once(JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php');
require_once JPATH_ROOT . '/administrator/components/com_eshop/libraries/autoload.php';

class plgSearchEshop extends CMSPlugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		return $this->onSearch($text, $phrase, $ordering, $areas);
	}

	public function onContentSearchAreas()
	{
		return $this->onSearchAreas();
	}

	public function onSearchAreas()
	{
		static $areas = ['eshop' => 'Products'];

		return $areas;
	}

	public function onSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		if (is_array($areas) && !array_intersect($areas, array_keys($this->onContentSearchAreas())))
		{
			return [];
		}
		$plugin = PluginHelper::getPlugin('search', 'eshop');
		$params = new Registry($plugin->params);
		$text   = StringHelper::trim($text);
		if ($text == '')
		{
			return [];
		}
		$text  = StringHelper::strtolower($text);
		$db    = Factory::getDBO();
		$limit = $params->get('search_limit', 50);
		switch ($phrase)
		{
			case 'exact':
				$text      = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres2   = [];
				$wheres2[] = 'p.product_sku LIKE ' . $text;
				$wheres2[] = 'pd.product_name LIKE ' . $text;
				$wheres2[] = 'pd.product_short_desc LIKE ' . $text;
				$wheres2[] = 'pd.product_desc LIKE ' . $text;
				$wheres2[] = 'pd.tab1_title LIKE ' . $text;
				$wheres2[] = 'pd.tab1_content LIKE ' . $text;
				$wheres2[] = 'pd.tab2_title LIKE ' . $text;
				$wheres2[] = 'pd.tab2_content LIKE ' . $text;
				$wheres2[] = 'pd.tab3_title LIKE ' . $text;
				$wheres2[] = 'pd.tab3_content LIKE ' . $text;
				$wheres2[] = 'pd.tab4_title LIKE ' . $text;
				$wheres2[] = 'pd.tab4_content LIKE ' . $text;
				$wheres2[] = 'pd.tab5_title LIKE ' . $text;
				$wheres2[] = 'pd.tab5_content LIKE ' . $text;
				$where     = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words  = explode(' ', $text);
				$wheres = [];

				foreach ($words as $word)
				{
					$word      = $db->quote('%' . $db->escape($word, true) . '%', false);
					$wheres2   = [];
					$wheres2[] = 'LOWER(p.product_sku) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.product_name) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.product_short_desc) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.product_desc) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.tab1_title) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.tab1_content) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.tab2_title) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.tab2_content) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.tab3_title) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.tab3_content) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.tab4_title) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.tab4_content) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.tab5_title) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(pd.tab5_content) LIKE LOWER(' . $word . ')';
					$wheres[]  = implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}
		switch ($ordering)
		{
			case 'oldest':
				$orderBy = 'p.created_date ASC';
				break;
			case 'popular':
				$orderBy = 'p.hits DESC';
				break;
			case 'alpha':
				$orderBy = 'pd.product_name ASC';
				break;
			case 'category':
				$orderBy = 'pd.product_name ASC';
				break;
			case 'newest':
			default :
				$orderBy = 'p.created_date DESC';
				break;
		}
		$query = "SELECT DISTINCT pd.product_id, pd.product_name AS title, pd.product_desc AS text, p.created_date AS created" .
			" FROM #__eshop_products AS p" .
			" INNER JOIN #__eshop_productdetails AS pd ON p.id = pd.product_id" .
			" WHERE (" . $where . ") AND p.published = 1" .
			" GROUP BY p.id" .
			" ORDER BY {$orderBy}" .
			" LIMIT " . $limit;
		$db->setQuery($query);
		$results = $db->loadObjectList();
		$ret     = [];
		if (empty($results))
		{
			return $ret;
		}
		foreach ($results as $result)
		{
			$categoryId = EShopHelper::getProductCategory($result->product_id);
			if ($categoryId > 0)
			{
				$category           = EShopHelper::getCategory($categoryId, false);
				$result->href       = EShopRoute::getProductRoute($result->product_id, $categoryId);
				$result->section    = $category->category_name;
				$result->browsernav = 2;
				$ret[]              = $result;
			}
		}

		return $ret;
	}
}