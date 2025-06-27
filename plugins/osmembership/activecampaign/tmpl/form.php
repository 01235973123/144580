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
 * @var   array                 $listOptions
 * @var   array                 $tagOptions
 * @var   OSMembershipTablePlan $row
 */

$params                   = new Registry($row->params);
$activeListIds            = explode(',', $params->get('active_list_ids', ''));
$activeTagsIds            = explode(',', $params->get('active_tag_ids', ''));
$expiredListIds           = explode(',', $params->get('expired_list_ids', ''));
$expiredTagIds            = explode(',', $params->get('expired_tag_ids', ''));
$subscriptionCancelTagIds = explode(',', $params->get('cancel_subscription_tag_ids', ''));

if ($this->app->isClient('site')) {
    $bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
} else {
    $bootstrapHelper = OSMembershipHelperHtml::getAdminBootstrapHelper();
}
?>
<div class="<?php
echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
	<div class="<?php
    echo $bootstrapHelper->getClassMapping('span6 pull-left'); ?>">
		<fieldset class="adminform">
			<legend><?php
                echo Text::_('OSM_WHEN_SUBSCRIPTION_ACTIVE'); ?></legend>
			<div class="control-group">
				<div class="control-label">
                    <?php
                    echo OSMembershipHelperHtml::getFieldLabel(
                        'active_list_ids',
                        Text::_('PLG_OSMEMBERSHIP_ACTIVECAMPAGIN_INTEGRATION_ADD_TO_LISTS')
                    ); ?>
				</div>
				<div class="controls">
                    <?php
                    echo OSMembershipHelperHtml::getChoicesJsSelect(
                        HTMLHelper::_(
                            'select.genericlist',
                            $listOptions,
                            'active_list_ids[]',
                            'class="form-select advSelect" multiple="multiple" size="10"',
                            'value',
                            'text',
                            $activeListIds
                        )
                    ); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
                    <?php
                    echo OSMembershipHelperHtml::getFieldLabel(
                        'active_tag_ids',
                        Text::_('PLG_OSMEMBERSHIP_ACTIVECAMPAGIN_INTEGRATION_ADD_TO_TAGS')
                    ); ?>
				</div>
				<div class="controls">
                    <?php
                    echo OSMembershipHelperHtml::getChoicesJsSelect(
                        HTMLHelper::_(
                            'select.genericlist',
                            $tagOptions,
                            'active_tag_ids[]',
                            'class="form-select advSelect" multiple="multiple" size="10"',
                            'value',
                            'text',
                            $activeTagsIds
                        )
                    ); ?>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="<?php
    echo $bootstrapHelper->getClassMapping('span6 pull-left'); ?>">
		<fieldset class="adminform">
			<legend><?php
                echo Text::_('OSM_WHEN_SUBSCRIPTION_EXPIRED'); ?></legend>
			<div class="control-group">
				<div class="control-label">
                    <?php
                    echo OSMembershipHelperHtml::getFieldLabel(
                        'expired_list_ids',
                        Text::_('PLG_OSMEMBERSHIP_ACTIVECAMPAGIN_INTEGRATION_REMOVE_LISTS')
                    ); ?>
				</div>
				<div class="controls">
                    <?php
                    echo OSMembershipHelperHtml::getChoicesJsSelect(
                        HTMLHelper::_(
                            'select.genericlist',
                            $listOptions,
                            'expired_list_ids[]',
                            'class="form-select advSelect" multiple="multiple" size="10"',
                            'value',
                            'text',
                            $expiredListIds
                        )
                    ); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
                    <?php
                    echo OSMembershipHelperHtml::getFieldLabel(
                        'expired_tag_ids',
                        Text::_('PLG_OSMEMBERSHIP_ACTIVECAMPAGIN_INTEGRATION_REMOVE_TAGS')
                    ); ?>
				</div>
				<div class="controls">
                    <?php
                    echo OSMembershipHelperHtml::getChoicesJsSelect(
                        HTMLHelper::_(
                            'select.genericlist',
                            $tagOptions,
                            'expired_tag_ids[]',
                            'class="form-select advSelect" multiple="multiple" size="10"',
                            'value',
                            'text',
                            $expiredTagIds
                        )
                    ); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
                    <?php
                    echo OSMembershipHelperHtml::getFieldLabel(
                        'cancel_subscription_tag_ids',
                        Text::_('PLG_OSMEMBERSHIP_ACTIVECAMPAGIN_SUBSCRIPTION_CANCEL_REMOVE_TAGS'),
                        Text::_('PLG_OSMEMBERSHIP_ACTIVECAMPAGIN_SUBSCRIPTION_CANCEL_REMOVE_TAGS_EXPLAIN')
                    ); ?>
				</div>
				<div class="controls">
                    <?php
                    echo OSMembershipHelperHtml::getChoicesJsSelect(
                        HTMLHelper::_(
                            'select.genericlist',
                            $tagOptions,
                            'cancel_subscription_tag_ids[]',
                            'class="form-select advSelect" multiple="multiple" size="10"',
                            'value',
                            'text',
                            $subscriptionCancelTagIds
                        )
                    ); ?>
				</div>
			</div>
	</div>
</div>
