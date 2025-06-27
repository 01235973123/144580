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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('ESHOP_TOOLS'), 'generic.png');
?>
<script type="text/javascript">
	function confirmation(message, destnUrl) {
		var answer = confirm(message);
		if (answer) {
			window.location = destnUrl;
		}
	}
</script>
<div class="clearfix">
	<div style="width: 100%; float: left;">
		<div class="bs-example bs-shop-tools">
			<table class="table dashboard-table">
				<tbody>
					<tr>
						<td width="20%">
							<div id="cpanel">
								<?php $this->quickiconButton('index.php?option=com_eshop&task=tools.migrateFromJoomla', 'icon-48-tools-migrate-from-joomla.png', Text::_('ESHOP_MIGRATE_FROM_JOOMLA'), Text::_('ESHOP_MIGRATE_FROM_JOOMLA_CONFIRM')); ?>
							</div>
						</td>
						<td width="20%">
							<div id="cpanel">
								<?php $this->quickiconButton('index.php?option=com_eshop&task=tools.migrateFromMembershipPro', 'icon-48-tools-migrate-from-membership.png', Text::_('ESHOP_MIGRATE_FROM_MEMBERSHIP'), Text::_('ESHOP_MIGRATE_FROM_MEMBERSHIP_CONFIRM')); ?>
							</div>
						</td>
						<td width="20%">
							<div id="cpanel">
								<?php $this->quickiconButton('index.php?option=com_eshop&task=tools.cleanData', 'icon-48-tools-clean-data.png', Text::_('ESHOP_CLEAN_DATA'), Text::_('ESHOP_CLEAN_DATA_CONFIRM')); ?>
							</div>
						</td>
						<td width="20%">
							<div id="cpanel">
								<?php $this->quickiconButton('index.php?option=com_eshop&task=tools.addSampleData', 'icon-48-install.png', Text::_('ESHOP_ADD_SAMPLE_DATA'), Text::_('ESHOP_ADD_SAMPLE_DATA_CONFIRM')); ?>
							</div>
						</td>
						<td width="20%">
							<div id="cpanel">
								<?php $this->quickiconButton('index.php?option=com_eshop&task=tools.cleanOrders', 'icon-48-tools-clean-orders.png', Text::_('ESHOP_CLEAN_ORDERS'), Text::_('ESHOP_CLEAN_ORDERS_CONFIRM')); ?>
							</div>
						</td>
					</tr>
					<tr>
						<td width="20%">
							<?php echo Text::_('ESHOP_MIGRATE_FROM_JOOMLA_HELP'); ?>
						</td>
						<td width="20%">
							<?php echo Text::_('ESHOP_MIGRATE_FROM_MEMBERSHIP_HELP'); ?>
						</td>
						<td width="20%">
							<?php echo Text::_('ESHOP_CLEAN_DATA_HELP'); ?>
						</td>
						<td width="20%">
							<?php echo Text::_('ESHOP_ADD_SAMPLE_DATA_HELP'); ?>
						</td>
						<td width="20%">
							<div id="cpanel">
								<?php echo Text::_('ESHOP_CLEAN_ORDERS_HELP'); ?>
							</div>
						</td>
					</tr>
					<tr>
						<td width="20%">
							<div id="cpanel">
								<?php $this->quickiconButton('index.php?option=com_eshop&task=tools.synchronizeData', 'icon-48-tools-synchronize-data.png', Text::_('ESHOP_SYNCHRONIZE_DATA'), Text::_('ESHOP_SYNCHRONIZE_DATA_CONFIRM')); ?>
							</div>
						</td>
						<td width="20%">
							<div id="cpanel">
								<?php $this->quickiconButton('index.php?option=com_eshop&task=tools.migrateVirtuemart', 'icon-48-tools-migrate_virtuemart.png', Text::_('ESHOP_MIGRATE_VIRTUEMART'), Text::_('ESHOP_MIGRATE_VIRTUEMART_CONFIRM')); ?>
							</div>
						</td>
						<td width="20%">
							<div id="cpanel">
								<?php $this->quickiconButton('index.php?option=com_eshop&task=tools.resetHits', 'icon-48-tools-reset-hits.png', Text::_('ESHOP_RESET_HITS'), Text::_('ESHOP_RESET_HITS_CONFIRM')); ?>
							</div>
						</td>
						<td width="20%">
							<div id="cpanel">
								<?php $this->quickiconButton('index.php?option=com_eshop&task=tools.purgeUrls', 'icon-48-tools-purge-urls.png', Text::_('ESHOP_PURGE_URLS'), Text::_('ESHOP_PURGE_URLS_CONFIRM')); ?>
							</div>
						</td>
						<td width="20%">
							<div id="cpanel">
								<?php $this->quickiconButton('index.php?option=com_eshop&task=tools.migrateJ2store', 'icon-48-tools-migrate-j2store.png', Text::_('ESHOP_MIGRATE_J2STORE'), Text::_('ESHOP_MIGRATE_J2STORE_CONFIRM')); ?>
							</div>
						</td>
					</tr>
					<tr>
						<td width="20%">
							<?php echo Text::_('ESHOP_SYNCHRONIZE_DATA_HELP'); ?>
						</td>
						<td width="20%">
							<?php echo Text::_('ESHOP_MIGRATE_VIRTUEMART_HELP'); ?>
						</td>
						<td width="20%">
							<?php echo Text::_('ESHOP_RESET_HITS_HELP'); ?>
						</td>
						<td width="20%">
							<?php echo Text::_('ESHOP_PURGE_URLS_HELP'); ?>
						</td>
						<td width="20%">
							<?php echo Text::_('ESHOP_MIGRATE_J2STORE_HELP'); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>