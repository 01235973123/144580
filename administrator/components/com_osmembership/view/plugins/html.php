<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class OSMembershipViewPluginsHtml extends MPFViewList
{
	/**
	 * Method to add toolbar buttons
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('OSM_PLUGINS_MANAGEMENT'));
		ToolbarHelper::deleteList(Text::_('OSM_PLUGIN_UNINSTALL_CONFIRM'), 'uninstall', 'Uninstall');
		ToolbarHelper::publish('publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('unpublish', 'JTOOLBAR_UNPUBLISH', true);
	}
}
