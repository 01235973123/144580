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
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('OSM_IMPORT_SUBSCRIBERS_TITLE'));
ToolbarHelper::save('subscription.import');
ToolbarHelper::cancel('subscription.cancel');
?>
<form action="index.php?option=com_osmembership&view=import" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table class="admintable adminform">
		<tr>
			<td class="key">
				<?php echo Text::_('OSM_SUBSCRIBERS_FILE'); ?>
			</td>
			<td>
				<input type="file" name="input_file" size="40" />
			</td>
			<td>
				<?php echo Text::_('OSM_SUBSCRIBERS_FILE_EXPLAIN'); ?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>