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
<form action="index.php?option=com_eshop&view=countries" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar<?php if ($isJoomla4) echo ' js-stools-container-filters-visible'; ?>">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('ESHOP_FILTER_SEARCH_COUNTRIES_DESC');?></label>
				<input type="text" name="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('ESHOP_SEARCH_COUNTRIES_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<?php
					echo $this->lists['filter_state'];
					echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped" id="recordsList">
			<thead>
			<tr>
				<th width="2%" class="text_center">
					<?php echo HTMLHelper::_('grid.checkall'); ?>
				</th>
				<?php
				$colspan = 6;
				
				if (!$isJoomla4)
				{
				    $colspan = 7;
				    ?>
				    <th width="1%" class="text_center" style="min-width:55px">
    					<?php echo HTMLHelper::_('grid.sort', Text::_('JSTATUS'), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
    				</th>
				    <?php
				}
				?>
				<th class="text_left" width="20%">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_NAME'), 'country_name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="text_center" width="10%">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ISO_CODE2'), 'iso_code_2', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="text_center" width="10%">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ISO_CODE3'), 'iso_code_3', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="10%" class="text_center">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_PUBLISHED'), 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="5%" class="text_center">
					<?php echo HTMLHelper::_('grid.sort',  Text::_('ESHOP_ID'), 'id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
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
				$link 	= Route::_( 'index.php?option=com_eshop&task=country.edit&cid[]='. $row->id);
				$checked 	= HTMLHelper::_('grid.id',   $i, $row->id );
				$published 	= HTMLHelper::_('jgrid.published', $row->published, $i, 'country.');
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="text_center">
						<?php echo $checked; ?>
					</td>
					<?php
					if (!$isJoomla4)
					{
					    ?>
					    <td class="text_center">
    						<div class="btn-group">
    							<?php
    							echo $published;
    							echo $this->addDropdownList(Text::_('ESHOP_COPY'), 'copy', $i, 'country.copy');
    							echo $this->addDropdownList(Text::_('ESHOP_DELETE'), 'trash', $i, 'country.remove');
    							echo $this->renderDropdownList($this->escape($row->country_name));
    							?>
    						</div>
    					</td>
					    <?php
					}
					?>
					<td>
						<a href="<?php echo $link; ?>"><?php echo $row->country_name; ?></a>
					</td>
					<td class="text_center">
						<?php echo $row->iso_code_2; ?>
					</td>
					<td class="text_center">
						<?php echo $row->iso_code_3; ?>
					</td>

					<td class="text_center">
						<?php echo $published; ?>
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
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>