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

ToolbarHelper::title(Text::_('ESHOP_ORDERS_REPORT'), 'generic.png');
ToolbarHelper::custom('exports.process', 'download', 'download', Text::_('ESHOP_EXPORTS'), false);
ToolbarHelper::custom('order.downloadInvoice', 'print', 'print', Text::_('ESHOP_DOWNLOAD_INVOICE'), false);
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
<form action="index.php?option=com_eshop&view=reports&layout=orders" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar<?php if ($isJoomla4) echo ' js-stools-container-filters-visible'; ?>">
			<div class="btn-group pull-left">
				<?php echo HTMLHelper::_('calendar', $dateStart, 'date_start', 'date_start', '%Y-%m-%d', ['placeholder' => Text::_('ESHOP_FROM')]); ?>
			</div>
			<div class="btn-group pull-left">	
				<?php echo HTMLHelper::_('calendar', $dateEnd, 'date_end', 'date_end', '%Y-%m-%d', ['placeholder' => Text::_('ESHOP_TO')]); ?>
			</div>
			<div class="btn-group pull-left">	
				<?php echo $this->lists['group_by']; ?>
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
    				<th width="20%" class="text_left"><?php echo Text::_('ESHOP_START_DATE'); ?></th>
    				<th width="20%" class="text_left"><?php echo Text::_('ESHOP_END_DATE'); ?></th>
    				<th width="10%" class="text_center"><?php echo Text::_('ESHOP_NUMBER_ORDERS'); ?></th>
    				<th width="10%" class="text_center"><?php echo Text::_('ESHOP_NUMBER_PRODUCTS'); ?></th>
    				<th width="10%" class="text_center"><?php echo Text::_('ESHOP_UNIT_PRICE'); ?></th>
    				<th width="10%" class="text_center"><?php echo Text::_('ESHOP_SHIPPING'); ?></th>
    				<th width="10%" class="text_center"><?php echo Text::_('ESHOP_TAX'); ?></th>
    				<th width="10%" class="text_center"><?php echo Text::_('ESHOP_TOTAL'); ?></th>
    			</tr>
    		</thead>
    		<tbody>
    			<?php
    			$k = 0;
    			for ($i = 0, $n = count( $this->items ); $i < $n; $i++)
    			{
    				$row = &$this->items[$i];
    				?>
    				<tr class="<?php echo "row$k"; ?>">
    					<td class="text_left">
    						<?php echo HTMLHelper::_('date', $row->date_start ? $row->date_start : $dateStart, EShopHelper::getConfigValue('date_format', 'm-d-Y'), null); ?>
    					</td>																			
    					<td class="text_left">
    						<?php echo HTMLHelper::_('date', $row->date_end ? $row->date_end : $dateEnd, EShopHelper::getConfigValue('date_format', 'm-d-Y'), null); ?>
    					</td>
    					<td class="text_center">
    						<?php echo $row->orders; ?>
    					</td>
    					<td class="text_center">
    						<?php echo $row->products; ?>
    					</td>
    					<td class="text_center">
    						<?php echo $this->currency->format($row->sub_total ? $row->sub_total : 0, EShopHelper::getConfigValue('default_currency_code')); ?>
    					</td>
    					<td class="text_center">
    						<?php echo $this->currency->format($row->shipping ? $row->shipping : 0, EShopHelper::getConfigValue('default_currency_code')); ?>
    					</td>
    					<td class="text_center">
    						<?php echo $this->currency->format($row->tax ? $row->tax : 0, EShopHelper::getConfigValue('default_currency_code')); ?>
    					</td>
    					<td class="text_center">
    						<?php echo $this->currency->format($row->total ? $row->total : 0, EShopHelper::getConfigValue('default_currency_code')); ?>
    					</td>
    				</tr>
    				<?php
    				$k = 1 - $k;
    			}
    			?>
    		</tbody>
    	</table>
		<div class="clearfix"></div>
	</div>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="export_type" value="orders" />
	<input type="hidden" name="from_exports" value="1" />
</form>