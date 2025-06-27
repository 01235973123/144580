<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die ();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class DonationViewPluginsHtml extends OSFViewList
{

	public function addToolbar()
	{
		ToolbarHelper::title(Text::_('JD_PAYMENT_PLUGIN_MANAGEMENT'), 'stack.png');
		ToolbarHelper::publishList('publish');
		ToolbarHelper::unpublishList('unpublish');
		ToolbarHelper::deleteList(Text::_('JD_PAYMENT_PLUGIN_UNINSTALL_CONFIRM'), 'uninstall', 'Uninstall');
	}
}
