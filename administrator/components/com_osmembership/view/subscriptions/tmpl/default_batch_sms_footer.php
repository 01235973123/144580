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
<button class="btn" type="button" data-dismiss="modal" data-bs-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</button>
<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('subscription.batch_sms'); return false;">
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>