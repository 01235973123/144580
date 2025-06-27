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
use Joomla\CMS\Uri\Uri;

OSMembershipHelper::addLangLinkForAjax();
OSMembershipHelperJquery::loadjQuery();

Factory::getApplication()
	->getDocument()
	->addScriptOptions('MPBaseUrl', Uri::base(true))
	->addScriptOptions('selectedState', $this->selectedState)
	->getWebAssetManager()
	->useScript('core')
	->addInlineScript(
		'
	var siteUrl = "' . Uri::root() . '";			
'
	)->registerAndUseScript('com_osmembership.membershippro', 'media/com_osmembership/assets/js/membershippro.min.js')
	->registerAndUseScript('com_osmembership.admin-groupmember-default', 'media/com_osmembership/js/admin-groupmember-default.min.js');

$keys = [
	'OSM_PLEASE_SELECT_PLAN',
	'OSM_PLEASE_SELECT_GROUP',
	'OSM_PLEASE_ENTER_USERNAME',
	'OSM_PLEASE_ENTER_PASSWORD',
];

OSMembershipHelperHtml::addJSStrings($keys);

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" enctype="multipart/form-data" class="form form-horizontal">
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_PLAN'); ?>
        </div>
        <div class="controls">
            <?php echo $this->lists['plan_id'] ; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_GROUP'); ?>
        </div>
        <div class="controls" id="group_admin_container">
            <?php echo $this->lists['group_admin_id'] ; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->item->id ? Text::_('OSM_USER') : Text::_('OSM_EXISTING_USER'); ?>
        </div>
        <div class="controls">
            <?php echo OSMembershipHelper::getUserInput((int) $this->item->user_id, (int) $this->item->id) ; ?>
        </div>
    </div>
    <?php
	if (!$this->item->id)
	{
	?>
        <div class="control-group" id="username_container">
            <div class="control-label">
                <?php echo Text::_('OSM_USERNAME'); ?>
            </div>
            <div class="controls">
                <input type="text" class="form-control" id="username" name="username" size="20" value="" />
                <?php echo Text::_('OSM_USERNAME_EXPLAIN'); ?>
            </div>
        </div>
        <div class="control-group" id="password_container">
            <div class="control-label">
                <?php echo Text::_('OSM_PASSWORD'); ?>
            </div>
            <div class="controls">
                <input type="password" class="form-control" id="password" name="password" size="20" value="" />
            </div>
        </div>
    <?php
	}

	// Fake class mapping to make the layout works well on J4
	$bootstrapHelper->getUi()
		->addClassMapping('control-group', 'control-group')
		->addClassMapping('control-label', 'control-label')
		->addClassMapping('controls', 'controls');

	foreach ($this->form->getFields() as $field)
	{
		if (!$field->row->show_on_group_member_form)
		{
			continue;
		}

		/* @var MPFFormField $field */
		echo $field->getControlGroup($bootstrapHelper);
	}

	if ($this->item->id)
	{
	?>
        <div class="control-group">
            <div class="control-label">
                <?php echo  Text::_('OSM_CREATED_DATE'); ?>
            </div>
            <div class="controls">
                <?php echo HTMLHelper::_('calendar', $this->item->created_date, 'created_date', 'created_date', $this->datePickerFormat . ' %H:%M:%S') ; ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <?php echo  Text::_('OSM_SUBSCRIPTION_START_DATE'); ?>
            </div>
            <div class="controls">
                <?php echo HTMLHelper::_('calendar', $this->item->from_date, 'from_date', 'from_date', $this->datePickerFormat . ' %H:%M:%S') ; ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <?php echo  Text::_('OSM_SUBSCRIPTION_END_DATE'); ?>
            </div>
            <div class="controls">
                <?php
				if ($this->item->lifetime_membership || $this->item->to_date == '2099-12-31 23:59:59')
				{
					echo Text::_('OSM_LIFETIME');
				}
				else
				{
					echo HTMLHelper::_('calendar', $this->item->to_date, 'to_date', 'to_date', $this->datePickerFormat . ' %H:%M:%S') ;
				}
				?>
            </div>
        </div>
    <?php
	}
	?>
    <div class="clr"></div>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
    <input type="hidden" name="task" value="" />
    <input type="hidden" id="current_group_admin_id" value="<?php echo (int) $this->item->group_admin_id; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>