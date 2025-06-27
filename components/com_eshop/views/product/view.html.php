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

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
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
class EShopViewProduct extends EShopView
{
	/**
	 *
	 * @var $currency
	 */
	protected $currency;

	/**
	 *
	 * @var $product_available_date
	 */
	protected $product_available_date;

	/**
	 *
	 * @var $productTags
	 */
	protected $productTags;

	/**
	 *
	 * @var $productAttachments
	 */
	protected $productAttachments;

	/**
	 *
	 * @var $productReviews
	 */
	protected $productReviews;

	/**
	 *
	 * @var $item
	 */
	protected $item;

	/**
	 *
	 * @var $plugins
	 */
	protected $plugins;

	/**
	 *
	 * @var $productImages
	 */
	protected $productImages;

	/**
	 *
	 * @var $discountPrices
	 */
	protected $discountPrices;

	/**
	 *
	 * @var $productOptions
	 */
	protected $productOptions;

	/**
	 *
	 * @var $hasSpecification
	 */
	protected $hasSpecification;

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

	/**
	 *
	 * @var $productRelations
	 */
	protected $productRelations;

	/**
	 *
	 * @var $manufacturer
	 */
	protected $manufacturer;

	/**
	 *
	 * @var $tax
	 */
	protected $tax;

	/**
	 *
	 * @var $ratingHtml
	 */
	protected $ratingHtml;

	/**
	 *
	 * @var $selectRating
	 */
	protected $selectRating;

	/**
	 *
	 * @var $productsNavigation
	 */
	protected $productsNavigation;

	/**
	 *
	 * @var $labels
	 */
	protected $labels;

	/**
	 *
	 * @var $showCaptcha
	 */
	protected $showCaptcha;

	/**
	 *
	 * @var $captcha
	 */
	protected $captcha;

	/**
	 *
	 * @var $captchaPlugin
	 */
	protected $captchaPlugin;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	/**
	 * Display function
	 * @see JView::display()
	 */
	public function display($tpl = null)
	{
		$app            = Factory::getApplication();
		$session        = $app->getSession();
		$document       = $app->getDocument();
		$item           = $this->get('Data');
		$this->currency = EShopCurrency::getInstance();

		if (!is_object($item))
		{
			// Requested product does not existed.
			$session->set('warning', Text::_('ESHOP_PRODUCT_DOES_NOT_EXIST'));
			$app->redirect(Route::_(EShopRoute::getViewRoute('categories')));
		}
		else
		{
			$rootUri = Uri::root(true);
			
			$document->addStyleSheet($rootUri . '/media/com_eshop/assets/colorbox/colorbox.css');
			
			if (EShopHelper::getConfigValue('enable_canoncial_link', 0))
			{
				$currentUrl = Uri::getInstance()->toString();
				$productCanoncialLink = $item->product_canoncial_link;
				
				if ($productCanoncialLink != '' && $productCanoncialLink != $currentUrl)
				{
					$document->addHeadLink($productCanoncialLink, 'canonical');
				}
			}

			if (EShopHelper::getConfigValue('view_image') == 'zoom')
			{
				$document->addStyleSheet($rootUri . '/media/com_eshop/assets/css/jquery-picZoomer.css');
			}

			$document->addStyleSheet($rootUri . '/media/com_eshop/assets/css/labels.css');
			$document->addStyleSheet($rootUri . '/media/com_eshop/assets/rating/dist/star-rating.css');

			$productId = $this->input->getInt('id');
			//Set session for viewed products
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
			$session->set('continue_shopping_url', Uri::getInstance()->toString());

			// Handle breadcrumb
			$db         = Factory::getDbo();
			$categoryId = $this->input->getInt('catid');

			if (!$categoryId)
			{
				$query = $db->getQuery(true);
				$query->select('a.id')
					->from('#__eshop_categories AS a')
					->innerJoin('#__eshop_productcategories AS b ON a.id = b.category_id')
					->where('b.product_id = ' . (int) $productId);
				$db->setQuery($query);
				$categoryId = (int) $db->loadResult();
			}

			$menuItem = $app->getMenu()->getActive();

			if ($menuItem && (isset($menuItem->query['view']) && ($menuItem->query['view'] == 'frontpage' || $menuItem->query['view'] == 'categories' || $menuItem->query['view'] == 'category')))
			{
				$parentId = isset($menuItem->query['id']) ? (int) $menuItem->query['id'] : '0';

				if ($categoryId)
				{
					$pathway = $app->getPathway();
					$paths   = EShopHelper::getCategoriesBreadcrumb($categoryId, $parentId);

					for ($i = count($paths) - 1; $i >= 0; $i--)
					{
						$category = $paths[$i];
						$pathUrl  = EShopRoute::getCategoryRoute($category->id);
						$pathway->addItem($category->category_name, $pathUrl);
					}

					$pathway->addItem($item->product_name);
				}
			}

			// Update hits for product
			EShopHelper::updateHits($productId, 'products');

			// Set title of the page
			$productPageTitle = $item->product_page_title != '' ? $item->product_page_title : $item->product_name;
			$this->setPageTitle($productPageTitle);

			$additionalImageSizeFunction = EShopHelper::getConfigValue('additional_image_size_function', 'resizeImage');
			$thumbImageSizeFunction      = EShopHelper::getConfigValue('thumb_image_size_function', 'resizeImage');
			$popupImageSizeFunction      = EShopHelper::getConfigValue('popup_image_size_function', 'resizeImage');
			$relatedImageSizeFunction    = EShopHelper::getConfigValue('related_image_size_function', 'resizeImage');

			// Main image resize
			if ($item->product_image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $item->product_image))
			{
				if (EShopHelper::getConfigValue('product_use_image_watermarks'))
				{
					$watermarkImage = EShopHelper::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/products/' . $item->product_image);
					$productImage   = $watermarkImage;
				}
				else
				{
					$productImage = $item->product_image;
				}

				$smallThumbImage = call_user_func_array(['EShopHelper', $additionalImageSizeFunction],
					[
						$productImage,
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_additional_width'),
						EShopHelper::getConfigValue('image_additional_height'),
					]);
				$thumbImage      = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
					[
						$productImage,
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_thumb_width'),
						EShopHelper::getConfigValue('image_thumb_height'),
					]);
				$popupImage      = call_user_func_array(['EShopHelper', $popupImageSizeFunction],
					[
						$productImage,
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_popup_width'),
						EShopHelper::getConfigValue('image_popup_height'),
					]);
			}
			else
			{
				$smallThumbImage = call_user_func_array(['EShopHelper', $additionalImageSizeFunction],
					[
						EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_additional_width'),
						EShopHelper::getConfigValue('image_additional_height'),
					]);
				$thumbImage      = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
					[
						EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_thumb_width'),
						EShopHelper::getConfigValue('image_thumb_height'),
					]);
				$popupImage      = call_user_func_array(['EShopHelper', $popupImageSizeFunction],
					[
						EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
						JPATH_ROOT . '/media/com_eshop/products/',
						EShopHelper::getConfigValue('image_popup_width'),
						EShopHelper::getConfigValue('image_popup_height'),
					]);
			}

			if ($additionalImageSizeFunction == 'notResizeImage')
			{
				$item->small_thumb_image = $rootUri . '/media/com_eshop/products/' . $smallThumbImage;
			}
			else
			{
				$item->small_thumb_image = $rootUri . '/media/com_eshop/products/resized/' . $smallThumbImage;
			}

			if ($thumbImageSizeFunction == 'notResizeImage')
			{
				$item->thumb_image = $rootUri . '/media/com_eshop/products/' . $thumbImage;
			}
			else
			{
				$item->thumb_image = $rootUri . '/media/com_eshop/products/resized/' . $thumbImage;
			}

			if ($popupImageSizeFunction == 'notResizeImage')
			{
				$item->popup_image = $rootUri . '/media/com_eshop/products/' . $popupImage;
			}
			else
			{
				$item->popup_image = $rootUri . '/media/com_eshop/products/resized/' . $popupImage;
			}

			// Set metakey and metadesc
			$metaKey  = $item->meta_key;
			$metaDesc = $item->meta_desc;

			if ($metaKey)
			{
				$document->setMetaData('keywords', $metaKey);
			}

			if ($metaDesc)
			{
				$document->setMetaData('description', $metaDesc);
			}

			// Product availability
			$productInventory = EShopHelper::getProductInventory($productId);

			if ($item->product_quantity <= 0)
			{
				$nullDate = $db->getNullDate();

				if ($item->product_available_date != $nullDate)
				{
					$this->product_available_date = HTMLHelper::date(
						$item->product_available_date,
						EShopHelper::getConfigValue('date_format', 'm-d-Y')
					);
				}
				$availability = EShopHelper::getStockStatusName($productInventory['product_stock_status_id'], Factory::getLanguage()->getTag());
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
			$productTags       = EShopHelper::getProductTags($item->id);
			$this->productTags = $productTags;

			//Product attachments
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__eshop_productattachments')
				->where('product_id = ' . intval($item->id))
				->order('ordering');
			$db->setQuery($query);
			$this->productAttachments = $db->loadObjectList();
			$item->product_short_desc = HTMLHelper::_('content.prepare', $item->product_short_desc);
			$item->product_desc       = HTMLHelper::_('content.prepare', $item->product_desc);

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

			PluginHelper::importPlugin('eshop');
			$plugins = $app->triggerEvent('onProductDisplay', [$item]);

			// Additional images resize
			$productImages = EShopHelper::getProductImages($productId);

			for ($i = 0; $n = count($productImages), $i < $n; $i++)
			{
				if ($productImages[$i]->image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $productImages[$i]->image))
				{
					if (EShopHelper::getConfigValue('product_use_image_watermarks'))
					{
						$watermarkImage = EShopHelper::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/products/' . $productImages[$i]->image);
						$productImage   = $watermarkImage;
					}
					else
					{
						$productImage = $productImages[$i]->image;
					}

					$smallThumbImage = call_user_func_array(['EShopHelper', $additionalImageSizeFunction],
						[
							$productImage,
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_additional_width'),
							EShopHelper::getConfigValue('image_additional_height'),
						]);
					$thumbImage      = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
						[
							$productImage,
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_thumb_width'),
							EShopHelper::getConfigValue('image_thumb_height'),
						]);
					$popupImage      = call_user_func_array(['EShopHelper', $popupImageSizeFunction],
						[
							$productImage,
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_popup_width'),
							EShopHelper::getConfigValue('image_popup_height'),
						]);
				}
				else
				{
					$smallThumbImage = call_user_func_array(['EShopHelper', $additionalImageSizeFunction],
						[
							EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_additional_width'),
							EShopHelper::getConfigValue('image_additional_height'),
						]);
					$thumbImage      = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
						[
							EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_thumb_width'),
							EShopHelper::getConfigValue('image_thumb_height'),
						]);
					$popupImage      = call_user_func_array(['EShopHelper', $popupImageSizeFunction],
						[
							EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
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
			$productOptions    = EShopHelper::getProductOptions($productId, Factory::getLanguage()->getTag());
			$hasSpecification  = false;
			$attributeGroups   = EShopHelper::getAttributeGroups(Factory::getLanguage()->getTag());
			$productAttributes = [];

			for ($i = 0; $n = count($attributeGroups), $i < $n; $i++)
			{
				$productAttributes[] = EShopHelper::getAttributes($productId, $attributeGroups[$i]->id, Factory::getLanguage()->getTag());
				if (count($productAttributes[$i]))
				{
					$hasSpecification = true;
				}
			}

			$productRelations = EShopHelper::getProductRelations($productId, Factory::getLanguage()->getTag());

			// Related products images resize
			$imageRelatedWidth  = EShopHelper::getConfigValue('image_related_width');
			$imageRelatedHeight = EShopHelper::getConfigValue('image_related_height');

			for ($i = 0; $n = count($productRelations), $i < $n; $i++)
			{
				if ($productRelations[$i]->product_image && is_file(
						JPATH_ROOT . '/media/com_eshop/products/' . $productRelations[$i]->product_image
					))
				{
					if (EShopHelper::getConfigValue('product_use_image_watermarks'))
					{
						$watermarkImage = EShopHelper::generateWatermarkImage(
							JPATH_ROOT . '/media/com_eshop/products/' . $productRelations[$i]->product_image
						);
						$productImage   = $watermarkImage;
					}
					else
					{
						$productImage = $productRelations[$i]->product_image;
					}

					$thumbImage = call_user_func_array(['EShopHelper', $relatedImageSizeFunction],
						[$productImage, JPATH_ROOT . '/media/com_eshop/products/', $imageRelatedWidth, $imageRelatedHeight]);
				}
				else
				{
					$thumbImage = call_user_func_array(['EShopHelper', $relatedImageSizeFunction],
						[
							EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
							JPATH_ROOT . '/media/com_eshop/products/',
							$imageRelatedWidth,
							$imageRelatedHeight,
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

				// Related products additional image
				$relatedProductImages = EShopHelper::getProductImages($productRelations[$i]->id);

				if (count($relatedProductImages) > 0)
				{
					$relatedProductImage = $relatedProductImages[0]->image;

					if ($relatedProductImage && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $relatedProductImage))
					{
						if (EShopHelper::getConfigValue('product_use_image_watermarks'))
						{
							$watermarkImage      = EShopHelper::generateWatermarkImage(
								JPATH_ROOT . '/media/com_eshop/products/' . $relatedProductImage
							);
							$relatedProductImage = $watermarkImage;
						}

						$additionalRelatedProductImage = call_user_func_array(['EShopHelper', $relatedImageSizeFunction],
							[$relatedProductImage, JPATH_ROOT . '/media/com_eshop/products/', $imageRelatedWidth, $imageRelatedHeight]);
					}
					else
					{
						$additionalRelatedProductImage = call_user_func_array(['EShopHelper', $relatedImageSizeFunction],
							[
								EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
								JPATH_ROOT . '/media/com_eshop/products/',
								$imageRelatedWidth,
								$imageRelatedHeight,
							]);
					}

					if ($relatedImageSizeFunction == 'notResizeImage')
					{
						$productRelations[$i]->additional_image = $rootUri . '/media/com_eshop/products/' . $additionalRelatedProductImage;
					}
					else
					{
						$productRelations[$i]->additional_image = $rootUri . '/media/com_eshop/products/resized/' . $additionalRelatedProductImage;
					}
				}
			}

			if (EShopHelper::getConfigValue('allow_reviews'))
			{
				$productReviews       = EShopHelper::getProductReviews($productId);
				$this->productReviews = $productReviews;
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

			$this->item              = $item;
			$this->plugins           = $plugins;
			$this->productImages     = $productImages;
			$this->discountPrices    = $discountPrices;
			$this->productOptions    = $productOptions;
			$this->hasSpecification  = $hasSpecification;
			$this->attributeGroups   = $attributeGroups;
			$this->productAttributes = $productAttributes;
			$this->productRelations  = $productRelations;
			$this->manufacturer      = EShopHelper::getProductManufacturer($productId, Factory::getLanguage()->getTag());
			$this->tax               = $tax;

			// Preparing rating html
			$ratingHtml = '<b>' . Text::_('ESHOP_BAD') . '</b>';

			for ($i = 1; $i <= 5; $i++)
			{
				$ratingHtml .= '<input class="form-check-input" type="radio" name="rating" value="' . $i . '" />';
			}

			$ratingHtml .= '<b>' . Text::_('ESHOP_EXCELLENT') . '</b>';

			$this->ratingHtml = $ratingHtml;

			$options   = [];
			$options[] = HTMLHelper::_('select.option', '', Text::_('ESHOP_RATING_SELECT'));
			$options[] = HTMLHelper::_('select.option', '5', Text::_('ESHOP_RATING_EXCELLENT'));
			$options[] = HTMLHelper::_('select.option', '4', Text::_('ESHOP_RATING_VERY_GOOD'));
			$options[] = HTMLHelper::_('select.option', '3', Text::_('ESHOP_RATING_AVERAGE'));
			$options[] = HTMLHelper::_('select.option', '2', Text::_('ESHOP_RATING_POOR'));
			$options[] = HTMLHelper::_('select.option', '1', Text::_('ESHOP_RATING_TERRIBLE'));

			$this->selectRating = HTMLHelper::_(
				'select.genericlist',
				$options,
				'rating',
				'class="star-rating" data-options=\'{"clearable":false, "tooltip":"' . Text::_('ESHOP_RATING_SELECT') . '"}\'',
				'value',
				'text',
				''
			);

			$this->productsNavigation = EShopHelper::getProductsNavigation($item->id);
			$this->labels             = EShopHelper::getProductLabels($item->id);
			
			//Captcha
			$this->showCaptcha = false;

			if (EShopHelper::getConfigValue('enable_reviews_captcha'))
			{
				$captchaPlugin = $app->get('captcha') ?: 'recaptcha';
				$plugin = PluginHelper::getPlugin('captcha', $captchaPlugin);

				if ($plugin)
				{
					$this->showCaptcha = true;
					$this->captcha     = Captcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required');
				}
				else
				{
					$app->enqueueMessage(Text::_('ESHOP_CAPTCHA_IS_NOT_ACTIVATED'), 'error');
				}

				$this->captchaPlugin = $captchaPlugin;
			}

			$this->bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

			parent::display($tpl);
		}
	}
}