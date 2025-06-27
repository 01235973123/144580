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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 *
 * @package    Joomla
 * @subpackage EShop
 * @since      1.5
 */
class EShopViewFilter extends EShopView
{
	/**
	 *
	 * @var $products
	 */
	protected $products;

	/**
	 *
	 * @var $category
	 */
	protected $category;

	/**
	 *
	 * @var $sort_options
	 */
	protected $sort_options;

	/**
	 *
	 * @var $pagination
	 */
	protected $pagination;

	/**
	 *
	 * @var $actionUrl
	 */
	protected $actionUrl;

	/**
	 *
	 * @var $categories
	 */
	protected $categories;

	/**
	 *
	 * @var $manufacturers
	 */
	protected $manufacturers;

	/**
	 *
	 * @var $attributes
	 */
	protected $attributes;

	/**
	 *
	 * @var $options
	 */
	protected $options;

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
	 * @var $filterData
	 */
	protected $filterData;

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
		require_once JPATH_ROOT . '/components/com_eshop/helpers/filter.php';

		$app      = Factory::getApplication();
		$document = $app->getDocument();
		$rootUri  = Uri::root(true);
		$document->addStyleSheet($rootUri . '/media/com_eshop/assets/colorbox/colorbox.css')
			->addStyleSheet($rootUri . '/media/com_eshop/assets/css/labels.css');

		$filterData = EShopFilter::getFilterData();


		$session = $app->getSession();
		$model   = $this->getModel();
		$state   = $model->getState();

		$products = $model->getData();
		EShopHelper::prepareCustomFieldsData($products, true);

		$this->products = $products;

		$productIds = [];

		foreach ($products as $product)
		{
			$productIds[] = $product->id;
		}

		$filterData['product_ids'] = $productIds;

		/* @var JPagination $pagination */
		$pagination      = $model->getPagination();
		$actionUrl       = Route::_(EShopRoute::getViewRoute('filter'));
		$actionUrlParams = [];

		foreach ($filterData as $key => $values)
		{
			if ($key == 'product_ids')
			{
				continue;
			}
			if (is_array($values))
			{
				$index = 0;

				foreach ($values as $value)
				{
					$pagination->setAdditionalUrlParam($key . "[$index]", $value);
					$actionUrlParams[] = $key . '[' . $index . ']=' . $value;
					$index++;
				}
			}
			elseif (in_array($key, ['min_weight', 'max_weight', 'min_length', 'max_length', 'min_width', 'max_width', 'min_height', 'max_height']))
			{
				$pagination->setAdditionalUrlParam($key, $this->input->getString($key));
				$actionUrlParams[] = $key . '=' . $this->input->getString($key);
			}
			else
			{
				$pagination->setAdditionalUrlParam($key, $values);
				$actionUrlParams[] = $key . '=' . $values;
			}
		}

		if (count($actionUrlParams))
		{
			$actionUrl .= '?' . implode('&', $actionUrlParams);
		}

		if (!empty($filterData['category_id']))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select(' a.id, a.category_parent_id, b.category_name')
				->from('#__eshop_categories AS a')
				->innerJoin('#__eshop_categorydetails AS b ON a.id = b.category_id')
				->where('a.id = ' . $filterData['category_id']);

			if (Multilanguage::isEnabled())
			{
				$query->where('b.language = ' . $db->quote(Factory::getLanguage()->getTag()));
			}

			$db->setQuery($query);
			$category = $db->loadObject();

			$this->category = $category;
		}
		else
		{
			$this->category = null;
		}

		//Sort options
		$sortOptions = EShopHelper::getConfigValue('sort_options');
		$sortOptions = explode(',', $sortOptions);
		$sortValues  = [
			'a.ordering-ASC',
			'a.ordering-DESC',
			'b.product_name-ASC',
			'b.product_name-DESC',
			'a.product_sku-ASC',
			'a.product_sku-DESC',
			'a.product_price-ASC',
			'a.product_price-DESC',
			'a.product_length-ASC',
			'a.product_length-DESC',
			'a.product_width-ASC',
			'a.product_width-DESC',
			'a.product_height-ASC',
			'a.product_height-DESC',
			'a.product_weight-ASC',
			'a.product_weight-DESC',
			'a.product_quantity-ASC',
			'a.product_quantity-DESC',
			'a.product_available_date-ASC',
			'a.product_available_date-DESC',
			'b.product_short_desc-ASC',
			'b.product_short_desc-DESC',
			'b.product_desc-ASC',
			'b.product_desc-DESC',
			'product_rates-ASC',
			'product_rates-DESC',
			'product_reviews-ASC',
			'product_reviews-DESC',
			'a.id-DESC',
			'a.id-ASC',
			'product_best_sellers-DESC',
		];
		$sortTexts   = [
			Text::_('ESHOP_SORTING_PRODUCT_ORDERING_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_ORDERING_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_NAME_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_NAME_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_SKU_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_SKU_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_PRICE_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_PRICE_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_LENGTH_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_LENGTH_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_WIDTH_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_WIDTH_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_HEIGHT_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_HEIGHT_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_WEIGHT_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_WEIGHT_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_QUANTITY_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_QUANTITY_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_AVAILABLE_DATE_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_AVAILABLE_DATE_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_SHORT_DESC_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_SHORT_DESC_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_DESC_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_DESC_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_RATES_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_RATES_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_REVIEWS_ASC'),
			Text::_('ESHOP_SORTING_PRODUCT_REVIEWS_DESC'),
			Text::_('ESHOP_SORTING_PRODUCT_LATEST'),
			Text::_('ESHOP_SORTING_PRODUCT_OLDEST'),
			Text::_('ESHOP_SORTING_PRODUCT_BEST_SELLERS'),
		];

		$options = [];

		for ($i = 0; $i < count($sortValues); $i++)
		{
			if (in_array($sortValues[$i], $sortOptions))
			{
				$options[] = HTMLHelper::_('select.option', $sortValues[$i], $sortTexts[$i]);
			}
		}

		if (count($options) > 1)
		{
			$this->sort_options = HTMLHelper::_(
				'select.genericlist',
				$options,
				'sort_options',
				'class="input-xlarge form-select" onchange="this.form.submit();" ',
				'value',
				'text',
				$state->sort_options ?: EShopHelper::getConfigValue('default_sorting')
			);
		}
		else
		{
			$this->sort_options = '';
		}

		$app->setUserState('sort_options', $state->sort_options ?: EShopHelper::getConfigValue('default_sorting'));
		$app->setUserState('from_view', 'filter');

		if ($state->sort_options)
		{
			$pagination->setAdditionalUrlParam('sort_options', $state->sort_options);
		}

		// Store session for Continue Shopping Url
		$session->set('continue_shopping_url', $actionUrl);

		$langCode = Factory::getLanguage()->getTag();

		$attributeGroups   = EShopHelper::getAttributeGroups($langCode);
		$attributeGroupIds = [];

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

		foreach ($products as $product)
		{
			EShopHelper::$productsAlias[$langCode . '.' . $product->id] = strlen(
				$product->product_alias ?? ''
			) > 0 ? $product->product_alias : $product->product_name;
		}

		$this->pagination        = $pagination;
		$this->actionUrl         = $actionUrl;
		$this->categories        = EShopFilter::getCategories($filterData);
		$this->manufacturers     = EShopFilter::getManufacturers($filterData);
		$this->attributes        = EShopFilter::getAttributes($filterData, true);
		$this->options           = EShopFilter::getOptions($filterData, true);
		$this->tax               = new EShopTax(EShopHelper::getConfig());
		$this->currency          = EShopCurrency::getInstance();
		$this->productsPerRow    = EShopHelper::getConfigValue('items_per_row', 3);
		$this->filterData        = $filterData;
		$this->bootstrapHelper   = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
		$this->attributeGroups   = $attributeGroups;
		$this->productAttributes = $productAttributes;

		$router = $app::getRouter();
		$router->setVar('format', 'html');

		parent::display($tpl);
	}
}