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

class OSMembershipViewTaxHtml extends MPFViewItem
{
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
		$options                = [];
		$options[]              = HTMLHelper::_('select.option', 0, Text::_('OSM_ALL_PLANS'), 'id', 'title');
		$options                = array_merge($options, $db->loadObjectList());
		$this->lists['plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'plan_id',
			'class="form-select"',
			'id',
			'title',
			$this->item->plan_id
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_ALL_COUNTRIES'));
		$query->clear()
			->select('name AS value, name AS text')
			->from('#__osmembership_countries')
			->where('published = 1')
			->order('name');
		$db->setQuery($query);
		$options                = array_merge($options, $db->loadObjectList());
		$this->lists['country'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'country',
			'class="form-select"',
			'value',
			'text',
			$this->item->country,
			'country'
		);

		$defaultCountry = $config->default_country;
		$countryCode    = OSMembershipHelper::getCountryCode($defaultCountry);

		if (OSMembershipHelperEuvat::isEUCountry($countryCode) && $config->eu_vat_number_field)
		{
			$this->lists['vies'] = OSMembershipHelperHtml::getBooleanInput('vies', $this->item->vies);
		}

		// States
		$options = [];

		$stateCountries = [
			'US', //United States
			'CA', //Canada
			'IN', //India
			'ES', //Spain
		];

		$options[] = HTMLHelper::_('select.option', '', 'N/A');

		foreach ($stateCountries as $countryCode)
		{
			$query->clear()
				->select('id, name')
				->from('#__osmembership_countries')
				->where('country_2_code = ' . $db->quote($countryCode))
				->where('published = 1');
			$db->setQuery($query);
			$rowCountry = $db->loadObject();

			if (!$rowCountry)
			{
				continue;
			}

			$options[] = HTMLHelper::_('select.option', '<OPTGROUP>', $rowCountry->name);

			// Get list of states belong to this country
			$query->clear()
				->select('state_2_code AS value, state_name AS text')
				->from('#__osmembership_states AS a')
				->innerJoin('#__osmembership_countries AS b ON a.country_id = b.id ')
				->where('a.country_id = ' . $rowCountry->id);
			$db->setQuery($query);
			$options   = array_merge($options, $db->loadObjectList());
			$options[] = HTMLHelper::_('select.option', '</OPTGROUP>');
		}

		$this->lists['state'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'state',
			' class="form-control" ',
			'value',
			'text',
			$this->item->state,
			'state'
		);

		$this->lists['plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
			$this->lists['plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_ONE_PLAN')
		);

		$keys = ['country', 'state'];

		foreach ($keys as $key)
		{
			$this->lists[$key] = OSMembershipHelperHtml::getChoicesJsSelect($this->lists[$key]);
		}

		return true;
	}
}
