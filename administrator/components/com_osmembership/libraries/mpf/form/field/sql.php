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
 * Supports an custom SQL select list
 *
 * @package     Joomla.MPF
 * @subpackage  Form
 */
class MPFFormFieldSQL extends MPFFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'SQL';

	/**
	 * The query.
	 *
	 * @var    string
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

		$this->query = $row->default_values;
	}

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		try
		{
			/* @var \Joomla\Database\DatabaseDriver $db */
			$db = Factory::getContainer()->get('db');
			$db->setQuery($this->query);

			$options = [];

			if (!$this->multiple)
			{
				$options[] = HTMLHelper::_('select.option', '', $this->row->prompt_text ?: Text::_('OSM_SELECT'));
			}

			$options = array_merge($options, $db->loadObjectList());
		}
		catch (Exception $e)
		{
			$options = [];
		}

		return $options;
	}
}
