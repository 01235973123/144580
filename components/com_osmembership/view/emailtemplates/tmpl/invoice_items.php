<?php
/**
 * @package        	Joomla
 * @subpackage		Membership Pro
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 *
 * @var array     $items
 * @var MPFConfig $config
 * @var float     $subTotal
 * @var float     $discountAmount
 * @var float     $taxAmount
 * @var float     $total
 */

?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<thead>
		<tr>
			<th align="left" valign="top" width="10%">#</th>
			<th align="left" valign="top" width="80%"><?php echo Text::_('OSM_ITEM_NAME'); ?></th>
			<th align="right" valign="top" width="10%"><?php echo Text::_('OSM_PRICE'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			$i = 1;
			foreach($items as $item)
			{
			?>
				<tr>
					<td>
						<?php echo $i++; ?>
					</td>
					<td>
						<?php echo $item->title; ?>
					</td>
					<td align="right">
						<?php echo OSMembershipHelper::formatCurrency($item->amount, $config); ?>
					</td>
				</tr>
			<?php
			}
		?>		
		<tr>
			<td colspan="2" align="right" valign="top" width="90%"><?php echo Text::_('OSM_SUB_TOTAL'); ?> :</td>
			<td align="right" valign="top" width="10%"><?php echo OSMembershipHelper::formatCurrency($subTotal, $config);  ?></td>
		</tr>
		<tr>
			<td colspan="2" align="right" valign="top" width="90%"><?php echo Text::_('OSM_DISCOUNT_AMOUNT'); ?> :</td>
			<td align="right" valign="top" width="10%"><?php echo OSMembershipHelper::formatCurrency($discountAmount, $config); ?></td>
		</tr>		
		<tr>
			<td colspan="2" align="right" valign="top" width="90%"><?php echo Text::_('OSM_TAX_AMOUNT');?> :</td>
			<td align="right" valign="top" width="10%"><?php echo OSMembershipHelper::formatCurrency($taxAmount, $config); ?></td>
		</tr>
		<tr>
			<td colspan="2" align="right" valign="top" width="90%"><?php echo Text::_('OSM_GROSS_AMOUNT');?></td>
			<td align="right" valign="top" width="10%"><?php echo OSMembershipHelper::formatCurrency($total, $config);?></td>
		</tr>
	</tbody>
</table>