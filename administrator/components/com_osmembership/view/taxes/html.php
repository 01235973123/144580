<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class OSMembershipViewTaxesHtml extends MPFViewList
{
	/**
	 * The flag to mark if we should show vies column
	 *
	 * @var bool
	 */
	protected $showVies;

	/**
	 * Prepare view data
	 *
	 * @return void
	 */
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

		// Build countries dropdown
		$query->clear();
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_SELECT_COUNTRY'));
		$query->select('name AS value, name AS text')
			->from('#__osmembership_countries')
			->where('published = 1')
			->order('name');
		$db->setQuery($query);

		$options                       = array_merge($options, $db->loadObjectList());
		$this->lists['filter_country'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_country',
			'class="form-select" onchange="submit();" ',
			'value',
			'text',
			$this->state->filter_country
		);

		$defaultCountry = $config->default_country;
		$countryCode    = OSMembershipHelper::getCountryCode($defaultCountry);

		if (OSMembershipHelperEuvat::isEUCountry($countryCode) && $config->eu_vat_number_field)
		{
			$this->showVies             = true;
			$options                    = [];
			$options[]                  = HTMLHelper::_('select.option', -1, Text::_('OSM_VIES'));
			$options[]                  = HTMLHelper::_('select.option', 0, Text::_('JNO'));
			$options[]                  = HTMLHelper::_('select.option', 1, Text::_('JYES'));
			$this->lists['filter_vies'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'filter_vies',
				'class="form-select" onchange="submit();" ',
				'value',
				'text',
				$this->state->filter_vies
			);
		}
		else
		{
			$this->showVies = false;
		}

		$this->lists['filter_plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
			$this->lists['filter_plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_ONE_PLAN')
		);
		$this->lists['filter_country'] = OSMembershipHelperHtml::getChoicesJsSelect($this->lists['filter_country']);
	}
}
