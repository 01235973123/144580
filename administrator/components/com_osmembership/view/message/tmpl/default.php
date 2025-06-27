<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('OSM_EMAIL_MESSAGES'), 'generic.png');
ToolbarHelper::apply();
ToolbarHelper::save('save');
ToolbarHelper::cancel('cancel');

$config       = OSMembershipHelper::getConfig();
$editor       = OSMembershipHelper::getEditor();
$translatable = Multilanguage::isEnabled() && count($this->languages);

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core');

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);
?>
<form action="index.php?option=com_osmembership&view=message" method="post" name="adminForm" id="adminForm" class="form form-horizontal  osm-messages-form">
	<?php
	echo HTMLHelper::_( 'uitab.startTabSet', 'message', ['active' => 'general-page', 'recall' => true]);

	echo HTMLHelper::_( 'uitab.addTab', 'message', 'general-page', Text::_('OSM_GENERAL_MESSAGES'));
	echo $this->loadTemplate('general', ['editor' => $editor]);
	echo HTMLHelper::_( 'uitab.endTab');

	echo HTMLHelper::_( 'uitab.addTab', 'message', 'renewal-page', Text::_('OSM_RENEWAL_MESSAGES'));
	echo $this->loadTemplate('renewal', ['editor' => $editor]);
	echo HTMLHelper::_( 'uitab.endTab');

	echo HTMLHelper::_( 'uitab.addTab', 'message', 'upgrade-page', Text::_('OSM_UPGRADE_MESSAGES'));
	echo $this->loadTemplate('upgrade', ['editor' => $editor]);
	echo HTMLHelper::_( 'uitab.endTab');

	echo HTMLHelper::_( 'uitab.addTab', 'message', 'recurring-page', Text::_('OSM_RECURRING_MESSAGES'));
	echo $this->loadTemplate('recurring', ['editor' => $editor]);
	echo HTMLHelper::_( 'uitab.endTab');

	echo HTMLHelper::_( 'uitab.addTab', 'message', 'reminder-page', Text::_('OSM_REMINDER_MESSAGES'));
	echo $this->loadTemplate('reminder', ['editor' => $editor]);
	echo HTMLHelper::_( 'uitab.endTab');

	echo HTMLHelper::_( 'uitab.addTab', 'message', 'group-membership-page', Text::_('OSM_GROUP_MEMBERSHIP_MESSAGES'));
	echo $this->loadTemplate('group_membership', ['editor' => $editor]);
	echo HTMLHelper::_( 'uitab.endTab');

	echo HTMLHelper::_( 'uitab.addTab', 'message', 'subscription-payment-page', Text::_('OSM_SUBSCRIPTION_PAYMENT_MESSAGES'));
	echo $this->loadTemplate('subscription_payment', ['editor' => $editor]);
	echo HTMLHelper::_( 'uitab.endTab');

	echo HTMLHelper::_( 'uitab.addTab', 'message', 'sms-page', Text::_('OSM_SMS_MESSAGES'));
	echo $this->loadTemplate('sms', ['editor' => $editor]);
	echo HTMLHelper::_( 'uitab.endTab');

	// Add support for custom messages layout
	if (file_exists(__DIR__ . '/default_custom_messages.php'))
	{
		echo HTMLHelper::_( 'uitab.addTab', 'message', 'custom-messages-page', Text::_('OSM_CUSTOM_MESSAGES'));
		echo $this->loadTemplate('custom_messages', ['config' => $config, 'editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');
	}

	if ($translatable)
	{
		echo HTMLHelper::_( 'uitab.addTab', 'message', 'translation-page', Text::_('OSM_TRANSLATION'));
		echo $this->loadTemplate('translation', ['editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');
	}

	echo HTMLHelper::_( 'uitab.endTabSet');
?>
	<input type="hidden" name="task" value="" />	
</form>