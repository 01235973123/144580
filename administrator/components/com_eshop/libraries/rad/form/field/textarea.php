<?php
/**
 * Form Field class for the Joomla EshopRAD.
 * Supports a textarea inut.
 *
 * @package     Joomla.EshopRAD
 * @subpackage  Form
 */

use Joomla\CMS\Table\Table;

class EshopRADFormFieldTextarea extends EshopRADFormField
{

	protected $type = 'Textarea';

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
		if ($row->place_holder)
		{
			$this->attributes['placeholder'] = $row->place_holder;
		}
		if ($row->max_length)
		{
			$this->attributes['maxlength'] = $row->max_length;
		}
		if ($row->rows)
		{
			$this->attributes['rows'] = $row->rows;
		}
		if ($row->cols)
		{
			$this->attributes['cols'] = $row->cols;
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 */
	public function getInput()
	{
		$attributes = $this->buildAttributes();

		return '<textarea class="form-control" name="' . $this->name . '" id="' . $this->name . '"' . $attributes . $this->extraAttributes . ' >' .
			htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
	}
}