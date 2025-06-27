<?php
/**
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Layout variables
 *
 * @var MPFConfig $config
 * @var array     $rows
 * @var bool      $showLastName
 */
?>
<table class="adminlist table table-striped eshop-latest-orders-table">
	<thead>
	<tr>
		<th class="title" nowrap="nowrap"><?php echo Text::_('ESHOP_CUSTOMER'); ?></th>
		<th class="center" nowrap="nowrap"><?php echo Text::_('ESHOP_ORDER_STATUS'); ?></th>
		<th class="center" nowrap="nowrap"><?php echo Text::_('ESHOP_ORDER_TOTAL'); ?></th>
		<th class="center" nowrap="nowrap"><?php echo Text::_('ESHOP_CREATED_DATE'); ?></th>
		<th class="title" nowrap="nowrap"><?php echo Text::_('ESHOP_ORDER_NUMBER'); ?></th>
		<th class="title" nowrap="nowrap"><?php echo Text::_('ESHOP_TRANSACTION_ID'); ?></th>
		<th class="title" nowrap="nowrap"><?php echo Text::_('ESHOP_ID'); ?></th>
		<th class="title" nowrap="nowrap"><?php echo Text::_('ESHOP_ACTION'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($rows as $row)
	{
		$link 	= Route::_('index.php?option=com_eshop&task=order.edit&cid[]='. $row->id);
		?>
		<tr>
			<td><?php echo $row->firstname . ' ' . $row->lastname; ?></td>
			<td class="center">
				<?php echo EShopHelper::getOrderStatusName($row->order_status_id, ComponentHelper::getParams('com_languages')->get('site', 'en-GB')); ?>
			</td>
			<td class="center">
				<?php echo $currency->format($row->total, $row->currency_code, $row->currency_exchanged_value); ?>
			</td>
			<td class="center">
				<?php
				if ($row->created_date != $nullDate)
				{
					echo HTMLHelper::date($row->created_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'));
				}							
				?>
			</td>
			<td class="center">
				<?php echo $row->order_number; ?>
			</td>
			<td class="center">
				<?php echo $row->transaction_id; ?>
			</td>
			<td class="center">
				<?php echo $row->id; ?>
			</td>
			<td class="center">
				<a href="<?php echo $link; ?>"><?php echo Text::_('ESHOP_EDIT'); ?></a>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>