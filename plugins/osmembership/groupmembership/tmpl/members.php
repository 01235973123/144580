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

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$config = OSMembershipHelper::getConfig();

/**
 * @var array $rowMembers
 * @var array $fields
 * @var array $fieldsData
 * @var int   $Itemid
 */

$rowFluidClass = $bootstrapHelper->getClassMapping('row-fluid');
$clearFixClass = $bootstrapHelper->getClassMapping('clearfix');
$centerClass   = $bootstrapHelper->getClassMapping('center');

$cols = count($fields) + 3;
?>
<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered table-hover'); ?>">
	<thead>
	<tr>
		<th><?php echo Text::_('OSM_PLAN'); ?></th>
		<th><?php echo Text::_('OSM_USERNAME'); ?></th>
		<?php
		foreach($fields as $field)
		{
		?>
			<th><?php echo $field->title; ?></th>
		<?php
		}

		if ($config->auto_generate_membership_id)
		{
			$cols++ ;
			?>
			<th width="8%" class="<?php echo $centerClass; ?>">
				<?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>
			</th>
			<?php
		}
		?>
		<th class="<?php echo $centerClass; ?>">
			<?php echo Text::_('OSM_CREATED_DATE') ; ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	for ($i = 0 , $n = count($rowMembers) ; $i < $n ; $i++)
	{
		$rowMember  = $rowMembers[$i];
		$link = Route::_('index.php?option=com_osmembership&view=groupmember&id=' . $rowMember->id . '&Itemid=' . $Itemid);
		?>
		<tr>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $rowMember->plan_title;?></a>
			</td>
			<td>
				<?php echo $rowMember->username; ?>
			</td>
			<?php
			foreach ($fields as $field)
			{
			?>
				<td>
					<?php
					if ($field->is_core)
					{
						echo $rowMember->{$field->name};
					}
					else
					{
						echo $fieldsData[$rowMember->id][$field->id] ?? '';
					}
					?>
				</td>
			<?php
			}

			if ($config->auto_generate_membership_id)
			{
			?>
				<td class="<?php echo $centerClass; ?>">
					<?php echo $rowMember->membership_id ? OSMembershipHelper::formatMembershipId($rowMember, $config) : ''; ?>
				</td>
			<?php
			}
			?>
			<td class="<?php echo $centerClass; ?>">
				<?php echo HTMLHelper::_('date', $rowMember->created_date, $config->date_format); ?>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>
