<?php

/**
 * @package     MPF
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for the Joomla MPF.
 * Supports a generic list of options.
 *
 * @package     Joomla.MPF
 * @subpackage  Form
 */
class MPFFormFieldList extends MPFFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'List';

	/**
	 * Values used to genereate list options
	 *
	 * @var mixed
	 */
	protected $values;
	/**
	 * Is multiple
	 *
	 * @var bool
	 */

	protected $multiple = false;

	/**
	 * Constructor.
	 *
	 * @param   OSMembershipTableField  $row
	 * @param   mixed                   $value
	 * @param   string                  $fieldSuffix
	 */
	public function __construct($row, $value, $fieldSuffix)
	{
		parent::__construct($row, $value, $fieldSuffix);

		if ($row->size)
		{
			$this->attributes['size'] = $row->size;
		}

		if ($row->multiple)
		{
			$this->attributes['multiple'] = true;
			$this->multiple               = true;
		}

		$this->values = $row->values;
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @param   OSMembershipHelperBootstrap  $bootstrapHelper
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput($bootstrapHelper = null)
	{
		// Add uk-checkbox if UIKit3 is used
		if (!$this->multiple && $bootstrapHelper && $bootstrapHelper->getFrameworkClass('uk-select'))
		{
			$this->addClass('uk-select');
		}

		if ($bootstrapHelper && $bootstrapHelper->getFrameworkClass('form-select'))
		{
			$this->addClass('form-select');
		}
		elseif ($bootstrapHelper && $bootstrapHelper->getFrameworkClass('form-control'))
		{
			$this->addClass('form-control');
		}

		// Get the field options.
		$options    = (array) $this->getOptions();
		$attributes = $this->buildAttributes();

		if ($this->multiple)
		{
			if (is_array($this->value))
			{
				$selectedOptions = $this->value;
			}
			elseif (is_string($this->value) && strpos($this->value, "\r\n"))
			{
				$selectedOptions = explode("\r\n", $this->value);
			}
			elseif (is_string($this->value) && is_array(json_decode($this->value)))
			{
				$selectedOptions = json_decode($this->value);
			}
			elseif ($this->value)
			{
				$selectedOptions = [$this->value];
			}
			else
			{
				$selectedOptions = [];
			}

			$selectedOptions = array_map('trim', $selectedOptions);
		}
		else
		{
			$selectedOptions = trim((string) $this->value);
		}

		return HTMLHelper::_(
			'select.genericlist',
			$options,
			$this->name . ($this->multiple ? '[]' : ''),
			trim($attributes),
			'value',
			'text',
			$selectedOptions
		);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects
	 */
	protected function getOptions()
	{
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', $this->row->prompt_text ?: Text::_('OSM_SELECT'));

		if (is_array($this->values))
		{
			$values = $this->values;
		}
		elseif (is_string($this->values) && str_contains($this->values, "\r\n"))
		{
			$values = explode("\r\n", $this->values);
		}
		else
		{
			$values = explode(',', $this->values);
		}

		$values = array_map('trim', $values);

		$values = array_filter($values, function ($value) {
			return strlen(trim($value)) > 0;
		});

		foreach ($values as $value)
		{
			$options[] = HTMLHelper::_('select.option', trim($value), $value);
		}

		return $options;
	}
}
