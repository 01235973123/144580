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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$language = Factory::getLanguage();
$tag = $language->getTag();
$bootstrapHelper        = $this->bootstrapHelper;
$rowFluidClass          = $bootstrapHelper->getClassMapping('row-fluid');
$span2Class             = $bootstrapHelper->getClassMapping('span2');
$pullLeftClass          = $bootstrapHelper->getClassMapping('pull-left');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

if (!$tag)
{
	$tag = 'en-GB';
}

if (isset($this->warning))
{
	?>
	<div class="warning"><?php echo $this->warning; ?></div>
	<?php
}
?>
<div class="page-header">
	<h1 class="page-title eshop-title"><?php echo Text::_('ESHOP_ORDER_HISTORY'); ?></h1>
</div>
<?php
if (!count($this->orders))
{
	?>
	<div class="no-content"><?php echo Text::_('ESHOP_NO_ORDERS'); ?></div>
	<?php
}
else
{
	?>
	<div class="<?php echo $rowFluidClass; ?>">
		<form id="adminForm" class="order-list">
			<?php
			foreach ($this->orders as $order)
			{
				?>
				<div class="order-id"><b><?php echo Text::_('ESHOP_ORDER_ID'); ?>: </b>#<?php echo $order->id; ?></div>
				<div class="order-status"><b><?php echo Text::_('ESHOP_STATUS'); ?>: </b><?php echo EShopHelper::getOrderStatusName($order->order_status_id, $tag); ?></div>
				<div class="order-content">
					<div>
						<b><?php echo Text::_('ESHOP_DATE_ADDED'); ?>: </b><?php echo HTMLHelper::date($order->created_date, EShopHelper::getConfigValue('date_format', 'm-d-Y')); ?><br />
						<b><?php echo Text::_('ESHOP_PRODUCT'); ?>: </b><?php echo EShopHelper::getNumberProduct($order->id); ?>
					</div>
					<div>
						<b><?php echo Text::_('ESHOP_CUSTOMER'); ?>: </b><?php echo $order->firstname . ' ' . $order->lastname; ?><br />
						<b><?php echo Text::_('ESHOP_TOTAL'); ?>: </b> <?php echo $order->total; ?>
					</div>
					<div class="order-info" align="right">
						<a href="<?php echo Route::_(EShopRoute::getViewRoute('customer').'&layout=order&order_id='.(int)$order->id); ?>"><?php echo Text::_('ESHOP_VIEW'); ?></a>
						<?php
						if (EShopHelper::getConfigValue('allow_re_order'))
						{
							?>
							&nbsp;|&nbsp;
							<a href="<?php echo Route::_('index.php?option=com_eshop&task=cart.reOrder&order_id='.(int)$order->id); ?>"><?php echo Text::_('ESHOP_RE_ORDER'); ?></a>
							<?php
						}
						if (EShopHelper::isInvoiceAvailable($order, '0', true))
						{
							?>
							&nbsp;|&nbsp;
							<a href="<?php echo Route::_('index.php?option=com_eshop&task=customer.downloadInvoice&order_id='.(int)$order->id); ?>"><?php echo Text::_('ESHOP_DOWNLOAD_INVOICE'); ?></a>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}
			?>
		</form>
	</div>
	<?php
}
?>
<div class="<?php echo $rowFluidClass; ?>">
	<div class="<?php echo $span2Class; ?>">
		<input type="button" value="<?php echo Text::_('ESHOP_BACK'); ?>" id="button-back-order" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullLeftClass; ?>" />
	</div>
</div>
<script type="text/javascript">
	Eshop.jQuery(function($){
		$(document).ready(function(){
			$('#button-back-order').click(function() {
				var url = '<?php echo Route::_(EShopRoute::getViewRoute('customer')); ?>';
				$(location).attr('href', url);
			});
		})
	});
</script>