<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

/**
 * HTML View class for Membership Pro component
 *
 * @static
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipViewMplanHtml extends MPFViewItem
{
	use OSMembershipViewPlan;

	protected $hideButtons = ['save2new'];

	/**
	 * Add toolbar buttons
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		parent::addToolbar();
	}
}
