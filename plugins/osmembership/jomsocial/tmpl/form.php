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

$params            = new Registry($row->params);
$jSGroupIds        = explode(',', $params->get('jomsocial_group_ids', ''));
$jSExpiredGroupIds = explode(',', $params->get('jomsocial_expried_group_ids', ''));
?>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('jomsocial_group_ids', Text::_('PLG_OSMEMBERSHIP_JOMSOCIAL_ASSIGN_TO_GROUPS'), Text::_('PLG_OSMEMBERSHIP_JOMSOCIAL_ASSIGN_TO_GROUPS_EXPLAIN')); ?>
    </div>
    <div class="controls">
	    <?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'jomsocial_group_ids[]', ' multiple="multiple" size="6" ', 'id', 'name', $jSGroupIds)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('jomsocial_expried_group_ids', Text::_('PLG_OSMEMBERSHIP_JOMSOCIAL_REMOVE_FROM_GROUPS'), Text::_('PLG_OSMEMBERSHIP_JOMSOCIAL_REMOVE_FROM_GROUPS_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'jomsocial_expried_group_ids[]', ' multiple="multiple" size="6" ', 'id', 'name', $jSExpiredGroupIds)); ?>
    </div>
</div>
