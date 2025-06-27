<?php

/**
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
$i = 1;
?>
<p style="padding-bottom: 20px; text-align: center;">
<h1><?php echo Text::_('JD_DONORS_LIST'); ?></h1>
</p>
<table border="1" width="100%" cellspacing="0" cellpadding="2" style="margin-top: 100px;">
	<thead>
	<tr>
		<?php
		if ($config->use_campaign)
		{
		?>
			<th width="15%" height="20" style="text-align: center;">
				<?php echo Text::_('JD_CAMPAIGN');?>
			</th>
		<?php
		}
		?>
		<th height="20" width="15%">
			<?php echo Text::_('JD_DONOR_INFORMATION'); ?>
		</th height="20">
		<th height="20" width="10%">
			<?php echo Text::_('JD_DONATION_TYPE'); ?>
		</th height="20">
		<th height="20" width="10%">
			<?php echo Text::_('JD_FREQUENCY'); ?>
		</th>
		<th height="20" width="10%" style="text-align: center">
			<?php echo Text::_('JD_NUMBER_PAYMENTS'); ?>
		</th>
		<th height="20" width="10%">
			<?php echo Text::_('JD_DONATION_AMOUNT'); ?>
		</th>
		<th height="20" width="7%" style="text-align: center;">
			<?php echo Text::_('JD_DONATION_DATE'); ?>
		</th>
		<th width="10%" height="20" style="text-align: center;">
			<?php echo Text::_('JD_PAYMENT_METHOD'); ?>
		</th>
        <th width="8%" height="20">
			<?php echo Text::_('JD_PAID_STATUS'); ?>
        </th>
		<th width="7%" height="20" style="text-align: center;">
			<?php echo Text::_('JD_TRANSACTION_ID'); ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($rows as $row)
	{
		?>
		<tr>
			<?php
			if ($config->use_campaign)
			{
			?>
				<td width="15%">
					<?php echo $row->title;?>
				</td>
			<?php
			}
			?>
			<td width="15%">
				<?php echo $row->first_name." ".$row->last_name; ?>
				<BR />
				<?php 
				echo $row->email;
				?>
			</td>
			<td width="10%">
				<?php 
				if ($row->donation_type == 'R')
				{
					echo 'Recurring';
				}
				else
				{
					echo 'One time';
				}
				?>
			</td>
			<td width="10%">
				<?php 
				switch ($row->r_frequency)
				{
					case 'd':
						echo 'Daily';
						break;
					case 'w':
						echo 'Weekly';
						break;
					case 'm':
						echo 'Monthly';
						break;
					case 'q':
						echo 'Quaterly';
						break;
					case 's':
						echo 'Semi-Annually';
						break;
					case 'a':
						echo 'Annually';
						break;
					default:
						echo '';
						break;
				}
				?>
			</td>
			<td width="10%;">
				<?php 
				if ($row->donation_type == 'R')
				{
					if (!$row->r_times)
					{
						$numberDonations = 'Un-limit';
					}
					else
					{
						$numberDonations = $row->r_times;
					}
					echo $row->payment_made . '/' . $numberDonations;
				}
				else
				{
					echo '';
				}
				?>
			</td>
			<td width="10%">
				<?php echo number_format($row->amount, 2); ?>
			</td>
			<td width="7%" style="text-align: center;"><?php echo HTMLHelper::_('date', $row->created_date, $config->date_format); ?></td>
			<td width="10%">
				<?php
				$method   = os_jdpayments::getPaymentMethod($row->payment_method);
				if ($method)
				{
					echo $method->getTitle();
				}
				else
				{
					echo '';
				}
				?>
			</td>
			<td width="8%">
				<?php 
				if($row->published == 1){
					echo Text::_('JD_PAID');
				}else{
					echo Text::_('JD_UNPAID');
				}
				?>
			</td>
            <td width="7%" height="20">
                <?php
                echo $row->transaction_id;
                ?>
            </td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>
