<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

Factory::getApplication()->getDocument()->getWebAssetManager()
	->useScript('table.columns')
	->useScript('multiselect');

$discountTypes = [0 => '%', 1 => $this->config->currency_symbol];
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="mp-joomla4-container">
        <div id="filter-bar" class="btn-toolbar js-stools-container-filters-visible">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_DISCOUNTS_DESC');?></label>
				<input type="text" name="filter_search" inputmode="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_DISCOUNTS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right">
				<?php
					echo $this->lists['filter_plan_id'];
					echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
					</th>
                    <th class="title">
	                    <?php echo $this->gridSort('OSM_TITLE', 'tbl.title'); ?>
                    </th>
					<th class="title">
						<?php echo $this->gridSort('OSM_PLAN', 'b.title'); ?>
					</th>
					<th class="title center">
						<?php echo $this->gridSort('OSM_NUMBER_DAYS', 'tbl.number_days'); ?>
					</th>											
					<th class="center title">
						<?php echo $this->gridSort('OSM_DISCOUNT', 'tbl.discount_amount'); ?>
					</th>
                    <th width="5%" class="title text_center" nowrap="nowrap">
	                    <?php echo $this->gridSort('OSM_PUBLISHED', 'tbl.published'); ?>
                    </th>
					<th width="1%" nowrap="nowrap">
						<?php echo $this->gridSort('OSM_ID', 'tbl.id'); ?>
					</th>													
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="7">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i = 0, $n = count($this->items); $i < $n; $i++)
			{
				$row       = $this->items[$i];
				$link      = $this->getEditItemLink($row);
				$checked   = HTMLHelper::_('grid.id', $i, $row->id);
				$published = HTMLHelper::_('jgrid.published', $row->published, $i, 'discount.');
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->title; ?>
						</a>
					</td>
                    <td>
                        <?php echo $row->plan_title ?: Text::_('OSM_ALL_PLANS'); ?>
                    </td>
                    <td class="center">
                        <?php echo $row->number_days; ?>
                    </td>
					<td class="center">
						<?php echo number_format($row->discount_amount, 2) . $discountTypes[$row->discount_type]; ?>
					</td>
                    <td class="center"><?php echo $published; ?></td>
                    <td><?php echo $row->id; ?></td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
	</div>
	<?php $this->renderFormHiddenVariables(); ?>
</form>