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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\LanguageHelper;

class EShopRoute
{
	protected static $lookup;
	protected static $lang_lookup = [];

	/**
	 *
	 * Function to get Product Route
	 *
	 * @param   int  $id
	 * @param   int  $catid
	 *
	 * @return string
	 */
	public static function getProductRoute($id, $catid, $language = '')
	{
		$link = 'index.php?option=com_eshop&view=product&id=' . $id;

		if (!EShopHelper::getConfigValue('add_category_path'))
		{
			$item = self::getDefaultItemId($language);
			$link .= '&Itemid=' . $item;
		}
		else
		{
			$needles = ['product' => [(int) $id]];

			if ($catid)
			{
				$needles['category']   = array_reverse(EShopHelper::getCategoryPath($catid, 'id'));
				$needles['categories'] = $needles['category'];
				$link                  .= '&catid=' . $catid;
			}

			if ($language && $language != "*" && Multilanguage::isEnabled())
			{
				self::buildLanguageLookup();

				if (isset(self::$lang_lookup[$language]))
				{
					$link                .= '&lang=' . self::$lang_lookup[$language] . '&l=' . $language;
					$needles['language'] = $language;
				}
			}

			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
			else
			{
				$item = self::getDefaultItemId($language);
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;
	}

	/**
	 *
	 * Function to get Category Route
	 *
	 * @param   int  $id
	 *
	 * @return string
	 */
	public static function getCategoryRoute($id, $language = '')
	{
		if (!$id)
		{
			$link = '';
		}
		else
		{
			//Create the link
			$link    = 'index.php?option=com_eshop&view=category&id=' . $id;
			$catids  = array_reverse(EShopHelper::getCategoryPath($id, 'id'));
			$needles = [
				'category'   => $catids,
				'categories' => $catids,
			];

			if ($language && $language != "*" && Multilanguage::isEnabled())
			{
				self::buildLanguageLookup();

				if (isset(self::$lang_lookup[$language]))
				{
					$link                .= '&lang=' . self::$lang_lookup[$language] . '&l=' . $language;
					$needles['language'] = $language;
				}
			}

			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;
	}

	/**
	 *
	 * Function to get Manufacturer Route
	 *
	 * @param   int  $id
	 *
	 * @return string
	 */
	public static function getManufacturerRoute($id, $language = '')
	{
		if (!$id)
		{
			$link = '';
		}
		else
		{
			//Create the link
			$link    = 'index.php?option=com_eshop&view=manufacturer&id=' . $id;
			$needles = [
				'manufacturer' => [(int) $id],
			];

			if ($language && $language != "*" && Multilanguage::isEnabled())
			{
				self::buildLanguageLookup();

				if (isset(self::$lang_lookup[$language]))
				{
					$link                .= '&lang=' . self::$lang_lookup[$language] . '&l=' . $language;
					$needles['language'] = $language;
				}
			}

			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;
	}

	/**
	 *
	 * Function to get View Route
	 *
	 * @param   string  $view  (cart, checkout, compare, wishlist)
	 *
	 * @return string
	 */
	public static function getViewRoute($view, $language = '')
	{
		if (Multilanguage::isEnabled() && $language == '')
		{
			$language = Factory::getLanguage()->getTag();
		}

		//Create the link
		$link = 'index.php?option=com_eshop&view=' . $view;

		if ($language && $language != "*" && Multilanguage::isEnabled())
		{
			self::buildLanguageLookup();

			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language] . '&l=' . $language;
			}
		}

		if ($item = self::findView($view, $language))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 *
	 * Function to find a view
	 *
	 * @param   string  $view
	 *
	 * @return int
	 */
	public static function findView($view, $language = '')
	{
		$needles = [
			$view => [0],
		];

		if ($language && $language != "*" && Multilanguage::isEnabled())
		{
			self::buildLanguageLookup();

			if (isset(self::$lang_lookup[$language]))
			{
				$needles['language'] = $language;
			}
		}

		if ($item = self::_findItem($needles))
		{
			return $item;
		}
		elseif ($item = self::getDefaultItemId($language))
		{
			return $item;
		}
		else
		{
			return 0;
		}
	}

	/**
	 *
	 * Function to build lanugage lookup
	 */
	protected static function buildLanguageLookup()
	{
		if (count(self::$lang_lookup) == 0)
		{
			foreach (LanguageHelper::getLanguages('default') as $lang)
			{
				self::$lang_lookup[$lang->lang_code] = $lang->sef;
			}
		}
	}

	/**
	 *
	 * Function to find Itemid
	 *
	 * @param   string  $needles
	 *
	 * @return int
	 */
	protected static function _findItem($needles = null)
	{
		$app      = Factory::getApplication();
		$menus    = $app->getMenu('site');
		$language = $needles['language'] ?? '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = [];

			$component  = ComponentHelper::getComponent('com_eshop');
			$attributes = ['component_id'];
			$values     = [$component->id];

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = [$needles['language'], '*'];
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
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

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$language][$view][(int) $id]))
						{
							return self::$lookup[$language][$view][(int) $id];
						}
					}
				}
			}

			if ($defaultItemid = self::getDefaultItemId($language))
			{
				return $defaultItemid;
			}
		}

		return 0;
	}

	/**
	 *
	 * Function to find default item id
	 */
	public static function getDefaultItemId($language = '')
	{
		if (!$language || $language == '*')
		{
			$language = Factory::getLanguage()->getTag();
		}

		if (Multilanguage::isEnabled())
		{
			if (EShopHelper::getConfigValue('default_menu_item_' . $language) > 0)
			{
				return EShopHelper::getConfigValue('default_menu_item_' . $language);
			}
		}
		else
		{
			if (EShopHelper::getConfigValue('default_menu_item') > 0)
			{
				return EShopHelper::getConfigValue('default_menu_item');
			}
			else
			{
				//Find in order: frontpage, categories
				$defaultViews = ['frontpage', 'categories'];

				foreach ($defaultViews as $view)
				{
					if (isset(self::$lookup[$language][$view]))
					{
						return self::$lookup[$language][$view][0];
					}
				}
			}
		}

		return 0;
	}
}