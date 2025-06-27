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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// Load css/js files for sliders
$document->addStyleSheet($rootUri . '/media/com_eshop/assets/js/splide/css/themes/' . $params->get('theme', 'splide-default.min.css'))
	->addScript($rootUri . '/media/com_eshop/assets/js/splide/js/splide.min.js');

$numberCategories = count($categories);

if ($numberCategories)
{
	$sliderSettings = [
		'type'       => 'loop',
		'perPage'    => min($params->get('number_items', 3), $numberCategories),
		'speed'      => (int) $params->get('speed', 300),
		'autoplay'   => (bool) $params->get('autoplay', 1),
		'arrows'     => (bool) $params->get('arrows', 1),
		'pagination' => (bool) $params->get('pagination', 1),
		'gap'        => $params->get('gap', '1em'),
	];

	$numberItemsXs = $params->get('number_items_xs', 0);
	$numberItemsSm = $params->get('number_items_sm', 0);
	$numberItemsMd = $params->get('number_items_md', 0);
	$numberItemsLg = $params->get('number_items_lg', 0);

	if ($numberItemsXs)
	{
		$sliderSettings['breakpoints'][576]['perPage'] = min($numberItemsXs, $numberCategories);
	}

	if ($numberItemsSm)
	{
		$sliderSettings['breakpoints'][768]['perPage'] = min($numberItemsSm, $numberCategories);
	}

	if ($numberItemsMd)
	{
		$sliderSettings['breakpoints'][992]['perPage'] = min($numberItemsMd, $numberCategories);
	}

	if ($numberItemsLg)
	{
		$sliderSettings['breakpoints'][1200]['perPage'] = min($numberItemsLg, $numberCategories);
	}
}

if (EshopHelper::isValidMessage($params->get('pre_text')))
{
	echo $params->get('pre_text');
}
?>
<div class="eshop-categories-slider-container_<?php echo $module->id; ?> splide eshop-categories-grid-items">
	<div class="splide__track">
		<ul class="splide__list">
			<?php
			foreach ($categories as $category)
			{
				$categoryUrl = Route::_(EShopRoute::getCategoryRoute($category->id));
				?>
					<li class="splide__slide">
						<div class="eshop-category-item eshop-category-item-grid-default">
							<div class="eshop-category-image">
		    					<a href="<?php echo $categoryUrl; ?>" title="<?php echo $category->category_page_title != '' ? $category->category_page_title : $category->category_name; ?>">
		    						<img src="<?php echo $category->image; ?>" alt="<?php echo $category->category_alt_image != '' ? $category->category_alt_image : $category->category_name; ?>" />	            
		    					</a>
		    	            </div>
							<div class="eshop-category-information">
								<a href="<?php echo $categoryUrl; ?>" class="eshop-category-link">
									<?php echo $category->category_name; ?>
								</a>
								<?php
								if ($showNumberProducts)
								{
								?>
									<br />
									<span class="<?php echo $bootstrapHelper->getClassMapping('badge badge-info'); ?>"><?php echo EShopHelper::getNumCategoryProducts($category->id, true) ;?> <?php echo Text::_('ESHOP_PRODUCTS') ; ?></span>
								<?php
								}
								?>
							</div>
							<?php
								if ($params->get('show_description', 1))
								{
								?>
									<div class="eshop-category-description">
										<?php
										if ($params->get('category_description_limit'))
										{
											echo HTMLHelper::_('string.truncate', $category->category_desc, $params->get('category_description_limit', 120));
										}
										else
										{
											echo $category->category_desc;
										}
										?>
									</div>
								<?php
								}
							?>
						</div>
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
		var splide = new Splide('.eshop-categories-slider-container_<?php echo $module->id; ?>', <?php echo json_encode($sliderSettings) ?>);
		splide.mount();
	});
</script>