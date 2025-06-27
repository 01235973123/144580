<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;

?>
<div id="osm-subscription-complete" class="osm-container">
	<?php
	if (isset($this->params) && $this->params->get('show_page_heading', 1))
	{
	?>
		<h1 class="osm-page-title"><?php echo Text::_('OSM_SUBSCRIPTION_COMPLETE'); ?></h1>
	<?php
	}
	?>
	<p class="osm-message"><?php echo $this->message; ?></p>
</div>
<?php
	echo $this->conversionTrackingCode;
