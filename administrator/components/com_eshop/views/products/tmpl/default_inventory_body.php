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

use Joomla\CMS\Language\Text;

?>
<table class="adminlist table table-striped" id="recordsList">
	<thead>
		<tr>
			<th class="title" width="35%">
				<?php echo Text::_('ESHOP_NAME'); ?>
			</th>
			<th class="title" width="15%">
				<?php echo Text::_('ESHOP_PRICE'); ?>
			</th>
			<th class="title" width="15%">
				<?php echo Text::_('ESHOP_PRODUCT_MANAGE_STOCK'); ?>
			</th>
			<th class="title" width="15%">
				<?php echo Text::_('ESHOP_PRODUCT_QUANTITY'); ?>
			</th>
			<th class="title" width="20%">
				<?php echo Text::_('ESHOP_PRODUCT_THRESHOLD'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
    	<?php
    	for ($i = 0, $n = count( $this->items ); $i < $n; $i++)
    	{
    	    $row = &$this->items[$i];
    	    ?>
    	    <tr>
    	    	<td>
    	    		<?php echo $row->product_name; ?>
    	    		<input type="hidden" name="product_id[]" value="<?php echo $row->id; ?>">
    	    	</td>
    	    	<td><input class="input-small form-control" type="text" name="product_price_fi[]" maxlength="10" value="<?php echo $row->product_price; ?>" /></td>
    	    	<td>
        	    	<?php
        	    	echo EShopHtmlHelper::getBooleanInput('product_manage_stock_' . $row->id, $row->product_manage_stock ?? '1');
        	    	?>
    	    	</td>
    	    	<td><input class="input-small form-control" type="text" name="product_quantity_fi[]" maxlength="10" value="<?php echo $row->product_quantity; ?>" /></td>
    	    	<td><input class="input-small form-control" type="text" name="product_threshold_fi[]" maxlength="10" value="<?php echo $row->product_threshold; ?>" /></td>
    	    </tr>
    	    <?php
    	}
    	?>
	</tbody>
</table>