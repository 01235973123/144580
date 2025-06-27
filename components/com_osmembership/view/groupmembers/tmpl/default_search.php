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
<div class="filter-search btn-group <?php echo $this->bootstrapHelper->getClassMapping('pull-left'); ?>">
    <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_SUBSCRIPTIONS_DESC'); ?>" />
</div>
<div class="btn-group <?php echo $this->bootstrapHelper->getClassMapping('pull-left'); ?>">
    <button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="<?php echo $this->bootstrapHelper->getClassMapping('icon-search'); ?>"></span></button>
    <button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="<?php echo $this->bootstrapHelper->getClassMapping('icon-remove'); ?>"></span></button>
</div>