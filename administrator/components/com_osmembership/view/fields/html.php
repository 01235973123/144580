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

class OSMembershipViewFieldsHtml extends MPFViewList
{
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

		$options                        = [];
		$options[]                      = HTMLHelper::_('select.option', 1, Text::_('Show Core Fields'));
		$options[]                      = HTMLHelper::_('select.option', 2, Text::_('Hide Core Fields'));
		$this->lists['show_core_field'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'show_core_field',
			' class="form-select input-medium" onchange="submit();" ',
			'value',
			'text',
			$this->state->show_core_field
		);

		$fieldTypes = [
			'Text',
			'Url',
			'Email',
			'Number',
			'Tel',
			'Range',
			'Textarea',
			'List',
			'Checkboxes',
			'Radio',
			'Date',
			'Heading',
			'Message',
			'File',
			'Countries',
			'State',
			'SQL',
		];

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_FIELD_TYPE'));

		foreach ($fieldTypes as $fieldType)
		{
			$options[] = HTMLHelper::_('select.option', $fieldType, $fieldType);
		}

		$this->lists['filter_fieldtype'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_fieldtype',
			'class="form-select" onchange="submit();"',
			'value',
			'text',
			$this->state->filter_fieldtype
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', -1, Text::_('OSM_FEE_FIELD'));
		$options[] = HTMLHelper::_('select.option', 0, Text::_('JNO'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('JYES'));

		$this->lists['filter_fee_field'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_fee_field',
			'class="form-select input-medium" onchange="submit();" ',
			'value',
			'text',
			$this->state->filter_fee_field
		);

		$this->lists['plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
			$this->lists['plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_ONE_PLAN')
		);
	}
}
