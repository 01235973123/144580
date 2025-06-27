<?php
/**
 * Form Field class for the Joomla MPF.
 *
 * Supports a email input.
 *
 * @package     Joomla.MPF
 * @subpackage  Form
 */

class MPFFormFieldEmail extends MPFFormFieldText
{
	/**
	 * Field Type
	 *
	 * @var string
	 */
	protected $type = 'Email';

	/**
	 * Overide getDisplayValue to render make value entered for this field type linkable
	 *
	 * @return mixed|string
	 */
	public function getRawDisplayValue()
	{
		if ($this->value && filter_var($this->value, FILTER_VALIDATE_EMAIL))
		{
			return '<a href="mailto:' . $this->value . '">' . $this->value . '</a>';
		}

		return $this->value;
	}
}
