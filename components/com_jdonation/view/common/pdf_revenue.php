<?php
use Joomla\CMS\Language\Text;
/**
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$i = 1;
?>
<p style="padding-bottom: 20px; text-align: center;">
<h2><?php echo Text::_('JD_DONATION_REPORT_FOR_THE_CURRENT_YEAR_FOR_ALL_CAMPAIGNS'); ?></h2>
<BR />
<?php echo Text::_("JD_DATE_RANGE");?>: January 1, <?php echo date("Y");?> <?php echo Text::_("JD_TO");?> <?php echo date("M");?> <?php echo date("j");?>, <?php echo date("Y");?>
</p>
<table border="1" width="100%" cellspacing="0" cellpadding="2" style="margin-top: 100px;">
	<thead>
		<tr>
			<?php
			if ($config->use_campaign)
			{
			?>
				<th width="50%" height="20" style="text-align: center;">
					<?php echo Text::_('JD_CAMPAIGN');?>
				</th>
			<?php
			}
			?>
			
			<th height="20" width="25%" style="text-align: center">
				<?php echo Text::_('JD_NUMBER_DONORS'); ?>
			</th>
			<th height="20" width="25%">
				<?php echo Text::_('JD_DONATION_AMOUNT'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($campaigns as $row)
	{
		if($row->total_donation > 0)
		{
			?>
			<tr>
				<?php
				if ($config->use_campaign)
				{
				?>
					<td width="50%">
						<?php echo $row->title;?>
					</td>
				<?php
				}
				?>
				<td width="25%" align="center">
					<?php echo (int)$row->total_donation; ?>
				</td>
				<td width="25%" align="center">
					<?php echo $config->currency_symbol.number_format($row->total_donated, 2); ?>
				</td>
			</tr>
			<?php
		}
	}
	?>
	</tbody>
</table>
