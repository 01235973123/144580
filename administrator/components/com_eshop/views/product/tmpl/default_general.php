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

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$translatable = $this->isMultilingualTranslable;
$editor       = Editor::getInstance(Factory::getApplication()->get('editor'));

if (EShopHelper::isJoomla4())
{
    $tabApiPrefix = 'uitab.';
}
else
{
    HTMLHelper::_('behavior.tabstate');

    $tabApiPrefix = 'bootstrap.';
}

if ($translatable)
{
	$rootUri = Uri::root();
	echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'general-page-translation', array('active' => 'general-page-translation-' . $this->languages[0]->lang_code));

	foreach ($this->languages as $language)
	{
		$langId   = $language->lang_id;
		$langCode = $language->lang_code;

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'general-page-translation', 'general-page-translation-' . $langCode, $this->languageData['title'][$langCode] . ' <img src="' . $rootUri . 'media/com_eshop/flags/' . $this->languageData['flag'][$langCode] . '" />');
		?>
		<div class="control-group">
			<div class="control-label">
				<span class="required">*</span>
				<?php echo  Text::_('ESHOP_NAME'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="product_name_<?php echo $langCode; ?>" id="product_name_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'product_name_'.$langCode} ?? ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_ALIAS'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="product_alias_<?php echo $langCode; ?>" id="product_alias_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'product_alias_'.$langCode} ?? ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_PAGE_TITLE'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="product_page_title_<?php echo $langCode; ?>" id="product_page_title_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo isset($this->item->{'product_page_title_'.$langCode}) ? htmlspecialchars($this->item->{'product_page_title_'.$langCode}, ENT_COMPAT, 'UTF-8') : ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_PAGE_HEADING'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="product_page_heading_<?php echo $langCode; ?>" id="product_page_heading_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo isset($this->item->{'product_page_heading_'.$langCode}) ? htmlspecialchars($this->item->{'product_page_heading_'.$langCode}, ENT_COMPAT, 'UTF-8') : ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_ALT_IMAGE'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="product_alt_image_<?php echo $langCode; ?>" id="product_alt_image_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'product_alt_image_'.$langCode} ?? ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_CANONCIAL_LINK'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="product_canoncial_link_<?php echo $langCode; ?>" id="product_canoncial_link_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'product_canoncial_link_'.$langCode} ?? ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_PRICE_TEXT'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="product_price_text_<?php echo $langCode; ?>" id="product_price_text_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'product_price_text_'.$langCode} ?? ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_PRODUCT_CUSTOM_MESSAGE'); ?>
			</div>
			<div class="controls">
				<?php echo $editor->display( 'product_custom_message_'.$langCode,  $this->item->{'product_custom_message_'.$langCode} ?? '' , '100%', '250', '75', '10' ); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_PRODUCT_SHORT_DESCRIPTION'); ?>
			</div>
			<div class="controls">
				<?php echo $editor->display( 'product_short_desc_'.$langCode,  $this->item->{'product_short_desc_'.$langCode} ?? '' , '100%', '250', '75', '10' ); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_DESCRIPTION'); ?>
			</div>
			<div class="controls">
				<?php echo $editor->display( 'product_desc_'.$langCode,  $this->item->{'product_desc_'.$langCode} ?? '' , '100%', '250', '75', '10' ); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_TAB1_TITLE'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="tab1_title_<?php echo $langCode; ?>" id="tab1_title_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'tab1_title_'.$langCode} ?? ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_TAB1_CONTENT'); ?>
			</div>
			<div class="controls">
				<?php echo $editor->display( 'tab1_content_'.$langCode,  $this->item->{'tab1_content_'.$langCode} ?? '' , '100%', '250', '75', '10' ); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_TAB2_TITLE'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="tab2_title_<?php echo $langCode; ?>" id="tab2_title_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'tab2_title_'.$langCode} ?? ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_TAB2_CONTENT'); ?>
			</div>
			<div class="controls">
				<?php echo $editor->display( 'tab2_content_'.$langCode,  $this->item->{'tab2_content_'.$langCode} ?? '' , '100%', '250', '75', '10' ); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_TAB3_TITLE'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="tab3_title_<?php echo $langCode; ?>" id="tab3_title_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'tab3_title_'.$langCode} ?? ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_TAB3_CONTENT'); ?>
			</div>
			<div class="controls">
				<?php echo $editor->display( 'tab3_content_'.$langCode,  $this->item->{'tab3_content_'.$langCode} ?? '' , '100%', '250', '75', '10' ); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_TAB4_TITLE'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="tab4_title_<?php echo $langCode; ?>" id="tab4_title_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'tab4_title_'.$langCode} ?? ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_TAB4_CONTENT'); ?>
			</div>
			<div class="controls">
				<?php echo $editor->display( 'tab4_content_'.$langCode,  $this->item->{'tab4_content_'.$langCode} ?? '' , '100%', '250', '75', '10' ); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_TAB5_TITLE'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge form-control" type="text" name="tab5_title_<?php echo $langCode; ?>" id="tab5_title_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'tab5_title_'.$langCode} ?? ''; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_TAB5_CONTENT'); ?>
			</div>
			<div class="controls">
				<?php echo $editor->display( 'tab5_content_'.$langCode,  $this->item->{'tab5_content_'.$langCode} ?? '' , '100%', '250', '75', '10' ); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_META_KEYS'); ?>
			</div>
			<div class="controls">
				<textarea class="form-control" rows="5" cols="30" name="meta_key_<?php echo $langCode; ?>"><?php echo $this->item->{'meta_key_'.$langCode}; ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_META_DESC'); ?>
			</div>
			<div class="controls">
				<textarea class="form-control" rows="5" cols="30" name="meta_desc_<?php echo $langCode; ?>"><?php echo $this->item->{'meta_desc_'.$langCode}; ?></textarea>
			</div>
		</div>
		<?php
		echo HTMLHelper::_($tabApiPrefix . 'endTab');
	}

	echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
}
else
{
?>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo  Text::_('ESHOP_NAME'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="product_name" id="product_name" size="" maxlength="250" value="<?php echo $this->item->product_name; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_ALIAS'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="product_alias" id="product_alias" size="" maxlength="250" value="<?php echo $this->item->product_alias; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_PAGE_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="product_page_title" id="product_page_title" size="" maxlength="250" value="<?php echo htmlspecialchars($this->item->product_page_title, ENT_COMPAT, 'UTF-8'); ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_PAGE_HEADING'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="product_page_heading" id="product_page_heading" size="" maxlength="250" value="<?php echo htmlspecialchars($this->item->product_page_heading, ENT_COMPAT, 'UTF-8'); ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_ALT_IMAGE'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="product_alt_image" id="product_alt_image" size="" maxlength="250" value="<?php echo $this->item->product_alt_image; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_CANONCIAL_LINK'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="product_canoncial_link" id="product_canoncial_link" size="" maxlength="250" value="<?php echo $this->item->product_canoncial_link; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_PRICE_TEXT'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="product_price_text" id="product_price_text" size="" maxlength="250" value="<?php echo $this->item->product_price_text; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_PRODUCT_CUSTOM_MESSAGE'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'product_custom_message',  $this->item->product_custom_message , '100%', '250', '75', '10' ); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_PRODUCT_SHORT_DESCRIPTION'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'product_short_desc',  $this->item->product_short_desc , '100%', '250', '75', '10' ); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_DESCRIPTION'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'product_desc',  $this->item->product_desc , '100%', '250', '75', '10' ); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TAB1_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="tab1_title" id="tab1_title" size="" maxlength="250" value="<?php echo $this->item->tab1_title; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TAB1_CONTENT'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'tab1_content',  $this->item->tab1_content , '100%', '250', '75', '10' ); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TAB2_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="tab2_title" id="tab2_title" size="" maxlength="250" value="<?php echo $this->item->tab2_title; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TAB2_CONTENT'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'tab2_content',  $this->item->tab2_content , '100%', '250', '75', '10' ); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TAB3_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="tab3_title" id="tab3_title" size="" maxlength="250" value="<?php echo $this->item->tab3_title; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TAB3_CONTENT'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'tab3_content',  $this->item->tab3_content , '100%', '250', '75', '10' ); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TAB4_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="tab4_title" id="tab4_title" size="" maxlength="250" value="<?php echo $this->item->tab4_title; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TAB4_CONTENT'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'tab4_content',  $this->item->tab4_content , '100%', '250', '75', '10' ); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TAB5_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="input-xxlarge form-control" type="text" name="tab5_title" id="tab5_title" size="" maxlength="250" value="<?php echo $this->item->tab5_title; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TAB5_CONTENT'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'tab5_content',  $this->item->tab5_content , '100%', '250', '75', '10' ); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_META_KEYS'); ?>
		</div>
		<div class="controls">
			<textarea class="form-control" rows="5" cols="30" name="meta_key"><?php echo $this->item->meta_key; ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_META_DESC'); ?>
		</div>
		<div class="controls">
			<textarea class="form-control" rows="5" cols="30" name="meta_desc"><?php echo $this->item->meta_desc; ?></textarea>
		</div>
	</div>
<?php
}