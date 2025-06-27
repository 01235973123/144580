<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     ESshop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
?>
<div class="control-group">
	<div class="control-label">
		<span class="required">*</span>
		<?php echo  Text::_('ESHOP_NAME'); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="category_name<?php echo $this->nameSuffix; ?>" id="category_name<?php echo $this->idSuffix; ?>" size="" maxlength="250" value="<?php echo $this->item->{'category_name' . $this->nameSuffix} ?? ''; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('ESHOP_ALIAS'); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="category_alias<?php echo $this->nameSuffix; ?>" id="category_alias<?php echo $this->idSuffix; ?>" size="" maxlength="250" value="<?php echo $this->item->{'category_alias' . $this->nameSuffix} ?? ''; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('ESHOP_PAGE_TITLE'); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="category_page_title<?php echo $this->nameSuffix; ?>" id="category_page_title<?php echo $this->idSuffix; ?>" size="" maxlength="250" value="<?php echo $this->item->{'category_page_title' . $this->nameSuffix} ?? ''; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('ESHOP_PAGE_HEADING'); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="category_page_heading<?php echo $this->nameSuffix; ?>" id="category_page_heading<?php echo $this->idSuffix; ?>" size="" maxlength="250" value="<?php echo $this->item->{'category_page_heading' . $this->nameSuffix} ?? ''; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('ESHOP_ALT_IMAGE'); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="category_alt_image<?php echo $this->nameSuffix; ?>" id="category_alt_image<?php echo $this->idSuffix; ?>" size="" maxlength="250" value="<?php echo $this->item->{'category_alt_image' . $this->nameSuffix} ?? ''; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('ESHOP_CANONCIAL_LINK'); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="category_canoncial_link<?php echo $this->nameSuffix; ?>" id="category_canoncial_link<?php echo $this->idSuffix; ?>" size="" maxlength="250" value="<?php echo $this->item->{'category_canoncial_link' . $this->nameSuffix} ?? ''; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_DESCRIPTION'); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display('category_desc' . $this->nameSuffix,  $this->item->{'category_desc' . $this->nameSuffix} ?? '' , '100%', '250', '75', '10' ); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_META_KEYS'); ?>
	</div>
	<div class="controls">
		<textarea class="form-control" rows="5" cols="80" name="meta_key<?php echo $this->nameSuffix; ?>"><?php echo $this->item->{'meta_key' . $this->nameSuffix}; ?></textarea>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_META_DESC'); ?>
	</div>
	<div class="controls">
		<textarea class="form-control" rows="5" cols="80" name="meta_desc<?php echo $this->nameSuffix; ?>"><?php echo $this->item->{'meta_desc' . $this->nameSuffix}; ?></textarea>
	</div>
</div>
