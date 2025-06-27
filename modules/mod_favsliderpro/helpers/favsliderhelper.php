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

class FavSliderHelper {

	public static function generate_video_thumbnail($video_source, $video_id, $thumbnails_width, $thumbnails_height) {

		$output = '<span class="favsliderpro-video-thumbnail">';

        if ($video_source == "favsliderpro-video-youtube") {

			$output .= '<img style="width: '.$thumbnails_width.'; height: '.$thumbnails_height.';" src="https://i.ytimg.com/vi/'.$video_id.'/default.jpg">';


		} else if ($video_source == "favsliderpro-video-vimeo") {

            $output .= '<span class="favsliderpro-video-thumbnail">';

            $vimeocurl = curl_init('http://vimeo.com/api/v2/video/'.$video_id.'.json');
            curl_setopt($vimeocurl,CURLOPT_RETURNTRANSFER, TRUE);
            $vimeojson = curl_exec($vimeocurl);
            curl_close($vimeocurl);

            $vimeojsonresponse = json_decode($vimeojson,true);

            $vimeo_thumbnails_url = $vimeojsonresponse[0]['thumbnail_large'];

            $output .= '<img style="width: '.$thumbnails_width.'; height: '.$thumbnails_height.';" src="'.$vimeo_thumbnails_url.'">';

        }

        $output .= '</span>';

        return $output;

	}

	public static function generate_img_video_str($slide_type, $upload_image, $image_link, $image_link_target, $image_alt, $video_source, $video_id, $image_border_radius, $video_height, $l) {

		$output = '';

        if ($slide_type == "favsliderpro-image") {

            // config image

			if (!empty($image_link)) {

                $output .= '<a href="'.$image_link.'" target="_'.$image_link_target.'" >';

            }

            if ($upload_image) {

				$src_str = $upload_image;

			} else {

				$image_src = 'demo-image'.$l.'.jpg';
				$src_str = "modules/mod_favsliderpro/demo/".$image_src;

			}

            $output .=  '<img style="-webkit-border-radius: '.$image_border_radius.';
                                -moz-border-radius: '.$image_border_radius.';
                                border-radius: '.$image_border_radius.';"
                            src="'.$src_str.'"
                            alt="'.$image_alt.'"/>';

			if (!empty($image_link)) {

				$output .= '</a>';

			}

            // end config image

        } else if ($slide_type == "favsliderpro-video") {

            // config video

            if ($video_source == "favsliderpro-video-youtube") {

                $output .= '<iframe src="https://www.youtube.com/embed/'.$video_id.'" allowfullscreen="" height="'.$video_height.'" frameborder="0"></iframe>';

            } else if ($video_source == "favsliderpro-video-vimeo") {

                $output .= '<iframe src="https://player.vimeo.com/video/'.$video_id.'" allowfullscreen="" height="'.$video_height.'" frameborder="0"></iframe>';

            }

            // end config video

        }

        return $output;

	}

}
