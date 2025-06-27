<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

/* @var OSMembershipViewMembersHtml $this */

$showAvatar              = $this->params->get('show_avatar', 1);
$showPlan                = $this->params->get('show_plan', 1);
$showSubscriptionDate    = $this->params->get('show_subscription_date', 1);
$showSubscriptionEndDate = $this->params->get('show_subscription_end_date', 0);
$showLinkToProfile       = $this->params->get('show_link_to_detail', 0);
$showMembershipId        = $this->params->get('show_membership_id', 0);

$bootstrapHelper = $this->bootstrapHelper;
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$clearfixClass   = $bootstrapHelper->getClassMapping('clearfix');
$centerClass     = $bootstrapHelper->getClassMapping('center');
$imgCircle       = $bootstrapHelper->getClassMapping('img-circle');

$fields = $this->fields;

// Remove first_name and last_name as it is displayed in single name field

for ($i = 0, $n = count($fields); $i < $n; $i++)
{
	if (in_array($fields[$i]->name, ['first_name', 'last_name']))
	{
		unset($fields[$i]);
	}
}

$cols      = count($fields);
$rootUri   = Uri::root(true);
?>
<div id="osm-members-list" class="osm-container">
	<?php
	if ($this->params->get('show_page_heading', 1))
	{
		if ($this->input->getInt('hmvc_call'))
		{
			$hTag = 'h2';
		}
		else
		{
			$hTag = 'h1';
		}
	?>
		<<?php echo $hTag; ?> class="osm-page-title"><?php echo $this->params->get('page_heading') ?: Text::_('OSM_MEMBERS_LIST') ; ?></<?php echo $hTag; ?>>
	<?php
	}

	if (OSMembershipHelper::isValidMessage($this->params->get('intro_text')))
	{
	?>
		<div class="osm-description osm-page-intro-text <?php echo $this->bootstrapHelper->getClassMapping('clearfix'); ?>">
			<?php echo HTMLHelper::_('content.prepare', $this->params->get('intro_text')); ?>
		</div>
	<?php
	}
	?>
	<form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_osmembership&view=members&Itemid=' . $this->Itemid); ?>">
		<fieldset class="filters btn-toolbar <?php echo $clearfixClass; ?>">
            <?php echo $this->loadTemplate('search'); ?>
		</fieldset>
		<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered table-hover'); ?> osm-responsive-table">
			<thead>
				<tr>
					<?php
						if ($showAvatar)
						{
							$cols++;
						?>
							<th class="osm-members-list-heading-avatar">
								<?php echo Text::_('OSM_AVATAR') ?>
							</th>
						<?php
						}

						if ($showMembershipId)
						{
							$cols++;
						?>
                            <th class="osm-members-list-heading-membership-id">
	                            <?php echo HTMLHelper::_('grid.sort', Text::_('OSM_MEMBERSHIP_ID'), 'tbl.membership_id', $this->state->filter_order_Dir, $this->state->filter_order); ?>
                            </th>
                        <?php
						}
						?>
							<th>
								<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_NAME'), 'tbl.name', $this->state->filter_order_Dir, $this->state->filter_order); ?>
							</th>
						<?php
						if ($showPlan)
						{
							$cols++;
						?>
							<th class="osm-members-list-heading-plan">
								<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_PLAN'), 'b.title', $this->state->filter_order_Dir, $this->state->filter_order); ?>
							</th>
						<?php
						}

						foreach($fields as $field)
						{
						?>
							<th class="osm-members-list-heading-<?php echo $field->name; ?>">
                                <?php
									if ($field->is_core || $field->is_searchable)
									{
									?>
	                                    <?php echo HTMLHelper::_('grid.sort', $field->title, 'tbl.' . $field->name, $this->state->filter_order_Dir, $this->state->filter_order); ?>
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

						if ($showSubscriptionDate)
						{
							$cols++;
						?>
							<th class="<?php echo $centerClass; ?> osm-members-list-heading-created-date">
								<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_SUBSCRIPTION_DATE'), 'tbl.created_date', $this->state->filter_order_Dir, $this->state->filter_order); ?>
							</th>
						<?php
						}

						if ($showSubscriptionEndDate)
						{
							$cols++;
						?>
                            <th class="<?php echo $centerClass; ?> osm-members-list-heading-to-date">
	                            <?php echo HTMLHelper::_('grid.sort', Text::_('OSM_SUBSCRIPTION_END_DATE'), 'tbl.plan_subscription_to_date', $this->state->filter_order_Dir, $this->state->filter_order); ?>
                            </th>
                        <?php
						}
					?>
				</tr>
			</thead>
			<tbody>
			<?php
				$fieldsData = $this->fieldsData;

				for ($i = 0 , $n = count($this->items) ; $i < $n ; $i++)
				{
					$row  = $this->items[$i];
					$link = Route::_('index.php?option=com_osmembership&view=member&id=' . $row->id . '&Itemid=' . $this->Itemid);
				?>
					<tr>
						<?php
						if ($showAvatar)
						{
						?>
							<td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('OSM_AVATAR'); ?>">
								<?php
								if ($row->avatar && file_exists(JPATH_ROOT . '/media/com_osmembership/avatars/' . $row->avatar))
								{
									if ($showLinkToProfile)
									{
									?>
										<a href="<?php echo $link; ?>"><img class="oms-avatar <?php echo $imgCircle; ?>" src="<?php echo $rootUri . '/media/com_osmembership/avatars/' . $row->avatar; ?>"/></a>
									<?php
									}
									else
									{
									?>
										<img class="oms-avatar <?php echo $imgCircle; ?>" src="<?php echo $rootUri . '/media/com_osmembership/avatars/' . $row->avatar; ?>"/>
									<?php
									}
								}
								?>
							</td>
						<?php
						}

						if ($showMembershipId)
						{
						?>
                            <td class="<?php echo $centerClass; ?> tdno<?php echo $i; ?>" data-content="<?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>"><?php echo OSMembershipHelper::formatMembershipId($row, $this->config); ?></td>
                        <?php
						}
						?>
						<td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('OSM_NAME'); ?>">
							<?php
								if ($showLinkToProfile)
								{
								?>
									<a href="<?php echo $link; ?>"><?php echo rtrim($row->first_name . ' ' . $row->last_name); ?></a>
								<?php
								}
								else
								{
									echo rtrim($row->first_name . ' ' . $row->last_name);
								}
							?>
						</td>
						<?php

						if ($showPlan)
						{
						?>
							<td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('OSM_PLAN'); ?>">
								<?php echo $row->plan_title; ?>
							</td>
						<?php
						}

						foreach ($fields as $field)
						{
							if ($field->is_core)
							{
								$fieldValue = $row->{$field->name};
							}
							else
							{
								$fieldValue = $fieldsData[$row->id][$field->id] ?? '';
							}

							$rowField = MPFFormField::create($field, $fieldValue);
							?>
								<td class="tdno<?php echo $i; ?>" data-content="<?php echo $field->title; ?>">
									<?php echo $rowField->getDisplayValue(); ?>
								</td>
							<?php
						}

						if ($showSubscriptionDate)
						{
						?>
							<td class="<?php echo $centerClass; ?> tdno<?php echo $i; ?>" data-content="<?php echo Text::_('OSM_SUBSCRIPTION_DATE'); ?>">
								<?php echo HTMLHelper::_('date', $row->created_date, $this->config->date_format); ?>
							</td>
						<?php
						}

						if ($showSubscriptionEndDate)
						{
						?>
                            <td class="<?php echo $centerClass; ?> tdno<?php echo $i; ?>" data-content="<?php echo Text::_('OSM_SUBSCRIPTION_END_DATE'); ?>">
								<?php echo HTMLHelper::_('date', $row->plan_subscription_to_date, $this->config->date_format); ?>
                            </td>
						<?php
						}
						?>
					</tr>
				<?php
				}
				?>
				</tbody>
				<?php
				if ($this->pagination->total > $this->pagination->limit)
				{
				?>
				<tfoot>
					<tr>
						<td colspan="<?php echo $cols; ?>">
							<div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
						</td>
					</tr>
				</tfoot>
				<?php
				}
			?>
		</table>

        <?php
			if (count($this->items) == 0)
			{
			?>
                <p class="text-info"><?php echo Text::_('OSM_NO_MEMBERS_FOUND'); ?></p>
            <?php
			}
		?>

        <input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
	</form>
</div>