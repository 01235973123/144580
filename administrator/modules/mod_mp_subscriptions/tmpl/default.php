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

/**
 * Layout variables
 *
 * @var MPFConfig $config
 * @var array     $rows
 * @var bool      $showLastName
 */
?>
<table class="adminlist table table-striped osm-latest-subscriptions-table">
	<thead>
	<tr>
		<th class="title" nowrap="nowrap"><?php echo Text::_('OSM_FIRSTNAME'); ?></th>
		<?php
			if ($showLastName)
			{
			?>
				<th class="title" nowrap="nowrap"><?php echo Text::_('OSM_LASTNAME'); ?></th>
			<?php
			}
		?>
		<th class="title" nowrap="nowrap"><?php echo Text::_('OSM_PLAN'); ?></th>
		<th class="center" nowrap="nowrap"><?php echo Text::_('OSM_START_DATE'); ?></th>
		<th class="center" nowrap="nowrap"><?php echo Text::_('OSM_END_DATE'); ?></th>
		<th class="center" nowrap="nowrap"><?php echo Text::_('OSM_CREATED_DATE'); ?></th>
		<th class="title" nowrap="nowrap"><?php echo Text::_('OSM_GROSS_AMOUNT'); ?></th>
		<th class="title" nowrap="nowrap"><?php echo Text::_('OSM_SUBSCRIPTION_STATUS'); ?></th>
		<?php
		if ($config->activate_invoice_feature)
		{
		?>
			<th class="center">
				<?php echo Text::_('OSM_INVOICE_NUMBER'); ?>
			</th>
		<?php
		}

		if ($config->auto_generate_membership_id)
		{
		?>
			<th class="center">
				<?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>
			</th>
		<?php
		}
		?>
		<th class="title" nowrap="nowrap"><?php echo Text::_('ID'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($rows as $row)
	{
		$link      = Route::_('index.php?option=com_osmembership&view=subscription&id=' . $row->id);
		?>
		<tr>
			<td><a href="<?php echo $link ?>" target="_blank"><?php echo $row->first_name ?: $row->username; ?></a></td>
			<?php
			if ($showLastName)
			{
			?>
				<td><?php echo $row->last_name; ?></td>
			<?php
			}
			?>
			<td><a href="<?php echo Route::_('index.php?option=com_osmembership&task=plan.edit&cid[]=' . $row->plan_id); ?>"
			       target="_blank"><?php echo $row->plan_title; ?></a></td>
			<td class="center">
				<?php echo HTMLHelper::_('date', $row->from_date, $config->date_format); ?>
			</td>
			<td class="center">
				<?php
				if ($row->lifetime_membership || $row->to_date == '2099-12-31 23:59:59')
				{
					echo Text::_('OSM_LIFETIME');
				}
				else
				{
					echo HTMLHelper::_('date', $row->to_date, $config->date_format);
				}
				?>
			</td>
			<td class="center">
				<?php echo HTMLHelper::_('date', $row->created_date, $config->date_format . ' H:i:s'); ?>
			</td>
			<td>
				<?php echo OSMembershipHelper::formatCurrency($row->gross_amount, $config); ?>
			</td>
			<td>
				<?php
				switch ($row->published)
				{
					case 0 :
						echo Text::_('OSM_PENDING');
						break;
					case 1 :
						echo Text::_('OSM_ACTIVE');
						break;
					case 2 :
						echo Text::_('OSM_EXPIRED');
						break;
					case 3 :
						echo Text::_('OSM_CANCELLED_PENDING');
						break;
					case 4 :
						echo Text::_('OSM_CANCELLED_REFUNDED');
						break;
				}
				?>
			</td>
			<?php
			if ($config->activate_invoice_feature)
			{
			?>
				<td class="center">
					<?php
					if ($row->invoice_number)
					{
					?>
						<a href="<?php echo Route::_('index.php?option=com_osmembership&task=download_invoice&id=' . $row->id); ?>"
						   title="<?php echo Text::_('OSM_DOWNLOAD'); ?>"><?php echo OSMembershipHelper::formatInvoiceNumber($row, $config); ?></a>
					<?php
					}
					?>
				</td>
			<?php
			}

			if ($config->auto_generate_membership_id)
			{
			?>
				<td class="center">
					<?php echo OSMembershipHelper::formatMembershipId($row, $config); ?>
				</td>
			<?php
			}
			?>
			<td class="center">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>