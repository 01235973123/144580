<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\DatabaseDriver;

class OSMembershipHelperRoute
{
	/**
	 * Menu items look up array
	 *
	 * @var array
	 */
	protected static $lookup;

	/**
	 * Categories data
	 *
	 * @var array
	 */
	protected static $categories;

	/**
	 * Plans data
	 *
	 * @var array
	 */
	protected static $plans;

	/**
	 * Cached component menu item base on language
	 *
	 * @var array
	 */
	protected static $items;

	/**
	 * Find menu item associated to given plan
	 *
	 * @param   int     $id
	 * @param   int     $catId
	 * @param   int     $itemId
	 * @param   string  $language
	 *
	 * @return int
	 */
	public static function getPlanMenuId($id, $catId = 0, $itemId = 0, $language = null)
	{
		$needles = ['plan' => [(int) $id]];

		if ($catId)
		{
			$needles['plans']      = self::getCategoryIdsTree($catId);
			$needles['categories'] = $needles['plans'];
		}

		if ($language)
		{
			$needles['language'] = $language;
		}
		elseif (Multilanguage::isEnabled())
		{
			$needles['language'] = Factory::getApplication()->getLanguage()->getTag();
		}

		return self::findItem($needles, $itemId);
	}

	/**
	 * Function to get Category Route
	 *
	 * @param   int     $id
	 * @param   int     $itemId
	 * @param   string  $language
	 *
	 * @return  string
	 */
	public static function getCategoryRoute($id, $itemId = 0, $language = null)
	{
		//Create the link
		$link                  = 'index.php?option=com_osmembership&view=plans&id=' . $id;
		$needles['plans']      = self::getCategoryIdsTree($id);
		$needles['categories'] = $needles['plans'];

		if ($language)
		{
			$needles['language'] = $language;
			$link                .= '&lang=' . $language;
		}
		elseif (Multilanguage::isEnabled())
		{
			$needles['language'] = Factory::getApplication()->getLanguage()->getTag();
		}

		if ($item = self::findItem($needles, $itemId))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Function to get sign up router
	 *
	 * @param   int     $id
	 * @param   int     $itemId
	 * @param   string  $language
	 *
	 * @return string
	 */
	public static function getSignupRoute($id, $itemId = 0, $language = null)
	{
		self::loadPlan($id);

		//Create the link
		$link    = 'index.php?option=com_osmembership&view=register&id=' . $id;
		$needles = ['register' => [$id], 'plan' => [$id]];
		$catId   = (int) self::$plans[$id]->category_id;

		if ($catId)
		{
			$link                  .= '&catid=' . $catId;
			$needles['plans']      = self::getCategoryIdsTree($catId);
			$needles['categories'] = $needles['plans'];
		}

		if ($language)
		{
			$needles['language'] = $language;
			$link                .= '&lang=' . $language;
		}
		elseif (Multilanguage::isEnabled())
		{
			$needles['language'] = Factory::getApplication()->getLanguage()->getTag();
		}

		if ($item = self::findItem($needles, $itemId))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Method to find link to certain view without view key
	 *
	 * @param   string  $view
	 * @param   int     $Itemid
	 * @param   string  $language
	 *
	 * @return string
	 */
	public static function getViewRoute($view, $Itemid)
	{
		$link = 'index.php?option=com_osmembership&view=' . $view;

		if ($item = self::findView($view, $Itemid))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Get event title, used for building the router
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function getPlanTitle($id)
	{
		self::loadPlan($id);

		return self::$plans[$id]->alias;
	}

	/**
	 * Find item id variable corresponding to the view
	 *
	 * @param   string  $view
	 * @param   int     $itemId
	 *
	 * @return int
	 */
	public static function findView($view, $itemId = 0)
	{
		if (Multilanguage::isEnabled())
		{
			$language = Factory::getApplication()->getLanguage()->getTag();
		}
		else
		{
			$language = '*';
		}

		$items = self::getMenuItems($language);

		foreach ($items as $item)
		{
			if (isset($item->query['view']) && $item->query['view'] === $view)
			{
				return $item->id;
			}
		}

		return $itemId;
	}

	/**
	 * Get path from parent category to the given category
	 *
	 * @param   int  $id
	 * @param   int  $parentId
	 *
	 * @return  array
	 */
	public static function getCategoryRoutePath($id, $parentId = 0)
	{
		self::buildCategories();

		$paths = [];

		do
		{
			if (isset(self::$categories[$id]))
			{
				$paths[] = self::$categories[$id]->alias;
				$id      = self::$categories[$id]->parent_id;
			}
			else
			{
				break;
			}
		} while ($id != $parentId);

		return array_reverse($paths);
	}

	/**
	 * Method to allow passing plan from outside to route to reduce number of query
	 *
	 * @param   stdClass  $plan
	 */
	public static function addPlan($plan)
	{
		if (!isset(self::$plans[$plan->id]))
		{
			self::$plans[$plan->id] = $plan;
		}
	}

	/**
	 * Load the plan if it is not available in cache
	 *
	 * @param   int  $id
	 *
	 * @return void
	 */
	protected static function loadPlan($id)
	{
		if (!isset(self::$plans[$id]))
		{
			$fieldSuffix = OSMembershipHelper::getFieldSuffix();

			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select('id, category_id')
				->select($db->quoteName('alias' . $fieldSuffix, 'alias'))
				->from('#__osmembership_plans')
				->where('id  = ' . (int) $id);
			$db->setQuery($query);

			self::$plans[$id] = $db->loadObject();
		}
	}

	/**
	 * Get IDs of all categories in category tree from the given category to root
	 *
	 * @param   int  $id
	 * @param   int  $parentId
	 *
	 * @retrun []
	 */
	protected static function getCategoryIdsTree($id, $parentId = 0)
	{
		self::buildCategories();

		$catIds = [];

		do
		{
			if (isset(self::$categories[$id]))
			{
				$catIds[] = self::$categories[$id]->id;
				$id       = self::$categories[$id]->parent_id;
			}
			else
			{
				break;
			}
		} while ($id != $parentId);

		return $catIds;
	}

	/**
	 * Build categories data
	 *
	 * @return void
	 */
	protected static function buildCategories()
	{
		if (self::$categories === null)
		{
			$fieldSuffix = OSMembershipHelper::getFieldSuffix();

			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select('id, parent_id')
				->select($db->quoteName('alias' . $fieldSuffix, 'alias'))
				->from('#__osmembership_categories')
				->where('published = 1');
			$db->setQuery($query);

			self::$categories = $db->loadObjectList('id');
		}
	}

	/**
	 * Find menu item which matches needles array
	 *
	 * @param   array  $needles
	 * @param   int    $itemId
	 *
	 * @return  int
	 */
	public static function findItem($needles = [], $itemId = 0)
	{
		$language = $needles['language'] ?? '*';

		self::buildLookup($language);

		foreach ($needles as $view => $ids)
		{
			if (isset(self::$lookup[$language][$view]))
			{
				foreach ($ids as $id)
				{
					$id = (int) $id;

					if (isset(self::$lookup[$language][$view][(int) $id]))
					{
						return self::$lookup[$language][$view][(int) $id];
					}
				}
			}
		}

		//Return default item id
		return $itemId;
	}

	/**
	 * Get default menu item
	 *
	 * @param   string  $language
	 *
	 * @return int
	 */
	public static function getDefaultMenuItem($language = null)
	{
		if ($language === null && Multilanguage::isEnabled())
		{
			$language = Factory::getApplication()->getLanguage()->getTag();
		}

		if ($language === null)
		{
			$language = '*';
		}

		$items = self::getMenuItems($language);

		$defaultViews = ['plans', 'categories', 'plan'];

		foreach ($items as $item)
		{
			if (!empty($item->query['view']) && in_array($item->query['view'], $defaultViews))
			{
				return $item->id;
			}
		}

		return 0;
	}

	/**
	 * Build and cache the lookup array
	 *
	 * @param $language
	 */
	protected static function buildLookup($language = '*')
	{
		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = [];

			$items = self::getMenuItems($language);

			foreach ($items as $item)
			{
				if (!empty($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = [];
					}

					if (isset($item->query['id']))
					{
						self::$lookup[$language][$view][$item->query['id']] = $item->id;
					}
					else
					{
						self::$lookup[$language][$view][0] = $item->id;
					}
				}
			}
		}
	}

	/**
	 * Get component menu items for given language
	 *
	 * @param   string  $language
	 */
	protected static function getMenuItems($language = '*')
	{
		if (!isset(self::$items[$language]))
		{
			$component  = ComponentHelper::getComponent('com_osmembership');
			$attributes = ['component_id'];
			$values     = [$component->id];

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = [$language, '*'];
			}

			self::$items[$language] = Factory::getApplication()->getMenu('site')->getItems($attributes, $values);
		}

		return self::$items[$language];
	}
}
