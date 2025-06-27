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
 * @var   array                 $options
 * @var   OSMembershipTablePlan $row
 */

$params = new Registry($row->params);

$activeGroupIds        = explode(',', $params->get('joomla_group_ids', ''));
$activeRemoveGroupIds  = explode(',', $params->get('remove_joomla_group_ids', ''));
$expiredRemoveGroupIds = explode(',', $params->get('joomla_expried_group_ids', ''));
$expiredGroupIds       = explode(',', $params->get('subscription_expired_joomla_group_ids', ''));

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
	<div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?> pull-left">
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('OSM_WHEN_SUBSCRIPTION_ACTIVE'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('PLG_OSMEMBERSHIP_JOOMLA_ASSIGN_TO_JOOMLA_GROUPS'); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'joomla_group_ids[]', ' multiple="multiple" size="6" class="form-select advSelect" ', 'value', 'text', $activeGroupIds)); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('PLG_OSMEMBERSHIP_JOOMLA_REMOVE_FROM_JOOMLA_GROUPS'); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'remove_joomla_group_ids[]', ' multiple="multiple" size="6" class="form-select advSelect" ', 'value', 'text', $activeRemoveGroupIds)); ?>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?> pull-left">
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('OSM_WHEN_SUBSCRIPTION_EXPIRED'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('PLG_OSMEMBERSHIP_JOOMLA_REMOVE_FROM_JOOMLA_GROUPS'); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'joomla_expried_group_ids[]', ' multiple="multiple" size="6" class="form-select advSelect" ', 'value', 'text', $expiredRemoveGroupIds)); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('PLG_OSMEMBERSHIP_JOOMLA_ASSIGN_TO_JOOMLA_GROUPS'); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'subscription_expired_joomla_group_ids[]', ' multiple="multiple" size="6" class="form-select advSelect" ', 'value', 'text', $expiredGroupIds)); ?>
				</div>
			</div>
		</fieldset>
	</div>
</div>

