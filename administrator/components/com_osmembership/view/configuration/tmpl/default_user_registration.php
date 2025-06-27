<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Layout variables
 *
 * @var MPFConfig $config
 */
?>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('OSM_USER_REGISTRATION_SETTINGS'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('registration_integration', Text::_('OSM_REGISTRATION_INTEGRATION'), Text::_('OSM_REGISTRATION_INTEGRATION_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('registration_integration', $config->registration_integration); ?>
		</div>
	</div>
	<?php
	if (ComponentHelper::isInstalled('com_comprofiler') && PluginHelper::isEnabled('osmembership', 'cb'))
	{
		?>
		<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['registration_integration' => '1']); ?>'>
			<div class="control-label">
				<?php echo OSMembershipHelperHtml::getFieldLabel('use_cb_api', Text::_('OSM_USE_CB_API'), Text::_('OSM_USE_CB_API_EXPLAIN')); ?>
			</div>
			<div class="controls">
				<?php echo OSMembershipHelperHtml::getBooleanInput('use_cb_api', $config->use_cb_api); ?>
			</div>
		</div>
		<?php
	}
	?>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['registration_integration' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('use_email_as_username', Text::_('OSM_USE_EMAIL_AS_USERNAME'), Text::_('OSM_USE_EMAIL_AS_USERNAME_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('use_email_as_username', $config->use_email_as_username); ?>
		</div>
	</div>
    <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['registration_integration' => '1']); ?>'>
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('auto_generate_password', Text::_('OSM_AUTO_GENERATE_PASSWORD'), Text::_('OSM_AUTO_GENERATE_PASSWORD_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('auto_generate_password', $config->auto_generate_password); ?>
        </div>
    </div>
    <div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['auto_generate_password' => '1']); ?>'>
        <div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('auto_generate_password_length', Text::_('OSM_PASSWORD_LENGTH'), Text::_('OSM_PASSWORD_LENGTH_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<input type="number" name="auto_generate_password_length" class="form-control" value="<?php echo $config->get('auto_generate_password_length', 8); ?>" step="1" />
        </div>
    </div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['registration_integration' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('send_activation_email', Text::_('OSM_SEND_ACTIVATION_EMAIL'), Text::_('OSM_SEND_ACTIVATION_EMAIL_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('send_activation_email', $config->send_activation_email); ?>
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['registration_integration' => '1']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('create_account_when_membership_active', Text::_('OSM_CREATE_ACCOUNT_WHEN_MEMBERSHIP_ACTIVE'), Text::_('OSM_CREATE_ACCOUNT_WHEN_MEMBERSHIP_ACTIVE_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getBooleanInput('create_account_when_membership_active', $config->create_account_when_membership_active); ?>
		</div>
	</div>
</fieldset>
