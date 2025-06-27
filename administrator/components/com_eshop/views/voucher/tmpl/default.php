<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (EShopHelper::isJoomla4())
{
    $tabApiPrefix = 'uitab.';
}
else
{
    HTMLHelper::_('behavior.tabstate');

    $tabApiPrefix = 'bootstrap.';
}
?>
<script type="text/javascript">	
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'voucher.cancel') {
			Joomla.submitform(pressbutton, form);
			return;				
		} else {
			//Validate the entered data before submitting
			if (form.voucher_code.value == '') {
				alert("<?php echo Text::_('ESHOP_ENTER_VOUCHER_CODE'); ?>");
				form.voucher_code.focus();
				return;
			}
			if (form.voucher_start_date.value > form.voucher_end_date.value) {
				alert("<?php echo Text::_('ESHOP_DATE_VALIDATE'); ?>");
				form.voucher_start_date.focus();
				return;
			}
			Joomla.submitform(pressbutton, form);
		}
	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'voucher', array('active' => 'general-page'));
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'voucher', 'general-page', Text::_('ESHOP_GENERAL', true));
	?>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo  Text::_('ESHOP_CODE'); ?>
		</div>
		<div class="controls">
			<input class="input-large form-control" type="text" name="voucher_code" id="voucher_code" maxlength="250" value="<?php echo $this->item->voucher_code; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_AMOUNT'); ?>
		</div>
		<div class="controls">
			<input class="input-medium form-control" type="text" name="voucher_amount" id="voucher_amount" maxlength="250" value="<?php echo $this->item->voucher_amount ? $this->item->voucher_amount : 0; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_START_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->voucher_start_date, 'voucher_start_date', 'voucher_start_date', '%Y-%m-%d %H:%M', ['showTime' => true]); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_END_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->voucher_end_date, 'voucher_end_date', 'voucher_end_date', '%Y-%m-%d %H:%M', ['showTime' => true]); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'voucher', 'history-page', Text::_('ESHOP_VOUCHER_HISTORY', true));
	?>
	<table class="adminlist" style="text-align: center;">
		<thead>
			<tr>
				<th class="title" width="10%"><?php echo Text::_('ESHOP_ORDER_ID')?></th>
				<th class="title" width="30%"><?php echo Text::_('ESHOP_AMOUNT')?></th>
				<th class="title" width="20%"><?php echo Text::_('ESHOP_CREATED_DATE')?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$voucherHistories = $this->voucherHistories;
			if (count($voucherHistories) == 0)
			{
				?>
				<tr>
					<td colspan="3" style="text-align: center;">
						<?php echo Text::_('ESHOP_NO_RESULTS'); ?>
					</td>
				</tr>
				<?php
			}
			else
			{
				for ($i = 0; $i< count($voucherHistories); $i++)
				{
					$voucherHistory = $voucherHistories[$i];
					?>
					<tr>
						<td align="center">
							<?php echo $voucherHistory->order_id; ?>
						</td>
						<td align="center">
							<?php echo number_format($voucherHistory->amount, 2); ?>
						</td>
						<td align="center">
							<?php echo HTMLHelper::_('date', $voucherHistory->created_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'), null); ?>
						</td>
					</tr>
					<?php
				}
			}
			?>
		</tbody>
	</table>
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
	echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	?>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<input type="hidden" name="task" value="" />	
</form>