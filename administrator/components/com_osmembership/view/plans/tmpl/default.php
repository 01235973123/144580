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
use Joomla\CMS\Uri\Uri;

Factory::getApplication()->getDocument()->getWebAssetManager()
	->useScript('table.columns')
	->useScript('multiselect');

$this->loadDraggableLib('planList');
$this->loadSearchTools();

$saveOrder       = $this->state->filter_order === 'tbl.ordering';
$saveOrderingUrl = 'index.php?option=com_osmembership&task=plan.save_order_ajax';
$rootUri         = Uri::root(true);

$config = OSMembershipHelper::getConfig();
$cols   = 11;

if ($this->showThumbnail)
{
	$cols++;
}

if ($this->showCategory)
{
	$cols++;
}
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="mp-joomla4-container">
        <div id="filter-bar" class="btn-toolbar js-stools js-stools-container-filters-visible">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_PLANS_DESC');?></label>
				<input type="text" name="filter_search" inputmode="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_PLANS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
            <?php
				if (isset($this->lists['filter_category_id']))
				{
				?>
                    <div class="btn-group pull-right">
                        <?php echo $this->lists['filter_category_id']; ?>
                    </div>
                <?php
				}
			?>
			<div class="btn-group pull-right">
				<?php
					echo $this->lists['filter_state'];
					echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="adminlist table table-striped" id="planList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo $this->searchToolsSortHeader(); ?>
					</th>
					<th width="20">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th class="title">
						<?php echo $this->searchToolsSort('OSM_TITLE', 'tbl.title'); ?>
					</th>
					<?php
						if ($this->showCategory)
						{
						?>
							<th class="title">
								<?php echo $this->searchToolsSort('OSM_CATEGORY', 'b.title'); ?>
							</th>
						<?php
						}
						if ($this->showThumbnail)
						{
						?>
							<th class="title" width="10%">
								<?php echo Text::_('OSM_THUMB'); ?>
							</th>
						<?php
						}
					?>
					<th class="title" width="8%">
						<?php echo Text::_('OSM_LENGTH'); ?>
					</th>
					<th class="center" width="8%">
						<?php echo $this->searchToolsSort('OSM_RECURRING', 'tbl.recurring_subscription'); ?>
					</th>
					<th class="title" width="8%">
						<?php echo $this->searchToolsSort('OSM_PRICE', 'tbl.price'); ?>
					</th>
					<th class="title center" width="12%">
						<?php echo Text::_('OSM_TOTAL_SUBSCRIBERS'); ?>
					</th>
					<th class="title center" width="12%">
						<?php echo Text::_('OSM_ACTIVE_SUBSCRIBERS'); ?>
					</th>
					<th width="5%">
						<?php echo $this->searchToolsSort('JGRID_HEADING_ACCESS', 'tbl.access'); ?>
					</th>
					<th width="5%">
						<?php echo $this->searchToolsSort('OSM_PUBLISHED', 'tbl.published'); ?>
					</th>
					<th width="2%">
						<?php echo $this->searchToolsSort('OSM_ID', 'tbl.id'); ?>
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
            <tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->state->filter_order_Dir); ?>" <?php endif; ?>>
			<?php
			$k = 0;
			$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
			$iconPublish = $bootstrapHelper->getClassMapping('icon-publish');
			$iconUnPublish = $bootstrapHelper->getClassMapping('icon-unpublish');

			for ($i = 0, $n = count($this->items); $i < $n; $i++)
			{
				$row       = $this->items[$i];
				$link      = $this->getEditItemLink($row);
				$checked   = HTMLHelper::_('grid.id', $i, $row->id);
				$published = HTMLHelper::_('jgrid.published', $row->published, $i, 'plan.');
				$symbol    = $row->currency_symbol ?: $row->currency;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="order nowrap center hidden-phone">
						<?php $this->reOrderCell($row); ?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>"><?php echo $row->title ; ?></a>
						<?php
						if ($row->hidden)
						{
						?>
							<span class="badge rounded-pill bg-success"><?php echo Text::_('OSM_HIDDEN'); ?></span>
						<?php
						}
						?>
					</td>
					<?php
						if ($this->showCategory)
						{
						?>
							<td><?php echo $row->category_title; ?></td>
						<?php
						}
						if ($this->showThumbnail)
						{
						?>
							<td class="center">
								<?php
								if ($row->thumb)
								{
								?>
									<a href="<?php echo $rootUri . '/media/com_osmembership/' . $row->thumb ; ?>" target="_blank"><img src="<?php echo $rootUri . '/media/com_osmembership/' . $row->thumb ; ?>" class="osm-plan-thumb" style="max-width: 120px;" /></a>
								<?php
								}
								?>
							</td>
						<?php
						}
					?>
					<td>
						<?php
						if ($row->lifetime_membership)
						{
							echo Text::_('OSM_LIFETIME');
						}
						else
						{
							echo OSMembershipHelperSubscription::getDurationText($row->subscription_length, $row->subscription_length_unit);
						}
						?>
					</td>
					<td class="center">
                        <a class="tbody-icon"><span class="<?php echo $row->recurring_subscription ? $iconPublish : $iconUnPublish; ?>"></span></a>
					</td>
					<td>
						<?php
						if ($row->price > 0)
						{
							echo OSMembershipHelper::formatCurrency($row->price, $config, $symbol);
						}
						else
						{
							echo Text::_('OSM_FREE');
						}
						?>
					</td>
					<td class="center">
                        <a href="index.php?option=com_osmembership&view=subscriptions&plan_id=<?php echo $row->id ?>"><?php echo OSMembershipHelper::countSubscribers($row->id); ?></a>
					</td>
					<td class="center">
                        <a href="index.php?option=com_osmembership&view=subscriptions&plan_id=<?php echo $row->id ?>&published=1"><?php echo OSMembershipHelper::countSubscribers($row->id, 1); ?></a>
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
