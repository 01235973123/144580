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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

// Little command to allow viewing subscription data easier without having to edit code during support
if ($this->input->getInt('debug'))
{
	print_r($this->item);
}

if ($this->canCancelSubscription)
{
	ToolbarHelper::custom('cancel_subscription', 'delete', 'delete', Text::_('OSM_CANCEL_SUBSCRIPTION'), false);
}

if ($this->canRefundSubscription)
{
	ToolbarHelper::custom('refund', 'delete', 'delete', Text::_('OSM_REFUND'), false);
}

OSMembershipHelperJquery::loadjQuery();
OSMembershipHelper::loadLanguage();
OSMembershipHelperJquery::validateForm();

Factory::getApplication()
	->getDocument()
	->addScriptOptions('selectedState', $this->selectedState)
	->addScriptOptions('MPBaseUrl', Uri::base(true))
	->addScriptOptions('validate_form_data', (bool) $this->config->get('validate_backend_subscription_form_data', 1))
	->getWebAssetManager()
	->useScript('core')
	->addInlineScript(
		'
			var siteUrl = "' . Uri::root() . '";			
		'
	)->registerAndUseScript('com_osmembership.membershippro', 'media/com_osmembership/assets/js/membershippro.min.js')
	->registerAndUseScript('com_osmembership.admin-subscription-default', 'media/com_osmembership/js/admin-subscription-default.min.js');

$languageItems = [
	'OSM_CANCEL_SUBSCRIPTION_CONFIRM',
	'OSM_REFUND_SUBSCRIPTION_CONFIRM',
];

OSMembershipHelperHtml::addJSStrings($languageItems);

$selectedState  = '';
$numberDecimals = (int) $this->config->get('decimals') ?: 2;

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" enctype="multipart/form-data" class="form form-horizontal">
	<div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
		<div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
			<fieldset class="form-horizontal options-form">
				<legend><?php echo Text::_('OSM_ACCOUNT_INFORMATION'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('OSM_PLAN'); ?><span class="required">&nbsp;*</span>
					</div>
					<div class="controls">
						<?php echo $this->lists['plan_id'] ; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->item->id ? Text::_('OSM_USER') : Text::_('OSM_EXISTING_USER'); ?>
					</div>
					<div class="controls">
						<?php echo OSMembershipHelper::getUserInput($this->item->user_id, (int) $this->item->id) ; ?>
					</div>
				</div>
				<?php
				if (!$this->item->id && $this->config->registration_integration)
				{
				?>
					<div class="control-group" id="username_container">
						<div class="control-label">
							<?php echo Text::_('OSM_USERNAME'); ?><span class="required">*</span>
						</div>
						<div class="controls">
							<input type="text" name="username" size="20" class="form-control validate[ajax[ajaxUserCall]]" value="" />
							<?php echo Text::_('OSM_USERNAME_EXPLAIN'); ?>
						</div>
					</div>
					<?php
					if (empty($this->config->auto_generate_password))
					{
					?>
						<div class="control-group" id="password_container">
							<div class="control-label">
								<?php echo Text::_('OSM_PASSWORD'); ?><span class="required">*</span>
							</div>
							<div class="controls">
								<?php
								$params = ComponentHelper::getParams('com_users');
								$minimumLength = $params->get('minimum_length', 4);

								if ($minimumLength)
								{
									$passwordValidation = "minSize[$minimumLength],ajax[ajaxValidatePassword]";
								}
								else
								{
									$passwordValidation = 'ajax[ajaxValidatePassword]';
								}
								?>
								<?php // Disables autocomplete ?> <input type="password" style="display:none">
								<input type="password" name="password" autocomplete="new-password" size="20" value="" class="form-control validate[<?php echo $passwordValidation;?>]" />
							</div>
						</div>
<?php
					}
				}

				if ($this->config->enable_avatar)
				{
					$avatarExists = false;

					if ($this->item->avatar && file_exists(JPATH_ROOT . '/media/com_osmembership/avatars/' . $this->item->avatar))
					{
						$avatarExists = true;
					?>
						<div class="control-group">
							<div class="control-label">
								<label><?php echo Text::_('OSM_AVATAR'); ?></label>
							</div>
							<div class="controls">
								<img class="oms-avatar" src="<?php echo Uri::root(true) . '/media/com_osmembership/avatars/' . $this->item->avatar; ?>" />
								<div id="osm-delete-avatar-container" style="margin-top: 10px;">
									<label class="checkbox">
										<input type="checkbox" name="delete_avatar" value="1" />
										<?php echo Text::_('OSM_DELETE_AVATAR'); ?>
									</label>
								</div>
							</div>
						</div>
					<?php
					}
					?>
					<div class="control-group">
						<div class="control-label">
							<label><?php echo $avatarExists ? Text::_('OSM_NEW_AVATAR') : Text::_('OSM_AVATAR'); ?></label>
						</div>
						<div class="controls">
							<input type="file" name="profile_avatar" accept="image/*">
						</div>
					</div>
					<?php
				}

				if ($this->config->get('enable_select_show_hide_members_list'))
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo OSMembershipHelperHtml::getFieldLabel('show_on_members_list', Text::_('OSM_SHOW_ON_MEMBERS_LIST')); ?>
						</div>
						<div class="controls">
							<?php echo OSMembershipHelperHtml::getBooleanInput('show_on_members_list', $this->item->show_on_members_list); ?>
						</div>
					</div>
				<?php
				}

				if ($this->config->auto_generate_membership_id)
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>
						</div>
						<div class="controls">
							<input type="text" name="membership_id" value="<?php echo $this->item->membership_id > 0 ? $this->item->membership_id : ''; ?>" class="form-control" size="20" />
						</div>
					</div>
				<?php
				}

				// Fake class mapping to make the layout works well on J4
				$bootstrapHelper->getUi()->addClassMapping('control-group', 'control-group')
					->addClassMapping('control-label', 'control-label')
					->addClassMapping('controls', 'controls');

				foreach ($this->form->getFields() as $field)
				{
					echo $field->getControlGroup($bootstrapHelper);
				}

				if ($this->item->ip_address)
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo Text::_('IP'); ?>
						</div>
						<div class="controls">
							<?php echo $this->item->ip_address; ?>
						</div>
					</div>
				<?php
				}
				?>
			</fieldset>
		</div>
		<div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
			<fieldset class="form-horizontal options-form">
				<legend><?php echo Text::_('OSM_SUBSCRIPTION_INFORMATION'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('OSM_SUBSCRIPTION_START_DATE'); ?>
					</div>
					<div class="controls">
						<?php echo HTMLHelper::_('calendar', $this->item->from_date, 'from_date', 'from_date', $this->datePickerFormat . ' %H:%M:%S'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('OSM_SUBSCRIPTION_END_DATE'); ?>
					</div>
					<div class="controls">
						<?php
						if ($this->item->lifetime_membership || $this->item->to_date == '2099-12-31 23:59:59')
						{
							echo Text::_('OSM_LIFETIME');
						}
						else
						{
							echo HTMLHelper::_('calendar', $this->item->to_date, 'to_date', 'to_date', $this->datePickerFormat . ' %H:%M:%S') ;
						}
						?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('OSM_CREATED_DATE'); ?>
					</div>
					<div class="controls">
						<?php echo HTMLHelper::_('calendar', $this->item->created_date, 'created_date', 'created_date', $this->datePickerFormat . ' %H:%M:%S'); ?>
					</div>
				</div>
				<?php
				if ((int) $this->item->payment_date)
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo  Text::_('OSM_PAYMENT_DATE'); ?>
						</div>
						<div class="controls">
							<?php echo HTMLHelper::_('calendar', $this->item->payment_date, 'payment_date', 'payment_date', $this->datePickerFormat . ' %H:%M:%S'); ?>
						</div>
					</div>
				<?php
				}
				?>
			</fieldset>
			<fieldset class="form-horizontal options-form">
				<legend><?php echo Text::_('OSM_PAYMENT_INFORMATION'); ?></legend>
				<?php
				if ($this->item->setup_fee > 0 || !$this->item->id)
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo  Text::_('OSM_SETUP_FEE'); ?>
						</div>
						<div class="controls">
							<?php
								$input = '<input type="text" class="input-medium form-control" name="setup_fee" value="' . ($this->item->setup_fee > 0 ? round($this->item->setup_fee, $numberDecimals) : '') . '" size="7" />';
								echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
							?>
						</div>
					</div>
				<?php
				}
				?>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('OSM_NET_AMOUNT'); ?>
					</div>
					<div class="controls">
						<?php
						$input = '<input type="text" class="input-medium form-control" name="amount" value="' . ($this->item->amount > 0 ? round($this->item->amount, $numberDecimals) : '') . '" size="7" />';
						echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('OSM_DISCOUNT_AMOUNT'); ?>
					</div>
					<div class="controls">
						<?php
						$input = '<input type="text" class="input-medium form-control" name="discount_amount" value="' . ($this->item->discount_amount > 0 ? round($this->item->discount_amount, $numberDecimals) : '') . '" size="7" />';
						echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('OSM_TAX_AMOUNT'); ?>
					</div>
					<div class="controls">
						<?php
						$input = '<input type="text" class="input-medium form-control" name="tax_amount" value="' . ($this->item->tax_amount > 0 ? round($this->item->tax_amount, $numberDecimals) : '') . '" size="7" />';
						echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						?>
					</div>
				</div>
				<?php

				if (!$this->item->id || $this->item->payment_processing_fee > 0 || OSMembershipHelperSubscription::hasPaymentProcessingFee($this->item->payment_method))
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo  Text::_('OSM_PAYMENT_FEE'); ?>
						</div>
						<div class="controls">
							<?php
							$input = '<input type="text" class="input-medium form-control" name="payment_processing_fee" value="' . ($this->item->payment_processing_fee > 0 ? round($this->item->payment_processing_fee, $numberDecimals) : '') . '" size="7" />';
							echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
							?>
						</div>
					</div>
				<?php
				}
				?>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('OSM_GROSS_AMOUNT'); ?>
					</div>
					<div class="controls">
						<?php
						$input = '<input type="text" class="input-medium form-control" name="gross_amount" value="' . ($this->item->gross_amount > 0 ? round($this->item->gross_amount, $numberDecimals) : '') . '" size="7" />';
						echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('OSM_PAYMENT_METHOD') ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['payment_method'] ; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('OSM_TRANSACTION_ID'); ?>
					</div>
					<div class="controls">
						<input type="text" class="form-control" size="40" name="transaction_id" id="transaction_id" value="<?php echo $this->item->transaction_id ; ?>" />
					</div>
				</div>

				<?php
				if (!empty($this->item->recurring_subscription))
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo Text::_('OSM_SUBSCRIPTION_ID'); ?>
						</div>
						<div class="controls">
							<input type="text" class="form-control" size="40" name="subscription_id" id="subscription_id" value="<?php echo $this->item->subscription_id ; ?>" />
						</div>
					</div>
				<?php
				}

				if ($this->config->show_subscribe_newsletter_checkbox && !$this->item->id)
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo Text::_('OSM_JOIN_NEWSLETTER'); ?>
						</div>
						<div class="controls">
							<input type="checkbox" name="subscribe_to_newsletter" id="subscribe_to_newsletter" value="1" class="form-check-input checkbox" checked />
						</div>
					</div>
				<?php
				}
				?>

				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('OSM_SUBSCRIPTION_STATUS'); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['published'] ; ?>
					</div>
				</div>
				<?php
				if ($this->item->payment_method == 'os_offline_creditcard')
				{
					$params = new Registry($this->item->params);
					?>
					<div class="control-group">
						<div class="control-label">
							<?php echo Text::_('OSM_FIRST_12_DIGITS_CREDITCARD_NUMBER'); ?>
						</div>
						<div class="controls">
							<?php echo $params->get('card_number'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo Text::_('AUTH_CARD_EXPIRY_DATE'); ?>
						</div>
						<div class="controls">
							<?php echo $params->get('exp_date'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo Text::_('AUTH_CVV_CODE'); ?>
						</div>
						<div class="controls">
							<?php echo $params->get('cvv'); ?>
						</div>
					</div>
					<?php
				}
				?>
			</fieldset>
			<?php
			if ($this->item->plan_id > 0)
			{
				$plan = OSMembershipHelperDatabase::getPlan($this->item->plan_id);
			}
			else
			{
				$plan = null;
			}

			if ($this->item->id && $plan && ($plan->send_first_reminder != 0
					|| $plan->send_subscription_end != 0
					|| ($plan->recurring_subscription && str_contains($this->item->payment_method, 'os_offline') && PluginHelper::isEnabled('system', 'mpofflinerecurringinvoice')))
			)
			{
				echo $this->loadTemplate('reminder_emails_info', ['plan' => $plan]);
			}

			if ($this->config->enable_editing_recurring_payment_amounts && $plan && $plan->recurring_subscription && $this->item->id > 0)
			{
				echo $this->loadTemplate('recurring_payment_amounts');
			}

			if (is_file(JPATH_ADMINISTRATOR . '/components/com_osmembership/view/subscription/tmpl/default_custom_settings.php'))
			{
				echo $this->loadTemplate('custom_settings');
			}
			?>
		</div>
		<input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
