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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

// Little command to allow viewing plan data easier without having to edit code during support
if ($this->input->getInt('debug'))
{
	print_r($this->item);
}

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core')
	->useScript('showon')
	->registerAndUseScript('com_osmembership.admin-plan-default', 'media/com_osmembership/js/admin-plan-default.min.js');

$keys = ['OSM_ENTER_PLAN_TITLE', 'OSM_ENTER_SUBSCRIPTION_LENGTH', 'OSM_PRICE_REQUIRED'];
OSMembershipHelperHtml::addJSStrings($keys);

$config       = OSMembershipHelper::getConfig();
$editor       = OSMembershipHelper::getEditor();
$translatable = Multilanguage::isEnabled() && count($this->languages);

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$rowFluid        = $bootstrapHelper->getClassMapping('row-fluid');
$span8           = $bootstrapHelper->getClassMapping('span7');
$span4           = $bootstrapHelper->getClassMapping('span5');
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
	<?php
	echo HTMLHelper::_( 'uitab.startTabSet', 'plan', ['active' => 'basic-information-page', 'recall' => true]);
	echo HTMLHelper::_( 'uitab.addTab', 'plan', 'basic-information-page', Text::_('OSM_BASIC_INFORMATION'));
	?>
		<div class="<?php echo $rowFluid; ?> clearfix">
			<div class="<?php echo $span8; ?> pull-left">
				<?php echo $this->loadTemplate('general', ['editor' => $editor]); ?>
			</div>
			<div class="<?php echo $span4; ?> pull-left" style="display: inline;">
				<?php
					echo $this->loadTemplate('recurring_settings');
					echo $this->loadTemplate('reminders_settings');
					echo $this->loadTemplate('advanced_settings');
					echo $this->loadTemplate('metadata');
				?>
			</div>
		</div>
	<?php
		echo HTMLHelper::_( 'uitab.endTab');

		if (!empty($this->planFieldsForm))
		{
			echo HTMLHelper::_( 'uitab.addTab', 'plan', 'custom-fields-page', Text::_('OSM_CUSTOM_FIELDS'));
			echo $this->loadTemplate('custom_fields');
			echo HTMLHelper::_( 'uitab.endTab');
		}

		echo HTMLHelper::_( 'uitab.addTab', 'plan', 'renew-options-page', Text::_('OSM_RENEW_OPTIONS'));
		echo $this->loadTemplate('renew_options');
		echo HTMLHelper::_( 'uitab.endTab');

		echo HTMLHelper::_( 'uitab.addTab', 'plan', 'upgrade-options-page', Text::_('OSM_UPGRADE_OPTIONS'));
		echo $this->loadTemplate('upgrade_options');
		echo HTMLHelper::_( 'uitab.endTab');

		echo HTMLHelper::_( 'uitab.addTab', 'plan', 'renewal-discounts-page', Text::_('OSM_EARLY_RENEWAL_DISCOUNTS'));
		echo $this->loadTemplate('renewal_discounts');
		echo HTMLHelper::_( 'uitab.endTab');

		if ($this->config->activate_member_card_feature)
		{
			echo HTMLHelper::_( 'uitab.addTab', 'plan', 'member-card-page', Text::_('OSM_MEMBER_CARD_SETTINGS'));
			echo $this->loadTemplate('member_card', ['editor' => $editor]);
			echo HTMLHelper::_( 'uitab.endTab');
		}

		echo HTMLHelper::_( 'uitab.addTab', 'plan', 'messages-page', Text::_('OSM_MESSAGES'));
		echo $this->loadTemplate('messages', ['editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');

		echo HTMLHelper::_( 'uitab.addTab', 'plan', 'reminder-messages-page', Text::_('OSM_REMINDER_MESSAGES'));
		echo $this->loadTemplate('reminder_messages', ['editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');

		if ($translatable)
		{
			echo HTMLHelper::_( 'uitab.addTab', 'plan', 'translation-page', Text::_('OSM_TRANSLATION'));
			echo $this->loadTemplate('translation', ['editor' => $editor]);
			echo HTMLHelper::_( 'uitab.endTab');
		}

		if (count($this->plugins))
		{
			$count = 0 ;

			foreach ($this->plugins as $plugin)
			{
				$count++ ;
				echo HTMLHelper::_( 'uitab.addTab', 'plan', 'tab_' . $count, Text::_($plugin['title']));
				echo $plugin['form'];
				echo HTMLHelper::_( 'uitab.endTab');
			}
		}

		// Add support for custom settings layout
		if (file_exists(__DIR__ . '/default_custom_settings.php'))
		{
			echo HTMLHelper::_( 'uitab.addTab', 'plan', 'custom-settings-page', Text::_('OSM_CUSTOM_SETTINGS'));
			echo $this->loadTemplate('custom_settings', ['editor' => $editor]);
			echo HTMLHelper::_( 'uitab.endTab');
		}

		echo HTMLHelper::_( 'uitab.endTabSet');
	?>
	<div class="clearfix"></div>
	<?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
	<input type="hidden" id="recurring" name="recurring" value="<?php echo (int) $this->item->recurring_subscription;?>" />
</form>