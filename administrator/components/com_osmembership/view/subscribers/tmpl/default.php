<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright	Copyright (C) 2012 - 2025 Ossolution Team
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

$cols = 5;
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="mp-joomla4-container">
        <div id="filter-bar" class="btn-toolbar js-stools-container-filters-visible">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_SUBSCRIBERS_DESC');?></label>
				<input type="text" name="filter_search" inputmode="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_SUBSCRIBERS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right">
				<?php
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
					<th class="title">
						<?php echo $this->gridSort('OSM_FIRSTNAME', 'tbl.first_name'); ?>
					</th>
                    <?php
						if (in_array('last_name', $this->coreFields))
						{
							$cols++;
						?>
                            <th class="title">
	                            <?php echo $this->gridSort('OSM_LASTNAME', 'tbl.last_name'); ?>
                            </th>
                        <?php
						}
					?>
					<th class="title">
						<?php echo $this->gridSort('OSM_EMAIL', 'tbl.email'); ?>
					</th>
					<?php
						if ($this->config->auto_generate_membership_id)
						{
							$cols++ ;
						?>
							<th width="8%">
								<?php echo $this->gridSort('OSM_MEMBERSHIP_ID', 'tbl.membership_id'); ?>
							</th>
						<?php
						}
					?>
					<th class="title center">
						<?php echo Text::_('OSM_PLANS'); ?>
					</th>
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
				$link        = $this->getEditItemLink($row);
				$checked     = HTMLHelper::_('grid.id', $i, $row->id);
				$accountLink = 'index.php?option=com_users&task=user.edit&id=' . $row->user_id;
				$plans       = $row->plans;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>"><?php echo $row->first_name ; ?></a>
						<?php
						if ($row->username)
						{
						?>
							[<a href="<?php echo $accountLink; ?>" title="View Profile"><strong><?php echo $row->username ; ?></strong></a>]
						<?php
						}
						?>
					</td>
                    <?php
						if (in_array('last_name', $this->coreFields))
						{
						?>
                            <td>
		                        <?php echo $row->last_name ; ?>
                            </td>
                        <?php
						}
					?>
					<td>
						<a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a>
					</td>
					<?php
						if ($this->config->auto_generate_membership_id)
						{
						?>
							<td>
								<?php echo OSMembershipHelper::formatMembershipId($row, $this->config); ?>
							</td>
						<?php
						}
					?>
					<td>
						<ul class="osm-plans-container">
							<?php
								foreach($plans as $plan)
								{
								?>
									<li>
										<strong><a href="<?php echo Route::_('index.php?option=com_osmembership&task=plan.edit&cid[]=' . $plan->id); ?>"><?php echo $plan->title; ?></a></strong> - [


										<strong><?php echo HTMLHelper::_('date', $plan->subscription_from_date, $this->config->date_format); ?></strong> <?php echo Text::_('OSM_TO'); ?>
										<strong>
											<?php
												if ($plan->lifetime_membership || $plan->subscription_to_date == '2099-12-31 23:59:59')
												{
													echo Text::_('OSM_LIFETIME');
												}
												else
												{
													echo HTMLHelper::_('date', $plan->subscription_to_date, $this->config->date_format);
												}
											?>
										</strong>
										] -
										<?php
											switch ($plan->subscription_status)
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
												default:
													echo Text::_('OSM_CANCELLED');
													break;

											}
										?>
									</li>
								<?php
								}
							?>
						</ul>
					</td>
					<td align="center">
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