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
use Joomla\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewExports extends HtmlView
{
	/**
	 *
	 * @var $lists
	 */
	protected $lists;

	public function display($tpl = null)
	{
		$input = Factory::getApplication()->input;
		$lists = [];
		//Export type
		$options              = [];
		$options[]            = HTMLHelper::_('select.option', '', Text::_('ESHOP_NONE'));
		$options[]            = HTMLHelper::_('select.option', 'products', Text::_('ESHOP_PRODUCTS'));
		$options[]            = HTMLHelper::_('select.option', 'categories', Text::_('ESHOP_CATEGORIES'));
		$options[]            = HTMLHelper::_('select.option', 'manufacturers', Text::_('ESHOP_MANUFACTURERS'));
		$options[]            = HTMLHelper::_('select.option', 'customers', Text::_('ESHOP_CUSTOMERS'));
		$options[]            = HTMLHelper::_('select.option', 'orders', Text::_('ESHOP_ORDERS'));
		$options[]            = HTMLHelper::_('select.option', 'google_feed', Text::_('ESHOP_GOOGLE_FEED'));
		$options[]            = HTMLHelper::_('select.option', 'pinterest_feed', Text::_('ESHOP_PINTEREST_FEED'));
		$lists['export_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'export_type',
			' class="input-xlarge form-select" onchange="changeExportType(); "',
			'value',
			'text',
			$input->getString('export_type')
		);

		//Export format
		$options                = [];
		$options[]              = HTMLHelper::_('select.option', 'csv', Text::_('ESHOP_EXPORT_FORMAT_CSV'));
		$options[]              = HTMLHelper::_('select.option', 'xlsx', Text::_('ESHOP_EXPORT_FORMAT_XLSX'));
		$options[]              = HTMLHelper::_('select.option', 'xml', Text::_('ESHOP_EXPORT_FORMAT_XML'));
		$lists['export_format'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'export_format',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$input->getString('export_format', 'csv')
		);

		//Language
		$path      = JPATH_ROOT . '/language';
		$folders   = Folder::folders($path);
		$languages = [];
		foreach ($folders as $folder)
		{
			if ($folder != 'pdf_fonts' && $folder != 'overrides')
			{
				$languages[] = $folder;
			}
		}
		$options = [];
		foreach ($languages as $language)
		{
			$options[] = HTMLHelper::_('select.option', $language, $language);
		}
		$lists['language'] = HTMLHelper::_('select.genericlist', $options, 'language', 'class="input-xlarge form-select"', 'value', 'text', '');

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id AS value, b.orderstatus_name AS text')
			->from('#__eshop_orderstatuses AS a')
			->innerJoin('#__eshop_orderstatusdetails AS b ON (a.id = b.orderstatus_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', 0, Text::_('ESHOP_ORDERSSTATUS_ALL'));
		$options                  = array_merge($options, $db->loadObjectList());
		$lists['order_status_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'order_status_id',
			' class="input-xlarge form-select" style="width: 150px;" ',
			'value',
			'text',
			0
		);

		$lists['remove_zero_price_products']   = EShopHtmlHelper::getBooleanInput('remove_zero_price_products', '0');
		$lists['remove_out_of_stock_products'] = EShopHtmlHelper::getBooleanInput('remove_out_of_stock_products', '0');
		$lists['google_id']                    = EShopHtmlHelper::getBooleanInput('google_id', '1');
		$lists['google_title']                 = EShopHtmlHelper::getBooleanInput('google_title', '1');
		$lists['google_description']           = EShopHtmlHelper::getBooleanInput('google_description', '1');
		$lists['google_product_type']          = EShopHtmlHelper::getBooleanInput('google_product_type', '1');
		$lists['google_link']                  = EShopHtmlHelper::getBooleanInput('google_link', '1');
		$lists['google_mobile_link']           = EShopHtmlHelper::getBooleanInput('google_mobile_link', '1');
		$lists['google_image_link']            = EShopHtmlHelper::getBooleanInput('google_image_link', '1');
		$lists['google_additional_image_link'] = EShopHtmlHelper::getBooleanInput('google_additional_image_link', '1');
		$lists['google_availability']          = EShopHtmlHelper::getBooleanInput('google_availability', '1');
		$lists['google_price']                 = EShopHtmlHelper::getBooleanInput('google_price', '1');
		$lists['google_sale_price']            = EShopHtmlHelper::getBooleanInput('google_sale_price', '1');
		$lists['google_mpn']                   = EShopHtmlHelper::getBooleanInput('google_mpn', '1');
		$lists['google_brand']                 = EShopHtmlHelper::getBooleanInput('google_brand', '1');
		$lists['google_shipping_weight']       = EShopHtmlHelper::getBooleanInput('google_shipping_weight', '1');
		$lists['google_alias']                 = EShopHtmlHelper::getBooleanInput('google_alias', '1');

		$lists['pinterest_id']           = EShopHtmlHelper::getBooleanInput('pinterest_id', '1');
		$lists['pinterest_title']        = EShopHtmlHelper::getBooleanInput('pinterest_title', '1');
		$lists['pinterest_description']  = EShopHtmlHelper::getBooleanInput('pinterest_description', '1');
		$lists['pinterest_link']         = EShopHtmlHelper::getBooleanInput('pinterest_link', '1');
		$lists['pinterest_image_link']   = EShopHtmlHelper::getBooleanInput('pinterest_image_link', '1');
		$lists['pinterest_availability'] = EShopHtmlHelper::getBooleanInput('pinterest_availability', '1');
		$lists['pinterest_price']        = EShopHtmlHelper::getBooleanInput('pinterest_price', '1');
		$lists['pinterest_condition']    = EShopHtmlHelper::getBooleanInput('pinterest_condition', '1');
		$lists['pinterest_brand']        = EShopHtmlHelper::getBooleanInput('pinterest_brand', '1');

		//Categories
		$query->clear();
		$query->select('a.id, a.category_parent_id AS parent_id, b.category_name AS title')
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

		$list    = HTMLHelper::_('menu.treerecurse', 0, '', [], $children, 9999, 0, 0);
		$options = [];

		foreach ($list as $listItem)
		{
			$options[] = HTMLHelper::_('select.option', $listItem->id, $listItem->treename);
		}

		$lists['category_ids'] = HTMLHelper::_('select.genericlist', $options, 'category_ids[]', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"="multiple"',
			'list.select'        => '',
		]);

		//Products status
		$options                 = [];
		$options[]               = HTMLHelper::_('select.option', '2', Text::_('ESHOP_ALL_PRODUCTS'));
		$options[]               = HTMLHelper::_('select.option', '1', Text::_('ESHOP_PUBLISHED'));
		$options[]               = HTMLHelper::_('select.option', '0', Text::_('ESHOP_UNPUBLISHED'));
		$lists['product_status'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_status',
			'class="input-xlarge form-select"',
			'value',
			'text',
			''
		);

		//Fields
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'id', 'id');
		$options[] = HTMLHelper::_('select.option', 'language', 'language');
		$options[] = HTMLHelper::_('select.option', 'product_name', 'product_name');
		$options[] = HTMLHelper::_('select.option', 'product_sku', 'product_sku');
		$options[] = HTMLHelper::_('select.option', 'product_alias', 'product_alias');
		$options[] = HTMLHelper::_('select.option', 'product_desc', 'product_desc');
		$options[] = HTMLHelper::_('select.option', 'product_short_desc', 'product_short_desc');
		$options[] = HTMLHelper::_('select.option', 'product_page_title', 'product_page_title');
		$options[] = HTMLHelper::_('select.option', 'product_page_heading', 'product_page_heading');
		$options[] = HTMLHelper::_('select.option', 'tab1_title', 'tab1_title');
		$options[] = HTMLHelper::_('select.option', 'tab1_content', 'tab1_content');
		$options[] = HTMLHelper::_('select.option', 'tab2_title', 'tab2_title');
		$options[] = HTMLHelper::_('select.option', 'tab2_content', 'tab2_content');
		$options[] = HTMLHelper::_('select.option', 'tab3_title', 'tab3_title');
		$options[] = HTMLHelper::_('select.option', 'tab3_content', 'tab3_content');
		$options[] = HTMLHelper::_('select.option', 'tab4_title', 'tab4_title');
		$options[] = HTMLHelper::_('select.option', 'tab4_content', 'tab4_content');
		$options[] = HTMLHelper::_('select.option', 'tab5_title', 'tab5_title');
		$options[] = HTMLHelper::_('select.option', 'tab5_content', 'tab5_content');
		$options[] = HTMLHelper::_('select.option', 'product_meta_key', 'product_meta_key');
		$options[] = HTMLHelper::_('select.option', 'product_meta_desc', 'product_meta_desc');
		$options[] = HTMLHelper::_('select.option', 'product_weight', 'product_weight');
		$options[] = HTMLHelper::_('select.option', 'product_weight_id', 'product_weight_id');
		$options[] = HTMLHelper::_('select.option', 'product_length', 'product_length');
		$options[] = HTMLHelper::_('select.option', 'product_width', 'product_width');
		$options[] = HTMLHelper::_('select.option', 'product_height', 'product_height');
		$options[] = HTMLHelper::_('select.option', 'product_length_id', 'product_length_id');
		$options[] = HTMLHelper::_('select.option', 'product_cost', 'product_cost');
		$options[] = HTMLHelper::_('select.option', 'product_price', 'product_price');
		$options[] = HTMLHelper::_('select.option', 'product_call_for_price', 'product_call_for_price');
		$options[] = HTMLHelper::_('select.option', 'product_taxclass_id', 'product_taxclass_id');
		$options[] = HTMLHelper::_('select.option', 'product_manage_stock', 'product_manage_stock');
		$options[] = HTMLHelper::_('select.option', 'product_stock_display', 'product_stock_display');
		$options[] = HTMLHelper::_('select.option', 'product_stock_warning', 'product_stock_warning');
		$options[] = HTMLHelper::_('select.option', 'product_inventory_global', 'product_inventory_global');
		$options[] = HTMLHelper::_('select.option', 'product_quantity', 'product_quantity');
		$options[] = HTMLHelper::_('select.option', 'product_threshold', 'product_threshold');
		$options[] = HTMLHelper::_('select.option', 'product_threshold_notify', 'product_threshold_notify');
		$options[] = HTMLHelper::_('select.option', 'product_stock_checkout', 'product_stock_checkout');
		$options[] = HTMLHelper::_('select.option', 'product_minimum_quantity', 'product_minimum_quantity');
		$options[] = HTMLHelper::_('select.option', 'product_maximum_quantity', 'product_maximum_quantity');
		$options[] = HTMLHelper::_('select.option', 'product_shipping', 'product_shipping');
		$options[] = HTMLHelper::_('select.option', 'product_shipping_cost', 'product_shipping_cost');
		$options[] = HTMLHelper::_('select.option', 'product_shipping_cost_geozones', 'product_shipping_cost_geozones');
		$options[] = HTMLHelper::_('select.option', 'product_image', 'product_image');
		$options[] = HTMLHelper::_('select.option', 'product_available_date', 'product_available_date');
		$options[] = HTMLHelper::_('select.option', 'product_featured', 'product_featured');
		$options[] = HTMLHelper::_('select.option', 'product_customergroups', 'product_customergroups');
		$options[] = HTMLHelper::_('select.option', 'product_stock_status_id', 'product_stock_status_id');
		$options[] = HTMLHelper::_('select.option', 'product_cart_mode', 'product_cart_mode');
		$options[] = HTMLHelper::_('select.option', 'product_quote_mode', 'product_quote_mode');
		$options[] = HTMLHelper::_('select.option', 'product_published', 'product_published');
		$options[] = HTMLHelper::_('select.option', 'product_ordering', 'product_ordering');
		$options[] = HTMLHelper::_('select.option', 'product_hits', 'product_hits');
		$options[] = HTMLHelper::_('select.option', 'product_additional_images', 'product_additional_images');
		$options[] = HTMLHelper::_('select.option', 'manufacturer_name', 'manufacturer_name');
		$options[] = HTMLHelper::_('select.option', 'category_name', 'category_name');
		$options[] = HTMLHelper::_('select.option', 'option_type', 'option_type');
		$options[] = HTMLHelper::_('select.option', 'option_name', 'option_name');
		$options[] = HTMLHelper::_('select.option', 'option_value', 'option_value');
		$options[] = HTMLHelper::_('select.option', 'option_sku', 'option_sku');
		$options[] = HTMLHelper::_('select.option', 'option_quantity', 'option_quantity');
		$options[] = HTMLHelper::_('select.option', 'option_price', 'option_price');
		$options[] = HTMLHelper::_('select.option', 'option_price_sign', 'option_price_sign');
		$options[] = HTMLHelper::_('select.option', 'option_price_type', 'option_price_type');
		$options[] = HTMLHelper::_('select.option', 'option_weight', 'option_weight');
		$options[] = HTMLHelper::_('select.option', 'option_weight_sign', 'option_weight_sign');
		$options[] = HTMLHelper::_('select.option', 'option_image', 'option_image');
		$options[] = HTMLHelper::_('select.option', 'attributegroup_name', 'attributegroup_name');
		$options[] = HTMLHelper::_('select.option', 'attribute_name', 'attribute_name');
		$options[] = HTMLHelper::_('select.option', 'attribute_value', 'attribute_value');

		$lists['export_fields'] = HTMLHelper::_('select.genericlist', $options, 'export_fields[]', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"="multiple"',
			'list.select'        => '',
		]);

		//Fields
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'order_id', 'order_id');
		$options[] = HTMLHelper::_('select.option', 'order_number', 'order_number');
		$options[] = HTMLHelper::_('select.option', 'invoice_number', 'invoice_number');
		$options[] = HTMLHelper::_('select.option', 'customer_firstname', 'customer_firstname');
		$options[] = HTMLHelper::_('select.option', 'customer_lastname', 'customer_lastname');
		$options[] = HTMLHelper::_('select.option', 'customer_email', 'customer_email');
		$options[] = HTMLHelper::_('select.option', 'customer_telephone', 'customer_telephone');
		$options[] = HTMLHelper::_('select.option', 'customer_fax', 'customer_fax');
		$options[] = HTMLHelper::_('select.option', 'payment_firstname', 'payment_firstname');
		$options[] = HTMLHelper::_('select.option', 'payment_lastname', 'payment_lastname');
		$options[] = HTMLHelper::_('select.option', 'payment_email', 'payment_email');
		$options[] = HTMLHelper::_('select.option', 'payment_telephone', 'payment_telephone');
		$options[] = HTMLHelper::_('select.option', 'payment_fax', 'payment_fax');
		$options[] = HTMLHelper::_('select.option', 'payment_company', 'payment_company');
		$options[] = HTMLHelper::_('select.option', 'payment_company_id', 'payment_company_id');
		$options[] = HTMLHelper::_('select.option', 'payment_address_1', 'payment_address_1');
		$options[] = HTMLHelper::_('select.option', 'payment_address_2', 'payment_address_2');
		$options[] = HTMLHelper::_('select.option', 'payment_city', 'payment_city');
		$options[] = HTMLHelper::_('select.option', 'payment_postcode', 'payment_postcode');
		$options[] = HTMLHelper::_('select.option', 'payment_country_name', 'payment_country_name');
		$options[] = HTMLHelper::_('select.option', 'payment_zone_name', 'payment_zone_name');
		$options[] = HTMLHelper::_('select.option', 'payment_method', 'payment_method');
		$options[] = HTMLHelper::_('select.option', 'payment_method_title', 'payment_method_title');
		$options[] = HTMLHelper::_('select.option', 'transaction_id', 'transaction_id');
		$options[] = HTMLHelper::_('select.option', 'shipping_firstname', 'shipping_firstname');
		$options[] = HTMLHelper::_('select.option', 'shipping_lastname', 'shipping_lastname');
		$options[] = HTMLHelper::_('select.option', 'shipping_email', 'shipping_email');
		$options[] = HTMLHelper::_('select.option', 'shipping_telephone', 'shipping_telephone');
		$options[] = HTMLHelper::_('select.option', 'shipping_fax', 'shipping_fax');
		$options[] = HTMLHelper::_('select.option', 'shipping_company', 'shipping_company');
		$options[] = HTMLHelper::_('select.option', 'shipping_company_id', 'shipping_company_id');
		$options[] = HTMLHelper::_('select.option', 'shipping_address_1', 'shipping_address_1');
		$options[] = HTMLHelper::_('select.option', 'shipping_address_2', 'shipping_address_2');
		$options[] = HTMLHelper::_('select.option', 'shipping_city', 'shipping_city');
		$options[] = HTMLHelper::_('select.option', 'shipping_postcode', 'shipping_postcode');
		$options[] = HTMLHelper::_('select.option', 'shipping_country_name', 'shipping_country_name');
		$options[] = HTMLHelper::_('select.option', 'shipping_zone_name', 'shipping_zone_name');
		$options[] = HTMLHelper::_('select.option', 'shipping_method', 'shipping_method');
		$options[] = HTMLHelper::_('select.option', 'shipping_method_title', 'shipping_method_title');
		$options[] = HTMLHelper::_('select.option', 'shipping_tracking_number', 'shipping_tracking_number');
		$options[] = HTMLHelper::_('select.option', 'shipping_tracking_url', 'shipping_tracking_url');
		$options[] = HTMLHelper::_('select.option', 'shipping_amount', 'shipping_amount');
		$options[] = HTMLHelper::_('select.option', 'tax_amount', 'tax_amount');
		$options[] = HTMLHelper::_('select.option', 'total', 'total');
		$options[] = HTMLHelper::_('select.option', 'comment', 'comment');
		$options[] = HTMLHelper::_('select.option', 'order_status', 'order_status');
		$options[] = HTMLHelper::_('select.option', 'created_date', 'created_date');
		$options[] = HTMLHelper::_('select.option', 'modified_date', 'modified_date');
		$options[] = HTMLHelper::_('select.option', 'product_id', 'product_id');
		$options[] = HTMLHelper::_('select.option', 'product_name', 'product_name');
		$options[] = HTMLHelper::_('select.option', 'option_name', 'option_name');
		$options[] = HTMLHelper::_('select.option', 'option_value', 'option_value');
		$options[] = HTMLHelper::_('select.option', 'option_sku', 'option_sku');
		$options[] = HTMLHelper::_('select.option', 'model', 'model');
		$options[] = HTMLHelper::_('select.option', 'quantity', 'quantity');
		$options[] = HTMLHelper::_('select.option', 'unit_price', 'unit_price');
		$options[] = HTMLHelper::_('select.option', 'unit_total', 'unit_total');

		$lists['export_orders_fields'] = HTMLHelper::_('select.genericlist', $options, 'export_orders_fields[]', [
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"="multiple"',
			'list.select'        => '',
		]);

		$this->lists = $lists;
		parent::display($tpl);
	}
}