<?php

/**
*   FavSlider Pro
*
*   Responsive and customizable Joomla!3 module
*
*   @version        1.1
*   @link           http://extensions.favthemes.com/favsliderpro
*   @author         FavThemes - http://www.favthemes.com
*   @copyright      Copyright (C) 2012-2017 FavThemes.com. All Rights Reserved.
*   @license        Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
*/

// no direct access

defined('_JEXEC') or die;

require_once(JPATH_ROOT.'/modules/mod_favsliderpro/helpers/favsliderhelper.php');
require_once(JPATH_ROOT.'/modules/mod_favsliderpro/helpers/hikashophelper.php');

// general params

$jquery_load                            = $params->get('jquery_load');

$slideshow_type                         = $params->get('slideshow_type');
$transition_effect                      = $params->get('transition_effect');
$carousel_autorun                       = $params->get('carousel_autorun');
$cycling_speed                          = $params->get('cycling_speed');

$show_image                             = $params->get('show_image');
$show_title                             = $params->get('show_title');
$show_description                       = $params->get('show_description');
$show_readmore                          = $params->get('show_readmore');

$image_width                            = $params->get('image_width');
$slideshow_layout                       = $params->get('slideshow_layout');
$image_border_radius                    = $params->get('image_border_radius');
$video_height                           = $params->get('video_height');
$thumbnails_width                       = $params->get('thumbnails_width');
$thumbnails_height                      = $params->get('thumbnails_height');

$caption_style                          = $params->get('caption_style');
$caption_color                          = $params->get('caption_color');
$caption_center_width                   = $params->get('caption_center_width');
$caption_height                         = $params->get('caption_height');

$title_google_font                      = $params->get('title_google_font');
$title_font_weight                      = $params->get('title_font_weight');
$title_font_style                       = $params->get('title_font_style');
$title_font_size                        = $params->get('title_font_size');
$title_line_height                      = $params->get('title_line_height');
$title_text_align                       = $params->get('title_text_align');
$title_text_transform                   = $params->get('title_text_transform');
$title_font_weight                      = $params->get('title_font_weight');
$title_margin                           = $params->get('title_margin');

$description_google_font                = $params->get('description_google_font');
$description_font_weight                = $params->get('description_font_weight');
$description_font_style                 = $params->get('description_font_style');
$description_text_color                 = $params->get('description_text_color');
$description_text_font_size             = $params->get('description_text_font_size');
$description_text_line_height           = $params->get('description_text_line_height');
$description_text_align                 = $params->get('description_text_align');
$description_text_margin                = $params->get('description_text_margin');

$readmore_google_font                   = $params->get('readmore_google_font');
$readmore_font_weight                   = $params->get('readmore_font_weight');
$readmore_font_style                    = $params->get('readmore_font_style');
$readmore_text_color                    = $params->get('readmore_text_color');
$readmore_text_bg_color                 = $params->get('readmore_text_bg_color');
$readmore_text_bg_color_hover           = $params->get('readmore_text_bg_color_hover');
$readmore_text_font_size                = $params->get('readmore_text_font_size');
$readmore_text_line_height              = $params->get('readmore_text_line_height');
$readmore_text_align                    = $params->get('readmore_text_align');
$readmore_text_padding                  = $params->get('readmore_text_padding');
$readmore_text_transform                = $params->get('readmore_text_transform');
$readmore_border_radius                 = $params->get('readmore_border_radius');

$show_mobile_title                      = $params->get('show_mobile_title');
$show_mobile_description                = $params->get('show_mobile_description');
$show_mobile_readmore                   = $params->get('show_mobile_readmore');
$caption_hide                           = $params->get('caption_hide');

$show_arrows                            = $params->get('show_arrows');
$arrows_position                        = $params->get('arrows_position');
$arrows_align                           = $params->get('arrows_align');
$arrows_color                           = $params->get('arrows_color');
$arrows_bg_color                        = $params->get('arrows_bg_color');
$arrows_border_radius                   = $params->get('arrows_border_radius');

$show_indicators                        = $params->get('show_indicators');
$indicators_color                       = $params->get('indicators_color');
$indicators_active_color                = $params->get('indicators_active_color');
$indicators_style                       = $params->get('indicators_style');
$indicators_align                       = $params->get('indicators_align');

$use_hikashop_category                  = $params->get('use_hikashop_category');
$hikashop_menu_id                       = $params->get('hikashop_menu_id');
$category_number_of_products            = $params->get('category_number_of_products');
$category_order_by                      = $params->get('category_order_by');
$description_limit                      = $params->get('description_limit');
$image_effect                           = $params->get('image_effect');
$caption_effect                         = $params->get('caption_effect');

$hikashophelper = new HikaShopHelper($hikashop_menu_id);

// end general params

// calculate caption width for left/right image
$caption_width = '';
if ($slideshow_layout == 'favsliderpro-image-left' || $slideshow_layout == 'favsliderpro-image-right') {
  if ($image_width == 'favth-col-lg-2 favth-col-md-2 favth-col-sm-2 favth-col-xs-12') {
    $caption_width = 'favth-col-lg-10 favth-col-md-10 favth-col-sm-10 favth-col-xs-12';
  }
  if ($image_width == 'favth-col-lg-3 favth-col-md-3 favth-col-sm-3 favth-col-xs-12') {
    $caption_width = 'favth-col-lg-9 favth-col-md-9 favth-col-sm-9 favth-col-xs-12';
  }
  if ($image_width == 'favth-col-lg-4 favth-col-md-4 favth-col-sm-4 favth-col-xs-12') {
    $caption_width = 'favth-col-lg-8 favth-col-md-8 favth-col-sm-8 favth-col-xs-12';
  }
  if ($image_width == 'favth-col-lg-6 favth-col-md-6 favth-col-sm-6 favth-col-xs-12') {
    $caption_width = 'favth-col-lg-6 favth-col-md-6 favth-col-sm-6 favth-col-xs-12';
  }
}
// end calculate caption width for left/right image

// calculate cat products
$catproducts = array();
if ($use_hikashop_category > 0) {
    $cnt = $hikashophelper->get_cat_products_cnt($use_hikashop_category);
    $catproducts = $hikashophelper->get_category_products($use_hikashop_category, $category_number_of_products,$category_order_by,$description_limit);
    if ($cnt < $category_number_of_products) {
        $category_number_of_products = $cnt;
    }
    if ($category_number_of_products < 1) { $category_number_of_products = 24; }
    $products = (int)$category_number_of_products;
}
// end calculate cat products

// calculate final number of products
if (!isset($products)) { $products = 24; }
// end calculate final number of products

$custom_id = rand(10000,20000);
$jsFavSliderPro = 'window.favsliderproeffects'.$custom_id.' = {';

// product params
$rows_arrays = array();

for ($j=1;$j<=$products;$j++) {

    if ($use_hikashop_category > 0) { if (!isset($catproducts[$j-1])) { continue; } }

    ${'show_slide'.$j}                      = $params->get('show_slide'.$j);
    ${'use_hikashop_product'.$j}            = $params->get('use_hikashop_product'.$j);

    ${'image_effect'.$j}                    = $params->get('image_effect'.$j);
    ${'caption_effect'.$j}                  = $params->get('caption_effect'.$j);

    ${'slide_type'.$j}                      = $params->get('slide_type'.$j);
    ${'upload_image'.$j}                    = $params->get('upload_image'.$j);
    ${'image_alt'.$j}                       = $params->get('image_alt'.$j);
    ${'image_link'.$j}                      = $params->get('image_link'.$j);
    ${'image_link_target'.$j}               = $params->get('image_link_target'.$j);

    ${'video_source'.$j}                    = $params->get('video_source'.$j);
    ${'video_id'.$j}                        = $params->get('video_id'.$j);
    ${'vimeo_thumbnails_url'.$j}            = $params->get('vimeo_thumbnails_url'.$j);

    ${'title_text'.$j}                      = $params->get('title_text'.$j);
    ${'title_color'.$j}                     = $params->get('title_color'.$j);
    ${'title_link'.$j}                      = $params->get('title_link'.$j);
    ${'title_link_target'.$j}               = $params->get('title_link_target'.$j);
    ${'description_text'.$j}                = $params->get('description_text'.$j);
    ${'readmore_text'.$j}                   = $params->get('readmore_text'.$j);
    ${'readmore_link'.$j}                   = $params->get('readmore_link'.$j);
    ${'readmore_link_target'.$j}            = $params->get('readmore_link_target'.$j);

    if ($use_hikashop_category > 0 || ${'use_hikashop_product'.$j} > 0) {

        if ($use_hikashop_category > 0) {

            $l = $j-1; $cnt_arr = $catproducts[$l];
            ${'use_hikashop_product'.$j} = 0;
            ${'image_effect'.$j}                    = $image_effect;
            ${'caption_effect'.$j}                  = $caption_effect;

        } else {

            $cnt_arr = $hikashophelper->get_product_details(${'use_hikashop_product'.$j},$description_limit);

        }

        ${'slide_type'.$j}                      = 'favsliderpro-image';
        ${'upload_image'.$j}                    = $cnt_arr['image'];
        ${'image_alt'.$j}                       = $cnt_arr['product_name'];
        ${'image_link'.$j}                      = $cnt_arr['url'];
        ${'image_link_target'.$j}               = 'self';

        ${'title_text'.$j}                      = $cnt_arr['product_name'].' - '.$cnt_arr['price_str'];
        ${'title_link'.$j}                      = $cnt_arr['url'];
        ${'title_link_target'.$j}               = 'self';

        ${'description_text'.$j}                = $cnt_arr['description_str'];

        ${'readmore_link'.$j}                   = $cnt_arr['url'];
        ${'readmore_link_target'.$j}            = 'self';

        //${'badge_text'.$j}                      = $cnt_arr['badge'];

    }

    if (${'show_slide'.$j} == 1) {
      $rows_arrays[] =  array($j);
    }

    $jsFavSliderPro .= "captioneffect".$j.":'".${'caption_effect'.$j}."',";
    $jsFavSliderPro .= "imageeffect".$j.":'".${'image_effect'.$j}."',";

}

$rows_arrays_cnt = count($rows_arrays);
// end product params

$jsFavSliderPro .= '};'; $jsFavSliderPro = str_replace(',};','};',$jsFavSliderPro);

// Load Bootstrap

if ($jquery_load) { JHtml::_('jquery.framework'); }

// check if favth-bootstrap already loaded
$jhead = JFactory::getDocument();
$lscripts = $jhead->_scripts;
$load_favthb = true;
foreach ($lscripts as $k => $v) { if (strpos($k, 'favth-bootstrap') !== false) { $load_favthb = false; break; } }
// end check if favth-bootstrap already loaded

if ($load_favthb) {
  JHTML::stylesheet('modules/mod_favsliderpro/theme/bootstrap/favth-bootstrap.css');
  JHTML::script('modules/mod_favsliderpro/theme/bootstrap/favth-bootstrap.js');
}
// END Load Bootstrap

// Module CSS
JHTML::stylesheet('modules/mod_favsliderpro/theme/css/favsliderpro.css');
//JHTML::stylesheet('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
// Google Font
JHTML::stylesheet('//fonts.googleapis.com/css?family='.str_replace(" ","+",$title_google_font).':'.$title_font_weight.str_replace("normal","",$title_font_style));
JHTML::stylesheet('//fonts.googleapis.com/css?family='.str_replace(" ","+",$description_google_font).':'.$description_font_weight.str_replace("normal","",$description_font_style));
JHTML::stylesheet('//fonts.googleapis.com/css?family='.str_replace(" ","+",$readmore_google_font).':'.$readmore_font_weight.str_replace("normal","",$readmore_font_style));

if (count($rows_arrays) > 0) {

?>

  <script type="text/javascript">

  <?php echo $jsFavSliderPro; ?>

  // FavSlider Pro Effects
  jQuery(document).ready(function() {

      function favanimate(favSlide) {

        var favSlideNumber = jQuery(favSlide).attr('data-order');
        var favImageContainer = jQuery(favSlide).find('.image-effect');
        var favCaptionContainer = jQuery(favSlide).find('.caption-effect');
        var favImageEffectClass = window.favsliderproeffects<?php echo $custom_id; ?>['imageeffect'+favSlideNumber];
        var favCaptionEffectClass = window.favsliderproeffects<?php echo $custom_id; ?>['captioneffect'+favSlideNumber];

        favImageContainer.removeClass('favshow'); favImageContainer.removeClass(favImageEffectClass); favImageContainer.addClass('favhide');
        favCaptionContainer.removeClass('favshow'); favCaptionContainer.removeClass(favCaptionEffectClass); favCaptionContainer.addClass('favhide');

        jQuery(favSlide).addClass("favshow");

        if (favCaptionEffectClass != 'caption-effect-none' || favImageEffectClass != 'image-effect-none') {

          if (favImageEffectClass == 'image-effect-none') {

            favImageContainer.addClass('favshow');
            window.setTimeout(function(){
              favCaptionContainer.addClass('favshow ' + favCaptionEffectClass);
            }, 500);

          } else if (favCaptionEffectClass == 'caption-effect-none') {

            favCaptionContainer.addClass('favshow');
            window.setTimeout(function(){
              favImageContainer.addClass('favshow ' + favImageEffectClass);
            }, 500);

          } else {

            window.setTimeout(function(){
              favImageContainer.addClass('favshow ' + favImageEffectClass);
              window.setTimeout(function(){
                favCaptionContainer.addClass('favshow ' + favCaptionEffectClass);
              }, 500);
            }, 500);

          }

        } else if (favCaptionEffectClass == 'caption-effect-none' && favImageEffectClass == 'image-effect-none') {

          favImageContainer.addClass('favshow');
          favCaptionContainer.addClass('favshow');

        }

      }

      jQuery('#favsliderpro-container-<?php echo $custom_id; ?> .favth-item').addClass("favhide");
      jQuery('#favsliderpro-container-<?php echo $custom_id; ?> .favth-item .image-effect').addClass("favhide");
      jQuery('#favsliderpro-container-<?php echo $custom_id; ?> .favth-item .caption-effect').addClass("favhide");
      var fslide = jQuery('#favsliderpro-container-<?php echo $custom_id; ?> #favsliderpro-slides').find("[data-order=1]");

      favanimate(fslide);

      // Animation for the slideshow elements inside the carousel
      jQuery('#favsliderpro-container-<?php echo $custom_id; ?>').on('slide.bs.favth-carousel', function(e) {
        if (typeof(e.relatedTarget) !== "undefined") {
          var favSlide = e.relatedTarget;
          favanimate(favSlide);
        }

      });

  });
  // END FavSlider Pro Effects

  </script>

  <style type="text/css">

    #favsliderpro-container-<?php echo $custom_id; ?> .favsliderpro-caption-readmore a.btn:hover,
    #favsliderpro-container-<?php echo $custom_id; ?> .favsliderpro-caption-readmore a.btn:focus {
      background-color: #<?php echo $readmore_text_bg_color_hover; ?>!important;
    }
    #favsliderpro-container-<?php echo $custom_id; ?> #favsliderpro-indicators .favth-carousel-indicators li.favth-active {
      background-color: #<?php echo $indicators_active_color; ?>;
    }
    <?php if($show_title == 0 && $show_description == 0 && $show_readmore == 0) : ?>
      #favsliderpro-container-<?php echo $custom_id; ?> .favsliderpro-carousel .favth-carousel-caption {
        display: none;
      }
    <?php endif; ?>
    /* hide caption on mobile */
    @media (max-width: 767px) {

      <?php if($show_mobile_title == 0) : ?>
        #favsliderpro-container-<?php echo $custom_id; ?> .favsliderpro-caption-title {
          display: none;
        }
      <?php endif; ?>
      <?php if($show_mobile_description == 0) : ?>
        #favsliderpro-container-<?php echo $custom_id; ?> .favsliderpro-caption-description {
          display: none;
        }
      <?php endif; ?>
      <?php if($show_mobile_readmore == 0) : ?>
        #favsliderpro-container-<?php echo $custom_id; ?> .favsliderpro-caption-readmore {
          display: none;
        }
      <?php endif; ?>
      <?php if($show_mobile_title == 0 && $show_mobile_description == 0 && $show_mobile_readmore == 0) : ?>
        #favsliderpro-container-<?php echo $custom_id; ?> .favsliderpro-carousel .favth-carousel-caption {
          display: none;
        }
      <?php endif; ?>

    }
    @media (max-width: <?php echo $caption_hide; ?>) {
      .favsliderpro-carousel .favth-carousel-caption {
        display: none;
      }
    }

  </style>

<div id="favsliderpro-container-<?php echo $custom_id; ?>" class="favth-row favsliderpro" >

    <div id="favsliderpro-carousel-<?php echo $custom_id; ?>" class="favsliderpro-carousel favth-carousel favth-slide <?php echo $slideshow_type; ?> <?php echo (($transition_effect == 'fade') ? 'favth-carousel-fade': ''); ?> <?php echo (($show_arrows == 1) ? $arrows_position: ''); ?> <?php echo (($show_arrows == 1) ? $arrows_align: ''); ?> <?php echo (($show_image == 1) ? $slideshow_layout: ''); ?> <?php echo $caption_style; ?>" data-ride="favth-carousel" data-interval="<?php echo (($carousel_autorun == 0) ? 'false': $cycling_speed); ?>">

      <!-- Wrapper for slides -->
      <?php if ($slideshow_type == 'favsliderpro-basic' || $slideshow_type == 'favsliderpro-thumbnails') { ?>

        <div id="favsliderpro-slides" class="favth-carousel-inner" style="<?php echo (($show_arrows == 1 && $arrows_position == 'favsliderpro-arrows-bottom') ? 'margin-bottom: 90px;': ''); ?><?php echo (($show_arrows == 1 && $arrows_position == 'favsliderpro-arrows-top') ? 'margin-top: 90px;': ''); ?>" role="listbox">

          <?php $l = 1; foreach ($rows_arrays as $k => $v) {

              $order = $k+1;
              $col_class = 'favth-col-lg-12 favth-col-md-12 favth-col-sm-12 favth-col-xs-12';

          ?>

              <div class="favth-item<?php echo (($order == 1) ? ' favth-active favth-clearfix': ''); ?>" data-order="<?php echo $order; ?>">

                <?php // output content
                foreach ($v as $i) {
                ?>

                <div class="<?php echo $col_class; ?> favsliderpro<?php echo $i; ?> favth-clearfix">

                  <div class="favsliderpro <?php echo ${'slide_type'.$i}; ?>">

                    <div class="favsliderpro-images image-effect <?php echo $slideshow_layout; ?> <?php echo (($show_image == 1 && $show_title == 1 && $show_description == 1 && $show_readmore == 1 && $slideshow_layout != 'favsliderpro-image-center') ? $image_width: ''); ?>">

                      <?php if ($show_image == 1) {

                        if ($l > 5) { $l = 1; }

                        echo FavSliderHelper::generate_img_video_str(${'slide_type'.$i}, ${'upload_image'.$i}, ${'image_link'.$i}, ${'image_link_target'.$i}, ${'image_alt'.$i}, ${'video_source'.$i}, ${'video_id'.$i}, $image_border_radius, $video_height, $l);

                      } ?>

                    </div>

                    <div class="favsliderpro-caption <?php echo $caption_style; ?> <?php echo $caption_color; ?> <?php echo (($show_image == 1 && $show_title == 1 && $show_description == 1 && $show_readmore == 1 && $slideshow_layout != 'favsliderpro-image-center') ? $caption_width: ''); ?>" style="<?php echo ((${'slide_type'.$i} == 'favsliderpro-video') ? 'display: none;': ''); ?>">

                      <div class="favth-carousel-caption caption-effect" style="width: <?php echo (($slideshow_layout == 'favsliderpro-image-center') ? $caption_center_width : ''); ?><?php echo (($slideshow_layout != 'favsliderpro-image-center') ? 'initial' : ''); ?>; height: <?php echo (($slideshow_layout == 'favsliderpro-image-center') ? $caption_height : ''); ?><?php echo (($slideshow_layout != 'favsliderpro-image-center') ? 'initial' : ''); ?>; <?php echo (($show_image == 0) ? 'margin-right: 50px; margin-left: 50px;': ''); ?><?php echo (($show_title == 0 && $show_description == 0 && $show_readmore == 0) ? 'padding: 0px;': ''); ?>">

                        <?php if ($show_title == 1) { ?>

                          <h3 id="favsliderpro-caption-title<?php echo $i; ?>" class="favsliderpro-caption-title"
                              style="color: #<?php echo ${'title_color'.$i}; ?>;
                                  font-family: <?php echo $title_google_font; ?>;
                                  font-weight: <?php echo $title_font_weight; ?>;
                                  font-style: <?php echo $title_font_style; ?>;
                                  font-size: <?php echo $title_font_size; ?>;
                                  line-height: <?php echo $title_line_height; ?>;
                                  text-align: <?php echo $title_text_align; ?>;
                                  text-transform: <?php echo $title_text_transform; ?>;
                                  font-weight: <?php echo $title_font_weight; ?>;
                                  margin: <?php echo $title_margin; ?> !important;">

                            <?php //Do not receive link if the link setting is empty
                            if(empty(${'title_link'.$i})) { ?>

                              <?php echo ${'title_text'.$i}; ?>

                            <?php } else { ?>

                              <a href="<?php echo ${'title_link'.$i}; ?>" target="_<?php echo ${'title_link_target'.$i}; ?>"
                                style="color: #<?php echo ${'title_color'.$i}; ?>;">
                                <?php echo ${'title_text'.$i}; ?>
                              </a>

                            <?php } ?>

                          </h3>

                        <?php } ?>

                        <?php if ($show_description == 1) { ?>

                          <p class="favsliderpro-caption-description"
                            style="font-family: <?php echo $description_google_font; ?>;
                            font-weight: <?php echo $description_font_weight; ?>;
                            font-style: <?php echo $description_font_style; ?>;
                            color: #<?php echo $description_text_color; ?>;
                            font-size: <?php echo $description_text_font_size; ?>;
                            line-height: <?php echo $description_text_line_height; ?>;
                            text-align: <?php echo $description_text_align; ?>;
                            margin: <?php echo $description_text_margin; ?> !important;">

                            <?php echo ${'description_text'.$i}; ?>

                          </p>

                        <?php } ?>

                        <?php if ($show_readmore == 1) { ?>

                          <div id="favsliderpro-caption-readmore<?php echo $i; ?>" class="favsliderpro-caption-readmore"
                              style="text-align: <?php echo $readmore_text_align; ?>;">

                              <a style="display: inline-block;
                                    background-color: #<?php echo $readmore_text_bg_color; ?>;
                                    color: #<?php echo $readmore_text_color; ?>;
                                    padding: <?php echo $readmore_text_padding; ?>;
                                    font-family: <?php echo $readmore_google_font; ?>;
                                    font-weight: <?php echo $readmore_font_weight; ?>;
                                    font-style: <?php echo $readmore_font_style; ?>;
                                    font-size: <?php echo $readmore_text_font_size; ?>;
                                    line-height: <?php echo $readmore_text_line_height; ?>;
                                    text-transform: <?php echo $readmore_text_transform; ?>;
                                    -webkit-border-radius: <?php echo $readmore_border_radius; ?>;
                                    -moz-border-radius: <?php echo $readmore_border_radius; ?>;
                                    border-radius: <?php echo $readmore_border_radius; ?>;"
                                  class="btn"
                                  href="<?php echo ${'readmore_link'.$i}; ?>"
                                  target="_<?php echo ${'readmore_link_target'.$i}; ?>">

                                    <?php echo ${'readmore_text'.$i}; ?>

                              </a>

                          </div>

                        <?php } ?>

                      </div>

                    </div>

                  </div>

                </div>

                <?php $l++; } // end output content
                ?>

              </div>

          <?php } ?>

        </div>

      <?php } ?>

      <!-- Thumbnails -->
      <?php if ($slideshow_type == 'favsliderpro-thumbnails') { ?>

        <ol id="favsliderpro-thumbnails" class="favsliderpro-thumbnails favth-carousel-indicators">

          <?php $l = 1; foreach ($rows_arrays as $k => $v) { ?>

            <li data-target="#favsliderpro-carousel-<?php echo $custom_id; ?>" favth-data-slide-to="<?php echo $k; ?>" class="<?php echo (($k == 0) ? 'favth-active': ''); ?>" style="width: <?php echo $thumbnails_width; ?>;">

              <?php // output content
              foreach ($v as $i) {
              ?>

                <?php if ($show_image == 1) { ?>

                  <?php if (${'slide_type'.$i} == 'favsliderpro-image') { ?>

                      <?php if (${'upload_image'.$i}) { ?>

                        <?php if (!empty(${'image_link'.$j})) { ?><a href="<?php echo ${'image_link'.$j}; ?>" target="_<?php echo ${'image_link_target'.$j}; ?>"><?php } ?>

                        <img
                          style="height: <?php echo $thumbnails_height; ?>;
                                -webkit-border-radius: <?php echo $image_border_radius; ?>;
                                -moz-border-radius: <?php echo $image_border_radius; ?>;
                                border-radius: <?php echo $image_border_radius; ?>;"
                          src="<?php echo ${'upload_image'.$i}; ?>"
                          alt="<?php echo ${'image_alt'.$i}; ?>"/>

                          <?php if (!empty(${'image_link'.$j})) { ?></a><?php } ?>

                      <?php } else {

                          if ($l > 5) { $l = 1; }
                          $image_src = 'demo-image'.$l.'.jpg';

                      ?>

                        <img style="height: <?php echo $thumbnails_height; ?>;
                            -webkit-border-radius: <?php echo $image_border_radius; ?>;
                            -moz-border-radius: <?php echo $image_border_radius; ?>;
                            border-radius: <?php echo $image_border_radius; ?>;"
                        src="modules/mod_favsliderpro/demo/<?php echo $image_src; ?>"
                        alt="<?php echo ${'image_alt'.$i}; ?>" />

                      <?php } ?>

                  <?php } ?>

                  <?php if (${'slide_type'.$i} == 'favsliderpro-video') {

                    echo FavSliderHelper::generate_video_thumbnail(${'video_source'.$i}, ${'video_id'.$i}, $thumbnails_width, $thumbnails_height);

                  } ?>

                <?php } ?>

              <?php $l++; } // end output content
              ?>

            </li>

          <?php } ?>

        </ol>

      <?php } ?>

      <!-- Controls -->
      <?php if ($show_arrows == 1) { ?>

        <div id="favsliderpro-arrows">

          <a class="favth-left favth-carousel-control" href="#favsliderpro-carousel-<?php echo $custom_id; ?>"
              role="button"
              data-slide="prev">
            <i class="fa fa-angle-left" aria-hidden="true"
              style="color: #<?php echo $arrows_color; ?>;
                    background-color: #<?php echo $arrows_bg_color; ?>;
                    -webkit-border-radius: <?php echo $arrows_border_radius; ?>;
                    -moz-border-radius: <?php echo $arrows_border_radius; ?>;
                    border-radius: <?php echo $arrows_border_radius; ?>"></i>
            <span class="favth-sr-only">Previous</span>
          </a>
          <a class="favth-right favth-carousel-control" href="#favsliderpro-carousel-<?php echo $custom_id; ?>"
              role="button"
              data-slide="next">
            <i class="fa fa-angle-right" aria-hidden="true"
              style="color: #<?php echo $arrows_color; ?>;
                    background-color: #<?php echo $arrows_bg_color; ?>;
                    -webkit-border-radius: <?php echo $arrows_border_radius; ?>;
                    -moz-border-radius: <?php echo $arrows_border_radius; ?>;
                    border-radius: <?php echo $arrows_border_radius; ?>"></i>
            <span class="favth-sr-only">Next</span>
          </a>

        </div>

      <?php } ?>

      <!-- Indicators -->
      <?php if ($show_indicators == 1 && $slideshow_type != 'favsliderpro-thumbnails') { ?>

        <div id="favsliderpro-indicators" class="favth-clearfix">

          <ol class="favth-carousel-indicators <?php echo $indicators_align; ?>">

            <?php $l = 1; foreach ($rows_arrays as $k => $v) { ?>

              <li data-target="#favsliderpro-carousel-<?php echo $custom_id; ?>" favth-data-slide-to="<?php echo $k; ?>" class="<?php echo (($k == 0) ? 'favth-active': ''); ?> <?php echo $indicators_color; ?> <?php echo $indicators_style; ?>"></li>

            <?php } ?>

          </ol>

        </div>

      <?php } ?>

    </div>

</div>

<?php } ?>
