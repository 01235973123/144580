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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Options helper class
 *
 */
class EShopOption
{
	/**
	 *
	 * Function to render an option input for a product
	 *
	 * @param   int  $productId
	 * @param   int  $optionId
	 * @param   int  $optionType
	 * @param   int  $taxClassId
	 *
	 * @return string code
	 */
	public static function renderOption($productId, $optionId, $optionType, $taxClassId)
	{
		$currency        = EShopCurrency::getInstance();
		$tax             = new EShopTax(EShopHelper::getConfig());
		$bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
		$product         = EShopHelper::getProduct($productId);
		$db              = Factory::getDbo();
		$query           = $db->getQuery(true);
		$query->select('id')
			->from('#__eshop_productoptions')
			->where('product_id = ' . intval($productId))
			->where('option_id = ' . intval($optionId));
		$db->setQuery($query);
		$productOptionId = $db->loadResult();

		switch ($optionType)
		{
			case 'Text':
			case 'Textarea':
				$query->clear()
					->select('price, price_sign, price_type')
					->from('#__eshop_productoptionvalues')
					->where('product_option_id = ' . intval($productOptionId));
				break;
			default:
				$query->clear()
					->select('ovd.value, pov.id, pov.price, pov.price_sign, pov.price_type, pov.image')
					->from('#__eshop_optionvalues AS ov')
					->innerJoin('#__eshop_optionvaluedetails AS ovd ON (ov.id = ovd.optionvalue_id)')
					->innerJoin('#__eshop_productoptionvalues AS pov ON (ovd.optionvalue_id = pov.option_value_id)')
					->where('ov.published = 1')
					->where('pov.published = 1')
					->where('pov.product_option_id = ' . intval($productOptionId))
					->where('value != ""')
					->where('ovd.language = "' . Factory::getLanguage()->getTag() . '"');

				$optionValueDefaultSorting = EShopHelper::getConfigValue('option_value_default_sorting', 'name-asc');

				switch ($optionValueDefaultSorting)
				{
					case 'name-desc':
						$query->order('ovd.value * 1 DESC, ovd.value DESC');
						break;
					case 'ordering-asc':
						$query->order('ov.ordering ASC');
						break;
					case 'ordering-desc':
						$query->order('ov.ordering DESC');
						break;
					case 'id-asc':
						$query->order('ov.id ASC');
						break;
					case 'id-desc':
						$query->order('ov.id DESC');
						break;
					case 'random':
						$query->order('RAND()');
						break;
					case 'name-asc':
					default:
						$query->order('ovd.value * 1 ASC, ovd.value ASC');
						break;
				}

				if (EShopHelper::getConfigValue('hide_out_of_stock_products'))
				{
					$query->where('pov.quantity > 0');
				}
				break;
		}

		$db->setQuery($query);
		$rows                   = $db->loadObjectList();
		$optionHtml             = '';
		$optionImage            = '';
		$imagePath              = JPATH_ROOT . '/media/com_eshop/options/';
		$imageSizeFunction      = EShopHelper::getConfigValue('option_image_size_function', 'resizeImage');
		$thumbImageSizeFunction = EShopHelper::getConfigValue('thumb_image_size_function', 'resizeImage');
		$popupImageSizeFunction = EShopHelper::getConfigValue('popup_image_size_function', 'resizeImage');
		$baseUri                = Uri::base(true);

		for ($i = 0; $n = count($rows), $i < $n; $i++)
		{
			$row = $rows[$i];

			if (EShopHelper::showPrice() && $row->price > 0 && !$product->product_call_for_price)
			{
				if (EShopHelper::getConfigValue('display_option_price', 'only_option_price') == 'only_option_price')
				{
					if ($optionType == 'Text' || $optionType == 'Textarea')
					{
						if ($row->price_type == 'P')
						{
							$rows[$i]->text = '(' . $row->price_sign . number_format($row->price, 2) . '% ' . Text::_('ESHOP_PER_CHAR') . ')';
						}
						else
						{
							if (EShopHelper::getConfigValue('display_option_price_with_tax', 1))
							{
								$rows[$i]->text = '(' . $row->price_sign . $currency->format(
										$tax->calculate($row->price, $taxClassId, EShopHelper::getConfigValue('tax'))
									) . ' ' . Text::_('ESHOP_PER_CHAR') . ')';
							}
							else
							{
								$rows[$i]->text = '(' . $row->price_sign . $currency->format($row->price) . ' ' . Text::_('ESHOP_PER_CHAR') . ')';
							}
						}
					}
					else
					{
						if ($row->price_type == 'P')
						{
							$rows[$i]->text = $row->value . ' (' . $row->price_sign . number_format($row->price, 2) . '%' . ')';
						}
						else
						{
							if (EShopHelper::getConfigValue('display_option_price_with_tax', 1))
							{
								$rows[$i]->text = $row->value . ' (' . $row->price_sign . $currency->format(
										$tax->calculate($row->price, $taxClassId, EShopHelper::getConfigValue('tax'))
									) . ')';
							}
							else
							{
								$rows[$i]->text = $row->value . ' (' . $row->price_sign . $currency->format($row->price) . ')';
							}
						}
					}
				}
				else
				{
					$rows[$i]->text = $row->value ?? '';
				}
			}
			else
			{
				$rows[$i]->text = $row->value ?? '';
			}

			if ($optionType != 'Text' && $optionType != 'Textarea')
			{
				$rows[$i]->value = $row->id;
			}

			//Resize option image
			if (isset($row->image) && $row->image != '')
			{
				if (EShopHelper::getConfigValue('product_use_image_watermarks'))
				{
					$watermarkImage = EShopHelper::generateWatermarkImage(JPATH_ROOT . '/media/com_eshop/options/' . $row->image);
					$tempImage      = $watermarkImage;
				}
				else
				{
					$tempImage = $row->image;
				}

				$thumbImage = call_user_func_array(['EShopHelper', $thumbImageSizeFunction],
					[$tempImage, $imagePath, EShopHelper::getConfigValue('image_thumb_width'), EShopHelper::getConfigValue('image_thumb_height')]);
				$popupImage = call_user_func_array(['EShopHelper', $popupImageSizeFunction],
					[$tempImage, $imagePath, EShopHelper::getConfigValue('image_popup_width'), EShopHelper::getConfigValue('image_popup_height')]);

				if ($thumbImageSizeFunction == 'notResizeImage')
				{
					$rows[$i]->thumb_image = $baseUri . '/media/com_eshop/options/' . $thumbImage;
				}
				else
				{
					$rows[$i]->thumb_image = $baseUri . '/media/com_eshop/options/resized/' . $thumbImage;
				}

				if ($popupImageSizeFunction == 'notResizeImage')
				{
					$rows[$i]->popup_image = $baseUri . '/media/com_eshop/options/' . $popupImage;
				}
				else
				{
					$rows[$i]->popup_image = $baseUri . '/media/com_eshop/options/resized/' . $popupImage;
				}

				$imageWidth = EShopHelper::getConfigValue('image_option_width');

				if (!$imageWidth)
				{
					$imageWidth = 100;
				}

				$imageHeight = EShopHelper::getConfigValue('image_option_height');

				if (!$imageHeight)
				{
					$imageHeight = 100;
				}

				if ($imageSizeFunction != 'notResizeImage')
				{
					if (!is_file(
						$imagePath . 'resized/' . File::stripExt($row->image) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt(
							$row->image
						)
					))
					{
						$rows[$i]->image = $baseUri . '/media/com_eshop/options/resized/' . call_user_func_array(['EShopHelper', $imageSizeFunction],
								[$tempImage, $imagePath, $imageWidth, $imageHeight]);
					}
					else
					{
						$rows[$i]->image = $baseUri . '/media/com_eshop/options/resized/' . File::stripExt(
								$row->image
							) . '-' . $imageWidth . 'x' . $imageHeight . '.' . EShopHelper::getFileExt($row->image);
					}
				}
				else
				{
					$rows[$i]->image = $baseUri . '/media/com_eshop/options/' . $row->image;
				}

				if (EShopHelper::getConfigValue('view_image') == 'zoom')
				{
					$optionImage .= '<a id="option-image-' . $rows[$i]->id . '" class="option-image-zoom" href="javascript:void(0);" rel="{gallery: \'product-thumbnails\', smallimage: \'' . $rows[$i]->thumb_image . '\',largeimage: \'' . $rows[$i]->popup_image . '\'}">
										<img src="' . $rows[$i]->image . '">
									</a>';
				}
				else
				{
					$optionImage .= '<a id="option-image-' . $rows[$i]->id . '" class="product-image" href="' . $rows[$i]->popup_image . '">
										<img src="' . $rows[$i]->thumb_image . '" title="' . $rows[$i]->text . '" alt="' . $rows[$i]->text . '" />
									</a>';
				}
			}
		}

		if ($optionImage != '')
		{
			$optionHtml .= '<span style="display:none;" class="option-image">' . $optionImage . '</span>';
		}

		if (EShopHelper::isCartMode($product) || EShopHelper::isQuoteMode($product))
		{
			$updateInfo      = false;
			$updateFunctions = '';

			if (EShopHelper::getConfigValue('dynamic_info', '0') && (EShopHelper::getConfigValue('show_sku') || EShopHelper::getConfigValue('show_availability') || EShopHelper::getConfigValue('show_product_weight')))
			{
				$updateInfo = true;
			}

			if (EShopHelper::getConfigValue('dynamic_price') && EShopHelper::showPrice() && !$product->product_call_for_price)
			{
				switch ($optionType)
				{
					case 'Text':
					case 'Textarea':
						$updateFunctions = ' onchange="updatePrice();"';
						break;
					default:
						if ($updateInfo)
						{
							$updateFunctions = ' onchange="updatePrice(); updateInfo();"';
						}
						else
						{
							$updateFunctions = ' onchange="updatePrice();"';
						}
						break;
				}
			}
			else
			{
				if ($updateInfo && ($optionType == 'Select' || $optionType == 'Checkbox' || $optionType == 'Radio'))
				{
					$updateFunctions = ' onchange="updateInfo();"';
				}
			}

			switch ($optionType)
			{
				case 'Select':
					$options[]  = HTMLHelper::_('select.option', '', Text::_('ESHOP_PLEASE_SELECT'), 'value', 'text');
					$optionHtml .= HTMLHelper::_(
						'select.genericlist',
						array_merge($options, $rows),
						'options[' . $productOptionId . ']',
						[
							'option.text.toHtml' => false,
							'option.value'       => 'value',
							'option.text'        => 'text',
							'list.attr'          => 'class="input-xlarge form-select"' . $updateFunctions,
						]
					);
					break;
				case 'Checkbox':
					for ($i = 0; $n = count($rows), $i < $n; $i++)
					{
						$optionHtml .= '<label class="checkbox">';
						$optionHtml .= '<input type="checkbox" name="options[' . $productOptionId . '][]" value="' . $rows[$i]->id . '"' . $updateFunctions . '> ' . $rows[$i]->text;
						$optionHtml .= '</label>';
					}
					break;
				case 'Radio':
					for ($i = 0; $n = count($rows), $i < $n; $i++)
					{
						$optionHtml .= '<label class="radio">';
						$optionHtml .= '<input type="radio" name="options[' . $productOptionId . ']" value="' . $rows[$i]->id . '"' . $updateFunctions . '> ' . $rows[$i]->text;
						$optionHtml .= '</label>';
					}
					break;
				case 'Text':
					$optionHtml .= '<input type="text" name="options[' . $productOptionId . ']" value=""' . $updateFunctions . ' />' . $rows[0]->text;
					break;
				case 'Textarea':
					$optionHtml .= '<textarea name="options[' . $productOptionId . ']" cols="40" rows="5"' . $updateFunctions . ' ></textarea>' . $rows[0]->text;
					break;
				case 'File':
					$optionHtml .= '<input type="button" value="' . Text::_(
							'ESHOP_UPLOAD_FILE'
						) . '" id="button-option-' . $productOptionId . '" class="' . $bootstrapHelper->getClassMapping('btn') . ' btn-primary">';
					$optionHtml .= '<input type="hidden" name="options[' . $productOptionId . ']" value="" />';
					break;
				case 'Date':
					$optionHtml .= HTMLHelper::_(
						'calendar',
						'',
						'options[' . $productOptionId . ']',
						'options[' . $productOptionId . ']',
						'%Y-%m-%d'
					);
					break;
				case 'Datetime':
					$optionHtml .= HTMLHelper::_(
						'calendar',
						'',
						'options[' . $productOptionId . ']',
						'options[' . $productOptionId . ']',
						'%Y-%m-%d 00:00:00'
					);
					break;
				default:
					break;
			}
		}
		else
		{
			for ($i = 0; $n = count($rows), $i < $n; $i++)
			{
				echo $rows[$i]->text . '<br />';
			}
		}

		return $optionHtml;
	}
}