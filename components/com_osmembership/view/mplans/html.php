<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

class OSMembershipViewMplansHtml extends MPFViewList
{
	/**
	 * Contains select lists use on the view
	 *
	 * @var array
	 */
	protected $lists;

	/**
	 * Bootstrap Helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * Wheter we should show thumbnail column on this view
	 *
	 * @var bool
	 */
	protected $showThumbnail;

	/**
	 * Wheter we should show category column on this view
	 *
	 * @var bool
	 */
	protected $showCategory;

	/**
	 * Preview view data before rendering
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$db    = $this->model->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__osmembership_categories')
			->where('published = 1');
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		if (count($categories))
		{
			$this->lists['filter_category_id'] = OSMembershipHelperHtml::buildCategoryDropdown(
				$this->state->filter_category_id,
				'filter_category_id',
				'onchange="submit();"'
			);
		}

		// Check to see whether we will show thumbnail column
		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_plans')
			->where('thumb != ""');
		$db->setQuery($query);
		$this->showThumbnail = (int) $db->loadResult();

		// Check to see whether we should show category column
		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_plans')
			->where('category_id > 0');
		$db->setQuery($query);
		$this->showCategory = (int) $db->loadResult();

		$this->lists['filter_state'] = str_replace(
			['class="inputbox"', 'class="form-control"'],
			'class="input-medium form-select w-auto"',
			HTMLHelper::_('grid.state', $this->state->filter_state)
		);

		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();

		$this->addToolbar();
	}

	/**
	 * Add Toolbar
	 */
	protected function addToolbar()
	{
		parent::addToolbar();
	}
}
