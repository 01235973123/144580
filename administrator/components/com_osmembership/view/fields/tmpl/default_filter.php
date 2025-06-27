<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<div id="filter-bar" class="btn-toolbar js-stools-container-filters-visible clearfix">
    <div class="filter-search btn-group pull-left">
        <label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_FIELDS_DESC');?></label>
        <input type="text" name="filter_search" inputmode="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_FIELDS_DESC'); ?>" />
    </div>
    <div class="btn-group pull-left">
        <button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
        <button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
    </div>
    <div class="btn-group pull-right">
        <?php echo $this->lists['plan_id']; ?>
    </div>
    <div class="btn-group pull-right">
        <?php
			echo $this->lists['filter_state'];
			echo $this->pagination->getLimitBox();
		?>
	</div>
    <div class="btn-group pull-right osm-filter-second-row">
        <?php
			echo $this->lists['filter_fieldtype'];
			echo $this->lists['show_core_field'];
			echo $this->lists['filter_fee_field'];
		?>
    </div>
</div>
