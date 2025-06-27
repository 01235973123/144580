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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewProduct extends EShopViewForm
{
	/**
	 *
	 * @var $productAttributes
	 */
	protected $productAttributes;

	/**
	 *
	 * @var $productImages
	 */
	protected $productImages;

	/**
	 *
	 * @var $productAttachments
	 */
	protected $productAttachments;

	/**
	 *
	 * @var $notProductOptions
	 */
	protected $notProductOptions;

	/**
	 *
	 * @var $productOptions
	 */
	protected $productOptions;

	/**
	 *
	 * @var $productOptionValues
	 */
	protected $productOptionValues;

	/**
	 *
	 * @var $options
	 */
	protected $options;

	/**
	 *
	 * @var $productDiscounts
	 */
	protected $productDiscounts;

	/**
	 *
	 * @var $productSpecials
	 */
	protected $productSpecials;

	/**
	 *
	 * @var $nullDate
	 */
	protected $nullDate;

	/**
	 *
	 * @var $form
	 */
	protected $form;

	/**
	 *
	 * @var $plugins
	 */
	protected $plugins;

	public function _buildListArray(&$lists, $item)
	{
		$db       = Factory::getDbo();
		$query    = $db->getQuery(true);
		$nullDate = $db->getNullDate();

		//Build AcyMailing list
		if (EShopHelper::getConfigValue('acymailing_integration') && is_file(
				JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php'
			))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';
			$params                       = new Registry($item->params ?? '{}');
			$listIds                      = explode(',', $params->get('acymailing_list_ids', ''));
			$listClass                    = acymailing_get('class.list');
			$allLists                     = $listClass->getLists();
			$lists['acymailing_list_ids'] = HTMLHelper::_(
				'select.genericlist',
				$allLists,
				'acymailing_list_ids[]',
				'class="input-xlarge form-select" multiple="multiple" size="10"',
				'listid',
				'name',
				$listIds
			);
		}

		//Build MailChimp list
		if (EShopHelper::getConfigValue('mailchimp_integration') && EShopHelper::getConfigValue('api_key_mailchimp') != '')
		{
			require_once JPATH_SITE . '/components/com_eshop/helpers/MailChimp.php';
			$mailchimp  = new MailChimp(EShopHelper::getConfigValue('api_key_mailchimp'));
			$listsChimp = $mailchimp->get('lists', ['count' => 1000]);

			if ($listsChimp === false)
			{
				$lists['mailchimp_list_ids'] = '';
			}
			else
			{
				$params  = new Registry($item->params ?? '{}');
				$listIds = explode(',', $params->get('mailchimp_list_ids', ''));
				$options = [];

				foreach ($listsChimp['lists'] as $listChimp)
				{
					$options[] = HTMLHelper::_('select.option', $listChimp['id'], $listChimp['name']);
				}

				$lists['mailchimp_list_ids'] = HTMLHelper::_(
					'select.genericlist',
					$options,
					'mailchimp_list_ids[]',
					'class="input-xlarge form-select" multiple="multiple" size="10"',
					'value',
					'text',
					$listIds
				);
			}
		}

		//Build manufacturer list
		$query->select('a.id AS value, b.manufacturer_name AS text')
			->from('#__eshop_manufacturers AS a')
			->innerJoin('#__eshop_manufacturerdetails AS b ON (a.id = b.manufacturer_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('a.ordering');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'value', 'text');

		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}

		$lists['manufacturer'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'manufacturer_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select chosen" ',
				'list.select'        => $item->manufacturer_id,
			]
		);

		$images = Folder::files(JPATH_ROOT . '/media/com_eshop/products');
		sort($images);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('ESHOP_NONE'));
		for ($i = 0, $n = count($images); $i < $n; $i++)
		{
			$image     = $images[$i];
			$options[] = HTMLHelper::_('select.option', $image, $image);
		}
		$lists['existed_image'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'existed_image',
			'class="input-xlarge form-select chosen"',
			'value',
			'text',
			$item->product_image ?? ''
		);

		//Build categories list
		$query->clear();
		$query->select('a.id, b.category_name AS title, a.category_parent_id AS parent_id')
			->from('#__eshop_categories AS a')
			->innerJoin('#__eshop_categorydetails AS b ON (a.id = b.category_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$children = [];

		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : [];
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		$list      = HTMLHelper::_('menu.treerecurse', 0, '', [], $children, 9999, 0, 0);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_SELECT_CATEGORY'));

		foreach ($list as $listItem)
		{
			$options[] = HTMLHelper::_('select.option', $listItem->id, $listItem->treename);
		}

		if ($item->id)
		{
			$query->clear();
			$query->select('category_id')
				->from('#__eshop_productcategories')
				->where('product_id = ' . $item->id)
				->where('main_category = 1');
			$db->setQuery($query);
			$mainCategoryId = $db->loadResult();
			$query->clear();
			$query->select('category_id')
				->from('#__eshop_productcategories')
				->where('product_id = ' . $item->id)
				->where('main_category != 1');
			$db->setQuery($query);
			$additionalCategories = $db->loadColumn();
		}
		else
		{
			$mainCategoryId       = 0;
			$additionalCategories = [0];
		}

		$lists['main_category_id'] = HTMLHelper::_('select.genericlist', $options, 'main_category_id', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="input-xlarge form-select chosen"',
			'list.select'        => $mainCategoryId,
		]);

		array_shift($options);

		$lists['category_id'] = HTMLHelper::_('select.genericlist', $options, 'category_id[]', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"="multiple"',
			'list.select'        => $additionalCategories,
		]);

		//Build customer groups list
		$query->clear();
		$query->select('a.id AS value, b.customergroup_name AS text')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.customergroup_name');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		if ($item->product_customergroups != '')
		{
			$selectedItems = explode(',', $item->product_customergroups);
		}
		else
		{
			$selectedItems = [];
		}

		$lists['product_customergroups'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_customergroups[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
				'list.select'        => $selectedItems,
			]
		);

		//Build Lengths list
		$query->clear();
		$query->select('a.id, b.length_name')
			->from('#__eshop_lengths AS a')
			->innerJoin('#__eshop_lengthdetails AS b ON (a.id = b.length_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$lists['product_length_id'] = HTMLHelper::_(
			'select.genericlist',
			$db->loadObjectList(),
			'product_length_id',
			'class="input-xlarge form-select"',
			'id',
			'length_name',
			$item->product_length_id
		);

		//Build Weights list
		$query->clear();
		$query->select('a.id, b.weight_name')
			->from('#__eshop_weights AS a')
			->innerJoin('#__eshop_weightdetails AS b ON (a.id = b.weight_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$lists['product_weight_id'] = HTMLHelper::_(
			'select.genericlist',
			$db->loadObjectList(),
			'product_weight_id',
			'class="input-xlarge form-select"',
			'id',
			'weight_name',
			$item->product_weight_id
		);

		//Build downloads
		$query->clear();
		$query->select('a.id AS value, b.download_name AS text')
			->from('#__eshop_downloads AS a')
			->innerJoin('#__eshop_downloaddetails AS b ON (a.id = b.download_id)')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows                = $db->loadObjectList();
		$productDownloads    = EShopHelper::getProductDownloads($item->id);
		$productDownloadsArr = [];

		for ($i = 0; $n = count($productDownloads), $i < $n; $i++)
		{
			$productDownloadsArr[] = $productDownloads[$i]->id;
		}

		if (count($rows))
		{
			$lists['product_downloads'] = HTMLHelper::_(
				'select.genericlist',
				$rows,
				'product_downloads_id[]',
				[
					'option.text.toHtml' => false,
					'option.text'        => 'text',
					'option.value'       => 'value',
					'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
					'list.select'        => $productDownloadsArr,
				]
			);
		}
		else
		{
			$lists['product_downloads'] = '';
		}

		//Build related products list
		$query->clear();
		$query->select('a.id AS value, b.product_name AS text')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.product_name');

		if ($item->id)
		{
			$query->where('a.id != ' . $item->id);
		}

		$query->order('a.ordering');
		$db->setQuery($query);
		$rows                = $db->loadObjectList();
		$productRelations    = EShopHelper::getProductRelations($item->id);
		$productRelationsArr = [];

		for ($i = 0; $n = count($productRelations), $i < $n; $i++)
		{
			$productRelationsArr[] = $productRelations[$i]->id;
		}

		if (count($rows))
		{
			$lists['related_products'] = HTMLHelper::_(
				'select.genericlist',
				$rows,
				'related_product_id[]',
				[
					'option.text.toHtml' => false,
					'option.text'        => 'text',
					'option.value'       => 'value',
					'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
					'list.select'        => $productRelationsArr,
				]
			);

			if (EShopHelper::getConfigValue('assign_same_options'))
			{
				$options                        = [];
				$options[]                      = HTMLHelper::_('select.option', '-1', '-- ' . Text::_('ESHOP_ALL_PRODUCTS') . ' --');
				$options                        = array_merge($options, $rows);
				$lists['same_options_products'] = HTMLHelper::_(
					'select.genericlist',
					$options,
					'same_options_products_id[]',
					[
						'option.text.toHtml' => false,
						'option.text'        => 'text',
						'option.value'       => 'value',
						'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
						'list.select'        => [],
					]
				);
			}
		}
		else
		{
			$lists['related_products'] = '';

			if (EShopHelper::getConfigValue('assign_same_options'))
			{
				$lists['same_options_products'] = '';
			}
		}

		$lists['relate_product_to_category'] = EShopHtmlHelper::getBooleanInput('relate_product_to_category', 0);

		//Build attributes list
		$query->clear();
		$query->select('a.id, b.attributegroup_name')
			->from('#__eshop_attributegroups AS a')
			->innerJoin('#__eshop_attributegroupdetails AS b ON (a.id = b.attributegroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('a.ordering');
		$db->setQuery($query);
		$attributeGroups    = $db->loadObjectList();
		$attributeGroupsArr = [];

		for ($i = 0; $n = count($attributeGroups), $i < $n; $i++)
		{
			$query->clear();
			$query->select('a.id, b.attribute_name')
				->from('#__eshop_attributes AS a')
				->innerJoin('#__eshop_attributedetails AS b ON (a.id = b.attribute_id)')
				->where('a.attributegroup_id = ' . intval($attributeGroups[$i]->id))
				->where('a.published = 1')
				->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
				->order('a.ordering');
			$db->setQuery($query);
			$attributes = $db->loadObjectList();

			if (count($attributes))
			{
				$attributeGroupsArr[addcslashes($attributeGroups[$i]->attributegroup_name, "'")] = [];
			}
			for ($j = 0; $m = count($attributes), $j < $m; $j++)
			{
				$attributeGroupsArr[addcslashes($attributeGroups[$i]->attributegroup_name, "'")][] = HTMLHelper::_(
					'select.option',
					$attributes[$j]->id,
					addcslashes($attributes[$j]->attribute_name, "'")
				);
			}
		}

		$lists['attributes'] = HTMLHelper::_(
			'select.groupedlist',
			$attributeGroupsArr,
			'attribute_id[]',
			[
				'list.attr'          => 'class="input-xlarge form-select"',
				'id'                 => 'attribute_id',
				'list.select'        => null,
				'group.items'        => null,
				'option.key.toHtml'  => false,
				'option.text.toHtml' => false,
			]
		);
		$productAttributes   = EShopHelper::getProductAttributes($item->id);

		for ($i = 0; $n = count($productAttributes), $i < $n; $i++)
		{
			$productAttribute                             = $productAttributes[$i];
			$lists['attributes_' . $productAttribute->id] = HTMLHelper::_(
				'select.groupedlist',
				$attributeGroupsArr,
				'attribute_id[]',
				[
					'list.attr'          => 'class="input-xlarge form-select"',
					'id'                 => 'attribute_id_' . $productAttribute->id,
					'list.select'        => $productAttribute->id,
					'group.items'        => null,
					'option.key.toHtml'  => false,
					'option.text.toHtml' => false,
				]
			);
		}

		$this->productAttributes = $productAttributes;
		$this->productImages     = EShopHelper::getProductImages($item->id, 0);

		//Get data product attachments
		$query->clear();
		$query->select('*')
			->from('#__eshop_productattachments')
			->where('product_id = ' . intval($item->id))
			->order('ordering');
		$db->setQuery($query);
		$this->productAttachments = $db->loadObjectList();

		//Build options list
		$query->clear();
		$query->select('a.id AS value, b.option_name AS text, a.id AS id, a.option_type, a.option_image, b.option_name, b.option_desc')
			->from('#__eshop_options AS a')
			->innerJoin('#__eshop_optiondetails AS b ON (a.id = b.option_id)')
			->where('a.published = 1')
			->where('a.id NOT IN (SELECT option_id FROM #__eshop_productoptions WHERE product_id = ' . ($item->id ?: 0) . ')')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('a.ordering');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'value', 'text');

		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}

		$this->notProductOptions = $rows;

		//Build options type list
		$query->clear();
		$query->select('id AS value, option_type AS text')
			->from('#__eshop_options')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		$lists['options_type'] = HTMLHelper::_(
			'select.genericlist',
			$db->loadObjectList(),
			'option_type_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" style="display: none;"',
			]
		);

		//Build product options data
		$productOptions       = EShopHelper::getProductOptions($item->id);
		$this->productOptions = $productOptions;
		$productOptionValues  = [];

		for ($i = 0; $n = count($productOptions), $i < $n; $i++)
		{
			$productOptionValues[] = EShopHelper::getProductOptionValues($item->id, $productOptions[$i]->id);
		}

		$this->productOptionValues = $productOptionValues;

		//Build option values data
		$query->clear();
		$query->select('o.id, o.option_type, o.option_image, od.option_name, od.option_desc')
			->from('#__eshop_options AS o')
			->innerJoin('#__eshop_optiondetails AS od ON (o.id = od.option_id)')
			->where('o.published = 1')
			->order('o.ordering');
		$db->setQuery($query);
		$rows          = $db->loadObjectList();
		$this->options = $rows;
		$subRows       = [];

		for ($i = 0; $n = count($rows), $i < $n; $i++)
		{
			$query->clear();
			$query->select('a.id AS value, b.value AS text')
				->from('#__eshop_optionvalues AS a')
				->innerJoin('#__eshop_optionvaluedetails AS b ON (a.id = b.optionvalue_id)')
				->where('a.option_id = ' . intval($rows[$i]->id))
				->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
				->order('a.ordering');
			$db->setQuery($query);

			$subRows[$rows[$i]->id]                  = $db->loadObjectList();
			$lists['option_values_' . $rows[$i]->id] = HTMLHelper::_(
				'select.genericlist',
				$subRows[$rows[$i]->id],
				'option_values_' . $rows[$i]->id,
				[
					'option.text.toHtml' => false,
					'option.value'       => 'value',
					'option.text'        => 'text',
					'list.attr'          => ' class="input-large form-select" style="display: none;" ',
				]
			);
		}

		$signOptions      	= [];
		$signOptions[]     	= HTMLHelper::_('select.option', '+', '+');
		$signOptions[]     	= HTMLHelper::_('select.option', '-', '-');
		$typeOptions       	= [];
		$typeOptions[]     	= HTMLHelper::_('select.option', 'F', Text::_('ESHOP_FIXED_AMOUNT'));
		$typeOptions[]     	= HTMLHelper::_('select.option', 'P', Text::_('ESHOP_PERCENTAGE'));
		$shippingOptions   	= [];
		$shippingOptions[] 	= HTMLHelper::_('select.option', '1', Text::_('ESHOP_YES'));
		$shippingOptions[]	= HTMLHelper::_('select.option', '0', Text::_('ESHOP_NO'));
		$publishedOptions  	= [];
		$publishedOptions[]	= HTMLHelper::_('select.option', '1', Text::_('ESHOP_YES'));
		$publishedOptions[]	= HTMLHelper::_('select.option', '0', Text::_('ESHOP_NO'));

		$lists['price_sign'] = HTMLHelper::_(
			'select.genericlist',
			$signOptions,
			'price_sign',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-mini form-select" style="display: none;" ',
			]
		);

		$lists['price_sign_visible'] = HTMLHelper::_(
			'select.genericlist',
			$signOptions,
			'price_sign',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-mini form-select" ',
			]
		);

		$lists['price_type'] = HTMLHelper::_(
			'select.genericlist',
			$typeOptions,
			'price_type',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-medium form-select" style="display: none;" ',
			]
		);

		$lists['price_type_visible'] = HTMLHelper::_(
			'select.genericlist',
			$typeOptions,
			'price_type',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-medium form-select" ',
			]
		);

		$lists['weight_sign'] = HTMLHelper::_(
			'select.genericlist',
			$signOptions,
			'weight_sign',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-mini form-select" style="display: none;" ',
			]
		);

		$lists['shipping'] = HTMLHelper::_(
			'select.genericlist',
			$shippingOptions,
			'shipping',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" style="display: none;" ',
			]
		);
		
		$lists['option_value_published'] = HTMLHelper::_(
			'select.genericlist',
			$publishedOptions,
			'option_value_published',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => ' class="input-xlarge form-select" style="display: none;" ',
			]
			);

		for ($i = 0; $n = count($productOptions), $i < $n; $i++)
		{
			$productOptionValues = EShopHelper::getProductOptionValues($item->id, $productOptions[$i]->id);

			if ($productOptions[$i]->option_type == 'Text' || $productOptions[$i]->option_type == 'Textarea')
			{
				$j                                                = 0;
				$lists['price_sign_t_' . $productOptions[$i]->id] = HTMLHelper::_(
					'select.genericlist',
					$signOptions,
					'product_option_value_' . $productOptions[$i]->id . '_price_sign[]',
					[
						'option.text.toHtml' => false,
						'option.value'       => 'value',
						'option.text'        => 'text',
						'list.attr'          => ' class="input-mini form-select" ',
						'list.select'        => $productOptionValues[$j]->price_sign,
					]
				);
				$lists['price_type_t_' . $productOptions[$i]->id] = HTMLHelper::_(
					'select.genericlist',
					$typeOptions,
					'product_option_value_' . $productOptions[$i]->id . '_price_type[]',
					[
						'option.text.toHtml' => false,
						'option.value'       => 'value',
						'option.text'        => 'text',
						'list.attr'          => ' class="input-medium form-select" ',
						'list.select'        => $productOptionValues[$j]->price_type,
					]
				);
				continue;
			}

			for ($j = 0; $m = count($productOptionValues), $j < $m; $j++)
			{
				if (!isset($subRows[$productOptions[$i]->id]))
				{
					$query->clear();
					$query->select('a.id AS value, b.value AS text')
						->from('#__eshop_optionvalues AS a')
						->innerJoin('#__eshop_optionvaluedetails AS b ON (a.id = b.optionvalue_id)')
						->where('a.option_id = ' . intval($productOptions[$i]->id))
						->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
						->order('a.ordering');
					$db->setQuery($query);
					$subRows[$productOptions[$i]->id] = $db->loadObjectList();
				}

				$lists['option_value_' . $productOptionValues[$j]->id] = HTMLHelper::_(
					'select.genericlist',
					$subRows[$productOptions[$i]->id],
					'option_value_' . $productOptions[$i]->id . '_id[]',
					[
						'option.text.toHtml' => false,
						'option.value'       => 'value',
						'option.text'        => 'text',
						'list.attr'          => 'class="input-large form-select"',
						'list.select'        => $productOptionValues[$j]->option_value_id,
					]
				);

				$lists['price_sign_' . $productOptionValues[$j]->id] = HTMLHelper::_(
					'select.genericlist',
					$signOptions,
					'product_option_value_' . $productOptions[$i]->id . '_price_sign[]',
					[
						'option.text.toHtml' => false,
						'option.value'       => 'value',
						'option.text'        => 'text',
						'list.attr'          => 'class="input-mini form-select"',
						'list.select'        => $productOptionValues[$j]->price_sign,
					]
				);

				$lists['price_type_' . $productOptionValues[$j]->id] = HTMLHelper::_(
					'select.genericlist',
					$typeOptions,
					'product_option_value_' . $productOptions[$i]->id . '_price_type[]',
					[
						'option.text.toHtml' => false,
						'option.value'       => 'value',
						'option.text'        => 'text',
						'list.attr'          => 'class="input-medium form-select"',
						'list.select'        => $productOptionValues[$j]->price_type,
					]
				);

				$lists['weight_sign_' . $productOptionValues[$j]->id] = HTMLHelper::_(
					'select.genericlist',
					$signOptions,
					'product_option_value_' . $productOptions[$i]->id . '_weight_sign[]',
					[
						'option.text.toHtml' => false,
						'option.value'       => 'value',
						'option.text'        => 'text',
						'list.attr'          => 'class="input-mini form-select"',
						'list.select'        => $productOptionValues[$j]->weight_sign,
					]
				);

				$lists['shipping_' . $productOptionValues[$j]->id] = HTMLHelper::_(
					'select.genericlist',
					$shippingOptions,
					'product_option_value_' . $productOptions[$i]->id . '_shipping[]',
					[
						'option.text.toHtml' => false,
						'option.value'       => 'value',
						'option.text'        => 'text',
						'list.attr'          => 'class="input-mini form-select"',
						'list.select'        => $productOptionValues[$j]->shipping,
					]
				);
				
				$lists['option_value_published_' . $productOptionValues[$j]->id] = HTMLHelper::_(
					'select.genericlist',
					$publishedOptions,
					'product_option_value_' . $productOptions[$i]->id . '_published[]',
					[
						'option.text.toHtml' => false,
						'option.value'       => 'value',
						'option.text'        => 'text',
						'list.attr'          => 'class="input-mini form-select"',
						'list.select'        => $productOptionValues[$j]->published,
					]
					);
			}
		}

		// Get product tags
		$productTags    = EShopHelper::getProductTags($item->id);
		$productTagsArr = [];

		if (count($productTags))
		{
			for ($i = 0; $n = count($productTags), $i < $n; $i++)
			{
				$productTagsArr[] = $productTags[$i]->tag_name;
			}
			$item->product_tags = implode(',', $productTagsArr);
		}
		else
		{
			$item->product_tags = '';
		}

		//Discounts and Specials
		$this->productDiscounts = EShopHelper::getProductDiscounts($item->id);
		$this->productSpecials  = EShopHelper::getProductSpecials($item->id);

		//Build customer groups list
		$query->clear();
		$query->select('a.id AS value, b.customergroup_name AS text')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.customergroup_name');
		$db->setQuery($query);

		$customerGroups                   = $db->loadObjectList();
		$lists['discount_customer_group'] = HTMLHelper::_(
			'select.genericlist',
			$customerGroups,
			'discount_customergroup_id[]',
			['option.text.toHtml' => false, 'option.value' => 'value', 'option.text' => 'text', 'list.attr' => 'class="input-large form-select"']
		);
		$lists['special_customer_group']  = HTMLHelper::_(
			'select.genericlist',
			$customerGroups,
			'special_customergroup_id[]',
			['option.text.toHtml' => false, 'option.value' => 'value', 'option.text' => 'text', 'list.attr' => 'class="input-large form-select"']
		);

		for ($i = 0; $n = count($this->productDiscounts), $i < $n; $i++)
		{
			$productDiscount                                          = $this->productDiscounts[$i];
			$lists['discount_customer_group_' . $productDiscount->id] = HTMLHelper::_(
				'select.genericlist',
				$customerGroups,
				'discount_customergroup_id[]',
				[
					'option.text.toHtml' => false,
					'option.value'       => 'value',
					'option.text'        => 'text',
					'list.attr'          => 'class="input-medium form-select"',
					'list.select'        => $productDiscount->customergroup_id,
				]
			);
		}

		for ($i = 0; $n = count($this->productSpecials), $i < $n; $i++)
		{
			$productSpecial                                         = $this->productSpecials[$i];
			$lists['special_customer_group_' . $productSpecial->id] = HTMLHelper::_(
				'select.genericlist',
				$customerGroups,
				'special_customergroup_id[]',
				[
					'option.text.toHtml' => false,
					'option.value'       => 'value',
					'option.text'        => 'text',
					'list.attr'          => 'class="input-large form-select"',
					'list.select'        => $productSpecial->customergroup_id,
				]
			);
		}

		parent::_buildListArray($lists, $item);

		//Build inventory lists
		$lists['product_manage_stock']      = EShopHtmlHelper::getBooleanInput(
			'product_manage_stock',
			$item->product_manage_stock ?? '1'
		);
		$lists['product_stock_display']     = EShopHtmlHelper::getBooleanInput(
			'product_stock_display',
			$item->product_stock_display ?? '1'
		);
		$lists['product_show_availability'] = EShopHtmlHelper::getBooleanInput(
			'product_show_availability',
			$item->product_show_availability ?? '1'
		);
		$lists['product_stock_warning']     = EShopHtmlHelper::getBooleanInput(
			'product_stock_warning',
			$item->product_stock_warning ?? '1'
		);
		$lists['product_inventory_global']  = EShopHtmlHelper::getBooleanInput(
			'product_inventory_global',
			$item->product_inventory_global ?? '1'
		);
		$lists['product_stock_checkout']    = EShopHtmlHelper::getBooleanInput(
			'product_stock_checkout',
			$item->id ? $item->product_stock_checkout : '0'
		);

		//Build shipping list
		$lists['product_shipping'] = EShopHtmlHelper::getBooleanInput('product_shipping', $item->id ? $item->product_shipping : '1');

		//Build featured  list
		$lists['featured'] = EShopHtmlHelper::getBooleanInput('product_featured', $item->product_featured);

		//Build call for price list
		$lists['product_call_for_price'] = EShopHtmlHelper::getBooleanInput('product_call_for_price', $item->product_call_for_price);

		//Build taxclasses list
		$query->clear();
		$query->select('id AS value, taxclass_name AS text')
			->from('#__eshop_taxclasses')
			->where('published = 1');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'value', 'text');

		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}

		$lists['taxclasses'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_taxclass_id',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => 'class="input-xlarge form-select"',
				'list.select'        => $item->product_taxclass_id,
			]
		);

		//Stock status list
		$query->clear();
		$query->select('a.id, b.stockstatus_name')
			->from('#__eshop_stockstatuses AS a')
			->innerJoin('#__eshop_stockstatusdetails AS b ON (a.id = b.stockstatus_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$lists['product_stock_status_id'] = HTMLHelper::_(
			'select.genericlist',
			$db->loadObjectList(),
			'product_stock_status_id',
			'class="input-xlarge form-select"',
			'id',
			'stockstatus_name',
			$item->product_stock_status_id ?? '1'
		);

		//Shopping cart mode
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'public', 'Public');
		$options[] = HTMLHelper::_('select.option', 'registered', 'Only Registered Users');
		$options[] = HTMLHelper::_('select.option', 'hide', 'Hide');

		$lists['product_cart_mode'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_cart_mode',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$item->product_cart_mode ?? 'public'
		);

		//Quote mode
		//Shopping cart mode
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '1', 'Public');
		$options[] = HTMLHelper::_('select.option', '2', 'Only Registered Users');
		$options[] = HTMLHelper::_('select.option', '0', 'Hide');

		$lists['product_quote_mode'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_quote_mode',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$item->product_quote_mode ?? '1'
		);

		//Build product languages list
		$query->clear()
			->select('lang_code AS value, title AS text')
			->from('#__languages')
			->order('ordering');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		if ($item->product_languages != '')
		{
			$selectedItems = explode(',', $item->product_languages);
		}
		else
		{
			$selectedItems = [];
		}

		$lists['product_languages'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_languages[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
				'list.select'        => $selectedItems,
			]
		);

		$this->nullDate = $nullDate;

		//Custom fields handle
		if (EShopHelper::getConfigValue('product_custom_fields'))
		{
			$registry     = new Registry($item->custom_fields ?? '{}');
			$data         = new stdClass();
			$data->params = $registry->toArray();
			$form         = Form::getInstance('pmform', JPATH_ROOT . '/components/com_eshop/fields.xml', [], false, '//config');
			$form->bind($data);
			$this->form = $form;
		}

		PluginHelper::importPlugin('eshop');
		$plugins       = Factory::getApplication()->triggerEvent('onEditProduct', [$item]);
		$this->plugins = $plugins;
	}

	/**
	 * Build the toolbar for product edit form
	 */
	public function _buildToolbar()
	{
		$input    = Factory::getApplication()->input;
		$viewName = $this->getName();
		$canDo    = EShopHelper::getActions($viewName);
		$edit     = $input->get('edit');
		$text     = $edit ? Text::_($this->lang_prefix . '_EDIT') : Text::_($this->lang_prefix . '_NEW');

		if ($edit)
		{
			if (Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
			{
				$productName = $this->item->{'product_name_' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB')};
			}
			else
			{
				$productName = $this->item->product_name;
			}

			if (isset($this->item->product_sku) && $this->item->product_sku != '')
			{
				$productInfo = ' - [ ' . $this->item->product_sku . ' ]';

				if ($productName != '')
				{
					$productInfo .= ' - [ ' . $productName . ' ]';
				}
			}
			else
			{
				$productInfo = ' [ ' . $productName . ' ]';
			}
		}
		else
		{
			$productInfo = '';
		}

		ToolbarHelper::title(Text::_($this->lang_prefix . '_' . $viewName) . ': <small><small>[ ' . $text . ' ]' . $productInfo . '</small></small>');
		ToolbarHelper::apply($viewName . '.apply');
		ToolbarHelper::save($viewName . '.save');
		ToolbarHelper::save2new($viewName . '.save2new');

		if ($edit)
		{
			ToolbarHelper::cancel($viewName . '.cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			ToolbarHelper::cancel($viewName . '.cancel');
		}
	}
}