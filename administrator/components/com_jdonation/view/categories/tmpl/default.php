<?php

/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$listOrder			 = $this->state->filter_order;
$listDirn			 = $this->state->filter_order_Dir;
$saveOrder			 = $listOrder == 'tbl.ordering';
$ordering			 = ($this->state->filter_order == 'tbl.ordering');
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_jdonation&task=category.save_order_ajax';
	HTMLHelper::_('sortablelist.sortable', 'categoryTable', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}

$customOptions = array(
	'filtersHidden'       => true,
	'defaultLimit'        => Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#filter_full_ordering'
);
HTMLHelper::_('searchtools.form', '#adminForm', $customOptions);
if (count($this->items))
{
	foreach ($this->items as $item)
	{
		$this->ordering[$item->parent_id][] = $item->id;
	}
}
$bootstrapHelper		= $this->bootstrapHelper;
$rowFluidClass			= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class			= $bootstrapHelper->getClassMapping('span12');
if(DonationHelper::isJoomla4())
{
	$searchBtnClass		= "btn-primary";
	$cancelSearchBtnClass = "btn-warning";
	HTMLHelper::_('draggablelist.draggable');
}
else
{
	$searchBtnClass		= "";
	$cancelSearchBtnClass = "btn-warning";
	HTMLHelper::_('behavior.multiselect');
}
?>
<form action="index.php?option=com_jdonation&view=categories" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container">
        <div class="<?php echo $rowFluidClass;?>">
            <div id="filter-bar" class="btn-toolbar">
                <div class="filter-search btn-group pull-left">
					<div class="input-group input-append">
						<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" />
						<button type="submit" class="btn <?php echo $searchBtnClass; ?> hasTooltip" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
						<button type="button" class="btn <?php echo $cancelSearchBtnClass;?> hasTooltip js-stools-btn-clear" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
					</div>
                </div>
                <div class="btn-group pull-right" style="margin-left:10px;">
                    <?php echo $this->lists['filter_state']; ?>
                </div>
				<div class="ordering-select">
					<div class="js-stools-field-list">
						<?php
						echo $this->pagination->getLimitBox();
						?>
					</div>
				</div>
            </div>
        </div>
        <div id="editcell">
            <?php
            if(count($this->items) > 0) 
			{
				if (!DonationHelper::isJoomla4())
				{
					$tableClass = "table-striped";
				}
				else
				{
					$tableClass = "itemList";
				}
            ?>
                <table class="table <?php echo $tableClass;?>" id="categoryTable">
                <thead>
                    <tr>
                        <th width="1%" class="nowrap center hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', '', 'tbl.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
                        <th width="20">
                            <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                        </th>
                        <th class="title">
                            <?php echo HTMLHelper::_('grid.sort',  'JD_TITLE', 'tbl.title', $this->state->filter_order_Dir, $this->state->filter_order); ?>
                        </th>
                        <th class="title text_right">
                            <?php echo HTMLHelper::_('grid.sort',  'JD_DONATED_AMOUNT', 'tbl.donated_amount', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                        </th>

                        <th class="title center" style="text-align:center;">
                            <?php echo HTMLHelper::_('grid.sort',  'JD_PUBLISHED', 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                        </th>
                        <th width="1%" nowrap="nowrap" style="text-align:center;">
                            <?php echo HTMLHelper::_('grid.sort',  'JD_ID', 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="11">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
                <?php
                $k = 0;
				$canChange = true;
                for ($i=0, $n=count( $this->items ); $i < $n; $i++)
                {
                    $row		= $this->items[$i];
					$orderkey   = array_search($row->id, $this->ordering[$row->parent_id]);
                    $link 		= Route::_( 'index.php?option=com_jdonation&task=category.edit&id='. $row->id );
                    $checked 	= HTMLHelper::_('grid.id',   $i, $row->id );
                    $published	= HTMLHelper::_('jgrid.published', $row->published, $i);
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" data-draggable-group="0" sortable-group-id="0" item-id="<?php echo $row->id ?>" parents="<?php echo $parentsStr ?>" level="0">
                        <td class="order nowrap center hidden-phone">
							<?php
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass ?>">
								<span class="icon-menu"></span>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
							<?php endif; ?>
                        </td>
                        <td>
                            <?php echo $checked; ?>
                        </td>
                        <td>
                            <a href="<?php echo $link; ?>">
                                <?php echo $row->title; ?>
                            </a>
                        </td>
                        <td class="text_right">
								<?php echo DonationHelper::getCategoryDonatedAmount($row->id);?>
                        </td>
                        <td class="center" style="text-align:center;">
                            <?php echo $published ; ?>
                        </td>
                        <td class="center" style="text-align:center;">
                            <?php echo $row->id; ?>
                        </td>
                    </tr>
                    <?php
                    $k = 1 - $k;
                }
                ?>
                </tbody>
                </table>
            <?php }else{
                ?>
                <div class="jd-no-items-wrapper">
                    <div class="jd-alert-no-items alert alert-info text-center">
                        <i class="fas fa-search-minus fa-2x mb-2 text-secondary"></i><br>
                        <?php echo Text::_('JD_NO_MATCHING_RESULTS'); ?>
                    </div>
                </div>
                <?php
            } ?>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
		<input type="hidden" id="filter_full_ordering" name="filter_full_ordering" value="" />
        <?php echo HTMLHelper::_( 'form.token' ); ?>
    </div>
</form>
