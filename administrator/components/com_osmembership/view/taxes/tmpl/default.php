<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

Factory::getApplication()->getDocument()->getWebAssetManager()
	->useScript('table.columns')
	->useScript('multiselect');

$cols = 6;
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="mp-joomla4-container">
        <div id="filter-bar" class="btn-toolbar js-stools-container-filters-visible">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_TAX_RULES_DESC');?></label>
				<input type="text" name="filter_search" inputmode="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_TAX_RULES_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right">
				<?php
					echo $this->lists['filter_country'];
					echo $this->lists['filter_plan_id'];

					if ($this->showVies)
					{
						echo $this->lists['filter_vies'];
					}

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
						<?php echo $this->gridSort('OSM_PLAN', 'b.title'); ?>
					</th>
					<th width="10%" class="title" nowrap="nowrap">
						<?php echo $this->gridSort('OSM_COUNTRY', 'tbl.country'); ?>
					</th>
					<th width="10%" class="title" nowrap="nowrap">
						<?php echo $this->gridSort('OSM_STATE', 'tbl.state'); ?>
					</th>
					<th width="10%" class="title" nowrap="nowrap">
						<?php echo $this->gridSort('OSM_TAX_RATE', 'tbl.rate'); ?>
					</th>
					<?php
						if ($this->showVies)
						{
							$cols++;
						?>
							<th width="10%" class="title" nowrap="nowrap">
								<?php echo $this->gridSort('OSM_VIES', 'tbl.rate'); ?>
							</th>
						<?php
						}
					?>
					<th width="5%" class="title" nowrap="nowrap">
						<?php echo $this->gridSort('OSM_PUBLISHED', 'tbl.published'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $cols; ?>">
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
				$published = HTMLHelper::_('jgrid.published', $row->published, $i, 'tax.');
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->title ?: Text::_('OSM_ALL_PLANS'); ?>
						</a>
					</td>
					<td>
						<?php echo $row->country ?: Text::_('OSM_ALL_COUNTRIES');?>
					</td>
					<td>
						<?php echo $row->state ? OSMembershipHelper::getStateName($row->country, $row->state) : Text::_('OSM_ALL_STATES');?>
					</td>
					<td>
						<?php echo $row->rate; ?>
					</td>
					<?php
						if ($this->showVies)
						{
						?>
							<td>
								<?php echo $row->vies ? Text::_('JYES') : Text::_('JNO');?>
							</td>
						<?php
						}
					?>
					<td class="text_center">
						<?php echo $published; ?>
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
