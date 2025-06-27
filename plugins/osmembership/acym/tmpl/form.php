<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Layout variables
 * -----------------
 * @var   array                 $allLists
 * @var   array                 $mailingListFields
 * @var   OSMembershipTablePlan $row
 */

$params               = new Registry($row->params);
$activeAssignListIds  = explode(',', $params->get('acymailing_list_ids', ''));
$activeRemoveListIds  = explode(',', $params->get('acymailing_active_remove_list_ids', ''));
$expiredRemoveListIds = explode(',', $params->get('subscription_expired_acymailing_list_ids', ''));
$expiredAssignListIds = explode(',', $params->get('acymailing_expired_assign_list_ids', ''));

if ($this->app->isClient('site'))
{
	$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
}
else
{
	$bootstrapHelper = OSMembershipHelperHtml::getAdminBootstrapHelper();
}
?>
<div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
	<div class="<?php echo $bootstrapHelper->getClassMapping('span6 pull-left'); ?>">
		<fieldset class="adminform">
			<legend><?php echo Text::_('OSM_WHEN_SUBSCRIPTION_ACTIVE'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo OSMembershipHelperHtml::getFieldLabel('acymailing_list_ids', Text::_('OSM_ASSIGN_TO_MAILING_LISTS'), Text::_('OSM_ASSIGN_TO_MAILING_LISTS_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $allLists, 'acymailing_list_ids[]', 'class="form-select advSelect" multiple="multiple" size="10"', 'listid', 'name', $activeAssignListIds)); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo OSMembershipHelperHtml::getFieldLabel('acymailing_active_remove_list_ids', Text::_('OSM_REMOVE_FROM_MAILING_LISTS'), Text::_('OSM_REMOVE_FROM_MAILING_LISTS_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $allLists, 'acymailing_active_remove_list_ids[]', 'class="form-select advSelect" multiple="multiple" size="10"', 'listid', 'name', $activeRemoveListIds)); ?>
				</div>
			</div>
			<?php
			if (count($mailingListFields))
			{
				$options   = [];
				$options[] = HTMLHelper::_('select.option', '', 'Select Field', 'id', 'name');
				$options   = array_merge($options, $mailingListFields);
				?>
				<div class="control-group">
					<div class="control-label">
						<?php echo OSMembershipHelperHtml::getFieldLabel('mailing_list_custom_field', Text::_('OSM_MAILING_LISTS_CUSTOM_FIELD'), Text::_('OSM_MAILING_LISTS_CUSTOM_FIELD_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'mailing_list_custom_field', 'class="form-select advSelect"', 'id', 'name', (int) $params->get('mailing_list_custom_field'))); ?>
					</div>
				</div>
				<?php
			}
			?>
		</fieldset>
	</div>
	<div class="<?php echo $bootstrapHelper->getClassMapping('span6 pull-left'); ?>">
		<fieldset class="adminform">
			<legend><?php echo Text::_('OSM_WHEN_SUBSCRIPTION_EXPIRED'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_expired_acymailing_list_ids', Text::_('OSM_REMOVE_FROM_MAILING_LISTS'), Text::_('OSM_REMOVE_FROM_MAILING_LISTS_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $allLists, 'subscription_expired_acymailing_list_ids[]', 'class="form-select advSelect" multiple="multiple" size="10"', 'listid', 'name', $expiredRemoveListIds)); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo OSMembershipHelperHtml::getFieldLabel('acymailing_expired_assign_list_ids', Text::_('OSM_ASSIGN_TO_MAILING_LISTS'), Text::_('OSM_ASSIGN_TO_MAILING_LISTS_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $allLists, 'acymailing_expired_assign_list_ids[]', 'class="form-select advSelect" multiple="multiple" size="10"', 'listid', 'name', $expiredAssignListIds)); ?>
				</div>
			</div>
	</div>
</div>

