<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright	Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

OSMembershipHelperJquery::loadjQuery();
OSMembershipHelper::addLangLinkForAjax($this->item->language);

$wa = Factory::getApplication()
	->getDocument()
	->addScriptOptions('selectedState', $this->selectedState)
	->getWebAssetManager()
	->addInlineScript(
		'
	var siteUrl = "' . Uri::root() . '";			
'
	)
	->registerAndUseScript('com_osmembership.membershippro', 'media/com_osmembership/assets/js/membershippro.min.js');

if ($this->stateType)
{
	$wa->registerAndUseScript('com_osmembership.admin-subscriber-default', 'media/com_osmembership/js/admin-subscriber-default.min.js');
}

if ($this->item->user_id)
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_USERNAME'); ?>
		</div>
		<div class="controls">
			<?php echo $this->item->username; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_PASSWORD'); ?>
		</div>
		<div class="controls">
			<?php // Disables autocomplete ?> <input type="password" style="display:none">
			<input type="password" class="form-control" name="password" autocomplete="new-password" size="20" value="" />
		</div>
	</div>
<?php
}

if ($this->item->membership_id)
{
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelper::formatMembershipId($this->item, $this->config);?>
		</div>
	</div>
<?php
}

$fields = $this->form->getFields();

if (isset($fields['email']))
{
	$fields['email']->setAttribute('class', 'validate[required,custom[email]]');
}

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();

// Fake class mapping to make the layout works well on J4
$bootstrapHelper->getUi()->addClassMapping('control-group', 'control-group')
	->addClassMapping('control-label', 'control-label')
	->addClassMapping('controls', 'controls');

foreach ($fields as $field)
{
	/* @var MPFFormField $field */
	echo $field->getControlGroup($bootstrapHelper);
}