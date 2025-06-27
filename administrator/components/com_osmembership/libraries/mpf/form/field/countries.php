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
class MPFFormFieldCountries extends MPFFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Countries';

	/**
	 * The query.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $query;

	/**
	 * Constructor.
	 *
	 * @param   OSMembershipTableField  $row
	 * @param   string                  $value
	 * @param   string                  $fieldSuffix
	 */
	public function __construct($row, $value, $fieldSuffix)
	{
		parent::__construct($row, $value, $fieldSuffix);

		/* @var \Joomla\Database\DatabaseDriver $db */
		$nameField = Factory::getContainer()->get('db')->quoteName('name' . $row->fieldSuffix);

		$this->query = "SELECT name AS value, IF(CHAR_LENGTH($nameField) > 0, $nameField, `name`) AS text FROM #__osmembership_countries WHERE published = 1 ORDER BY ordering";
	}

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		try
		{
			/* @var \Joomla\Database\DatabaseDriver $db */
			$db = Factory::getContainer()->get('db');
			// Set the query and get the result list.
			$db->setQuery($this->query);
			$options   = [];
			$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_SELECT_COUNTRY'));
			$options   = array_merge($options, $db->loadObjectlist());
		}
		catch (Exception $e)
		{
			$options = [];
		}

		return $options;
	}
}
