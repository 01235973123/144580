<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\Filesystem\File;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

?>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_ITEMS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo Text::_('ESHOP_CONFIG_DEFAULT_ITEMS_PER_PAGE'); ?><br />
			<span class="help"><?php echo Text::_('ESHOP_CONFIG_DEFAULT_ITEMS_PER_PAGE_HELP'); ?></span>
		</div>
		<div class="controls">
			<input class="input-mini form-control" type="text" name="catalog_limit" id="catalog_limit"  value="<?php echo $this->config->catalog_limit ?? ''; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span><?php echo Text::_('ESHOP_CONFIG_DEFAULT_ITEMS_PER_ROW'); ?><br />
			<span class="help"><?php echo Text::_('ESHOP_CONFIG_DEFAULT_ITEMS_PER_ROW_HELP'); ?></span>
		</div>
		<div class="controls">
			<input class="input-mini form-control" type="text" name="items_per_row" id="items_per_row"  value="<?php echo $this->config->items_per_row ?? ''; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('start_quantity_number', Text::_('ESHOP_CONFIG_START_QUANTITY_NUMBER'), Text::_('ESHOP_CONFIG_START_QUANTITY_NUMBER_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-mini form-control" type="text" name="start_quantity_number" id="start_quantity_number"  value="<?php echo $this->config->start_quantity_number ?? '1'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quantity_step', Text::_('ESHOP_CONFIG_QUANTITY_STEP'), Text::_('ESHOP_CONFIG_QUANTITY_STEP_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-mini form-control" type="text" name="quantity_step" id="quantity_step"  value="<?php echo $this->config->quantity_step ?? '1'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('shop_offline', Text::_('ESHOP_CONFIG_SHOP_OFFLINE'), Text::_('ESHOP_CONFIG_SHOP_OFFLINE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['shop_offline']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('catalog_mode', Text::_('ESHOP_CONFIG_CATALOG_MODE'), Text::_('ESHOP_CONFIG_CATALOG_MODE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['catalog_mode']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_cart_mode', Text::_('ESHOP_CONFIG_QUOTE_CART_MODE'), Text::_('ESHOP_CONFIG_QUOTE_CART_MODE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['quote_cart_mode']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('add_category_path', Text::_('ESHOP_CONFIG_ADD_CATEGORY_PATH'), Text::_('ESHOP_CONFIG_ADD_CATEGORY_PATH_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['add_category_path']; ?>
		</div>
	</div>
	<?php
	if (version_compare(JVERSION, '3.0', 'ge') && Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
	{
		$languages = $this->languages;
		for ($i = 0; $i < count($languages); $i++)
		{
			
			?>
			<div class="control-group">
				<div class="control-label">
					<?php echo EShopHtmlHelper::getFieldLabel('default_menu_item_' . $languages[$i]->lang_code, Text::_('ESHOP_CONFIG_DEFAULT_MENU_ITEM') . ' (' . $languages[$i]->title . ')', Text::_('ESHOP_CONFIG_DEFAULT_MENU_ITEM_HELP') . ' (' . $languages[$i]->title . ')'); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['default_menu_item_' . $languages[$i]->lang_code]; ?>
				</div>
			</div>
			<?php
		}
	}
	else 
	{
		?>
		<div class="control-group">
			<div class="control-label">
				<?php echo EShopHtmlHelper::getFieldLabel('default_menu_item', Text::_('ESHOP_CONFIG_DEFAULT_MENU_ITEM'), Text::_('ESHOP_CONFIG_DEFAULT_MENU_ITEM_HELP')); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['default_menu_item']; ?>
			</div>
		</div>
		<?php
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('users_menu_item', Text::_('ESHOP_CONFIG_USERS_MENU_ITEM'), Text::_('ESHOP_CONFIG_USERS_MENU_ITEM_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="users_menu_item" id="users_menu_item"  value="<?php echo $this->config->users_menu_item ?? '0'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('require_name_in_multiple_languages', Text::_('ESHOP_CONFIG_REQUIRE_NAME_IN_MULTILINGUAL'), Text::_('ESHOP_CONFIG_REQUIRE_NAME_IN_MULTILINGUAL_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['require_name_in_multiple_languages']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('enable_canoncial_link', Text::_('ESHOP_CONFIG_ENABLE_CANONCIAL_LINK'), Text::_('ESHOP_CONFIG_ENABLE_CANONCIAL_LINK_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_canoncial_link']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_PRODUCTS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('product_sku_validation', Text::_('ESHOP_CONFIG_PRODUCT_SKU_VALIDATION'), Text::_('ESHOP_CONFIG_PRODUCT_SKU_VALIDATION_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['product_sku_validation']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('product_count', Text::_('ESHOP_CONFIG_CATEGORY_PRODUCT_COUNT'), Text::_('ESHOP_CONFIG_CATEGORY_PRODUCT_COUNT_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['product_count']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('rich_snippets', Text::_('ESHOP_CONFIG_RICH_SNIPPETS'), Text::_('ESHOP_CONFIG_RICH_SNIPPETS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['rich_snippets']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_reviews', Text::_('ESHOP_CONFIG_ALLOW_REVIEWS'), Text::_('ESHOP_CONFIG_ALLOW_REVIEWS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_reviews']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('enable_reviews_captcha', Text::_('ESHOP_CONFIG_ENABLE_REVIEWS_CAPTCHA'), Text::_('ESHOP_CONFIG_ENABLE_REVIEWS_CAPTCHA_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_reviews_captcha']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('enable_register_account_captcha', Text::_('ESHOP_CONFIG_ENABLE_REGISTER_ACCOUNT_CAPTCHA'), Text::_('ESHOP_CONFIG_ENABLE_REGISTER_ACCOUNT_CAPTCHA_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_register_account_captcha']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('enable_checkout_captcha', Text::_('ESHOP_CONFIG_ENABLE_CHECKOUT_CAPTCHA'), Text::_('ESHOP_CONFIG_ENABLE_CHECKOUT_CAPTCHA_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_checkout_captcha']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('enable_quote_captcha', Text::_('ESHOP_CONFIG_ENABLE_QUOTE_CAPTCHA'), Text::_('ESHOP_CONFIG_ENABLE_QUOTE_CAPTCHA_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_quote_captcha']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_notify', Text::_('ESHOP_CONFIG_ALLOW_NOTIFY'), Text::_('ESHOP_CONFIG_ALLOW_NOTIFY_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_notify']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_wishlist', Text::_('ESHOP_CONFIG_ALLOW_WISHLIST'), Text::_('ESHOP_CONFIG_ALLOW_WISHLIST_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_wishlist']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_compare', Text::_('ESHOP_CONFIG_ALLOW_COMPARE'), Text::_('ESHOP_CONFIG_ALLOW_COMPARE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_compare']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_ask_question', Text::_('ESHOP_CONFIG_ALLOW_ASK_QUESTION'), Text::_('ESHOP_CONFIG_ALLOW_ASK_QUESTION_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_ask_question']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_price_match', Text::_('ESHOP_CONFIG_ALLOW_PRICE_MATCH'), Text::_('ESHOP_CONFIG_ALLOW_PRICE_MATCH_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_price_match']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_email_to_a_friend', Text::_('ESHOP_CONFIG_ALLOW_EMAIL_TO_A_FRIEND'), Text::_('ESHOP_CONFIG_ALLOW_EMAIL_TO_A_FRIEND_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_email_to_a_friend']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_download_pdf_product', Text::_('ESHOP_CONFIG_ALLOW_DOWNLOAD_PDF_PRODUCT'), Text::_('ESHOP_CONFIG_ALLOW_DOWNLOAD_PDF_PRODUCT_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_download_pdf_product']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('dynamic_price', Text::_('ESHOP_CONFIG_DYNAMIC_PRICE'), Text::_('ESHOP_CONFIG_DYNAMIC_PRICE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['dynamic_price']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('dynamic_info', Text::_('ESHOP_CONFIG_DYNAMIC_INFO'), Text::_('ESHOP_CONFIG_DYNAMIC_INFO_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['dynamic_info']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('display_price', Text::_('ESHOP_CONFIG_DISPLAY_PRICE'), Text::_('ESHOP_CONFIG_DISPLAY_PRICE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['display_price']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('display_option_price', Text::_('ESHOP_CONFIG_DISPLAY_OPTION_PRICE'), Text::_('ESHOP_CONFIG_DISPLAY_OPTION_PRICE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['display_option_price']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('display_option_price_with_tax', Text::_('ESHOP_CONFIG_DISPLAY_OPTION_PRICE_WITH_TAX'), Text::_('ESHOP_CONFIG_DISPLAY_OPTION_PRICE_WITH_TAX_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['display_option_price_with_tax']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('product_custom_fields', Text::_('ESHOP_CONFIG_PRODUCT_CUSTOM_FIELDS'), Text::_('ESHOP_CONFIG_PRODUCT_CUSTOM_FIELDS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['product_custom_fields']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('assign_same_options', Text::_('ESHOP_CONFIG_ASSIGN_SAME_OPTIONS'), Text::_('ESHOP_CONFIG_ASSIGN_SAME_OPTIONS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['assign_same_options']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_SEARCH'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_sku', Text::_('ESHOP_CONFIG_SEARCH_SKU')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_sku']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_short_desc', Text::_('ESHOP_CONFIG_SEARCH_SHORT_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_short_desc']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_desc', Text::_('ESHOP_CONFIG_SEARCH_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_desc']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tab1_title', Text::_('ESHOP_CONFIG_SEARCH_TAB1_TITLE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tab1_title']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tab1_content', Text::_('ESHOP_CONFIG_SEARCH_TAB1_CONTENT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tab1_content']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tab2_title', Text::_('ESHOP_CONFIG_SEARCH_TAB2_TITLE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tab2_title']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tab2_content', Text::_('ESHOP_CONFIG_SEARCH_TAB2_CONTENT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tab2_content']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tab3_title', Text::_('ESHOP_CONFIG_SEARCH_TAB3_TITLE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tab3_title']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tab3_content', Text::_('ESHOP_CONFIG_SEARCH_TAB3_CONTENT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tab3_content']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tab4_title', Text::_('ESHOP_CONFIG_SEARCH_TAB4_TITLE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tab4_title']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tab4_content', Text::_('ESHOP_CONFIG_SEARCH_TAB4_CONTENT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tab4_content']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tab5_title', Text::_('ESHOP_CONFIG_SEARCH_TAB5_TITLE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tab5_title']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tab5_content', Text::_('ESHOP_CONFIG_SEARCH_TAB5_CONTENT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tab5_content']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_tag', Text::_('ESHOP_CONFIG_SEARCH_TAG')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_tag']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('search_option_value', Text::_('ESHOP_CONFIG_SEARCH_OPTION_VALUE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['search_option_value']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_TAXES'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('tax', Text::_('ESHOP_CONFIG_TAX_CLASS')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['tax_class']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('tax', Text::_('ESHOP_CONFIG_ENABLE_TAX'), Text::_('ESHOP_CONFIG_ENABLE_TAX_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['tax']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('display_ex_tax', Text::_('ESHOP_CONFIG_DISPLAY_EX_TAX'), Text::_('ESHOP_CONFIG_DISPLAY_EX_TAX_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['display_ex_tax']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('display_ex_tax_base_price', Text::_('ESHOP_CONFIG_DISPLAY_EX_TAX_BASE_PRICE'), Text::_('ESHOP_CONFIG_DISPLAY_EX_TAX_BASE_PRICE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['display_ex_tax_base_price']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('include_tax_anywhere', Text::_('ESHOP_CONFIG_INCLUDE_TAX_ANYWHERE'), Text::_('ESHOP_CONFIG_INCLUDE_TAX_ANYWHERE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['include_tax_anywhere']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('enable_eu_vat_rules', Text::_('ESHOP_CONFIG_ENABLE_EU_VAT_RULES')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_eu_vat_rules']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('eu_vat_rules_based_on', Text::_('ESHOP_CONFIG_EU_VAT_RULES_BASED_ON')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['eu_vat_rules_based_on']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('tax_default', Text::_('ESHOP_CONFIG_USE_STORE_TAX_ADDRESS'), Text::_('ESHOP_CONFIG_USE_STORE_TAX_ADDRESS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['tax_default']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_ACCOUNT'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('customergroup_id', Text::_('ESHOP_CONFIG_CUSTOMER_GROUP'), Text::_('ESHOP_CONFIG_CUSTOMER_GROUP_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['customergroup_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('customer_group_display', Text::_('ESHOP_CONFIG_CUSTOMER_GROUPS'), Text::_('ESHOP_CONFIG_CUSTOMER_GROUPS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['customer_group_display']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('account_terms', Text::_('ESHOP_CONFIG_ACCOUNT_TERMS'), Text::_('ESHOP_CONFIG_ACCOUNT_TERMS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['account_terms']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_PRIVACY_POLICY'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('display_privacy_policy', Text::_('ESHOP_DISPLAY_PRIVACY_POLICY'), Text::_('ESHOP_DISPLAY_PRIVACY_POLICY_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['display_privacy_policy']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('checkout_terms', Text::_('ESHOP_CONFIG_CHECKOUT_TERMS'), Text::_('ESHOP_CONFIG_CHECKOUT_TERMS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['checkout_terms']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_privacy_policy_checkbox', Text::_('ESHOP_CONFIG_SHOW_PRIVACY_POLICY_CHECKBOX'), Text::_('ESHOP_CONFIG_SHOW_PRIVACY_POLICY_CHECKBOX_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_privacy_policy_checkbox']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('privacy_policy_article', Text::_('ESHOP_CONFIG_PRIVACY_POLICY_ARTICLE'), Text::_('ESHOP_CONFIG_PRIVACY_POLICY_ARTICLE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['privacy_policy_article']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('acymailing_integration', Text::_('ESHOP_CONFIG_ACYMAILING_INTEGRATION'), Text::_('ESHOP_CONFIG_ACYMAILING_INTEGRATION_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['acymailing_integration']; ?>
		</div>
	</div>
	<?php
	if (is_file(JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php'))
	{
	    ?>
	    <div class="control-group">
    		<div class="control-label">
    			<?php echo Text::_('ESHOP_ACYMAILING_NEWSLETTER_LISTS'); ?>
    		</div>
    		<div class="controls">
    			<?php echo $this->lists['acymailing_list_ids']; ?>
    		</div>
    	</div>
	    <?php
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('mailchimp_integration', Text::_('ESHOP_CONFIG_MAILCHIMP_INTEGRATION'), Text::_('ESHOP_CONFIG_MAILCHIMP_INTEGRATION_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['mailchimp_integration']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_CHECKOUT'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('start_order_id', Text::_('ESHOP_CONFIG_START_ORDER_ID'), Text::_('ESHOP_CONFIG_START_ORDER_ID_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-medium form-control" type="text" name="start_order_id" id="start_order_id" size="3" value="<?php echo $this->config->start_order_id ?? '0'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('min_sub_total', Text::_('ESHOP_CONFIG_MIN_SUB_TOTAL'), Text::_('ESHOP_CONFIG_MIN_SUB_TOTAL_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-medium form-control" type="text" name="min_sub_total" id="min_sub_total" size="3" value="<?php echo $this->config->min_sub_total ?? ''; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('min_quantity', Text::_('ESHOP_CONFIG_MIN_QUANTITY'), Text::_('ESHOP_CONFIG_MIN_QUANTITY_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-medium form-control" type="text" name="min_quantity" id="min_quantity" size="3" value="<?php echo $this->config->min_quantity ?? '0'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('store_cart', Text::_('ESHOP_CONFIG_STORE_CART'), Text::_('ESHOP_CONFIG_STORE_CART_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['store_cart']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('store_cart_schedule', Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE'), Text::_('ESHOP_CONFIG_STORE_CART_SCHEDULE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['store_cart_schedule']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('send_1st_abandon_cart_reminder', Text::_('ESHOP_CONFIG_SEND_1ST_ABANDON_CART_REMINDER'), Text::_('ESHOP_CONFIG_SEND_1ST_ABANDON_CART_REMINDER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['send_1st_abandon_cart_reminder']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('send_1st_abandon_cart_reminder_after', Text::_('ESHOP_CONFIG_SEND_1ST_ABANDON_CART_REMINDER_AFTER'), Text::_('ESHOP_CONFIG_SEND_1ST_ABANDON_CART_REMINDER_AFTER_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-medium form-control" type="text" name="send_1st_abandon_cart_reminder_after" id="send_1st_abandon_cart_reminder_after" size="3" value="<?php echo $this->config->send_1st_abandon_cart_reminder_after ?? '1'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('send_2nd_abandon_cart_reminder', Text::_('ESHOP_CONFIG_SEND_2ND_ABANDON_CART_REMINDER'), Text::_('ESHOP_CONFIG_SEND_2ND_ABANDON_CART_REMINDER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['send_2nd_abandon_cart_reminder']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('send_2nd_abandon_cart_reminder_after', Text::_('ESHOP_CONFIG_SEND_2ND_ABANDON_CART_REMINDER_AFTER'), Text::_('ESHOP_CONFIG_SEND_2ND_ABANDON_CART_REMINDER_AFTER_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-medium form-control" type="text" name="send_2nd_abandon_cart_reminder_after" id="send_2nd_abandon_cart_reminder_after" size="3" value="<?php echo $this->config->send_2nd_abandon_cart_reminder_after ?? '24'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('send_3rd_abandon_cart_reminder', Text::_('ESHOP_CONFIG_SEND_3RD_ABANDON_CART_REMINDER'), Text::_('ESHOP_CONFIG_SEND_3RD_ABANDON_CART_REMINDER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['send_3rd_abandon_cart_reminder']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('send_3rd_abandon_cart_reminder_after', Text::_('ESHOP_CONFIG_SEND_3RD_ABANDON_CART_REMINDER_AFTER'), Text::_('ESHOP_CONFIG_SEND_3RD_ABANDON_CART_REMINDER_AFTER_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-medium form-control" type="text" name="send_3rd_abandon_cart_reminder_after" id="send_3rd_abandon_cart_reminder_after" size="3" value="<?php echo $this->config->send_3rd_abandon_cart_reminder_after ?? '72'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('number_customers', Text::_('ESHOP_CONFIG_NUMBER_CUSTOMERS'), Text::_('ESHOP_CONFIG_NUMBER_CUSTOMERS_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-medium form-control" type="text" name="number_customers" id="number_customers" size="3" value="<?php echo $this->config->number_customers ?? '10'; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('only_free_shipping', Text::_('ESHOP_CONFIG_ONLY_FREE_SHIPPING'), Text::_('ESHOP_CONFIG_ONLY_FREE_SHIPPING_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['only_free_shipping']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('one_add_to_cart_button', Text::_('ESHOP_CONFIG_ONE_ADD_TO_CART_BUTTON'), Text::_('ESHOP_CONFIG_ONE_ADD_TO_CART_BUTTON_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['one_add_to_cart_button']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('active_https', Text::_('ESHOP_CONFIG_ACTIVE_HTTPS'), Text::_('ESHOP_CONFIG_ACTIVE_HTTPS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['active_https']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('collect_user_ip', Text::_('ESHOP_CONFIG_COLLECT_USER_IP'), Text::_('ESHOP_CONFIG_COLLECT_USER_IP_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['collect_user_ip']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_re_order', Text::_('ESHOP_CONFIG_ALLOW_RE_ORDER'), Text::_('ESHOP_CONFIG_ALLOW_RE_ORDER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_re_order']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_coupon', Text::_('ESHOP_CONFIG_ALLOW_COUPON'), Text::_('ESHOP_CONFIG_ALLOW_COUPON_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_coupon']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('change_coupon', Text::_('ESHOP_CONFIG_CHANGE_COUPON'), Text::_('ESHOP_CONFIG_CHANGE_COUPON_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['change_coupon']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('allow_voucher', Text::_('ESHOP_CONFIG_ALLOW_VOUCHER'), Text::_('ESHOP_CONFIG_ALLOW_VOUCHER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['allow_voucher']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('change_voucher', Text::_('ESHOP_CONFIG_CHANGE_VOUCHER'), Text::_('ESHOP_CONFIG_CHANGE_VOUCHER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['change_voucher']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('cart_weight', Text::_('ESHOP_CONFIG_DISPLAY_WEIGHT_ON_CART_PAGE'), Text::_('ESHOP_CONFIG_DISPLAY_WEIGHT_ON_CART_PAGE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['cart_weight']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('checkout_weight', Text::_('ESHOP_CONFIG_DISPLAY_WEIGHT_ON_CHECKOUT_PAGE'), Text::_('ESHOP_CONFIG_DISPLAY_WEIGHT_ON_CHECKOUT_PAGE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['checkout_weight']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('require_shipping', Text::_('ESHOP_CONFIG_REQUIRE_SHIPPING'), Text::_('ESHOP_CONFIG_REQUIRE_SHIPPING_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['require_shipping']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('require_shipping_address', Text::_('ESHOP_CONFIG_REQUIRE_SHIPPING_ADDRESS'), Text::_('ESHOP_CONFIG_REQUIRE_SHIPPING_ADDRESS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['require_shipping_address']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('shipping_estimate', Text::_('ESHOP_CONFIG_SHIPPING_ESTIMATE'), Text::_('ESHOP_CONFIG_SHIPPING_ESTIMATE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['shipping_estimate']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('enable_existing_addresses', Text::_('ESHOP_CONFIG_ENABLE_EXISTING_ADDRESSES'), Text::_('ESHOP_CONFIG_ENABLE_EXISTED_ADDRESSES_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_existing_addresses']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('checkout_type', Text::_('ESHOP_CONFIG_CHECKOUT_TYPE'), Text::_('ESHOP_CONFIG_CHECKOUT_TYPE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['checkout_type']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('order_edit', Text::_('ESHOP_CONFIG_ORDER_EDITING'), Text::_('ESHOP_CONFIG_ORDER_EDITING_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-medium form-control" type="text" name="order_edit" id="order_edit" size="3" value="<?php echo $this->config->order_edit ?? ''; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('order_status_id', Text::_('ESHOP_CONFIG_ORDER_STATUS'), Text::_('ESHOP_CONFIG_ORDER_STATUS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['order_status_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('complete_status_id', Text::_('ESHOP_CONFIG_COMPLETE_ORDER_STATUS'), Text::_('ESHOP_CONFIG_COMPLETE_ORDER_STATUS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['complete_status_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('shipped_status_id', Text::_('ESHOP_CONFIG_SHIPPED_ORDER_STATUS'), Text::_('ESHOP_CONFIG_SHIPPED_ORDER_STATUS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['shipped_status_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('canceled_status_id', Text::_('ESHOP_CONFIG_CANCELED_ORDER_STATUS'), Text::_('ESHOP_CONFIG_CANCELED_ORDER_STATUS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['canceled_status_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('failed_status_id', Text::_('ESHOP_CONFIG_FAILED_ORDER_STATUS'), Text::_('ESHOP_CONFIG_FAILED_ORDER_STATUS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['failed_status_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('delivery_date', Text::_('ESHOP_CONFIG_DELIVERY_DATE'), Text::_('ESHOP_CONFIG_DELIVERY_DATE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['delivery_date']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('display_comment', Text::_('ESHOP_CONFIG_DISPLAY_COMMENT'), Text::_('ESHOP_CONFIG_DISPLAY_COMMENT_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['display_comment']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('completed_url', Text::_('ESHOP_CONFIG_COMPLETED_URL'), Text::_('ESHOP_CONFIG_COMPLETED_URL_HELP')); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="completed_url" id="completed_url"  value="<?php echo $this->config->completed_url ?? ''; ?>" />
		</div>
	</div>
	<?php
	if (version_compare(JVERSION, '3.0', 'ge') && Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1)
	{
		$languages = $this->languages;
		
		for ($i = 0; $i < count($languages); $i++)
		{
		    $langCode = $languages[$i]->lang_code;
			?>
			<div class="control-group">
        		<div class="control-label">
        			<?php echo EShopHtmlHelper::getFieldLabel('continue_shopping_url_' . $langCode, Text::_('ESHOP_CONFIG_CONTINUE_SHOPPING_URL') . ' (' . $languages[$i]->title . ')', Text::_('ESHOP_CONFIG_CONTINUE_SHOPPING_URL_HELP') . ' (' . $languages[$i]->title . ')'); ?>
        		</div>
        		<div class="controls">
        			<input class="form-control" type="text" name="continue_shopping_url_<?php echo $langCode; ?>" id="continue_shopping_url_<?php echo $languages[$i]->lang_code; ?>"  value="<?php echo $this->config->{'continue_shopping_url_' . $langCode} ?? ''; ?>" />
        		</div>
        	</div>
			<?php
		}
	}
	else 
	{
	    ?>
	    <div class="control-group">
    		<div class="control-label">
    			<?php echo EShopHtmlHelper::getFieldLabel('continue_shopping_url', Text::_('ESHOP_CONFIG_CONTINUE_SHOPPING_URL'), Text::_('ESHOP_CONFIG_CONTINUE_SHOPPING_URL_HELP')); ?>
    		</div>
    		<div class="controls">
    			<input class="form-control" type="text" name="continue_shopping_url" id="continue_shopping_url"  value="<?php echo $this->config->continue_shopping_url ?? ''; ?>" />
    		</div>
    	</div>
	    <?php
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('idevaffiliate_integration', Text::_('ESHOP_CONFIG_IDEVAFFILIATE_INTEGRATION'), Text::_('ESHOP_CONFIG_IDEVAFFILIATE_INTEGRATION_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['idevaffiliate_integration']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('idevaffiliate_path', Text::_('ESHOP_CONFIG_IDEVAFFILIATE_PATH'), Text::_('ESHOP_CONFIG_IDEVAFFILIATE_PATH_HELP')); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="idevaffiliate_path" id="idevaffiliate_path"  value="<?php echo $this->config->idevaffiliate_path ?? ''; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('conversion_tracking_code', Text::_('ESHOP_CONFIG_CONVERSION_TRACKING_CODE'), Text::_('ESHOP_CONFIG_CONVERSION_TRACKING_CODE_HELP')); ?>
		</div>
		<div class="controls">
			<textarea class="form-control" name="conversion_tracking_code" id="donate_amounts" rows="5" cols="50"><?php echo $this->config->conversion_tracking_code ?? ''; ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('api_key_mailchimp', Text::_('ESHOP_CONFIG_API_KEY_MAILCHIMP'), Text::_('ESHOP_CONFIG_API_KEY_MAILCHIMP_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="api_key_mailchimp" id="api_key_mailchimp" size="3" value="<?php echo $this->config->api_key_mailchimp ?? ''; ?>" />
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_INVENTORY'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('stock_manage', Text::_('ESHOP_CONFIG_MANAGE_STOCK'), Text::_('ESHOP_CONFIG_MANAGE_STOCK_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['stock_manage']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('stock_display', Text::_('ESHOP_CONFIG_DISPLAY_STOCK'), Text::_('ESHOP_CONFIG_DISPLAY_STOCK_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['stock_display']; ?>
		</div>
	</div>	
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('stock_warning', Text::_('ESHOP_CONFIG_STOCK_WARNING'), Text::_('ESHOP_CONFIG_STOCK_WARNING_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['stock_warning']; ?>
		</div>
	</div>	
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('stock_checkout', Text::_('ESHOP_CONFIG_STOCK_CHECKOUT'), Text::_('ESHOP_CONFIG_STOCK_CHECKOUT_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['stock_checkout']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('stock_status_id', Text::_('ESHOP_CONFIG_STOCK_STATUS'), Text::_('ESHOP_CONFIG_STOCK_STATUS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['stock_status_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('hide_out_of_stock_products', Text::_('ESHOP_CONFIG_HIDE_OUT_OF_STOCK_PRODUCTS'), Text::_('ESHOP_CONFIG_HIDE_OUT_OF_STOCK_PRODUCTS_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['hide_out_of_stock_products']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('threshold', Text::_('ESHOP_CONFIG_THRESHOLD'), Text::_('ESHOP_CONFIG_THRESHOLD_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-mini form-control" type="text" name="threshold" id="threshold" value="<?php echo $this->config->threshold ?? '0'; ?>" />
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_FILE'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('file_extensions_allowed', Text::_('ESHOP_CONFIG_FILE_EXTENSIONS_ALLOWED'), Text::_('ESHOP_CONFIG_FILE_EXTENSIONS_ALLOWED_HELP')); ?>
		</div>
		<div class="controls">
			<textarea class="form-control" name="file_extensions_allowed" id="file_extensions_allowed" rows="5" cols="50"><?php echo $this->config->file_extensions_allowed ?? ''; ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('file_mime_types_allowed', Text::_('ESHOP_CONFIG_FILE_MIME_TYPES_ALLOWED'), Text::_('ESHOP_CONFIG_FILE_MIME_TYPES_ALLOWED_HELP')); ?>
		</div>
		<div class="controls">
			<textarea class="form-control" name="file_mime_types_allowed" id="file_mime_types_allowed" rows="5" cols="50"><?php echo $this->config->file_mime_types_allowed ?? ''; ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('csv_delimiter', Text::_('ESHOP_CSV_DELIMITER')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['csv_delimiter']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('export_data_format', Text::_('ESHOP_EXPORT_DATA_FORMAT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['export_data_format']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_CHECKOUT_DONATE'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('enable_checkout_donate', Text::_('ESHOP_CONFIG_CHECKOUT_DONATE_ENABLE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_checkout_donate']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('donate_amounts', Text::_('ESHOP_CONFIG_CHECKOUT_DONATE_AMOUNTS'), Text::_('ESHOP_CONFIG_CHECKOUT_DONATE_AMOUNTS_HELP')); ?>
		</div>
		<div class="controls">
			<textarea class="form-control" name="donate_amounts" id="donate_amounts" rows="5" cols="50"><?php echo $this->config->donate_amounts ?? ''; ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('donate_explanations', Text::_('ESHOP_CONFIG_CHECKOUT_DONATE_EXPLANATIONS'), Text::_('ESHOP_CONFIG_CHECKOUT_DONATE_EXPLANATIONS_HELP')); ?>
		</div>
		<div class="controls">
			<textarea class="form-control" name="donate_explanations" id="donate_explanations" rows="5" cols="50"><?php echo $this->config->donate_explanations ?? ''; ?></textarea>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_CHECKOUT_DISCOUNT'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('enable_checkout_discount', Text::_('ESHOP_CONFIG_CHECKOUT_DISCOUNT_ENABLE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_checkout_discount']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('checkout_discount_type', Text::_('ESHOP_CHECKOUT_DISCOUNT_TYPE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['checkout_discount_type']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('total_range', Text::_('ESHOP_CONFIG_CHECKOUT_DISCOUNT_TOTAL_RANGE')); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="total_range" id="total_range"  value="<?php echo $this->config->total_range ?? ''; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quantity_range', Text::_('ESHOP_CONFIG_CHECKOUT_DISCOUNT_QUANTITY_RANGE')); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="quantity_range" id="quantity_range"  value="<?php echo $this->config->quantity_range ?? ''; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('discount_range', Text::_('ESHOP_CONFIG_CHECKOUT_DISCOUNT_DISCOUNT_RANGE')); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="discount_range" id="discount_range"  value="<?php echo $this->config->discount_range ?? ''; ?>" />
		</div>
	</div>
</fieldset>
<div class="control-group">
	<span class="help"><?php echo Text::_('ESHOP_CONFIG_CHECKOUT_DISCOUNT_HELP'); ?></span>
</div>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_GOOGLE_ANALYTICS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('ga_tracking_id', Text::_('ESHOP_CONFIG_GOOGLE_ANALYTICS_TRACKING_ID'), Text::_('ESHOP_CONFIG_GOOGLE_ANALYTICS_TRACKING_ID_HELP')); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="ga_tracking_id" id="ga_tracking_id" value="<?php echo $this->config->ga_tracking_id ?? ''; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('ga_js_type', Text::_('ESHOP_CONFIG_GOOGLE_ANALYTICS_TRACKING_TYPE'), Text::_('ESHOP_CONFIG_GOOGLE_ANALYTICS_TRACKING_TYPE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['ga_js_type']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('variation_type', Text::_('ESHOP_CONFIG_GOOGLE_ANALYTICS_VARIATION_TYPE'), Text::_('ESHOP_CONFIG_GOOGLE_ANALYTICS_VARIATION_TYPE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['variation_type']; ?>
		</div>
	</div>
</fieldset>