<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 Ossolution Team
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

OSMembershipHelperJquery::validateForm();

/* @var OSMembershipHelperBootstrap $bootstrapHelper */
$bootstrapHelper = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$clearFix          = $bootstrapHelper->getClassMapping('clearfix');

Factory::getApplication()
	->getDocument()
	->addScriptOptions('siteUrl', Uri::root(true))
	->addScriptOptions('selectedState', $this->selectedState)
	->getWebAssetManager()
	->useScript('core')
	->addInlineScript('var siteUrl = "' . OSMembershipHelper::getSiteUrl() . '";')
	->registerAndUseScript('com_osmembership.site-groupmember-default', 'media/com_osmembership/js/site-groupmember-default.min.js');

$fields = $this->form->getFields();
?>
<div id="osm-add-edit-groupmember" class="osm-container osm-container-j4">
    <h1 class="osm-page-title"><?php echo $this->item->id ? Text::_('OSM_EDIT_GROUP_MEMBER') : Text::_('OSM_NEW_GROUP_MEMBER'); ?></h1>
    <div class="btn-toolbar" id="btn-toolbar">
        <?php echo Toolbar::getInstance('toolbar')->render(); ?>
    </div>
    <form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_osmembership&Itemid=' . $this->Itemid, false, 0); ?>" enctype="multipart/form-data" autocomplete="off" class="<?php echo $bootstrapHelper->getClassMapping('form form-horizontal'); ?>">
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>">
                <?php echo  Text::_('OSM_PLAN') ?>
                <span class="required">*</span>
            </div>
            <div class="<?php echo $controlsClass; ?>">
                <?php
					if (isset($this->plan))
					{
						echo $this->plan->title;
					}
					else
					{
						echo $this->lists['plan_id'];
					}
				?>
            </div>
        </div>
        <?php
			if (!$this->item->id)
			{
				$params                  = ComponentHelper::getParams('com_users');
				$passwordValidationRules = 'validate[required';
				$minimumLength           = $params->get('minimum_length', 4);

				if ($minimumLength > 0)
				{
					$passwordValidationRules .= ",minSize[$minimumLength]";
				}

				$passwordValidationRules .= ',ajax[ajaxValidatePassword]]';

				$userType = $this->input->post->getInt('user_type');

				if (!empty($this->config->enable_select_existing_users))
				{
				?>
                    <div class="<?php echo $controlGroupClass; ?>">
                        <div class="<?php echo $controlLabelClass; ?>">
                            <?php echo  Text::_('OSM_USER_TYPE') ?>
                        </div>
                        <div class="<?php echo $controlsClass; ?> osm-user-type-container">
                            <label style="display: inline;">
                                <input type="radio" name="user_type" value="0" style="display: inline;"<?php if ($userType == 0) echo ' checked'; ?>>
                                <?php echo Text::_('OSM_NEW_USER'); ?>
                            </label>
                            <label style="display: inline; padding-left: 5px;">
                                <input type="radio" name="user_type" value="1" style="display: inline;" <?php if ($userType == 1) echo ' checked'; ?>>
                                <?php echo Text::_('OSM_EXISTING_USER'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="<?php echo $controlGroupClass; ?> existing-user">
                        <div class="<?php echo $controlLabelClass; ?>" for="existing_user_username">
                            <?php echo  Text::_('OSM_EXISTING_USER_USERNAME') ?><span class="required">*</span>
                        </div>
                        <div class="<?php echo $controlsClass; ?>">
                            <input type="text" name="existing_user_username" id="existing_user_username" class="form-control validate[required]<?php echo $bootstrapHelper->getFrameworkClass('uk-input', 1) ?>" value="<?php echo $this->input->post->getCmd('existing_user_username'); ?>" size="15" autocomplete="off"/>
                        </div>
                    </div>
                <?php
				}

				if (empty($this->config->use_email_as_username))
				{
				?>
                    <div class="new-user member-existing <?php echo $controlGroupClass; ?>">
                        <div class="<?php echo $controlLabelClass; ?>" for="username1">
                            <?php echo  Text::_('OSM_USERNAME') ?><span class="required">*</span>
                        </div>
                        <div class="<?php echo $controlsClass; ?>">
                            <input type="text" name="username" id="username1" class="form-control validate[required,ajax[ajaxUserCall]]<?php echo $bootstrapHelper->getFrameworkClass('uk-input', 1) ?>" value="<?php echo Factory::getApplication()->getInput()->getString('username', null, 'post'); ?>" size="15" autocomplete="off"/>
                        </div>
                    </div>
                <?php
				}
				else
				{
					$emailField = $fields['email'];
					$cssClass = $emailField->getAttribute('class');
					$cssClass = str_replace('ajax[ajaxEmailCall]', 'ajax[ajaxValidateGroupMemberEmail]', $cssClass);
					$emailField->setAttribute('class', $cssClass);
					echo $emailField->getControlGroup($bootstrapHelper);
					unset($fields['email']);
				}
			?>
                <div class="new-user member-existing <?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>" for="password1">
                        <?php echo  Text::_('OSM_PASSWORD') ?>
                        <span class="required">*</span>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <?php // Disables autocomplete ?> <input type="password" style="display:none">
                        <input value="" autocomplete="new-password" class="form-control <?php echo $passwordValidationRules; ?><?php echo $bootstrapHelper->getFrameworkClass('uk-input', 1) ?>" type="password" name="password1" id="password1" autocomplete="off"/>
                    </div>
                </div>
                <div class="new-user member-existing <?php echo $controlGroupClass; ?>">
                    <div class="<?php echo $controlLabelClass; ?>" for="password2">
                        <?php echo  Text::_('OSM_RETYPE_PASSWORD') ?>
                        <span class="required">*</span>
                    </div>
                    <div class="<?php echo $controlsClass; ?>">
                        <input value="" class="form-control validate[required,equals[password1]]<?php echo $bootstrapHelper->getFrameworkClass('uk-input', 1) ?>" type="password" name="password2" id="password2" />
                    </div>
                </div>
<?php
			}

			if (isset($fields['email']))
			{
				$emailField = $fields['email'];
				$cssClass = $emailField->getAttribute('class');

				if ($this->item->id)
				{
					// No validation
					$cssClass = str_replace(',ajax[ajaxEmailCall]', '', $cssClass);
				}
				else
				{
					$cssClass = str_replace('ajax[ajaxEmailCall]', 'ajax[ajaxValidateGroupMemberEmail]', $cssClass);
				}

				$emailField->setAttribute('class', $cssClass);
			}

			foreach ($fields as $field)
			{
				/* @var MPFFormField $field */
				if ($field->row->show_on_group_member_form)
				{
					echo $field->getControlGroup($bootstrapHelper);
				}
			}
		?>
        <div class="<?php echo $clearFix; ?>">
            <img id="ajax-loading-animation" src="<?php echo Uri::root(true); ?>/media/com_osmembership/ajax-loadding-animation.gif" style="display: none;"/>
        </div>
        <input type="hidden" name="cid[]" value="<?php echo (int) $this->item->id; ?>" />
        <input type="hidden" id="member_id" value="<?php echo (int) $this->item->id; ?>" />
        <input type="hidden" name="task" value="" />
        <?php
		if (isset($this->plan))
		{
		?>
            <input type="hidden" id="plan_id" name="plan_id" value="<?php echo $this->plan->id; ?>" />
        <?php
		}

		echo HTMLHelper::_('form.token');
		?>
    </form>
</div>
