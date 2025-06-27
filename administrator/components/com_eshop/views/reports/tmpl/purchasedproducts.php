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
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('ESHOP_PURCHASED_PRODUCTS_REPORT'), 'generic.png');
ToolbarHelper::cancel('reports.cancel');

$input = Factory::getApplication()->input;

$dateStart = $input->getString('date_start', '');

if ($dateStart == '')
{
    $dateStart = date('Y-m-d', strtotime(date('Y') . '-' . date('m') . '-01'));
}

$dateEnd = $input->getString('date_end', '');

if ($dateEnd == '')
{
    $dateEnd = date('Y-m-d');
}

$isJoomla4 = EshopHelper::isJoomla4();

if ($isJoomla4)
{
	Factory::getApplication()->getDocument()
		->getWebAssetManager()
		->useScript('table.columns')
		->useScript('multiselect');
}
?>
<form action="index.php?option=com_eshop&view=reports&layout=purchasedproducts" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar<?php if ($isJoomla4) echo ' js-stools-container-filters-visible'; ?>">
			<div class="btn-group pull-left">
				<?php echo $this->lists['category_id']; ?>
			</div>
			<div class="btn-group pull-left">
				<?php echo HTMLHelper::_('calendar', $dateStart, 'date_start', 'date_start', '%Y-%m-%d', ['placeholder' => Text::_('ESHOP_FROM')]); ?>
			</div>
			<div class="btn-group pull-left">	
				<?php echo HTMLHelper::_('calendar', $dateEnd, 'date_end', 'date_end', '%Y-%m-%d', ['placeholder' => Text::_('ESHOP_TO')]); ?>
			</div>
			<div class="btn-group pull-left">	
				<?php echo $this->lists['order_status_id']; ?>
				<button onclick="this.form.submit();" class="btn btn-primary"><?php echo Text::_( 'ESHOP_GO' ); ?></button>
			</div>
		</div>
		<div class="clearfix"></div>
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th class="text_left" width="40%">
					<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>
				</th>
				<th class="text_center" width="15%">
					<?php echo Text::_('ESHOP_PRODUCT_SKU'); ?>
				</th>
				<th class="text_center" width="15%">
					<?php echo Text::_('ESHOP_PRODUCT_QUANTITY'); ?>
				</th>
				<th class="text_center" width="15%">
					<?php echo Text::_('ESHOP_PRODUCT_PROFIT'); ?>
				</th>
				<th class="text_center" width="15%">
					<?php echo Text::_('ESHOP_TOTAL'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$k = 0;
			$totalPrice = 0;
			$totalProfit = 0;
			
			for ($i = 0, $n = count( $this->items ); $i < $n; $i++)
			{
				$row = &$this->items[$i];
				$totalPrice += $this->tax->calculate($row->total_price, $row->product_taxclass_id, EShopHelper::getConfigValue('tax'));
				$profit = $this->tax->calculate($row->total_price - $row->quantity * $row->product_cost, $row->product_taxclass_id, EShopHelper::getConfigValue('tax'));
				$totalProfit += $profit;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="text_left">																			
						<b><?php echo $row->product_name; ?></b>
						<?php
						$orderOptions = $row->orderOptions;
				
        				for ($j = 0; $m = count($orderOptions), $j < $m; $j++)
        				{
        				    $orderOptionRow = $orderOptions[$j];
        				    
                            if ($j >= 0 && $j < $m)
                            {
                                echo "<br /> - ";
                            }
        
                            echo $orderOptionRow->option_name . ' (' . $orderOptionRow->option_value . ') ' . Text::_('ESHOP_QUANTITY') . ' ' . $orderOptionRow->total_quantity . ' ' . Text::_('ESHOP_TOTAL') . ' ' . $this->currency->format($this->tax->calculate($orderOptionRow->total_quantity * $row->product_price + $orderOptionRow->total_price, $row->product_taxclass_id, EShopHelper::getConfigValue('tax')), EShopHelper::getConfigValue('default_currency_code'));
        				}
        				?>
					</td>																			
					<td class="text_center">
						<?php echo $row->product_sku; ?>
					</td>
					<td class="text_center">
						<?php echo $row->quantity; ?>
					</td>
					<td class="text_center">
						<?php echo $this->currency->format($this->tax->calculate($profit, $row->product_taxclass_id, EShopHelper::getConfigValue('tax')), EShopHelper::getConfigValue('default_currency_code')); ?>
					</td>
					<td class="text_center">
						<?php echo $this->currency->format($this->tax->calculate($row->total_price, $row->product_taxclass_id, EShopHelper::getConfigValue('tax')), EShopHelper::getConfigValue('default_currency_code')); ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			<tr>
				<td colspan="3"></td>
				<td class="text_center">
					<?php echo $this->currency->format($totalProfit, EShopHelper::getConfigValue('default_currency_code')); ?>
				</td>
				<td class="text_center">
					<?php echo $this->currency->format($totalPrice, EShopHelper::getConfigValue('default_currency_code')); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="task" value="" />
</form>	