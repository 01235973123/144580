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
use Joomla\CMS\Toolbar\ToolbarHelper;

Factory::getApplication()->getDocument()->getWebAssetManager()
	->useScript('table.columns')
	->useScript('multiselect');

ToolbarHelper::custom('export', 'download', 'download', 'Export Coupons', false);
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="mp-joomla4-container">
        <div id="filter-bar" class="btn-toolbar js-stools-container-filters-visible">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_COUPONS_DESC');?></label>
				<input type="text" name="filter_search" inputmode="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_COUPONS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right">
				<?php
				echo $this->lists['filter_plan_id'];
				echo $this->lists['filter_state'];
				echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped">
			<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
				</th>
				<th class="title" style="text-align: left;">
					<?php echo $this->gridSort('OSM_CODE', 'tbl.code'); ?>
				</th>
				<th width="15%" class="title text_center" nowrap="nowrap">
					<?php echo Text::_('Discount'); ?>
				</th>
				<th width="10%" class="title text_center" nowrap="nowrap">
					<?php echo $this->gridSort('OSM_TIMES', 'tbl.times'); ?>
				</th>
				<th width="10%" class="title text_center" nowrap="nowrap">
					<?php echo $this->gridSort('OSM_USED', 'tbl.used'); ?>
				</th>
				<th width="10%" class="title text_center" nowrap="nowrap">
					<?php echo $this->gridSort('OSM_VALID_FROM', 'tbl.valid_from'); ?>
				</th>
				<th width="10%" class="title text_center" nowrap="nowrap">
					<?php echo $this->gridSort('OSM_VALID_TO', 'tbl.valid_to'); ?>
				</th>
                <th class="title text_center" nowrap="nowrap">
	                <?php echo $this->gridSort('OSM_NOTE', 'tbl.note'); ?>
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
				<td colspan="10">
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
				$published = HTMLHelper::_('jgrid.published', $row->published, $i, 'coupon.');
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->code; ?>
						</a>
					</td>
					<td class="text_center">
						<?php echo OSMembershipHelper::formatAmount($row->discount, $this->config) . $this->discountTypes[$row->coupon_type]; ?>
					</td>
					<td class="text_center">
						<?php echo $row->times; ?>
					</td>
					<td class="text_center">
						<?php echo $row->used; ?>
					</td>
					<td class="text_center">
						<?php
						if ((int)$row->valid_from)
						{
							echo HTMLHelper::_('date', $row->valid_from, $this->config->date_format, null);
						}
						?>
					</td>
					<td class="text_center">
						<?php
						if ((int)$row->valid_to)
						{
							echo HTMLHelper::_('date', $row->valid_to, $this->config->date_format, null);
						}
						?>
					</td>
                    <td>
                        <?php echo $row->note; ?>
                    </td>
					<td class="text_center">
						<?php echo $published; ?>
					</td>
                    <td class="text_center">
                        <?php echo $row->id; ?>
                    </td>
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