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

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

class plgContentEshopProduct extends CMSPlugin
{

	public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
	{
		if (file_exists(JPATH_ROOT . '/components/com_eshop/eshop.php'))
		{
			if (!Factory::getApplication()->isClient('site'))
			{
				return;
			}

			if (strpos($article->text, 'eshopproduct') === false)
			{
				return true;
			}

			$regex         = "#{eshopproduct (\d+)}#s";
			$article->text = preg_replace_callback($regex, [&$this, 'displayProduct'], $article->text);
		}

		return true;
	}

	/**
	 * Replace callback function
	 *
	 * @param   array  $matches
	 */
	public function displayProduct($matches)
	{
		// Register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

		$app      = Factory::getApplication();
		$document = $app->getDocument();
		$currency = EShopCurrency::getInstance();
		$config   = EShopHelper::getConfig();
		$tax      = new EShopTax($config);
		$language = Factory::getLanguage();
		$tag      = $language->getTag();
		if (!$tag)
		{
			$tag = 'en-GB';
		}
		$language->load('com_eshop', JPATH_ROOT, $tag);

		$rootUri = Uri::root(true);

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

		// Load custom CSS file
		if (is_file(JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/css/custom.css'))
		{
			$document->addStyleSheet($rootUri . '/components/com_eshop/themes/' . $theme . '/css/custom.css');
		}
		elseif (is_file(JPATH_ROOT . '/components/com_eshop/themes/default/css/custom.css'))
		{
			$document->addStyleSheet($rootUri . '/components/com_eshop/themes/default/css/custom.css');
		}

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

		$productId = $matches[1];

		//echo $productId;
		$viewConfig['name']          = 'product';
		$viewConfig['base_path']     = JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/views/product';
		$viewConfig['template_path'] = JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/views/product';
		$viewConfig['layout']        = $this->params->get('layout', 'default');
		$view                        = new HtmlView($viewConfig);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
			'a.*, b.product_name, b.product_alias, b.product_desc, b.product_short_desc, b.product_page_title, b.product_page_heading, b.meta_key, b.meta_desc, b.tab1_title, b.tab1_content, b.tab2_title, b.tab2_content, b.tab3_title, b.tab3_content, b.tab4_title, b.tab4_content, b.tab5_title, b.tab5_content'
		)
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.id = ' . intval($productId))
			->where('b.language = "' . $tag . '"');
		$db->setQuery($query);
		$item = $db->loadObject();
		if (!is_object($item))
		{
			return '';
		}
		else
		{
			//Set session for viewed products
			$session          = Factory::getApplication()->getSession();
			$viewedProductIds = $session->get('viewed_product_ids');
			if (empty($viewedProductIds))
			{
				$viewedProductIds = [];
			}
			if (!in_array($productId, $viewedProductIds))
			{
				$viewedProductIds[] = $productId;
			}
			$session->set('viewed_product_ids', $viewedProductIds);
			// Update hits for product
			EShopHelper::updateHits($productId, 'products');

			$additionalImageSizeFunction = EShopHelper::getConfigValue('additional_image_size_function', 'resizeImage');
			$thumbImageSizeFunction      = EShopHelper::getConfigValue('thumb_image_size_function', 'resizeImage');
			$popupImageSizeFunction      = EShopHelper::getConfigValue('popup_image_size_function', 'resizeImage');
			$relatedImageSizeFunction    = EShopHelper::getConfigValue('related_image_size_function', 'resizeImage');

			// Main image resize
			if ($item->product_image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $item->product_image))
			{
				$smallThumbImage = call_user_func_array(['EShopHelper', $additionalImageSizeFunction],
					[
						$item->product_image,
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_additional_width'),
						EShopHelper::getConfigValue('image_additional_height'),
					]);
				$thumbImage      = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
					[
						$item->product_image,
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_thumb_width'),
						EShopHelper::getConfigValue('image_thumb_height'),
					]);
				$popupImage      = call_user_func_array(['EShopHelper', $popupImageSizeFunction],
					[
						$item->product_image,
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_popup_width'),
						EShopHelper::getConfigValue('image_popup_height'),
					]);
			}
			else
			{
				$smallThumbImage = call_user_func_array(['EShopHelper', $additionalImageSizeFunction],
					[
						'no-image.png',
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_additional_width'),
						EShopHelper::getConfigValue('image_additional_height'),
					]);
				$thumbImage      = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
					[
						'no-image.png',
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_thumb_width'),
						EShopHelper::getConfigValue('image_thumb_height'),
					]);
				$popupImage      = call_user_func_array(['EShopHelper', $popupImageSizeFunction],
					[
						'no-image.png',
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_popup_width'),
						EShopHelper::getConfigValue('image_popup_height'),
					]);
			}

			if ($additionalImageSizeFunction == 'notResizeImage')
			{
				$item->small_thumb_image = Uri::base(true) . '/media/com_eshop/products/' . $smallThumbImage;
			}
			else
			{
				$item->small_thumb_image = Uri::base(true) . '/media/com_eshop/products/resized/' . $smallThumbImage;
			}

			if ($thumbImageSizeFunction == 'notResizeImage')
			{
				$item->thumb_image = Uri::base(true) . '/media/com_eshop/products/' . $thumbImage;
			}
			else
			{
				$item->thumb_image = Uri::base(true) . '/media/com_eshop/products/resized/' . $thumbImage;
			}

			if ($popupImageSizeFunction == 'notResizeImage')
			{
				$item->popup_image = Uri::base(true) . '/media/com_eshop/products/' . $popupImage;
			}
			else
			{
				$item->popup_image = Uri::base(true) . '/media/com_eshop/products/resized/' . $popupImage;
			}

			// Product availability
			$productInventory = EShopHelper::getProductInventory($productId);

			if ($item->product_quantity <= 0)
			{
				$availability = EShopHelper::getStockStatusName($productInventory['product_stock_status_id'], $tag);
			}
			elseif ($productInventory['product_stock_display'])
			{
				$availability = $item->product_quantity;
			}
			else
			{
				$availability = Text::_('ESHOP_IN_STOCK');
			}
			$item->availability = $availability;
			// Product tags
			$productTags = EShopHelper::getProductTags($item->id);

			//Product attachments
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__eshop_productattachments')
				->where('product_id = ' . intval($item->id))
				->order('ordering');
			$db->setQuery($query);
			$productAttachments = $db->loadObjectList();
			$item->product_desc = HTMLHelper::_('content.prepare', $item->product_desc);

			if ($item->tab1_title != '' && $item->tab1_content != '')
			{
				$item->tab1_content = HTMLHelper::_('content.prepare', $item->tab1_content);
			}

			if ($item->tab2_title != '' && $item->tab2_content != '')
			{
				$item->tab2_content = HTMLHelper::_('content.prepare', $item->tab2_content);
			}

			if ($item->tab3_title != '' && $item->tab3_content != '')
			{
				$item->tab3_content = HTMLHelper::_('content.prepare', $item->tab3_content);
			}

			if ($item->tab4_title != '' && $item->tab4_content != '')
			{
				$item->tab4_content = HTMLHelper::_('content.prepare', $item->tab4_content);
			}

			if ($item->tab5_title != '' && $item->tab5_content != '')
			{
				$item->tab5_content = HTMLHelper::_('content.prepare', $item->tab5_content);
			}

			// Get information related to this current product
			$productImages = EShopHelper::getProductImages($productId);
			// Additional images resize
			for ($i = 0; $n = count($productImages), $i < $n; $i++)
			{
				if ($productImages[$i]->image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $productImages[$i]->image))
				{
					$smallThumbImage = call_user_func_array(['EShopHelper', $additionalImageSizeFunction],
						[
							$productImages[$i]->image,
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_additional_width'),
							EShopHelper::getConfigValue('image_additional_height'),
						]);
					$thumbImage      = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
						[
							$productImages[$i]->image,
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_thumb_width'),
							EShopHelper::getConfigValue('image_thumb_height'),
						]);
					$popupImage      = call_user_func_array(['EShopHelper', $popupImageSizeFunction],
						[
							$productImages[$i]->image,
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_popup_width'),
							EShopHelper::getConfigValue('image_popup_height'),
						]);
				}
				else
				{
					$smallThumbImage = call_user_func_array(['EShopHelper', $additionalImageSizeFunction],
						[
							'no-image.png',
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_additional_width'),
							EShopHelper::getConfigValue('image_additional_height'),
						]);
					$thumbImage      = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
						[
							'no-image.png',
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_thumb_width'),
							EShopHelper::getConfigValue('image_thumb_height'),
						]);
					$popupImage      = call_user_func_array(['EShopHelper', $popupImageSizeFunction],
						[
							'no-image.png',
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_popup_width'),
							EShopHelper::getConfigValue('image_popup_height'),
						]);
				}

				if ($additionalImageSizeFunction == 'notResizeImage')
				{
					$productImages[$i]->small_thumb_image = Uri::base(true) . '/media/com_eshop/products/' . $smallThumbImage;
				}
				else
				{
					$productImages[$i]->small_thumb_image = Uri::base(true) . '/media/com_eshop/products/resized/' . $smallThumbImage;
				}

				if ($thumbImageSizeFunction == 'notResizeImage')
				{
					$productImages[$i]->thumb_image = Uri::base(true) . '/media/com_eshop/products/' . $thumbImage;
				}
				else
				{
					$productImages[$i]->thumb_image = Uri::base(true) . '/media/com_eshop/products/resized/' . $thumbImage;
				}

				if ($popupImageSizeFunction == 'notResizeImage')
				{
					$productImages[$i]->popup_image = Uri::base(true) . '/media/com_eshop/products/' . $popupImage;
				}
				else
				{
					$productImages[$i]->popup_image = Uri::base(true) . '/media/com_eshop/products/resized/' . $popupImage;
				}
			}
			$discountPrices    = EShopHelper::getDiscountPrices($productId);
			$productOptions    = EShopHelper::getProductOptions($productId, $tag);
			$hasSpecification  = false;
			$attributeGroups   = EShopHelper::getAttributeGroups($tag);
			$productAttributes = [];
			for ($i = 0; $n = count($attributeGroups), $i < $n; $i++)
			{
				$productAttributes[] = EShopHelper::getAttributes($productId, $attributeGroups[$i]->id, $tag);
				if (count($productAttributes[$i]))
				{
					$hasSpecification = true;
				}
			}
			$productRelations = EShopHelper::getProductRelations($productId, $tag);
			// Related products images resize
			for ($i = 0; $n = count($productRelations), $i < $n; $i++)
			{
				if ($productRelations[$i]->product_image && is_file(
						JPATH_ROOT . '/media/com_eshop/products/' . $productRelations[$i]->product_image
					))
				{
					$thumbImage = call_user_func_array(['EShopHelper', $relatedImageSizeFunction],
						[
							$productRelations[$i]->product_image,
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_related_width'),
							EShopHelper::getConfigValue('image_related_height'),
						]);
				}
				else
				{
					$thumbImage = call_user_func_array(['EShopHelper', $relatedImageSizeFunction],
						[
							'no-image.png',
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_related_width'),
							EShopHelper::getConfigValue('image_related_height'),
						]);
				}

				if ($relatedImageSizeFunction == 'notResizeImage')
				{
					$productRelations[$i]->thumb_image = Uri::base(true) . '/media/com_eshop/products/' . $thumbImage;
				}
				else
				{
					$productRelations[$i]->thumb_image = Uri::base(true) . '/media/com_eshop/products/resized/' . $thumbImage;
				}
			}
			if (EShopHelper::getConfigValue('allow_reviews'))
			{
				$productReviews       = EShopHelper::getProductReviews($productId);
				$view->productReviews = $productReviews;
			}
			$tax = new EShopTax(EShopHelper::getConfig());

			if (EShopHelper::getConfigValue('social_enable'))
			{
				EShopHelper::loadShareScripts($item);
			}

			//Custom fields handle
			if (EShopHelper::getConfigValue('product_custom_fields'))
			{
				EShopHelper::prepareCustomFieldsData([$item]);
			}

			$view->currency           = $currency;
			$view->item               = $item;
			$view->productImages      = $productImages;
			$view->discountPrices     = $discountPrices;
			$view->productOptions     = $productOptions;
			$view->hasSpecification   = $hasSpecification;
			$view->attributeGroups    = $attributeGroups;
			$view->productAttributes  = $productAttributes;
			$view->productRelations   = $productRelations;
			$view->productAttachments = $productAttachments;
			$view->productTags        = $productTags;
			$manufacturer             = EShopHelper::getProductManufacturer($productId, $tag);
			$view->manufacturer       = $manufacturer;
			$view->tax                = $tax;

			// Preparing rating html
			$ratingHtml = '<b>' . Text::_('ESHOP_BAD') . '</b>';
			for ($i = 1; $i <= 5; $i++)
			{
				$ratingHtml .= '<input type="radio" name="rating" value="' . $i . '" style="width: 25px;" />';
			}
			$ratingHtml               .= '<b>' . Text::_('ESHOP_EXCELLENT') . '</b>';
			$view->ratingHtml         = $ratingHtml;
			$productsNavigation       = ['', ''];
			$view->productsNavigation = $productsNavigation;
			$labels                   = EShopHelper::getProductLabels($item->id);
			$view->labels             = $labels;

			//Captcha
			$showCaptcha = 0;
			if (EShopHelper::getConfigValue('enable_reviews_captcha'))
			{
				$captchaPlugin = $app->getParams()->get('captcha', $app->get('captcha'));

				if ($captchaPlugin == 'recaptcha')
				{
					$showCaptcha   = 1;
					$view->captcha = Captcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required');
				}
			}
			$view->showCaptcha     = $showCaptcha;
			$bootstrapHelper       = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
			$view->bootstrapHelper = $bootstrapHelper;
			ob_start();
			$view->display();
			$text = ob_get_contents();
			ob_end_clean();

			return $text;
		}
	}
}
