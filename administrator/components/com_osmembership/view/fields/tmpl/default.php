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

Factory::getApplication()->getDocument()->getWebAssetManager()
	->useScript('table.columns')
	->useScript('multiselect');

$this->loadDraggableLib('fieldList');
$this->loadSearchTools();

$saveOrder       = $this->state->filter_order === 'tbl.ordering';
$saveOrderingUrl = 'index.php?option=com_osmembership&task=field.save_order_ajax';
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
        <?php echo $this->loadTemplate('filter'); ?>
		<div class="clearfix"> </div>
	    <table class="adminlist table table-striped" id="fieldList">
            <thead>
                <tr>
                    <th width="1%" class="nowrap center hidden-phone">
                        <?php echo $this->searchToolsSortHeader(); ?>
                    </th>
                    <th width="20">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>
                    <th class="title">
	                    <?php echo $this->searchToolsSort('OSM_NAME', 'tbl.name'); ?>
                    </th>
                    <th class="title">
	                    <?php echo $this->searchToolsSort('OSM_TITLE', 'tbl.title'); ?>
                    </th>
                    <th class="title">
	                    <?php echo $this->searchToolsSort('OSM_FIELD_TYPE', 'tbl.field_type'); ?>
                    </th>
                    <th class="title center">
	                    <?php echo $this->searchToolsSort('OSM_CORE_FIELD', 'tbl.is_core'); ?>
                    </th>
                    <th class="title center">
	                    <?php echo $this->searchToolsSort('OSM_PUBLISHED', 'tbl.published'); ?>
                    </th>
                    <th width="1%" nowrap="nowrap">
	                    <?php echo $this->searchToolsSort('OSM_ID', 'tbl.id'); ?>
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
            <tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->state->filter_order_Dir); ?>" <?php endif; ?>>
            <?php
			$k = 0;
			$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
			$iconPublish = $bootstrapHelper->getClassMapping('icon-publish');
			$iconUnPublish = $bootstrapHelper->getClassMapping('icon-unpublish');

            for ($i = 0, $n = count($this->items); $i < $n; $i++)
			{
				$row       = $this->items[$i];
				$link      = $this->getEditItemLink($row);
				$checked   = HTMLHelper::_('grid.id', $i, $row->id);
				$published = HTMLHelper::_('jgrid.published', $row->published, $i, 'field.');
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
                            <?php echo $row->name; ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $row->title; ?>
                        </a>
                    </td>
                    <td>
                        <?php
							echo $row->fieldtype;
						?>
                    </td>
                    <td class="center">
                        <a class="tbody-icon"><span class="<?php echo $row->is_core ? $iconPublish : $iconUnPublish; ?>"></span></a>
                    </td>
                    <td class="center">
                        <?php echo $published ; ?>
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
	<?php $this->renderFormHiddenVariables(); ?>
</form>