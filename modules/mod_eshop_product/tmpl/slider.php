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

// Load css/js files for sliders
$document->addStyleSheet($rootUri . '/media/com_eshop/assets/js/splide/css/themes/' . $params->get('theme', 'splide-default.min.css'))
	->addScript($rootUri . '/media/com_eshop/assets/js/splide/js/splide.min.js');

$numberItems = count($items);

if ($numberItems)
{
	$sliderSettings = [
		'type'       => 'loop',
		'perPage'    => min($params->get('number_items', 3), $numberItems),
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
		$sliderSettings['breakpoints'][576]['perPage'] = min($numberItemsXs, $numberItems);
	}

	if ($numberItemsSm)
	{
		$sliderSettings['breakpoints'][768]['perPage'] = min($numberItemsSm, $numberItems);
	}

	if ($numberItemsMd)
	{
		$sliderSettings['breakpoints'][992]['perPage'] = min($numberItemsMd, $numberItems);
	}

	if ($numberItemsLg)
	{
		$sliderSettings['breakpoints'][1200]['perPage'] = min($numberItemsLg, $numberItems);
	}
}

if (EshopHelper::isValidMessage($headerText))
{
	?>
	<div class="eshop-header"><?php echo $headerText; ?></div>
	<?php
}
?>
<div class="eshop-categories-slider-container_<?php echo $module->id; ?> splide eshop-categories-grid-items">
	<div class="splide__track">
		<ul class="splide__list">
		<?php
		foreach ($items as $product)
		{
			?>
			<li class="splide__slide">
				<?php
				echo EShopHtmlHelper::loadCommonLayout('common/product.php', array (
					'product'	=> $product,
					'params'		=> $params
				));
				?>
			</li>
			<?php
		}
		?>
		</ul>
	</div>
</div>
<?php
if (EshopHelper::isValidMessage($footerText))
{
	?>
	<div class="eshop-footer"><?php echo $footerText; ?></div>
	<?php
}
?>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		var splide = new Splide('.eshop-categories-slider-container_<?php echo $module->id; ?>', <?php echo json_encode($sliderSettings) ?>);
		splide.mount();
	});
</script>