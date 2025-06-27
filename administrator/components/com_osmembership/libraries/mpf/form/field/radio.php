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
 * Supports a radiolist custom field.
 *
 * @package     Joomla.MPF
 * @subpackage  Form
 */
class MPFFormFieldRadio extends MPFFormField
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'Radio';

	/**
	 * Radio options
	 *
	 * @var mixed
	 */
	protected $values;

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

		$this->values = $row->values ?? '';
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @param   OSMembershipHelperBootstrap  $bootstrapHelper
	 *
	 * @return string The field input markup.
	 */
	public function getInput($bootstrapHelper = null)
	{
		$options = (array) $this->getOptions();
		$value   = trim((string) $this->value);

		// Add uk-radio for UIKIT 3
		if ($bootstrapHelper && $bootstrapHelper->getFrameworkClass('uk-radio'))
		{
			$this->addClass('uk-radio');
		}

		// form-check-input for twitter bootstrap 4 and 5
		if ($bootstrapHelper && $bootstrapHelper->getFrameworkClass('form-check-input'))
		{
			$this->addClass('form-check-input');
		}

		$data = [
			'name'            => $this->name,
			'options'         => $options,
			'value'           => $value,
			'attributes'      => $this->buildAttributes(),
			'bootstrapHelper' => $bootstrapHelper,
			'row'             => $this->row,
		];

		return OSMembershipHelperHtml::loadCommonLayout('fieldlayout/radio.php', $data);
	}

	/**
	 * Get radio options
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		if (is_array($this->values))
		{
			$values = $this->values;
		}
		elseif (str_contains($this->values, "\r\n"))
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

		return $values;
	}
}
