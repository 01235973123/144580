<?php

/*------------------------------------------------------------------------
# mod_ol_testimonials Extension
# ------------------------------------------------------------------------
# author    olwebdesign
# copyright Copyright (C) 2021 olwebdesign.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.olwebdesign.com
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

$slide = $params->get('slides');
$cacheFolder = Uri::base(true).'/cache/';
$modID = $module->id;
$modPath = Uri::base(true).'/modules/mod_ol_testimonials/';
$document = Factory::getDocument(); 
$moduleclass_sfx = $params->get('moduleclass_sfx');
$jqueryload = $params->get('jqueryload');
$jpreload = $params->get('jpreload');
$showarrows = $params->get('showarrows');
$customone = $params->get('customone');
$get_fonto = $params->get('get_fonto');
$testimonials_items    = $params->get('testimonials_items');
$title            = $params->get('title');
$img            = $params->get('img');
$info       = $params->get('info');
$name           = $params->get('name');
$bg_color    = $params->get('bg_color');
$rmore           = $params->get('rmore');
$rmoretxt           = $params->get('rmoretxt');
$get_target = $params->get('get_target');
$get_publish = $params->get('get_publish');
if($jqueryload) $document->addScript($modPath.'assets/js/jquery.min.js');
if($jqueryload) $document->addScript($modPath.'assets/js/jquery-noconflict.js');
$document->addScript($modPath.'assets/js/jquery.owl.carousel.js');
$document->addScript($modPath.'assets/js/theme.js');
if($jpreload) $document->addStyleSheet($modPath.'assets/css/jpreload.css');
$document->addStyleSheet($modPath.'assets/css/style.css');
$document->addStyleSheet($modPath.'assets/css/owl.carousel.css');
if($get_fonto) $document->addStyleSheet($modPath.'assets/font-awesome.css');
$document->addStyleDeclaration('.set_testimon { margin:'.$params->get('container_fix',6).'px; }'); 
?>

<?php if($params->get('jpreload')=='1') : ?>
<script type="text/javascript">
function preload(){
document.getElementById("loaded").style.display = "none";
document.getElementById("owl-testimonial").style.display = "block";
}//preloader
window.onload = preload;
</script>
<div id="loaded"></div>
<?php endif; ?>
<div class="owl-carousel <?php echo $params->get('navStyle')?> <?php echo $params->get('navPosit')?> <?php echo $params->get('navRounded')?>" data-dots="false" data-autoplay="<?php echo $params->get('autoplay')?>" data-autoplay-hover-pause="<?php echo $params->get('autoplay-hover-pause')?>" data-autoplay-timeout="<?php echo $params->get('autoplay-timeout')?>" data-autoplay-speed="<?php echo $params->get('autoplay-speed')?>" data-loop="<?php echo $params->get('dataLoop')?>" data-nav="<?php echo $params->get('dataNav')?>" data-nav-speed="<?php echo $params->get('autoplay-speed')?>" data-items="<?php echo $params->get('image_width')?>" data-tablet-landscape="<?php echo $params->get('image_width_tabl')?>" data-tablet-portrait="<?php echo $params->get('image_width_tabp')?>" data-mobile-landscape="<?php echo $params->get('image_width_mobl')?>" data-mobile-portrait="<?php echo $params->get('image_width_mobp')?>">
<?php foreach ($testimonials_items as $item) : ?>
<?php if (!empty($item->get_publish)) : ?>
<div class="set_testimon">
<div class="reviews-block__slide">
<?php if (!empty($item->info)) : ?><div class="reviews-block__text"><?php echo $item->info; ?></div><?php endif;?>
<?php if (!empty($item->rmore)) : ?><a class="read-rmore" href="<?php echo $item->rmore; ?>" target="<?php echo $item->get_target; ?>" ><?php echo $item->rmoretxt; ?></a><?php endif;?>
<div class="reviews-block__person">
<?php if (!empty($item->img)) : ?><div class="reviews-block__person-image"> <img src="<?php echo $item->img; ?>" alt="<?php echo $item->name; ?>"> </div><?php endif;?>
<div class="reviews-block__person-data">
<?php if (!empty($item->name)) : ?><div class="reviews-block__person-name"><?php echo $item->name; ?></div><?php endif;?>
<?php if (!empty($item->title)) : ?><div class="reviews-block__person-role"><?php echo $item->title; ?></div><?php endif;?>
</div>
</div>
</div>	
</div>
<?php endif; ?>
<?php endforeach; ?>
</div>
