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
use Joomla\CMS\Router\Route;

Factory::getApplication()->getDocument()->getWebAssetManager()
	->useScript('table.columns')
	->useScript('multiselect');

$this->loadDraggableLib('categoryList');
$this->loadSearchTools();

$saveOrder       = $this->state->filter_order === 'tbl.ordering';
$saveOrderingUrl = 'index.php?option=com_osmembership&task=category.save_order_ajax';

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="mp-joomla4-container">
        <div id="filter-bar" class="btn-toolbar js-stools js-stools-container-filters-visible">
			<div class="filter-search btn-group <?php echo $bootstrapHelper->getClassMapping('pull-left'); ?>">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_CATEGORIES_DESC');?></label>
				<input type="text" name="filter_search" inputmode="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_CATEGORIES_DESC'); ?>" />
			</div>
			<div class="btn-group <?php echo $bootstrapHelper->getClassMapping('pull-left'); ?>">
				<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group <?php echo $bootstrapHelper->getClassMapping('pull-right'); ?>">
				<?php
					echo $this->lists['filter_state'];
					echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped" id="categoryList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo $this->searchToolsSortHeader(); ?>
					</th>
					<th width="2%" class="text_center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th class="title" width="40%">
						<?php echo $this->searchToolsSort('OSM_TITLE', 'tbl.title'); ?>
					</th>
                    <th class="center">
                        <?php echo Text::_('OSM_NUMBER_PLANS'); ?>
                    </th>
					<th width="5%">
						<?php echo $this->searchToolsSort('JGRID_HEADING_ACCESS', 'tbl.access'); ?>
					</th>
					<th width="5%" class="center">
						<?php echo $this->searchToolsSort('OSM_PUBLISHED', 'tbl.published'); ?>
					</th>
					<th width="2%" class="center">
						<?php echo $this->searchToolsSort('OSM_ID', 'tbl.id'); ?>
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
            <tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->state->filter_order_Dir); ?>" data-nested="false"<?php endif; ?>>
			<?php
			$k = 0;
			for ($i = 0, $n = count($this->items); $i < $n; $i++)
			{
				$row       = $this->items[$i];
				$link      = $this->getEditItemLink($row);
				$checked   = HTMLHelper::_('grid.id', $i, $row->id);
				$published = HTMLHelper::_('jgrid.published', $row->published, $i, 'category.');

				?>
                    <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $row->parent_id; ?>" item-id="<?php echo $row->id ?>" parents="<?php echo $row->parentsStr; ?>" level="<?php echo $row->level ?>">
                <?php
				?>
					<td class="order nowrap center hidden-phone">
						<?php $this->reOrderCell($row); ?>
					</td>
					<td class="center">
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>"><?php echo $row->treename ; ?></a>
					</td>
                    <td class="center">
                        <a href="<?php echo Route::_('index.php?option=com_osmembership&view=plans&filter_category_id=' . $row->id); ?>"><?php echo $row->number_plans; ?></a>
                    </td>
					<td>
						<?php echo $row->access_level; ?>
					</td>
					<td class="center">
						<?php echo $published; ?>
					</td>
					<td class="center">
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