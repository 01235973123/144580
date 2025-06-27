<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**@var OSMembershipHelperBootstrap $bootstrapHelper **/
$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$btnPrimaryClass   = $bootstrapHelper->getClassMapping('btn btn-primary');
$clearFixClass     = $bootstrapHelper->getClassMapping('clearfix');

$formFormat = $this->config->get('form_format', 'horizontal') ?: 'horizontal';

if ($formFormat == 'horizontal')
{
	$formClass = $bootstrapHelper->getClassMapping('form form-horizontal');
}
else
{
	$formClass = $bootstrapHelper->getClassMapping('form');
}

$actionUrl = Route::_('index.php?option=com_users&task=user.login');
$returnUrl = Uri::getInstance()->toString();

Text::script('OSM_ENTER_USERNAME_TO_LOGIN');
Text::script('OSM_ENTER_PASSWORD_TO_LOGIN');
?>
<h3 class="osm-heading" id="osm-existing-user-login"><?php echo Text::_('OSM_EXISTING_USER_LOGIN'); ?></h3>
<div class="<?php echo $controlGroupClass ?>">
    <div class="<?php echo $controlLabelClass; ?>">
        <label for="login_username">
            <?php echo  empty($this->config->use_email_as_username) ? Text::_('OSM_USERNAME') : $fields['email']->title; ?><span class="required">*</span>
        </label>
    </div>
    <div class="<?php echo $controlsClass; ?>">
        <input type="text" name="login_username" id="login_username" class="form-control<?php echo $bootstrapHelper->getFrameworkClass('uk-input', 1); ?>" value=""/>
    </div>
</div>
<div class="<?php echo $controlGroupClass ?>">
    <div class="<?php echo $controlLabelClass; ?>">
        <label for="login_password">
            <?php echo  Text::_('OSM_PASSWORD') ?><span class="required">*</span>
        </label>
    </div>
    <div class="<?php echo $controlsClass; ?>">
	    <?php
	    $passwordCssClass = 'form-control input-large' . $bootstrapHelper->getFrameworkClass('uk-input', 1);

		echo OSMembershipHelperHtml::getPasswordInput('login_password', '', $passwordCssClass);
	    ?>
    </div>
</div>
<?php
if (count($this->extraLoginButtons) > 0)
{
	echo $this->loadCommonLayout('register/tmpl/extra_login_buttons.php');
}
?>
<div class="<?php echo $controlGroupClass ?>">
    <div class="<?php echo $controlsClass; ?>">
        <input type="button" name="btnLoginButton" id="btnLoginButton" value="<?php echo Text::_('OSM_LOGIN'); ?>" class="<?php echo $btnPrimaryClass; ?>" />
    </div>
</div>

<?php
// Show forgot username and password if configured
if ($this->config->show_forgot_username_password)
{
	Factory::getApplication()->getLanguage()->load('com_users');
	$navClass = $bootstrapHelper->getClassMapping('nav');
	$navTabsClass = $bootstrapHelper->getClassMapping('nav-tabs');
	$navStackedClass = $bootstrapHelper->getClassMapping('nav-stacked');
?>
    <div id="osm-forgot-username-password" class="<?php echo $clearFixClass; ?>">
        <ul class="<?php echo $navClass . ' ' . $navTabsClass . ' ' . $navStackedClass; ?>">
            <li>
                <a href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>">
                    <?php echo Text::_('COM_USERS_LOGIN_RESET'); ?></a>
            </li>
            <li>
                <a href="<?php echo Route::_('index.php?option=com_users&view=remind'); ?>">
                    <?php echo Text::_('COM_USERS_LOGIN_REMIND'); ?></a>
            </li>
        <ul>
    </div>
<?php
}

if ($this->config->registration_integration)
{
?>
    <h3 class="osm-heading" id="osm-new-user-register"><?php echo Text::_('OSM_NEW_USER_REGISTER'); ?></h3>
<?php
}
