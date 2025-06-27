<?php

/**
 * @package     MPF
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

/**
 * Form Field class for the Joomla MPF.
 * Supports a a text input.
 *
 * @package     Joomla.MPF
 * @subpackage  Form
 */
class MPFFormFieldText extends MPFFormField
{
	/**
	 * Field Type
	 *
	 * @var string
	 */
	protected $type = 'Text';

	/**
	 * MPFFormFieldText constructor.
	 *
	 * @param   OSMembershipTableField  $row
	 * @param   mixed                   $value
	 * @param   string                  $fieldSuffix
	 */
	public function __construct($row, $value = null, $fieldSuffix = null)
	{
		parent::__construct($row, $value, $fieldSuffix);

		if ($row->place_holder)
		{
			$this->attributes['placeholder'] = $row->place_holder;
		}

		if ($row->max_length)
		{
			$this->attributes['maxlength'] = $row->max_length;
		}

		if ($row->size)
		{
			$this->attributes['size'] = $row->size;
		}

		if ($row->readonly)
		{
			$this->attributes['readonly'] = true;
		}

		if ($row->input_mask)
		{
			$this->attributes['data-input-mask'] = $row->input_mask;
		}
	}

	/**
	 * Get the field input markup.
	 *
	 * @param   OSMembershipHelperBootstrap  $bootstrapHelper
	 *
	 * @return string
	 */
	public function getInput($bootstrapHelper = null)
	{
		// Add uk-input to input elements
		if ($bootstrapHelper && $bootstrapHelper->getBootstrapVersion() === 'uikit3')
		{
			if ($this->type == 'Range')
			{
				$class = 'uk-range';
			}
			else
			{
				$class = 'uk-input';
			}

			$this->addClass($class);
		}

		if ($bootstrapHelper && $bootstrapHelper->getFrameworkClass('form-control'))
		{
			$this->addClass('form-control');
		}

		$data = [
			'type'       => strtolower($this->type),
			'name'       => $this->name,
			'value'      => $this->value,
			'attributes' => $this->buildAttributes(),
			'row'        => $this->row,
		];

		return OSMembershipHelperHtml::loadCommonLayout('fieldlayout/text.php', $data);
	}

	/**
	 * Overide getDisplayValue to render make value entered for this field type linkable
	 *
	 * @return mixed|string
	 */
	public function getRawDisplayValue()
	{
		if ($this->value && filter_var($this->value, FILTER_VALIDATE_URL))
		{
			return '<a href="' . $this->value . '" target="_blank">' . $this->value . '</a>';
		}

		if ($this->value && str_starts_with($this->value, 'www.'))
		{
			return '<a href="https://' . $this->value . '" target="_blank">' . $this->value . '</a>';
		}

		return $this->value;
	}
}
