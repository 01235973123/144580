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
 * @var   array                 $planIds
 * @var   OSMembershipTablePlan $row
 */

$params  = new Registry($row->params);
$planIds = explode(',', $params->get('auto_subscribe_plan_ids', ''));
$planIds = array_filter($planIds);
?>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('auto_subscribe_plan_ids', Text::_('OSM_SELECT_PLANS'), Text::_('OSM_AUTO_SUBSCRIBE_PLAN_IDS_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $options, 'auto_subscribe_plan_ids[]', 'class="advSelect form-select" multiple="multiple" size="10"', 'value', 'text', $planIds)); ?>
	</div>
</div>
