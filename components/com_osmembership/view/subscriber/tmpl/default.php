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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

HTMLHelper::_('formbehavior.chosen', 'select');

$rootUri = Uri::root(true);

OSMembershipHelperJquery::loadjQuery();

Factory::getApplication()
	->getDocument()
	->addScriptOptions('selectedState', $this->selectedState)
	->getWebAssetManager()
	->useScript('core')
	->addInlineScript(
		'
	var siteUrl = "' . $rootUri . '/";			
'
	)->registerAndUseScript('com_osmembership.membershippro', 'media/com_osmembership/assets/js/membershippro.min.js')
	->registerAndUseScript('com_osmembership.site-subscriber-default', 'media/com_osmembership/js/site-subscriber-default.min.js');

OSMembershipHelper::loadLanguage();
OSMembershipHelperJquery::validateForm();

$bootstrapHelper   = OSMembershipHelperBootstrap::getInstance();
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');

$selectedState = '';
?>
<div id="osm-subscription-add-edit-subscriber" class="osm-container osm-container-j4">
    <h1 class="osm-page-title"><?php echo $this->item->id ? Text::_('OSM_EDIT_SUBSCRIPTION') : Text::_('OSM_ADD_SUBSCRIPTION'); ?></h1>
    <div class="btn-toolbar" id="btn-toolbar">
		<?php echo Toolbar::getInstance('toolbar')->render(); ?>
    </div>
    <form action="<?php echo Route::_('index.php?option=com_osmembership&view=subscriber&Itemid=' . $this->Itemid, false); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" enctype="multipart/form-data" class="<?php echo $bootstrapHelper->getClassMapping('form form-horizontal'); ?>">
            <div class="<?php echo $controlGroupClass; ?>">
                <div class="<?php echo $controlLabelClass; ?>">
                    <?php echo Text::_('OSM_PLAN'); ?><span class="required">&nbsp;*</span>
                </div>
                <div class="<?php echo $controlsClass; ?>">
                    <?php echo $this->lists['plan_id'] ; ?>
                </div>
            </div>
            <div class="<?php echo $controlGroupClass; ?>">
                <div class="<?php echo $controlLabelClass; ?>">
                    <?php echo Text::_('OSM_SELECT_USER'); ?>
                </div>
                <div class="<?php echo $controlsClass; ?>">
                    <?php echo OSMembershipHelper::getUserInput($this->item->user_id, (int) $this->item->id) ; ?>
                </div>
            </div>
            <?php
			if (!$this->item->id)
			{
			?>
                <div class="<?php echo $controlGroupClass; ?>" id="username_container">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo Text::_('OSM_USERNAME'); ?><span class="required">*</span>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <input type="text" name="username" size="20" class="form-control validate[ajax[ajaxUserCall]]<?php echo $bootstrapHelper->getFrameworkClass('uk-input', 1); ?>" value="" />
                        <?php echo Text::_('OSM_USERNAME_EXPLAIN'); ?>
                    </div>
                </div>

                <div class="<?php echo $controlGroupClass; ?>" id="password_container">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo Text::_('OSM_PASSWORD'); ?><span class="required">*</span>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php
						$params        = ComponentHelper::getParams('com_users');
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
                        <input type="password" name="password" size="20" value="" class="form-control validate[<?php echo $passwordValidation;?>]<?php echo $bootstrapHelper->getFrameworkClass('uk-input', 1); ?>" />
                    </div>
                </div>
            <?php
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
                            <?php echo Text::_('OSM_AVATAR'); ?>
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
                        <?php echo $avatarExists ? Text::_('OSM_NEW_AVATAR') : Text::_('OSM_AVATAR'); ?>
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
                <div class="<?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>">
			            <?php echo Text::_('OSM_SHOW_ON_MEMBERS_LIST'); ?>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
			            <?php echo $this->lists['show_on_members_list']; ?>
                    </div>
                </div>
	        <?php
			}

			if ($this->config->auto_generate_membership_id)
			{
			?>
                <div class="<?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <input type="text" name="membership_id" value="<?php echo $this->item->membership_id > 0 ? $this->item->membership_id : ''; ?>"<?php echo $bootstrapHelper->getFrameworkClass('uk-input', 3); ?> size="20" />
                    </div>
                </div>
            <?php
			}

			$fields = $this->form->getFields();

			if (isset($fields['email']))
			{
				$fields['email']->setAttribute('class', 'validate[required,custom[email]]');
			}

			foreach ($fields as $field)
			{
				/* @var MPFFormField $field */
				echo $field->getControlGroup($bootstrapHelper);
			}
			?>
            <div class="<?php echo $controlGroupClass; ?>">
                <div class="<?php echo $controlLabelClass; ?>">
                    <?php echo  Text::_('OSM_CREATED_DATE'); ?>
                </div>
                <div class="<?php echo $controlsClass; ?>">
                    <?php echo HTMLHelper::_('calendar', $this->item->created_date, 'created_date', 'created_date', $this->datePickerFormat . ' %H:%M:%S'); ?>
                </div>
            </div>
            <div class="<?php echo $controlGroupClass; ?>">
                <div class="<?php echo $controlLabelClass; ?>">
                    <?php echo  Text::_('OSM_SUBSCRIPTION_START_DATE'); ?>
                </div>
                <div class="<?php echo $controlsClass; ?>">
                    <?php echo HTMLHelper::_('calendar', $this->item->from_date, 'from_date', 'from_date', $this->datePickerFormat . ' %H:%M:%S'); ?>
                </div>
            </div>
            <div class="<?php echo $controlGroupClass; ?>">
                <div class="<?php echo $controlLabelClass; ?>">
                    <?php echo  Text::_('OSM_SUBSCRIPTION_END_DATE'); ?>
                </div>
                <div class="<?php echo $controlsClass; ?>">
                    <?php
					if ($this->item->lifetime_membership || $this->item->to_date == '2099-12-31 23:59:59')
					{
						echo Text::_('OSM_LIFETIME');
					}
					else
					{
						echo HTMLHelper::_('calendar', $this->item->to_date, 'to_date', 'to_date', $this->datePickerFormat . ' %H:%M:%S');
					}
					?>
                </div>
            </div>
            <?php
			if ($this->item->setup_fee > 0 || !$this->item->id)
			{
			?>
                <div class="<?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo  Text::_('OSM_SETUP_FEE'); ?>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php
							$input = '<input type="text" class="form-control" name="setup_fee" value="' . ($this->item->setup_fee > 0 ? round($this->item->setup_fee, 2) : '') . '" size="7" />';
							echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						?>
                    </div>
                </div>
            <?php
			}
			$showDiscount = false;
			$showTax = false;
			$showPaymentProcessingFee = false;
			?>
            <div class="<?php echo $controlGroupClass; ?>">
                <div class="<?php echo $controlLabelClass; ?>">
                    <?php echo  Text::_('OSM_NET_AMOUNT'); ?>
                </div>
                <div class="<?php echo $controlsClass; ?>">
                    <?php
						$input = '<input type="text" class="form-control" name="amount" value="' . ($this->item->amount > 0 ? round($this->item->amount, 2) : '') . '" size="7" />';
						echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
					?>
                </div>
            </div>
            <?php
			if ($this->item->discount_amount > 0 || !$this->item->id)
			{
				$showDiscount = true;
			?>
                <div class="<?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo  Text::_('OSM_DISCOUNT_AMOUNT'); ?>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php
							$input = '<input type="text" class="form-control" name="discount_amount" value="' . ($this->item->discount_amount > 0 ? round($this->item->discount_amount, 2) : '') . '" size="7" />';
							echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						?>
                    </div>
                </div>
            <?php
			}

			if ($this->item->tax_amount > 0 || !$this->item->id)
			{
				$showTax = true;
			?>
                <div class="<?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo  Text::_('OSM_TAX_AMOUNT'); ?>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php
							$input = '<input type="text" class="form-control" name="tax_amount" value="' . ($this->item->tax_amount > 0 ? round($this->item->tax_amount, 2) : '') . '" size="7" />';
							echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						?>
                    </div>
                </div>
            <?php
			}
			if ($this->item->payment_processing_fee > 0 || !$this->item->id)
			{
				$showPaymentProcessingFee = true;
			?>
                <div class="<?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo  Text::_('OSM_PAYMENT_FEE'); ?>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php
							$input = '<input type="text" class="form-control" name="payment_processing_fee" value="' . ($this->item->payment_processing_fee > 0 ? round($this->item->payment_processing_fee, 2) : '') . '" size="7" />';
							echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						?>
                    </div>
                </div>
            <?php
			}

			if ($showDiscount || $showTax || $showPaymentProcessingFee)
			{
			?>
                <div class="<?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo  Text::_('OSM_GROSS_AMOUNT'); ?>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php
						$input = '<input type="text" class="form-control" name="gross_amount" value="' . ($this->item->gross_amount > 0 ? round($this->item->gross_amount, 2) : '') . '" size="7" />';
						echo $bootstrapHelper->getPrependAddon($input, $this->config->currency_symbol);
						?>
                    </div>
                </div>
            <?php
			}
			?>
            <div class="<?php echo $controlGroupClass; ?>">
                <div class="<?php echo $controlLabelClass; ?>">
                    <?php echo Text::_('OSM_PAYMENT_METHOD') ?>
                </div>
                <div class="<?php echo $controlsClass; ?>">
                    <?php echo $this->lists['payment_method'] ; ?>
                </div>
            </div>
            <div class="<?php echo $controlGroupClass; ?>">
                <div class="<?php echo $controlLabelClass; ?>">
                    <?php echo Text::_('OSM_TRANSACTION_ID'); ?>
                </div>
                <div class="<?php echo $controlsClass; ?>">
                    <input type="text" class="form-control" size="50" name="transaction_id" id="transaction_id" value="<?php echo $this->item->transaction_id ; ?>" />
                </div>
            </div>
		    <?php
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
            <div class="<?php echo $controlGroupClass; ?>">
                <div class="<?php echo $controlLabelClass; ?>">
                    <?php echo Text::_('OSM_SUBSCRIPTION_STATUS'); ?>
                </div>
                <div class="<?php echo $controlsClass; ?>">
                    <?php echo $this->lists['published'] ; ?>
                </div>
            </div>
            <?php
			if ($this->item->payment_method == 'os_offline_creditcard')
			{
				$params = new Registry($this->item->params);
			?>
                <div class="<?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo Text::_('OSM_FIRST_12_DIGITS_CREDITCARD_NUMBER'); ?>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php echo $params->get('card_number'); ?>
                    </div>
                </div>
                <div class="<?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo Text::_('AUTH_CARD_EXPIRY_DATE'); ?>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php echo $params->get('exp_date'); ?>
                    </div>
                </div>
                <div class="<?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>">
                        <?php echo Text::_('AUTH_CVV_CODE'); ?>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php echo $params->get('cvv'); ?>
                    </div>
                </div>
            <?php
			}
			?>
    <div class="clr"></div>
    <input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
