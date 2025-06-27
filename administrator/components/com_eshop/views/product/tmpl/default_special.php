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
		<th class="text_center" width="10%"><?php echo Text::_('ESHOP_PRIORITY'); ?></th>
		<th class="text_center" width="10%"><?php echo Text::_('ESHOP_PRICE'); ?></th>
		<th class="text_center" width="25%"><?php echo Text::_('ESHOP_START_DATE'); ?></th>
		<th class="text_center" width="25%"><?php echo Text::_('ESHOP_END_DATE'); ?></th>
		<th class="text_center" width="10%"><?php echo Text::_('ESHOP_PUBLISHED'); ?></th>
		<th class="text_center" width="10%">&nbsp;</th>
	</tr>
	</thead>
	<tbody id="product_specials_area">
	<?php
	$options = array();
	$options[] = HTMLHelper::_('select.option', '1', Text::_('ESHOP_YES'));
	$options[] = HTMLHelper::_('select.option', '0', Text::_('ESHOP_NO'));
	for ($i = 0; $n = count($this->productSpecials), $i < $n; $i++) {
		$productSpecial = $this->productSpecials[$i];
		?>
		<tr id="product_special_<?php echo $i; ?>">
			<td style="text-align: center;">
				<?php echo $this->lists['special_customer_group_'.$productSpecial->id]; ?>
				<input type="hidden" class="input-large form-select" name="productspecial_id[]" value="<?php echo $productSpecial->id; ?>" />
			</td>
			<td style="text-align: center;">
				<input class="input-mini form-control" type="text" name="special_priority[]" maxlength="10" value="<?php echo $productSpecial->priority; ?>" />
			</td>
			<td style="text-align: center;">
				<input class="input-medium form-control" type="text" name="special_price[]" maxlength="10" value="<?php echo $productSpecial->price; ?>" />
			</td>
			<td style="text-align: center;">
				<?php echo HTMLHelper::_('calendar', $productSpecial->date_start, 'special_date_start[]', 'special_date_start_'.$i, '%Y-%m-%d %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
			</td>
			<td style="text-align: center;">
				<?php echo HTMLHelper::_('calendar', $productSpecial->date_end, 'special_date_end[]', 'special_date_end_'.$i, '%Y-%m-%d %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
			</td>
			<td style="text-align: center;">
				<?php echo HTMLHelper::_('select.genericlist', $options, 'special_published[]', 'class="input-medium form-select"', 'value', 'text', $productSpecial->published); ?>
			</td>
			<td style="text-align: center;">
				<input type="button" class="btn btn-small btn-primary" name="btnRemove" value="<?php echo Text::_('ESHOP_BTN_REMOVE'); ?>" onclick="removeProductSpecial(<?php echo $i; ?>);" />
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan="7">
			<input type="button" class="btn btn-small btn-primary" name="btnAdd" value="<?php echo Text::_('ESHOP_BTN_ADD'); ?>" onclick="addProductSpecial();" />
		</td>
	</tr>
	</tfoot>
</table>