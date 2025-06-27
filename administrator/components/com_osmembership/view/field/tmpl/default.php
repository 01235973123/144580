<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

// Little command to allow viewing field data easier without having to edit code during support
if ($this->input->getInt('debug'))
{
	print_r($this->item);
}

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

Factory::getApplication()
	->getDocument()
	->addScriptOptions('validateRules', OSMembershipHelper::validateRules())
	->addScriptOptions('siteUrl', Uri::base(true))
	->getWebAssetManager()
	->useScript('core')
	->useScript('showon')
	->registerAndUseScript('com_osmembership.admin-field-default', 'media/com_osmembership/js/admin-field-default.min.js')
	->addInlineStyle('.hasTip{display:block !important}');

$translatable      = Multilanguage::isEnabled() && count($this->languages);
$hasCustomSettings = file_exists(__DIR__ . '/default_custom_settings.php');
$useTabs           = $translatable || $hasCustomSettings;

$keys = [
	'OSM_ENTER_CUSTOM_FIELD_NAME',
	'OSM_ENTER_CUSTOM_FIELD_TITLE',
	'OSM_CHOOSE_CUSTOM_FIELD_TYPE',
];

OSMembershipHelperHtml::addJSStrings($keys);

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
<?php
	if ($useTabs)
	{
		echo HTMLHelper::_( 'uitab.startTabSet', 'field', ['active' => 'general-page', 'recall' => true]);
		echo HTMLHelper::_( 'uitab.addTab', 'field', 'general-page', Text::_('OSM_GENERAL'));
	}
?>
    <div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
        <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
            <fieldset class="form-horizontal options-form">
                <legend><?php echo Text::_('OSM_GENERAL'); ?></legend>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo Text::_('OSM_FIELD_ASSIGNMENT'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['assignment'] ; ?>
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['assignment' => ['1', '-1']]); ?>'>
                    <div class="control-label">
                            <?php echo Text::_('OSM_PLAN'); ?>
                        </div>
                        <div class="controls">
                            <?php echo $this->lists['plan_id'] ; ?>
                        </div>
                    </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('name', Text::_('OSM_NAME'), Text::_('OSM_FIELD_NAME_REQUIREMENT')); ?>
                    </div>
                    <div class="controls">
                        <input class="form-control" type="text" name="name" id="name" size="40" maxlength="250" value="<?php echo $this->item->name;?>" <?php if ($this->item->is_core) echo 'readonly="readonly"' ; ?> />
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo  Text::_('OSM_TITLE'); ?>
                    </div>
                    <div class="controls">
                        <input class="form-control" type="text" name="title" id="title" size="40" maxlength="250" value="<?php echo $this->item->title;?>" />
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
			            <?php echo Text::_('OSM_DESCRIPTION'); ?>
                    </div>
                    <div class="controls">
                        <textarea rows="7" cols="40" name="description" class="form-control"><?php echo $this->item->description;?></textarea>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo Text::_('OSM_ACCESS'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['access']; ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo Text::_('OSM_REQUIRED'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['required']; ?>
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Text', 'Email', 'Email', 'Number', 'Tel', 'Textarea', 'Password']]); ?>'>
                    <div class="control-label">
	                    <?php echo OSMembershipHelperHtml::getFieldLabel('readonly', Text::_('OSM_READONLY')); ?>
                    </div>
                    <div class="controls">
	                    <?php echo OSMembershipHelperHtml::getBooleanInput('readonly', $this->item->readonly); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo Text::_('OSM_PUBLISHED'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['published']; ?>
                    </div>
                </div>
            </fieldset>
            <?php
			if (isset($this->lists['field_mapping']) || isset($this->lists['newsletter_field_mapping']) || PluginHelper::isEnabled('osmembership',
					'userprofile'))
				{
				?>
                    <fieldset class="form-horizontal options-form">
                        <legend><?php echo Text::_('OSM_FIELD_MAPPING'); ?></legend>
		                <?php
						if (isset($this->lists['field_mapping']))
						{
						?>
                            <div class="control-group">
                                <div class="control-label">
					                <?php echo OSMembershipHelperHtml::getFieldLabel('field_mapping', Text::_('OSM_FIELD_MAPPING'), Text::_('OSM_FIELD_MAPPING_GUIDE')); ?>
                                </div>
                                <div class="controls">
					                <?php echo $this->lists['field_mapping'] ; ?>
                                </div>
                            </div>
			            <?php
						}

						if (isset($this->lists['newsletter_field_mapping']))
						{
						?>
                            <div class="control-group">
                                <div class="control-label">
					                <?php echo OSMembershipHelperHtml::getFieldLabel('newsletter_field_mapping', Text::_('OSM_NEWSLETTER_FIELD_MAPPING'), Text::_('OSM_NEWSLETTER_FIELD_MAPPING_EXPLAIN')); ?>
                                </div>
                                <div class="controls">
					                <?php echo $this->lists['newsletter_field_mapping'] ; ?>
                                </div>
                            </div>
			            <?php
						}

						if (PluginHelper::isEnabled('osmembership', 'userprofile'))
						{
						?>
                            <div class="control-group">
                                <div class="control-label">
					                <?php echo OSMembershipHelperHtml::getFieldLabel('profile_field_mapping', Text::_('OSM_PROFILE_FIELD_MAPPING'), Text::_('OSM_PROFILE_FIELD_MAPPING_GUIDE')); ?>
                                </div>
                                <div class="controls">
					                <?php echo $this->lists['profile_field_mapping'] ; ?>
                                </div>
                            </div>
			            <?php
						}
						?>
                    </fieldset>
                <?php
				}
			?>
            <fieldset class="form-horizontal options-form">
                <legend><?php echo Text::_('OSM_SUBSCRIPTION_SETTINGS'); ?></legend>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Text', 'Email']]); ?>'>
                    <div class="control-label">
                            <?php echo OSMembershipHelperHtml::getFieldLabel('receive_emails', Text::_('OSM_RECEIVE_EMAILS'), Text::_('OSM_RECEIVE_EMAILS_EXPLAIN')); ?>
                        </div>
                        <div class="controls">
                            <?php echo OSMembershipHelperHtml::getBooleanInput('receive_emails', $this->item->receive_emails); ?>
                        </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('show_on_subscription_form', Text::_('OSM_SHOW_ON_SUBSCRIPTION_FORM'), Text::_('OSM_SHOW_ON_SUBSCRIPTION_FORM_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo OSMembershipHelperHtml::getBooleanInput('show_on_subscription_form', $this->item->show_on_subscription_form); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
			            <?php echo OSMembershipHelperHtml::getFieldLabel('hide_on_membership_renewal', Text::_('OSM_HIDE_ON_MEMBERSHIP_RENEWAL'), Text::_('OSM_HIDE_ON_MEMBERSHIP_RENEWAL_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
			            <?php echo $this->lists['hide_on_membership_renewal']; ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('show_on_user_profile', Text::_('OSM_SHOW_ON_USER_PROFILE'), Text::_('OSM_SHOW_ON_USER_PROFILE')); ?>
                    </div>
                    <div class="controls">
                        <?php echo OSMembershipHelperHtml::getBooleanInput('show_on_user_profile', $this->item->show_on_user_profile); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('can_edit_on_profile', Text::_('OSM_CAN_EDIT_ON_PROFILE'), Text::_('OSM_CAN_EDIT_ON_PROFILE_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['can_edit_on_profile']; ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('show_on_subscriptions', Text::_('OSM_SHOW_ON_SUBSCRIPTIONS'), Text::_('OSM_SHOW_ON_SUBSCRIPTIONS_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo OSMembershipHelperHtml::getBooleanInput('show_on_subscriptions', $this->item->show_on_subscriptions); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('show_on_members_list', Text::_('OSM_SHOW_ON_MEMBER_LIST'), Text::_('OSM_SHOW_ON_MEMBER_LIST_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['show_on_members_list']; ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('show_on_profile', Text::_('OSM_SHOW_ON_PROFILE'), Text::_('OSM_SHOW_ON_PROFILE')); ?>
                    </div>
                    <div class="controls">
                        <?php echo OSMembershipHelperHtml::getBooleanInput('show_on_profile', $this->item->show_on_profile); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('show_on_subscription_payment', Text::_('OSM_SHOW_ON_SUBSCRIPTION_PAYMENT'), Text::_('OSM_SHOW_ON_SUBSCRIPTION_PAYMENT_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo OSMembershipHelperHtml::getBooleanInput('show_on_subscription_payment', $this->item->show_on_subscription_payment); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('hide_on_email', Text::_('OSM_HIDE_ON_EMAIL'), Text::_('OSM_HIDE_ON_EMAIL_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['hide_on_email']; ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('hide_on_export', Text::_('OSM_HIDE_ON_EXPORT'), Text::_('OSM_HIDE_ON_EXPORT_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['hide_on_export']; ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('populate_from_previous_subscription', Text::_('OSM_POPULATE_FROM_PREVIOUS_SUBSCRIPTION'), Text::_('OSM_POPULATE_FROM_PREVIOUS_SUBSCRIPTION_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo OSMembershipHelperHtml::getBooleanInput('populate_from_previous_subscription', $this->item->populate_from_previous_subscription); ?>
                    </div>
                </div>
	            <div class="control-group">
		            <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('synchronize_data', Text::_('OSM_FIELD_SYNCHRONIZE_DATA'), Text::_('OSM_FIELD_SYNCHRONIZE_DATA_EXPLAIN')); ?>
		            </div>
		            <div class="controls">
                        <?php echo OSMembershipHelperHtml::getBooleanInput('synchronize_data', $this->item->synchronize_data); ?>
		            </div>
	            </div>
	            <?php
				if (PluginHelper::isEnabled('osmembership', 'groupmembership'))
				{
				?>
                    <div class="control-group">
                        <div class="control-label">
				            <?php echo OSMembershipHelperHtml::getFieldLabel('show_on_group_member_form', Text::_('OSM_SHOW_ON_GROUP_MEMBER_FORM'), Text::_('OSM_SHOW_ON_GROUP_MEMBER_FORM_EXPLAIN')); ?>
                        </div>
                        <div class="controls">
				            <?php echo $this->lists['show_on_group_member_form']; ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
				            <?php echo OSMembershipHelperHtml::getFieldLabel('populate_from_group_admin', Text::_('OSM_POPULATE_FROM_GROUP_ADMIN'), Text::_('OSM_POPULATE_FROM_GROUP_ADMIN_EXPLAIN')); ?>
                        </div>
                        <div class="controls">
				            <?php echo OSMembershipHelperHtml::getBooleanInput('populate_from_group_admin', $this->item->populate_from_group_admin); ?>
                        </div>
                    </div>
		        <?php
				}
				?>
                <div class="control-group">
                    <div class="control-label">
			            <?php echo  Text::_('OSM_EXTRA'); ?>
                    </div>
                    <div class="controls">
                        <input class="form-control" type="text" name="extra" id="extra" size="40" maxlength="250" value="<?php echo $this->escape($this->item->extra);?>" />
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
            <fieldset class="form-horizontal options-form">
                <legend><?php echo Text::_('OSM_FIELD_SETTINGS'); ?></legend>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo Text::_('OSM_FIELD_TYPE'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['fieldtype']; ?>
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['File']]); ?>'>
                    <div class="control-label">
                            <?php echo OSMembershipHelperHtml::getFieldLabel('allowed_file_types', Text::_('OSM_ALLOWED_FILE_TYPES'), Text::_('OSM_ALLOWED_FILE_TYPES_EXPLAIN')); ?>
                        </div>
                        <div class="controls">
                            <input type="text" name="allowed_file_types" class="form-control" value="<?php echo $this->item->allowed_file_types; ?>" size="40" />
                        </div>
                    </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Number', 'Range']]); ?>'>
                    <div class="control-label">
                        <?php echo Text::_('OSM_MAX'); ?>
                    </div>
                    <div class="controls">
                        <input type="number" name="max" value="<?php echo $this->item->max; ?>" class="form-control" />
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Number', 'Range']]); ?>'>
                    <div class="control-label">
                            <?php echo Text::_('OSM_MIN'); ?>
                        </div>
                        <div class="controls">
                            <input type="number" name="min" value="<?php echo $this->item->min; ?>" class="form-control" />
                        </div>
                    </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Number', 'Range']]); ?>'>
                    <div class="control-label">
                            <?php echo Text::_('OSM_STEP'); ?>
                        </div>
                        <div class="controls">
                            <input type="number" name="step" value="<?php echo $this->item->step; ?>" class="form-control" />
                        </div>
                    </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => 'List']); ?>'>
                    <div class="control-label">
                            <?php echo Text::_('OSM_MULTIPLE'); ?>
                        </div>
                        <div class="controls">
                            <?php echo $this->lists['multiple']; ?>
                        </div>
                    </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['List', 'Checkboxes', 'Radio']]); ?>'>
                    <div class="control-label">
                            <?php echo OSMembershipHelperHtml::getFieldLabel('values', Text::_('OSM_VALUES'), Text::_('OSM_EACH_ITEM_IN_ONELINE')); ?>
                        </div>
                        <div class="controls">
                            <textarea rows="5" cols="40" name="values" class="form-control"><?php echo $this->item->values; ?></textarea>
                        </div>
                    </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('default_values', Text::_('OSM_DEFAULT_VALUES'), Text::_('OSM_EACH_ITEM_IN_ONELINE')); ?>
                    </div>
                    <div class="controls">
                        <textarea rows="5" cols="40" class="form-control" name="default_values"><?php echo $this->item->default_values; ?></textarea>
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Text', 'Number', 'List', 'Checkboxes', 'Radio', 'Range']]); ?>'>
                    <div class="control-label">
                        <?php echo Text::_('OSM_FEE_FIELD') ; ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['fee_field']; ?>
                    </div>
                </div>
                <?php
					$showOnData = [
						'fieldtype' => ['List', 'Checkboxes', 'Radio'],
						'fee_field' => '1',
					];
				?>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon($showOnData); ?>'>
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('fee_values', Text::_('OSM_FEE_VALUES'), Text::_('OSM_EACH_ITEM_IN_ONELINE')); ?>
                    </div>
                    <div class="controls">
                        <textarea rows="5" cols="40" class="form-control" name="fee_values"><?php echo $this->item->fee_values; ?></textarea>
                    </div>
                </div>
                <?php
					$showOnData = [
						'fieldtype' => ['Text', 'Number', 'List', 'Checkboxes', 'Radio', 'Range', 'Hidden'],
						'fee_field' => '1',
					];
				?>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon($showOnData); ?>'>
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('fee_formula', Text::_('OSM_FEE_FORMULA'), Text::_('OSM_FEE_FORMULA_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <input type="text" class="form-control" size="40" name="fee_formula" value="<?php echo $this->item->fee_formula ; ?>" />
                    </div>
                </div>
	            <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fee_field' => '1']); ?>'>
		            <div class="control-label">
			            <?php echo OSMembershipHelperHtml::getFieldLabel('fee_usage', Text::_('OSM_FEE_USAGE'), Text::_('OSM_FEE_USAGE_EXPLAIN')); ?>
		            </div>
		            <div class="controls">
			            <?php echo $this->lists['fee_usage']; ?>
		            </div>
	            </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fee_field' => '1']); ?>'>
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('taxable', Text::_('OSM_TAXABLE'), Text::_('OSM_TAXABLE_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo OSMembershipHelperHtml::getBooleanInput('taxable', $this->item->taxable); ?>
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Text', 'Tel', 'Range', 'Number']]); ?>'>
                    <div class="control-label">
			            <?php echo OSMembershipHelperHtml::getFieldLabel('input-mask', Text::_('OSM_INPUT_MASK')); ?>
                    </div>
                    <div class="controls">
                        <input type="text" class="form-control" size="50" name="input_mask" value="<?php echo $this->escape($this->item->input_mask);?>" />
                        <div class="form-text"><?php echo Text::_('OSM_INPUT_MASK_EXPLAIN') ?></div>
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['List', 'SQL']]); ?>'>
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('prompt_text', Text::_('OSM_PROMPT_TEXT'), Text::_('OSM_PROMPT_TEXT_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <input type="text" class="form-control" size="40" name="prompt_text" value="<?php echo $this->item->prompt_text ; ?>" />
                    </div>
                </div>
                <?php
				$showOnData = [
					'fieldtype' => ['List', 'Checkboxes', 'Radio', 'Countries', 'State'],
				];
				?>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon($showOnData); ?>'>
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('filterable', Text::_('OSM_FILTERABLE'), Text::_('OSM_FILTERABLE_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <?php echo OSMembershipHelperHtml::getBooleanInput('filterable', $this->item->filterable); ?>
                    </div>
                </div>
	            <div class="control-group">
		            <div class="control-label">
			            <?php echo OSMembershipHelperHtml::getFieldLabel('payment_method', Text::_('OSM_PAYMENT_METHOD'), Text::_('OSM_FIELD_PAYMENT_METHOD_EXPLAIN')); ?>
		            </div>
		            <div class="controls">
			            <?php echo $this->lists['payment_method']; ?>
		            </div>
	            </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo Text::_('OSM_DEPEND_ON_FIELD');?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['depend_on_field_id']; ?>
                    </div>
                </div>
                <div class="control-group" id="depend_on_options_container" style="display: <?php echo $this->item->depend_on_field_id ? '' : 'none'; ?>">
                    <div class="control-label">
                        <?php echo Text::_('OSM_DEPEND_ON_OPTIONS');?>
                    </div>
                    <div class="controls" id="options_container">
                        <?php
						if (count($this->dependOptions))
						{
						?>
                            <div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
                                <?php
								$span4Class = $bootstrapHelper->getClassMapping('span4');

								for ($i = 0 , $n = count($this->dependOptions) ; $i < $n ; $i++)
								{
									$value = $this->dependOptions[$i] ;
								?>
                                    <div class="<?php echo $span4Class; ?>">
                                        <input value="<?php echo $this->escape($value); ?>" type="checkbox" class="form-check-input" name="depend_on_options[]" <?php if (in_array($value, $this->dependOnOptions)) echo 'checked="checked"'; ?>><?php echo $value;?>
                                    </div>
                                <?php
								}
								?>
                            </div>
                        <?php
						}
						?>
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['List', 'Checkboxes', 'Radio']]); ?>' style="margin-top:10px;">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('joomla_group_ids', Text::_('OSM_JOOMLA_GROUP_IDS'), Text::_('OSM_JOOMLA_GROUP_IDS_EXPLAINS')); ?>
                    </div>
                    <div class="controls">
                        <textarea rows="5" cols="40" class="form-control" name="joomla_group_ids"><?php echo $this->item->joomla_group_ids; ?></textarea>
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['List', 'Radio']]); ?>' style="margin-top:10px;">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('modify_subscription_duration', Text::_('OSM_MODIFY_SUBSCRIPTION_DURATION'), Text::_('OSM_MODIFY_SUBSCRIPTION_DURATION_EXPLAINS')); ?>
                    </div>
                    <div class="controls">
                        <textarea rows="5" cols="40" class="form-control" name="modify_subscription_duration"><?php echo $this->item->modify_subscription_duration; ?></textarea>
                    </div>
                </div>
            </fieldset>
            <fieldset class="form-horizontal options-form">
                <legend><?php echo Text::_('OSM_DISPLAY_SETTINGS'); ?></legend>
	            <div class="control-group">
		            <div class="control-label">
			            <?php echo  Text::_('OSM_POSITION'); ?>
		            </div>
		            <div class="controls">
			            <?php echo $this->lists['position']; ?>
		            </div>
	            </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Textarea']]); ?>'>
                    <div class="control-label">
                        <?php echo  Text::_('OSM_ROWS'); ?>
                    </div>
                    <div class="controls">
                        <input class="form-control" type="number" name="rows" id="rows" size="10" maxlength="250" value="<?php echo $this->item->rows;?>" />
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Textarea']]); ?>'>
                    <div class="control-label">
                        <?php echo  Text::_('OSM_COLS'); ?>
                    </div>
                    <div class="controls">
                        <input class="form-control" type="number" name="cols" id="cols" size="10" maxlength="250" value="<?php echo $this->item->cols;?>" />
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Text', 'Checkboxes', 'Radio', 'List']]); ?>'>
                    <div class="control-label">
                        <?php echo  Text::_('OSM_SIZE'); ?>
                    </div>
                    <div class="controls">
                        <input class="form-control" type="number" name="size" id="size" size="10" maxlength="250" value="<?php echo $this->item->size;?>" />
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
			            <?php echo  Text::_('OSM_INPUT_SIZE'); ?>
                    </div>
                    <div class="controls">
			            <?php echo $this->lists['input_size'];  ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
			            <?php echo  Text::_('OSM_FIELD_CONTAINER_SIZE'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['container_size'];  ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
			            <?php echo  Text::_('OSM_FIELD_CONTAINER_CLASS'); ?>
                    </div>
                    <div class="controls">
                        <input class="form-control" type="text" name="container_class" id="container_class" size="10" maxlength="250" value="<?php echo $this->item->container_class;?>" />
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo  Text::_('OSM_CSS_CLASS'); ?>
                    </div>
                    <div class="controls">
                        <input class="form-control" type="text" name="css_class" id="css_class" size="10" maxlength="250" value="<?php echo $this->item->css_class;?>" />
                    </div>
                </div>
	            <?php
	            $showOnData = [
		            'fieldtype' => ['Text', 'Url', 'Email', 'Number', 'Tel', 'Time'],
	            ];
	            ?>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon($showOnData); ?>'>
                    <div class="control-label">
                        <?php echo  Text::_('OSM_PLACE_HOLDER'); ?>
                    </div>
                    <div class="controls">
                        <input class="form-control" type="text" name="place_holder" id="place_holder" size="40" maxlength="250" value="<?php echo $this->item->place_holder;?>" />
                    </div>
                </div>
                <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['fieldtype' => ['Text', 'Textarea']]); ?>'>
                    <div class="control-label">
                        <?php echo  Text::_('OSM_MAX_LENGTH'); ?>
                    </div>
                    <div class="controls">
                        <input class="form-control" type="number" name="max_length" id="max_length" size="40" maxlength="250" value="<?php echo $this->item->max_length;?>" />
                    </div>
                </div>
            </fieldset>
            <fieldset class="form-horizontal options-form">
                <legend><?php echo Text::_('OSM_FIELD_DATA_VALIDATION'); ?></legend>
                <div class="control-group">
                    <div class="control-label">
			            <?php echo Text::_('OSM_DATA_FILTER') ; ?>
                    </div>
                    <div class="controls">
			            <?php echo $this->lists['filter']; ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo Text::_('OSM_DATATYPE_VALIDATION') ; ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->lists['datatype_validation']; ?>
                    </div>
                </div>
                <div class="control-group validation-rules">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('validation_rules', Text::_('OSM_VALIDATION_RULES'), Text::_('OSM_VALIDATION_RULES_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <input type="text" class="form-control" size="40" name="validation_rules" value="<?php echo $this->item->validation_rules ; ?>" />
                    </div>
                </div>
                <div class="control-group validation-rules">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('server_validation_rules', Text::_('OSM_SERVER_VALIDATION_RULES'), Text::_('OSM_SERVER_VALIDATION_RULES_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <input type="text" class="form-control" size="40" name="server_validation_rules" value="<?php echo $this->item->server_validation_rules ; ?>" />
                    </div>
                </div>
                <div class="control-group validation-rules">
                    <div class="control-label">
                        <?php echo OSMembershipHelperHtml::getFieldLabel('validation_error_message', Text::_('OSM_VALIDATION_ERROR_MESSAGE'), Text::_('OSM_VALIDATION_ERROR_MESSAGE_EXPLAIN')); ?>
                    </div>
                    <div class="controls">
                        <input type="text" class="form-control" size="40" name="validation_error_message" value="<?php echo $this->escape($this->item->validation_error_message); ?>" />
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
	<?php

	if ($useTabs)
	{
		echo HTMLHelper::_( 'uitab.endTab');
	}

	if ($translatable)
	{
		echo HTMLHelper::_( 'uitab.addTab', 'field', 'translation-page', Text::_('OSM_TRANSLATION'));
		echo HTMLHelper::_( 'uitab.startTabSet', 'field-translation', ['active' => 'translation-page-' . $this->languages[0]->sef, 'recall' => true]);
		$rootUri = Uri::root(true);

		foreach ($this->languages as $language)
		{
			$sef = $language->sef;
			echo HTMLHelper::_( 'uitab.addTab', 'field-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/mod_languages/images/' . $language->image . '.gif" />');
		?>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('OSM_TITLE'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="title_<?php echo $sef; ?>" id="title_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'title_' . $sef}; ?>" />
				</div>
			</div>
            <div class="control-group">
                <div class="control-label">
					<?php echo  Text::_('OSM_PLACE_HOLDER'); ?>
                </div>
                <div class="controls">
                    <input class="form-control" type="text" name="place_holder_<?php echo $sef; ?>" id="place_holder_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'place_holder_' . $sef}; ?>" />
                </div>
            </div>

			<?php
			$showOnData = [
				'fieldtype' => ['List'],
			];
			?>
            <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon($showOnData); ?>'>
                <div class="control-label">
	                <?php echo OSMembershipHelperHtml::getFieldLabel('prompt_text_' . $sef, Text::_('OSM_PROMPT_TEXT'), Text::_('OSM_PROMPT_TEXT_EXPLAIN')); ?>
                </div>
                <div class="controls">
                    <input class="form-control" type="text" name="prompt_text_<?php echo $sef; ?>" id="prompt_text_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'prompt_text_' . $sef}; ?>" />
                </div>
            </div>

			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_DESCRIPTION'); ?>
				</div>
				<div class="controls">
					<textarea rows="5" cols="40" class="form-control" name="description_<?php echo $sef; ?>"><?php echo $this->item->{'description_' . $sef};?></textarea>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_VALUES'); ?>
				</div>
				<div class="controls">
					<textarea rows="5" cols="40" class="form-control" name="values_<?php echo $sef; ?>"><?php echo $this->item->{'values_' . $sef}; ?></textarea>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_DEFAULT_VALUES'); ?>
				</div>
				<div class="controls">
					<textarea rows="5" cols="40" class="form-control" name="default_values_<?php echo $sef; ?>"><?php echo $this->item->{'default_values_' . $sef}; ?></textarea>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_FEE_VALUES'); ?>
				</div>
				<div class="controls">
					<textarea class="form-control" rows="5" cols="40" name="fee_values_<?php echo $sef; ?>"><?php echo $this->item->{'fee_values_' . $sef}; ?></textarea>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('OSM_VALIDATION_ERROR_MESSAGE'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="validation_error_message_<?php echo $sef; ?>" id="validation_error_message_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'validation_error_message_' . $sef}; ?>" />
				</div>
			</div>
		<?php
			echo HTMLHelper::_( 'uitab.endTab');
		}

		echo HTMLHelper::_( 'uitab.endTabSet');
		echo HTMLHelper::_( 'uitab.endTab');
	}

	// Add support for custom settings layout
	if ($hasCustomSettings)
	{
		echo HTMLHelper::_( 'uitab.addTab', 'field', 'custom-settings-page', Text::_('OSM_CUSTOM_SETTINGS'));
		echo $this->loadTemplate('custom_settings');
		echo HTMLHelper::_( 'uitab.endTab');
	}

	if ($useTabs)
	{
		echo HTMLHelper::_( 'uitab.endTabSet');
	}
	?>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>