<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use OSSolution\MembershipPro\Admin\Event\Category\EditCategory;

class OSMembershipViewCategoryHtml extends MPFViewItem
{
	/**
	 * Plugins
	 *
	 * @var array
	 */
	protected $plugins;

	protected function prepareView()
	{
		parent::prepareView();

		PluginHelper::importPlugin('osmembership');

		$event = new EditCategory(['row' => $this->item]);

		$this->plugins = Factory::getApplication()->triggerEvent($event->getName(), $event);
	}
}
