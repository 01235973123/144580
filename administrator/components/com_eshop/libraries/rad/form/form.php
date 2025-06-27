<?php
/**
 * Form Class for handling custom fields
 *
 * @package        EshopRAD
 * @subpackage     Form
 */

use Joomla\CMS\Language\Text;

class EshopRADForm
{

	/**
	 * The array hold list of custom fields
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * Form validator, use to validate data on form field
	 *
	 * @var EshopRADValidator
	 */
	protected $validator;
	/**
	 * Contains validation rules applied for form fields
	 * @var array
	 */
	protected $rules = [];
	/**
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Custom error messages
	 * @var array
	 */
	protected $customErrorMessages = [];

	/**
	 * Constructor
	 *
	 * @param   array  $fields
	 */
	public function __construct($fields, $config = [])
	{
		$this->validator = new EshopRADValidator();
		foreach ($fields as $field)
		{
			$class = 'EshopRADFormField' . ucfirst($field->fieldtype);
			if (class_exists($class))
			{
				$this->fields[$field->name] = new $class($field, $field->default_values);
				if ($field->validation_rules_string)
				{
					$this->rules[$field->name] = $field->validation_rules_string;
				}
				if ($field->validation_error_message)
				{
					$this->customErrorMessages[$field->name] = Text::_($field->validation_error_message);
				}
			}
			else
			{
				throw new RuntimeException('The field type ' . $field->fieldType . ' is not supported');
			}
		}
	}

	/**
	 * Get fields of form
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Get the field object from name
	 *
	 * @param   string  $name
	 *
	 * @return EshopRADFormField
	 */
	public function getField($name)
	{
		return $this->fields[$name];
	}

	/**
	 * Remove a field from validation process
	 *
	 * @param   string  $fieldName
	 */
	public function removeRule($fieldName)
	{
		if (isset($this->rules[$fieldName]))
		{
			unset($this->rules[$fieldName]);
		}
	}

	/**
	 *
	 * Bind data into form fields
	 *
	 * @param   array  $data
	 * @param   bool   $useDefault
	 */
	public function bind($data, $useDefault = false)
	{
		foreach ($this->fields as $field)
		{
			if (isset($data[$field->name]))
			{
				$field->setValue($data[$field->name]);
			}
			else
			{
				if ($useDefault)
				{
					$field->setValue($field->row->default_values);
				}
				else
				{
					$field->setValue(null);
				}
			}
		}

		return $this;
	}

	/**
	 * Validate the data in the form
	 *
	 * @param   array  $data
	 */
	public function validate($data)
	{
		$this->validator->setRules($this->rules);
		$valid = $this->validator->validate($data);
		if (!$valid)
		{
			foreach ($this->fields as $field)
			{
				$this->validator->setFieldName($field->name, $field->title);
			}
			$this->errors = $this->validator->getErrors();
			if (count($this->customErrorMessages))
			{
				foreach ($this->customErrorMessages as $fieldName => $errorMessage)
				{
					if (isset($this->errors[$fieldName]))
					{
						$this->errors[$fieldName] = $errorMessage;
					}
				}
			}
		}

		return $valid;
	}

	/**
	 * Method to get form rendered string
	 *
	 * @return string
	 */
	public function render($tableLess = true)
	{
		ob_start();
		foreach ($this->fields as $field)
		{
			echo $field->getControlGroup($tableLess);
		}

		return ob_get_clean();
	}

	/**
	 * Get the error message
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}
}