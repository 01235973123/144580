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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewCart extends EShopView
{
	/**
	 *
	 * @var $cartData
	 */
	protected $cartData;

	/**
	 *
	 * @var $totalData
	 */
	protected $totalData;

	/**
	 *
	 * @var $total
	 */
	protected $total;

	/**
	 *
	 * @var $taxes
	 */
	protected $taxes;

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
	 * @var $coupon_code
	 */
	protected $coupon_code;

	/**
	 *
	 * @var $voucher_code
	 */
	protected $voucher_code;

	/**
	 *
	 * @var $postcode
	 */
	protected $postcode;

	/**
	 *
	 * @var $shipping_required
	 */
	protected $shipping_required;

	/**
	 *
	 * @var $weight
	 */
	protected $weight;

	/**
	 *
	 * @var $shipping_method
	 */
	protected $shipping_method;

	/**
	 *
	 * @var $lists
	 */
	protected $lists;

	/**
	 *
	 * @var $shipping_zone_id
	 */
	protected $shipping_zone_id;

	/**
	 *
	 * @var $success
	 */
	protected $success;

	/**
	 *
	 * @var $warning
	 */
	protected $warning;

	/**
	 *
	 * @var $user
	 */
	protected $user;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	public function display($tpl = null)
	{
		$app = Factory::getApplication();

		if (EShopHelper::isCatalogMode())
		{
			$session = $app->getSession();
			$session->set('warning', Text::_('ESHOP_CATALOG_MODE_ON'));
			$app->redirect(Route::_(EShopRoute::getViewRoute('categories')));
		}
		else
		{
			$menu     = $app->getMenu();
			$menuItem = $menu->getActive();

			if ($menuItem && (isset($menuItem->query['view']) && ($menuItem->query['view'] == 'frontpage')))
			{
				$pathway = $app->getPathway();
				$pathUrl = EShopRoute::getViewRoute('frontpage');
				$pathway->addItem(Text::_('ESHOP_SHOPPING_CART'), $pathUrl);
			}

			$document = $app->getDocument();
			$document->addStyleSheet(Uri::root(true) . '/media/com_eshop/assets/colorbox/colorbox.css');

			$this->setPageTitle(Text::_('ESHOP_SHOPPING_CART'));

			$session  = $app->getSession();
			$tax      = new EShopTax(EShopHelper::getConfig());
			$cart     = new EShopCart();
			$currency = EShopCurrency::getInstance();
			$cartData = $this->get('CartData');
			$model    = $this->getModel();
			$model->getCosts();
			$totalData               = $model->getTotalData();
			$total                   = $model->getTotal();
			$taxes                   = $model->getTaxes();
			$this->cartData          = $cartData;
			$this->totalData         = $totalData;
			$this->total             = $total;
			$this->taxes             = $taxes;
			$this->tax               = $tax;
			$this->currency          = $currency;
			$this->coupon_code       = $session->get('coupon_code');
			$this->voucher_code      = $session->get('voucher_code');
			$this->postcode          = $session->get('shipping_postcode');
			$this->shipping_required = $cart->hasShipping();

			if (EShopHelper::getConfigValue('cart_weight') && $cart->hasShipping())
			{
				$eshopWeight  = EShopWeight::getInstance();
				$this->weight = $eshopWeight->format($cart->getWeight(), EShopHelper::getConfigValue('weight_id'));
			}
			else
			{
				$this->weight = 0;
			}

			if ($this->shipping_required)
			{
				$shippingMethod = $session->get('shipping_method');

				if (is_array($shippingMethod))
				{
					$this->shipping_method = $shippingMethod['name'];
				}
				else
				{
					$this->shipping_method = '';
				}

				$document->addScriptDeclaration(EShopHtmlHelper::getZonesArrayJs());

				//Country list
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select('id, country_name AS name')
					->from('#__eshop_countries')
					->where('published=1')
					->order('country_name');
				$db->setQuery($query);
				$options             = [];
				$options[]           = HTMLHelper::_('select.option', 0, Text::_('ESHOP_PLEASE_SELECT'), 'id', 'name');
				$options             = array_merge($options, $db->loadObjectList());
				$countryId           = $session->get('shipping_country_id') > 0 ? $session->get('shipping_country_id') : EShopHelper::getConfigValue(
					'country_id'
				);
				$lists['country_id'] = HTMLHelper::_(
					'select.genericlist',
					$options,
					'country_id',
					' class="input-xlarge form-select" ',
					'id',
					'name',
					$countryId
				);

				//Zone list
				$query->clear()
					->select('id, zone_name')
					->from('#__eshop_zones')
					->where('country_id=' . (int) $countryId)
					->where('published=1')
					->order('zone_name');

				$db->setQuery($query);
				$options                = [];
				$options[]              = HTMLHelper::_('select.option', 0, Text::_('ESHOP_PLEASE_SELECT'), 'id', 'zone_name');
				$options                = array_merge($options, $db->loadObjectList());
				$lists['zone_id']       = HTMLHelper::_(
					'select.genericlist',
					$options,
					'zone_id',
					' class="input-xlarge form-select" ',
					'id',
					'zone_name',
					$session->get('shipping_zone_id')
				);
				$this->lists            = $lists;
				$this->shipping_zone_id = $session->get('shipping_zone_id');
			}

			// Success message
			if ($session->get('success'))
			{
				$this->success = $session->get('success');
				$session->clear('success');
			}

			if ($cart->getStockWarning() != '')
			{
				$this->warning = $cart->getStockWarning();
			}
			elseif ($cart->getMinSubTotalWarning() != '')
			{
				$this->warning = $cart->getMinSubTotalWarning();
			}
			elseif ($cart->getMinQuantityWarning() != '')
			{
				$this->warning = $cart->getMinQuantityWarning();
			}
			elseif ($cart->getMinProductQuantityWarning() != '')
			{
				$this->warning = $cart->getMinProductQuantityWarning();
			}
			elseif ($cart->getMaxProductQuantityWarning() != '')
			{
				$this->warning = $cart->getMaxProductQuantityWarning();
			}

			if ($session->get('warning'))
			{
				$this->warning = $session->get('warning');
				$session->clear('warning');
			}

			$this->user            = Factory::getUser();
			$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

			parent::display($tpl);
		}
	}
}