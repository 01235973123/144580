<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<table class="adminlist table table-striped">
	<thead>
		<th>
			<?php echo Text::_('OSM_ID'); ?>
		</th>
		<th>
			<?php echo Text::_('OSM_FIRSTNAME'); ?>
		</th>
		<th>
			<?php echo Text::_('OSM_LASTNAME'); ?>
		</th>
		<th>
			<?php echo Text::_('OSM_EMAIL'); ?>
		</th>
		<th>
			<?php echo Text::_('OSM_CREATED_DATE'); ?>
		</th>
		<th>
			<?php echo Text::_('OSM_DISCOUNT_AMOUNT'); ?>
		</th>
	</thead>
	<tbody>
		<?php
			foreach($this->subscriptions as $subscription)
			{
			?>
				<tr>
					<td><a href="index.php?option=com_osmembership&view=subscription&id=<?php echo $subscription->id; ?>" target="_blank"><?php echo $subscription->id; ?></a></td>
					<td><?php echo $subscription->first_name; ?></td>
					<td><?php echo $subscription->last_name; ?></td>
					<td><a href="mailto:<?php echo $subscription->email; ?>"><?php echo $subscription->email; ?></a></td>
					<td><?php echo HTMLHelper::_('date', $subscription->created_date, $this->config->date_format); ?></td>
					<td>
						<?php
							if ($this->item->coupon_type == 1)
							{
								echo OSMembershipHelper::formatAmount($this->item->discount, $this->config);
							}
							else
							{
								echo OSMembershipHelper::formatAmount($subscription->amount*$this->item->discount/100, $this->config);
							}
						?>
					</td>
				</tr>

			<?php
			}
		?>
	</tbody>
</table>