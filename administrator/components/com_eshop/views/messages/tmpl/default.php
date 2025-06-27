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
<form action="index.php?option=com_eshop&view=messages" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th width="2%" class="text_center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th class="text_left" width="40%">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_TITLE'), 'message_title', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>	
					<th class="text_left" width="40%">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_NAME'), 'message_name', $this->lists['order_Dir'], $this->lists['order'] ); ?>				
					</th>
					<th width="5%" class="text_center">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ID'), 'id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="4">
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
				$link 	= Route::_( 'index.php?option=com_eshop&task=message.edit&cid[]='. $row->id);
				$checked 	= HTMLHelper::_('grid.id',   $i, $row->id );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="text_center">
						<?php echo $checked; ?>
					</td>	
					<td>
						<a href="<?php echo $link; ?>"><?php echo $row->message_title; ?></a>				
					</td>
					<td>
						<?php echo $row->message_name; ?>
					</td>
					<td class="text_center">
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
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />	
	<?php echo HTMLHelper::_( 'form.token' ); ?>			
</form>