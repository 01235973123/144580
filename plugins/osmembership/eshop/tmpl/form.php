<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Layout variables
 *
 * @var array $options
 * @var int   $customerGroupId
 */
?>

<table class="admintable adminform" style="width: 90%;">
	<tr>
		<td width="220" class="key">
			<?php echo  Text::_('PLG_OSMEMBERSHIP_ESHOP_ASSIGN_TO_CUSTOMER_GROUP'); ?>
		</td>
		<td>
			<?php
			echo HTMLHelper::_('select.genericlist', $options, 'eshop_customer_group_id', '', 'id', 'customergroup_name', $customerGroupId);
			?>
		</td>
		<td>
			<?php echo Text::_('PLG_OSMEMBERSHIP_ESHOP_ASSIGN_TO_CUSTOMER_GROUP_EXPLAIN'); ?>
		</td>
	</tr>
</table>
