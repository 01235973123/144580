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
<div id="osm-renew-options-page" class="osm-container">
	<?php
		if ($this->params->get('show_page_heading', 1))
		{
		?>
			<h1 class="osm-page-title"><?php echo Text::_('OSM_RENREW_MEMBERSHIP'); ?></h1>
		<?php
		}
	?>
		<p class="text-info"><?php echo Text::_('OSM_NO_RENEW_OPTIONS_AVAILABLE'); ?></p>
</div>