<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_ACYMAILING_NEWSLETTER_LISTS'); ?>
		<span class="help"><?php echo Text::_('ESHOP_ACYMAILING_NEWSLETTER_LISTS_HELP'); ?></span>
	</div>
	<div class="controls">
		<?php echo $this->lists['acymailing_list_ids']; ?>
	</div>
</div>

