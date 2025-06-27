<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

/**
 * Layout variables
 *
 * @var MPFConfig $config
 */

$form                    = Form::getInstance('css_classes_map', JPATH_ADMINISTRATOR . '/components/com_osmembership/view/configuration/forms/css_classes_map.xml');
$formData['css_classes_map'] = [];

if ($config->css_classes_map)
{
	$cssClassesMap = json_decode($config->css_classes_map, true);
}
else
{
	$cssClassesMap = [];
}

foreach ($cssClassesMap as $cssClassMap)
{
	$formData['css_classes_map'][] = [
		'class'        => $cssClassMap['class'],
		'mapped_class' => $cssClassMap['mapped_class'],
	];
}

$form->bind($formData);

?>
<p class="text-info"><?php echo Text::_('OSM_CSS_CLASSES_MAP_EXPLAIN'); ?></p>
<?php

foreach ($form->getFieldset() as $field)
{
	echo $field->input;
}