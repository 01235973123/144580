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

class OSMembershipViewDiscountHtml extends MPFViewItem
{
	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	protected function prepareView()
	{
		parent::prepareView();

		$config = OSMembershipHelper::getConfig();
		$db     = $this->model->getDbo();
		$query  = $db->getQuery(true)
			->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_ALL_PLANS'), 'id', 'title');
		$options   = array_merge($options, $db->loadObjectList());

		if ($this->getLayout() === 'batch')
		{
			$this->lists['plan_id'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'plan_id[]',
				'class="chosen" multiple',
				'id',
				'title',
				[]
			);
		}
		else
		{
			$this->lists['plan_id'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'plan_id',
				'class="chosen"',
				'id',
				'title',
				$this->item->plan_id
			);
		}

		$options                      = [];
		$options[]                    = HTMLHelper::_('select.option', 0, Text::_('%'));
		$options[]                    = HTMLHelper::_('select.option', 1, $config->currency_symbol);
		$this->lists['discount_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'discount_type',
			' class="form-select d-inline input-small" ',
			'value',
			'text',
			$this->item->discount_type
		);

		if ($this->getLayout() === 'batch')
		{
			$this->lists['plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect($this->lists['plan_id']);
		}
		else
		{
			$this->lists['plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
				$this->lists['plan_id'],
				Text::_('OSM_TYPE_OR_SELECT_ONE_PLAN')
			);
		}

		$this->config = $config;
	}

	/**
	 * Override addToolbar method to add toolbar for batch discounts
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		if ($this->getLayout() === 'batch')
		{
			ToolbarHelper::title(Text::_('OSM_BATCH_DISCOUNTS_TITLE'));
			ToolbarHelper::custom('batch', 'upload', 'upload', 'OSM_GENERATE_DISCOUNTS', false);
			ToolbarHelper::cancel('cancel');
		}
		else
		{
			parent::addToolbar();
		}
	}
}
