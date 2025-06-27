<?php
/**
 * @package        Joomla
 * @subpackage     Events Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

/**
 * Layout variables
 *
 * @var stdClass                  $module
 * @var \Joomla\Registry\Registry $params
 * @var array                     $rows
 * @var int                       $itemId
 * @var array                     $sliderSettings
 */

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->registerAndUseStyle(
		'com_eventbooking.splide-theme',
		'media/com_eventbooking/assets/js/splide/css/themes/' . $params->get('theme', 'splide-default.min.css')
	)
	->registerAndUseStyle('com_eventbooking.categorygrid', 'media/com_eventbooking/assets/css/categorygrid.min.css', ['version' => EventbookingHelper::getInstalledVersion()])
	->registerAndUseScript('com_eventbooking.splide', 'media/com_eventbooking/assets/js/splide/js/splide.min.js');

EventbookingHelper::loadComponentCssForModules();

$cssVariables = [];

if ($params->get('hover_bg_color'))
{
	$cssVariables[] = '--eb-category-box-hover-bg-color: '.$params->get('hover_bg_color');
}

if ($params->get('hover_color'))
{
	$cssVariables[] = '--eb-category-box-hover-color: '.$params->get('hover_color');
}

if (count($cssVariables))
{
	$inlineStyles = ' style="' . implode(';', $cssVariables) . '"';
}
else
{
	$inlineStyles = '';
}

if (EventbookingHelper::isValidMessage($params->get('pre_text')))
{
	echo $params->get('pre_text');
}
?>
<div class="eb-categories-slider-container_<?php echo $module->id; ?> splide eb-categories-grid-items"<?php echo $inlineStyles; ?>>
	<div class="splide__track">
		<ul class="splide__list">
			<?php
			foreach ($rows as $item)
			{
				if ($item->category_detail_url)
				{
					$url = $item->category_detail_url;
				}
				elseif ($Itemid = EventbookingHelperRoute::getCategoriesMenuId($item->id))
				{
					$url = Route::_('index.php?option=com_eventbooking&view=categories&id=' . $item->id . '&Itemid=' . $Itemid);
				}
				else
				{
					$url = Route::_(EventbookingHelperRoute::getCategoryRoute($item->id, $itemId));
				}

				$item->url = $url;

				$layoutData = [
					'item'   => $item,
					'params' => $params,
				];
				?>
					<li class="splide__slide">
						<?php
						echo EventbookingHelperHtml::loadSharedLayout(
							'categorygrid/' . $params->get('category_item_layout', 'default') . '.php',
							$layoutData
						);
						?>
					</li>
				<?php
			}
			?>
		</ul>
	</div>
</div>
<?php
if (EventbookingHelper::isValidMessage($params->get('post_text')))
{
	echo $params->get('post_text');
}
?>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		var splide = new Splide('.eb-categories-slider-container_<?php echo $module->id; ?>', <?php echo json_encode($sliderSettings) ?>);
		splide.mount();
	});
</script>
