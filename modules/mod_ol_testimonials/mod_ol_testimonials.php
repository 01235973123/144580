<?php
/*------------------------------------------------------------------------
# mod_ol_testimonials Extension
# ------------------------------------------------------------------------
# author    olwebdesign
# copyright Copyright (C) 2019 olwebdesign.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.olwebdesign.com
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

require ModuleHelper::getLayoutPath('mod_ol_testimonials', $params->get('layout', 'default'));
