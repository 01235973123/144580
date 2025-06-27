<?php

/**
 * @package     MPF
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Supports a custom field which display list of countries
 *
 * @package     Joomla.MPF
 * @subpackage  Form
 */
class MPFFormFieldState extends MPFFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'State';

	/**
	 * The current selected country
	 *
	 * @var string
	 */
	public $country = '';

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = [];

		if ($this->country)
		{
			/* @var \Joomla\Database\DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select('state_2_code AS value, state_name AS text')
				->from('#__osmembership_states AS a')
				->innerJoin('#__osmembership_countries AS b ON a.country_id = b.country_id')
				->where('b.name = ' . $db->quote($this->country))
				->where('a.published = 1')
				->order('a.state_name');
			$db->setQuery($query);
			$states = $db->loadObjectList();

			if (count($states))
			{
				$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_SELECT_STATE'));
				$options   = array_merge($options, $states);
			}
			else
			{
				$options[] = HTMLHelper::_('select.option', 'N/A', Text::_('OSM_NA'));
			}

			if ($this->value !== null && strlen($this->value) > 2)
			{
				// We are having state name because of a bug in old version, convert it to state code
				$query->clear()
					->select('a.state_2_code')
					->from('#__osmembership_states AS a')
					->innerJoin('#__osmembership_countries AS b ON a.country_id = b.id')
					->where('b.name = ' . $db->quote($this->country))
					->where('a.state_name = ' . $db->quote($this->value));
				$db->setQuery($query);

				$stateCode = $db->loadResult();

				if ($stateCode)
				{
					$this->value = $stateCode;
				}
			}


			// Workaround for countries need different tax rate by state
			if (Factory::getApplication()->getInput()->getCmd('view') === 'register')
			{
				$taxByStateCountries = explode(',', OSMembershipHelper::getTaxStateCountries());

				if (in_array($this->country, $taxByStateCountries))
				{
					$this->setAttribute('onchange', 'calculateSubscriptionFee();');
				}
			}
		}

		return $options;
	}
}
