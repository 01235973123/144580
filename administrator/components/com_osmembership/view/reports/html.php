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

class OSMembershipViewReportsHtml extends MPFViewList
{
	/**
	 * Component config data
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Prepare view data
	 *
	 * @return void
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

		$options                = [];
		$options[]              = HTMLHelper::_('select.option', 0, Text::_('OSM_SELECT_PLAN'), 'id', 'title');
		$options                = array_merge($options, $db->loadObjectList());
		$this->lists['plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'plan_id',
			' class="form-select" onchange="submit();" ',
			'id',
			'title',
			$this->state->plan_id
		);

		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', -1, Text::_('OSM_ALL'));
		$options[]                = HTMLHelper::_('select.option', 0, Text::_('OSM_PENDING'));
		$options[]                = HTMLHelper::_('select.option', 1, Text::_('OSM_ACTIVE'));
		$options[]                = HTMLHelper::_('select.option', 2, Text::_('OSM_EXPIRED'));
		$options[]                = HTMLHelper::_('select.option', 3, Text::_('OSM_CANCELLED_PENDING'));
		$options[]                = HTMLHelper::_('select.option', 4, Text::_('OSM_UPCOMING_EXPIRED'));
		$options[]                = HTMLHelper::_('select.option', 5, Text::_('OSM_UPCOMING_RENEWAL'));
		$this->lists['published'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'published',
			' class="form-select" onchange="submit();" ',
			'value',
			'text',
			$this->state->published
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_IN'));

		for ($i = 5; $i <= 60; $i += 5)
		{
			$options[] = HTMLHelper::_('select.option', $i, $i . ' ' . Text::_('OSM_DAYS'));
		}

		$this->lists['filter_in'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_in',
			' class="input-small" onchange="submit();" ',
			'value',
			'text',
			$this->state->filter_in
		);

		$this->lists['plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
			$this->lists['plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_ONE_PLAN')
		);

		$this->config = OSMembershipHelper::getConfig();

		$this->setLayout('default');
	}

	/**
	 * Empty method so that no default toolbar buttons ar added
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_(strtoupper('OSM_REPORT_MANAGEMENT')), 'link ' . $this->name);
	}
}
