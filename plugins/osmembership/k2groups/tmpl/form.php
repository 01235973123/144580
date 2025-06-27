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

$params           = new Registry($row->params);
$k2GroupId        = $params->get('k2_group_id', '');
$k2ExpiredGroupId = $params->get('k2_expired_group_id', '');
?>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('k2_group_id', Text::_('PLG_OSMEMBERSHIP_K2GROUP_ASSIGN_TO_GROUP'), Text::_('PLG_OSMEMBERSHIP_K2GROUP_ASSIGN_TO_GROUP_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'k2_group_id', '', 'value', 'text', $k2GroupId)); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('k2_expired_group_id', Text::_('PLG_OSMEMBERSHIP_K2GROUP_SUBSCRIPTION_EXPIRED_ASSIGN_TO_GROUPS'), Text::_('PLG_OSMEMBERSHIP_K2GROUP_SUBSCRIPTION_EXPIRED_ASSIGN_TO_GROUPS_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'k2_expired_group_id', '', 'value', 'text', $k2ExpiredGroupId)); ?>
	</div>
</div>
