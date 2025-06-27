<?php

/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

class plgContentEshopCategory extends CMSPlugin
{

	public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
	{
		if (file_exists(JPATH_ROOT . '/components/com_eshop/eshop.php'))
		{
			$app = Factory::getApplication();
			if ($app->getName() != 'site')
			{
				return;
			}
			if (strpos($article->text, 'eshopcategory') === false)
			{
				return true;
			}
			$regex         = "#{eshopcategory (\d+)}#s";
			$article->text = preg_replace_callback($regex, [&$this, 'displayProducts'], $article->text);
		}

		return true;
	}

	/**
	 * Replace callback function
	 *
	 * @param   array  $matches
	 */
	public function displayProducts($matches)
	{
		// Register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

		$rootUri = Uri::root(true);

		$categoryId = $matches[1];
		$document   = Factory::getApplication()->getDocument();
		$currency   = EShopCurrency::getInstance();
		$config     = EShopHelper::getConfig();
		$tax        = new EShopTax($config);
		$category   = EShopHelper::getCategory($categoryId);
		//Added by tuanpn, to use share common layout
		$productsPerRow = $category->products_per_row;
		if (!$productsPerRow)
		{
			$productsPerRow = EShopHelper::getConfigValue('items_per_row', 3);
		}
		$categoriesNavigation = EShopHelper::getCategoriesNavigation($category->id);
		$language             = Factory::getLanguage();
		$tag                  = $language->getTag();
		if (!$tag)
		{
			$tag = 'en-GB';
		}
		$language->load('com_eshop', JPATH_ROOT, $tag);
		//Load javascript and css
		$theme = EShopHelper::getConfigValue('theme');

		if (is_file(JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/css/style.css'))
		{
			$document->addStyleSheet($rootUri . '/components/com_eshop/themes/' . $theme . '/css/style.css');
		}
		else
		{
			$document->addStyleSheet($rootUri . '/components/com_eshop/themes/default/css/style.css');
		}

		$document->addStyleSheet($rootUri . '/components/com_eshop/assets/colorbox/colorbox.css');

		//Load JQuery Framework
		if (EShopHelper::getConfigValue('load_jquery_framework', 1))
		{
			HTMLHelper::_('jquery.framework');
		}

		// Load Bootstrap CSS
		if (EShopHelper::getConfigValue('load_bootstrap_css', 1))
		{
			EShopHelper::loadBootstrapCss();
		}

		$document->addScript($rootUri . '/components/com_eshop/assets/js/noconflict.js')
			->addScript($rootUri . '/components/com_eshop/assets/js/eshop.js');

		$Itemid = Factory::getApplication()->input->getInt('Itemid');

		if (!$Itemid)
		{
			$Itemid = EShopHelper::getItemid();
		}

		$eshopModel    = new EShopModel();
		$categoryModel = $eshopModel->getModel('category');
		$products      = $categoryModel->reset()->id($categoryId)->limitstart(0)->limit($this->params->get('number_product', 15))->getData();

		$category       = EShopHelper::getCategory($categoryId, true, true);
		$categoryLayout = '';

		$bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
		$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
		$span4Class      = $bootstrapHelper->getClassMapping('span4');
		$span8Class      = $bootstrapHelper->getClassMapping('span8');
		$span12Class     = $bootstrapHelper->getClassMapping('span12');
		$imgPolaroid     = $bootstrapHelper->getClassMapping('img-polaroid');

		if (EShopHelper::getConfigValue('show_category_image') || EShopHelper::getConfigValue('show_category_desc'))
		{
			$categoryLayout .= '<div class="' . $rowFluidClass . '">';

			if (EShopHelper::getConfigValue('show_category_image'))
			{
				$categoryLayout .= '<div class="' . $span4Class . '"><img class="' . $imgPolaroid . '" src="' . $category->image . '" title="' . ($category->category_page_title != '' ? $category->category_page_title : $category->category_name) . '" alt="' . ($category->category_page_title != '' ? $category->category_page_title : $category->category_name) . '" /></div>';
			}

			if (EShopHelper::getConfigValue('show_category_desc'))
			{
				$categoryLayout .= '<div class="' . (EShopHelper::getConfigValue(
						'show_category_image'
					) ? $span8Class : $span12Class) . '">' . $category->category_desc . '</div>';
			}

			$categoryLayout .= '</div><hr />';
		}

		$productsListLayout = EShopHtmlHelper::loadCommonLayout(
			'common/products.php',
			[
				'products'             => $products,
				'productsPerRow'       => $productsPerRow,
				'categoriesNavigation' => $categoriesNavigation,
				'category'             => $category,
				'currency'             => $currency,
				'config'               => $config,
				'tax'                  => $tax,
				'Itemid'               => $Itemid,
				'catId'                => $categoryId,
				'showSortOptions'      => false,
				'bootstrapHelper'      => $bootstrapHelper,
			]
		);

		return $categoryLayout . $productsListLayout;
	}
}