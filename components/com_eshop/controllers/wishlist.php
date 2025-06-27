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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

require_once __DIR__ . '/jsonresponse.php';

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerWishlist extends BaseController
{
	use EShopControllerJsonresponse;

	/**
	 *
	 * Function to add a product into the wishlist
	 */
	public function add()
	{
		$productId = $this->input->getInt('product_id', 0);

		/* @var EShopModelWishlist $model */
		$model = $this->getModel('Wishlist');
		$json  = $model->add($productId);

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to remove a product from the wishlist
	 */
	public function remove()
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		if (!$user->get('id'))
		{
			$usersMenuItemStr = EShopHelper::getUsersMenuItemStr();
			$app->enqueueMessage(Text::_('ESHOP_YOU_MUST_LOGIN_TO_VIEW_WISHLIST'), 'Notice');
			$app->redirect(
				Route::_(
					'index.php?option=com_users&view=login&return=' . base64_encode('index.php?option=com_eshop&view=wishlist') . $usersMenuItemStr
				)
			);
		}
		else
		{
			$productId = $this->input->getInt('product_id', 0);
			$db        = Factory::getDbo();
			$query     = $db->getQuery(true);
			$query->delete('#__eshop_wishlists')
				->where('customer_id = ' . intval($user->get('id')))
				->where('product_id = ' . intval($productId));
			$db->setQuery($query);
			$db->execute();

			$app->getSession()->set('success', Text::_('ESHOP_WISHLIST_REMOVED_MESSAGE'));

			$json['redirect'] = Route::_(EShopRoute::getViewRoute('wishlist'));

			$this->sendJsonResponse($json);
		}
	}
}