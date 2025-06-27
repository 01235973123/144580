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

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

$isJoomla4 = EshopHelper::isJoomla4();

if ($isJoomla4)
{
	Factory::getApplication()->getDocument()
		->getWebAssetManager()
		->useScript('table.columns')
		->useScript('multiselect');
}
?>
<form action="index.php?option=com_eshop&view=orders" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar<?php if ($isJoomla4) echo ' js-stools-container-filters-visible'; ?>">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('ESHOP_FILTER_SEARCH_ORDERS_DESC');?></label>
				<input type="text" name="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('ESHOP_SEARCH_ORDERS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<?php
				    echo $this->lists['product_id'];
				    echo $this->lists['manufacturer_id'];
				    echo $this->lists['payment_method'];
				    echo $this->lists['shipping_method'];
					echo $this->lists['order_status_id'];
					echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped">
			<thead>
			<tr>
				<th width="2%" class="text_center">
					<?php echo HTMLHelper::_('grid.checkall'); ?>
				</th>
				<th class="text_left" width="15%">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_CUSTOMER'), 'a.firstname', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="text_center" width="10%">
					<?php echo Text::_('ESHOP_ORDER_STATUS'); ?>
				</th>
				<th class="text_center" width="10%">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ORDER_TOTAL'), 'a.total', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="text_center" width="10%">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_CREATED_DATE'), 'a.created_date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="10%" class="text_center">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ORDER_NUMBER'), 'a.order_number', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="10%" class="text_center">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_TRANSACTION_ID'), 'a.transaction_id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<?php
				$colspan = 9;
				
				if (EShopHelper::getConfigValue('invoice_enable'))
				{
				    $colspan = 10;
				?>
					<th width="5%" class="text_center">
    					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_INVOICE_NUMBER'), 'a.invoice_number', $this->lists['order_Dir'], $this->lists['order'] ); ?>
    				</th>	
				<?php
				}
				?>
				<th width="5%" class="text_center">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ID'), 'a.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="20%" class="text_center">
					<?php echo Text::_('ESHOP_ACTION'); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="<?php echo $colspan; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			
			for ($i = 0, $n = count( $this->items ); $i < $n; $i++)
			{
				$row = &$this->items[$i];
				$link 	= Route::_('index.php?option=com_eshop&task=order.edit&cid[]='. $row->id);
				$checked 	= HTMLHelper::_('grid.id',   $i, $row->id );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="text_center">
						<?php echo $checked; ?>
					</td>
					<td class="text_left">
						<?php echo $row->firstname . ' ' . $row->lastname; ?>
					</td>
					<td class="text_center">
						<?php echo EShopHelper::getOrderStatusName($row->order_status_id, ComponentHelper::getParams('com_languages')->get('site', 'en-GB')); ?>
					</td>
					<td class="text_center">
						<?php echo $this->currency->format($row->total, $row->currency_code, $row->currency_exchanged_value); ?>
					</td>
					<td class="text_center">
						<?php
						if ($row->created_date != $this->nullDate)
						{
							echo HTMLHelper::date($row->created_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'));
						}							
						?>
					</td>
					<td class="text_center">
						<?php echo $row->order_number; ?>
					</td>
					<td class="text_center">
						<?php echo $row->transaction_id; ?>
					</td>
					<?php
					if (EShopHelper::getConfigValue('invoice_enable'))
					{
					?>
						<td class="text_center">
							<?php
							if (EShopHelper::isInvoiceAvailable($row, '1', false))
							{
							    ?>
							    <a href="<?php echo Route::_('index.php?option=com_eshop&task=order.downloadInvoice&cid[]='. $row->id); ?>"><?php echo $row->invoice_number; ?></a>
							    <?php
							}
							?>
						</td>    					
					<?php
					}
						?>
					<td class="text_center">
						<?php echo $row->id; ?>
					</td>
					<td class="text_center">
						<a href="<?php echo $link; ?>"><?php echo Text::_('ESHOP_EDIT'); ?></a>
						<?php
						if (EShopHelper::isInvoiceAvailable($row, '1', false))
						{
						?>
							&nbsp;|&nbsp;<a href="<?php echo Route::_('index.php?option=com_eshop&task=order.downloadInvoice&cid[]='. $row->id); ?>"><?php echo Text::_('ESHOP_DOWNLOAD_INVOICE'); ?></a>
						<?php
						}
						?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php
		// Load the batch processing form
		echo HTMLHelper::_(
			'bootstrap.renderModal',
			'collapseModal',
			array(
				'title' => Text::_('ESHOP_ORDERS_BATCH_OPTIONS'),
				'footer' => $this->loadTemplate('batch_footer')
			),
			$this->loadTemplate('batch_body')
		);

		echo HTMLHelper::_( 'form.token' );
	?>	
</form>