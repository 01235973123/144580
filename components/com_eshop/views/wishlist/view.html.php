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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewWishlist extends EShopView
{
	/**
	 *
	 * @var $success
	 */
	protected $success;

	/**
	 *
	 * @var $products
	 */
	protected $products;

	/**
	 *
	 * @var $tax
	 */
	protected $tax;

	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	public function display($tpl = null)
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
			$this->setPageTitle(Text::_('ESHOP_WISHLIST'));

			$app->getDocument()->addStyleSheet(Uri::root(true) . '/media/com_eshop/assets/colorbox/colorbox.css');

			$tax      = new EShopTax(EShopHelper::getConfig());
			$currency = EShopCurrency::getInstance();

			$session  = $app->getSession();
			$wishlist = $session->get('wishlist') ?: [];

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			if (count($wishlist))
			{
				//Save wishlist from session
				foreach ($wishlist as $productId)
				{
					$query->clear()
						->select('COUNT(*)')
						->from('#__eshop_wishlists')
						->where('customer_id = ' . intval($user->get('id')))
						->where('product_id = ' . intval($productId));
					$db->setQuery($query);

					if (!$db->loadResult())
					{
						$row              = Table::getInstance('Eshop', 'Wishlist');
						$row->customer_id = $user->get('id');
						$row->product_id  = $productId;
						$row->store();
					}
				}

				$session->clear('wishlist');
			}

			$query->clear()
				->select('a.*, b.product_name')
				->from('#__eshop_products AS a')
				->innerJoin('#__eshop_wishlists AS w ON (a.id = w.product_id)')
				->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
				->where('a.published = 1')
				->where('w.customer_id = ' . intval($user->get('id')))
				->where('b.language = "' . Factory::getLanguage()->getTag() . '"');
			$db->setQuery($query);
			$products = $db->loadObjectList();

			for ($i = 0; $n = count($products), $i < $n; $i++)
			{
				// Resize wishlist images
				$imageSizeFunction = EShopHelper::getConfigValue('wishlist_image_size_function', 'resizeImage');

				if ($products[$i]->product_image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $products[$i]->product_image))
				{
					if (EShopHelper::getConfigValue('product_use_image_watermarks'))
					{
						$watermarkImage = EShopHelper::generateWatermarkImage(
							JPATH_ROOT . '/media/com_eshop/products/' . $products[$i]->product_image
						);
						$productImage   = $watermarkImage;
					}
					else
					{
						$productImage = $products[$i]->product_image;
					}

					$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
						[
							$productImage,
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_wishlist_width'),
							EShopHelper::getConfigValue('image_wishlist_height'),
						]);
				}
				else
				{
					$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
						[
							EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_wishlist_width'),
							EShopHelper::getConfigValue('image_wishlist_height'),
						]);
				}

				if ($imageSizeFunction == 'notResizeImage')
				{
					$products[$i]->image = Uri::base(true) . '/media/com_eshop/products/' . $image;
				}
				else
				{
					$products[$i]->image = Uri::base(true) . '/media/com_eshop/products/resized/' . $image;
				}

				// Product availability
				$productInventory = EShopHelper::getProductInventory($products[$i]->id);

				if ($products[$i]->product_quantity <= 0)
				{
					$availability = EShopHelper::getStockStatusName($productInventory['product_stock_status_id'], Factory::getLanguage()->getTag());
				}
				elseif ($productInventory['product_stock_display'])
				{
					$availability = $products[$i]->product_quantity;
				}
				else
				{
					$availability = Text::_('ESHOP_IN_STOCK');
				}

				$products[$i]->availability = $availability;

				// Price
				$productPriceArray = EShopHelper::getProductPriceArray($products[$i]->id, $products[$i]->product_price);

				if ($productPriceArray['salePrice'] >= 0)
				{
					$basePrice = $currency->format(
						$tax->calculate($productPriceArray['basePrice'], $products[$i]->product_taxclass_id, EShopHelper::getConfigValue('tax'))
					);
					$salePrice = $currency->format(
						$tax->calculate($productPriceArray['salePrice'], $products[$i]->product_taxclass_id, EShopHelper::getConfigValue('tax'))
					);
				}
				else
				{
					$basePrice = $currency->format(
						$tax->calculate($productPriceArray['basePrice'], $products[$i]->product_taxclass_id, EShopHelper::getConfigValue('tax'))
					);
					$salePrice = 0;
				}

				$products[$i]->base_price = $basePrice;
				$products[$i]->sale_price = $salePrice;
			}

			if ($session->get('success'))
			{
				$this->success = $session->get('success');
				$session->clear('success');
			}

			$this->products        = $products;
			$this->tax             = $tax;
			$this->currency        = $currency;
			$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
		}

		parent::display($tpl);
	}
}