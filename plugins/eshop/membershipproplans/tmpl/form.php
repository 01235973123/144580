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

/**
 * Layout variables
 *
 * @var string $list
 */
?>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('PLG_ESHOP_MEMBERSHIPPRO_PLANS'); ?>
	</div>
	<div class="controls">
		<?php echo $list; ?>
	</div>
</div>
