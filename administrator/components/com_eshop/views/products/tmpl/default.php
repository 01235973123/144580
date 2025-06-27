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
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

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
	$saveOrderingUrl = 'index.php?option=com_eshop&task=product.save_order_ajax';
	
	if ($isJoomla4)
	{
	    HTMLHelper::_('draggablelist.draggable');
	}
	else
	{
	    HTMLHelper::_('sortablelist.sortable', 'recordsList', 'adminForm', strtolower($this->lists['order_Dir']), $saveOrderingUrl, false, true);
	}
}

$customOptions = array(
	'filtersHidden'       => true,
	'defaultLimit'        => Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#filter_full_ordering'
);
HTMLHelper::_('searchtools.form', '#adminForm', $customOptions);
?>
<form action="index.php?option=com_eshop&view=products" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar<?php if ($isJoomla4) echo ' js-stools-container-filters-visible'; ?>">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('ESHOP_FILTER_SEARCH_PRODUCTS_DESC');?></label>
				<input type="text" name="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('ESHOP_SEARCH_PRODUCTS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<?php
					echo $this->lists['category_id'];
					echo $this->lists['mf_id'];
					echo $this->lists['filter_state'];
					echo $this->lists['stock_status'];
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
				<th class="text_center" width="2%">
					<?php echo HTMLHelper::_('grid.checkall'); ?>
				</th>
				<?php
				$colspan = 11;
				
				if (!$isJoomla4)
				{
				    $colspan = 13;
				    ?>
				    <th width="1%" class="text_center" style="min-width:55px">
    					<?php echo HTMLHelper::_('searchtools.sort', Text::_('JSTATUS'), 'a.published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
    				</th>
				    <?php
				}
				?>
				<th class="text_left" width="15%">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('ESHOP_NAME'), 'b.product_name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="text_center" width="5%">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('ESHOP_PRODUCT_SKU'), 'a.product_sku', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="text_center" width="2%">
					<?php echo Text::_('ESHOP_IMAGE'); ?>
				</th>
				<th class="text_center" width="10%">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('ESHOP_PRODUCT_PRICE'), 'a.product_price', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="text_center" width="10%">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('ESHOP_PRODUCT_QUANTITY'), 'a.product_quantity', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="text_center" width="18%">
					<?php echo Text::_('ESHOP_CATEGORY'); ?>
				</th>
				<th class="text_center" width="10%">
					<?php echo Text::_('ESHOP_MANUFACTURER'); ?>
				</th>
				<?php
				if (!$isJoomla4)
				{
				    ?>
				    <th class="text_center" width="5%">
    					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('ESHOP_FEATURED'), 'a.product_featured', $this->lists['order_Dir'], $this->lists['order'] ); ?>
    				</th>
				    <?php
				}
				?>
				<th class="text_center" width="5%">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('ESHOP_PUBLISHED'), 'a.published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="text_center" width="4%">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('ESHOP_ID'), 'a.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
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
			<tbody <?php if ($ordering) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->lists['order_Dir']); ?>" data-nested="false"<?php endif; ?>>
			<?php
			$k = 0;
			$rootUri = Uri::root();
			
			for ($i = 0, $n = count( $this->items ); $i < $n; $i++)
			{
				$row = &$this->items[$i];
				$link 	= Route::_( 'index.php?option=com_eshop&task=product.edit&cid[]='. $row->id);
				$published = HTMLHelper::_('jgrid.published', $row->published, $i, 'product.');
				$productPreviewUrl = EShopHelper::getSiteUrl() . EShopRoute::getProductRoute($row->id, EShopHelper::getProductCategory($row->id));
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
					<td class="text_center">
						<?php echo HTMLHelper::_('grid.id',   $i, $row->id ); ?>
					</td>
					<?php
					if (!$isJoomla4)
					{
					    ?>
					    <td class="text_center">
    						<div class="btn-group">
    							<?php
    							echo $published;
    							echo $this->featured($row->product_featured, $i);
    							echo $this->addDropdownList(Text::_('ESHOP_COPY'), 'copy', $i, 'product.copy');
    							echo $this->addDropdownList(Text::_('ESHOP_DELETE'), 'trash', $i, 'product.remove');
    							echo $this->renderDropdownList($this->escape($row->product_name));
    							?>
    						</div>
    					</td>
					    <?php
					}
					?>
					<td class="text_left">
						<a href="<?php echo $link; ?>"><?php echo $row->product_name; ?></a> - [<a href="<?php echo $productPreviewUrl; ?>" target="_blank"><?php echo Text::_('ESHOP_PRODUCT_PREVIEW'); ?></a>]
					</td>
					<td class="text_center">
						<?php echo $row->product_sku; ?>
					</td>
					<td class="text_center">
						<?php
						if (is_file(JPATH_ROOT.'/media/com_eshop/products/'.$row->product_image))
						{
							$viewImage = File::stripExt($row->product_image).'-100x100.'.EShopHelper::getFileExt($row->product_image);

							if (is_file(JPATH_ROOT.'/media/com_eshop/products/resized/'.$viewImage))
							{
							?>
								<img src="<?php echo $rootUri . 'media/com_eshop/products/resized/' . $viewImage; ?>" width="50" />
							<?php
							}
							else
							{
							?>
								<img src="<?php echo $rootUri . 'media/com_eshop/products/' . $row->product_image; ?>" width="50" />
							<?php
							}
						}
						?>
					</td>
					<td class="text_center">
						<?php
						$productPriceArray = EShopHelper::getProductPriceArray($row->id, $row->product_price);

						if ($productPriceArray['salePrice'] >= 0)
						{
						?>
							<span class="base-price"><?php echo $this->currency->format($productPriceArray['basePrice'], EShopHelper::getConfigValue('default_currency_code')); ?></span>&nbsp;
							<span class="sale-price"><?php echo $this->currency->format($productPriceArray['salePrice'], EShopHelper::getConfigValue('default_currency_code')); ?></span>
						<?php
						}
						else
						{
						?>
							<span class="price"><?php echo $this->currency->format($productPriceArray['basePrice'], EShopHelper::getConfigValue('default_currency_code')); ?></span>
						<?php
						}
						?>
					</td>
					<td class="text_center">
						<?php echo $row->product_quantity; ?>
					</td>
					<td class="text_center">
						<?php
						$categories = EShopHelper::getProductCategories($row->id);

						for ($j = 0; $m = count($categories), $j < $m; $j++)
						{
							$category = $categories[$j];
							$editCategoryLink = Route::_( 'index.php?option=com_eshop&task=category.edit&cid[]='. $category->id);
							$dividedChar = ($j < ($m - 1)) ? ' | ' : '';
							?>
							<a href='<?php echo $editCategoryLink; ?>'><?php echo $category->category_name; ?></a><?php echo $dividedChar; ?>
							<?php
						}
						?>
					</td>
					<td class="text_center">
						<?php
						$manufacturer = EShopHelper::getProductManufacturer($row->id);

						if (is_object($manufacturer))
						{
							$editManufacturerLink = Route::_( 'index.php?option=com_eshop&task=manufacturer.edit&cid[]='. $manufacturer->id);
							?>
							<a href='<?php echo $editManufacturerLink; ?>'><?php echo $manufacturer->manufacturer_name; ?></a>
							<?php
						}
						?>
					</td>
					<?php
					if (!$isJoomla4)
					{
					    ?>
					    <td class="text_center">
    						<?php echo $this->featured($row->product_featured, $i); ?>
    					</td>
					    <?php
					}
					?>
					<td class="text_center">
						<?php echo $published; ?>
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
		<div class="clearfix"></div>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<input type="hidden" id="filter_full_ordering" name="filter_full_ordering" value="" />
	<?php
		// Load the batch processing form
		echo HTMLHelper::_(
			'bootstrap.renderModal',
			'collapseModal',
			array(
				'title' => Text::_('ESHOP_PRODUCTS_BATCH_OPTIONS'),
				'footer' => $this->loadTemplate('batch_footer')
			),
			$this->loadTemplate('batch_body')
		);
		
		// Load the batch processing form
		echo HTMLHelper::_(
		    'bootstrap.renderModal',
		    'inventoryModal',
		    array(
		        'title' => Text::_('ESHOP_MANAGE_INVENTORY'),
		        'footer' => $this->loadTemplate('inventory_footer')
		    ),
		    $this->loadTemplate('inventory_body')
        );

		echo HTMLHelper::_( 'form.token' );
	?>
</form>