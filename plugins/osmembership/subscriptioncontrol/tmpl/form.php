<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Layout variables
 * -----------------
 * @var   array                 $options
 * @var   OSMembershipTablePlan $row
 */

$params = new Registry($row->params);

$expiringPlanIds  = explode(',', $params->get('expiring_plan_ids', ''));
$subscribePlanIds = explode(',', $params->get('subscription_expired_subscribe_plan_ids', ''));

if ($this->app->isClient('site'))
{
	$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
}
else
{
	$bootstrapHelper = OSMembershipHelperHtml::getAdminBootstrapHelper();
}
?>
<div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
	<div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?> pull-left">
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('OSM_WHEN_SUBSCRIPTION_ACTIVE'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('PLG_SUBSCRIPTION_CONTROL_EXPIRING_PLANS'); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'expiring_plan_ids[]', ' multiple="multiple" size="6" class="form-select advSelect" ', 'value', 'text', $expiringPlanIds)); ?>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?> pull-left">
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('OSM_WHEN_SUBSCRIPTION_EXPIRED'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('PLG_SUBSCRIPTION_CONTROL_SUBSCRIBE_PLANS'); ?>
				</div>
				<div class="controls">
					<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'subscription_expired_subscribe_plan_ids[]', ' multiple="multiple" size="6" class="form-select advSelect" ', 'value', 'text', $subscribePlanIds)); ?>
				</div>
			</div>
		</fieldset>
	</div>
</div>

