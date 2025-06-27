<?php
/**
 * @package		   Joomla
 * @subpackage	   Membership Pro
 * @author		   Tuan Pham Ngoc
 * @copyright	   Copyright (C) 2012 - 2025 Ossolution Team
 * @license		   GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="row-fluid form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_SELECT_TEMPLATE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['export_template']; ?>
		</div>
	</div>
</div>
