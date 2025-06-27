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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class OSMembershipViewCheckinlogsHtml extends MPFViewList
{
	/**
	 * Buttons to hide
	 *
	 * @var string[]
	 */
	protected $hideButtons = ['add', 'edit'];

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Prepare view data before displaying
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$db    = $this->model->getDbo();
		$query = $db->getQuery(true)
			->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		$options                       = [];
		$options[]                     = HTMLHelper::_('select.option', 0, Text::_('OSM_SELECT_PLAN'), 'id', 'title');
		$options                       = array_merge($options, $db->loadObjectList());
		$this->lists['filter_plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_plan_id',
			'class="form-select" onchange="submit();" ',
			'id',
			'title',
			$this->state->filter_plan_id
		);

		$this->lists['filter_plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
			$this->lists['filter_plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_ONE_PLAN')
		);

		$this->config = OSMembershipHelper::getConfig();
	}

	protected function addCustomToolbarButtons()
	{
		parent::addCustomToolbarButtons();

		if ($this->items)
		{
			ToolbarHelper::custom('export', 'download', 'download', 'OSM_EXPORT', false);
		}
	}
}
