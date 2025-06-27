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
class EShopViewCategories extends EShopView
{

	/**
	 *
	 * @var $warning
	 */
	protected $warning;

	/**
	 *
	 * @var $config
	 */
	protected $config;

	/**
	 *
	 * @var $items
	 */
	protected $items;

	/**
	 *
	 * @var $categoriesPerRow
	 */
	protected $categoriesPerRow;

	/**
	 *
	 * @var $pagination
	 */
	protected $pagination;

	/**
	 *
	 * @var $params
	 */
	protected $params;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

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

	public function display($tpl = null)
	{
		$app      = Factory::getApplication();
		$document = $app->getDocument();
		$session  = $app->getSession();
		$params   = $app->getParams();
		$model    = $this->getModel();

		$title = $params->get('page_title', '');

		if ($title == '')
		{
			$title = Text::_('ESHOP_CATEGORIES_TITLE');
		}

		$this->setPageTitle($title);

		$params->def('page_heading', Text::_('ESHOP_CATEGORIES_HEADING'));

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

		$session->set('continue_shopping_url', Uri::getInstance()->toString());

		if ($session->get('warning'))
		{
			$this->warning = $session->get('warning');
			$session->clear('warning');
		}

		$this->config           = EShopHelper::getConfig();
		$this->items            = $model->getData();
		$this->categoriesPerRow = EShopHelper::getConfigValue('items_per_row', 3);
		$this->pagination       = $model->getPagination();
		$this->params           = $params;
		$this->bootstrapHelper  = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

		if ($this->getLayout() == 'products')
		{
			JLoader::register('EShopModelProducts', JPATH_ROOT . '/components/com_eshop/models/products.php');

			for ($i = 0, $n = count($this->items); $i < $n; $i++)
			{
				$item     = $this->items[$i];
				$products = EshopRADModel::getInstance('Products', 'EShopModel', ['remember_states' => false])
					->limitstart(0)
					->limit($params->get('number_products', '5'))
					->category_id($item->id)
					->sort_options(EShopHelper::getConfigValue('default_sorting'))
					->getData();
				EShopHelper::prepareCustomFieldsData($products, true);
				$item->products = $products;
			}

			$this->tax      = new EShopTax(EShopHelper::getConfig());
			$this->currency = EShopCurrency::getInstance();
		}

		parent::display($tpl);
	}
}