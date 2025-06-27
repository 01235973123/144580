<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012-2015 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;

?>
<div id="osm-subscription-cancel" class="osm-container">
	<h1 class="osm-page-title"><?php echo Text::_('OSM_SUBSCRIPTION_CANCELLED'); ?></h1>
	<p class="osm-message"><?php echo $this->message; ?></p>
</div>