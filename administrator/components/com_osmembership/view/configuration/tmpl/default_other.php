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
?>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('OSM_OTHER_SETTINGS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('show_incomplete_payment_subscriptions', Text::_('OSM_SHOW_INCOMPLETE_PAYMENT_SUBSCRIPTIONS'), Text::_('OSM_SHOW_INCOMPLETE_PAYMENT_SUBSCRIPTIONS_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('show_incomplete_payment_subscriptions', $config->get('show_incomplete_payment_subscriptions', 1)); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('send_attachments_to_admin', Text::_('OSM_SEND_ATTACHMENTS_TO_ADMIN'), Text::_('OSM_SEND_ATTACHMENTS_TO_ADMIN_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('send_attachments_to_admin', $config->send_attachments_to_admin); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('use_https', Text::_('OSM_ACTIVATE_HTTPS'), ''); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('use_https', $config->use_https); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('country_list', Text::_('OSM_DEFAULT_COUNTRY'), ''); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['country_list']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('article_id', Text::_('OSM_TERMS_AND_CONDITIONS_ARTICLE'), ''); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getArticleInput($this->config->article_id, 'article_id'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('allowed_file_types', Text::_('OSM_ALLOWED_FILE_TYPES'), Text::_('OSM_ALLOWED_FILE_TYPES_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="allowed_file_types" class="form-control" value="<?php echo $this->config->allowed_file_types; ?>" size="40" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('upload_max_file_size', Text::_('OSM_UPLOAD_MAX_FILE_SIZE'), Text::_('OSM_UPLOAD_MAX_FILE_SIZE_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="upload_max_file_size" class="form-control" value="<?php echo $this->config->upload_max_file_size; ?>" size="40" /> MB
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('checkout_tracking_code', Text::_('OSM_CHECKOUT_TRACKING_CODE'), Text::_('OSM_CHECKOUT_TRACKING_CODE_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<textarea name="checkout_tracking_code" class="form-control" rows="10"><?php echo $this->config->checkout_tracking_code;?></textarea>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('conversion_tracking_code', Text::_('OSM_CONVERSION_TRACKING_CODE'), Text::_('OSM_CONVERSION_TRACKING_CODE_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<textarea name="conversion_tracking_code" class="form-control" rows="10"><?php echo $this->config->conversion_tracking_code;?></textarea>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('export_data_format', Text::_('OSM_EXPORT_DATA_FORMAT')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['export_data_format']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('qrcode_size', Text::_('OSM_QRCODE_SIZE')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['qrcode_size']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('open_exchange_rates_app_id', Text::_('OSM_OPEN_EXCHANGE_RATE_APP_ID')); ?>
		</div>
		<div class="controls">
			<input type="text" name="open_exchange_rates_app_id" class="form-control" value="<?php echo $config->open_exchange_rates_app_id ?>" size="60" />
			<p class="info" style="margin-top: 10px;">
				<?php echo Text::_('OSM_OPEN_EXCHANGE_RATE_APP_ID_EXPLAIN'); ?>
			</p>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('activate_simple_multilingual', Text::_('OSM_ACTIVATE_SIMPLE_MULTILINGUAL'), Text::_('OSM_ACTIVATE_SIMPLE_MULTILINGUAL_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('activate_simple_multilingual', $config->activate_simple_multilingual); ?>
		</div>
	</div>
	<?php
	if (version_compare(PHP_VERSION, '7.4.0', '<'))
	{
	?>
		<div class="control-group">
			<div class="control-label">
				<?php echo OSMembershipHelperHtml::getFieldLabel('display_php_version_warning', Text::_('Display PHP Version Warning')); ?>
			</div>
			<div class="controls">
				<?php echo OSMembershipHelperHtml::getBooleanInput('display_php_version_warning', $config->get('display_php_version_warning', 1)); ?>
			</div>
		</div>
	<?php
	}
	?>
</fieldset>
