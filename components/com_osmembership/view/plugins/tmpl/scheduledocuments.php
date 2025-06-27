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

/**
 * Layout variables
 * -----------------
 * @var   array $items
 * @var   array $subscriptions
 */

$config          = OSMembershipHelper::getConfig();
$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$centerClass     = $bootstrapHelper->getClassMapping('center');
?>
<table class="adminlist <?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?>" id="adminForm">
	<thead>
	<tr>
		<th class="title"><?php echo Text::_('OSM_DOCUMENT'); ?></th>
		<th class="title <?php echo $centerClass; ?>"><?php echo Text::_('OSM_ACCESSIBLE_ON'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($items as $item)
	{
		$subscription = $subscriptions[$item->plan_id];
		$date         = Factory::getDate($subscription->active_from_date);
		$date->add(new DateInterval('P' . $item->number_days . 'D'));
		?>
		<tr>
			<td>
				<i class="icon-file"></i>
				<?php
				if ($subscription->active_in_number_days >= $item->number_days)
				{
				?>
					<a href="<?php echo Route::_('index.php?option=com_osmembership&task=download_schedule_document&id=' . $item->id) ?>"><?php echo $item->document; ?></a>
				<?php
				}
				else
				{
					echo $item->document . ' <span class="label">' . Text::_('OSM_LOCKED') . '</span>';
				}
				?>
			</td>
			<td class="<?php echo $centerClass; ?>">
				<?php echo HTMLHelper::_('date', $date->format('Y-m-d H:i:s'), $config->date_format); ?>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>
