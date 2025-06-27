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

$params                   = new Registry($row->params);
$vmShopperGroupIds        = explode(',', $params->get('vm_shopper_group_ids', ''));
$vmExpiredShopperGroupIds = explode(',', $params->get('vm_expired_shopper_group_ids', ''));

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
    <div class="<?php echo $bootstrapHelper->getClassMapping('span6 pull-left'); ?>">
        <fieldset class="adminform">
            <legend><?php echo Text::_('OSM_WHEN_SUBSCRIPTION_ACTIVE'); ?></legend>
            <div class="control-group">
                <div class="control-label">
					<?php echo Text::_('OSM_ASSIGN_TO_VM_SHOPPER_GROUPS'); ?>
                </div>
                <div class="controls">
					<?php echo HTMLHelper::_('select.genericlist', $options, 'vm_shopper_group_ids[]', ' multiple="multiple" size="6" ', 'value', 'text', $vmShopperGroupIds); ?>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="<?php echo $bootstrapHelper->getClassMapping('span6 pull-left'); ?>">
        <fieldset class="adminform">
            <legend><?php echo Text::_('OSM_WHEN_SUBSCRIPTION_EXPIRED'); ?></legend>
            <div class="control-group">
                <div class="control-label">
					<?php echo Text::_('OSM_REMOVE_FROM_VM_SHOPPER_GROUPS'); ?>
                </div>
                <div class="controls">
					<?php echo HTMLHelper::_('select.genericlist', $options, 'vm_expired_shopper_group_ids[]', ' multiple="multiple" size="6" ', 'value', 'text', $vmExpiredShopperGroupIds); ?>
                </div>
            </div>
        </fieldset>
    </div>
</div>
