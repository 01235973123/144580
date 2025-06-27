<?php
/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2020 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 *
 * @var MPFConfig $config
 */
?>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_id', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_ID'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_id', $config->get('export_id', 1)); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_category', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_CATEGORY'))); ?>
	</div>
	<div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_category', $config->get('export_category', 0)); ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_plan', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_PLAN'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_plan', $config->get('export_plan', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_user_id', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_USER_ID'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_user_id', $config->get('export_user_id', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_username', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_USERNAME'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_username', $config->get('export_username', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_created_date', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_CREATED_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_created_date', $config->get('export_created_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('export_payment_date', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_PAYMENT_DATE'))); ?>
    </div>
    <div class="controls">
        <?php echo OSMembershipHelperHtml::getBooleanInput('export_payment_date', $config->get('export_payment_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_from_date', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_SUBSCRIPTION_START_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_from_date', $config->get('export_from_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_to_date', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_SUBSCRIPTION_END_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_to_date', $config->get('export_to_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_published', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_PUBLISHED'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_published', $config->get('export_published', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_amount', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_NET_AMOUNT'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_amount', $config->get('export_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('export_discount_amount', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_DISCOUNT_AMOUNT'))); ?>
    </div>
    <div class="controls">
        <?php echo OSMembershipHelperHtml::getBooleanInput('export_discount_amount', $config->get('export_discount_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('export_tax_amount', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_TAX_AMOUNT'))); ?>
    </div>
    <div class="controls">
        <?php echo OSMembershipHelperHtml::getBooleanInput('export_tax_amount', $config->get('export_tax_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('export_gross_amount', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_GROSS_AMOUNT'))); ?>
    </div>
    <div class="controls">
        <?php echo OSMembershipHelperHtml::getBooleanInput('export_gross_amount', $config->get('export_gross_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_payment_method', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_PAYMENT_METHOD'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_payment_method', $config->get('export_payment_method', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_transaction_id', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_TRANSACTION_ID'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_transaction_id', $config->get('export_transaction_id', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_membership_id', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_MEMBERSHIP_ID'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_membership_id', $config->get('export_membership_id', 1)); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_subscription_type', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_SUBSCRIPTION_TYPE'))); ?>
	</div>
	<div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_subscription_type', $config->get('export_subscription_type', 1)); ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_invoice_number', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_INVOICE_NUMBER'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_invoice_number', $config->get('export_invoice_number', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_coupon', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_COUPON'))); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_coupon', $config->get('export_coupon', 1)); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('export_subscribe_to_newsletter', Text::sprintf('OSM_EXPORT_FIELD', Text::_('OSM_SUBSCRIBE_TO_NEWSLETTER'))); ?>
	</div>
	<div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('export_subscribe_to_newsletter', $config->get('export_subscribe_to_newsletter', 0)); ?>
	</div>
</div>