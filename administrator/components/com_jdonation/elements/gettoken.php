<?php 
 /** 
  * @version     1.0 
  * @package     Joom Donation
  * @copyright   Copyright (C) 2023. All rights reserved. 
  * @license     GNU General Public License version 2 or later; see LICENSE.txt 
  * @author      <your_name> http://www.joellipman.com 
  */ 
  
defined('JPATH_BASE') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\FormField;

jimport('joomla.html.html');
jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
  
 /** 
  * Supports an HTML select list of options driven by SQL 
  */ 
 class JFormFieldGetToken extends FormField
 { 
     /** 
      * The form field type. 
      */ 
     public $type = 'gettoken';
  
     /** 
      * Overrides parent's getinput method 
      */ 
     protected function getInput() 
     { 
         // Initialize variables. 
         $html[] = '<strong>To integrate with Satispay, you should fill below inputboxes.</strong>';
		 $html[] = '<BR /><strong>1.</strong>Activation Code';
		 $html[] = '<BR /><strong>2.</strong>Satispay mode';
		 $html[] = '<BR />Then, please save plugin and re-open this plugin for modification, click on this link <a href=\''.Uri::root().'administrator/index.php?option=com_jdonation&task=retrieveSatispayKey\' target=\'_blank\' class=\'btn btn-primary\'>Click here</a>';

         return implode($html); 
     } 
 } 
 ?> 