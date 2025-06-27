<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<div class="filter-search btn-group <?php echo $this->bootstrapHelper->getClassMapping('pull-left'); ?> mb-2">
	<div class="input-group">
		<label for="filter_search" class="sr-only"><?php echo Text::_('OSM_FILTER_SEARCH_MEMBERS_DESC');?></label>
		<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_MEMBERS_DESC'); ?>" />
		<span class="input-group-append">
            <button type="submit" class="btn hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="fa fa-search"></span></button>
            <button type="button" class="btn hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="fa fa-remove"></span></button>
        </span>
	</div>
</div>
