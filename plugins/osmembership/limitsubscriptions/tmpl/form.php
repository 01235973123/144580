<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Layout variables
 * -----------------
 * @var   OSMembershipTablePlan $row
 */

$params           = new Registry($row->params);
$maxSubscriptions = $params->get('max_subscriptions', '');
?>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('max_subscriptions', Text::_('PLG_OSMEMBERSHIP_MAX_SUBSCRIPTIONS'), Text::_('PLG_OSMEMBERSHIP_MAX_SUBSCRIPTIONS_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" class="form-control input-small" name="max_subscriptions"
		       value="<?php echo $maxSubscriptions; ?>"/>
	</div>
</div>

