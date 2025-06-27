<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<fieldset class="form-horizontal options-form">
    <legend class="adminform"><?php echo Text::_('OSM_META_DATA'); ?></legend>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_PAGE_TITLE'); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="text" name="page_title" id="page_title" size="" maxlength="250"
                   value="<?php echo $this->item->page_title; ?>"/>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_PAGE_HEADING'); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="text" name="page_heading" id="page_heading" size="" maxlength="250"
                   value="<?php echo $this->item->page_heading; ?>"/>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_META_KEYWORDS'); ?>
        </div>
        <div class="controls">
			<textarea rows="5" cols="30" class="form-control" name="meta_keywords"><?php echo $this->item->meta_keywords; ?></textarea>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_META_DESCRIPTION'); ?>
        </div>
        <div class="controls">
			<textarea rows="5" cols="30" class="form-control" name="meta_description"><?php echo $this->item->meta_description; ?></textarea>
        </div>
    </div>
</fieldset>
