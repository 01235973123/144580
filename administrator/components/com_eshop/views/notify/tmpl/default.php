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
use Joomla\CMS\Router\Route;

$ordering = ($this->lists['order'] == 'a.ordering');

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);
?>
<form action="index.php?option=com_eshop&view=notify" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
    	<div id="filter-bar" class="btn-toolbar<?php if (EShopHelper::isJoomla4()) echo ' js-stools-container-filters-visible'; ?>">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('ESHOP_FILTER_SEARCH_ATTRIBUTE_GROUPS_DESC');?></label>
				<input type="text" name="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('ESHOP_SEARCH_ATTRIBUTE_GROUPS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn<?php if (EShopHelper::isJoomla4()) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn<?php if (EShopHelper::isJoomla4()) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th width="2%" class="text_center">
						<?php echo Text::_( 'ESHOP_NUM' ); ?>
					</th>
					<th class="text_left" width="40%">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_NAME'), 'b.product_name', $this->lists['order_Dir'], $this->lists['order'] ); ?>				
					</th>											
					<th width="20%" class="text_left">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_EMAIL'), 'a.notify_email', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="10%" class="text_center">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_NOTIFY_SENT'), 'a.sent_email', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="10%" class="text_center">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_NOTIFY_SENT_DATE'), 'a.sent_date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="5%" class="text_center">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ID'), 'a.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>													
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
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
				$link 	= Route::_( 'index.php?option=com_eshop&task=product.edit&cid[]='. $row->product_id);
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="text_center">
						<?php echo $this->pagination->getRowOffset( $i ); ?>
					</td>
					<td>																			
						<a href="<?php echo $link; ?>"><?php echo $row->product_name; ?></a>				
					</td>			
					<td class="text_left">
						<a href="mailto:<?php echo $row->notify_email; ?>"><?php echo $row->notify_email; ?></a>
					</td>
					<td class="text_center" >
					   <?php echo $row->sent_email? '<span class="icon-publish"></span>':'<span class="icon-unpublish"></span>'; ?>
					</td>
					<td class="text_center">
						<?php echo $row->sent_email ? HTMLHelper::_('date', $row->sent_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'), null) : ''; ?>
					</td>
					<td class="text_center" width="5%">
						<?php echo $row->id; ?>
					</td>
				</tr>		
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
    </div>	
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />	
	<?php echo HTMLHelper::_( 'form.token' ); ?>			
</form>