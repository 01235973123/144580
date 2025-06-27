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
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewConfiguration extends HtmlView
{
	/**
	 * @var $editor
	 */
	protected $editor;

	/**
	 *
	 * @var $languages
	 */
	protected $languages;

	/**
	 *
	 * @var $currentOrderId
	 */
	protected $currentOrderId;

	/**
	 *
	 * @var $lists
	 */
	protected $lists;

	/**
	 *
	 * @var $config
	 */
	protected $config;

	/**
	 *
	 * @var $sortOptions
	 */
	protected $sortOptions;

	/**
	 *
	 * @var $sortValues
	 */
	protected $sortValues;

	/**
	 *
	 * @var $sortTexts
	 */
	protected $sortTexts;

	public function display($tpl = null)
	{
		$document = Factory::getApplication()->getDocument();
		$document->addScript(Uri::base(true) . '/components/com_eshop/assets/colorpicker/jscolor.js');

		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

		// Check access first
		$app = Factory::getApplication();

		if (!Factory::getUser()->authorise('eshop.configuration', 'com_eshop'))
		{
			$app->enqueueMessage(Text::_('ESHOP_ACCESS_NOT_ALLOW'), 'error');
			$app->redirect('index.php?option=com_eshop&view=dashboard');
		}

		$config     = $this->get('Data');
		$tempConfig = $config;

		//Build AcyMailing list
		if (is_file(JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php'))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';

			$listIds                      = explode(',', $tempConfig->acymailing_list_ids ?? '');
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

		$config = $tempConfig;
		$db     = Factory::getDbo();

		// Introduction display list
		$options                          = [];
		$options[]                        = HTMLHelper::_('select.option', 'front_page', Text::_('ESHOP_CONFIG_INTRODUCTION_DISPLAY_ON_FRONT_PAGE'));
		$options[]                        = HTMLHelper::_(
			'select.option',
			'categories_page',
			Text::_('ESHOP_CONFIG_INTRODUCTION_DISPLAY_ON_CATEGORIES_PAGE')
		);
		$lists['introduction_display_on'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'introduction_display_on',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->introduction_display_on ?? 'frontpage'
		);

		//Country list
		$query = $db->getQuery(true);
		$query->select('id, country_name AS name')
			->from('#__eshop_countries')
			->where('published = 1')
			->order('country_name');
		$db->setQuery($query);
		$options             = [];
		$options[]           = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'id', 'name');
		$options             = array_merge($options, $db->loadObjectList());
		$lists['country_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'country_id',
			' class="input-xlarge form-select" onchange="Eshop.updateStateList(this.value, \'zone_id\')" ',
			'id',
			'name',
			$config->country_id ?? '0'
		);

		//Zone list
		$query->clear();
		$query->select('id, zone_name')
			->from('#__eshop_zones')
			->where('country_id = ' . intval($config->country_id ?? '0'))
			->where('published = 1')
			->order('zone_name');
		$db->setQuery($query);
		$options          = [];
		$options[]        = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'id', 'zone_name');
		$options          = array_merge($options, $db->loadObjectList());
		$lists['zone_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'zone_id',
			'class="input-xlarge form-select"',
			'id',
			'zone_name',
			$config->zone_id ?? '0'
		);

		//Currencies list
		$query->clear();
		$query->select('currency_code, currency_name')
			->from('#__eshop_currencies')
			->where('published = 1');
		$db->setQuery($query);
		$rows                           = $db->loadObjectList();
		$lists['default_currency_code'] = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'default_currency_code',
			'class="input-xlarge form-select"',
			'currency_code',
			'currency_name',
			$config->default_currency_code ?? 'USD'
		);

		//Lengths list
		$query->clear();
		$query->select('a.id, b.length_name')
			->from('#__eshop_lengths AS a')
			->innerJoin('#__eshop_lengthdetails AS b ON (a.id = b.length_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows               = $db->loadObjectList();
		$lists['length_id'] = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'length_id',
			'class="input-xlarge form-select"',
			'id',
			'length_name',
			$config->length_id ?? '1'
		);

		//Weights list
		$query->clear();
		$query->select('a.id, b.weight_name')
			->from('#__eshop_weights AS a')
			->innerJoin('#__eshop_weightdetails AS b ON (a.id = b.weight_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows               = $db->loadObjectList();
		$lists['weight_id'] = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'weight_id',
			'class="input-xlarge form-select"',
			'id',
			'weight_name',
			$config->weight_id ?? '1'
		);

		//Customer group list
		$query->clear();
		$query->select('a.id, b.customergroup_name AS name')
			->from('#__eshop_customergroups AS a')
			->innerJoin('#__eshop_customergroupdetails AS b ON (a.id = b.customergroup_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('b.customergroup_name');
		$db->setQuery($query);
		$rows                      = $db->loadObjectList();
		$options                   = [];
		$options[]                 = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'id', 'name');
		$options                   = array_merge($options, $rows);
		$lists['customergroup_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'customergroup_id',
			'class="input-xlarge form-select"',
			'id',
			'name',
			$config->customergroup_id ?? '1'
		);

		//Customer group display list
		$customerGroupDisplay            = explode(',', $config->customer_group_display ?? '');
		$lists['customer_group_display'] = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'customer_group_display[]',
			'class="input-xlarge form-select chosen" multiple="multiple"',
			'id',
			'name',
			$customerGroupDisplay
		);

		//Stock status list
		$query->clear();
		$query->select('a.id, b.stockstatus_name')
			->from('#__eshop_stockstatuses AS a')
			->innerJoin('#__eshop_stockstatusdetails AS b ON (a.id = b.stockstatus_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows                     = $db->loadObjectList();
		$lists['stock_status_id'] = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'stock_status_id',
			'class="input-xlarge form-select"',
			'id',
			'stockstatus_name',
			$config->stock_status_id ?? '1'
		);

		//Order status and complete status list
		$query->clear();
		$query->select('a.id, b.orderstatus_name')
			->from('#__eshop_orderstatuses AS a')
			->innerJoin('#__eshop_orderstatusdetails AS b ON (a.id = b.orderstatus_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"');
		$db->setQuery($query);
		$rows                               = $db->loadObjectList();
		$lists['order_status_id']           = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'order_status_id',
			'class="input-xlarge form-select"',
			'id',
			'orderstatus_name',
			$config->order_status_id ?? '8'
		);
		$lists['complete_status_id']        = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'complete_status_id',
			'class="input-xlarge form-select"',
			'id',
			'orderstatus_name',
			$config->complete_status_id ?? '4'
		);
		$lists['shipped_status_id']         = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'shipped_status_id',
			'class="input-xlarge form-select"',
			'id',
			'orderstatus_name',
			$config->shipped_status_id ?? '13'
		);
		$lists['canceled_status_id']        = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'canceled_status_id',
			'class="input-xlarge form-select"',
			'id',
			'orderstatus_name',
			$config->canceled_status_id ?? '1'
		);
		$lists['failed_status_id']          = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'failed_status_id',
			'class="input-xlarge form-select"',
			'id',
			'orderstatus_name',
			$config->failed_status_id ?? '7'
		);
		$invoiceStatusIds                   = explode(',', $config->invoice_status_ids ?? '');
		$lists['invoice_status_ids']        = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'invoice_status_ids[]',
			' class="input-xlarge form-select" multiple',
			'id',
			'orderstatus_name',
			$invoiceStatusIds
		);
		$lists['delivery_date']             = EShopHtmlHelper::getBooleanInput(
			'delivery_date',
			$config->delivery_date ?? '0'
		);
		$lists['idevaffiliate_integration'] = EShopHtmlHelper::getBooleanInput(
			'idevaffiliate_integration',
			$config->idevaffiliate_integration ?? '0'
		);

		//Comment
		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', '', Text::_('ESHOP_HIDE'));
		$options[]                = HTMLHelper::_('select.option', '4', Text::_('ESHOP_DISPLAY_ON_SHIPPING_METHOD_STEP'));
		$options[]                = HTMLHelper::_('select.option', '5', Text::_('ESHOP_DISPLAY_ON_PAYMENT_METHOD_STEP'));
		$options[]                = HTMLHelper::_('select.option', '45', Text::_('ESHOP_DISPLAY_ON_BOTH_STEPS'));
		$lists['display_comment'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'display_comment',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->display_comment ?? '45'
		);

		//Tax default
		$options              = [];
		$options[]            = HTMLHelper::_('select.option', '', Text::_('ESHOP_NONE'));
		$options[]            = HTMLHelper::_('select.option', 'shipping', Text::_('ESHOP_SHIPPING_ADDRESS'));
		$options[]            = HTMLHelper::_('select.option', 'payment', Text::_('ESHOP_PAYMENT_ADDRESS'));
		$lists['tax_default'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'tax_default',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->tax_default ?? ''
		);


		//Tax customer
		$options                        = [];
		$options[]                      = HTMLHelper::_('select.option', 'shipping', Text::_('ESHOP_SHIPPING_ADDRESS'));
		$options[]                      = HTMLHelper::_('select.option', 'payment', Text::_('ESHOP_PAYMENT_ADDRESS'));
		$lists['eu_vat_rules_based_on'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'eu_vat_rules_based_on',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->eu_vat_rules_based_on ?? 'shipping'
		);

		//Account terms and Checkout terms
		$query->clear();
		$query->select('id, title')
			->from('#__content')
			->where('state = 1');
		$db->setQuery($query);
		$rows                                  = $db->loadObjectList();
		$options                               = [];
		$options[]                             = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'id', 'title');
		$options                               = array_merge($options, $rows);
		$lists['account_terms']                = HTMLHelper::_(
			'select.genericlist',
			$options,
			'account_terms',
			'class="input-xlarge form-select"',
			'id',
			'title',
			$config->account_terms ?? '0'
		);
		$lists['checkout_terms']               = HTMLHelper::_(
			'select.genericlist',
			$options,
			'checkout_terms',
			'class="input-xlarge form-select"',
			'id',
			'title',
			$config->checkout_terms ?? '0'
		);
		$lists['privacy_policy_article']       = HTMLHelper::_(
			'select.genericlist',
			$options,
			'privacy_policy_article',
			'class="input-xlarge form-select"',
			'id',
			'title',
			$config->privacy_policy_article ?? '0'
		);
		$lists['show_privacy_policy_checkbox'] = EShopHtmlHelper::getBooleanInput(
			'show_privacy_policy_checkbox',
			$config->show_privacy_policy_checkbox ?? '0'
		);

		$options                         = [];
		$options[]                       = HTMLHelper::_('select.option', 'payment_method_step', Text::_('ESHOP_PAYMENT_METHOD_STEP'));
		$options[]                       = HTMLHelper::_('select.option', 'confirm_step', Text::_('ESHOP_CONFIRM_STEP'));
		$lists['display_privacy_policy'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'display_privacy_policy',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->display_privacy_policy ?? 'step_5'
		);

		//Themes list
		$query->clear();
		$query->select('name AS value, title AS text')
			->from('#__eshop_themes')
			->where('published = 1');
		$db->setQuery($query);
		$rows           = $db->loadObjectList();
		$lists['theme'] = HTMLHelper::_(
			'select.genericlist',
			$rows,
			'theme',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->theme ?? 'default'
		);

		//Build products filter layout list
		$options                         = [];
		$options[]                       = HTMLHelper::_('select.option', 'default', Text::_('ESHOP_CONFIG_PRODUCTS_FILTER_LAYOUT_DEFAULT'));
		$options[]                       = HTMLHelper::_('select.option', 'table', Text::_('ESHOP_CONFIG_PRODUCTS_FILTER_LAYOUT_TABLE'));
		$lists['products_filter_layout'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'products_filter_layout',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->products_filter_layout ?? 'default'
		);

		//Build products filter layout list
		$options              = [];
		$options[]            = HTMLHelper::_('select.option', 'popout', Text::_('ESHOP_CART_POPOUT'));
		$options[]            = HTMLHelper::_('select.option', 'redirect', Text::_('ESHOP_CART_REDIRECT'));
		$options[]            = HTMLHelper::_('select.option', 'message', Text::_('ESHOP_CART_MESSAGE'));
		$lists['cart_popout'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'cart_popout',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->cart_popout ?? 'popout'
		);

		//Build update cart function
		$options                       = [];
		$options[]                     = HTMLHelper::_('select.option', 'update_button', Text::_('ESHOP_UPDATE_BUTTON'));
		$options[]                     = HTMLHelper::_('select.option', 'quantity_button', Text::_('ESHOP_QUANTITY_BUTTON'));
		$lists['update_cart_function'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'update_cart_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->update_cart_function ?? 'update_button'
		);

		//Build update quote function
		$options                        = [];
		$options[]                      = HTMLHelper::_('select.option', 'update_button', Text::_('ESHOP_UPDATE_BUTTON'));
		$options[]                      = HTMLHelper::_('select.option', 'quantity_button', Text::_('ESHOP_QUANTITY_BUTTON'));
		$lists['update_quote_function'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'update_quote_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->update_quote_function ?? 'update_button'
		);

		$lists['auto_update_currency']            = EShopHtmlHelper::getBooleanInput(
			'auto_update_currency',
			$config->auto_update_currency ?? '0'
		);
		$lists['show_eshop_update']               = EShopHtmlHelper::getBooleanInput(
			'show_eshop_update',
			$config->show_eshop_update ?? '1'
		);
		$lists['show_eshop_copyright']            = EShopHtmlHelper::getBooleanInput(
			'show_eshop_copyright',
			$config->show_eshop_copyright ?? '1'
		);
		$lists['product_sku_validation']          = EShopHtmlHelper::getBooleanInput(
			'product_sku_validation',
			$config->product_sku_validation ?? '0'
		);
		$lists['product_count']                   = EShopHtmlHelper::getBooleanInput(
			'product_count',
			$config->product_count ?? '0'
		);
		$lists['rich_snippets']                   = EShopHtmlHelper::getBooleanInput(
			'rich_snippets',
			$config->rich_snippets ?? '0'
		);
		$lists['allow_reviews']                   = EShopHtmlHelper::getBooleanInput(
			'allow_reviews',
			$config->allow_reviews ?? '1'
		);
		$lists['enable_reviews_captcha']          = EShopHtmlHelper::getBooleanInput(
			'enable_reviews_captcha',
			$config->enable_reviews_captcha ?? '1'
		);
		$lists['enable_register_account_captcha'] = EShopHtmlHelper::getBooleanInput(
			'enable_register_account_captcha',
			$config->enable_register_account_captcha ?? '1'
		);
		$lists['enable_checkout_captcha']         = EShopHtmlHelper::getBooleanInput(
			'enable_checkout_captcha',
			$config->enable_checkout_captcha ?? '1'
		);
		$lists['enable_quote_captcha']            = EShopHtmlHelper::getBooleanInput(
			'enable_quote_captcha',
			$config->enable_quote_captcha ?? '1'
		);
		$lists['allow_notify']                    = EShopHtmlHelper::getBooleanInput(
			'allow_notify',
			$config->allow_notify ?? '1'
		);
		$lists['allow_wishlist']                  = EShopHtmlHelper::getBooleanInput(
			'allow_wishlist',
			$config->allow_wishlist ?? '1'
		);
		$lists['allow_compare']                   = EShopHtmlHelper::getBooleanInput(
			'allow_compare',
			$config->allow_compare ?? '1'
		);
		$lists['allow_ask_question']              = EShopHtmlHelper::getBooleanInput(
			'allow_ask_question',
			$config->allow_ask_question ?? '1'
		);
		$lists['allow_price_match']				  = EShopHtmlHelper::getBooleanInput(
			'allow_price_match',
			$config->allow_price_match ?? '0'
			);
		$lists['allow_email_to_a_friend']         = EShopHtmlHelper::getBooleanInput(
			'allow_email_to_a_friend',
			$config->allow_email_to_a_friend ?? '1'
		);
		$lists['dynamic_price']                   = EShopHtmlHelper::getBooleanInput(
			'dynamic_price',
			$config->dynamic_price ?? '1'
		);
		$lists['dynamic_info']                    = EShopHtmlHelper::getBooleanInput(
			'dynamic_info',
			$config->dynamic_info ?? '0'
		);
		$lists['hide_out_of_stock_products']      = EShopHtmlHelper::getBooleanInput(
			'hide_out_of_stock_products',
			$config->hide_out_of_stock_products ?? '0'
		);
		$lists['acymailing_integration']          = EShopHtmlHelper::getBooleanInput(
			'acymailing_integration',
			$config->acymailing_integration ?? '0'
		);
		$lists['mailchimp_integration']           = EShopHtmlHelper::getBooleanInput(
			'mailchimp_integration',
			$config->mailchimp_integration ?? '0'
		);
		$lists['allow_download_pdf_product']      = EShopHtmlHelper::getBooleanInput(
			'allow_download_pdf_product',
			$config->allow_download_pdf_product ?? '1'
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'public', Text::_('ESHOP_CONFIG_DISPLAY_PRICE_PUBLIC'));
		$options[] = HTMLHelper::_('select.option', 'registered', Text::_('ESHOP_CONFIG_DISPLAY_PRICE_ONLY_REGISTERED_USERS'));
		$options[] = HTMLHelper::_('select.option', 'hide', Text::_('ESHOP_CONFIG_DISPLAY_PRICE_HIDE'));

		$lists['display_price'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'display_price',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->display_price ?? 'public'
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'only_option_price', Text::_('ESHOP_CONFIG_DISPLAY_OPTION_PRICE_ONLY_OPTION_PRICE'));
		$options[] = HTMLHelper::_('select.option', 'hide', Text::_('ESHOP_CONFIG_DISPLAY_OPTION_PRICE_HIDE'));

		$lists['display_option_price'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'display_option_price',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->display_option_price ?? 'only_option_price'
		);

		$lists['display_option_price_with_tax'] = EShopHtmlHelper::getBooleanInput(
			'display_option_price_with_tax',
			$config->display_option_price_with_tax ?? '1'
		);
		$lists['product_custom_fields']         = EShopHtmlHelper::getBooleanInput(
			'product_custom_fields',
			$config->product_custom_fields ?? '0'
		);
		$lists['assign_same_options']           = EShopHtmlHelper::getBooleanInput(
			'assign_same_options',
			$config->assign_same_options ?? '0'
		);
		$query->clear()
			->select('id AS value, taxclass_name AS text')
			->from('#__eshop_taxclasses')
			->where('published = 1')
			->order('taxclass_name');
		$db->setQuery($query);
		$rows      = $db->loadObjectList();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'value', 'text');
		if (count($rows))
		{
			$options = array_merge($options, $rows);
		}
		$lists['tax_class']                 = HTMLHelper::_(
			'select.genericlist',
			$options,
			'tax_class',
			[
				'option.text.toHtml' => false,
				'option.value'       => 'value',
				'option.text'        => 'text',
				'list.attr'          => 'class="input-xlarge form-select"',
				'list.select'        => $config->tax_class ?? '0',
			]
		);
		$lists['tax']                       = EShopHtmlHelper::getBooleanInput('tax', $config->tax ?? '1');
		$lists['display_ex_tax']            = EShopHtmlHelper::getBooleanInput(
			'display_ex_tax',
			$config->display_ex_tax ?? '1'
		);
		$lists['display_ex_tax_base_price'] = EShopHtmlHelper::getBooleanInput(
			'display_ex_tax_base_price',
			$config->display_ex_tax_base_price ?? '1'
		);
		$lists['include_tax_anywhere']      = EShopHtmlHelper::getBooleanInput(
			'include_tax_anywhere',
			$config->include_tax_anywhere ?? '0'
		);
		$lists['enable_eu_vat_rules']       = EShopHtmlHelper::getBooleanInput(
			'enable_eu_vat_rules',
			$config->enable_eu_vat_rules ?? '0'
		);
		$lists['shop_offline']              = EShopHtmlHelper::getBooleanInput(
			'shop_offline',
			$config->shop_offline ?? '0'
		);
		
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '1', 'Public');
		$options[] = HTMLHelper::_('select.option', '2', 'Only Registered Users');
		$options[] = HTMLHelper::_('select.option', '0', 'Hide');
		
		$lists['quote_cart_mode'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'quote_cart_mode',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->quote_cart_mode ?? '1'
			);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '1', Text::_('ESHOP_CONFIG_CATALOG_MODE_YES'));
		$options[] = HTMLHelper::_('select.option', '2', Text::_('ESHOP_CONFIG_CATALOG_MODE_ONLY_REGISTERED_USERS'));
		$options[] = HTMLHelper::_('select.option', '3', Text::_('ESHOP_CONFIG_CATALOG_MODE_ONLY_GUESTS'));
		$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_CONFIG_CATALOG_MODE_NO'));

		$lists['catalog_mode'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'catalog_mode',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->catalog_mode ?? '0'
		);

		$lists['search_sku']          = EShopHtmlHelper::getBooleanInput('search_sku', $config->search_sku ?? '1');
		$lists['search_short_desc']   = EShopHtmlHelper::getBooleanInput(
			'search_short_desc',
			$config->search_short_desc ?? '1'
		);
		$lists['search_desc']         = EShopHtmlHelper::getBooleanInput('search_desc', $config->search_desc ?? '1');
		$lists['search_tab1_title']   = EShopHtmlHelper::getBooleanInput(
			'search_tab1_title',
			$config->search_tab1_title ?? '1'
		);
		$lists['search_tab1_content'] = EShopHtmlHelper::getBooleanInput(
			'search_tab1_content',
			$config->search_tab1_content ?? '1'
		);
		$lists['search_tab2_title']   = EShopHtmlHelper::getBooleanInput(
			'search_tab2_title',
			$config->search_tab2_title ?? '1'
		);
		$lists['search_tab2_content'] = EShopHtmlHelper::getBooleanInput(
			'search_tab2_content',
			$config->search_tab2_content ?? '1'
		);
		$lists['search_tab3_title']   = EShopHtmlHelper::getBooleanInput(
			'search_tab3_title',
			$config->search_tab3_title ?? '1'
		);
		$lists['search_tab3_content'] = EShopHtmlHelper::getBooleanInput(
			'search_tab3_content',
			$config->search_tab3_content ?? '1'
		);
		$lists['search_tab4_title']   = EShopHtmlHelper::getBooleanInput(
			'search_tab4_title',
			$config->search_tab4_title ?? '1'
		);
		$lists['search_tab4_content'] = EShopHtmlHelper::getBooleanInput(
			'search_tab4_content',
			$config->search_tab4_content ?? '1'
		);
		$lists['search_tab5_title']   = EShopHtmlHelper::getBooleanInput(
			'search_tab5_title',
			$config->search_tab5_title ?? '1'
		);
		$lists['search_tab5_content'] = EShopHtmlHelper::getBooleanInput(
			'search_tab5_content',
			$config->search_tab5_content ?? '1'
		);
		$lists['search_tag']          = EShopHtmlHelper::getBooleanInput('search_tag', $config->search_tag ?? '1');
		$lists['search_option_value'] = EShopHtmlHelper::getBooleanInput(
			'search_option_value',
			$config->search_option_value ?? '1'
		);

		$options                                = [];
		$options[]                              = HTMLHelper::_('select.option', 'global', Text::_('ESHOP_GLOBAL'));
		$options[]                              = HTMLHelper::_('select.option', 'store', Text::_('ESHOP_STORE'));
		$lists['send_from']                     = HTMLHelper::_(
			'select.genericlist',
			$options,
			'send_from',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->send_from ?? 'global'
		);
		$lists['order_alert_mail']              = EShopHtmlHelper::getBooleanInput(
			'order_alert_mail',
			$config->order_alert_mail ?? '1'
		);
		$lists['order_alert_mail_admin']        = EShopHtmlHelper::getBooleanInput(
			'order_alert_mail_admin',
			$config->order_alert_mail_admin ?? '1'
		);
		$lists['order_cancel_mail_admin']       = EShopHtmlHelper::getBooleanInput(
			'order_cancel_mail_admin',
			$config->order_cancel_mail_admin ?? '1'
		);
		$lists['order_failure_mail_admin']       = EShopHtmlHelper::getBooleanInput(
			'order_failure_mail_admin',
			$config->order_failure_mail_admin ?? '1'
			);
		$lists['order_alert_mail_manufacturer'] = EShopHtmlHelper::getBooleanInput(
			'order_alert_mail_manufacturer',
			$config->order_alert_mail_manufacturer ?? '1'
		);
		$lists['order_alert_mail_customer']     = EShopHtmlHelper::getBooleanInput(
			'order_alert_mail_customer',
			$config->order_alert_mail_customer ?? '1'
		);
		$lists['order_reply_to_customer']       = EShopHtmlHelper::getBooleanInput(
			'order_reply_to_customer',
			$config->order_reply_to_customer ?? '0'
		);
		$lists['quote_alert_mail']              = EShopHtmlHelper::getBooleanInput(
			'quote_alert_mail',
			$config->quote_alert_mail ?? '1'
		);
		$lists['quote_alert_mail_admin']        = EShopHtmlHelper::getBooleanInput(
			'quote_alert_mail_admin',
			$config->quote_alert_mail_admin ?? '1'
		);
		$lists['quote_alert_mail_customer']     = EShopHtmlHelper::getBooleanInput(
			'quote_alert_mail_customer',
			$config->quote_alert_mail_customer ?? '1'
		);
		$lists['product_alert_ask_question']    = EShopHtmlHelper::getBooleanInput(
			'product_alert_ask_question',
			$config->product_alert_ask_question ?? '1'
		);
		$lists['product_alert_review']          = EShopHtmlHelper::getBooleanInput(
			'product_alert_review',
			$config->product_alert_review ?? '1'
		);
		$lists['cart_weight']                   = EShopHtmlHelper::getBooleanInput(
			'cart_weight',
			$config->cart_weight ?? '1'
		);
		$lists['checkout_weight']               = EShopHtmlHelper::getBooleanInput(
			'checkout_weight',
			$config->checkout_weight ?? '0'
		);
		$lists['require_shipping']              = EShopHtmlHelper::getBooleanInput(
			'require_shipping',
			$config->require_shipping ?? '1'
		);
		$lists['require_shipping_address']      = EShopHtmlHelper::getBooleanInput(
			'require_shipping_address',
			$config->require_shipping_address ?? '1'
		);
		$lists['shipping_estimate']             = EShopHtmlHelper::getBooleanInput(
			'shipping_estimate',
			$config->shipping_estimate ?? '1'
		);
		$lists['enable_existing_addresses']     = EShopHtmlHelper::getBooleanInput(
			'enable_existing_addresses',
			$config->enable_existing_addresses ?? '1'
		);
		$lists['one_add_to_cart_button']        = EShopHtmlHelper::getBooleanInput(
			'one_add_to_cart_button',
			$config->one_add_to_cart_button ?? '0'
		);
		$lists['active_https']                  = EShopHtmlHelper::getBooleanInput(
			'active_https',
			$config->active_https ?? '0'
		);
		$lists['collect_user_ip']               = EShopHtmlHelper::getBooleanInput(
			'collect_user_ip',
			$config->collect_user_ip ?? '0'
		);
		$lists['allow_re_order']                = EShopHtmlHelper::getBooleanInput(
			'allow_re_order',
			$config->allow_re_order ?? '0'
		);
		$lists['allow_coupon']                  = EShopHtmlHelper::getBooleanInput(
			'allow_coupon',
			$config->allow_coupon ?? '1'
		);
		$lists['change_coupon']                 = EShopHtmlHelper::getBooleanInput(
			'change_coupon',
			$config->change_coupon ?? '0'
		);
		$lists['allow_voucher']                 = EShopHtmlHelper::getBooleanInput(
			'allow_voucher',
			$config->allow_voucher ?? '1'
		);
		$lists['change_voucher']                = EShopHtmlHelper::getBooleanInput(
			'change_voucher',
			$config->change_voucher ?? '0'
		);

		$lists['store_cart'] = EShopHtmlHelper::getBooleanInput('store_cart', $config->store_cart ?? '0');

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE_FOREVER'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE_1_DAY'));
		$options[] = HTMLHelper::_('select.option', '7', Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE_1_WEEK'));
		$options[] = HTMLHelper::_('select.option', '14', Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE_2_WEEKS'));
		$options[] = HTMLHelper::_('select.option', '21', Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE_3_WEEKS'));
		$options[] = HTMLHelper::_('select.option', '30', Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE_1_MONTH'));
		$options[] = HTMLHelper::_('select.option', '60', Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE_2_MONTHS'));
		$options[] = HTMLHelper::_('select.option', '90', Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE_3_MONTHS'));
		$options[] = HTMLHelper::_('select.option', '365', Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE_1_YEAR'));

		$lists['store_cart_schedule'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'store_cart_schedule',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->store_cart_schedule ?? '7'
		);

		$lists['send_1st_abandon_cart_reminder'] = EShopHtmlHelper::getBooleanInput(
			'send_1st_abandon_cart_reminder',
			$config->send_1st_abandon_cart_reminder ?? '1'
		);
		$lists['send_2nd_abandon_cart_reminder'] = EShopHtmlHelper::getBooleanInput(
			'send_2nd_abandon_cart_reminder',
			$config->send_2nd_abandon_cart_reminder ?? '1'
		);
		$lists['send_3rd_abandon_cart_reminder'] = EShopHtmlHelper::getBooleanInput(
			'send_3rd_abandon_cart_reminder',
			$config->send_3rd_abandon_cart_reminder ?? '1'
		);

		$lists['only_free_shipping'] = EShopHtmlHelper::getBooleanInput(
			'only_free_shipping',
			$config->only_free_shipping ?? '0'
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', ',', Text::_('ESHOP_COMMA'));
		$options[] = HTMLHelper::_('select.option', ';', Text::_('ESHOP_SEMICOLON'));

		$lists['csv_delimiter'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'csv_delimiter',
			'class="form-select"',
			'value',
			'text',
			$config->csv_delimiter ?? ','
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'csv', Text::_('ESHOP_CSV'));
		$options[] = HTMLHelper::_('select.option', 'xlsx', Text::_('ESHOP_EXCEL'));

		$lists['export_data_format'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'export_data_format',
			'class="form-select"',
			'value',
			'text',
			$config->export_data_format ?? 'csv'
		);

		$options                           = [];
		$options[]                         = HTMLHelper::_(
			'select.option',
			'guest_and_registered',
			Text::_('ESHOP_CONFIG_CHECKOUT_TYPE_GUEST_AND_REGISTERED_USER')
		);
		$options[]                         = HTMLHelper::_('select.option', 'guest_only', Text::_('ESHOP_CONFIG_CHECKOUT_TYPE_GUEST_ONLY'));
		$options[]                         = HTMLHelper::_('select.option', 'registered_only', Text::_('ESHOP_CONFIG_CHECKOUT_TYPE_REGISTERED_ONLY'));
		$lists['checkout_type']            = HTMLHelper::_(
			'select.genericlist',
			$options,
			'checkout_type',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->checkout_type ?? 'guest_and_registered'
		);
		$lists['stock_manage']             = EShopHtmlHelper::getBooleanInput(
			'stock_manage',
			$config->stock_manage ?? '1'
		);
		$lists['stock_display']            = EShopHtmlHelper::getBooleanInput(
			'stock_display',
			$config->stock_display ?? '1'
		);
		$lists['stock_warning']            = EShopHtmlHelper::getBooleanInput(
			'stock_warning',
			$config->stock_warning ?? '1'
		);
		$lists['stock_checkout']           = EShopHtmlHelper::getBooleanInput(
			'stock_checkout',
			$config->stock_checkout ?? '0'
		);
		$lists['enable_checkout_donate']   = EShopHtmlHelper::getBooleanInput(
			'enable_checkout_donate',
			$config->enable_checkout_donate ?? '0'
		);
		$lists['enable_checkout_discount'] = EShopHtmlHelper::getBooleanInput(
			'enable_checkout_discount',
			$config->enable_checkout_discount ?? '0'
		);

		$options                         = [];
		$options[]                       = HTMLHelper::_('select.option', 'total', Text::_('ESHOP_CHECKOUT_DISCOUNT_TYPE_TOTAL'));
		$options[]                       = HTMLHelper::_('select.option', 'quantity', Text::_('ESHOP_CHECKOUT_DISCOUNT_TYPE_QUANTITY'));
		$lists['checkout_discount_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'checkout_discount_type',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->checkout_discount_type ?? 'total'
		);

		$lists['load_jquery_framework'] = EShopHtmlHelper::getBooleanInput(
			'load_jquery_framework',
			$config->load_jquery_framework ?? '1'
		);
		$lists['load_bootstrap_css']    = EShopHtmlHelper::getBooleanInput(
			'load_bootstrap_css',
			$config->load_bootstrap_css ?? '1'
		);

		$options                            = [];
		$options[]                          = HTMLHelper::_('select.option', 2, Text::_('ESHOP_TWITTER_BOOTSTRAP_VERSION_2'));
		$options[]                          = HTMLHelper::_('select.option', 3, Text::_('ESHOP_TWITTER_BOOTSTRAP_VERSION_3'));
		$options[]                          = HTMLHelper::_('select.option', 4, Text::_('ESHOP_TWITTER_BOOTSTRAP_VERSION_4'));
		$options[]                          = HTMLHelper::_('select.option', 5, Text::_('ESHOP_TWITTER_BOOTSTRAP_VERSION_5'));
		$options[]                          = HTMLHelper::_('select.option', 'uikit3', Text::_('ESHOP_UIKIT_3'));
		
		if (EShopHelper::isJoomla4())
		{
			$default = 5;
		}
		else
		{
			$default = 2;
		}
		
		$lists['twitter_bootstrap_version'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'twitter_bootstrap_version',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->twitter_bootstrap_version ?? $default
		);

		$lists['show_categories_nav'] = EShopHtmlHelper::getBooleanInput(
			'show_categories_nav',
			$config->show_categories_nav ?? '1'
		);
		$lists['show_products_nav']   = EShopHtmlHelper::getBooleanInput(
			'show_products_nav',
			$config->show_products_nav ?? '1'
		);

		$options                        = [];
		$options[]                      = HTMLHelper::_('select.option', '2:10', Text::_('ESHOP_GRID_RATIO_IMAGE_INFO_2_10'));
		$options[]                      = HTMLHelper::_('select.option', '3:9', Text::_('ESHOP_GRID_RATIO_IMAGE_INFO_3_9'));
		$options[]                      = HTMLHelper::_('select.option', '4:8', Text::_('ESHOP_GRID_RATIO_IMAGE_INFO_4_8'));
		$options[]                      = HTMLHelper::_('select.option', '5:7', Text::_('ESHOP_GRID_RATIO_IMAGE_INFO_5_7'));
		$options[]                      = HTMLHelper::_('select.option', '6:6', Text::_('ESHOP_GRID_RATIO_IMAGE_INFO_6_6'));
		$options[]                      = HTMLHelper::_('select.option', '7:5', Text::_('ESHOP_GRID_RATIO_IMAGE_INFO_7_5'));
		$options[]                      = HTMLHelper::_('select.option', '8:4', Text::_('ESHOP_GRID_RATIO_IMAGE_INFO_8_4'));
		$options[]                      = HTMLHelper::_('select.option', '9:3', Text::_('ESHOP_GRID_RATIO_IMAGE_INFO_9_3'));
		$options[]                      = HTMLHelper::_('select.option', '10:2', Text::_('ESHOP_GRID_RATIO_IMAGE_INFO_10_2'));
		$lists['grid_ratio_image_info'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'grid_ratio_image_info',
			'class="input-medium form-select"',
			'value',
			'text',
			$config->grid_ratio_image_info ?? '4:8'
		);

		$lists['show_manufacturer']           = EShopHtmlHelper::getBooleanInput(
			'show_manufacturer',
			$config->show_manufacturer ?? '1'
		);
		$lists['show_availability']           = EShopHtmlHelper::getBooleanInput(
			'show_availability',
			$config->show_availability ?? '1'
		);
		$lists['list_show_availability']           = EShopHtmlHelper::getBooleanInput(
			'list_show_availability',
			$config->list_show_availability ?? '0'
			);
		$lists['show_product_weight']         = EShopHtmlHelper::getBooleanInput(
			'show_product_weight',
			$config->show_product_weight ?? '1'
		);
		$lists['show_product_dimensions']     = EShopHtmlHelper::getBooleanInput(
			'show_product_dimensions',
			$config->show_product_dimensions ?? '1'
		);
		$lists['show_product_tags']           = EShopHtmlHelper::getBooleanInput(
			'show_product_tags',
			$config->show_product_tags ?? '1'
		);
		$lists['show_product_attachments']    = EShopHtmlHelper::getBooleanInput(
			'show_product_attachments',
			$config->show_product_attachments ?? '1'
		);
		$lists['show_sku']                    = EShopHtmlHelper::getBooleanInput('show_sku', $config->show_sku ?? '1');
		$lists['show_specification']          = EShopHtmlHelper::getBooleanInput(
			'show_specification',
			$config->show_specification ?? '1'
		);
		$lists['show_related_products']       = EShopHtmlHelper::getBooleanInput(
			'show_related_products',
			$config->show_related_products ?? '1'
		);
		$lists['show_category_image']         = EShopHtmlHelper::getBooleanInput(
			'show_category_image',
			$config->show_category_image ?? '1'
		);
		$lists['show_category_desc']          = EShopHtmlHelper::getBooleanInput(
			'show_category_desc',
			$config->show_category_desc ?? '1'
		);
		$lists['show_products_in_all_levels'] = EShopHtmlHelper::getBooleanInput(
			'show_products_in_all_levels',
			$config->show_products_in_all_levels ?? '1'
		);
		$lists['show_sub_categories']         = EShopHtmlHelper::getBooleanInput(
			'show_sub_categories',
			$config->show_sub_categories ?? '1'
		);

		$options                          = [];
		$options[]                        = HTMLHelper::_('select.option', 'list', Text::_('ESHOP_CONFIG_LIST'));
		$options[]                        = HTMLHelper::_('select.option', 'grid', Text::_('ESHOP_CONFIG_GRID'));
		$lists['default_products_layout'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'default_products_layout',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->default_products_layout ?? 'grid'
		);
		$options                          = [];
		$options[]                        = HTMLHelper::_(
			'select.option',
			'list_with_only_link',
			Text::_('ESHOP_CONFIG_SUB_CATEGORIES_LAYOUT_LIST_WITH_ONLY_LINK')
		);
		$options[]                        = HTMLHelper::_(
			'select.option',
			'list_with_image',
			Text::_('ESHOP_CONFIG_SUB_CATEGORIES_LAYOUT_LIST_WITH_IMAGE_AND_LINK')
		);
		$lists['sub_categories_layout']   = HTMLHelper::_(
			'select.genericlist',
			$options,
			'sub_categories_layout',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->sub_categories_layout ?? 'list_with_image'
		);
		$lists['show_quantity_box']       = EShopHtmlHelper::getBooleanInput(
			'show_quantity_box',
			$config->show_quantity_box ?? '1'
		);
		$lists['show_product_attributes'] = EShopHtmlHelper::getBooleanInput(
			'show_product_attributes',
			$config->show_product_attributes ?? '0'
		);

		$lists['table_show_image']              = EShopHtmlHelper::getBooleanInput(
			'table_show_image',
			$config->table_show_image ?? '1'
		);
		$lists['table_show_short_description']  = EShopHtmlHelper::getBooleanInput(
			'table_show_short_description',
			$config->table_show_short_description ?? '1'
		);
		$lists['table_show_category']           = EShopHtmlHelper::getBooleanInput(
			'table_show_category',
			$config->table_show_category ?? '1'
		);
		$lists['table_show_manufacturer']       = EShopHtmlHelper::getBooleanInput(
			'table_show_manufacturer',
			$config->table_show_manufacturer ?? '1'
		);
		$lists['table_show_price']              = EShopHtmlHelper::getBooleanInput(
			'table_show_price',
			$config->table_show_price ?? '1'
		);
		$lists['table_show_availability']       = EShopHtmlHelper::getBooleanInput(
			'table_show_availability',
			$config->table_show_availability ?? '1'
		);
		$lists['table_show_product_attributes'] = EShopHtmlHelper::getBooleanInput(
			'table_show_product_attributes',
			$config->table_show_product_attributes ?? '0'
		);
		$lists['table_show_quantity_box']       = EShopHtmlHelper::getBooleanInput(
			'table_show_quantity_box',
			$config->table_show_quantity_box ?? '1'
		);
		$lists['table_show_actions']            = EShopHtmlHelper::getBooleanInput(
			'table_show_actions',
			$config->table_show_actions ?? '1'
		);

		//Product Custom Fields
		if (EShopHelper::getConfigValue('product_custom_fields'))
		{
			$xml    = simplexml_load_file(JPATH_ROOT . '/components/com_eshop/fields.xml');
			$fields = $xml->fields->fieldset->children();

			foreach ($fields as $field)
			{
				$name                         = $field->attributes()->name;
				$lists['table_show_' . $name] = EShopHtmlHelper::getBooleanInput(
					'table_show_' . $name,
					$config->{'table_show_' . $name} ?? '1'
				);
			}
		}

		$lists['show_quantity_box_in_product_page'] = EShopHtmlHelper::getBooleanInput(
			'show_quantity_box_in_product_page',
			$config->show_quantity_box_in_product_page ?? '1'
		);
		$lists['show_short_desc_in_product_page']   = EShopHtmlHelper::getBooleanInput(
			'show_short_desc_in_product_page',
			$config->show_short_desc_in_product_page ?? '0'
		);
		$lists['invoice_enable']                    = EShopHtmlHelper::getBooleanInput(
			'invoice_enable',
			$config->invoice_enable ?? '0'
		);
		$lists['always_generate_invoice']           = EShopHtmlHelper::getBooleanInput(
			'always_generate_invoice',
			$config->always_generate_invoice ?? '0'
		);
		$lists['send_invoice_to_customer']          = EShopHtmlHelper::getBooleanInput(
			'send_invoice_to_customer',
			$config->send_invoice_to_customer ?? '0'
		);
		$lists['send_invoice_to_admin']             = EShopHtmlHelper::getBooleanInput(
			'send_invoice_to_admin',
			$config->send_invoice_to_admin ?? '0'
		);
		$lists['reset_invoice_number']              = EShopHtmlHelper::getBooleanInput(
			'reset_invoice_number',
			$config->reset_invoice_number ?? '1'
		);

		$fontsPath = JPATH_ROOT . '/components/com_eshop/tcpdf/fonts/';

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'courier', Text::_('Courier'));
		$options[] = HTMLHelper::_('select.option', 'helvetica', Text::_('Helvetica'));
		$options[] = HTMLHelper::_('select.option', 'symbol', Text::_('Symbol'));
		$options[] = HTMLHelper::_('select.option', 'times', Text::_('Times New Roman'));
		$options[] = HTMLHelper::_('select.option', 'zapfdingbats', Text::_('Zapf Dingbats'));
		$options[] = HTMLHelper::_('select.option', 'angsanaupc', Text::_('AngsanaUPC'));

		$additionalFonts = [
			'aealarabiya',
			'aefurat',
			'dejavusans',
			'dejavuserif',
			'freemono',
			'freesans',
			'freeserif',
			'hysmyeongjostdmedium',
			'kozgopromedium',
			'kozminproregular',
			'msungstdlight',
			'droidsansfallback',
		];

		foreach ($additionalFonts as $fontName)
		{
			if (file_exists($fontsPath . $fontName . '.php'))
			{
				$options[] = HTMLHelper::_('select.option', $fontName, ucfirst($fontName));
			}
		}

		$lists['pdf_font'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'pdf_font',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->pdf_font ?? 'times'
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'absolutely', Text::_('ESHOP_PDF_IMAGE_PATH_ABSOLUTELY'));
		$options[] = HTMLHelper::_('select.option', 'relatively', Text::_('ESHOP_PDF_IMAGE_PATH_RELATIVELY'));

		$lists['pdf_image_path'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'pdf_image_path',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->pdf_image_path ?? 'absolutely'
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'product_sku', Text::_('ESHOP_PRODUCT_SKU_FIELD'));
		$options[] = HTMLHelper::_('select.option', 'product_image', Text::_('ESHOP_PRODUCT_IMAGE_FIELD'));
		$options[] = HTMLHelper::_('select.option', 'product_quantity', Text::_('ESHOP_PRODUCT_QUANTITY_FIELD'));
		$options[] = HTMLHelper::_('select.option', 'product_custom_message', Text::_('ESHOP_PRODUCT_CUSTOM_MESSAGE'));

		$productFieldsDisplay            = explode(',', $config->product_fields_display ?? '');
		$lists['product_fields_display'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'product_fields_display[]',
			'class="input-xlarge form-select" multiple',
			'value',
			'text',
			$productFieldsDisplay
		);

		$lists['product_image_rollover']           = EShopHtmlHelper::getBooleanInput(
			'product_image_rollover',
			$config->product_image_rollover ?? '0'
		);
		$lists['autoplay']                         = EShopHtmlHelper::getBooleanInput('autoplay', $config->autoplay ?? '0');
		$lists['recreate_watermark_images']        = EShopHtmlHelper::getBooleanInput(
			'recreate_watermark_images',
			$config->recreate_watermark_images ?? '0'
		);
		$lists['product_use_image_watermarks']     = EShopHtmlHelper::getBooleanInput(
			'product_use_image_watermarks',
			$config->product_use_image_watermarks ?? '0'
		);
		$lists['category_use_image_watermarks']    = EShopHtmlHelper::getBooleanInput(
			'category_use_image_watermarks',
			$config->category_use_image_watermarks ?? '0'
		);
		$lists['manufacture_use_image_watermarks'] = EShopHtmlHelper::getBooleanInput(
			'manufacture_use_image_watermarks',
			$config->manufacture_use_image_watermarks ?? '0'
		);

		//WaterMark
		$options                     = [];
		$options[]                   = HTMLHelper::_('select.option', '1', Text::_('ESHOP_WATERMARK_TOP_LEFT'));
		$options[]                   = HTMLHelper::_('select.option', '2', Text::_('ESHOP_WATERMARK_TOP_CENTER'));
		$options[]                   = HTMLHelper::_('select.option', '3', Text::_('ESHOP_WATERMARK_TOP_RIGHT'));
		$options[]                   = HTMLHelper::_('select.option', '4', Text::_('ESHOP_WATERMARK_MIDDLE_RIGHT'));
		$options[]                   = HTMLHelper::_('select.option', '5', Text::_('ESHOP_WATERMARK_MIDDLE_CENTER'));
		$options[]                   = HTMLHelper::_('select.option', '6', Text::_('ESHOP_WATERMARK_MIDDLE_LEFT'));
		$options[]                   = HTMLHelper::_('select.option', '7', Text::_('ESHOP_WATERMARK_BOTTOM_RIGHT'));
		$options[]                   = HTMLHelper::_('select.option', '8', Text::_('ESHOP_WATERMARK_BOTTOM_CENTER'));
		$options[]                   = HTMLHelper::_('select.option', '9', Text::_('ESHOP_WATERMARK_BOTTOM_LEFT'));
		$lists['watermark_position'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'watermark_position',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->watermark_position ?? '1'
		);

		$options                 = [];
		$options[]               = HTMLHelper::_('select.option', '1', Text::_('ESHOP_WATERMARK_TEXT'));
		$options[]               = HTMLHelper::_('select.option', '2', Text::_('ESHOP_WATERMARK_IMAGE'));
		$lists['watermark_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'watermark_type',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->watermark_type ?? '1'
		);

		$options                 = [];
		$options[]               = HTMLHelper::_('select.option', 'arial.ttf', 'Unicode');
		$options[]               = HTMLHelper::_('select.option', 'Exo2-Bold.ttf', 'Non-Unicode');
		$options[]               = HTMLHelper::_('select.option', 'koodak1.ttf', 'Arab & Persian');
		$lists['watermark_font'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'watermark_font',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->watermark_font ?? 'arial.ttf'
		);

		$options                     = [];
		$options[]                   = HTMLHelper::_('select.option', '10', '10 px');
		$options[]                   = HTMLHelper::_('select.option', '20', '20 px');
		$options[]                   = HTMLHelper::_('select.option', '30', '30 px');
		$options[]                   = HTMLHelper::_('select.option', '40', '40 px');
		$options[]                   = HTMLHelper::_('select.option', '50', '50 px');
		$options[]                   = HTMLHelper::_('select.option', '60', '60 px');
		$lists['watermark_fontsize'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'watermark_fontsize',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->watermark_fontsize ?? '10'
		);

		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', '245,43,16', Text::_('ESHOP_WATERMARK_RED'));
		$options[]                = HTMLHelper::_('select.option', '29,188,13', Text::_('ESHOP_WATERMARK_GREEN'));
		$options[]                = HTMLHelper::_('select.option', '16,91,242', Text::_('ESHOP_WATERMARK_BLUE'));
		$options[]                = HTMLHelper::_('select.option', '237,245,16', Text::_('ESHOP_WATERMARK_YELLOW'));
		$options[]                = HTMLHelper::_('select.option', '246,151,16', Text::_('ESHOP_WATERMARK_ORANGE'));
		$options[]                = HTMLHelper::_('select.option', '0,0,0', Text::_('ESHOP_WATERMARK_BLACK'));
		$options[]                = HTMLHelper::_('select.option', '255,255,255', Text::_('ESHOP_WATERMARK_WHITE'));
		$options[]                = HTMLHelper::_('select.option', '59,75,65', Text::_('ESHOP_WATERMARK_GRAY'));
		$lists['watermark_color'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'watermark_color',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->watermark_color ?? '245,43,16'
		);

		//Compare page options
		$lists['compare_image']        = EShopHtmlHelper::getBooleanInput(
			'compare_image',
			$config->compare_image ?? '1'
		);
		$lists['compare_price']        = EShopHtmlHelper::getBooleanInput(
			'compare_price',
			$config->compare_price ?? '1'
		);
		$lists['compare_sku']          = EShopHtmlHelper::getBooleanInput('compare_sku', $config->compare_sku ?? '1');
		$lists['compare_manufacturer'] = EShopHtmlHelper::getBooleanInput(
			'compare_manufacturer',
			$config->compare_manufacturer ?? '1'
		);
		$lists['compare_availability'] = EShopHtmlHelper::getBooleanInput(
			'compare_availability',
			$config->compare_availability ?? '1'
		);
		$lists['compare_rating']       = EShopHtmlHelper::getBooleanInput(
			'compare_rating',
			$config->compare_rating ?? '1'
		);
		$lists['compare_short_desc']   = EShopHtmlHelper::getBooleanInput(
			'compare_short_desc',
			$config->compare_short_desc ?? '1'
		);
		$lists['compare_desc']         = EShopHtmlHelper::getBooleanInput('compare_desc', $config->compare_desc ?? '1');
		$lists['compare_weight']       = EShopHtmlHelper::getBooleanInput(
			'compare_weight',
			$config->compare_weight ?? '1'
		);
		$lists['compare_dimensions']   = EShopHtmlHelper::getBooleanInput(
			'compare_dimensions',
			$config->compare_dimensions ?? '1'
		);
		$lists['compare_attributes']   = EShopHtmlHelper::getBooleanInput(
			'compare_attributes',
			$config->compare_attributes ?? '1'
		);

		//Customer page options
		$lists['customer_manage_account']  = EShopHtmlHelper::getBooleanInput(
			'customer_manage_account',
			$config->customer_manage_account ?? '1'
		);
		$lists['customer_manage_order']    = EShopHtmlHelper::getBooleanInput(
			'customer_manage_order',
			$config->customer_manage_order ?? '1'
		);
		$lists['customer_manage_quote']    = EShopHtmlHelper::getBooleanInput(
			'customer_manage_quote',
			$config->customer_manage_quote ?? '1'
			);
		$lists['customer_manage_download'] = EShopHtmlHelper::getBooleanInput(
			'customer_manage_download',
			$config->customer_manage_download ?? '1'
		);
		$lists['customer_manage_address']  = EShopHtmlHelper::getBooleanInput(
			'customer_manage_address',
			$config->customer_manage_address ?? '1'
		);
		$lists['debug_mode']               = EShopHtmlHelper::getBooleanInput('debug_mode', $config->debug_mode ?? '0');

		//Quote form fields
		$lists['quote_form_name_published']      = EShopHtmlHelper::getBooleanInput(
			'quote_form_name_published',
			$config->quote_form_name_published ?? '1'
		);
		$lists['quote_form_name_required']       = EShopHtmlHelper::getBooleanInput(
			'quote_form_name_required',
			$config->quote_form_name_required ?? '1'
		);
		$lists['quote_form_email_published']     = EShopHtmlHelper::getBooleanInput(
			'quote_form_email_published',
			$config->quote_form_email_published ?? '1'
		);
		$lists['quote_form_email_required']      = EShopHtmlHelper::getBooleanInput(
			'quote_form_email_required',
			$config->quote_form_email_required ?? '1'
		);
		$lists['quote_form_company_published']   = EShopHtmlHelper::getBooleanInput(
			'quote_form_company_published',
			$config->quote_form_company_published ?? '1'
		);
		$lists['quote_form_company_required']    = EShopHtmlHelper::getBooleanInput(
			'quote_form_company_required',
			$config->quote_form_company_required ?? '0'
		);
		$lists['quote_form_telephone_published'] = EShopHtmlHelper::getBooleanInput(
			'quote_form_telephone_published',
			$config->quote_form_telephone_published ?? '1'
		);
		$lists['quote_form_telephone_required']  = EShopHtmlHelper::getBooleanInput(
			'quote_form_telephone_required',
			$config->quote_form_telephone_required ?? '0'
		);
		$lists['quote_form_address_published']   = EShopHtmlHelper::getBooleanInput(
			'quote_form_address_published',
			$config->quote_form_address_published ?? '0'
		);
		$lists['quote_form_address_required']    = EShopHtmlHelper::getBooleanInput(
			'quote_form_address_required',
			$config->quote_form_address_required ?? '0'
		);
		$lists['quote_form_city_published']      = EShopHtmlHelper::getBooleanInput(
			'quote_form_city_published',
			$config->quote_form_city_published ?? '0'
		);
		$lists['quote_form_city_required']       = EShopHtmlHelper::getBooleanInput(
			'quote_form_city_required',
			$config->quote_form_city_required ?? '0'
		);
		$lists['quote_form_postcode_published']  = EShopHtmlHelper::getBooleanInput(
			'quote_form_postcode_published',
			$config->quote_form_postcode_published ?? '0'
		);
		$lists['quote_form_postcode_required']   = EShopHtmlHelper::getBooleanInput(
			'quote_form_postcode_required',
			$config->quote_form_postcode_required ?? '0'
		);
		$lists['quote_form_country_published']   = EShopHtmlHelper::getBooleanInput(
			'quote_form_country_published',
			$config->quote_form_country_published ?? '0'
		);
		$lists['quote_form_country_required']    = EShopHtmlHelper::getBooleanInput(
			'quote_form_country_required',
			$config->quote_form_country_required ?? '0'
		);
		$lists['quote_form_state_published']     = EShopHtmlHelper::getBooleanInput(
			'quote_form_state_published',
			$config->quote_form_state_published ?? '0'
		);
		$lists['quote_form_state_required']      = EShopHtmlHelper::getBooleanInput(
			'quote_form_state_required',
			$config->quote_form_state_required ?? '0'
		);
		$lists['quote_form_message_published']   = EShopHtmlHelper::getBooleanInput(
			'quote_form_message_published',
			$config->quote_form_message_published ?? '1'
		);
		$lists['quote_form_message_required']    = EShopHtmlHelper::getBooleanInput(
			'quote_form_message_required',
			$config->quote_form_message_required ?? '1'
		);
		
		$lists['use_button_icons']    = EShopHtmlHelper::getBooleanInput(
			'use_button_icons',
			$config->use_button_icons ?? '0'
			);

		//Product sort options list
		$sortOptions = $config->sort_options ?? 'b.product_name-ASC';
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

		$sortTexts = [
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_ORDERING_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_ORDERING_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_NAME_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_NAME_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_SKU_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_SKU_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_PRICE_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_PRICE_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_LENGTH_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_LENGTH_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_WIDTH_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_WIDTH_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_HEIGHT_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_HEIGHT_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_WEIGHT_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_WEIGHT_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_QUANTITY_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_QUANTITY_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_AVAILABLE_DATE_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_AVAILABLE_DATE_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_SHORT_DESC_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_SHORT_DESC_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_DESC_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_DESC_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_RATES_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_RATES_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_REVIEWS_ASC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_REVIEWS_DESC'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_LATEST'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_OLDEST'),
			Text::_('ESHOP_CONFIG_SORTING_PRODUCT_BEST_SELLERS'),
			Text::_('ESHOP_CONFIG_SORTING_RANDOMLY'),
		];

		$options = [];

		for ($i = 0; $n = count($sortValues), $i < $n; $i++)
		{
			$options[] = HTMLHelper::_('select.option', $sortValues[$i], $sortTexts[$i]);
		}
		$lists['default_sorting'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'default_sorting',
			' class="input-xlarge form-select" style="width: 250px;" ',
			'value',
			'text',
			$config->default_sorting ?? 'a.ordering-ASC'
		);

		//Category sort options list
		$options                           = [];
		$options[]                         = HTMLHelper::_('select.option', 'name-asc', Text::_('ESHOP_CONFIG_SORTING_CATEGORY_NAME_ASC'));
		$options[]                         = HTMLHelper::_('select.option', 'name-desc', Text::_('ESHOP_CONFIG_SORTING_CATEGORY_NAME_DESC'));
		$options[]                         = HTMLHelper::_('select.option', 'ordering-asc', Text::_('ESHOP_CONFIG_SORTING_CATEGORY_ORDERING_ASC'));
		$options[]                         = HTMLHelper::_('select.option', 'ordering-desc', Text::_('ESHOP_CONFIG_SORTING_CATEGORY_ORDERING_DESC'));
		$options[]                         = HTMLHelper::_('select.option', 'id-asc', Text::_('ESHOP_CONFIG_SORTING_CATEGORY_LATEST'));
		$options[]                         = HTMLHelper::_('select.option', 'id-desc', Text::_('ESHOP_CONFIG_SORTING_CATEGORY_OLDEST'));
		$options[]                         = HTMLHelper::_('select.option', 'random', Text::_('ESHOP_CONFIG_SORTING_RANDOMLY'));
		$lists['category_default_sorting'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'category_default_sorting',
			'class="input-xlarge form-select" style="width: 250px;" ',
			'value',
			'text',
			$config->category_default_sorting ?? 'name-asc'
		);

		//Manufacturer sort options list
		$options                               = [];
		$options[]                             = HTMLHelper::_('select.option', 'name-asc', Text::_('ESHOP_CONFIG_SORTING_MANUFACTURER_NAME_ASC'));
		$options[]                             = HTMLHelper::_('select.option', 'name-desc', Text::_('ESHOP_CONFIG_SORTING_MANUFACTURER_NAME_DESC'));
		$options[]                             = HTMLHelper::_(
			'select.option',
			'ordering-asc',
			Text::_('ESHOP_CONFIG_SORTING_MANUFACTURER_ORDERING_ASC')
		);
		$options[]                             = HTMLHelper::_(
			'select.option',
			'ordering-desc',
			Text::_('ESHOP_CONFIG_SORTING_MANUFACTURER_ORDERING_DESC')
		);
		$options[]                             = HTMLHelper::_('select.option', 'id-asc', Text::_('ESHOP_CONFIG_SORTING_MANUFACTURER_LATEST'));
		$options[]                             = HTMLHelper::_('select.option', 'id-desc', Text::_('ESHOP_CONFIG_SORTING_MANUFACTURER_OLDEST'));
		$options[]                             = HTMLHelper::_('select.option', 'random', Text::_('ESHOP_CONFIG_SORTING_RANDOMLY'));
		$lists['manufacturer_default_sorting'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'manufacturer_default_sorting',
			'class="input-xlarge form-select" style="width: 250px;" ',
			'value',
			'text',
			$config->manufacturer_default_sorting ?? 'name-asc'
		);

		//Option sort options list
		$options                         = [];
		$options[]                       = HTMLHelper::_('select.option', 'name-asc', Text::_('ESHOP_CONFIG_SORTING_OPTION_NAME_ASC'));
		$options[]                       = HTMLHelper::_('select.option', 'name-desc', Text::_('ESHOP_CONFIG_SORTING_OPTION_NAME_DESC'));
		$options[]                       = HTMLHelper::_('select.option', 'ordering-asc', Text::_('ESHOP_CONFIG_SORTING_OPTION_ORDERING_ASC'));
		$options[]                       = HTMLHelper::_('select.option', 'ordering-desc', Text::_('ESHOP_CONFIG_SORTING_OPTION_ORDERING_DESC'));
		$options[]                       = HTMLHelper::_('select.option', 'id-asc', Text::_('ESHOP_CONFIG_SORTING_OPTION_LATEST'));
		$options[]                       = HTMLHelper::_('select.option', 'id-desc', Text::_('ESHOP_CONFIG_SORTING_OPTION_OLDEST'));
		$options[]                       = HTMLHelper::_('select.option', 'random', Text::_('ESHOP_CONFIG_SORTING_RANDOMLY'));
		$lists['option_default_sorting'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'option_default_sorting',
			'class="input-xlarge form-select" style="width: 250px;" ',
			'value',
			'text',
			$config->option_default_sorting ?? 'name-asc'
		);

		//Option value sort options list
		$options                               = [];
		$options[]                             = HTMLHelper::_('select.option', 'name-asc', Text::_('ESHOP_CONFIG_SORTING_OPTION_VALUE_NAME_ASC'));
		$options[]                             = HTMLHelper::_('select.option', 'name-desc', Text::_('ESHOP_CONFIG_SORTING_OPTION_VALUE_NAME_DESC'));
		$options[]                             = HTMLHelper::_(
			'select.option',
			'ordering-asc',
			Text::_('ESHOP_CONFIG_SORTING_OPTION_VALUE_ORDERING_ASC')
		);
		$options[]                             = HTMLHelper::_(
			'select.option',
			'ordering-desc',
			Text::_('ESHOP_CONFIG_SORTING_OPTION_VALUE_ORDERING_DESC')
		);
		$options[]                             = HTMLHelper::_('select.option', 'id-asc', Text::_('ESHOP_CONFIG_SORTING_OPTION_VALUE_LATEST'));
		$options[]                             = HTMLHelper::_('select.option', 'id-desc', Text::_('ESHOP_CONFIG_SORTING_OPTION_VALUE_OLDEST'));
		$options[]                             = HTMLHelper::_('select.option', 'random', Text::_('ESHOP_CONFIG_SORTING_RANDOMLY'));
		$lists['option_value_default_sorting'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'option_value_default_sorting',
			'class="input-xlarge form-select" style="width: 250px;" ',
			'value',
			'text',
			$config->option_value_default_sorting ?? 'name-asc'
		);

		//Attribute sort options list
		$options                            = [];
		$options[]                          = HTMLHelper::_('select.option', 'name-asc', Text::_('ESHOP_CONFIG_SORTING_ATTRIBUTE_NAME_ASC'));
		$options[]                          = HTMLHelper::_('select.option', 'name-desc', Text::_('ESHOP_CONFIG_SORTING_ATTRIBUTE_NAME_DESC'));
		$options[]                          = HTMLHelper::_('select.option', 'ordering-asc', Text::_('ESHOP_CONFIG_SORTING_ATTRIBUTE_ORDERING_ASC'));
		$options[]                          = HTMLHelper::_(
			'select.option',
			'ordering-desc',
			Text::_('ESHOP_CONFIG_SORTING_ATTRIBUTE_ORDERING_DESC')
		);
		$options[]                          = HTMLHelper::_('select.option', 'id-asc', Text::_('ESHOP_CONFIG_SORTING_ATTRIBUTE_LATEST'));
		$options[]                          = HTMLHelper::_('select.option', 'id-desc', Text::_('ESHOP_CONFIG_SORTING_ATTRIBUTE_OLDEST'));
		$options[]                          = HTMLHelper::_('select.option', 'random', Text::_('ESHOP_CONFIG_SORTING_RANDOMLY'));
		$lists['attribute_default_sorting'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'attribute_default_sorting',
			'class="input-xlarge form-select" style="width: 250px;" ',
			'value',
			'text',
			$config->attribute_default_sorting ?? 'name-asc'
		);

		//Image
		$options                                   = [];
		$options[]                                 = HTMLHelper::_('select.option', 'notResizeImage', Text::_('ESHOP_NOT_RESIZE_IMAGE'));
		$options[]                                 = HTMLHelper::_('select.option', 'resizeImage', Text::_('ESHOP_RESIZE_IMAGE'));
		$options[]                                 = HTMLHelper::_('select.option', 'cropsizeImage', Text::_('ESHOP_CROPSIZE_IMAGE'));
		$options[]                                 = HTMLHelper::_('select.option', 'maxsizeImage', Text::_('ESHOP_MAXSIZE_IMAGE'));
		$lists['category_image_size_function']     = HTMLHelper::_(
			'select.genericlist',
			$options,
			'category_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->category_image_size_function ?? 'resizeImage'
		);
		$lists['manufacturer_image_size_function'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'manufacturer_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->manufacturer_image_size_function ?? 'resizeImage'
		);
		$lists['thumb_image_size_function']        = HTMLHelper::_(
			'select.genericlist',
			$options,
			'thumb_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->thumb_image_size_function ?? 'resizeImage'
		);
		$lists['popup_image_size_function']        = HTMLHelper::_(
			'select.genericlist',
			$options,
			'popup_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->popup_image_size_function ?? 'resizeImage'
		);
		$lists['list_image_size_function']         = HTMLHelper::_(
			'select.genericlist',
			$options,
			'list_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->list_image_size_function ?? 'resizeImage'
		);
		$lists['additional_image_size_function']   = HTMLHelper::_(
			'select.genericlist',
			$options,
			'additional_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->additional_image_size_function ?? 'resizeImage'
		);
		$lists['related_image_size_function']      = HTMLHelper::_(
			'select.genericlist',
			$options,
			'related_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->related_image_size_function ?? 'resizeImage'
		);
		$lists['compare_image_size_function']      = HTMLHelper::_(
			'select.genericlist',
			$options,
			'compare_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->compare_image_size_function ?? 'resizeImage'
		);
		$lists['wishlist_image_size_function']     = HTMLHelper::_(
			'select.genericlist',
			$options,
			'wishlist_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->wishlist_image_size_function ?? 'resizeImage'
		);
		$lists['cart_image_size_function']         = HTMLHelper::_(
			'select.genericlist',
			$options,
			'cart_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->cart_image_size_function ?? 'resizeImage'
		);
		$lists['label_image_size_function']        = HTMLHelper::_(
			'select.genericlist',
			$options,
			'label_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->label_image_size_function ?? 'resizeImage'
		);
		$lists['option_image_size_function']       = HTMLHelper::_(
			'select.genericlist',
			$options,
			'option_image_size_function',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->option_image_size_function ?? 'resizeImage'
		);

		$options             = [];
		$options[]           = HTMLHelper::_('select.option', 'popout', Text::_('ESHOP_CONFIG_VIEW_IMAGE_POPOUT'));
		$options[]           = HTMLHelper::_('select.option', 'zoom', Text::_('ESHOP_CONFIG_VIEW_IMAGE_ZOOM'));
		$lists['view_image'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'view_image',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->view_image ?? 'popout'
		);

		$options             = [];
		$options[]           = HTMLHelper::_('select.option', '1.5', '1.5');
		$options[]           = HTMLHelper::_('select.option', '2.0', '2.0');
		$options[]           = HTMLHelper::_('select.option', '2.5', '2.5');
		$options[]           = HTMLHelper::_('select.option', '3.0', '3.0');
		$options[]           = HTMLHelper::_('select.option', '3.5', '3.5');
		$lists['zoom_scale'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'zoom_scale',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->zoom_scale ?? '2.5'
		);

		//Social
		$options              = [];
		$options[]            = HTMLHelper::_('select.option', 'arial', 'arial');
		$options[]            = HTMLHelper::_('select.option', 'lucida grande', 'lucida grande');
		$options[]            = HTMLHelper::_('select.option', 'segoe ui', 'segoe ui');
		$options[]            = HTMLHelper::_('select.option', 'tahoma', 'tahoma');
		$options[]            = HTMLHelper::_('select.option', 'trebuchet ms', 'trebuchet ms');
		$options[]            = HTMLHelper::_('select.option', 'verdana', 'verdana');
		$lists['button_font'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'button_font',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->button_font ?? 'arial'
		);

		$options               = [];
		$options[]             = HTMLHelper::_('select.option', 'light', 'light');
		$options[]             = HTMLHelper::_('select.option', 'dark', 'dark');
		$lists['button_theme'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'button_theme',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->button_theme ?? 'light'
		);

		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', 'af_ZA', 'Afrikaans');
		$options[]                = HTMLHelper::_('select.option', 'ar_AR', 'Arabic');
		$options[]                = HTMLHelper::_('select.option', 'az_AZ', 'Azerbaijani');
		$options[]                = HTMLHelper::_('select.option', 'be_BY', 'Belarusian');
		$options[]                = HTMLHelper::_('select.option', 'bg_BG', 'Bulgarian');
		$options[]                = HTMLHelper::_('select.option', 'bn_IN', 'Bengali');
		$options[]                = HTMLHelper::_('select.option', 'bs_BA', 'Bosnian');
		$options[]                = HTMLHelper::_('select.option', 'ca_ES', 'Catalan');
		$options[]                = HTMLHelper::_('select.option', 'cs_CZ', 'Czech');
		$options[]                = HTMLHelper::_('select.option', 'cy_GB', 'Welsh');
		$options[]                = HTMLHelper::_('select.option', 'da_DK', 'Danish');
		$options[]                = HTMLHelper::_('select.option', 'de_DE', 'German');
		$options[]                = HTMLHelper::_('select.option', 'el_GR', 'Greek');
		$options[]                = HTMLHelper::_('select.option', 'en_GB', 'English (UK)');
		$options[]                = HTMLHelper::_('select.option', 'en_PI', 'English (Pirate)');
		$options[]                = HTMLHelper::_('select.option', 'en_UD', 'English (Upside Down)');
		$options[]                = HTMLHelper::_('select.option', 'en_US', 'English (US)');
		$options[]                = HTMLHelper::_('select.option', 'eo_EO', 'Esperanto');
		$options[]                = HTMLHelper::_('select.option', 'es_ES', 'Spanish (Spain)');
		$options[]                = HTMLHelper::_('select.option', 'es_LA', 'Spanish');
		$options[]                = HTMLHelper::_('select.option', 'et_EE', 'Estonian');
		$options[]                = HTMLHelper::_('select.option', 'eu_ES', 'Basque');
		$options[]                = HTMLHelper::_('select.option', 'fa_IR', 'Persian');
		$options[]                = HTMLHelper::_('select.option', 'fb_LT', 'Leet Speak');
		$options[]                = HTMLHelper::_('select.option', 'fi_FI', 'Finnish');
		$options[]                = HTMLHelper::_('select.option', 'fo_FO', 'Faroese');
		$options[]                = HTMLHelper::_('select.option', 'fr_CA', 'French (Canada)');
		$options[]                = HTMLHelper::_('select.option', 'fr_FR', 'French (France)');
		$options[]                = HTMLHelper::_('select.option', 'fy_NL', 'Frisian');
		$options[]                = HTMLHelper::_('select.option', 'ga_IE', 'Irish');
		$options[]                = HTMLHelper::_('select.option', 'gl_ES', 'Galician');
		$options[]                = HTMLHelper::_('select.option', 'he_IL', 'Hebrew');
		$options[]                = HTMLHelper::_('select.option', 'hi_IN', 'Hindi');
		$options[]                = HTMLHelper::_('select.option', 'hr_HR', 'Croatian');
		$options[]                = HTMLHelper::_('select.option', 'hu_HU', 'Hungarian');
		$options[]                = HTMLHelper::_('select.option', 'hy_AM', 'Armenian');
		$options[]                = HTMLHelper::_('select.option', 'id_ID', 'Indonesian');
		$options[]                = HTMLHelper::_('select.option', 'is_IS', 'Icelandic');
		$options[]                = HTMLHelper::_('select.option', 'it_IT', 'Italian');
		$options[]                = HTMLHelper::_('select.option', 'ja_JP', 'Japanese');
		$options[]                = HTMLHelper::_('select.option', 'ka_GE', 'Georgian');
		$options[]                = HTMLHelper::_('select.option', 'km_KH', 'Khmer');
		$options[]                = HTMLHelper::_('select.option', 'ko_KR', 'Korean');
		$options[]                = HTMLHelper::_('select.option', 'ku_TR', 'Kurdish');
		$options[]                = HTMLHelper::_('select.option', 'la_VA', 'Latin');
		$options[]                = HTMLHelper::_('select.option', 'lt_LT', 'Lithuanian');
		$options[]                = HTMLHelper::_('select.option', 'lv_LV', 'Latvian');
		$options[]                = HTMLHelper::_('select.option', 'mk_MK', 'Macedonian');
		$options[]                = HTMLHelper::_('select.option', 'ml_IN', 'Malayalam');
		$options[]                = HTMLHelper::_('select.option', 'ms_MY', 'Malay');
		$options[]                = HTMLHelper::_('select.option', 'nb_NO', 'Norwegian (bokmal)');
		$options[]                = HTMLHelper::_('select.option', 'ne_NP', 'Nepali');
		$options[]                = HTMLHelper::_('select.option', 'nl_NL', 'Dutch');
		$options[]                = HTMLHelper::_('select.option', 'nn_NO', 'Norwegian (nynorsk)');
		$options[]                = HTMLHelper::_('select.option', 'pa_IN', 'Punjabi');
		$options[]                = HTMLHelper::_('select.option', 'pl_PL', 'Polish');
		$options[]                = HTMLHelper::_('select.option', 'ps_AF', 'Pashto');
		$options[]                = HTMLHelper::_('select.option', 'pt_BR', 'Portuguese (Brazil)');
		$options[]                = HTMLHelper::_('select.option', 'pt_PT', 'Portuguese (Portugal)');
		$options[]                = HTMLHelper::_('select.option', 'ro_RO', 'Romanian');
		$options[]                = HTMLHelper::_('select.option', 'ru_RU', 'Russian');
		$options[]                = HTMLHelper::_('select.option', 'sk_SK', 'Slovak');
		$options[]                = HTMLHelper::_('select.option', 'sl_SI', 'Slovenian');
		$options[]                = HTMLHelper::_('select.option', 'sq_AL', 'Albanian');
		$options[]                = HTMLHelper::_('select.option', 'sr_RS', 'Serbian');
		$options[]                = HTMLHelper::_('select.option', 'sv_SE', 'Swedish');
		$options[]                = HTMLHelper::_('select.option', 'sw_KE', 'Swahili');
		$options[]                = HTMLHelper::_('select.option', 'ta_IN', 'Tamil');
		$options[]                = HTMLHelper::_('select.option', 'te_IN', 'Telugu');
		$options[]                = HTMLHelper::_('select.option', 'th_TH', 'Thai');
		$options[]                = HTMLHelper::_('select.option', 'tl_PH', 'Filipino');
		$options[]                = HTMLHelper::_('select.option', 'tr_TR', 'Turkish');
		$options[]                = HTMLHelper::_('select.option', 'uk_UA', 'Ukrainian');
		$options[]                = HTMLHelper::_('select.option', 'vi_VN', 'Vietnamese');
		$options[]                = HTMLHelper::_('select.option', 'zh_CN', 'Simplified Chinese (China)');
		$options[]                = HTMLHelper::_('select.option', 'zh_HK', 'Traditional Chinese (Hong Kong)');
		$options[]                = HTMLHelper::_('select.option', 'zh_TW', 'Traditional Chinese (Taiwan)');
		$lists['button_language'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'button_language',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->button_language ?? 'en_GB'
		);

		$options                = [];
		$options[]              = HTMLHelper::_('select.option', 'standard', 'standard');
		$options[]              = HTMLHelper::_('select.option', 'button_count', 'button_count');
		$options[]              = HTMLHelper::_('select.option', 'box_count', 'box_count');
		$lists['button_layout'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'button_layout',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->button_layout ?? 'standard'
		);

		$options                        = [];
		$options[]                      = HTMLHelper::_('select.option', 'top', 'Vertical');
		$options[]                      = HTMLHelper::_('select.option', 'right', 'Horizontal');
		$options[]                      = HTMLHelper::_('select.option', 'no-count', 'No Count');
		$lists['linkedin_layout']       = HTMLHelper::_(
			'select.genericlist',
			$options,
			'linkedin_layout',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->linkedin_layout ?? 'top'
		);
		$lists['social_enable']         = EShopHtmlHelper::getBooleanInput(
			'social_enable',
			$config->social_enable ?? '0'
		);
		$lists['show_facebook_button']  = EShopHtmlHelper::getBooleanInput(
			'show_facebook_button',
			$config->show_facebook_button ?? '1'
		);
		$lists['show_faces']            = EShopHtmlHelper::getBooleanInput('show_faces', $config->show_faces ?? '0');
		$lists['show_facebook_comment'] = EShopHtmlHelper::getBooleanInput(
			'show_facebook_comment',
			$config->show_facebook_comment ?? '1'
		);
		$lists['show_twitter_button']   = EShopHtmlHelper::getBooleanInput(
			'show_twitter_button',
			$config->show_twitter_button ?? '1'
		);
		$lists['show_pinit_button']     = EShopHtmlHelper::getBooleanInput(
			'show_pinit_button',
			$config->show_pinit_button ?? '1'
		);
		$lists['show_google_button']    = EShopHtmlHelper::getBooleanInput(
			'show_google_button',
			$config->show_google_button ?? '1'
		);
		$lists['show_linkedin_button']  = EShopHtmlHelper::getBooleanInput(
			'show_linkedin_button',
			$config->show_linkedin_button ?? '1'
		);
		$lists['add_category_path']     = EShopHtmlHelper::getBooleanInput(
			'add_category_path',
			$config->add_category_path ?? '1'
		);

		$options             = [];
		$options[]           = HTMLHelper::_('select.option', 'ga.js', 'Classic Google Analytics');
		$options[]           = HTMLHelper::_('select.option', 'analytics.js', 'Universal Analytics');
		$options[]           = HTMLHelper::_('select.option', 'gtag.js', 'Google Analytics 4');
		$lists['ga_js_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'ga_js_type',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->ga_js_type ?? 'ga.js'
		);

		$options                 = [];
		$options[]               = HTMLHelper::_('select.option', 'none', 'Dont send variation');
		$options[]               = HTMLHelper::_('select.option', 'category', 'Send category');
		$options[]               = HTMLHelper::_('select.option', 'variation', 'Send product options/variations');
		$lists['variation_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'variation_type',
			'class="input-xlarge form-select"',
			'value',
			'text',
			$config->variation_type ?? 'none'
		);

		// Initialize variables.
		if (version_compare(JVERSION, '3.0', 'ge') && Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
		{
			$languages = EShopHelper::getLanguages();
			for ($j = 0; $j < count($languages); $j++)
			{
				$query->clear();
				$rows = [];
				$query->select('a.id AS value, a.title AS text, a.level');
				$query->from('#__menu AS a');
				$query->join('LEFT', $db->quoteName('#__menu') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
				$query->where('a.menutype != ' . $db->quote(''));
				$query->where('a.component_id IN (SELECT extension_id FROM #__extensions WHERE element="com_eshop")');
				$query->where('a.client_id = 0');
				$query->where('a.published = 1');
				$query->where('a.language = "' . $languages[$j]->lang_code . '" || a.language="*"');
				$query->group('a.id, a.title, a.level, a.lft, a.rgt, a.menutype, a.parent_id, a.published');
				$query->order('a.lft ASC');

				// Get the options.
				$db->setQuery($query);
				$rows = $db->loadObjectList();

				// Pad the option text with spaces using depth level as a multiplier.
				for ($i = 0, $n = count($rows); $i < $n; $i++)
				{
					$rows[$i]->text = str_repeat('- ', $rows[$i]->level) . $rows[$i]->text;
				}
				$options   = [];
				$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'value', 'text');
				$rows      = array_merge($options, $rows);

				$lists['default_menu_item_' . $languages[$j]->lang_code] = HTMLHelper::_(
					'select.genericlist',
					$rows,
					'default_menu_item_' . $languages[$j]->lang_code,
					[
						'option.text.toHtml' => false,
						'option.text'        => 'text',
						'option.value'       => 'value',
						'list.attr'          => 'class="input-xlarge form-select"',
						'list.select'        => $config->{'default_menu_item_' . $languages[$j]->lang_code} ?? '0',
					]
				);
			}
			$this->languages = $languages;
		}
		else
		{
			$query->clear();
			$rows = [];
			$query->select('a.id AS value, a.title AS text, a.level');
			$query->from('#__menu AS a');
			$query->join('LEFT', $db->quoteName('#__menu') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
			$query->where('a.menutype != ' . $db->quote(''));
			$query->where('a.component_id IN (SELECT extension_id FROM #__extensions WHERE element="com_eshop")');
			$query->where('a.client_id = 0');
			$query->where('a.published = 1');
			$query->group('a.id, a.title, a.level, a.lft, a.rgt, a.menutype, a.parent_id, a.published');
			$query->order('a.lft ASC');

			// Get the options.
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			// Pad the option text with spaces using depth level as a multiplier.
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$rows[$i]->text = str_repeat('- ', $rows[$i]->level) . $rows[$i]->text;
			}
			$options   = [];
			$options[] = HTMLHelper::_('select.option', 0, Text::_('ESHOP_NONE'), 'value', 'text');
			$rows      = array_merge($options, $rows);

			$lists['default_menu_item'] = HTMLHelper::_(
				'select.genericlist',
				$rows,
				'default_menu_item',
				[
					'option.text.toHtml' => false,
					'option.text'        => 'text',
					'option.value'       => 'value',
					'list.attr'          => 'class="input-xlarge form-select"',
					'list.select'        => $config->default_menu_item ?? '0',
				]
			);
		}

		$lists['require_name_in_multiple_languages'] = EShopHtmlHelper::getBooleanInput(
			'require_name_in_multiple_languages',
			$config->require_name_in_multiple_languages ?? '1'
		);
		
		$lists['enable_canoncial_link'] = EShopHtmlHelper::getBooleanInput(
			'enable_canoncial_link',
			$config->enable_canoncial_link ?? '0'
			);

		$query->clear();
		$query->select('MAX(id)')
			->from('#__eshop_orders');
		$db->setQuery($query);
		$this->currentOrderId = $db->loadResult();
		$this->lists          = $lists;
		$this->config         = $config;
		$this->sortOptions    = $sortOptions;
		$this->sortValues     = $sortValues;
		$this->sortTexts      = $sortTexts;
		Factory::getApplication()->getDocument()->addScript(
			Uri::root(true) . '/administrator/components/com_eshop/assets/js/eshop.js'
		)->addScriptDeclaration(
			EShopHtmlHelper::getZonesArrayJs()
		);

		// Editor plugin for code editing
		$editorPlugin = null;

		if (PluginHelper::isEnabled('editors', 'codemirror'))
		{
			$editorPlugin = 'codemirror';
		}
		elseif (PluginHelper::isEnabled('editor', 'none'))
		{
			$editorPlugin = 'none';
		}

		if ($editorPlugin)
		{
			$this->editor = Editor::getInstance($editorPlugin);
		}

		parent::display($tpl);
	}
}