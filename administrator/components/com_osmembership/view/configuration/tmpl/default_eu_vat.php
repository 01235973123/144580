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
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('eu_vat_number_field', Text::_('OSM_EU_VAT_NUMBER_FIELD'), Text::_('OSM_EU_VAT_NUMBER_FIELD_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['eu_vat_number_field']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('hide_vat_field_for_home_country', Text::_('OSM_HIDE_VAT_NUMBER_FIELD_FOR_HOME_COUNTRY'), Text::_('OSM_HIDE_VAT_NUMBER_FIELD_FOR_HOME_COUNTRY_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('hide_vat_field_for_home_country', $config->get('hide_vat_field_for_home_country')); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('block_subscription_if_eu_vat_number_is_invalid', Text::_('OSM_BLOCK_SUBSCRIPTION_IF_EU_VAT_NUMBER_IS_INVALID'), Text::_('OSM_BLOCK_SUBSCRIPTION_IF_EU_VAT_NUMBER_IS_INVALID_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('block_subscription_if_eu_vat_number_is_invalid', $config->get('block_subscription_if_eu_vat_number_is_invalid')); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('vat_number_validation_provider', Text::_('OSM_EU_VAT_NUMBER_VALIDATION_PROVIDER'), Text::_('OSM_EU_VAT_NUMBER_VALIDATION_PROVIDER_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['vat_number_validation_provider']; ?>
	</div>
</div>