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

class OSMembershipViewCouponsHtml extends MPFViewList
{
	/**
	 * The configured date format
	 *
	 * @var string
	 */
	protected $dateFormat;

	/**
	 * The database null date
	 *
	 * @var string
	 */
	protected $nullDate;

	/**
	 * Discount types
	 *
	 * @var array
	 */
	protected $discountTypes;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	protected function prepareView()
	{
		parent::prepareView();

		$db            = $this->model->getDbo();
		$config        = OSMembershipHelper::getConfig();
		$discountTypes = [0 => '%', 1 => $config->currency_symbol];
		$query         = $db->getQuery(true);
		$query->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$options                       = [];
		$options[]                     = HTMLHelper::_('select.option', 0, Text::_('OSM_PLAN'), 'id', 'title');
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

		$this->dateFormat    = $config->date_format;
		$this->nullDate      = $db->getNullDate();
		$this->discountTypes = $discountTypes;
		$this->config        = $config;
	}
}
