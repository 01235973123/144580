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
use OSSolution\HelpdeskPro\Site\Helper\Route as RouteHelper;
use OSSolution\HelpdeskPro\Site\Helper\Helper as HelpdeskProHelper;

/**
 * Layout variables
 *
 * @var array $rows
 * @var array $statusList
 * @var array $priorityList
 */

$bootstrapHelper  = OSMembershipHelperBootstrap::getInstance();
$hiddenPhoneClass = $bootstrapHelper->getClassMapping('hidden-phone');
$centerClass      = $bootstrapHelper->getClassMapping('center');
$btnDanger        = $bootstrapHelper->getClassMapping('btn btn-danger');
$config           = HelpdeskProHelper::getConfig();

HelpdeskProHelper::loadLanguage();
?>
<table class="osm-hdp-tickets-history <?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> osm-responsive-table">
	<thead>
	<tr>
		<th style="text-align: left;">
			<?php echo Text::_('HDP_TITLE'); ?>
		</th>
		<?php
		if ($this->params->get('show_created_date', 1))
		{
		?>
			<th class="<?php echo $centerClass; ?>">
				<?php echo Text::_('HDP_CREATED_DATE'); ?>
			</th>
		<?php
		}

		if ($this->params->get('show_modified_date', 1))
		{
		?>
			<th class="<?php echo $centerClass; ?>">
				<?php echo Text::_('HDP_MODIFIED_DATE'); ?>
			</th>
		<?php
		}

		if (isset($lists['filter_status_id']) && $this->params->get('show_status', 1))
		{
		?>
			<th width="8%">
				<?php echo Text::_('HDP_STATUS'); ?>
			</th>
		<?php
		}

		if (isset($lists['filter_priority_id']) && $this->params->get('show_priority', 1))
		{
		?>
			<th width="8%">
				<?php echo Text::_('HDP_PRIORITY'); ?>
			</th>
		<?php
		}
		?>
		<th width="2%" class="<?php echo $centerClass; ?>">
			<?php echo Text::_('HDP_ID'); ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$k = 0;
	foreach ($rows as $row)
	{
		$link 	= Route::_(RouteHelper::getTicketRoute($row->id), false);
		?>
		<tr class="<?php echo "row$k"; ?> hdp-ticket-status-<?php echo $row->status_id; ?>">
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->subject; ?></a>
				<?php
				if ($this->params->get('show_category', 1))
				{
				?>
					<br />
					<small><?php echo Text::_('HDP_CATEGORY'); ?>: <strong><?php echo $row->category_title ; ?></strong></small>
				<?php
				}
				?>
			</td>
			<?php

			if ($this->params->get('show_created_date', 1))
			{
			?>
				<td class="<?php echo $centerClass; ?>">
					<?php echo HTMLHelper::_('date', $row->created_date, $config->date_format); ?>
				</td>
			<?php
			}

			if ($this->params->get('show_modified_date', 1))
			{
			?>
				<td class="<?php echo $centerClass; ?>">
					<?php echo HTMLHelper::_('date', $row->modified_date, $config->date_format); ?>
				</td>
			<?php
			}

			if (count($statusList) && $this->params->get('show_status', 1))
			{
			?>
				<td>
					<?php echo $statusList[$row->status_id] ?? ''; ?>
				</td>
			<?php
			}

			if (count($priorityList) && $this->params->get('show_priority', 1))
			{
			?>
				<td>
					<?php echo $priorityList[$row->priority_id] ?? ''; ?>
				</td>
			<?php
			}

			?>
			<td class="<?php echo $centerClass; ?>">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
</table>
