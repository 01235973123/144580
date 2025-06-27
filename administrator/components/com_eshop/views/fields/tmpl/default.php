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

$ordering = ($this->lists['order'] == 'a.ordering');

if ($ordering)
{
    $saveOrderingUrl = 'index.php?option=com_eshop&task=field.save_order_ajax';

    if ($isJoomla4)
    {
        HTMLHelper::_('draggablelist.draggable');
    }
    else
    {
        HTMLHelper::_('sortablelist.sortable', 'recordsList', 'adminForm', strtolower($this->lists['order_Dir']), $saveOrderingUrl, false, true);
    }
}

$addressTypes = array(
	'A' => Text::_('ESHOP_ALL'),
	'B' => Text::_('ESHOP_BILLING_ADDRESS'),
	'S' => Text::_('ESHOP_SHIPPING_ADDRESS')
);

$customOptions = array(
	'filtersHidden'       => true,
	'defaultLimit'        => Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#filter_full_ordering'
);
HTMLHelper::_('searchtools.form', '#adminForm', $customOptions);
?>
<form action="index.php?option=com_eshop&view=fields" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar<?php if ($isJoomla4) echo ' js-stools-container-filters-visible'; ?>">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('ESHOP_FILTER_SEARCH_FIELDS_DESC');?></label>
				<input type="text" name="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('ESHOP_SEARCH_FIELDS_DESC'); ?>" />
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
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $this->lists['order_Dir'], $this->lists['order'], null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="2%" class="center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th class="text_left">
						<?php echo HTMLHelper::_('searchtools.sort',  'ESHOP_NAME', 'a.name', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th class="text_left">
						<?php echo HTMLHelper::_('searchtools.sort',  'ESHOP_TITLE', 'b.title', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th class="text_center">
						<?php echo HTMLHelper::_('searchtools.sort',  'ESHOP_FIELD_TYPE', 'a.fieldtype', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>			
					<th class="text_center">
						<?php echo HTMLHelper::_('searchtools.sort',  'ESHOP_ADDRESS_TYPE', 'a.is_core', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th class="text_center">
						<?php echo HTMLHelper::_('searchtools.sort',  'ESHOP_REQUIRED', 'a.required', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th class="text_center">
						<?php echo HTMLHelper::_('searchtools.sort',  'ESHOP_PUBLISHED', 'a.published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="5%" class="text_center">
						<?php echo HTMLHelper::_('searchtools.sort',  'ID', 'a.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
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
			<tbody <?php if ($ordering) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->lists['order_Dir']); ?>" data-nested="false"<?php endif; ?>>
			<?php
			$k = 0;
			$ordering = ($this->lists['order'] == 'a.ordering');

			for ($i = 0, $n = count( $this->items ); $i < $n; $i++)
			{
				$row = $this->items[$i];
				$link 	= Route::_( 'index.php?option=com_eshop&task=field.edit&cid[]='. $row->id );
				$checked 	= HTMLHelper::_('grid.id',   $i, $row->id );
				$published = HTMLHelper::_('jgrid.published', $row->published, $i, 'field.');
		        
		        $bootstrapHelper = EShopHtmlHelper::getAdminBootstrapHelper();
		        $iconPublish = $bootstrapHelper->getClassMapping('icon-publish');
		        $iconUnPublish = $bootstrapHelper->getClassMapping('icon-unpublish');
				?>
				<tr class="<?php echo "row$k"; ?>" data-draggable-group="none">
					<td class="order nowrap center hidden-phone">
						<?php
						$iconClass = '';

						if (!$ordering)
						{
							$iconClass = ' inactive tip-top hasTooltip';
						}
						?>
						<span class="sortable-handler<?php echo $iconClass ?>">
						<i class="icon-menu"></i>
						</span>
						<?php if ($ordering) : ?>
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->ordering ? $row->ordering : '0'; ?>" class="width-20 text-area-order "/>
						<?php endif; ?>
					</td>
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->name; ?>
						</a>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->title; ?>
						</a>
					</td>
					<td class="center">
						<?php
							echo $row->fieldtype;
					 	?>
					</td>
					<td class="center">
						<?php echo $addressTypes[$row->address_type]; ?>
					</td>
					<td class="center">
                        <a class="tbody-icon"><span class="<?php echo $row->required ? $iconPublish : $iconUnPublish; ?>"></span></a>
					</td>
					<td class="center">
						<?php echo $published; ?>
					</td>
					<td class="center">			
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
	<input type="hidden" id="filter_full_ordering" name="filter_full_ordering" value="" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>