<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core');

$this->loadDraggableLib('exporttmplList');
$this->loadSearchTools();

$saveOrder       = $this->state->filter_order === 'tbl.ordering';
$saveOrderingUrl = 'index.php?option=com_osmembership&task=exporttmpl.save_order_ajax';
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="eb-joomla4-container">
        <div id="filter-bar" class="btn-toolbar js-stools js-stools-container-filters-visible">
            <div class="filter-search btn-group pull-left">
                <label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_EXPORTTMPLS_DESC');?></label>
                <input type="text" name="filter_search" id="filter_search" inputmode="search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_EXPORTTMPLS_DESC'); ?>" />
            </div>
            <div class="btn-group pull-left">
                <button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
                <button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
            </div>
        </div>
        <div class="clearfix"></div>
        <table class="adminlist table table-striped" id="exporttmplList">
            <thead>
            <tr>
                <th width="1%" class="nowrap center hidden-phone">
					<?php echo $this->searchToolsSortHeader(); ?>
                </th>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
                </th>
                <th class="title">
	                <?php echo $this->searchToolsSort('OSM_NAME', 'tbl.title'); ?>
                </th>
                <th class="center">
	                <?php echo $this->searchToolsSort('OSM_PUBLISHED', 'tbl.published'); ?>
                </th>
                <th class="center" width="5%">
	                <?php echo $this->searchToolsSort('OSM_ID', 'tbl.id'); ?>
                </th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="7">
					<?php echo $this->pagination->getPaginationLinks(); ?>
                </td>
            </tr>
            </tfoot>
            <tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->state->filter_order_Dir); ?>" <?php endif; ?>>
			<?php
			$k = 0;

			for ($i = 0, $n = count($this->items); $i < $n; $i++)
			{
				$row       = $this->items[$i];
				$link      = $this->getEditItemLink($row);
				$checked   = HTMLHelper::_('grid.id', $i, $row->id);
				$published = HTMLHelper::_('jgrid.published', $row->published, $i);
				?>
                <tr class="<?php echo "row$k"; ?>">
                    <td class="order nowrap center hidden-phone">
	                    <?php $this->reOrderCell($row); ?>
                    </td>
                    <td>
						<?php echo $checked; ?>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
							<?php echo $row->title; ?>
                        </a>
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
    <input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>