<?php
/**
 * Form Field class for the Joomla MPF
 *
 * Supports a ranage input.
 *
 * @package     Joomla.MPF
 * @subpackage  Form
 */

class MPFFormFieldRange extends MPFFormFieldText
{
	/**
	 * Field Type
	 *
	 * @var string
	 */
	protected $type = 'Range';

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

		if ($row->min)
		{
			$this->attributes['min'] = $row->min;
		}

		if ($row->max)
		{
			$this->attributes['max'] = $row->max;
		}

		if ($row->step)
		{
			$this->attributes['step'] = $row->step;
		}
	}
}
