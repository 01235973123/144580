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
 * @var   array                 $allListIds
 * @var   array                 $groupOptions
 * @var   OSMembershipTablePlan $row
 */

$params        = new Registry($row->params);
$listIds       = explode(',', $params->get('mailchimp_list_ids', ''));
$removeListIds = explode(',', $params->get('remove_mailchimp_list_ids', ''));

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
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('OSM_WHEN_SUBSCRIPTION_ACTIVE'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo OSMembershipHelperHtml::getFieldLabel('mailchimp_list_ids', Text::_('PLG_MAILCHIMP_ASSIGN_TO_LISTS'), Text::_('PLG_MAILCHIMP_ASSIGN_TO_LISTS_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'mailchimp_list_ids[]', 'class="form-select advSelect" multiple="multiple" size="10"', 'value', 'text', $listIds)); ?>
				</div>
			</div>
			<?php

			if (count($groupOptions))
			{
			?>
				<div class="control-group">
					<div class="control-label">
						<?php echo OSMembershipHelperHtml::getFieldLabel('mailchimp_group_ids', Text::_('PLG_MAILCHIMP_ADD_TO_GROUPS'), Text::_('PLG_MAILCHIMP_ADD_TO_GROUPS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $groupOptions, 'mailchimp_group_ids[]', 'class="form-select advSelect" multiple="multiple" size="10"', 'value', 'text', explode(',', $params->get('mailchimp_group_ids')))); ?>
					</div>
				</div>
			<?php
			}
			?>
		</fieldset>
	</div>
	<div class="<?php echo $bootstrapHelper->getClassMapping('span6 pull-left'); ?>">
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('OSM_WHEN_SUBSCRIPTION_EXPIRED'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo OSMembershipHelperHtml::getFieldLabel('remove_mailchimp_list_ids', Text::_('PLG_MAILCHIMP_REMOVE_FROM_MAILING_LISTS'), Text::_('PLG_MAILCHIMP_REMOVE_FROM_MAILING_LISTS_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'remove_mailchimp_list_ids[]', 'class="form-select advSelect" multiple="multiple" size="10"', 'value', 'text', $removeListIds)); ?>
				</div>
			</div>
			<?php
			if (count($groupOptions))
			{
			?>
				<div class="control-group">
					<div class="control-label">
						<?php echo OSMembershipHelperHtml::getFieldLabel('remove_mailchimp_group_ids', Text::_('PLG_MAILCHIMP_REMOVE_FROM_GROUPS'), Text::_('PLG_MAILCHIMP_REMOVE_FROM_GROUPS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $groupOptions, 'remove_mailchimp_group_ids[]', 'class="form-select advSelect" multiple="multiple" size="10"', 'value', 'text', explode(',', $params->get('remove_mailchimp_group_ids')))); ?>
					</div>
				</div>
			<?php
			}
			?>
		</fieldset>
	</div>
</div>
