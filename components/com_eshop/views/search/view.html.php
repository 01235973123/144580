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
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewSearch extends EShopView
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
	 * @var $actionUrl
	 */
	protected $actionUrl;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	public function display($tpl = null)
	{
		$app     = Factory::getApplication();
		$keyword = $this->input->getString('keyword');
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$query->select('id')
			->from('#__eshop_tags')
			->where('tag_name = ' . $db->quote($keyword));
		$db->setQuery($query);
		$tagId = $db->loadResult();

		if ($tagId)
		{
			EShopHelper::updateHits($tagId, 'tags');
		}

		$model    = $this->getModel();
		$document = $app->getDocument();
		$params   = $app->getParams();
		$rootUri  = Uri::root(true);
		$document->addStyleSheet($rootUri . '/media/com_eshop/assets/colorbox/colorbox.css');
		$document->addStyleSheet($rootUri . '/media/com_eshop/assets/css/labels.css');

		$title = $params->get('page_title', '');

		if ($title == '')
		{
			$title = Text::_('ESHOP_SEARCH_RESULT');
		}

		$this->setPageTitle($title);

		//Sort options
		$sortOptions = EShopHelper::getConfigValue('sort_options');
		$sortOptions = explode(',', $sortOptions);
		$sortValues  = [
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
		];

		$sortTexts = [
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
				'class="input-large" onchange="this.form.submit();" ',
				'value',
				'text',
				$this->input->get('sort_options', '')
			);
		}
		else
		{
			$this->sort_options = '';
		}

		// Store session for Continue Shopping Url
		$app->getSession()->set('continue_shopping_url', Uri::getInstance()->toString());
		$this->products        = $model->getData();
		$this->pagination      = $model->getPagination();
		$this->tax             = new EShopTax(EShopHelper::getConfig());
		$this->currency        = EShopCurrency::getInstance();
		$this->productsPerRow  = EShopHelper::getConfigValue('items_per_row', 3);
		$this->actionUrl       = '';
		$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

		parent::display($tpl);
	}
}