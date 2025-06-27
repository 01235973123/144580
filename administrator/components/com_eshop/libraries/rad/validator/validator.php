<?php
/**
 * EshopRADValidator - A fast, extensible PHP input validation class based on GUMP
 *
 * @author      Sean Nieuwoudt (http://twitter.com/SeanNieuwoudt)
 * @copyright   Copyright (c) 2014 Wixelhq.com
 * @link        http://github.com/Wixel/GUMP
 * @version     1.0
 */

use Joomla\CMS\Language\Text;

class EshopRADValidator
{
	// Validation rules for execution
	protected $validationRules = [];

	// Instance attribute containing errors from last run
	protected $errors = [];

	// Contain readable field names that have been set manually
	protected static $fields = [];

	// Custom validation methods
	protected static $validationMethods = [];

	/**
	 * Adds a custom validation rule using a callback function
	 *
	 * @access public
	 *
	 * @param   string    $rule
	 * @param   callable  $callback
	 *
	 * @return bool
	 */
	public static function addValidator($rule, $callback)
	{
		$method = 'validate_' . $rule;

		if (method_exists(__CLASS__, $method) || isset(self::$validationMethods[$rule]))
		{
			throw new Exception("Validator rule '$rule' already exists.");
		}

		self::$validationMethods[$rule] = $callback;

		return true;
	}

	public function setRules($rules)
	{
		$this->validationRules = $rules;
	}

	/**
	 * Add a field to validation rule array
	 *
	 * @param   string  $field  name of the field
	 * @param   string  $rule
	 */
	public function addRule($field, $rule)
	{
		$this->validationRules[$field] = $rule;
	}

	/**
	 * Perform data validation against the provided ruleset
	 *
	 * @access public
	 *
	 * @param   mixed  $input
	 * @param   array  $ruleset
	 *
	 * @return mixed
	 */
	public function validate(array $input)
	{
		$this->errors = [];
		$ruleset      = $this->validationRules;
		foreach ($ruleset as $field => $rules)
		{
			#if(!array_key_exists($field, $input))
			#{
			#   continue;
			#}


			$rules = explode('|', $rules);

			if (in_array("required", $rules) || (isset($input[$field]) && trim($input[$field]) != ''))
			{
				foreach ($rules as $rule)
				{
					$method = null;
					$param  = null;

					if (strstr($rule, ',') !== false) // has params
					{
						$rule   = explode(',', $rule);
						$method = 'validate_' . $rule[0];
						$param  = $rule[1];
						$rule   = $rule[0];
					}
					else
					{
						$method = 'validate_' . $rule;
					}

					if (is_callable([$this, $method]))
					{
						$result = $this->$method($field, $input, $param);

						if (is_array($result)) // Validation Failed
						{
							$this->errors[] = $result;
						}
					}
					else
					{
						if (isset(self::$validationMethods[$rule]))
						{
							if (isset($input[$field]))
							{
								$result = call_user_func(self::$validationMethods[$rule], $field, $input, $param);

								if (!$result) // Validation Failed
								{
									$this->errors[] = ['field' => $field, 'value' => $input[$field], 'rule' => $method, 'param' => $param];
								}
							}
						}
						else
						{
							throw new Exception("Validator method '$method' does not exist.");
						}
					}
				}
			}
		}

		return (count($this->errors) > 0) ? false : true;
	}

	/**
	 * Set a readable name for a specified field names
	 *
	 * @param   string  $field_class
	 * @param   string  $readable_name
	 *
	 * @return void
	 */
	public static function setFieldName($field, $readable_name)
	{
		self::$fields[$field] = $readable_name;
	}

	/**
	 * Process the validation errors and return human readable error messages
	 *
	 * @param   bool    $convert_to_string  = false
	 * @param   string  $field_class
	 * @param   string  $error_class
	 *
	 * @return array
	 * @return string
	 */
	public function getErrors()
	{
		if (empty($this->errors))
		{
			return [];
		}

		$errors = [];

		foreach ($this->errors as $e)
		{
			$originalFieldname = $field = $e['field'];
			$param             = $e['param'];
			// Let's fetch explicit field names if they exist
			if (array_key_exists($e['field'], self::$fields))
			{
				$field = self::$fields[$e['field']];
			}

			switch ($e['rule'])
			{
				case 'validate_required':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_REQUIRED', $field);
					break;
				case 'validate_valid_email':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_VALID_EMAIL', $field);
					break;
				case 'validate_max_len':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_MAX_LENGTH', $field, $param);
					break;
				case 'validate_min_len':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_MIN_LENGTH', $field, $param);
					break;
				case 'validate_exact_len':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_EXACT_LENGTH', $field, $param);
					break;
				case 'validate_numeric':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_NUMERIC', $field);
					break;
				case 'validate_integer':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_INTEGER', $field);
					break;
				case 'validate_float':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_FLOAT', $field);
					break;
				case 'validate_valid_url':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_VALID_URL', $field);
					break;
				case 'validate_min_numeric':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_MIN_NUMERIC', $field, $param);
					break;
				case 'validate_max_numeric':
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_MAX_NUMERIC', $field, $param);
					break;
				default:
					$errors[$originalFieldname] = Text::sprintf('ESHOP_ERROR_VALIDATE_INVALID', $field);
					break;
			}
		}

		return $errors;
	}

	/**
	 * Check if the specified key is present and not empty
	 *
	 * Usage: '<index>' => 'required'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 *
	 * @return mixed
	 */
	protected function validate_required($field, $input, $param = null)
	{
		if (isset($input[$field]) &&
			($input[$field] === false || $input[$field] === 0 || $input[$field] === 0.0 || $input[$field] === "0" || !empty($input[$field])))
		{
			return;
		}

		return ['field' => $field, 'value' => null, 'rule' => __FUNCTION__, 'param' => $param];
	}

	/**
	 * Determine if the provided email is valid
	 *
	 * Usage: '<index>' => 'valid_email'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 *
	 * @return mixed
	 */
	protected function validate_valid_email($field, $input, $param = null)
	{
		if (!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if (!filter_var($input[$field], FILTER_VALIDATE_EMAIL))
		{
			return ['field' => $field, 'value' => $input[$field], 'rule' => __FUNCTION__, 'param' => $param];
		}
	}

	/**
	 * Determine if the provided value length is less or equal to a specific value
	 *
	 * Usage: '<index>' => 'max_len,240'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 *
	 * @return mixed
	 */
	protected function validate_max_len($field, $input, $param = null)
	{
		if (!isset($input[$field]))
		{
			return;
		}

		if (function_exists('mb_strlen'))
		{
			if (mb_strlen($input[$field]) <= (int) $param)
			{
				return;
			}
		}
		else
		{
			if (strlen($input[$field]) <= (int) $param)
			{
				return;
			}
		}

		return ['field' => $field, 'value' => $input[$field], 'rule' => __FUNCTION__, 'param' => $param];
	}

	/**
	 * Determine if the provided value length is more or equal to a specific value
	 *
	 * Usage: '<index>' => 'min_len,4'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 *
	 * @return mixed
	 */
	protected function validate_min_len($field, $input, $param = null)
	{
		if (!isset($input[$field]))
		{
			return;
		}

		if (function_exists('mb_strlen'))
		{
			if (mb_strlen($input[$field]) >= (int) $param)
			{
				return;
			}
		}
		else
		{
			if (strlen($input[$field]) >= (int) $param)
			{
				return;
			}
		}

		return ['field' => $field, 'value' => $input[$field], 'rule' => __FUNCTION__, 'param' => $param];
	}

	/**
	 * Determine if the provided value length matches a specific value
	 *
	 * Usage: '<index>' => 'exact_len,5'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 *
	 * @return mixed
	 */
	protected function validate_exact_len($field, $input, $param = null)
	{
		if (!isset($input[$field]))
		{
			return;
		}

		if (function_exists('mb_strlen'))
		{
			if (mb_strlen($input[$field]) == (int) $param)
			{
				return;
			}
		}
		else
		{
			if (strlen($input[$field]) == (int) $param)
			{
				return;
			}
		}

		return ['field' => $field, 'value' => $input[$field], 'rule' => __FUNCTION__, 'param' => $param];
	}

	/**
	 * Determine if the provided value is a valid number or numeric string
	 *
	 * Usage: '<index>' => 'numeric'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 *
	 * @return mixed
	 */
	protected function validate_numeric($field, $input, $param = null)
	{
		if (!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if (!is_numeric($input[$field]))
		{
			return ['field' => $field, 'value' => $input[$field], 'rule' => __FUNCTION__, 'param' => $param];
		}
	}

	/**
	 * Determine if the provided value is a valid integer
	 *
	 * Usage: '<index>' => 'integer'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 *
	 * @return mixed
	 */
	protected function validate_integer($field, $input, $param = null)
	{
		if (!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if (!filter_var($input[$field], FILTER_VALIDATE_INT))
		{
			return ['field' => $field, 'value' => $input[$field], 'rule' => __FUNCTION__, 'param' => $param];
		}
	}

	/**
	 * Determine if the provided value is a valid float
	 *
	 * Usage: '<index>' => 'float'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 *
	 * @return mixed
	 */
	protected function validate_float($field, $input, $param = null)
	{
		if (!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if (!filter_var($input[$field], FILTER_VALIDATE_FLOAT))
		{
			return ['field' => $field, 'value' => $input[$field], 'rule' => __FUNCTION__, 'param' => $param];
		}
	}

	/**
	 * Determine if the provided value is a valid URL
	 *
	 * Usage: '<index>' => 'valid_url'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 *
	 * @return mixed
	 */
	protected function validate_valid_url($field, $input, $param = null)
	{
		if (!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if (!filter_var($input[$field], FILTER_VALIDATE_URL))
		{
			return ['field' => $field, 'value' => $input[$field], 'rule' => __FUNCTION__, 'param' => $param];
		}
	}

	/**
	 * Determine if the provided numeric value is lower or equal to a specific value
	 *
	 * Usage: '<index>' => 'max_numeric,50'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 * @param   null    $param
	 *
	 * @return mixed
	 */
	protected function validate_max_numeric($field, $input, $param = null)
	{
		if (!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}

		if (is_numeric($input[$field]) && is_numeric($param) && ($input[$field] <= $param))
		{
			return;
		}

		return ['field' => $field, 'value' => $input[$field], 'rule' => __FUNCTION__, 'param' => $param];
	}

	/**
	 * Determine if the provided numeric value is higher or equal to a specific value
	 *
	 * Usage: '<index>' => 'min_numeric,1'
	 *
	 * @access protected
	 *
	 * @param   string  $field
	 * @param   array   $input
	 * @param   null    $param
	 *
	 * @return mixed
	 */
	protected function validate_min_numeric($field, $input, $param = null)
	{
		if (!isset($input[$field]) || empty($input[$field]))
		{
			return;
		}
		if ($field == 'zone_id')
		{
			echo $input[$field];
			echo ':' . $param;
		}
		if (is_numeric($input[$field]) && is_numeric($param) && ($input[$field] >= $param))
		{
			return;
		}

		return ['field' => $field, 'value' => $input[$field], 'rule' => __FUNCTION__, 'param' => $param];
	}
} // EOC
