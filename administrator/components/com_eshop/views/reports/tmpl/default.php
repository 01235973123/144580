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

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);
HTMLHelper::_('behavior.modal');
?>
<form action="index.php?option=com_eshop&view=orders" method="post" name="adminForm" id="adminForm">
	<table width="100%">
		<tr>
			<td align="left">
				<?php echo Text::_( 'ESHOP_FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->state->search; ?>" class="hasTooltip form-control" onchange="document.adminForm.submit();" />		
				<button onclick="this.form.submit();" class="btn"><?php echo Text::_( 'ESHOP_GO' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();" class="btn"><?php echo Text::_( 'ESHOP_RESET' ); ?></button>		
			</td>
		</tr>
	</table>
	<div id="editcell">
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th width="2%">
						<?php echo Text::_( 'ESHOP_NUM' ); ?>
					</th>
					<th width="2%">
						<input type="checkbox" class="form-check-input" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th class="title" width="30%" style="text-align: left;">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_CUSTOMER'), 'a.firstname, a.lastname', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>	
					<th class="title" width="15%">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ORDER_STATUS'), 'status_name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th class="title" width="15%">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ORDER_TOTAL'), 'total', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th class="title" width="15%">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_CREATED_DATE'), 'a.created_date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th class="title" width="15%">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_MODIFIED_DATE'), 'a.modified_date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="10%">
						<?php echo Text::_('ESHOP_ACTION'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="8">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i = 0, $n = count( $this->items ); $i < $n; $i++)
			{
				?>
				<tr class="<?php echo "row$k"; ?>">
				
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