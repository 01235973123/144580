<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 *
 * @var MPFConfig $config
 */

$tags = OSMembershipHelperHtml::getSupportedTags('invoice_format');
?>
<fieldset class="form-horizontal options-form  osm-mitem-form">
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('activate_invoice_feature', Text::_('OSM_ACTIVATE_INVOICE_FEATURE'), Text::_('OSM_ACTIVATE_INVOICE_FEATURE_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('activate_invoice_feature', $config->activate_invoice_feature); ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('generated_invoice_for_paid_subscription_only', Text::_('OSM_GENERATE_INVOICE_FOR_PAID_SUBSCRIPTION_ONLY'), Text::_('OSM_GENERATE_INVOICE_FOR_PAID_SUBSCRIPTION_ONLY_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('generated_invoice_for_paid_subscription_only', $config->generated_invoice_for_paid_subscription_only); ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('send_invoice_to_customer', Text::_('OSM_SEND_INVOICE_TO_SUBSCRIBERS'), Text::_('OSM_SEND_INVOICE_TO_SUBSCRIBERS_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('send_invoice_to_customer', $config->send_invoice_to_customer); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('send_invoice_to_admin', Text::_('OSM_SEND_COPY_OF_INVOICE_TO_ADMIN'), Text::_('OSM_SEND_COPY_OF_INVOICE_TO_ADMIN_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('send_invoice_to_admin', $config->send_invoice_to_admin); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('invoice_start_number', Text::_('OSM_INVOICE_START_NUMBER'), Text::_('OSM_INVOICE_START_NUMBER_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="number" name="invoice_start_number" class="form-control" value="<?php echo $this->config->invoice_start_number ?: 1; ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('reset_invoice_number', Text::_('OSM_RESET_INVOICE_NUMBER_EVERY_YEAR'), Text::_('OSM_RESET_INVOICE_NUMBER_EVERY_YEAR_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('reset_invoice_number', $config->reset_invoice_number); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('invoice_prefix', Text::_('OSM_INVOICE_PREFIX'), Text::_('OSM_INVOICE_PREFIX_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="invoice_prefix" class="form-control" value="<?php echo $this->config->invoice_prefix ?? 'IV'; ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('invoice_number_length', Text::_('OSM_INVOICE_NUMBER_LENGTH'), Text::_('OSM_INVOICE_NUMBER_LENGTH_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="number" name="invoice_number_length" class="form-control" value="<?php echo $this->config->invoice_number_length ?: 5; ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('invoice_format', Text::_('OSM_INVOICE_FORMAT'), ''); ?>
            <p class="osm-available-tags">
				<?php echo Text::_('OSM_AVAILABLE_TAGS'); ?>:<br /> <strong><?php echo '[' . implode(']<br /> [', $tags) . ']'; ?></strong>
            </p>
		</div>
		<div class="controls">
			<?php echo $editor->display('invoice_format', $this->config->invoice_format, '100%', '550', '75', '8') ;?>
		</div>
	</div>
</fieldset>
