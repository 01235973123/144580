<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

// Little command to allow viewing event data easier without having to edit code during support
if ($this->input->getInt('debug'))
{
	print_r($this->item);
}

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core')
	->useScript('showon')
	->registerAndUseScript('com_eventbooking.admin-event-default', 'media/com_eventbooking/js/admin-event-default.min.js');

$translatable    = Multilanguage::isEnabled() && count($this->languages);
$editor          = Editor::getInstance(Factory::getApplication()->get('editor'));
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$span8Class      = $bootstrapHelper->getClassMapping('span8');
$span4Class      = $bootstrapHelper->getClassMapping('span4');

$languageKeys = [
	'EB_PLEASE_ENTER_TITLE',
	'EB_CHOOSE_CATEGORY',
	'EB_ENTER_EVENT_DATE',
	'EB_ENTER_RECURRING_INTERVAL',
	'EB_CHOOSE_ONE_DAY',
	'EB_ENTER_DAY_IN_MONTH',
	'EB_ENTER_DAY_IN_MONTH',
	'EB_ENTER_RECURRING_ENDING_SETTINGS',
	'EB_NO_ROW_TO_DELETE',
];

EventbookingHelperHtml::addJSStrings($languageKeys);
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm"
	  class="form form-horizontal" enctype="multipart/form-data">
	<?php echo HTMLHelper::_( 'uitab.startTabSet', 'event', ['active' => 'basic-information-page', 'recall' => true]); ?>
	<?php echo HTMLHelper::_( 'uitab.addTab', 'event', 'basic-information-page', Text::_('EB_BASIC_INFORMATION')); ?>
	<div class="<?php echo $rowFluidClass; ?>">
		<div class="<?php echo $span8Class; ?>">
			<?php echo $this->loadTemplate('general', ['editor' => $editor]); ?>
		</div>
		<div class="<?php echo $span4Class; ?>">
			<?php
			if ($this->config->get('bes_show_group_registration_rates', 1))
			{
				echo $this->loadTemplate('group_rates');
			}

			if ($this->config->activate_recurring_event && (!$this->item->id || $this->item->event_type == 1))
			{
				echo $this->loadTemplate('recurring_settings');
			}

			echo $this->loadTemplate('misc');
			?>
		</div>
	</div>
	<?php

	echo HTMLHelper::_( 'uitab.endTab');

	if ($this->config->get('bes_show_tab_discount_settings', 1))
	{
		echo HTMLHelper::_( 'uitab.addTab', 'event', 'discount-page', Text::_('EB_DISCOUNT_SETTING'));
		echo $this->loadTemplate('discount_settings');
		echo HTMLHelper::_( 'uitab.endTab');
	}

	if ($this->config->event_custom_field)
	{
		echo HTMLHelper::_( 'uitab.addTab', 'event', 'extra-information-page', Text::_('EB_EXTRA_INFORMATION'));
	?>
		<table class="admintable" width="100%">
			<?php
				/* @var \Joomla\CMS\Form\FormField $field */
				foreach ($this->form->getFieldset('basic') as $field)
				{
					echo $field->renderField();
				}
			?>
		</table>
	<?php
		echo HTMLHelper::_( 'uitab.endTab');
	}

	if ($this->config->activate_tickets_pdf)
	{
		echo HTMLHelper::_( 'uitab.addTab', 'event', 'tickets-page', Text::_('EB_TICKETS_SETTINGS'));
		echo $this->loadTemplate('tickets', ['editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');
	}

	if ($this->config->activate_certificate_feature)
	{
		echo HTMLHelper::_( 'uitab.addTab', 'event', 'certificate-page', Text::_('EB_CERTIFICATE_SETTINGS'));
		echo $this->loadTemplate('certificate', ['editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');
	}

	echo HTMLHelper::_( 'uitab.addTab', 'event', 'metadata-page', Text::_('EB_META_DATA'));
	echo $this->loadTemplate('metadata', ['editor' => $editor]);
	echo HTMLHelper::_( 'uitab.endTab');

	if ($this->config->get('bes_show_options_tab', 1))
	{
		echo HTMLHelper::_( 'uitab.addTab', 'event', 'options-page', Text::_('EB_OPTIONS'));
		echo $this->loadTemplate('options', ['editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');
	}

	if ($this->config->get('bes_show_tab_advanced_settings', 1))
	{
		echo HTMLHelper::_( 'uitab.addTab', 'event', 'advance-settings-page', Text::_('EB_ADVANCED_SETTINGS'));
		echo $this->loadTemplate('advanced_settings', ['editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');
	}

	if ($this->config->get('bes_show_tab_messages', 1))
	{
		echo HTMLHelper::_( 'uitab.addTab', 'event', 'messages-page', Text::_('EB_MESSAGES'));
		echo $this->loadTemplate('messages', ['editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');
	}

	if ($translatable)
	{
		echo $this->loadTemplate('translation', ['editor' => $editor]);
	}

	if (count($this->plugins))
	{
		$count = 0;

		foreach ($this->plugins as $plugin)
		{
			$count++;
			echo HTMLHelper::_( 'uitab.addTab', 'event', 'tab_' . $count, Text::_($plugin['title']));
			echo $plugin['form'];
			echo HTMLHelper::_( 'uitab.endTab');
		}
	}

	// Add support for custom settings layout
	if (file_exists(__DIR__ . '/default_custom_settings.php'))
	{
		echo HTMLHelper::_( 'uitab.addTab', 'event', 'custom-settings-page', Text::_('EB_EVENT_CUSTOM_SETTINGS'));
		echo $this->loadTemplate('custom_settings', ['editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');
	}

	echo HTMLHelper::_( 'uitab.endTabSet');
	?>
	<input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" id="activate_recurring_events" value="<?php echo (int) $this->config->activate_recurring_event; ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>