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
<form action="index.php?option=com_eshop&view=questions" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar<?php if ($isJoomla4) echo ' js-stools-container-filters-visible'; ?>">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('ESHOP_FILTER_SEARCH_QUESTIONS_DESC');?></label>
				<input type="text" name="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('ESHOP_SEARCH_QUESTIONS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<?php
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
    					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_CUSTOMER'), 'a.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
    				</th>
    				<th class="text_left" width="15%">
    					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_EMAIL'), 'a.email', $this->lists['order_Dir'], $this->lists['order'] ); ?>
    				</th>
    				<th class="text_left" width="15%">
    					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_COMPANY'), 'a.company', $this->lists['order_Dir'], $this->lists['order'] ); ?>
    				</th>
    				<th class="text_left" width="15%">
    					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_TELEPHONE'), 'a.phone', $this->lists['order_Dir'], $this->lists['order'] ); ?>
    				</th>
    				<th class="text_center" width="10%">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_CREATED_DATE'), 'a.created_date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th class="text_left" width="15%">
    					<?php echo Text::_('ESHOP_PRODUCT'); ?>
    				</th>
					<th class="text_center" width="10%">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_MESSAGE'), 'a.message', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="5%" class="text_center">
						<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ID'), 'a.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="9">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			$dateFormat = EShopHelper::getConfigValue('date_format', 'm-d-Y');
			
			for ($i = 0, $n = count( $this->items ); $i < $n; $i++)
			{
				$row = &$this->items[$i];
				$link 	= Route::_('index.php?option=com_eshop&task=quote.edit&cid[]='. $row->id);
				$checked 	= HTMLHelper::_('grid.id',   $i, $row->id );
				$product = EshopHelper::getProduct($row->product_id);
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="text_center">
						<?php echo $checked; ?>
					</td>
					<td class="text_left"><?php echo $row->name; ?></td>
					<td class="text_left">
				    	<a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a>
				    </td>
				    <td class="text_left">
				    	<?php echo $row->company; ?>
				    </td>
				    <td class="text_left"><?php echo $row->phone; ?></td>
				    <td class="text_center">
						<?php
						if ($row->created_date != $this->nullDate)
						{
							echo HTMLHelper::_('date', $row->created_date, $dateFormat, null);
						}
						?>
					</td>
					<td class="text_left"><?php echo $product->product_name; ?></td>
					<td class="text_center">
						<?php
						echo $row->message;
						?>
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