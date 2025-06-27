<?php
/**
 * Form Field class for the Joomla EshopRAD.
 * Supports a checkbox list custom field.
 *
 * @package     Joomla.EshopRAD
 * @subpackage  Form
 */

use Joomla\CMS\Table\Table;

class EshopRADFormFieldCheckboxes extends EshopRADFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 */
	protected $type = 'Checkboxes';

	/**
	 * Options for checkbox lists
	 * @var array
	 */
	protected $options = [];

	/**
	 * Number options displayed perrow
	 * @var int
	 */
	protected $optionsPerRow = 1;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Table  $row    the table object store form field definitions
	 * @param   mixed  $value  the initial value of the form field
	 *
	 */
	public function __construct($row, $value)
	{
		parent::__construct($row, $value);
		if ((int) $row->size)
		{
			$this->optionsPerRow = (int) $row->size;
		}
		if (is_array($row->values))
		{
			$this->options = $row->values;
		}
		elseif (strpos($row->values, "\r\n") !== false)
		{
			$this->options = explode("\r\n", $row->values);
		}
		else
		{
			$this->options = explode(",", $row->values);
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 */
	protected function getInput()
	{
		$html       = [];
		$options    = $this->options;
		$attributes = $this->buildAttributes();
		if (is_array($this->value))
		{
			$selectedOptions = $this->value;
		}
		elseif (strpos($this->value, "\r\n"))
		{
			$selectedOptions = explode("\r\n", $this->value);
		}
		elseif (is_string($this->value) && is_array(json_decode($this->value)))
		{
			$selectedOptions = json_decode($this->value);
		}
		else
		{
			$selectedOptions = [$this->value];
		}

		$html[]        = '<fieldset id="' . $this->name . '" class="row-fluid clearfix"' . '>';
		$html[]        = '<ul class="clearfix">';
		$i             = 0;
		$optionsPerRow = (int) $this->optionsPerRow;
		$span          = intval(12 / $optionsPerRow);
		$numberOptions = count($options);
		foreach ($options as $option)
		{
			$i++;
			$optionValue = trim($option);
			$checked     = in_array($optionValue, $selectedOptions) ? 'checked' : '';
			$html[]      = '<li class="span' . $span . '">';
			$html[]      = '<label class="checkbox" for="' . $this->name . $i . '" >';
			$html[]      = '<input type="checkbox" class="form-check-input" id="' . $this->name . $i . '" name="' . $this->name . '[]" value="' .
				htmlspecialchars($optionValue, ENT_COMPAT, 'UTF-8') . '"' . $checked . $attributes . $this->extraAttributes . '/>' . $option;
			$html[]      = '</label>';

			$html[] = '</li>';
			if ($i % $optionsPerRow == 0 && $i < $numberOptions)
			{
				$html[] = '</ul>';
				$html[] = '<ul class="clearfix">';
			}
		}
		$html[] = '</ul>';

		// End the checkbox field output.
		$html[] = '</fieldset>';

		return implode($html);
	}
}