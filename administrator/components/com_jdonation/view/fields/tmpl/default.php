<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
HTMLHelper::_('bootstrap.tooltip');
$listOrder			 = $this->state->filter_order;
$listDirn			 = $this->state->filter_order_Dir;
$saveOrder			 = $listOrder == 'tbl.ordering';
$ordering			 = ($this->state->filter_order == 'tbl.ordering');
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_jdonation&task=field.save_order_ajax';
	HTMLHelper::_('sortablelist.sortable', 'fieldTable', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
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
if(DonationHelper::isJoomla4())
{
	$searchBtnClass = "btn-primary";
	$cancelSearchBtnClass = "btn-secondary";

	Factory::getApplication()->getDocument()->getWebAssetManager()
		->useScript('table.columns')
		->useScript('multiselect');
}
else
{
	$searchBtnClass = "";
	$cancelSearchBtnClass = "";
}
$db = Factory::getContainer()->get('db');
?>
<form action="index.php?option=com_jdonation&view=fields" method="post" name="adminForm" id="adminForm">
    <?php if (!empty( $this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php else : ?>
        <div id="j-main-container">
    <?php endif;?>
        <div class="<?php echo $rowFluidClass; ?>">
			<?php
			if(DonationHelper::isJoomla4())
			{
				?>
				<div class="js-stools" role="search">
					<div class="js-stools-container-bar">
						<div id="filter-bar" class="btn-toolbar">
							<div class="btn-group">
								<div class="input-group input-append">
									<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip input-medium form-control" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER'); ?>" inputmode="search" aria-describedby="filter_search-desc"/>
									
									<button type="submit" class="btn <?php echo $searchBtnClass;?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
									
								</div>
							</div>
							<div class="btn-group">
								<button type="button" class="btn btn-primary js-stools-btn-filter">
									Filter Options	<span class="icon-angle-down" aria-hidden="true"></span>
								</button>
								<button type="button" class="btn <?php echo $cancelSearchBtnClass; ?> hasTooltip js-stools-btn-clear" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';document.getElementById('filter_campaign_id').value='0';document.getElementById('field_type').value='';document.getElementById('published').value='-1';document.getElementById('is_core_field').value='-1';document.getElementById('require_status').value='-1';this.form.submit();"><span class="icon-remove"></span></button>
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
					<div class="js-stools-container-filters clearfix <?php echo $this->showFilterForm;?>">
						<?php
						if ($this->fieldCampaign)
						{				
						?>
							<div class="js-stools-field-filter">
								<?php echo $this->lists['campaign_id']; ?>
							</div>
						<?php
						}		
						?>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['field_type'];
							?>
						</div>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['require'];
							?>
						</div>
						
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['core_field'];
							?>
						</div>

						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['published'];
							?>
						</div>
					</div>
				<?php
			}
			else
			{
				//Filter form on Joomla 3
				?>
				<div class="js-stools clearfix" role="search">
					<div class="clearfix">
						<div class="js-stools-container-bar">
							<div id="filter-bar" class="js-stools-container-bar">
								<div class="btn-wrapper input-append">
									<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip input-medium form-control" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER'); ?>" inputmode="search" aria-describedby="filter_search-desc"/>
									
									<button type="submit" class="btn <?php echo $searchBtnClass;?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
									
								</div>
								<div class="btn-wrapper">
									<button type="button" class="btn hasTooltip js-stools-btn-filter">
										Filter Options	<span class="caret" aria-hidden="true"></span>
									</button>
									<button type="button" class="btn <?php echo $cancelSearchBtnClass; ?> hasTooltip js-stools-btn-clear" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';document.getElementById('filter_campaign_id').value='0';document.getElementById('field_type').value='';document.getElementById('published').value='-1';document.getElementById('is_core_field').value='-1';document.getElementById('require_status').value='-1';this.form.submit();"><span class="icon-remove"></span></button>
								</div>
							</div>
						</div>
						<div class="js-stools-container-list hidden-phone hidden-tablet shown">
							<div class="js-stools-field-list">
								<?php
								echo $this->pagination->getLimitBox();
								?>
							</div>
						</div>
					</div>
					<div class="js-stools-container-filters clearfix <?php echo $this->showFilterForm;?>">
						<?php
						if ($this->fieldCampaign)
						{				
						?>
							<div class="js-stools-field-filter">
								<?php echo $this->lists['campaign_id']; ?>
							</div>
						<?php
						}		
						?>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['field_type'];
							?>
						</div>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['require'];
							?>
						</div>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['core_field'];
							?>
						</div>
						<div class="js-stools-field-filter">
							<?php
							echo $this->lists['published'];
							?>
						</div>
					</div>
				</div>
			<?php
			}
		?>
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
            <table class="table <?php echo $tableClass;?>" id="fieldTable">
            <thead>
                <tr>
                     <th width="5%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', '', 'tbl.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
                    <th width="20">
                        <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                    </th>
                    <th style="text-align: left;">
                        <?php echo HTMLHelper::_('grid.sort',  'JD_NAME', 'tbl.name', $this->state->filter_order_Dir, $this->state->filter_order); ?>
                    </th>
                    <th style="text-align: left;">
                        <?php echo HTMLHelper::_('grid.sort',  'JD_TITLE', 'tbl.title', $this->state->filter_order_Dir, $this->state->filter_order); ?>
                    </th>
					<?php
					if ($this->fieldCampaign)
					{
						?>
						<th style="text-align: left;">
							<?php echo HTMLHelper::_('grid.sort',  'JD_CAMPAIGN', 'tbl.campaign_id', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
						</th>
						<?php
					}
					?>
                    <th style="text-align: left;">
                        <?php echo HTMLHelper::_('grid.sort',  'JD_FIELD_TYPE', 'tbl.field_type', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                    </th>
					<th class="title center" style="text-align:center;">
                        <?php echo HTMLHelper::_('grid.sort',  'JD_CORE_FIELD', 'tbl.is_core', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                    </th>
                    <th class="title center" style="text-align:center;">
                        <?php echo HTMLHelper::_('grid.sort',  'JD_REQUIRE', 'tbl.required', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                    </th>
					<th class="title center" style="text-align:center;">
                        <?php echo HTMLHelper::_('grid.sort',  'JD_ACCESS', 'tbl.access', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                    </th>
                    <th class="title center" style="text-align:center;">
                        <?php echo HTMLHelper::_('grid.sort',  'JD_PUBLISHED', 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
                    </th>
                    <th width="1%" nowrap="nowrap" class="center" style="text-align:center;">
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
            //$ordering = ($this->state->filter_order == 'tbl.ordering');
            for ($i=0, $n=count( $this->items ); $i < $n; $i++)
            {
                $row			= &$this->items[$i];
				$orderkey		= array_search($row->id, $this->ordering[$row->parent_id]);
                $link 			= Route::_( 'index.php?option=com_jdonation&task=field.edit&id='. $row->id );
                $checked 		= HTMLHelper::_('grid.id',   $i, $row->id );
                $published		= HTMLHelper::_('jgrid.published', $row->published, $i);
                $img 			= $row->required ? 'tick.png' : 'publish_x.png';
                $task 			= $row->required ? 'un_required' : 'required';
                $alt 			= $row->required ? Text::_( 'Required' ) : Text::_( 'Not required' );
                $action			= $row->required ? Text::_( 'Not Require' ) : Text::_( 'Require' );
                $img			= HTMLHelper::_('image','admin/'.$img, $alt, array('border' => 0), true) ;

				$href = '
                   <a href="javascript:void(0);" title="'. $action .'">
                   '.$img.'</a>'
                    ;
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
                            <?php echo $row->name; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $row->title; ?>
                        </a>
                    </td>
					<?php
					if ($this->fieldCampaign)
					{
						?>
						<td>
							<?php
							$db->setQuery("Select title from #__jd_campaigns where id = '$row->campaign_id'");
							echo $db->loadResult();
							?>
						</td>
						<?php
					}
					?>
                    <td>
                        <?php echo $row->fieldtype; ?>
                    </td>
					<td class="center" style="text-align:center;">
                        <?php 
						
						if($row->is_core == 1)
						{
							?>
							<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="green" class="bi bi-check2-circle" viewBox="0 0 16 16">
							  <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/>
							  <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/>
							</svg>
							<?php
						}
						else
						{
							?>
							<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="red" class="bi bi-x-circle" viewBox="0 0 16 16">
							  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
							  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
							</svg>
							<?php
						}
						?>
                    </td>
                    <td class="center" style="text-align:center;">
                        <?php 
						if(!DonationHelper::isJoomla4())
						{
							echo $href; 
						}
						else
						{
							if($row->required == 1)
							{
								?>
								<a href="index.php?option=com_jdonation&task=field.un_required&cid[]=<?php echo $row->id;?>" title="<?php echo Text::_('Change Required status')?>">
									<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="green" class="bi bi-check2-circle" viewBox="0 0 16 16">
									  <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/>
									  <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/>
									</svg>
								</a>
								<?php
							}
							else
							{
								?>
								<a href="index.php?option=com_jdonation&task=field.required&cid[]=<?php echo $row->id;?>" title="<?php echo Text::_('Change Required status')?>">
									<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="red" class="bi bi-x-circle" viewBox="0 0 16 16">
									  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
									  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
									</svg>
								</a>
								<?php
							}
						}
						?>
                    </td>
					<td style="text-align:center;">
                        <?php echo $row->access_level ; ?>
                    </td>
                    <td style="text-align:center;">
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
    </div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
	<input type="hidden" id="filter_full_ordering" name="filter_full_ordering" value="" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
