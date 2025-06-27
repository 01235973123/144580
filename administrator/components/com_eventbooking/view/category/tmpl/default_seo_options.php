<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('EB_PAGE_TITLE'); ?>
	</div>
	<div class="controls">
		<input class="form-control" type="text" name="page_title" id="page_title" size="" maxlength="250" value="<?php echo $this->item->page_title; ?>"/>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('EB_PAGE_HEADING'); ?>
	</div>
	<div class="controls">
		<input class="form-control" type="text" name="page_heading" id="page_heading" size="" maxlength="250" value="<?php echo $this->item->page_heading; ?>"/>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('EB_META_DESCRIPTION'); ?>
	</div>
	<div class="controls">
		<textarea rows="5" cols="80" class="form-control" name="meta_description"><?php echo $this->item->meta_description; ?></textarea>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo  Text::_('EB_META_KEYWORDS'); ?>
	</div>
	<div class="controls">
		<textarea rows="5" cols="80" class="form-control" name="meta_keywords"><?php echo $this->item->meta_keywords; ?></textarea>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('JFIELD_METADATA_ROBOTS_LABEL'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['robots']; ?>
	</div>
</div>

