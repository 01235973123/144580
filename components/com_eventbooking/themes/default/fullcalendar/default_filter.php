<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$btnPrimary      = $bootstrapHelper->getClassMapping('btn btn-primary');
$locationIds     = $this->params->get('location_ids', []);
$locationIds     = array_filter(ArrayHelper::toInteger($locationIds));
?>
<div class="filters btn-toolbar eb-search-bar-container clearfix" id="eb-fullcalendar-filter-container">
	<div class="filter-search pull-left">
		<input type="text" name="search" id="filter_search" class="input-large form-control" value="<?php echo htmlspecialchars($this->state->search, ENT_COMPAT, 'UTF-8'); ?>"
		       placeholder="<?php echo Text::_('EB_KEY_WORDS'); ?>"/>
	</div>
	<div class="btn-group pull-left">
		<?php
			if (isset($this->lists['filter_category_id']))
			{
				echo $this->lists['filter_category_id'];
			}

			if (isset($this->lists['filter_location_id']))
			{
				echo $this->lists['filter_location_id'];
			}
		?>
	</div>
	<div class="btn-group pull-left">
		<input type="button" class="<?php echo $btnPrimary; ?> eb-btn-apply-fullcalendar-filter" id="btn-apply-calendar-filter" value="<?php echo Text::_('EB_APPLY_FILTER'); ?>"/>
	</div>
</div>
