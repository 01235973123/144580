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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

OSMembershipHelperJquery::validateForm();

Factory::getApplication()
	->getDocument()
	->addScriptOptions('selectedState', $this->selectedState)
	->getWebAssetManager()
	->useScript('core')
	->addInlineScript('var siteUrl = "' . OSMembershipHelper::getSiteUrl() . '";')
	->registerAndUseScript('com_osmembership.site-profile-default', 'media/com_osmembership/js/site-profile-default.min.js');

Text::script('OSM_CANCEL_SUBSCRIPTION_CONFIRM', true);

if ($this->config->use_https)
{
	$ssl = 1;
}
else
{
	$ssl = 0;
}

/* @var OSMembershipHelperBootstrap $bootstrapHelper*/
$bootstrapHelper = $this->bootstrapHelper;

// Get mapping classes, make them ready for using
$rowFluidClass     = $bootstrapHelper->getClassMapping('row-fluid');
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$inputPrependClass = $bootstrapHelper->getClassMapping('input-group');
$addOnClass        = $bootstrapHelper->getClassMapping('add-on');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$btnClass          = $bootstrapHelper->getClassMapping('btn');

$fieldSuffix = OSMembershipHelper::getFieldSuffix();
?>
<div id="osm-profile-page" class="osm-container osm-container-j4">
<?php
	if ($this->params->get('show_page_heading', 1))
	{
		if ($this->input->getInt('hmvc_call'))
		{
			$hTag = 'h2';
		}
		else
		{
			$hTag = 'h1';
		}
	?>
        <<?php echo $hTag; ?> class="osm-page-title"><?php echo Text::_('OSM_USER_PROFILE'); ?></<?php echo $hTag; ?>>
    <?php
	}

	if (OSMembershipHelper::isValidMessage($this->params->get('intro_text')))
	{
	?>
		<div class="osm-description osm-page-intro-text <?php echo $this->bootstrapHelper->getClassMapping('clearfix'); ?>">
			<?php echo HTMLHelper::_('content.prepare', $this->params->get('intro_text')); ?>
		</div>
	<?php
	}
?>
<form action="<?php echo Route::_('index.php?option=com_osmembership&Itemid=' . $this->Itemid) ?>" method="post" name="osm_form" id="osm_form" autocomplete="off" enctype="multipart/form-data" class="<?php echo $bootstrapHelper->getClassMapping('form form-horizontal'); ?>">
	<?php
	$numberTabs = 0;
	$pluginExists = false;

	foreach ($this->plugins as $plugin)
	{
		if (!empty($plugin['form']))
		{
			$pluginExists = true;
			$numberTabs = 1;
			break;
		}
	}

	if ($this->params->get('show_edit_profile', 1))
	{
		$numberTabs++;
	}

	if ($this->params->get('show_my_subscriptions', 1))
	{
		$numberTabs++;
	}

	if ($this->params->get('show_subscriptions_history', 1))
	{
		$numberTabs++;
	}

	if (count($this->additionalTabs))
	{
		$numberTabs += count($this->additionalTabs);
	}

	$showTabs = $numberTabs > 1;

	if ($this->params->get('active_tab'))
	{
		$activeTab = $this->params->get('active_tab');
	}
	elseif ($this->params->get('show_edit_profile', 1))
	{
		$activeTab = 'profile-page';
	}
	elseif ($this->params->get('show_my_subscriptions', 1))
	{
		$activeTab = 'my-subscriptions-page';
	}
	else
	{
		$activeTab = 'subscription-history-page';
	}

	if ($showTabs)
	{
		echo HTMLHelper::_( 'uitab.startTabSet', 'osm-profile', ['active' => $activeTab, 'recall' => true]);
	}

	if ($this->params->get('show_edit_profile', 1))
	{
		if ($showTabs)
		{
			echo HTMLHelper::_( 'uitab.addTab', 'osm-profile', 'profile-page', Text::_('OSM_EDIT_PROFILE'));
		}

		$profileLayoutData = [
			'controlGroupClass' => $controlGroupClass,
			'controlLabelClass' => $controlLabelClass,
			'controlsClass' => $controlsClass,
			'bootstrapHelper' => $bootstrapHelper,
			'btnClass' => $btnClass,
		];

		echo $this->loadTemplate('profile', $profileLayoutData);

		if ($showTabs)
		{
			echo HTMLHelper::_( 'uitab.endTab');
		}
	}

	if ($this->params->get('show_my_subscriptions', 1))
	{
		if ($showTabs)
		{
			echo HTMLHelper::_( 'uitab.addTab', 'osm-profile', 'my-subscriptions-page', Text::_('OSM_MY_SUBSCRIPTIONS'));
		}

		echo $this->loadTemplate('subscriptions');

		if ($showTabs)
		{
			echo HTMLHelper::_( 'uitab.endTab');
		}
	}

	if ($this->params->get('show_subscriptions_history', 1))
	{
		if ($showTabs)
		{
			echo HTMLHelper::_( 'uitab.addTab', 'osm-profile', 'subscription-history-page', Text::_('OSM_SUBSCRIPTION_HISTORY'));
		}

		$layoutData = [
			'showPagination' => false,
		];
		echo $this->loadCommonLayout('common/tmpl/subscriptions_history.php', $layoutData);

		if ($showTabs)
		{
			echo HTMLHelper::_( 'uitab.endTab');
		}
	}

	$count = 0 ;

	if ($pluginExists)
	{
		foreach ($this->plugins as $plugin)
		{
			if (empty($plugin['form']))
			{
				continue;
			}

			$count++ ;

			echo HTMLHelper::_( 'uitab.addTab', 'osm-profile', 'tab_' . $count, Text::_($plugin['title']));
			echo $plugin['form'];
			echo HTMLHelper::_( 'uitab.endTab');
		}
	}

	if (count($this->additionalTabs))
	{
		foreach ($this->additionalTabs as $title => $content)
		{
			$count++;

			echo HTMLHelper::_( 'uitab.addTab', 'osm-profile', 'tab_' . $count, Text::_($title));
			echo HTMLHelper::_('content.prepare', $content);
			echo HTMLHelper::_( 'uitab.endTab');
		}
	}

	if ($showTabs)
	{
		echo HTMLHelper::_( 'uitab.endTabSet');
	}
	?>
	<div class="clearfix"></div>
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="profile.update" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php
// Renew Membership
if ($this->params->get('show_renew_options', 1) && $this->item->group_admin_id == 0 && count($this->planIds))
{
?>
	<form action="<?php echo Route::_('index.php?option=com_osmembership&task=register.process_renew_membership&Itemid=' . $this->Itemid, false, $ssl); ?>" method="post" name="osm_form_renew" id="osm_form_renew" autocomplete="off" class="<?php echo $bootstrapHelper->getClassMapping('form form-horizontal'); ?>">
		<h2 class="osm-form-heading"><?php echo Text::_('OSM_RENEW_MEMBERSHIP'); ?></h2>
		<?php echo $this->loadCommonLayout('common/tmpl/renew_options.php');?>
	</form>
<?php
}

// Upgrade Membership
if ($this->params->get('show_upgrade_options', 1) && $this->item->group_admin_id == 0 && !empty($this->upgradeRules))
{
?>
	<form action="<?php echo Route::_('index.php?option=com_osmembership&task=register.process_upgrade_membership&Itemid=' . $this->Itemid, false, $ssl); ?>" method="post" name="osm_form_update_membership" id="osm_form_update_membership" autocomplete="off" class="<?php echo $bootstrapHelper->getClassMapping('form form-horizontal'); ?>">
		<h2 class="osm-form-heading"><?php echo Text::_('OSM_UPGRADE_MEMBERSHIP'); ?></h2>
		<?php
			echo $this->loadCommonLayout('common/tmpl/upgrade_options.php');
		?>
		<div class="form-actions">
			<input type="submit" class="<?php echo $bootstrapHelper->getClassMapping('btn btn-primary'); ?>" value="<?php echo Text::_('OSM_PROCESS_UPGRADE'); ?>"/>
		</div>
	</form>
<?php
}
?>

<form action="<?php echo Route::_('index.php?option=com_osmembership&task=register.process_cancel_subscription&Itemid=' . $this->Itemid, false, $ssl); ?>" method="post" name="osm_form_cancel_subscription" id="osm_form_cancel_subscription" autocomplete="off" class="form form-horizontal">
	<input type="hidden" name="subscription_id" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<form name="osm_logout_form" id="osm_logout_form" action="<?php echo Route::_('index.php?option=com_users&task=user.logout'); ?>" method="post">
    <input type="hidden" name="return" value="<?php echo base64_encode(Uri::root()); ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
</div>