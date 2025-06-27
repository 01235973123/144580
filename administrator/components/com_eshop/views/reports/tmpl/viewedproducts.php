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

ToolbarHelper::title(Text::_('ESHOP_VIEWED_PRODUCTS_REPORT'), 'generic.png');
ToolbarHelper::cancel('reports.cancel');

$isJoomla4 = EshopHelper::isJoomla4();

if ($isJoomla4)
{
	Factory::getApplication()->getDocument()
		->getWebAssetManager()
		->useScript('table.columns')
		->useScript('multiselect');
}
?>
<form action="index.php?option=com_eshop&view=reports&layout=viewedproducts" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th class="text_center" with="5%">#</th>
				<th class="text_left" width="50%">
					<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>
				</th>
				<th class="text_center" width="15%">
					<?php echo Text::_('ESHOP_PRODUCT_SKU'); ?>
				</th>
				<th class="text_center" width="15%">
					<?php echo Text::_('ESHOP_PRODUCT_VIEWED'); ?>
				</th>
				<th class="text_center" width="15%">
					<?php echo Text::_('ESHOP_PRODUCT_PERCENT'); ?>
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
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="text_center"><?php echo ($i + 1); ?></td>
					<td class="text_left">																			
						<?php echo $row->product_name; ?>				
					</td>																			
					<td class="text_center">
						<?php echo $row->product_sku; ?>
					</td>
					<td class="text_center">
						<?php echo $row->hits; ?>
					</td>
					<td class="text_center">
						<?php echo $this->totalHits ? number_format($row->hits * 100 / $this->totalHits, 2) : '0.00'; ?>%
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
		</tbody>
	</table>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="task" value="" />
</form>