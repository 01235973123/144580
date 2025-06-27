<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.keepalive');

/**@var OSMembershipViewRegisterHtml $this **/

$selectedState = '';

$fields = $this->form->getFields();

if (isset($fields['state']))
{
	$selectedState = $fields['state']->value;
}

$headerText = Text::_('OSM_JOIN_GROUP');

$headerText = str_replace('[PLAN_TITLE]', $this->plan->title, $headerText);

/**@var OSMembershipHelperBootstrap $bootstrapHelper * */
$bootstrapHelper = $this->bootstrapHelper;

$controlGroupClass   = $bootstrapHelper->getClassMapping('control-group');
$inputPrependClass   = $bootstrapHelper->getClassMapping('input-prepend');
$inputAppendClass    = $bootstrapHelper->getClassMapping('input-append');
$addOnClass          = $bootstrapHelper->getClassMapping('add-on');
$controlLabelClass   = $bootstrapHelper->getClassMapping('control-label');
$controlsClass       = $bootstrapHelper->getClassMapping('controls');
$btnClass            = $bootstrapHelper->getClassMapping('btn');
$btnPrimaryClass     = $bootstrapHelper->getClassMapping('btn btn-primary');

$formFormat = $this->config->get('form_format', 'horizontal') ?: 'horizontal';

if ($formFormat == 'horizontal')
{
	$formClass = $bootstrapHelper->getClassMapping('form form-horizontal');
}
else
{
	$formClass = $bootstrapHelper->getClassMapping('form');
}

// Load necessary javascript library
$layoutData = [
	'selectedState'     => $selectedState,
];

$this->loadTemplate('js', $layoutData);
?>
<div id="osm-join-group-page" class="osm-container osm-plan-<?php echo $this->plan->id; ?>">
<?php
if ($this->params->get('show_page_heading', 1))
{
?>
	<h1 class="osm-page-title"><?php echo $headerText; ?></h1>
<?php
}

if (strlen($this->message))
{
?>
    <div class="osm-message clearfix"><?php echo $this->message; ?></div>
<?php
}

// Login form for existing user
echo $this->loadCommonLayout('register/tmpl/default_login.php', ['fields' => $fields]);
?>
<form method="post" name="os_form" id="os_form" action="<?php echo Route::_('index.php?option=com_osmembership&task=group.process&Itemid=' . $this->Itemid, false, $this->config->use_https ? 1 : 0); ?>" enctype="multipart/form-data" autocomplete="off" class="<?php echo $formClass; ?>">
	<?php
	echo $this->loadTemplate('form', ['fields' => $fields]);

	$layoutData = [
		'controlGroupClass' => $controlGroupClass,
		'controlLabelClass' => $controlLabelClass,
		'controlsClass'     => $controlsClass,
	];

	if ($this->config->show_privacy_policy_checkbox || $this->config->show_subscribe_newsletter_checkbox)
	{
		echo $this->loadCommonLayout('register/tmpl/default_gdpr.php', $layoutData);
	}

	echo $this->loadCommonLayout('register/tmpl/default_terms_conditions.php', $layoutData);

	if ($this->showCaptcha)
	{
		if ($this->captchaPlugin == 'recaptcha_invisible')
		{
			$style = ' style="display:none;"';
		}
		else
		{
			$style = '';
		}
	?>
		<div class="<?php echo $controlGroupClass ?> osm-captcha-container">
			<div class="<?php echo $controlLabelClass; ?>"<?php echo $style; ?>>
				<?php echo Text::_('OSM_CAPTCHA'); ?><span class="required">*</span>
			</div>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->captcha;?>
			</div>
		</div>
	<?php
	}
	?>
	<div class="form-actions">
		<input type="submit" class="<?php echo $btnPrimaryClass; ?>" name="btnSubmit" id="btn-submit" value="<?php echo  Text::_('OSM_PROCESS') ;?>">
		<img id="ajax-loading-animation" src="<?php echo Uri::root(true); ?>/media/com_osmembership/ajax-loadding-animation.gif" style="display: none;"/>
	</div>
    <input type="hidden" name="group_id" value="<?php echo $this->group->subscription_code ;?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
</div>