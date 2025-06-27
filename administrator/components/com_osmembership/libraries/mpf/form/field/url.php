<?php
/**
 * Form Field class for the Joomla MPF.
 *
 * Supports a Url input.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

class MPFFormFieldUrl extends MPFFormFieldText
{
	/**
	 * Field Type
	 *
	 * @var string
	 */
	protected $type = 'Url';

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

		return $this->value;
	}
}
