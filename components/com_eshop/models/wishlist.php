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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;

class EShopModelWishlist extends EShopModel
{
	public function add($productId)
	{
		$json        = [];
		$productInfo = EShopHelper::getProduct($productId, Factory::getLanguage()->getTag());

		if (is_object($productInfo))
		{
			$user             = Factory::getUser();
			$viewProductLink  = Route::_(EShopRoute::getProductRoute($productId, EShopHelper::getProductCategory($productId)));
			$viewWishListLink = Route::_(EShopRoute::getViewRoute('wishlist'));

			if ($user->get('id'))
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select('COUNT(*)')
					->from('#__eshop_wishlists')
					->where('customer_id = ' . intval($user->get('id')))
					->where('product_id = ' . intval($productId));
				$db->setQuery($query);

				if ($db->loadResult())
				{
					$message = '<div class="wish-list-message">' . sprintf(
							Text::_('ESHOP_ADD_TO_WISHLIST_ALREADY_IN_MESSAGE_USER'),
							$viewProductLink,
							$productInfo->product_name,
							$viewWishListLink
						) . '</div>';
				}
				else
				{
					$row              = Table::getInstance('Eshop', 'Wishlist');
					$row->customer_id = $user->get('id');
					$row->product_id  = $productId;
					$row->store();
					$message = '<div class="wish-list-message">' . sprintf(
							Text::_('ESHOP_ADD_TO_WISHLIST_SUCCESS_MESSAGE_USER'),
							$viewProductLink,
							$productInfo->product_name,
							$viewWishListLink
						) . '</div>';
				}
			}
			else
			{
				$session  = Factory::getApplication()->getSession();
				$wishlist = $session->get('wishlist');

				if (!$wishlist)
				{
					$wishlist = [];
				}

				if (!in_array($productId, $wishlist))
				{
					$wishlist[] = $productId;
					$session->set('wishlist', $wishlist);
				}

				$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();

				$loginLink    = Route::_(
					'index.php?option=com_users&view=login&return=' . base64_encode('index.php?option=com_eshop&view=wishlist') . $usersMenuItemStr
				);
				$registerLink = Route::_('index.php?option=com_users&view=registration' . $usersMenuItemStr);
				$message      = '<div class="wish-list-message">' . sprintf(
						Text::_('ESHOP_ADD_TO_WISHLIST_SUCCESS_MESSAGE_GUEST'),
						$loginLink,
						$registerLink,
						$viewProductLink,
						$productInfo->product_name,
						$viewWishListLink
					) . '</div>';
			}

			$json['success']['message'] = '<h1>' . Text::_('ESHOP_WISHLIST') . '</h1>' . $message;
		}

		return $json;
	}
}