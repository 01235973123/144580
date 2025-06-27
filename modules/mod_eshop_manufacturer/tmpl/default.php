<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2011 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

if (EshopHelper::isValidMessage($params->get('pre_text')))
{
	echo $params->get('pre_text');
}
?>
<div class="eshop-manufacturers-slider-container_<?php echo $module->id; ?> splide eshop-manufacturers-grid-items">
	<div class="splide__track">
		<ul class="splide__list">
			<?php
			foreach ($items as $item)
			{
				$viewManufacturerUrl = Route::_(EShopRoute::getManufacturerRoute($item->id));
				?>
					<li class="splide__slide">
						<a href="<?php echo $viewManufacturerUrl; ?>" title="<?php echo $item->manufacturer_name; ?>">
							<img src="<?php echo $item->image; ?>" alt="<?php echo $item->manufacturer_name; ?>" />
						</a>
					</li>
				<?php
			}
			?>
		</ul>
	</div>
</div>
<?php
if (EshopHelper::isValidMessage($params->get('post_text')))
{
	echo $params->get('post_text');
}
?>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		var splide = new Splide('.eshop-manufacturers-slider-container_<?php echo $module->id; ?>', <?php echo json_encode($sliderSettings) ?>);
		splide.mount();
	});
</script>