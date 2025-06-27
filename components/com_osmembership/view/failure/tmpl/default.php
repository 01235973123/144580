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

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
?>
<div id="osm-subscription-complete" class="osm-container">
	<h1 class="osm-page-title"><?php echo Text::_('OSM_SUBSCRIPTION_FAILURE'); ?></h1>
	<form class="<?php echo $bootstrapHelper->getClassMapping('form form-horizontal'); ?>">
		<div class="control-group osm-message">
			<?php echo Text::_('OSM_FAILURE_MESSAGE'); ?>
		</div>
		<div class="control-group">
			<label class="control-label">
				<?php echo  Text::_('OSM_REASON') ?>
			</label>
			<div class="controls">
				<p class="osm-message"><?php echo $this->reason; ?></p>
			</div>
		</div>		
	</form>
</div>