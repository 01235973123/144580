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

?>
<button class="btn" type="button" data-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</button>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('subscriber.batch_mail'); return false;">
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>