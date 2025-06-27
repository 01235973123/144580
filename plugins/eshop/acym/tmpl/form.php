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
 * @var array $allLists
 * @var array $listIds
 */
?>
<table class="admintable adminform" style="width: 90%;">
	<tr>
		<td width="220" class="key">
			<?php echo Text::_('PLG_ESHOP_ACYM_LISTS'); ?>
		</td>
		<td>
			<?php echo HTMLHelper::_('select.genericlist', $allLists, 'acymailing6_list_ids[]', 'class="inputbox" multiple="multiple" size="10"', 'id', 'name', $listIds) ?>
		</td>
		<td>
			<?php echo Text::_('PLG_ESHOP_ACYM_LISTS_EXPLAIN'); ?>
		</td>
	</tr>
</table>
