<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<table class="adminlist table table-bordered" style="text-align: center;">
	<thead>
	<tr>
		<th class="text_center" width="10%"><?php echo Text::_('ESHOP_CUSTOMER_GROUP'); ?></th>
		<th class="text_center" width="5%"><?php echo Text::_('ESHOP_QUANTITY'); ?></th>
		<th class="text_center" width="5%"><?php echo Text::_('ESHOP_PRIORITY'); ?></th>
		<th class="text_center" width="10%"><?php echo Text::_('ESHOP_PRICE'); ?></th>
		<th class="text_center" width="25%"><?php echo Text::_('ESHOP_START_DATE'); ?></th>
		<th class="text_center" width="25%"><?php echo Text::_('ESHOP_END_DATE'); ?></th>
		<th class="text_center" width="10%"><?php echo Text::_('ESHOP_PUBLISHED'); ?></th>
		<th class="text_center" width="5%">&nbsp;</th>
	</tr>
	</thead>
	<tbody id="product_discounts_area">
	<?php
	$options = array();
	$options[] = HTMLHelper::_('select.option', '1', Text::_('ESHOP_YES'));
	$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_NO'));
	
	for ($i = 0; $n = count($this->productDiscounts), $i < $n; $i++)
	{
		$productDiscount = $this->productDiscounts[$i];
		?>
		<tr id="product_discount_<?php echo $i; ?>">
			<td style="text-align: center;">
				<?php echo $this->lists['discount_customer_group_'.$productDiscount->id]; ?>
				<input type="hidden" class="input-large form-select" name="productdiscount_id[]" value="<?php echo $productDiscount->id; ?>" />
			</td>
			<td style="text-align: center;">
				<input class="input-mini form-control" type="text" name="discount_quantity[]" maxlength="10" value="<?php echo $productDiscount->quantity; ?>" />
			</td>
			<td style="text-align: center;">
				<input class="input-mini form-control" type="text" name="discount_priority[]" maxlength="10" value="<?php echo $productDiscount->priority; ?>" />
			</td>
			<td style="text-align: center;">
				<input class="input-medium form-control" type="text" name="discount_price[]" maxlength="10" value="<?php echo $productDiscount->price; ?>" />
			</td>
			<td style="text-align: center;">
				<?php echo HTMLHelper::_('calendar', $productDiscount->date_start, 'discount_date_start[]', 'discount_date_start_'.$i, '%Y-%m-%d %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
			</td>
			<td style="text-align: center;">
				<?php echo HTMLHelper::_('calendar', $productDiscount->date_end, 'discount_date_end[]', 'discount_date_end_'.$i, '%Y-%m-%d %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
			</td>
			<td style="text-align: center;">
				<?php echo HTMLHelper::_('select.genericlist', $options, 'discount_published[]', 'class="input-medium form-select"', 'value', 'text', $productDiscount->published); ?>
			</td>
			<td style="text-align: center;">
				<input type="button" class="btn btn-small btn-primary" name="btnRemove" value="<?php echo Text::_('ESHOP_BTN_REMOVE'); ?>" onclick="removeProductDiscount(<?php echo $i; ?>);" />
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan="9">
			<input type="button" class="btn btn-small btn-primary" name="btnAdd" value="<?php echo Text::_('ESHOP_BTN_ADD'); ?>" onclick="addProductDiscount();" />
		</td>
	</tr>
	</tfoot>
</table>
