<?php
/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2012 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;

?>
<div id="osm-payment-complete-page" class="osm-container">
	<h1 class="page-header"><?php echo $this->escape(Text::_('OSM_PAYMENT_COMPLETE')); ?></h1>
	<div id="osm-message" class="osm-message"><?php echo $this->message; ?></div>
</div>