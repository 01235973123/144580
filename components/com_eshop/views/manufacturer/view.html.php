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
class EShopViewManufacturer extends EShopView
{
	/**
	 *
	 * @var $sort_options
	 */
	protected $sort_options;

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
	 * @var $manufacturer
	 */
	protected $manufacturer;

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
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	public function display($tpl = null)
	{
		$app          = Factory::getApplication();
		$input        = $app->input;
		$model        = $this->getModel();
		$state        = $model->getState();
		$manufacturer = EShopHelper::getManufacturer($state->id, true, true);

		if (!is_object($manufacturer))
		{
			// Requested manufacturer does not existed.
			$app->getSession()->set('warning', Text::_('ESHOP_MANUFACTURER_DOES_NOT_EXIST'));
			$app->redirect(Route::_(EShopRoute::getViewRoute('categories')));
		}
		else
		{
			$products   = $model->getData();
			$pagination = $model->getPagination();
			$document   = $app->getDocument();
			$rootUri    = Uri::root(true);
			$document->addStyleSheet($rootUri . '/media/com_eshop/assets/colorbox/colorbox.css');
			$document->addStyleSheet($rootUri . '/media/com_eshop/assets/css/labels.css');

			// Update hits for manufacturer
			EShopHelper::updateHits($manufacturer->id, 'manufacturers');

			// Set title of the page
			$manufacturerPageTitle = $manufacturer->manufacturer_page_title != '' ? $manufacturer->manufacturer_page_title : $manufacturer->manufacturer_name;

			$this->setPageTitle($manufacturerPageTitle);

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
				'RAND()',
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
				Text::_('ESHOP_SORTING_PRODUCT_RANDOMLY'),
			];

			$options   = [];
			$options[] = HTMLHelper::_('select.option', 'a.id-DESC', Text::_('ESHOP_SORTING_DEFAULT'));

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
					$input->get('sort_options', '')
				);
			}
			else
			{
				$this->sort_options = '';
			}

			$app->setUserState('sort_options', $state->sort_options ?: EShopHelper::getConfigValue('default_sorting'));
			$app->setUserState('from_view', 'manufacturer');

			$app->getSession()->set('continue_shopping_url', Uri::getInstance()->toString());

			$tax                             = new EShopTax(EShopHelper::getConfig());
			$currency                        = EShopCurrency::getInstance();
			$this->products                  = $products;
			$this->pagination                = $pagination;
			$this->tax                       = $tax;
			$manufacturer->manufacturer_desc = HTMLHelper::_('content.prepare', $manufacturer->manufacturer_desc);
			$this->manufacturer              = $manufacturer;
			$this->currency                  = $currency;

			$this->actionUrl       = Route::_(EShopRoute::getManufacturerRoute($manufacturer->id));
			$this->productsPerRow  = EShopHelper::getConfigValue('items_per_row', 3);
			$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

			parent::display($tpl);
		}
	}
}