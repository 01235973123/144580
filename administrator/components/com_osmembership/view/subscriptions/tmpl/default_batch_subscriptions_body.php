<?php
/**
 * @package           Joomla
 * @subpackage        Membership Pro
 * @author            Tuan Pham Ngoc
 * @copyright         Copyright (C) 2012 - 2025 Ossolution Team
 * @license           GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="row-fluid form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_EXTEND_DURATION'); ?>
		</div>
		<div class="controls">
			<input class="form-control input-small d-inline-block" type="number" min="1" name="extend_subscription_duration" value="1" step="1"><?php echo $this->lists['extend_subscription_duration_unit']; ?>
		</div>
	</div>
</div>
