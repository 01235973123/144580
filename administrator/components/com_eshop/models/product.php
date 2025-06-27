<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2013 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Eshop Component Product Model
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopModelProduct extends EShopModel
{
	/**
	 * Flag to indicate that this model support event triggering
	 *
	 * @var boolean
	 */
	protected $triggerEvents = true;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct($config)
	{
		$config['translatable']        = true;
		$config['translatable_fields'] = [
			'product_name',
			'product_alias',
			'product_desc',
			'product_short_desc',
			'product_page_title',
			'product_page_heading',
			'product_alt_image',
			'product_canoncial_link',
			'product_price_text',
			'product_custom_message',
			'tab1_title',
			'tab1_content',
			'tab2_title',
			'tab2_content',
			'tab3_title',
			'tab3_content',
			'tab4_title',
			'tab4_content',
			'tab5_title',
			'tab5_content',
			'meta_key',
			'meta_desc',
		];
		parent::__construct($config);
	}

	/**
	 * Function to store product
	 * @see EShopModel::store()
	 */
	public function store(&$data)
	{
		$mainframe = Factory::getApplication();
		$isNew     = true;

		if ($data['id'])
		{
			$isNew = false;
			$row   = new EShopTable('#__eshop_products', 'id', $this->getDbo());
			$row->load($data['id']);
			$oldProductQuantity        = $row->product_quantity;
			$oldProductThreshold       = $row->product_threshold;
			$oldProductThresholdNotify = $row->product_threshold_notify;

			if ($oldProductThresholdNotify == 1 && $data['product_threshold'] > 0 && $data['product_threshold'] <= $data['product_quantity'])
			{
				$data['product_threshold_notify'] = 0;
			}
		}

		//Validate SKU
		if (EShopHelper::getConfigValue('product_sku_validation', '0') && EShopHelper::isExistedProductSku($data['product_sku'], $data['id']))
		{
			$mainframe->enqueueMessage(Text::_('ESHOP_ERROR_PRODUCT_SKU_EXISTED'), 'error');
			$mainframe->redirect('index.php?option=com_eshop&task=product.edit&cid[]=' . $data['id']);
		}

		$imagePath = JPATH_ROOT . '/media/com_eshop/products/';
		$input     = Factory::getApplication()->input;

		if ($input->getInt('remove_image') && $data['id'])
		{
			//Remove image first
			if (is_file($imagePath . $row->product_image))
			{
				File::delete($imagePath . $row->product_image);
			}

			if (is_file($imagePath . 'resized/' . File::stripExt($row->product_image) . '-100x100.' . EShopHelper::getFileExt($row->product_image)))
			{
				File::delete($imagePath . 'resized/' . File::stripExt($row->product_image) . '-100x100.' . EShopHelper::getFileExt($row->product_image));
			}

			$data['product_image'] = '';
		}

		// Check all of the images before uploading
		$errorUpload = '';

		// Check product main image first
		$productImage = $_FILES['product_image'];

		if ($productImage['name'])
		{
			$checkFileUpload = EShopFile::checkFileUpload($productImage);

			if (is_array($checkFileUpload))
			{
				$errorUpload = sprintf(Text::_('ESHOP_PRODUCT_MAIN_IMAGE_UPLOAD_ERROR'), implode(' / ', $checkFileUpload));
			}
		}

		// Check product additional images
		if ($errorUpload == '' && isset($_FILES['image']))
		{
			$image           = $_FILES['image'];
			$checkFileUpload = EShopFile::checkFileUpload($image);
			if (is_array($checkFileUpload))
			{
				$errorUpload = sprintf(Text::_('ESHOP_PRODUCT_ADDITIONAL_IMAGES_UPLOAD_ERROR'), implode(' / ', $checkFileUpload));
			}
		}

		// Check product attachments
		if ($errorUpload == '' && isset($_FILES['attachment']))
		{
			$attachment      = $_FILES['attachment'];
			$checkFileUpload = EShopFile::checkFileUpload($attachment);
			if (is_array($checkFileUpload))
			{
				$errorUpload = sprintf(Text::_('ESHOP_PRODUCT_ATTACHMENTS_UPLOAD_ERROR'), implode(' / ', $checkFileUpload));
			}
		}

		// Check product options images
		if ($errorUpload == '')
		{
			$optionIds = $input->getInt('option_ids', []);

			if (isset($optionIds) && count($optionIds))
			{
				for ($i = 0; $n = count($optionIds), $i < $n; $i++)
				{
					$optionId = $optionIds[$i];

					if (isset($_FILES['product_option_value_' . $optionId . '_image']))
					{
						$productOptionValueImages = $_FILES['product_option_value_' . $optionId . '_image'];
						$checkFileUpload          = EShopFile::checkFileUpload($productOptionValueImages);

						if (is_array($checkFileUpload))
						{
							$errorUpload = sprintf(Text::_('ESHOP_PRODUCT_OPTIONS_IMAGES_UPLOAD_ERROR'), implode(' / ', $checkFileUpload));
							break;
						}
					}
				}
			}
		}

		if ($errorUpload != '')
		{
			$mainframe->enqueueMessage($errorUpload, 'error');
			$mainframe->redirect('index.php?option=com_eshop&task=product.edit&cid[]=' . $data['id']);
		}
		//End check images

		// Process main image first
		$productImage = $_FILES['product_image'];

		if (is_uploaded_file($productImage['tmp_name']))
		{
			if (is_file($imagePath . $productImage['name']))
			{
				$imageFileName = uniqid('image_') . '_' . File::makeSafe($productImage['name']);
			}
			else
			{
				$imageFileName = File::makeSafe($productImage['name']);
			}
			File::upload($productImage['tmp_name'], $imagePath . $imageFileName, false, true);
			// Resize image
			EShopHelper::resizeImage($imageFileName, JPATH_ROOT . '/media/com_eshop/products/', 100, 100);
			$data['product_image'] = $imageFileName;
		}
		else
		{
			if ($data['existed_image'] != '')
			{
				$data['product_image'] = $data['existed_image'];
			}
		}

		if (isset($data['product_customergroups']) && count($data['product_customergroups']))
		{
			$data['product_customergroups'] = implode(',', $data['product_customergroups']);
		}
		else
		{
			$data['product_customergroups'] = '';
		}

		if (isset($data['product_languages']) && count($data['product_languages']))
		{
			$data['product_languages'] = implode(',', $data['product_languages']);
		}
		else
		{
			$data['product_languages'] = '';
		}

		if (EShopHelper::getConfigValue('product_custom_fields') && is_array($data['params']))
		{
			$data['custom_fields'] = json_encode($data['params']);
		}

		if ($data['product_available_date'] == '')
		{
			$data['product_available_date'] = '0000-00-00 00:00:00';
		}

		if ($data['product_end_date'] == '')
		{
			$data['product_end_date'] = '0000-00-00 00:00:00';
		}

		parent::store($data);
		$languages    = EShopHelper::getLanguages();
		$translatable = Multilanguage::isEnabled() && count($languages) > 1;
		$db           = $this->getDbo();
		$query        = $db->getQuery(true);

		//Store newsletter integration params
		$row = new EShopTable('#__eshop_products', 'id', $db);
		$row->load($data['id']);

		//Trigger event which allows plugins to save it own data
		PluginHelper::importPlugin('eshop');
		$app = Factory::getApplication();
		$app->triggerEvent('onAfterSaveProduct', [$row, $data, $isNew]);

		$params = new Registry($row->params);

		if (EShopHelper::getConfigValue('acymailing_integration') && is_file(
				JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php'
			))
		{
			$params->set('acymailing_list_ids', implode(',', $input->get('acymailing_list_ids', [])));
		}
		if (EShopHelper::getConfigValue('mailchimp_integration') && EShopHelper::getConfigValue('api_key_mailchimp') != '')
		{
			$params->set('mailchimp_list_ids', implode(',', $input->get('mailchimp_list_ids', [])));
		}

		$row->params = $params->toString();
		$row->store();
		$productId = $data['id'];
		$user      = Factory::getUser();

		//Store product categories
		$query->delete('#__eshop_productcategories')
			->where('product_id = ' . intval($productId));
		$db->setQuery($query);
		$db->execute();
		$row            = new EShopTable('#__eshop_productcategories', 'id', $db);
		$mainCategoryId = $data['main_category_id'];

		if ($mainCategoryId)
		{
			$row->id            = 0;
			$row->product_id    = $productId;
			$row->category_id   = $mainCategoryId;
			$row->main_category = 1;
			$row->store();
		}

		$additionalCategories = $data['category_id'] ?? [];

		if (isset($additionalCategories) && count($additionalCategories))
		{
			for ($i = 0; $n = count($additionalCategories), $i < $n; $i++)
			{
				$categoryId = $additionalCategories[$i];

				if ($categoryId && $categoryId != $mainCategoryId)
				{
					$row->id            = 0;
					$row->product_id    = $productId;
					$row->category_id   = $additionalCategories[$i];
					$row->main_category = 0;
					$row->store();
				}
			}
		}

		//Store download products
		$productDownloadsId = $input->getInt('product_downloads_id', []);
		$query->clear();
		$query->delete('#__eshop_productdownloads')
			->where('product_id = ' . intval($productId));
		$db->setQuery($query);
		$db->execute();
		$row = new EShopTable('#__eshop_productdownloads', 'id', $db);

		if (isset($productDownloadsId) && count($productDownloadsId))
		{
			for ($i = 0; $n = count($productDownloadsId), $i < $n; $i++)
			{
				$row->id          = 0;
				$row->product_id  = $productId;
				$row->download_id = $productDownloadsId[$i];
				$row->store();
			}
		}

		//Store related products
		$relatedProductId = $input->getInt('related_product_id', []);
		$query->clear();
		$query->delete('#__eshop_productrelations')
			->where('product_id = ' . intval($productId) . ' OR related_product_id = ' . intval($productId));
		$db->setQuery($query);
		$db->execute();
		$row = new EShopTable('#__eshop_productrelations', 'id', $db);

		if (isset($relatedProductId) && count($relatedProductId))
		{
			for ($i = 0; $n = count($relatedProductId), $i < $n; $i++)
			{
				$row->id                 = 0;
				$row->product_id         = $productId;
				$row->related_product_id = $relatedProductId[$i];
				$row->store();

				//And vice versa
				$row->id                 = 0;
				$row->product_id         = $relatedProductId[$i];
				$row->related_product_id = $productId;
				$row->store();
			}
		}

		//Relate product to category
		$input                   = Factory::getApplication()->input;
		$relateProductToCategory = $input->getInt('relate_product_to_category');

		if ($relateProductToCategory)
		{
			$query->clear();
			$query->delete('#__eshop_productrelations')
				->where('product_id = ' . intval($productId))
				->where(
					'related_product_id IN (SELECT product_id FROM #__eshop_productcategories WHERE category_id IN (SELECT category_id FROM #__eshop_productcategories WHERE product_id = ' . $productId . '))'
				);
			$db->setQuery($query);
			$db->execute();

			$query->clear();
			$query->delete('#__eshop_productrelations')
				->where('related_product_id = ' . intval($productId))
				->where(
					'product_id IN (SELECT product_id FROM #__eshop_productcategories WHERE category_id IN (SELECT category_id FROM #__eshop_productcategories WHERE product_id = ' . $productId . '))'
				);
			$db->setQuery($query);
			$db->execute();

			$sql = 'INSERT INTO #__eshop_productrelations' .
				' (id, product_id, related_product_id)' .
				' SELECT DISTINCT 0, ' . $productId . ', product_id FROM #__eshop_productcategories WHERE category_id IN (SELECT category_id FROM #__eshop_productcategories WHERE product_id = ' . $productId . ') AND product_id != ' . $productId;
			$db->setQuery($sql);
			$db->execute();

			$sql = 'INSERT INTO #__eshop_productrelations' .
				' (id, related_product_id, product_id)' .
				' SELECT DISTINCT 0, ' . $productId . ', product_id FROM #__eshop_productcategories WHERE category_id IN (SELECT category_id FROM #__eshop_productcategories WHERE product_id = ' . $productId . ') AND product_id != ' . $productId;
			$db->setQuery($sql);
			$db->execute();
		}

		//Store product tags
		$productTags = $input->getString('product_tags');

		if ($productTags != '')
		{
			$productTagsArr = explode(',', $productTags);

			if (count($productTagsArr))
			{
				$tagIdArr = [];

				foreach ($productTagsArr as $tag)
				{
					$tag = trim($tag);
					$query->clear();
					$query->select('id')
						->from('#__eshop_tags')
						->where('tag_name = ' . $db->quote($tag));
					$db->setQuery($query);
					$tagId = $db->loadResult();

					if (!$tagId)
					{
						$row            = new EShopTable('#__eshop_tags', 'id', $db);
						$row->id        = 0;
						$row->tag_name  = $tag;
						$row->hits      = 0;
						$row->published = 1;
						$row->store();
						$tagId = $row->id;
					}

					$tagIdArr[] = $tagId;
					$query->clear();
					$query->select('id')
						->from('#__eshop_producttags')
						->where('product_id = ' . intval($productId))
						->where('tag_id = ' . intval($tagId));
					$db->setQuery($query);

					if (!$db->loadResult())
					{
						$query->clear();
						$query->insert('#__eshop_producttags')
							->columns('id, product_id, tag_id')
							->values("'0', $productId, $tagId");
						$db->setQuery($query);
						$db->execute();
					}
				}
				$query->clear();
				$query->delete('#__eshop_producttags')
					->where('product_id = ' . intval($productId))
					->where('tag_id NOT IN (' . implode(',', $tagIdArr) . ')');
				$db->setQuery($query);
				$db->execute();
			}
		}
		else
		{
			$query->clear();
			$query->delete('#__eshop_producttags')
				->where('product_id = ' . intval($productId));
			$db->setQuery($query);
			$db->execute();
		}

		//Store product attributes
		$attributeId        = $input->getInt('attribute_id', []);
		$productAttributeId = $input->getInt('productattribute_id', []);
		$attributePublished = $input->getInt('attribute_published', []);

		//Delete in product attributes
		$query->clear();
		$query->delete('#__eshop_productattributes')
			->where('product_id = ' . intval($productId));

		if (isset($productAttributeId) && count($productAttributeId))
		{
			$query->where('id NOT IN (' . implode(',', $productAttributeId) . ')');
		}

		$db->setQuery($query);
		$db->execute();

		//Delete in product attribute details
		$query->clear();
		$query->delete('#__eshop_productattributedetails')
			->where('product_id = ' . intval($productId));

		if (isset($productAttributeId) && count($productAttributeId))
		{
			$query->where('productattribute_id NOT IN (' . implode(',', $productAttributeId) . ')');
		}

		$db->setQuery($query);
		$db->execute();

		if ($translatable)
		{
			if (isset($attributePublished) && count($attributePublished))
			{
				for ($i = 0; $n = count($attributePublished), $i < $n; $i++)
				{
					$row               = new EShopTable('#__eshop_productattributes', 'id', $db);
					$row->id           = $productAttributeId[$i] ?? 0;
					$row->product_id   = $productId;
					$row->attribute_id = $attributeId[$i];
					$row->published    = $attributePublished[$i];
					$row->store();

					foreach ($languages as $language)
					{
						$langCode                        = $language->lang_code;
						$productAttributeDetailsId       = $input->getInt('productattributedetails_id_' . $langCode);
						$attributeValue                  = $input->get(
							'attribute_value_' . $langCode,
							null,
							'default',
							'none',
							ESHOP_RAD_INPUT_ALLOWRAW
						);
						$detailsRow                      = new EShopTable('#__eshop_productattributedetails', 'id', $db);
						$detailsRow->id                  = $productAttributeDetailsId[$i] ?? 0;
						$detailsRow->productattribute_id = $row->id;
						$detailsRow->product_id          = $productId;
						$detailsRow->value               = $attributeValue[$i];
						$detailsRow->language            = $langCode;
						$detailsRow->store();
					}
				}
			}
		}
		else
		{
			$productAttributeDetailsId = $input->getInt('productattributedetails_id');
			$attributeValue            = $input->get('attribute_value', null, 'default', 'none', ESHOP_RAD_INPUT_ALLOWRAW);

			if (isset($attributePublished) && count($attributePublished))
			{
				for ($i = 0; $n = count($attributePublished), $i < $n; $i++)
				{
					$row               = new EShopTable('#__eshop_productattributes', 'id', $db);
					$row->id           = $productAttributeId[$i] ?? 0;
					$row->product_id   = $productId;
					$row->attribute_id = $attributeId[$i];
					$row->published    = $attributePublished[$i];
					$row->store();
					$detailsRow                      = new EShopTable('#__eshop_productattributedetails', 'id', $db);
					$detailsRow->id                  = $productAttributeDetailsId[$i] ?? 0;
					$detailsRow->productattribute_id = $row->id;
					$detailsRow->product_id          = $productId;
					$detailsRow->value               = $attributeValue[$i];
					$detailsRow->language            = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
					$detailsRow->store();
				}
			}
		}

		//Store product options
		$optionIds        = $input->getInt('option_ids', []);
		$productOptionIds = $input->getInt('product_option_ids', []);
		$optionTypes      = $input->getInt('option_types', []);

		if (isset($optionIds) && count($optionIds))
		{
			$row      = new EShopTable('#__eshop_productoptions', 'id', $db);
			$valueRow = new EShopTable('#__eshop_productoptionvalues', 'id', $db);

			for ($i = 0; $n = count($optionIds), $i < $n; $i++)
			{
				$optionId = $optionIds[$i];

				if ($input->getInt('assign_' . $optionId))
				{
					//Store options
					$row->id         = $productOptionIds[$i];
					$row->product_id = $productId;
					$row->option_id  = $optionId;
					$row->required   = $input->getInt('required_' . $optionId);
					$row->store();

					//Store options values
					$productOptionValueIds        = $input->getInt('product_option_value_' . $optionId . '_ids', []);
					$optionValueId                = $input->getInt('option_value_' . $optionId . '_id', []);
					$productOptionValueSku        = $input->get(
						'product_option_value_' . $optionId . '_sku',
						[],
						'default',
						'none',
						ESHOP_RAD_INPUT_ALLOWRAW
					);
					$productOptionValueQuantity   = $input->getInt('product_option_value_' . $optionId . '_quantity', []);
					$productOptionValuePriceSign  = $input->get(
						'product_option_value_' . $optionId . '_price_sign',
						[],
						'default',
						'none',
						ESHOP_RAD_INPUT_ALLOWRAW
					);
					$productOptionValuePriceType  = $input->getString('product_option_value_' . $optionId . '_price_type', []);
					$productOptionValuePrice      = $input->getFloat('product_option_value_' . $optionId . '_price', []);
					$productOptionValueWeightSign = $input->get(
						'product_option_value_' . $optionId . '_weight_sign',
						[],
						'default',
						'none',
						ESHOP_RAD_INPUT_ALLOWRAW
					);
					$productOptionValueWeight     = $input->getFloat('product_option_value_' . $optionId . '_weight', []);
					$productOptionValueShipping   = $input->get(
						'product_option_value_' . $optionId . '_shipping',
						[],
						'default',
						'none',
						ESHOP_RAD_INPUT_ALLOWRAW
					);
					$productOptionValuePublished   = $input->get(
						'product_option_value_' . $optionId . '_published',
						[],
						'default',
						'none',
						ESHOP_RAD_INPUT_ALLOWRAW
						);

					if (isset($optionValueId) && count($optionValueId))
					{
						//Delete some old option values first
						$query->clear();
						$query->delete('#__eshop_productoptionvalues')
							->where('product_id = ' . intval($productId))
							->where('option_id = ' . intval($optionId))
							->where('id NOT IN (' . implode(',', $productOptionValueIds) . ')');
						$db->setQuery($query);
						$db->execute();

						//Upload images if available
						$productOptionValueImages   = $_FILES['product_option_value_' . $optionId . '_image'] ?? [];
						$productOptionValueImage    = $input->getString('product_option_value_' . $optionId . '_imageold');
						$productOptionValueImageNew = [];

						if (isset($productOptionValueImages) && count($productOptionValueImages))
						{
							$imageOptionValuePath = JPATH_ROOT . '/media/com_eshop/options/';

							foreach ($productOptionValueImages['name'] as $index => $value)
							{
								if (is_uploaded_file($productOptionValueImages['tmp_name'][$index]) && $value != '')
								{
									if (is_file($imageOptionValuePath . $productOptionValueImages['name'][$index]))
									{
										$imageOptionValueFileName = uniqid('image_') . '_' . File::makeSafe(
												$productOptionValueImages['name'][$index]
											);
									}
									else
									{
										$imageOptionValueFileName = File::makeSafe($productOptionValueImages['name'][$index]);
									}

									if (File::upload(
										$productOptionValueImages['tmp_name'][$index],
										$imageOptionValuePath . $imageOptionValueFileName,
										false,
										true
									))
									{
										if (is_file($imageOptionValuePath . $productOptionValueImage[$index]))
										{
											File::delete($imageOptionValuePath . $productOptionValueImage[$index]);
										}

										if (is_file(
											$imageOptionValuePath . 'resized/' . File::stripExt(
												$productOptionValueImage[$index]
											) . '-100x100.' . EShopHelper::getFileExt($productOptionValueImage[$index])
										))
										{
											File::delete(
												$imageOptionValuePath . 'resized/' . File::stripExt(
													$productOptionValueImage[$index]
												) . '-100x100.' . EShopHelper::getFileExt($productOptionValueImage[$index])
											);
										}

										EShopHelper::resizeImage($imageOptionValueFileName, $imageOptionValuePath, 100, 100);
										$productOptionValueImage[$index] = $imageOptionValueFileName;
									}

									$productOptionValueImageNew[$index] = true;
								}
								else
								{
									$productOptionValueImageNew[$index] = false;
								}
							}
						}

						if (isset($optionValueId) && count($optionValueId))
						{
							for ($j = 0; $m = count($optionValueId), $j < $m; $j++)
							{
								$valueRow->id                = $productOptionValueIds[$j];
								$valueRow->product_option_id = $row->id;
								$valueRow->product_id        = $productId;
								$valueRow->option_id         = $optionId;
								$valueRow->option_value_id   = $optionValueId[$j];
								$valueRow->sku               = $productOptionValueSku[$j];
								$valueRow->quantity          = $productOptionValueQuantity[$j];
								$valueRow->price             = $productOptionValuePrice[$j];
								$valueRow->price_sign        = $productOptionValuePriceSign[$j];
								$valueRow->price_type        = $productOptionValuePriceType[$j];
								$valueRow->weight            = $productOptionValueWeight[$j];
								$valueRow->weight_sign       = $productOptionValueWeightSign[$j];
								$valueRow->shipping          = $productOptionValueShipping[$j];
								$valueRow->published         = $productOptionValuePublished[$j];
								$valueRow->image             = $productOptionValueImage[$j] ?? '';

								if ($productOptionValueIds[$j] > 0 && $input->getInt(
										'remove_image_' . $productOptionValueIds[$j]
									) && !$productOptionValueImageNew[$j])
								{
									$valueRow->image = '';
								}

								$valueRow->store();
							}
						}
					}
					else
					{
						if ($optionTypes[$i] == '1')
						{
							//Delete product option and product option value data
							$query->clear();
							$query->delete('#__eshop_productoptions')
								->where('product_id = ' . intval($productId))
								->where('option_id = ' . intval($optionId));
							$db->setQuery($query);
							$db->execute();

							//Delete some old option values first
							$query->clear();
							$query->delete('#__eshop_productoptionvalues')
								->where('product_id = ' . intval($productId))
								->where('option_id = ' . intval($optionId));
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
				else
				{
					//Delete product option and product option value data
					$query->clear();
					$query->delete('#__eshop_productoptions')
						->where('product_id = ' . intval($productId))
						->where('option_id = ' . intval($optionId));
					$db->setQuery($query);
					$db->execute();

					//Delete product option values
					$query->clear();
					$query->delete('#__eshop_productoptionvalues')
						->where('product_id = ' . intval($productId))
						->where('option_id = ' . intval($optionId));
					$db->setQuery($query);
					$db->execute();
				}
			}

			if (EShopHelper::getConfigValue('assign_same_options'))
			{
				//Assign options to other products
				$sameOptionsProductsId = $input->getInt('same_options_products_id');

				if (isset($sameOptionsProductsId) && count($sameOptionsProductsId))
				{
					if ($sameOptionsProductsId[0] == '-1')
					{
						//Assign to all other products
						$query->clear()
							->select('id')
							->from('#__eshop_products')
							->where('published = 1')
							->where('id != ' . intval($productId));
						$db->setQuery($query);
						$productIds = $db->loadColumn();
					}
					else
					{
						//Only assign to selected products
						$productIds = $sameOptionsProductsId;
					}

					//Delete all of old assigned options for these products
					$query->clear()
						->delete('#__eshop_productoptions')
						->where('product_id IN (' . implode(',', $productIds) . ')');
					$db->setQuery($query);
					$db->execute();

					$query->clear()
						->delete('#__eshop_productoptionvalues')
						->where('product_id IN (' . implode(',', $productIds) . ')');
					$db->setQuery($query);
					$db->execute();

					//Copy data to assign
					foreach ($productIds as $id)
					{
						$sql = 'INSERT INTO #__eshop_productoptions
									(id,
									product_id,
									option_id,
									required
									)
								SELECT
									"0",
									' . $id . ',
									option_id,
									required
								FROM #__eshop_productoptions
								WHERE product_id = ' . intval($productId);
						$db->setQuery($sql);
						$db->execute();

						$sql = 'INSERT INTO #__eshop_productoptionvalues
									(id,
									product_option_id,
									product_id,
									option_id,
									option_value_id,
									sku,
									quantity,
									price,
									price_sign,
									price_type,
									weight,
									weight_sign,
									image
									)
								SELECT
									"0",
									0,
									' . $id . ',
									option_id,
									option_value_id,
									sku,
									quantity,
									price,
									price_sign,
									price_type,
									weight,
									weight_sign,
									image
								FROM #__eshop_productoptionvalues
								WHERE product_id = ' . intval($productId);
						$db->setQuery($sql);
						$db->execute();
					}

					$sql = 'UPDATE #__eshop_productoptionvalues AS pov
							INNER JOIN #__eshop_productoptions AS po ON (pov.product_id = po.product_id AND pov.option_id = po.option_id)
							SET pov.product_option_id = po.id';
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}

		//Store product discounts
		$productDiscountId       = $input->getInt('productdiscount_id', []);
		$discountCustomerGroupId = $input->getInt('discount_customergroup_id', []);
		$discountQuantity        = $input->getInt('discount_quantity', []);
		$discountPriority        = $input->getInt('discount_priority', []);
		$discountPrice           = $input->getFloat('discount_price', []);
		$discountDateStart       = $input->getString('discount_date_start', []);
		$discountDateEnd         = $input->getString('discount_date_end', []);
		$discountPublished       = $input->getInt('discount_published', []);

		//Remove removed discounts first
		$query->clear();
		$query->delete('#__eshop_productdiscounts')
			->where('product_id = ' . intval($productId));

		if (isset($productDiscountId) && count($productDiscountId))
		{
			$query->where('id NOT IN (' . implode(',', $productDiscountId) . ')');
		}

		$db->setQuery($query);
		$db->execute();
		$row = new EShopTable('#__eshop_productdiscounts', 'id', $db);

		if (isset($discountCustomerGroupId) && count($discountCustomerGroupId))
		{
			for ($i = 0; $n = count($discountCustomerGroupId), $i < $n; $i++)
			{
				$row->id               = $productDiscountId[$i] ?? 0;
				$row->product_id       = $productId;
				$row->customergroup_id = $discountCustomerGroupId[$i];
				$row->quantity         = $discountQuantity[$i];
				$row->priority         = $discountPriority[$i];
				$row->price            = $discountPrice[$i];
				$row->date_start       = $discountDateStart[$i] != '' ? $discountDateStart[$i] : '0000-00-00 00:00:00';
				$row->date_end         = $discountDateEnd[$i] != '' ? $discountDateEnd[$i] : '0000-00-00 00:00:00';
				$row->published        = $discountPublished[$i];
				$row->store();
			}
		}

		//Store product specials
		$productSpecialId       = $input->getInt('productspecial_id', []);
		$specialCustomerGroupId = $input->getInt('special_customergroup_id', []);
		$specialPriority        = $input->getInt('special_priority', []);
		$specialPrice           = $input->getFloat('special_price', []);
		$specialDateStart       = $input->getString('special_date_start', []);
		$specialDateEnd         = $input->getString('special_date_end', []);
		$specialPublished       = $input->getInt('special_published', []);

		//Remove removed specials first
		$query->clear();
		$query->delete('#__eshop_productspecials')
			->where('product_id = ' . intval($productId));

		if (isset($productSpecialId) && count($productSpecialId))
		{
			$query->where('id NOT IN (' . implode(',', $productSpecialId) . ')');
		}

		$db->setQuery($query);
		$db->execute();
		$row = new EShopTable('#__eshop_productspecials', 'id', $db);

		if (isset($specialCustomerGroupId) && count($specialCustomerGroupId))
		{
			for ($i = 0; $n = count($specialCustomerGroupId), $i < $n; $i++)
			{
				$row->id               = $productSpecialId[$i] ?? 0;
				$row->product_id       = $productId;
				$row->customergroup_id = $specialCustomerGroupId[$i];
				$row->priority         = $specialPriority[$i];
				$row->price            = $specialPrice[$i];
				$row->date_start       = $specialDateStart[$i] != '' ? $specialDateStart[$i] : '0000-00-00 00:00:00';
				$row->date_end         = $specialDateEnd[$i] != '' ? $specialDateEnd[$i] : '0000-00-00 00:00:00';
				$row->published        = $specialPublished[$i];
				$row->store();
			}
		}

		//Images process
		//Old images
		$productImageId        = $input->getInt('productimage_id', []);
		$productImageOrdering  = $input->getInt('productimage_ordering', []);
		$productImagePublished = $input->getInt('productimage_published', []);
		// Delete image files first
		$query->clear();
		$query->select('image')
			->from('#__eshop_productimages')
			->where('product_id = ' . intval($productId));

		if (isset($productImageId) && count($productImageId))
		{
			$query->where('id NOT IN (' . implode(',', $productImageId) . ')');
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Then delete data
		$query->clear();
		$query->delete('#__eshop_productimages')
			->where('product_id = ' . intval($productId));

		if (isset($productImageId) && count($productImageId))
		{
			$query->where('id NOT IN (' . implode(',', $productImageId) . ')');
		}

		$db->setQuery($query);
		$db->execute();
		$row = new EShopTable('#__eshop_productimages', 'id', $db);

		if (isset($productImageId) && count($productImageId))
		{
			for ($i = 0; $n = count($productImageId), $i < $n; $i++)
			{
				$row->id               = $productImageId[$i];
				$row->product_id       = $productId;
				$row->published        = $productImagePublished[$i];
				$row->ordering         = $productImageOrdering[$i];
				$row->modified_date    = date('Y-m-d H:i:s');
				$row->modified_by      = $user->get('id');
				$row->checked_out      = 0;
				$row->checked_out_time = '0000-00-00 00:00:00';
				$row->store();
			}
		}

		// New images
		if (isset($_FILES['image']))
		{
			$image          = $_FILES['image'];
			$imageOrdering  = $input->getInt('image_ordering');
			$imagePublished = $input->getInt('image_published');

			for ($i = 0; $n = count($image['name']), $i < $n; $i++)
			{
				if (is_uploaded_file($image['tmp_name'][$i]))
				{
					if (is_file($imagePath . $image['name'][$i]))
					{
						$imageFileName = uniqid('image_') . '_' . File::makeSafe($image['name'][$i]);
					}
					else
					{
						$imageFileName = File::makeSafe($image['name'][$i]);
					}

					File::upload($image['tmp_name'][$i], $imagePath . $imageFileName, false, true);
					//Resize image
					EShopHelper::resizeImage($imageFileName, JPATH_ROOT . '/media/com_eshop/products/', 100, 100);

					$row->id               = 0;
					$row->product_id       = $productId;
					$row->image            = $imageFileName;
					$row->published        = $imagePublished[$i];
					$row->ordering         = $imageOrdering[$i];
					$row->created_date     = date('Y-m-d H:i:s');
					$row->created_by       = $user->get('id');
					$row->modified_date    = date('Y-m-d H:i:s');
					$row->modified_by      = $user->get('id');
					$row->checked_out      = 0;
					$row->checked_out_time = '0000-00-00 00:00:00';
					$row->store();
				}
			}
		}

		//Attachments process
		//Old attachments
		$productAttachmentId        = $input->getInt('productattachment_id', []);
		$productAttachmentOrdering  = $input->getInt('productattachment_ordering', []);
		$productAttachmentPublished = $input->getInt('productattachment_published', []);

		// Delete attachments files first
		$query->clear();
		$query->select('file_name')
			->from('#__eshop_productattachments')
			->where('product_id = ' . intval($productId));

		if (isset($productAttachmentId) && count($productAttachmentId))
		{
			$query->where('id NOT IN (' . implode(',', $productAttachmentId) . ')');
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Then delete data
		$query->clear();
		$query->delete('#__eshop_productattachments')
			->where('product_id = ' . intval($productId));

		if (isset($productAttachmentId) && count($productAttachmentId))
		{
			$query->where('id NOT IN (' . implode(',', $productAttachmentId) . ')');
		}

		$db->setQuery($query);
		$db->execute();
		$row = new EShopTable('#__eshop_productattachments', 'id', $db);

		if (isset($productAttachmentId) && count($productAttachmentId))
		{
			for ($i = 0; $n = count($productAttachmentId), $i < $n; $i++)
			{
				$row->id         = $productAttachmentId[$i];
				$row->product_id = $productId;
				$row->published  = $productAttachmentPublished[$i];
				$row->ordering   = $productAttachmentOrdering[$i];
				$row->store();
			}
		}

		// New attachments
		$attachmentPath = JPATH_ROOT . '/media/com_eshop/attachments/';

		if (isset($_FILES['attachment']))
		{
			$attachment          = $_FILES['attachment'];
			$attachmentOrdering  = $input->getInt('attachment_ordering');
			$attachmentPublished = $input->getInt('attachment_published');

			for ($i = 0; $n = count($attachment['name']), $i < $n; $i++)
			{
				if (is_uploaded_file($attachment['tmp_name'][$i]))
				{
					if (is_file($attachmentPath . File::makeSafe($attachment['name'][$i])))
					{
						$attachmentFileName = uniqid('attachment_') . '_' . File::makeSafe($attachment['name'][$i]);
					}
					else
					{
						$attachmentFileName = File::makeSafe($attachment['name'][$i]);
					}

					File::upload($attachment['tmp_name'][$i], $attachmentPath . $attachmentFileName, false, true);
					$row->id         = 0;
					$row->product_id = $productId;
					$row->file_name  = $attachmentFileName;
					$row->published  = $attachmentPublished[$i];
					$row->ordering   = $attachmentOrdering[$i];
					$row->store();
				}
			}
		}

		return true;
	}

	/**
	 * Method to remove products
	 *
	 * @access    public
	 * @return boolean True on success
	 * @since     1.5
	 */
	public function delete($cid = [])
	{
		if (count($cid))
		{
			$db    = $this->getDbo();
			$cids  = implode(',', $cid);
			$query = $db->getQuery(true);

			//Delete images of products from server
			$imageThumbWidth       = EShopHelper::getConfigValue('image_thumb_width');
			$imageThumbHeight      = EShopHelper::getConfigValue('image_thumb_height');
			$imagePopupWidth       = EShopHelper::getConfigValue('image_popup_width');
			$imagePopupHeight      = EShopHelper::getConfigValue('image_popup_height');
			$imageListWidth        = EShopHelper::getConfigValue('image_list_width');
			$imageListHeight       = EShopHelper::getConfigValue('image_list_height');
			$imageCompareWidth     = EShopHelper::getConfigValue('image_compare_width');
			$imageCompareHeight    = EShopHelper::getConfigValue('image_compare_height');
			$imageWishlistWidth    = EShopHelper::getConfigValue('image_wishlist_width');
			$imageWishlistHeight   = EShopHelper::getConfigValue('image_wishlist_height');
			$imageCartWidth        = EShopHelper::getConfigValue('image_cart_width');
			$imageCartHeight       = EShopHelper::getConfigValue('image_cart_height');
			$imageAdditionalWidth  = EShopHelper::getConfigValue('image_additional_width');
			$imageAdditionalHeight = EShopHelper::getConfigValue('image_additional_height');
			$imagePath             = JPATH_ROOT . '/media/com_eshop/products/';

			//Delete main images first
			$query->select('product_image')
				->from('#__eshop_products')
				->where('id IN (' . implode(',', $cid) . ')')
				->where('product_image != ""');
			$db->setQuery($query);
			$productImages = $db->loadColumn();

			if (count($productImages))
			{
				$imageSizesArr = [
					'100x100',
					$imageThumbWidth . 'x' . $imageThumbHeight,
					$imagePopupWidth . 'x' . $imagePopupHeight,
					$imageListWidth . 'x' . $imageListHeight,
					$imageCompareWidth . 'x' . $imageCompareHeight,
					$imageWishlistWidth . 'x' . $imageWishlistHeight,
					$imageCartWidth . 'x' . $imageCartHeight,
					$imageAdditionalWidth . 'x' . $imageAdditionalHeight,
				];

				foreach ($productImages as $image)
				{
					//Delete orginal image
					if (is_file($imagePath . $image))
					{
						File::delete($imagePath . $image);
					}

					$name = File::stripExt($image);
					$ext  = EShopHelper::getFileExt($image);

					//Delete resized images
					foreach ($imageSizesArr as $size)
					{
						if (is_file($imagePath . 'resized/' . $name . '-' . $size . '.' . $ext))
						{
							File::delete($imagePath . 'resized/' . $name . '-' . $size . '.' . $ext);
						}

						if (is_file($imagePath . 'resized/' . $name . '-cr-' . $size . '.' . $ext))
						{
							File::delete($imagePath . 'resized/' . $name . '-cr-' . $size . '.' . $ext);
						}

						if (is_file($imagePath . 'resized/' . $name . '-max-' . $size . '.' . $ext))
						{
							File::delete($imagePath . 'resized/' . $name . '-max-' . $size . '.' . $ext);
						}
					}
				}
			}

			//Delete additional images
			$query->clear();
			$query->select('image')
				->from('#__eshop_productimages')
				->where('product_id IN (' . implode(',', $cid) . ')')
				->where('image != ""');
			$db->setQuery($query);
			$images = $db->loadColumn();

			if (count($images))
			{
				$imageSizesArr = [
					'100x100',
					$imageAdditionalWidth . 'x' . $imageAdditionalHeight,
					$imageThumbWidth . 'x' . $imageThumbHeight,
					$imagePopupWidth . 'x' . $imagePopupHeight,
				];

				foreach ($images as $image)
				{
					//Delete orginal image
					if (is_file($imagePath . $image))
					{
						File::delete($imagePath . $image);
					}

					$name = File::stripExt($image);
					$ext  = EShopHelper::getFileExt($image);

					//Delete resized images
					foreach ($imageSizesArr as $size)
					{
						if (is_file($imagePath . 'resized/' . $name . '-' . $size . '.' . $ext))
						{
							File::delete($imagePath . 'resized/' . $name . '-' . $size . '.' . $ext);
						}

						if (is_file($imagePath . 'resized/' . $name . '-cr-' . $size . '.' . $ext))
						{
							File::delete($imagePath . 'resized/' . $name . '-cr-' . $size . '.' . $ext);
						}

						if (is_file($imagePath . 'resized/' . $name . '-max-' . $size . '.' . $ext))
						{
							File::delete($imagePath . 'resized/' . $name . '-max-' . $size . '.' . $ext);
						}
					}
				}
			}

			$query->clear();
			$query->delete('#__eshop_products')
				->where('id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			// Delete details records
			$query->clear();
			$query->delete('#__eshop_productdetails')
				->where('product_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete product attributes
			$query->clear();
			$query->delete('#__eshop_productattributes')
				->where('product_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete product attribute details
			$query->clear();
			$query->delete('#__eshop_productattributedetails')
				->where('product_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete product categories
			$query->clear();
			$query->delete('#__eshop_productcategories')
				->where('product_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete product discounts
			$query->clear();
			$query->delete('#__eshop_productdiscounts')
				->where('product_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete product images
			$query->clear();
			$query->delete('#__eshop_productimages')
				->where('product_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete product options
			$query->clear();
			$query->delete('#__eshop_productoptions')
				->where('product_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete product option values
			$query->clear();
			$query->delete('#__eshop_productoptionvalues')
				->where('product_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete product relations
			$query->clear();
			$query->delete('#__eshop_productrelations')
				->where('product_id IN (' . implode(',', $cid) . ') OR related_product_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete product specials
			$query->clear();
			$query->delete('#__eshop_productspecials')
				->where('product_id IN (' . implode(',', $cid) . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				//Removed error
				return 0;
			}

			//Delete product labels
			$query->clear();
			$query = $db->getQuery(true);
			$query->delete('#__eshop_labelelements')
				->where('element_id IN (' . implode(',', $cid) . ')')
				->where('element_type = "product"');
			$db->setQuery($query);
			$db->execute();

			//Delete SEF urls to products
			for ($i = 0; $n = count($cid), $i < $n; $i++)
			{
				$query->clear();
				$query->delete('#__eshop_urls')
					->where('query LIKE "view=product&id=' . $cid[$i] . '&catid=%"');
				$db->setQuery($query);
				$db->execute();
			}
		}

		//Removed success
		return 1;
	}

	/**
	 *
	 * Function to featured products
	 *
	 * @param   array  $cid
	 *
	 * @return boolean
	 */
	public function featured($cid)
	{
		if (count($cid))
		{
			$db    = $this->getDbo();
			$cids  = implode(',', $cid);
			$query = $db->getQuery(true);
			$query->update('#__eshop_products')
				->set('product_featured = 1')
				->where('id IN (' . $cids . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 *
	 * Function to unfeatured products
	 *
	 * @param   array  $cid
	 *
	 * @return boolean
	 */
	public function unfeatured($cid)
	{
		if (count($cid))
		{
			$db    = $this->getDbo();
			$cids  = implode(',', $cid);
			$query = $db->getQuery(true);
			$query->update('#__eshop_products')
				->set('product_featured = 0')
				->where('id IN (' . $cids . ')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Function to copy product and related data
	 * @see EShopModel::copy()
	 */
	public function copy($id)
	{
		$copiedProductId = parent::copy($id);
		$db              = $this->getDbo();
		$query           = $db->getQuery(true);

		//Categories
		$query->select('COUNT(*)')
			->from('#__eshop_productcategories')
			->where('product_id = ' . intval($id));
		$db->setQuery($query);

		if ($db->loadResult())
		{
			$sql = 'INSERT INTO #__eshop_productcategories'
				. ' (product_id, category_id, main_category)'
				. ' SELECT ' . $copiedProductId . ', category_id, main_category'
				. ' FROM #__eshop_productcategories'
				. ' WHERE product_id = ' . intval($id);
			$db->setQuery($sql);
			$db->execute();
		}

		//Additional images
		$query->clear();
		$query->select('*')
			->from('#__eshop_productimages')
			->where('product_id = ' . intval($id));
		$db->setQuery($query);
		$additionalImages = $db->loadObjectList();

		for ($i = 0; $n = count($additionalImages), $i < $n; $i++)
		{
			$additionalImage = $additionalImages[$i];
			$oldImage        = $additionalImage->image;

			if ($additionalImage->image != '' && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $oldImage))
			{
				$newImage = File::stripExt($oldImage) . ' ' . Text::_('ESHOP_COPY') . '.' . EShopHelper::getFileExt($oldImage);

				if (File::copy(JPATH_ROOT . '/media/com_eshop/products/' . $oldImage, JPATH_ROOT . '/media/com_eshop/products/' . $newImage))
				{
					//resize copied image
					EShopHelper::resizeImage($newImage, JPATH_ROOT . '/media/com_eshop/products/', 100, 100);
				}
				else
				{
					$newImage = $oldImage;
				}
			}

			$row                   = new EShopTable('#__eshop_productimages', 'id', $db);
			$row->id               = 0;
			$row->product_id       = $copiedProductId;
			$row->image            = $newImage;
			$row->published        = $additionalImage->published;
			$row->ordering         = $additionalImage->ordering;
			$row->modified_date    = $additionalImage->modified_date;
			$row->modified_by      = $additionalImage->modified_by;
			$row->checked_out      = 0;
			$row->checked_out_time = '0000-00-00 00:00:00';
			$row->store();
		}

		//Attributes
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eshop_productattributes')
			->where('product_id = ' . intval($id));
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		for ($i = 0; $n = count($rows), $i < $n; $i++)
		{
			$row                                = $rows[$i];
			$productAttributesRow               = new EShopTable('#__eshop_productattributes', 'id', $db);
			$productAttributesRow->id           = 0;
			$productAttributesRow->product_id   = $copiedProductId;
			$productAttributesRow->attribute_id = $row->attribute_id;
			$productAttributesRow->published    = $row->published;
			$productAttributesRow->store();
			$productAttributesId = $productAttributesRow->id;
			$sql                 = 'INSERT INTO #__eshop_productattributedetails'
				. ' (productattribute_id, product_id, value, language)'
				. ' SELECT ' . $productAttributesId . ', ' . $copiedProductId . ', value, language'
				. ' FROM #__eshop_productattributedetails'
				. ' WHERE productattribute_id = ' . intval($row->id);
			$db->setQuery($sql);
			$db->execute();
		}

		//Discounts
		$query->clear();
		$query->select('COUNT(*)')
			->from('#__eshop_productdiscounts')
			->where('product_id = ' . intval($id));
		$db->setQuery($query);

		if ($db->loadResult())
		{
			$sql = 'INSERT INTO #__eshop_productdiscounts'
				. ' (product_id, customergroup_id, quantity, priority, price, date_start, date_end, published)'
				. ' SELECT ' . $copiedProductId . ', customergroup_id, quantity, priority, price, date_start, date_end, published'
				. ' FROM #__eshop_productdiscounts'
				. ' WHERE product_id = ' . intval($id);
			$db->setQuery($sql);
			$db->execute();
		}

		//Specials
		$query->clear();
		$query->select('COUNT(*)')
			->from('#__eshop_productspecials')
			->where('product_id = ' . intval($id));
		$db->setQuery($query);

		if ($db->loadResult())
		{
			$sql = 'INSERT INTO #__eshop_productspecials'
				. ' (product_id, customergroup_id, priority, price, date_start, date_end, published)'
				. ' SELECT ' . $copiedProductId . ', customergroup_id, priority, price, date_start, date_end, published'
				. ' FROM #__eshop_productspecials'
				. ' WHERE product_id = ' . intval($id);
			$db->setQuery($sql);
			$db->execute();
		}

		//Options
		$query->clear();
		$query->select('*')
			->from('#__eshop_productoptions')
			->where('product_id = ' . intval($id))
			->order('id');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		for ($i = 0; $n = count($rows), $i < $n; $i++)
		{
			$row                           = $rows[$i];
			$productOptionsRow             = new EShopTable('#__eshop_productoptions', 'id', $db);
			$productOptionsRow->id         = 0;
			$productOptionsRow->product_id = $copiedProductId;
			$productOptionsRow->option_id  = $row->option_id;
			$productOptionsRow->required   = $row->required;
			$productOptionsRow->store();
			$productOptionsId = $productOptionsRow->id;
			$sql              = 'INSERT INTO #__eshop_productoptionvalues'
				. ' (product_option_id, product_id, option_id, option_value_id, sku, quantity, price, price_sign, price_type, weight, weight_sign, image)'
				. ' SELECT ' . $productOptionsId . ', ' . $copiedProductId . ', option_id, option_value_id, sku, quantity, price, price_sign, price_type, weight, weight_sign, image'
				. ' FROM #__eshop_productoptionvalues'
				. ' WHERE product_option_id = ' . intval($row->id)
				. ' ORDER BY id';
			$db->setQuery($sql);
			$db->execute();
		}

		return $copiedProductId;
	}
}