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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;

HTMLHelper::_('formbehavior.chosen', 'select');
Text::script('OSM_SELECT_PLAN_TO_ADD_SUBSCRIPTION', true);

Factory::getApplication()
	->getDocument()
	->addScriptOptions('force_select_plan', (int) $this->config->force_select_plan)
	->getWebAssetManager()
	->useScript('core')
	->registerAndUseScript('com_osmembership.site-subscribers-default', 'media/com_osmembership/js/site-subscribers-default.min.js');

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$centerClass  = $bootstrapHelper->getClassMapping('center');
$hiddenPhone  = $bootstrapHelper->getClassMapping('hidden-phone');

$cols = 5;
?>
<div id="osm-subscriptions-management" class="osm-container osm-container-j4">
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
		<<?php echo $hTag; ?> class="osm-heading"><?php echo Text::_('OSM_MANAGE_SUBSCRIPTIONS'); ?></<?php echo $hTag; ?>>
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
    <div class="btn-toolbar" id="btn-toolbar">
		<?php echo Toolbar::getInstance('toolbar')->render(); ?>
    </div>
	<form action="<?php echo Route::_('index.php?option=com_osmembership&view=subscribers&Itemid=' . $this->Itemid, false); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
		<div class="filters btn-toolbar clearfix mt-2 mb-2">
            <?php echo $this->loadTemplate('search_bar'); ?>
		</div>
		<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered table-hover'); ?>">
			<thead>
				<tr>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th class="title" style="text-align: left;">
						<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_FIRSTNAME'), 'tbl.first_name', $this->state->filter_order_Dir, $this->state->filter_order); ?>
					</th>
					<?php
					if ($this->params->get('show_last_name', 1))
					{
						$cols++;
					?>
						<th class="title <?php echo $hiddenPhone; ?>" style="text-align: left;">
							<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_LASTNAME'), 'tbl.last_name', $this->state->filter_order_Dir, $this->state->filter_order); ?>
						</th>
					<?php
					}

					foreach ($this->fields as $field)
					{
						$cols++;

						if ($field->is_core || $field->is_searchable)
						{
						?>
                            <th class="title <?php echo $hiddenPhone; ?>" nowrap="nowrap">
								<?php echo HTMLHelper::_('grid.sort', Text::_($field->title), 'tbl.' . $field->name, $this->state->filter_order_Dir, $this->state->filter_order); ?>
                            </th>
						<?php
						}
						else
						{
						?>
                            <th class="title <?php echo $hiddenPhone; ?>" nowrap="nowrap"><?php echo $field->title; ?></th>
						<?php
						}
					}
					?>
					<th class="title" style="text-align: left;">
						<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_PLAN'), 'b.title', $this->state->filter_order_Dir, $this->state->filter_order); ?>
					</th>
					<th class="title <?php echo $centerClass; ?>">
						<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_START_DATE'), 'tbl.from_date', $this->state->filter_order_Dir, $this->state->filter_order); ?>
						/
						<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_END_DATE'), 'tbl.to_date', $this->state->filter_order_Dir, $this->state->filter_order); ?>
					</th>
					<?php
					if ($this->params->get('show_created_date', 1))
					{
						$cols++;
					?>
						<th class="title <?php echo $centerClass . ' ' . $hiddenPhone; ?>">
							<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_CREATED_DATE'), 'tbl.created_date', $this->state->filter_order_Dir, $this->state->filter_order); ?>
						</th>
					<?php
					}

					if ($this->params->get('show_gross_amount', 1))
					{
						$cols++;
					?>
						<th width="10%" class="<?php echo $hiddenPhone; ?>">
							<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_GROSS_AMOUNT'), 'tbl.gross_amount', $this->state->filter_order_Dir, $this->state->filter_order); ?>
						</th>
					<?php
					}

					if ($this->config->enable_coupon && $this->params->get('show_coupon', 1))
					{
						$cols++;
					?>
						<th class="<?php echo $hiddenPhone; ?>">
							<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_COUPON'), 'd.code', $this->state->filter_order_Dir, $this->state->filter_order); ?>
						</th>
					<?php
					}
					?>
					<th width="8%" class="<?php echo $centerClass; ?>">
						<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_SUBSCRIPTION_STATUS'), 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order); ?>
					</th>
					<?php
					if ($this->config->auto_generate_membership_id && $this->params->get('show_membership_id', 1))
					{
						$cols++ ;
					?>
                        <th width="8%" class="<?php echo $centerClass . ' ' . $hiddenPhone; ?>">
							<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_MEMBERSHIP_ID'), 'tbl.membership_id', $this->state->filter_order_Dir, $this->state->filter_order); ?>
						</th>
					<?php
					}

					if ($this->config->activate_invoice_feature)
					{
						$cols++ ;
					?>
                        <th width="8%" class="<?php echo $centerClass . ' ' . $hiddenPhone; ?>">
							<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_INVOICE_NUMBER'), 'tbl.invoice_number', $this->state->filter_order_Dir, $this->state->filter_order); ?>
						</th>
					<?php
					}

					if ($this->params->get('show_id', 1))
					{
						$cols++;
					?>
						<th width="2%" class="<?php echo $hiddenPhone; ?>">
							<?php echo HTMLHelper::_('grid.sort', Text::_('OSM_ID'), 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order); ?>
						</th>
					<?php
					}
					?>
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
			for ($i=0, $n=count($this->items); $i < $n; $i++)
			{
				$row     = $this->items[$i];
				$link    = Route::_('index.php?option=com_osmembership&view=subscriber&id=' . $row->id . '&Itemid=' . $this->Itemid);
				$checked = HTMLHelper::_('grid.id', $i, $row->id);
				$symbol  = $row->currency_symbol ?: $row->currency;
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
							(<strong><?php echo $row->username ; ?></strong>)
						<?php
						}
						?>
					</td>
					<?php
					if ($this->params->get('show_last_name', 1))
					{
					?>
						<td class="<?php echo $hiddenPhone; ?>">
							<?php echo $row->last_name; ?>
						</td>
					<?php
					}

					foreach ($this->fields as $field)
					{
						if ($field->is_core)
						{
							$fieldValue = $row->{$field->name};
						}
						else
						{
							$fieldValue = $this->fieldsData[$row->id][$field->id] ?? '';
						}

						$rowField = MPFFormField::create($field, $fieldValue);
						?>
                        <td class="<?php echo $hiddenPhone; ?>">
							<?php echo $rowField->getDisplayValue(); ?>
                        </td>
						<?php
					}
					?>
					<td>
						<?php echo $row->plan_title ; ?>
					</td>
					<td class="<?php echo $centerClass; ?>">
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
					<?php
					if($this->params->get('show_created_date', 1))
					{
					?>
						<td class="<?php echo $centerClass . ' ' . $hiddenPhone; ?>">
							<?php echo HTMLHelper::_('date', $row->created_date, $this->config->date_format . ' H:i:s'); ?>
						</td>
					<?php
					}

					if($this->params->get('show_gross_amount', 1))
					{
					?>
						<td class="<?php echo $centerClass . ' ' . $hiddenPhone; ?>">
							<?php echo OSMembershipHelper::formatCurrency($row->gross_amount, $this->config, $symbol)?>
						</td>
					<?php
					}

					if ($this->config->enable_coupon && $this->params->get('show_coupon', 1))
					{
					?>
						<td class="<?php echo $hiddenPhone; ?>">
							<a href="index.php?option=com_osmembership&view=coupon&id=<?php echo $row->coupon_id; ?>" target="_blank"><?php  echo $row->coupon_code; ?></a>
						</td>
					<?php
					}
					?>
					<td class="<?php echo $centerClass; ?>">
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
							if ($row->recurring_subscription_cancelled)
							{
								echo '<br /><span class="text-error">' . Text::_('OSM_RECURRING_CANCELLED') . '</span>';
							}
						?>
					</td>
					<?php
					if ($this->config->auto_generate_membership_id && $this->params->get('show_membership_id', 1))
					{
					?>
                        <td class="<?php echo $centerClass . ' ' . $hiddenPhone; ?>">
							<?php echo OSMembershipHelper::formatMembershipId($row, $this->config); ?>
						</td>
					<?php
					}

					if ($this->config->activate_invoice_feature)
					{
					?>
                        <td class="<?php echo $centerClass . ' ' . $hiddenPhone; ?>">
							<?php
								if ($row->invoice_number)
								{
								?>
									<a href="<?php echo Route::_('index.php?option=com_osmembership&task=download_invoice&id=' . $row->id); ?>" title="<?php echo Text::_('OSM_DOWNLOAD'); ?>"><?php echo OSMembershipHelper::formatInvoiceNumber($row, $this->config) ; ?></a>
								<?php
								}
							?>
						</td>
					<?php
					}

					if($this->params->get('show_id', 1))
					{
					?>
						<td class="<?php echo $centerClass . ' ' . $hiddenPhone; ?>">
							<?php echo $row->id; ?>
						</td>
					<?php
					}
					?>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>

        <?php

		echo HTMLHelper::_(
			'bootstrap.renderModal',
			'collapseModal',
			[
				'title'  => Text::_('OSM_MASS_MAIL'),
				'footer' => $this->loadTemplate('batch_footer'),
			],
			$this->loadTemplate('batch_body')
		);
		?>

        <input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
    <?php
	if (!count($this->filters))
	{
	?>
        <form action="<?php echo Route::_('index.php?option=com_osmembership&view=subscribers&Itemid=' . $this->Itemid, false); ?>" method="post" name="subscriptionsExportForm" id="subscriptionsExportForm">
            <input type="hidden" name="task" value=""/>
            <input type="hidden" id="export_filter_search" name="filter_search"/>
            <input type="hidden" id="export_filter_date_field" name="filter_date_field" />
            <input type="hidden" id="export_filter_from_date" name="filter_from_date" value="">
            <input type="hidden" id="export_filter_to_date" name="filter_to_date" value="">
            <input type="hidden" id="export_plan_id" name="plan_id" value="">
	        <input type="hidden" id="export_filter_category_id" name="filter_category_id" value="">
            <input type="hidden" id="export_subscription_type" name="subscription_type" value="">
            <input type="hidden" id="export_published" name="published" value="">
            <input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    <?php
	}
	?>
</div>
