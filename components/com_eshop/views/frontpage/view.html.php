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
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewFrontpage extends EShopView
{
	/**
	 *
	 * @var $params
	 */
	protected $params;

	/**
	 *
	 * @var $categories
	 */
	protected $categories;

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
	 * @var $productsPerRow
	 */
	protected $productsPerRow;

	/**
	 *
	 * @var $categoriesPerRow
	 */
	protected $categoriesPerRow;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	public function display($tpl = null)
	{
		$app      = Factory::getApplication();
		$document = $app->getDocument();
		$rootUri  = Uri::root(true);
		$document->addStyleSheet($rootUri . '/media/com_eshop/assets/colorbox/colorbox.css');
		$document->addStyleSheet($rootUri . '/media/com_eshop/assets/css/labels.css');

		$params = $app->getParams();
		$title  = $params->get('page_title', '');

		if ($title == '')
		{
			$title = Text::_('ESHOP_FRONT_PAGE_TITLE');
		}

		$this->setPageTitle($title);

		$params->def('page_heading', Text::_('ESHOP_FRONT_PAGE_HEADING'));

		// Set metakey, metadesc and robots
		if ($params->get('menu-meta_keywords'))
		{
			$document->setMetaData('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('menu-meta_description'))
		{
			$document->setMetaData('description', $params->get('menu-meta_description'));
		}

		if ($params->get('robots'))
		{
			$document->setMetadata('robots', $params->get('robots'));
		}

		$numberCategories = (int) $params->get('num_categories', 9);
		$numberProducts   = (int) $params->get('num_products', 9);

		JLoader::register('EShopModelCategories', JPATH_ROOT . '/components/com_eshop/models/categories.php');
		JLoader::register('EShopModelProducts', JPATH_ROOT . '/components/com_eshop/models/products.php');

		if ($numberCategories > 0)
		{
			$categories = EshopRADModel::getInstance('Categories', 'EShopModel', ['remember_states' => false])
				->limitstart(0)
				->limit($numberCategories)
				->getData();
		}
		else
		{
			$categories = [];
		}

		if ($numberProducts > 0)
		{
			$products = EshopRADModel::getInstance('Products', 'EShopModel', ['remember_states' => false])
				->limitstart(0)
				->limit($numberProducts)
				->product_type($params->get('product_type', 'featured'))
				->sort_options(EShopHelper::getConfigValue('default_sorting'))
				->getData();
		}
		else
		{
			$products = [];
		}

		// Store session for Continue Shopping Url
		$app->getSession()->set('continue_shopping_url', Uri::getInstance()->toString());
		$tax      = new EShopTax(EShopHelper::getConfig());
		$currency = EShopCurrency::getInstance();

		$this->params           = $params;
		$this->categories       = $categories;
		$this->products         = $products;
		$this->tax              = $tax;
		$this->currency         = $currency;
		$this->productsPerRow   = EShopHelper::getConfigValue('items_per_row', 3);
		$this->categoriesPerRow = EShopHelper::getConfigValue('items_per_row', 3);
		$this->bootstrapHelper  = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

		parent::display($tpl);
	}
}