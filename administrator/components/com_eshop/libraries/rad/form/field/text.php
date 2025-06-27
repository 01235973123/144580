<?php
/**
 * Form Field class for the Joomla EshopRAD.
 * Supports a text input.
 *
 * @package     Joomla.EshopRAD
 * @subpackage  Form
 */

use Joomla\CMS\Table\Table;

class EshopRADFormFieldText extends EshopRADFormField
{

	/**
	 * Field Type
	 *
	 * @var string
	 */
	protected $type = 'Text';

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Table  $row    the table object store form field definitions
	 * @param   mixed  $value  the initial value of the form field
	 *
	 */
	public function __construct($row, $value = null)
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
		if ($row->size)
		{
			$this->attributes['size'] = $row->size;
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

		return '<input type="text" name="' . $this->name . '" id="' . $this->name . '" value="' . htmlspecialchars(
				$this->value,
				ENT_COMPAT,
				'UTF-8'
			) .
			'"' . $attributes . $this->extraAttributes . ' class="input-xlarge form-control" />';
	}
}