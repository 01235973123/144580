<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2025 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;

/**
 * Layout variables
 *
 * @var array                     $sponsors
 * @var \Joomla\Registry\Registry $params
 */

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->registerAndUseStyle('com_eventbooking.splide-theme', 'media/com_eventbooking/assets/js/splide/css/themes/' . $params->get('theme', 'splide-default.min.css'))
	->registerAndUseStyle('mod_eb_advslider.styles', 'media/mod_eb_advslider/assets/css/styles.css')
	->registerAndUseScript('com_eventbooking.splide', 'media/com_eventbooking/assets/js/splide/js/splide.min.js');

$numberSponsors        = count($sponsors);
$sliderSettings        = EventbookingHelperSlider::getSliderSettings($params, $numberSponsors);
$sliderContainerId     = 'eb-sponsors-slider-' . time() . '-' . mt_rand(1, 1000);
$sponsorContainerClass = 'eb-sponsor-container';
?>
<div class="eb-speakers-slider-container splide" id="<?php echo $sliderContainerId; ?>">
	<div class="splide__track">
		<ul class="splide__list">
			<?php
			foreach ($sponsors as $sponsor)
			{
			?>
				<li class="splide__slide">
					<?php
						echo EventbookingHelperHtml::loadCommonLayout('plugins/sponsor_item.php', ['sponsor' => $sponsor, 'sponsorContainerClass' => $sponsorContainerClass]);
					?>
				</li>
			<?php
			}
			?>
		</ul>
	</div>
</div>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		var splide = new Splide('#<?php echo $sliderContainerId; ?>', <?php echo json_encode($sliderSettings) ?>);
		splide.mount();
	});
</script>