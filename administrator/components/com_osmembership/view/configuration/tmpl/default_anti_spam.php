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
		<?php echo OSMembershipHelperHtml::getFieldLabel('honeypot_fieldname', Text::_('OSM_HONEYPOT_FIELD_NAME'), Text::_('OSM_HONEYPOT_FIELD_NAME_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="honeypot_fieldname" class="form-control" value="<?php echo $config->get('honeypot_fieldname', 'osm_my_own_website_name'); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('honeypot_field_css_class', Text::_('OSM_HONEYPOT_FIELD_CSS_CLASS'), Text::_('OSM_HONEYPOT_FIELD_CSS_CLASS_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="honeypot_field_css_class" class="form-control" value="<?php echo $config->get('honeypot_field_css_class', 'osm-invisible-to-visitors'); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('minimum_form_time', Text::_('OSM_MINIMUM_FORM_TIME'), Text::_('OSM_MINIMUM_FORM_TIME_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="number" name="minimum_form_time" class="form-control" value="<?php echo $config->minimum_form_time; ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('maximum_submits_per_session', Text::_('OSM_MAXIMUM_SUBMIT_PER_SESSIONS'), Text::_('OSM_MAXIMUM_SUBMIT_PER_SESSIONS_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="number" name="maximum_submits_per_session" class="form-control" value="<?php echo $config->maximum_submits_per_session; ?>" size="10" />
	</div>
</div>