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

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core')
	->useScript('table.columns')
	->useScript('multiselect')
	->registerAndUseScript('com_osmembership.admin-plugins-default', 'media/com_osmembership/js/admin-plugins-default.min.js');

$this->loadDraggableLib('pluginList');
$this->loadSearchTools();

$saveOrder       = $this->state->filter_order === 'tbl.ordering';
$saveOrderingUrl = 'index.php?option=com_osmembership&task=plugin.save_order_ajax';

Text::script('OSM_CHOOSE_PLUGIN', true);
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm">
    <div id="j-main-container" class="mp-joomla4-container">
        <div id="filter-bar" class="btn-toolbar js-stools js-stools-container-filters-visible">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_PAYMENT_PLUGINS_DESC');?></label>
				<input type="text" name="filter_search" inputmode="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_PAYMENT_PLUGINS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right">
				<?php
					echo $this->lists['filter_state'];
					echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped" id="pluginList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo $this->searchToolsSortHeader(); ?>
					</th>
					<th width="2%" class="center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th class="title">
						<?php echo $this->searchToolsSort('OSM_NAME', 'tbl.name'); ?>
					</th>
					<th class="title" width="20%">
						<?php echo $this->searchToolsSort('OSM_TITLE', 'tbl.title'); ?>
					</th>
					<th class="title">
						<?php echo $this->searchToolsSort('OSM_AUTHOR', 'tbl.author'); ?>
					</th>
					<th class="title center">
						<?php echo $this->searchToolsSort('OSM_AUTHOR_EMAIL', 'tbl.email'); ?>
					</th>
					<th class="title center">
						<?php echo $this->searchToolsSort('OSM_RECURRING_SUPPORT', 'tbl.support_recurring_subscription'); ?>
					</th>
					<th class="title center">
						<?php echo $this->searchToolsSort('OSM_PUBLISHED', 'tbl.published'); ?>
					</th>
					<th>
						<?php echo $this->searchToolsSort('OSM_ID', 'tbl.id'); ?>
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
				$published = HTMLHelper::_('jgrid.published', $row->published, $i, 'plugin.');
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
						<?php echo $row->title; ?>
					</td>
					<td>
						<?php echo $row->author; ?>
					</td>
					<td class="center">
						<?php echo $row->author_email;?>
					</td>
					<td class="center">
                        <a class="tbody-icon"><span class="<?php echo $row->support_recurring_subscription ? $iconPublish : $iconUnPublish; ?>"></span></a>
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
		<table class="adminform" style="margin-top: 50px;">
			<tr>
				<td>
					<fieldset class="form-horizontal options-form">
						<legend><?php echo Text::_('OSM_INSTALL_NEW_PLUGIN'); ?></legend>
						<table>
							<tr>
								<td>
									<input type="file" name="plugin_package" id="plugin_package" size="40" class="form-control" /> <input id="btn-install-plugin" type="button" class="btn btn-primary" value="<?php echo Text::_('OSM_INSTALL'); ?>" />
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
	</div>
	<?php $this->renderFormHiddenVariables(); ?>
</form>