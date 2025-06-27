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

$cols = 8 + count($this->fields);
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm">
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
		        <?php echo $this->lists['filter_plan_id']; ?>
            </div>
			<div class="btn-group pull-right">
				<?php
					if (isset($this->lists['filter_group_admin_id']))
					{
						echo $this->lists['filter_group_admin_id'];
					}

					echo $this->lists['filter_published'];
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
	                    <?php echo $this->gridSort('OSM_PLAN', 'b.title'); ?>
                    </th>
					<th class="title">
						<?php echo $this->gridSort('OSM_USERNAME', 'c.username'); ?>
					</th>
                    <?php
					foreach($this->fields as $field)
					{
					?>
                        <th>
	                        <?php
							if ($field->is_core || $field->is_searchable)
							{
							?>
								<?php echo $this->gridSort($field->title, 'tbl.' . $field->name); ?>
                            <?php
							}
							else
							{
								echo $field->title;
							}
							?>
                        </th>
	                <?php
					}
					?>
					<th class="title">
						<?php echo $this->gridSort('OSM_GROUP', 'tbl.group_admin_id'); ?>
					</th>
					<th class="title center">
						<?php echo $this->gridSort('OSM_START_DATE', 'tbl.from_date'); ?>
						/
						<?php echo $this->gridSort('OSM_END_DATE', 'tbl.to_date'); ?>
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

					if ($this->config->show_download_member_card)
					{
						$cols++;
					?>
						<th class="center">
							<?php echo Text::_('OSM_MEMBER_CARD'); ?>
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
				$row         = $this->items[$i];
				$link        = $this->getEditItemLink($row);
				$checked     = HTMLHelper::_('grid.id', $i, $row->id);
				$accountLink = 'index.php?option=com_users&task=user.edit&id=' . $row->user_id;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $checked; ?>
					</td>
                    <td>
                        <a href="<?php echo Route::_('index.php?option=com_osmembership&task=plan.edit&cid[]=' . $row->plan_id); ?>" target="_blank"><?php echo $row->plan_title; ?></a>
                    </td>
					<td>
						<a href="<?php echo $accountLink; ?>" title="View Profile"><?php echo $row->username ; ?></a>
					</td>
                    <?php
						$count = 0;

						foreach ($this->fields as $field)
						{
						?>
                        <td>
                            <?php
							if ($field->is_core)
							{
								if ($field->name == 'email')
								{
								?>
                                    <a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a>
		                        <?php
								}
								else
								{
									if ($field->name == 'first_name')
									{
									?>
                                        <a href="<?php echo $link ?>"><?php echo $row->{$field->name}; ?></a>
			                        <?php
									}
									else
									{
										echo $row->{$field->name};
									}
								}
							}
							elseif (isset($this->fieldsData[$row->id][$field->id]))
							{
								echo $this->fieldsData[$row->id][$field->id];
							}

							if ($count == 0 && $row->username)
							{
							?>
                                <a href="<?php echo $accountLink; ?>" title="View Profile">&nbsp;(<strong><?php echo $row->username ; ?>)</strong></a>
                            <?php
							}
							?>
                        </td>
                        <?php
							$count++;
						}
						?>
					<td>
						<?php
						if ($row->group_admin)
						{
						?>
							<a href="<?php echo 'index.php?option=com_users&task=user.edit&id=' . $row->group_admin_id; ?>" title="View Profile">&nbsp;<?php echo $row->group_admin; ?></a>
						<?php
						}
						?>
					</td>
					<td class="center">
						<strong><?php echo HTMLHelper::_('date', $row->from_date, $this->config->date_format); ?></strong> <?php echo Text::_('OSM_TO'); ?>
						<strong>
							<?php
								if ($row->lifetime_membership || $row->to_date == '2099-12-31 23:59:59')
								{
									echo Text::_('OSM_LIFETIME');
								}
								else
								{
									echo HTMLHelper::_('date', $row->to_date, $this->config->date_format);
								}
							?>
						</strong>
					</td>
					<td class="center">
						<?php echo HTMLHelper::_('date', $row->created_date, $this->config->date_format . ' H:i:s'); ?>
					</td>
					<td class="center">
						<?php
							switch ($row->published)
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
						?>
					</td>
					<?php
					if ($this->config->auto_generate_membership_id)
					{
					?>
					<td class="center">
						<?php echo $row->membership_id ? OSMembershipHelper::formatMembershipId($row, $this->config) : ''; ?>
					</td>
					<?php
					}

					if ($this->config->show_download_member_card)
					{
					?>
						<td class="center">
							<?php
							if ($row->activate_member_card_feature)
							{
							?>
								<a href="<?php echo Route::_('index.php?option=com_osmembership&task=subscription.download_member_card&id=' . $row->id); ?>" title="<?php echo Text::_('OSM_DOWNLOAD'); ?>"><i class="icon icon-download"></i></a>
							<?php
							}
							?>
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
	</div>
	<?php $this->renderFormHiddenVariables(); ?>
</form>