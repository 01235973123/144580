<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for Membership Pro component
 *
 * @static
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class DonationViewEmailHtml extends OSFViewItem
{
	/**
	 * Method to instantiate the view.
	 *
	 * @param   array  $config  A named configuration array for object construction
	 */
	protected function prepareView()
	{
		parent::prepareView();
	}

	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('JD_EMAIL_EDIT'));
		ToolbarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
	}
}
