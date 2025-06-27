<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright	Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<table class="adminlist table table-striped">
	<thead>
	<tr>
		<th>
			<?php echo Text::_('OSM_PLAN') ?>
		</th>
		<th class="title center">
			<?php echo Text::_('OSM_ACTIVATE_TIME') ; ?>
		</th>
		<th style="text-align: right;">
			<?php echo Text::_('OSM_GROSS_AMOUNT') ; ?>
		</th>
		<th class="title center">
			<?php echo Text::_('OSM_SUBSCRIPTION_STATUS'); ?>
		</th>
		<th class="title center">
			<?php echo Text::_('OSM_TRANSACTION_ID') ; ?>
		</th>
		<?php
		if ($this->config->activate_invoice_feature)
		{
		?>
			<th style="text-align: center;">
				<?php echo Text::_('OSM_INVOICE_NUMBER') ; ?>
			</th>
		<?php
		}
		?>
	</tr>
	</thead>
	<tbody>
	<?php
	for ($i = 0 , $n = count($this->items) ; $i < $n ; $i++)
	{
		$row = $this->items[$i] ;
		$link 	= Route::_('index.php?option=com_osmembership&task=subscription.edit&cid[]=' . $row->id);
		?>
		<tr>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->plan_title; ?></a>
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
			<td style="text-align: right;">
				<?php echo $this->config->currency_symbol . number_format($row->gross_amount, 2) ; ?>
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
			<td class="center">
				<?php echo $row->transaction_id ; ?>
			</td>
			<?php
			if ($this->config->activate_invoice_feature)
			{
			?>
				<td class="center">
					<a href="<?php echo Route::_('index.php?option=com_osmembership&task=download_invoice&id=' . $row->id); ?>" title="<?php echo Text::_('OSM_DOWNLOAD'); ?>"><?php echo OSMembershipHelper::formatInvoiceNumber($row, $this->config) ; ?></a>
				</td>
				<?php
			}
			?>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>
