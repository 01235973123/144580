<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Layout variables
 *
 * @var OSMembershipHelperBootstrap $bootstrapHelper
 * @var string                      $controlGroupClass
 * @var string                      $controlLabelClass
 * @var string                      $controlsClass
 */

$fields = $this->form->getFields();

if ($this->config->enable_avatar)
{
	$avatarExists = false;

	if ($this->item->avatar && file_exists(JPATH_ROOT . '/media/com_osmembership/avatars/' . $this->item->avatar))
	{
		$avatarExists = true;
	?>
		<div id="field_existing_profile_avatar" class="<?php echo $controlGroupClass; ?>">
			<div class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('OSM_AVATAR'); ?>
			</div>
			<div class="<?php echo $controlsClass; ?>">
				<img class="oms-avatar" src="<?php echo Uri::base(true) . '/media/com_osmembership/avatars/' . $this->item->avatar; ?>"/>
                <div id="osm-delete-avatar-container">
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
	<div id="field_upload_profile_avatar" class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo $avatarExists ? Text::_('OSM_NEW_AVATAR') : Text::_('OSM_AVATAR'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input type="file" name="profile_avatar" accept="image/*">
		</div>
	</div>
	<?php
}

if (isset($this->lists['show_on_members_list']))
{
?>
    <div id="field_show_on_members_list_control" class="<?php echo $controlGroupClass; ?>">
        <div class="<?php echo $controlLabelClass; ?>">
            <?php echo Text::_('OSM_SHOW_ON_MEMBERS_LIST'); ?>
        </div>
        <div class="<?php echo $controlsClass; ?>">
            <?php echo $this->lists['show_on_members_list']; ?>
        </div>
    </div>
<?php
}

if ($this->item->user_id)
{
	$params = ComponentHelper::getParams('com_users');
	$validationRules = [];
	$minimumLength = $params->get('minimum_length', 4);

	if ($minimumLength)
	{
		$validationRules[] = "minSize[$minimumLength]";
	}

	$validationRules[] = 'ajax[ajaxValidatePassword]';

	if (count($validationRules))
	{
		$class = ' class="validate[' . implode(',', $validationRules) . ']' . $this->bootstrapHelper->getFrameworkClass('uk-input', 1) . '"';
	}
	else
	{
		$class = $this->bootstrapHelper->getFrameworkClass('uk-input', 3);
	}
	?>
	<div  id="field_username" class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php
			if ($this->config->use_email_as_username && isset($fields['email']))
			{
				echo $fields['email']->label;
			}
			else
			{
				echo Text::_('OSM_USERNAME');
			}
			?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php
				if ($params->get('change_login_name'))
				{
					if ($this->config->use_email_as_username
						&& isset($fields['email']))
					{
						if ($fields['email']->row->can_edit_on_profile)
						{
							echo $fields['email']->getInput($bootstrapHelper);
						}
						else
						{
							echo $this->item->username;
						}

						unset($fields['email']);
					}
					else
					{
					?>
						<input type="text" name="username" id="username1" class="validate[required,minSize[2],ajax[ajaxUserCall]]<?php echo $this->bootstrapHelper->getFrameworkClass('uk-input', 1); ?> form-control" value="<?php echo $this->escape($this->input->post->getUsername('username', $this->item->username)); ?>" size="15" autocomplete="off"/>
					<?php
					}
				}
				else
				{
					echo $this->item->username;
				}
			?>
		</div>
	</div>
	<div id="field_password" class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('OSM_PASSWORD'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo OSMembershipHelperHtml::getPasswordInput('password', '', 'form-control'); ?>
		</div>
	</div>
	<div id="field_password2" class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
            <?php echo  Text::_('OSM_RETYPE_PASSWORD') ?>
            <span class="required">*</span>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php
			$passwordCssClass = 'form-control validate[equals[password]]' . $this->bootstrapHelper->getFrameworkClass('uk-input', 1);

			echo OSMembershipHelperHtml::getPasswordInput('password2', '', $passwordCssClass);
			?>
		</div>
	</div>
<?php
}

if ($this->item->membership_id)
{
?>
	<div id="field_membership_id" class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo OSMembershipHelper::formatMembershipId($this->item, $this->config); ?>
		</div>
	</div>
<?php
}

foreach ($fields as $field)
{
	if (!$field->row->show_on_user_profile)
	{
		continue;
	}

	/* @var MPFFormField $field*/
	if (($field->fee_field || !$field->row->can_edit_on_profile) && !in_array($field->type, ['Heading', 'Message']))
	{
		echo $field->getOutput(true, $bootstrapHelper);
	}
	else
	{
		echo $field->getControlGroup($bootstrapHelper);
	}
}
?>
<div class="form-actions">
	<input type="submit" class="<?php echo $bootstrapHelper->getClassMapping('btn btn-primary'); ?>" value="<?php echo Text::_('OSM_UPDATE'); ?>" id="osm-btn-update-profile"/>
    <input type="button" class="<?php echo $bootstrapHelper->getClassMapping('btn btn-primary'); ?>" value="<?php echo Text::_('OSM_LOGOUT'); ?>" onclick="document.osm_logout_form.submit();" id="osm-btn-logout" />
</div>
