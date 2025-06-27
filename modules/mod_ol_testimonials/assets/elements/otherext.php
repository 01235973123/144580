<?php
/**
* @version 4.0.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @copyright (c) 2012 Olwebdesign. All Rights Reserved.
* @author Olwebdesign http://www.olwebdesign.com
* 
*/

defined('_JEXEC') or die;

class JFormFieldOtherExt extends JFormField
{
protected $type = 'otherext';

protected function getLabel()
{
return '';
}

protected function getInput()
{
$otherextLink = 'https://www.olwebdesign.com/joomla-extensions.html';

$document = JFactory::getDocument();

// Add styles
$style = 'a.mx_info_button[target="_blank"]::before {' . 'content: ""!important;' . '}';
$style .= 'a.mx_info_button {' . 'margin-left: 9px;' . '}';

$version = new JVersion;

if ($version->isCompatible('4.0')) {
$document->addStyleDeclaration($style);
}
$otherext = '<div class="btn-wrapper" id="toolbar-pro"><a href="' . $otherextLink . '" title="Olwebdesign Other Extensions" target="_blank" class="mx_info_button"><button class="btn btn-small btn-primary"><span class="icon-cube icon-white" aria-hidden="true"></span> Olwebdesign Other Extensions</button></a></div>';
$document = JFactory::getDocument();
$scriptDeclaration = 'jQuery(function($){$("#toolbar").append(\'' . $otherext . '\');});';
$document->addScriptDeclaration($scriptDeclaration);

return '';
}
}
