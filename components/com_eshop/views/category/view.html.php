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
class EShopViewCategory extends EShopView
{
	/**
	 *
	 * @var $sort_options
	 */
	protected $sort_options;

	/**
	 *
	 * @var $category
	 */
	protected $category;

	/**
	 *
	 * @var $subCategories
	 */
	protected $subCategories;

	/**
	 *
	 * @var $subCategoriesPerRow
	 */
	protected $subCategoriesPerRow;

	/**
	 *
	 * @var $products
	 */
	protected $products;

	/**
	 *
	 * @var $pagination
	 */
	protected $pagination;

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
	 * @var $actionUrl
	 */
	protected $actionUrl;

	/**
	 *
	 * @var $productsPerRow
	 */
	protected $productsPerRow;

	/**
	 *
	 * @var $categoriesNavigation
	 */
	protected $categoriesNavigation;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	/**
	 *
	 * @var $attributeGroups
	 */
	protected $attributeGroups;

	/**
	 *
	 * @var $productAttributes
	 */
	protected $productAttributes;

	public function display($tpl = null)
	{
		$app      = Factory::getApplication();
		$session  = $app->getSession();
		$model    = $this->getModel();
		$state    = $model->getState();
		$category = EShopHelper::getCategory($state->id, true, true);

		if (!is_object($category))
		{
			// Requested category does not existed.
			$session->set('warning', Text::_('ESHOP_CATEGORY_DOES_NOT_EXIST'));
			$app->redirect(Route::_(EShopRoute::getViewRoute('categories')));
		}
		else
		{
			$document = Factory::getApplication()->getDocument();
			$baseUri  = Uri::root(true);
			$document->addStyleSheet($baseUri . '/media/com_eshop/assets/colorbox/colorbox.css');
			$document->addStyleSheet($baseUri . '/media/com_eshop/assets/css/labels.css');
			
			if (EShopHelper::getConfigValue('enable_canoncial_link', 0))
			{
				$currentUrl = Uri::getInstance()->toString();
				$categoryCanoncialLink = $category->category_canoncial_link;
					
				if ($categoryCanoncialLink != '' && $categoryCanoncialLink != $currentUrl)
				{
					$document->addHeadLink($categoryCanoncialLink, 'canonical');
				}
			}

			//Handle breadcrump
			$menu     = $app->getMenu();
			$menuItem = $menu->getActive();

			if ($menuItem && (isset($menuItem->query['view']) && ($menuItem->query['view'] == 'frontpage' || $menuItem->query['view'] == 'categories' || $menuItem->query['view'] == 'category')))
			{
				$parentId = isset($menuItem->query['id']) ? (int) $menuItem->query['id'] : '0';
				if ($category->id)
				{
					$pathway = $app->getPathway();
					$paths   = EShopHelper::getCategoriesBreadcrumb($category->id, $parentId);

					for ($i = count($paths) - 1; $i >= 0; $i--)
					{
						$path    = $paths[$i];
						$pathUrl = EShopRoute::getCategoryRoute($path->id);
						$pathway->addItem($path->category_name, $pathUrl);
					}
				}
			}

			// Update hits for category
			EShopHelper::updateHits($category->id, 'categories');

			// Set title of the page
			$categoryPageTitle = $category->category_page_title != '' ? $category->category_page_title : $category->category_name;
			$this->setPageTitle($categoryPageTitle);

			// Set metakey and metadesc
			$metaKey  = $category->meta_key;
			$metaDesc = $category->meta_desc;

			if ($metaKey)
			{
				$document->setMetaData('keywords', $metaKey);
			}

			if ($metaDesc)
			{
				$document->setMetaData('description', $metaDesc);
			}

			$products = $model->getData();
			EShopHelper::prepareCustomFieldsData($products, true);
			$pagination = $model->getPagination();

			//Get subcategories
			JLoader::register('EShopModelCategories', JPATH_ROOT . '/components/com_eshop/models/categories.php');
			$subCategories = EshopRADModel::getInstance('Categories', 'EShopModel', ['remember_states' => false])
				->limitstart(0)
				->limit(0)
				->id($state->id)
				->getData();

			//Sort options
			$sortOptions = EShopHelper::getConfigValue('sort_options');
			$sortOptions = explode(',', $sortOptions);

			$allSortOptions = [
				'a.ordering-ASC'                => Text::_('ESHOP_SORTING_PRODUCT_ORDERING_ASC'),
				'a.ordering-DESC'               => Text::_('ESHOP_SORTING_PRODUCT_ORDERING_DESC'),
				'b.product_name-ASC'            => Text::_('ESHOP_SORTING_PRODUCT_NAME_ASC'),
				'b.product_name-DESC'           => Text::_('ESHOP_SORTING_PRODUCT_NAME_DESC'),
				'a.product_sku-ASC'             => Text::_('ESHOP_SORTING_PRODUCT_SKU_ASC'),
				'a.product_sku-DESC'            => Text::_('ESHOP_SORTING_PRODUCT_SKU_DESC'),
				'a.product_price-ASC'           => Text::_('ESHOP_SORTING_PRODUCT_PRICE_ASC'),
				'a.product_price-DESC'          => Text::_('ESHOP_SORTING_PRODUCT_PRICE_DESC'),
				'a.product_length-ASC'          => Text::_('ESHOP_SORTING_PRODUCT_LENGTH_ASC'),
				'a.product_length-DESC'         => Text::_('ESHOP_SORTING_PRODUCT_LENGTH_DESC'),
				'a.product_width-ASC'           => Text::_('ESHOP_SORTING_PRODUCT_WIDTH_ASC'),
				'a.product_width-DESC'          => Text::_('ESHOP_SORTING_PRODUCT_WIDTH_DESC'),
				'a.product_height-ASC'          => Text::_('ESHOP_SORTING_PRODUCT_HEIGHT_ASC'),
				'a.product_height-DESC'         => Text::_('ESHOP_SORTING_PRODUCT_HEIGHT_DESC'),
				'a.product_weight-ASC'          => Text::_('ESHOP_SORTING_PRODUCT_WEIGHT_ASC'),
				'a.product_weight-DESC'         => Text::_('ESHOP_SORTING_PRODUCT_WEIGHT_DESC'),
				'a.product_quantity-ASC'        => Text::_('ESHOP_SORTING_PRODUCT_QUANTITY_ASC'),
				'a.product_quantity-DESC'       => Text::_('ESHOP_SORTING_PRODUCT_QUANTITY_DESC'),
				'a.product_available_date-ASC'  => Text::_('ESHOP_SORTING_PRODUCT_AVAILABLE_DATE_ASC'),
				'a.product_available_date-DESC' => Text::_('ESHOP_SORTING_PRODUCT_AVAILABLE_DATE_DESC'),
				'b.product_short_desc-ASC'      => Text::_('ESHOP_SORTING_PRODUCT_SHORT_DESC_ASC'),
				'b.product_short_desc-DESC'     => Text::_('ESHOP_SORTING_PRODUCT_SHORT_DESC_DESC'),
				'b.product_desc-ASC'            => Text::_('ESHOP_SORTING_PRODUCT_DESC_ASC'),
				'b.product_desc-DESC'           => Text::_('ESHOP_SORTING_PRODUCT_DESC_DESC'),
				'product_rates-ASC'             => Text::_('ESHOP_SORTING_PRODUCT_RATES_ASC'),
				'product_rates-DESC'            => Text::_('ESHOP_SORTING_PRODUCT_RATES_DESC'),
				'product_reviews-ASC'           => Text::_('ESHOP_SORTING_PRODUCT_REVIEWS_ASC'),
				'product_reviews-DESC'          => Text::_('ESHOP_SORTING_PRODUCT_REVIEWS_DESC'),
				'a.id-DESC'                     => Text::_('ESHOP_SORTING_PRODUCT_LATEST'),
				'a.id-ASC'                      => Text::_('ESHOP_SORTING_PRODUCT_OLDEST'),
				'product_best_sellers-DESC'     => Text::_('ESHOP_SORTING_PRODUCT_BEST_SELLERS'),
				'RAND()'                        => Text::_('ESHOP_SORTING_PRODUCT_RANDOMLY'),
			];

			$options = [];

			foreach ($allSortOptions as $value => $text)
			{
				if (in_array($value, $sortOptions))
				{
					$options[] = HTMLHelper::_('select.option', $value, $text);
				}
			}

			$selectedSortOptions = $state->sort_options ?: EShopHelper::getDefaultSortingCategory($category);

			if (count($options) > 1)
			{
				$this->sort_options = HTMLHelper::_(
					'select.genericlist',
					$options,
					'sort_options',
					'class="input-xlarge form-select" onchange="this.form.submit();" ',
					'value',
					'text',
					$selectedSortOptions
				);
			}
			else
			{
				$this->sort_options = '';
			}

			$app->setUserState('sort_options', $selectedSortOptions);
			$app->setUserState('from_view', 'category');

			// Store session for Continue Shopping Url
			$session->set('continue_shopping_url', Uri::getInstance()->toString());

			if ($state->sort_options)
			{
				$pagination->setAdditionalUrlParam('sort_options', $state->sort_options);
			}

			$tax                       = new EShopTax(EShopHelper::getConfig());
			$currency                  = EShopCurrency::getInstance();
			$category->category_desc   = HTMLHelper::_('content.prepare', $category->category_desc);
			$this->category            = $category;
			$this->subCategories       = $subCategories;
			$this->subCategoriesPerRow = EShopHelper::getConfigValue('items_per_row', 3);
			$this->products            = $products;
			$this->pagination          = $pagination;
			$this->tax                 = $tax;
			$this->currency            = $currency;

			//Added by tuanpn, to use share common layout
			$productsPerRow = $category->products_per_row;

			if (!$productsPerRow)
			{
				$productsPerRow = EShopHelper::getConfigValue('items_per_row', 3);
			}

			if ($pagination->limitstart)
			{
				$this->actionUrl = Route::_(EShopRoute::getCategoryRoute($category->id) . '&limitstart=' . $pagination->limitstart);
			}
			else
			{
				$this->actionUrl = Route::_(EShopRoute::getCategoryRoute($category->id));
			}

			$langCode = Factory::getLanguage()->getTag();
			$productIds = [];
			$attributeGroupIds = [];

			$attributeGroups   = EShopHelper::getAttributeGroups($langCode);

			foreach ($products as $product)
			{
				$productIds[]                                               = $product->id;
				EShopHelper::$productsAlias[$langCode . '.' . $product->id] = strlen($product->product_alias ?? '') > 0 ? $product->product_alias : $product->product_name;
			}

			foreach ($attributeGroups as $attributeGroup)
			{
				$attributeGroupIds[] = $attributeGroup->id;
			}

			if (EShopHelper::getConfigValue('show_product_attributes', 0) || EShopHelper::getConfigValue('table_show_product_attributes', 0))
			{
				$productsAttributes = EShopHelper::getProductsAttributes($productIds, $attributeGroupIds, Factory::getLanguage()->getTag());
			}
			else
			{
				$productsAttributes = [];
			}

			$productAttributes = [];

			foreach ($products as $product)
			{
				$productId             = $product->id;
				$productAttributesTemp = [];

				for ($i = 0; $n = count($attributeGroups), $i < $n; $i++)
				{
					$productAttributesTemp[] = $productsAttributes[$productId][$attributeGroups[$i]->id] ?? [];
				}

				$productAttributes[$productId] = $productAttributesTemp;
			}

			$this->productsPerRow       = $productsPerRow;
			$this->categoriesNavigation = EShopHelper::getCategoriesNavigation($category->id);
			$this->bootstrapHelper      = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
			$this->attributeGroups      = $attributeGroups;
			$this->productAttributes    = $productAttributes;

			parent::display($tpl);
		}
	}
}