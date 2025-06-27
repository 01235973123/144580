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
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::custom('export', 'download', 'download', 'OSM_EXPORT', false);

$cols = 7 ;

Factory::getApplication()->getDocument()->getWebAssetManager()
	->useScript('table.columns')
	->useScript('multiselect');
?>
<form action="index.php?option=com_osmembership&view=reports" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="mp-joomla4-container">
        <div id="filter-bar" class="btn-toolbar js-stools-container-filters-visible">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_SUBSCRIPTIONS_DESC');?></label>
				<input type="text" name="filter_search" inputmode="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_SUBSCRIPTIONS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right">
				<?php
					echo $this->lists['plan_id'];
					echo $this->lists['published'];

					if (in_array($this->state->published, [4, 5]))
					{
						echo $this->lists['filter_in'];
					}

					echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th class="title" style="text-align: left;">
						<?php echo $this->gridSort('OSM_FIRSTNAME', 'tbl.first_name'); ?>
					</th>
					<th class="title" style="text-align: left;">
						<?php echo $this->gridSort('OSM_LASTNAME', 'tbl.last_name'); ?>
					</th>
					<th class="title" style="text-align: left;">
						<?php echo $this->gridSort('OSM_PLAN', 'b.title'); ?>
					</th>
					<th class="title center">
						<?php echo $this->gridSort('OSM_START_DATE', 'tbl.plan_subscription_from_date'); ?>
						/
						<?php echo $this->gridSort('OSM_END_DATE', 'tbl.plan_subscription_to_date'); ?>
					</th>
					<th class="title center">
						<?php echo $this->gridSort('OSM_CREATED_DATE', 'tbl.created_date'); ?>
					</th>
					<th width="8%" class="center">
						<?php echo $this->gridSort('OSM_SUBSCRIPTION_STATUS', 'tbl.published'); ?>
					</th>
					<?php
						if ($this->config->auto_generate_membership_id)
						{
							$cols++ ;
						?>
							<th width="8%" class="center">
								<?php echo $this->gridSort('OSM_MEMBERSHIP_ID', 'tbl.membership_id'); ?>
							</th>
						<?php
						}
					?>
					<th width="2%">
						<?php echo $this->gridSort('OSM_ID', 'tbl.id'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $cols ; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i = 0, $n = count($this->items); $i < $n; $i++)
			{
				$row         = $this->items[$i];
				$link        = Route::_('index.php?option=com_osmembership&view=subscription&id=' . $row->id);
				$checked     = HTMLHelper::_('grid.id', $i, $row->id);
				$accountLink = 'index.php?option=com_users&task=user.edit&id=' . $row->user_id;
				$symbol      = $row->currency_symbol ?: $row->currency;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<a href="<?php echo $link; ?>"><?php echo $row->first_name ; ?></a>
					</td>
					<td>
						<?php
							echo $row->last_name ;

							if ($row->username)
							{
							?>
								<a href="<?php echo $accountLink; ?>" title="View Profile">&nbsp;(<strong><?php echo $row->username ; ?>)</strong></a>
							<?php
							}
						?>
					</td>
					<td>
						<a href="<?php echo Route::_('index.php?option=com_osmembership&task=plan.edit&cid[]=' . $row->plan_id); ?>" target="_blank"><?php echo $row->plan_title ; ?></a>
					</td>
					<td class="center">
						<strong><?php echo HTMLHelper::_('date', $row->plan_subscription_from_date, $this->config->date_format); ?></strong> <?php echo Text::_('OSM_TO'); ?>
						<strong>
							<?php
								if ($row->lifetime_membership || $row->plan_subscription_to_date == '2099-12-31 23:59:59')
								{
									echo Text::_('OSM_LIFETIME');
								}
								else
								{
									echo HTMLHelper::_('date', $row->plan_subscription_to_date, $this->config->date_format);
								}
							?>
						</strong>
					</td>
					<td class="center">
						<?php echo HTMLHelper::_('date', $row->created_date, $this->config->date_format . ' H:i:s'); ?>
					</td>
					<td class="center">
						<?php
							switch ($row->plan_subscription_status)
							{
								case 0 :
									echo Text::_('OSM_PENDING');
									break ;
								case 1 :
									echo Text::_('OSM_ACTIVE');
									break ;
								case 2 :
									echo Text::_('OSM_EXPIRED');
									break ;
								case 3 :
									echo Text::_('OSM_CANCELLED_PENDING');
									break ;
								case 4 :
									echo Text::_('OSM_CANCELLED_REFUNDED');
									break ;
							}
							if ($row->recurring_subscription_cancelled)
							{
								echo '<br /><span class="text-error">' . Text::_('OSM_RECURRING_CANCELLED') . '</span>';
							}
						?>
					</td>
					<?php
						if ($this->config->auto_generate_membership_id)
						{
						?>
							<td class="center">
								<?php echo OSMembershipHelper::formatMembershipId($row, $this->config); ?>
							</td>
						<?php
						}
					?>
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

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
