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

use Joomla\CMS\Factory;

/**
 * EShop Component Association Helper
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          3.0
 */
abstract class EShopHelperAssociation
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id    Id of the item
	 * @param   string   $view  Name of the view
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @since  3.0
	 */

	public static function getAssociations($id = 0, $view = null)
	{
		jimport('helper.route', JPATH_COMPONENT_SITE);

		$input  = Factory::getApplication()->input;
		$view   = is_null($view) ? $input->get('view') : $view;
		$id     = empty($id) ? $input->getInt('id') : $id;
		$return = [];

		if ($view == 'product' || $view == 'category' || $view == 'manufacturer')
		{
			if ($id)
			{
				$associations = EShopHelper::getAssociations($id, $view);

				foreach ($associations as $tag => $item)
				{
					if ($view == 'product')
					{
						$return[$tag] = EShopRoute::getProductRoute(
							$item->product_id,
							EShopHelper::getProductCategory($item->product_id),
							$item->language
						);
					}
					if ($view == 'category')
					{
						$return[$tag] = EShopRoute::getCategoryRoute($item->category_id, $item->language);
					}
				}
			}
		}
		elseif ($view == 'cart' || $view == 'checkout' || $view == 'wishlist' || $view == 'compare' || $view == 'customer')
		{
			$languageTag = Factory::getLanguage()->getTag();
			$languages   = EShopHelper::getLanguages();

			foreach ($languages as $language)
			{
				if ($language->lang_code != $languageTag)
				{
					$return[$language->lang_code] = EShopRoute::getViewRoute($view, $language->lang_code);
				}
			}
		}

		return $return;
	}
}