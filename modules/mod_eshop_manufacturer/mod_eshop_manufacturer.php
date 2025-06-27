<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2011 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

// Register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eshop/libraries/bootstrap.php';

$thumbnailWidth      = $params->get('image_width', 150);
$thumbnailHeight     = $params->get('image_height', 150);

$items = modEShopManufacturerHelper::getItems($params);

//Resize manufacturer images
for ($i = 0; $n = count($items), $i < $n; $i++)
{
	$item = $items[$i];

	// Image
	if ($item->manufacturer_image && is_file((JPATH_ROOT . '/media/com_eshop/manufacturers/' . $item->manufacturer_image)))
	{
		$image = EShopHelper::resizeImage(
			$item->manufacturer_image,
			JPATH_ROOT . '/media/com_eshop/manufacturers/',
			$thumbnailWidth,
			$thumbnailHeight
		);
	}
	else
	{
		$image = EShopHelper::resizeImage('no-image.png', JPATH_ROOT . '/media/com_eshop/manufacturers/', $thumbnailWidth, $thumbnailHeight);
	}

	$items[$i]->image = Uri::base() . 'media/com_eshop/manufacturers/resized/' . $image;
}

$document = Factory::getApplication()->getDocument();
$rootUri  = Uri::root(true);

//Load CSS of component
EShopHelper::loadComponentCssForModules();

// Load css/js files for sliders
$document->addStyleSheet($rootUri . '/media/com_eshop/assets/js/splide/css/themes/' . $params->get('theme', 'splide-default.min.css'))
	->addScript($rootUri . '/media/com_eshop/assets/js/splide/js/splide.min.js');

$numberManufacturers = count($items);

if ($numberManufacturers)
{
	$sliderSettings = [
		'type'       => 'loop',
		'perPage'    => min($params->get('number_items', 3), $numberManufacturers),
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
		$sliderSettings['breakpoints'][576]['perPage'] = min($numberItemsXs, $numberManufacturers);
	}

	if ($numberItemsSm)
	{
		$sliderSettings['breakpoints'][768]['perPage'] = min($numberItemsSm, $numberManufacturers);
	}

	if ($numberItemsMd)
	{
		$sliderSettings['breakpoints'][992]['perPage'] = min($numberItemsMd, $numberManufacturers);
	}

	if ($numberItemsLg)
	{
		$sliderSettings['breakpoints'][1200]['perPage'] = min($numberItemsLg, $numberManufacturers);
	}

	require ModuleHelper::getLayoutPath('mod_eshop_manufacturer', $params->get('layout', 'default'));
}