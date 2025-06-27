<?php
/**
* @Copyright   Copyright (C) 2010 BestAddon . All rights reserved.
* @license     GNU General Public License version 2 or later
* @link        http://www.bestaddon.com
**/
defined('_JEXEC') or die;
use Joomla\String\Normalise;

//$modName = Normalise::toCamelCase(basename(dirname(__DIR__)));
$modName = 'ModBaTabs';
$helper = $modName.'Helper'; // Call Helper class
$moduleid = !empty($module->id) ? $module->id : 0;
$modID = 'modID'.$moduleid;
$modData = !empty($module->content) ? json_decode($module->content, true) : $baData;

$jList = $helper::getList($modData, $params);
$helper::getobj($modData, 'data-mode', $dataSelect);
$helper::getobj($modData, $dataSelect, $dataBasic);
$helper::getobj($modData, 'setting-source', $setting);

// RENDER VARIABLES BY ARRAY AND GET DATA OBJECT
$css = '';
$css .= str_replace(['{ID}', '[ID]', 'ID'], $modID, $setting['tagCSS']);

// CHECK AJAX BY PREVIEW($ajaxData) & SITE
$assetPath = ((int)JVERSION >= 4 ? '' : '/').'modules/mod_ba_tabs/assets/front/';
$listCss = [
    'ba-animate'=>$assetPath.'css/animate.min.css',
    $modName.'-css'=>$assetPath.'css/styles.css'
];
$listJs = [
    'ba-tabs-js'=>$assetPath.'js/baTabs.js'
];
$listAsset = array_merge($listCss, $listJs); //'/assets/front/'
$helper::assets($listAsset, empty($ajaxData) ? true : false);
$helper::assets($css, empty($ajaxData) ? true : false);

$options = '{'.
                '"width":"'.(string)$helper::is($setting['width']).'"'.
                ',"height":"auto"'.
                ',"orient":"'.((bool)$helper::is($setting['displayMode']) ? 'horizontal' : 'vertical').'"'.
                ',"defaultid":'.((int)$helper::is($setting['defaultId']) - 1).
                ',"speed":"'.(string)$helper::is($setting['speed']).'"'.
                ',"interval":'.((bool)$helper::is($setting['autoPlay']) ? (int)$helper::is($setting['autoplayDelay']) : 0).
                ',"hoverpause":'.(int)$helper::is($setting['pauseOnHover']).
                ',"event":"'.(string)$helper::is($setting['trigger']).'"'.
                ',"nextPrev":'.(int)$helper::is($setting['nextPrev']).
                ',"keyNav":'.(int)$helper::is($setting['keyNav']).
                ',"effect":"'.(string)$helper::is($setting['effect']).'"'.
                ',"breakPoint":"'.(string)$helper::is($setting['breakPoint']).'"'.
                ',"breakPointWidth":'.(int)$helper::is($setting['breakPointWidth']).
                ',"style":"'.(string)$helper::is($setting['style']).'"'.
            '}';
$list = ($dataSelect == "source-article" && $jList) ? $jList : $dataBasic['children'];
$tabNavs = '';
$tabPanels = '';

//Cusstomized by Giang - start
if ($dataSelect == "source-article" && $jList)
{
	//Source Article
	foreach ($list ?: [] as $key => $item) {
		$tabNavs .= '<li role="presentation"><a href="#baTab'.$key.'" role="tab" class="ba--title"><span>'.(isset($item->title) ? $item->title : (!empty($item['icon']) ? '<i class="'.$item['icon'].'"></i>' : '').$item['header']).'</span></a></li>';
		
		$helper::getobj($modData, $dataSelect, $jData);
		
        $show_title			= (int) $helper::is($jData['show_title']);
        $show_date			= (int) $helper::is($jData['show_date']);
        $show_category		= (int) $helper::is($jData['show_category']);
        $show_author		= (int) $helper::is($jData['show_author']);
        $show_introtext		= (int) $helper::is($jData['show_introtext']);
        $introtext_limit	= (int) $helper::is($jData['introtext_limit']);
        $show_readmore		= (int) $helper::is($jData['show_readmore']);
        $readmore_text		= $helper::is($jData['readmore_text']) ?: 'Readmore';
		
		if ($item->catid)
		{
			$categoryLink  = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid));
		}
		else
		{
			$categoryLink = '';
		}
			
		$maintext = '';	
		$maintext .= '<div class="ba-infor row">';
		
		if ($item->image != '')
		{
			$colClass = "col-md-7";
			$maintext .= '<div class="col-md-5"><img src="'.$item->image.'" /></div>';
		}
		else
		{
			$colClass = "col-md-12";
		}
		
		$maintext .= '<div class="'.$colClass.'">';
		
		if ($show_title)
		{
			$maintext .= '<a href="'.$item->link.'"><h4>'.$item->title.'</h4></a>';
		}
		
		if ($show_date || $show_category)
		{
			$maintext .= '<ul>';
			
			if ($show_date)
			{
				$maintext .= '<li><span class="fa fa-calendar"></span>'.JHtml::_('date', $item->created, 'F d - Y').'</li>';
			}
			if ($show_category)
			{
				$maintext .= '<li><span class="fa fa-folder"></span><a href="'.$categoryLink.'">'.$item->category_title.'</a> </li>';
			}
			
            $maintext .= '<ul>';
		}
		
		if ($show_introtext)
		{
			$maintext .= '<div class="introtext"><p>'.$item->introtext.'</p></div>';
		}
		
		if ($show_readmore)
		{
			$maintext .= '<div class="read-more-link"><a href="'.$item->link.'">'.$readmore_text.'</a><i class="fa fa-chevron-right">fa</i></div>';
		}
		
		$maintext .= '</div>';
		$maintext .= '</div>';
		
		$tabPanels .= '<div role="tabpanel" class="ba--description">'.$maintext.'</div>';
	}
	
	$html = '<div class="baContainer clearfix '.$setting['tagClass'].' '.(empty($ajaxData) ? '' : 'ba-dialog-body').'">'.
            '<div id="ba-'.$modID.'-wrap">'.
                '<div id="'.$modID.'" class="ba--general row'.($helper::is($setting['styleMode']) ? 'custom' : '').'" data-ba-tabs="true" data-options=\''.$options.'\'>';
	$html .=            
						'<div class="ba__panel-tabs col-md-9 ">'.$tabPanels.'</div>'.
						'<nav class="col-md-3" ><ul class="ba__nav-tabs" role="tablist">'.$tabNavs.'</ul></nav>';
	$html .=        '</div>
				</div>
			</div>';
}
else
{
	//Source Basic
	foreach ($list ?: [] as $key => $item) {
		$tabNavs .= '<li role="presentation"><a href="#baTab'.$key.'" role="tab" class="ba--title"><span>'.(isset($item->title) ? $item->title : (!empty($item['icon']) ? '<i class="'.$item['icon'].'"></i>' : '').$item['header']).'</span></a></li>';
		$tabPanels .= '<div role="tabpanel" class="ba--description">'.(isset($item->maintext) ? $item->maintext : $helper::isMod($item['main'], $moduleid)).'</div>';
	}
	
	$html = '<div class="baContainer clearfix '.$setting['tagClass'].' '.(empty($ajaxData) ? '' : 'ba-dialog-body').'">'.
            '<div id="ba-'.$modID.'-wrap">'.
                '<div id="'.$modID.'" class="ba--general '.($helper::is($setting['styleMode']) ? 'custom' : '').'" data-ba-tabs="true" data-options=\''.$options.'\'>';
	$html .=            '<nav><ul class="ba__nav-tabs" role="tablist">'.$tabNavs.'</ul></nav>'.
                    '<div class="ba__panel-tabs">'.$tabPanels.'</div>';
	$html .=        '</div>
            </div>
        </div>';
}
//Cusstomized by Giang - end

echo $html;
