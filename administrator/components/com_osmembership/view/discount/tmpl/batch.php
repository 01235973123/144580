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

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core')
	->registerAndUseScript('com_osmembership.admin-discount-default', 'media/com_osmembership/js/admin-discount-default.min.js');

$keys = [
	'OSM_ENTER_TITLE',
	'OSM_ENTER_DISCOUNT_AMOUNT',
];
OSMembershipHelperHtml::addJSStrings($keys);
?>
<form action="index.php?option=com_osmembership&view=discount" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="title" id="title" size="15" maxlength="250"
			       value="<?php echo $this->item->title; ?>"/>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_PLANS'); ?>
        </div>
        <div class="controls">
			<?php echo $this->lists['plan_id']; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('number_days', Text::_('OSM_NUMBER_DAYS'), Text::_('OSM_NUMBER_DAYS_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <input class="form-control" type="number" name="number_days" id="number_days" size="15" maxlength="250"
                   value="<?php echo $this->item->number_days; ?>"/>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_DISCOUNT'); ?>
		</div>
		<div class="controls">
			<input class="form-control d-inline input-small" type="number" name="discount_amount" id="discount_amount" size="10" maxlength="250"
			       value="<?php echo $this->item->discount_amount; ?>"/>&nbsp;&nbsp;<?php echo $this->lists['discount_type']; ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_PUBLISHED'); ?>
        </div>
        <div class="controls">
            <?php echo $this->lists['published']; ?>
        </div>
    </div>
	<div class="clearfix"></div>
	<?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value=""/>
</form>