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
use Joomla\Filesystem\File;
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
class EShopViewCompare extends EShopView
{
	/**
	 *
	 * @var $success
	 */
	protected $success;

	/**
	 *
	 * @var $visibleAttributeGroups
	 */
	protected $visibleAttributeGroups;

	/**
	 *
	 * @var $products
	 */
	protected $products;

	/**
	 *
	 * @var $fieldTitle
	 */
	protected $fieldTitle;

	/**
	 *
	 * @var $bootstrapHelper
	 */
	protected $bootstrapHelper;

	public function display($tpl = null)
	{
		$app     = Factory::getApplication();
		$rootUri = Uri::root(true);
		$app->getDocument()->addStyleSheet($rootUri . '/media/com_eshop/assets/colorbox/colorbox.css');

		$tax      = new EShopTax(EShopHelper::getConfig());
		$currency = EShopCurrency::getInstance();

		$session                = $app->getSession();
		$compare                = $session->get('compare');
		$products               = [];
		$attributeGroups        = EShopHelper::getAttributeGroups(Factory::getLanguage()->getTag());
		$visibleAttributeGroups = [];


		$this->setPageTitle(Text::_('ESHOP_COMPARE'));

		$fieldTitle = [];

		if (isset($compare) && count($compare))
		{
			foreach ($compare as $productId)
			{
				$productInfo = EShopHelper::getProduct($productId, Factory::getLanguage()->getTag());

				if (is_object($productInfo))
				{
					// Image
					$imageSizeFunction = EShopHelper::getConfigValue('compare_image_size_function', 'resizeImage');

					if ($productInfo->product_image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $productInfo->product_image))
					{
						if (EShopHelper::getConfigValue('product_use_image_watermarks'))
						{
							$watermarkImage = EShopHelper::generateWatermarkImage(
								JPATH_ROOT . '/media/com_eshop/products/' . $productInfo->product_image
							);
							$productImage   = $watermarkImage;
						}
						else
						{
							$productImage = $productInfo->product_image;
						}

						$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
							[
								$productImage,
								JPATH_ROOT . '/media/com_eshop/products/',
								EShopHelper::getConfigValue('image_compare_width'),
								EShopHelper::getConfigValue('image_compare_height'),
							]);
					}
					else
					{
						$image = call_user_func_array(['EShopHelper', $imageSizeFunction],
							[
								EShopHelper::getConfigValue('default_product_image', 'no-image.png'),
								JPATH_ROOT . '/media/com_eshop/products/',
								EShopHelper::getConfigValue('image_compare_width'),
								EShopHelper::getConfigValue('image_compare_height'),
							]);
					}

					if ($imageSizeFunction == 'notResizeImage')
					{
						$image = $rootUri . '/media/com_eshop/products/' . $image;
					}
					else
					{
						$image = $rootUri . '/media/com_eshop/products/resized/' . $image;
					}

					// Product Availability
					$productInventory = EShopHelper::getProductInventory($productId);

					if ($productInfo->product_quantity <= 0)
					{
						$availability = EShopHelper::getStockStatusName(
							$productInventory['product_stock_status_id'],
							Factory::getLanguage()->getTag()
						);
					}
					elseif ($productInventory['product_stock_display'])
					{
						$availability = $productInfo->product_quantity;
					}
					else
					{
						$availability = Text::_('ESHOP_IN_STOCK');
					}

					// Manufacturer
					$manufacturer = EShopHelper::getProductManufacturer($productId, Factory::getLanguage()->getTag());

					// Price
					$productPriceArray = EShopHelper::getProductPriceArray($productId, $productInfo->product_price);

					if ($productPriceArray['salePrice'] >= 0)
					{
						$basePrice = $currency->format(
							$tax->calculate($productPriceArray['basePrice'], $productInfo->product_taxclass_id, EShopHelper::getConfigValue('tax'))
						);
						$salePrice = $currency->format(
							$tax->calculate($productPriceArray['salePrice'], $productInfo->product_taxclass_id, EShopHelper::getConfigValue('tax'))
						);
					}
					else
					{
						$basePrice = $currency->format(
							$tax->calculate($productPriceArray['basePrice'], $productInfo->product_taxclass_id, EShopHelper::getConfigValue('tax'))
						);
						$salePrice = 0;
					}

					// Atrributes
					$productAttributes = [];

					for ($j = 0; $m = count($attributeGroups), $j < $m; $j++)
					{
						$attributes = EShopHelper::getAttributes($productId, $attributeGroups[$j]->id, Factory::getLanguage()->getTag());

						if (count($attributes))
						{
							$visibleAttributeGroups[$attributeGroups[$j]->id]['id']                  = $attributeGroups[$j]->id;
							$visibleAttributeGroups[$attributeGroups[$j]->id]['attributegroup_name'] = $attributeGroups[$j]->attributegroup_name;

							foreach ($attributes as $attribute)
							{
								if (isset($visibleAttributeGroups[$attributeGroups[$j]->id]['attribute_name']))
								{
									if (!in_array($attribute->attribute_name, $visibleAttributeGroups[$attributeGroups[$j]->id]['attribute_name']))
									{
										$visibleAttributeGroups[$attributeGroups[$j]->id]['attribute_name'][] = $attribute->attribute_name;
									}
								}
								else
								{
									$visibleAttributeGroups[$attributeGroups[$j]->id]['attribute_name'][] = $attribute->attribute_name;
								}

								$productAttributes[$attributeGroups[$j]->id]['value'][$attribute->attribute_name] = $attribute->value;
							}
						}
					}

					//Custom fields handle
					$productFieldValue = [];

					if (EShopHelper::getConfigValue('product_custom_fields'))
					{
						EShopHelper::prepareCustomFieldsData([$productInfo], true);

						if (!count($fieldTitle))
						{
							foreach ($productInfo->paramData as $param)
							{
								$fieldTitle[] = $param['title'];
							}
						}

						foreach ($productInfo->paramData as $param)
						{
							$productFieldValue[] = $param['value'];
						}
					}

					$products[$productId] = [
						'product_id'             => $productId,
						'product_sku'            => $productInfo->product_sku,
						'product_name'           => $productInfo->product_name,
						'product_short_desc'     => $productInfo->product_short_desc,
						'image'                  => $image,
						'product_desc'           => substr(
								strip_tags(html_entity_decode($productInfo->product_desc, ENT_QUOTES, 'UTF-8')),
								0,
								200
							) . '...',
						'base_price'             => $basePrice,
						'sale_price'             => $salePrice,
						'product_call_for_price' => $productInfo->product_call_for_price,
						'availability'           => $availability,
						'rating'                 => EShopHelper::getProductRating($productId),
						'num_reviews'            => count(EShopHelper::getProductReviews($productId)),
						'weight'                 => number_format($productInfo->product_weight, 2) . EShopHelper::getWeightUnit(
								$productInfo->product_weight_id,
								Factory::getLanguage()->getTag()
							),
						'length'                 => number_format($productInfo->product_length, 2) . EShopHelper::getLengthUnit(
								$productInfo->product_length_id,
								Factory::getLanguage()->getTag()
							),
						'width'                  => number_format($productInfo->product_width, 2) . EShopHelper::getLengthUnit(
								$productInfo->product_length_id,
								Factory::getLanguage()->getTag()
							),
						'height'                 => number_format($productInfo->product_height, 2) . EShopHelper::getLengthUnit(
								$productInfo->product_length_id,
								Factory::getLanguage()->getTag()
							),
						'manufacturer'           => $manufacturer->manufacturer_name ?? '',
						'attributes'             => $productAttributes,
						'productFieldValue'      => $productFieldValue,
					];
				}
			}
		}

		if ($session->get('success'))
		{
			$this->success = $session->get('success');
			$session->clear('success');
		}

		$this->visibleAttributeGroups = $visibleAttributeGroups;
		$this->products               = $products;
		$this->fieldTitle             = $fieldTitle;
		$this->bootstrapHelper        = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));

		parent::display($tpl);
	}
}