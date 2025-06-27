<?php

/**
 * @package     MPF
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Valitron\Validator;

/**
 * Form Class for handling custom fields
 *
 * @package        MPF
 * @subpackage     Form
 */
class MPFForm
{
	/**
	 * The array hold list of custom fields
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Form Data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Language use for validator, default en
	 * @var
	 */
	protected $lang;

	/**
	 * Flag to mark if dependency was already processed before
	 *
	 * @var bool
	 */
	protected $dependencyProcessed = false;

	/**
	 * Constructor
	 *
	 * @param   array   $rows
	 * @param   array   $data
	 * @param   string  $fieldSuffix
	 */
	public function __construct($rows, $data = [], $fieldSuffix = null)
	{
		$hasInputMask = false;

		foreach ($rows as $row)
		{
			if ($row->input_mask)
			{
				$hasInputMask = true;
			}

			$class = 'MPFFormField' . ucfirst($row->fieldtype);

			if (class_exists($class))
			{
				$this->fields[$row->name] = new $class($row, $row->default_values, $fieldSuffix);
			}
			else
			{
				throw new RuntimeException('The field type ' . $row->fieldType . ' is not supported');
			}
		}

		$this->data = $data;

		if (count($this->data))
		{
			$this->bindData();
		}

		if ($hasInputMask)
		{
			Factory::getApplication()
				->getDocument()
				->getWebAssetManager()
				->registerAndUseScript('com_osmembership.imask', 'media/com_osmembership/assets/js/imask/imask.min.js');
		}
	}

	/**
	 * Method to bind data to the fields.
	 *
	 * @param   bool  $useDefault
	 *
	 * @return $this
	 */
	public function bindData($useDefault = false)
	{
		if (count($this->fields))
		{
			foreach ($this->fields as $field)
			{
				if ($field->type == 'State')
				{
					$fieldName = $field->name;
					$prefix    = str_replace('state', '', $fieldName);

					if (!empty($this->data['country' . $prefix]))
					{
						$field->country = $this->data['country' . $prefix];
					}
				}

				if (isset($this->data[$field->name]))
				{
					$field->setValue($this->data[$field->name]);
				}
				elseif ($useDefault)
				{
					$field->setValue($field->default_values);
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
	 * Get fields of form
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Set data for the form
	 *
	 * @param $data
	 *
	 * @return $this
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * Hide fields which does not match the payment methods
	 *
	 * @param $paymentMethod
	 *
	 * @return void
	 */
	public function handleFieldsDependOnPaymentMethod($paymentMethod)
	{
		foreach ($this->fields as $field)
		{
			if ($field->row->payment_method && $field->row->payment_method != $paymentMethod)
			{
				$field->setVisibility(false);
			}
		}
	}

	/**
	 * Build the custom field dependency
	 *
	 * @var bool $processMasterFields
	 */
	public function buildFieldsDependency($processMasterFields = true)
	{
		if ($this->dependencyProcessed)
		{
			// Already processed before, skip
			return;
		}

		$masterFields = [];
		$fieldsAssoc  = [];

		foreach ($this->fields as $field)
		{
			if ($field->depend_on_field_id)
			{
				$masterFields[] = $field->depend_on_field_id;
			}

			$fieldsAssoc[$field->id] = $field;
		}

		$masterFields = array_unique($masterFields);

		if (count($masterFields))
		{
			$hiddenFields = [];

			foreach ($this->fields as $field)
			{
				if (in_array($field->id, $masterFields) && $processMasterFields)
				{
					$field->setFeeCalculation(true);
					$field->setMasterField(true);

					switch (strtolower($field->type))
					{
						case 'list':
							$field->setAttribute('onchange', "showHideDependFields($field->id, '$field->name', '$field->type');");
							break;
						case 'radio':
						case 'checkboxes':
							$field->setAttribute('onclick', "showHideDependFields($field->id, '$field->name', '$field->type');");
							break;
					}
				}

				if ($field->depend_on_field_id && isset($fieldsAssoc[$field->depend_on_field_id]))
				{
					if (in_array($field->depend_on_field_id, $hiddenFields))
					{
						$field->setVisibility(false);
						$hiddenFields[] = $field->id;
					}
					else
					{
						$masterFieldValues = $fieldsAssoc[$field->depend_on_field_id]->value;

						if (is_array($masterFieldValues))
						{
							$selectedOptions = $masterFieldValues;
						}
						elseif (is_string($masterFieldValues) && strpos($masterFieldValues, "\r\n"))
						{
							$selectedOptions = explode("\r\n", $masterFieldValues);
						}
						elseif (is_string($masterFieldValues) && is_array(json_decode($masterFieldValues)))
						{
							$selectedOptions = json_decode($masterFieldValues);
						}
						else
						{
							$selectedOptions = [$masterFieldValues];
						}

						if (is_string($field->depend_on_options) && is_array(json_decode($field->depend_on_options)))
						{
							$dependOnOptions = json_decode($field->depend_on_options);
						}
						else
						{
							$dependOnOptions = explode(',', $field->depend_on_options);
						}

						if (!count(array_intersect($selectedOptions, $dependOnOptions)))
						{
							$field->setVisibility(false);
							$hiddenFields[] = $field->id;
						}
					}
				}
			}
		}

		// Mark as dependency processed to avoid the system process it again
		$this->dependencyProcessed = true;
	}

	/**
	 * Check if the form contains fee fields or not
	 *
	 * @return bool
	 */
	public function containFeeFields()
	{
		$containFeeFields = false;

		foreach ($this->fields as $field)
		{
			if ($field->fee_field)
			{
				$containFeeFields = true;
				break;
			}
		}

		return $containFeeFields;
	}

	/**
	 * Prepare form field, add necessary javascript trigger
	 *
	 * @param $calculationFeeMethod
	 */
	public function prepareFormFields($calculationFeeMethod)
	{
		$feeFormula = '';

		foreach ($this->fields as $field)
		{
			if ($field->fee_formula)
			{
				$feeFormula .= $field->fee_formula;
			}
		}

		foreach ($this->fields as $field)
		{
			if ($field->fee_field || str_contains($feeFormula, '[' . strtoupper($field->name) . ']'))
			{
				$field->setFeeCalculation(true);

				switch ($field->type)
				{
					case 'List':
					case 'Text':
					case 'Range':
					case 'Number':
						$field->setAttribute('onchange', $calculationFeeMethod);
						break;
					case 'Checkboxes':
					case 'Radio':
						$field->setAttribute('onclick', $calculationFeeMethod);
						break;
				}
			}
		}
	}

	/**
	 * Method to store custom fields data for a subscription
	 *
	 * @param   int    $subscriptionId
	 * @param   array  $data
	 *
	 * @return bool
	 */
	public function storeFormData($subscriptionId, $data)
	{
		// Return early in case there is no form fields
		if (!count($this->fields))
		{
			return true;
		}

		/* @var \Joomla\Database\DatabaseDriver $db */
		$db         = Factory::getContainer()->get('db');
		$query      = $db->getQuery(true);
		$config     = OSMembershipHelper::getConfig();
		$dateFormat = $config->date_field_format ?: '%Y-%m-%d';
		$dateFormat = str_replace('%', '', $dateFormat);

		// Don't delete the file upload custom fields
		$fieldIds     = [];
		$fileFieldIds = [];

		foreach ($this->fields as $field)
		{
			if ($field->type == 'File')
			{
				$fileFieldIds[] = $field->id;
			}
			else
			{
				$fieldIds[] = $field->id;
			}
		}

		if (count($fieldIds))
		{
			$query->delete('#__osmembership_field_value')
				->where('subscriber_id = ' . (int) $subscriptionId)
				->whereIn('field_id', $fieldIds);
			$db->setQuery($query)
				->execute();
		}

		$rowFieldValue = new OSMembershipTableFieldvalue($db);

		foreach ($this->fields as $field)
		{
			// Do not store data for field which is hidden by dependency
			if (!$field->visible)
			{
				continue;
			}

			$fieldType = strtolower($field->type);

			if ($fieldType == 'date')
			{
				$fieldValue = empty($data[$field->name]) ? '' : $data[$field->name];

				if ($fieldValue)
				{
					// Try to convert the format
					try
					{
						$date = DateTime::createFromFormat($dateFormat, $fieldValue);

						if ($date)
						{
							$fieldValue = $date->format('Y-m-d');
						}
						elseif (!OSMembershipHelper::isValidDate($fieldValue))
						{
							$fieldValue = '';
						}
					}
					catch (Exception $e)
					{
						$fieldValue = '';
					}

					$data[$field->name] = $fieldValue;
				}
			}

			$fieldValue = $data[$field->name] ?? '';

			if (in_array($field->id, $fileFieldIds))
			{
				// Need to delete the old file
				$query->clear()
					->delete('#__osmembership_field_value')
					->where('subscriber_id = ' . (int) $subscriptionId)
					->where('field_id = ' . $field->id);
				$db->setQuery($query);
				$db->execute();
			}

			$rowFieldValue->id            = 0;
			$rowFieldValue->field_id      = $field->id;
			$rowFieldValue->subscriber_id = $subscriptionId;

			if (is_array($fieldValue))
			{
				$rowFieldValue->field_value = json_encode($fieldValue, JSON_UNESCAPED_UNICODE);
			}
			else
			{
				$rowFieldValue->field_value = $fieldValue;
			}

			$rowFieldValue->store();
		}

		return true;
	}

	/**
	 * Store subscriber data into database
	 *
	 * @param $recordId
	 * @param $data
	 *
	 * @return bool
	 */
	public function storeData($recordId, $data, $excludeFeeFields = false)
	{
		if (!count($this->fields))
		{
			return true;
		}

		$config = OSMembershipHelper::getConfig();

		$accessLevels = Factory::getApplication()->getIdentity()->getAuthorisedViewLevels();

		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		$dateFormat = $config->date_field_format ?: '%Y-%m-%d';
		$dateFormat = str_replace('%', '', $dateFormat);

		// Don't delete the file upload custom fields
		$fieldIds     = [0];
		$fileFieldIds = [0];

		foreach ($this->fields as $field)
		{
			if ($field->type == 'File')
			{
				$fileFieldIds[] = $field->id;
			}
			elseif ((!$excludeFeeFields || !$field->fee_field) && in_array($field->row->access, $accessLevels))
			{
				$fieldIds[] = $field->id;
			}
		}

		$query->delete('#__osmembership_field_value')
			->where('subscriber_id = ' . (int) $recordId)
			->whereIn('field_id', $fieldIds);
		$db->setQuery($query)
			->execute();

		$rowFieldValue = new OSMembershipTableFieldvalue($db);

		foreach ($this->fields as $field)
		{
			// Do not store data for field which is hidden by dependency
			if (!$field->visible)
			{
				continue;
			}

			$fieldType = strtolower($field->type);

			if ($field->is_core || $fieldType == 'heading' || $fieldType == 'message')
			{
				continue;
			}

			// Don't update fee field if not needed
			if ($excludeFeeFields && $field->fee_field)
			{
				continue;
			}

			if ($fieldType == 'date')
			{
				$fieldValue = $data[$field->name];

				if ($fieldValue)
				{
					// Try to convert the format
					try
					{
						$date = DateTime::createFromFormat($dateFormat, $fieldValue);

						if ($date)
						{
							$fieldValue = $date->format('Y-m-d');
						}
						else
						{
							$fieldValue = '';
						}
					}
					catch (Exception $e)
					{
						$fieldValue = '';
					}

					$data[$field->name] = $fieldValue;
				}
			}

			$fieldValue = $data[$field->name] ?? '';

			if ($fieldValue != '')
			{
				if (in_array($field->id, $fileFieldIds))
				{
					// Need to delete the old file
					$query->clear()
						->delete('#__osmembership_field_value')
						->where('subscriber_id = ' . (int) $recordId)
						->where('field_id = ' . $field->id);
					$db->setQuery($query)
						->execute();
				}

				$rowFieldValue->id            = 0;
				$rowFieldValue->field_id      = $field->id;
				$rowFieldValue->subscriber_id = $recordId;

				if (is_array($fieldValue))
				{
					$rowFieldValue->field_value = json_encode($fieldValue);
				}
				else
				{
					$rowFieldValue->field_value = $fieldValue;
				}

				$rowFieldValue->store();
			}
		}

		return true;
	}

	/**
	 * Calculate total fee generated by all fields on the form
	 *
	 * @param   array  $replaces
	 *
	 * @return float total fee
	 */
	public function calculateFee($replaces = [])
	{
		if (!count($this->fields))
		{
			return 0;
		}

		if (!isset($replaces['PLAN_PRICE']))
		{
			$replaces['PLAN_PRICE'] = 1;
		}

		$fee = 0;
		$this->buildFieldsDependency();
		$fieldsFee = $this->calculateFieldsFee();

		foreach ($this->fields as $field)
		{
			if (!$field->visible)
			{
				continue;
			}

			if (!$field->row->fee_field)
			{
				continue;
			}

			if (strtolower($field->type) == 'text' || $field->row->fee_formula)
			{
				// Maybe we need to check fee formula
				if (!$field->row->fee_formula)
				{
					continue;
				}
				$formula = $field->row->fee_formula;
				$formula = str_replace('[FIELD_VALUE]', (float) $field->value, $formula);

				foreach ($fieldsFee as $fieldName => $fieldFee)
				{
					$fieldName = strtoupper($fieldName);
					$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
				}

				foreach ($replaces as $fieldName => $fieldFee)
				{
					$fieldName = strtoupper($fieldName);
					$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
				}

				$feeValue = 0;

				if ($formula)
				{
					@eval('$feeValue = ' . $formula . ';');
					$fee += $feeValue;
				}
			}
			else
			{
				$feeValues = explode("\r\n", $field->row->fee_values);
				$values    = explode("\r\n", $field->row->values);
				$values    = array_map('trim', $values);

				if (is_array($field->value))
				{
					$fieldValues = $field->value;
				}
				elseif (is_string($field->value) && is_array(json_decode($field->value)))
				{
					$fieldValues = json_decode($field->value);
				}
				elseif ($field->value)
				{
					$fieldValues   = [];
					$fieldValues[] = $field->value;
				}
				else
				{
					$fieldValues = [];
				}

				foreach ($fieldValues as $fieldValue)
				{
					$fieldValue      = trim($fieldValue);
					$fieldValueIndex = array_search($fieldValue, $values);

					if ($fieldValueIndex !== false && isset($feeValues[$fieldValueIndex]))
					{
						$fee += (float) $feeValues[$fieldValueIndex];
					}
				}
			}
		}

		return $fee;
	}

	/**
	 * Calculate Form Fees
	 *
	 * @param   array  $replaces
	 *
	 * @return float
	 */
	public function calculateFormFee(&$replaces = [])
	{
		if (!count($this->fields))
		{
			$replaces['none_taxable_fee'] = 0;

			return 0;
		}

		if (!isset($replaces['PLAN_PRICE']))
		{
			$replaces['PLAN_PRICE'] = 1;
		}

		$this->buildFieldsDependency();

		$fieldsFee = $this->calculateFieldsFee();

		$fee             = 0;
		$noneTaxableFee  = 0;
		$trialFee        = 0;
		$recurringFee    = 0;
		$fieldsFeeValues = [];

		foreach ($this->fields as $field)
		{
			if (!$field->visible)
			{
				continue;
			}

			if (!$field->row->fee_field)
			{
				continue;
			}

			// Set default fee formula if not set by admin
			if (!$field->row->fee_formula && in_array($field->type, ['Text', 'Number', 'Range', 'Hidden']))
			{
				$field->row->fee_formula = '[FIELD_VALUE]';
			}

			$feeValue = 0;

			if ($field->row->fee_formula)
			{
				$formula = $field->row->fee_formula;
				$formula = str_replace('[FIELD_VALUE]', (float) $field->value, $formula);

				foreach ($fieldsFee as $fieldName => $fieldFee)
				{
					$fieldName = strtoupper($fieldName);
					$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
				}

				foreach ($replaces as $fieldName => $fieldFee)
				{
					$fieldName = strtoupper($fieldName);
					$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
				}

				if ($formula)
				{
					@eval('$feeValue = ' . $formula . ';');
				}
			}
			else
			{
				$feeValues = explode("\r\n", $field->row->fee_values);
				$values    = explode("\r\n", $field->row->values);
				$values    = array_map('trim', $values);

				if (is_array($field->value))
				{
					$fieldValues = $field->value;
				}
				elseif (is_string($field->value) && is_array(json_decode($field->value)))
				{
					$fieldValues = json_decode($field->value);
				}
				elseif ($field->value)
				{
					$fieldValues   = [];
					$fieldValues[] = $field->value;
				}
				else
				{
					$fieldValues = [];
				}

				foreach ($fieldValues as $fieldValue)
				{
					$fieldValue      = trim($fieldValue);
					$fieldValueIndex = array_search($fieldValue, $values);

					if ($fieldValueIndex !== false && isset($feeValues[$fieldValueIndex]))
					{
						$fieldValueFee = $feeValues[$fieldValueIndex];

						if (str_contains($fieldValueFee, '['))
						{
							$formula = $fieldValueFee;

							foreach ($fieldsFee as $fieldName => $fieldFee)
							{
								$fieldName = strtoupper($fieldName);
								$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
							}

							foreach ($replaces as $fieldName => $fieldFee)
							{
								$fieldName = strtoupper($fieldName);
								$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
							}

							@eval('$fieldValueFee = ' . $formula . ';');

							$feeValue += (float) $fieldValueFee;
						}
						else
						{
							$feeValue += (float) $fieldValueFee;
						}
					}
				}
			}

			$fee += $feeValue;

			if (!$field->row->taxable)
			{
				$noneTaxableFee += $feeValue;
			}

			switch ($field->row->fee_usage)
			{
				case 0:
					$trialFee += $feeValue;
					break;
				case 1:
					$recurringFee += $feeValue;
					break;
				case 2:
					$trialFee     += $feeValue;
					$recurringFee += $feeValue;
					break;
			}

			$fieldsFeeValues[$field->row->name] = $feeValue;
		}

		$replaces['none_taxable_fee']  = $noneTaxableFee;
		$replaces['trial_fee']         = $trialFee;
		$replaces['recurring_fee']     = $recurringFee;
		$replaces['fields_fee_values'] = $fieldsFeeValues;

		return $fee;
	}

	/**
	 * Calculate the fee associated with each field to use in fee formula
	 *
	 * @return array
	 */
	private function calculateFieldsFee()
	{
		$fieldsFee     = [];
		$feeFieldTypes = ['text', 'range', 'number', 'radio', 'list', 'checkboxes', 'hidden'];

		foreach ($this->fields as $fieldName => $field)
		{
			$fieldType = strtolower($field->type);

			if (!in_array($fieldType, $feeFieldTypes))
			{
				continue;
			}

			if (in_array($fieldType, ['text', 'number', 'range', 'hidden']))
			{
				$fieldsFee[$fieldName] = floatval($field->value);
			}
			elseif ($fieldType == 'checkboxes' || ($fieldType == 'list' && $field->row->multiple))
			{
				$feeValues = explode("\r\n", $field->row->fee_values);
				$values    = explode("\r\n", $field->row->values);
				$feeAmount = 0;

				if (is_array($field->value))
				{
					$selectedOptions = $field->value;
				}
				elseif (is_string($field->value) && strpos($field->value, "\r\n"))
				{
					$selectedOptions = explode("\r\n", $field->value);
				}
				elseif (is_string($field->value) && is_array(json_decode($field->value)))
				{
					$selectedOptions = json_decode($field->value);
				}
				else
				{
					$selectedOptions = [$field->value];
				}

				if (is_array($selectedOptions))
				{
					foreach ($selectedOptions as $selectedOption)
					{
						$index = array_search($selectedOption, $values);

						if ($index !== false && isset($feeValues[$index]))
						{
							$feeAmount += floatval($feeValues[$index]);
						}
					}
				}

				$fieldsFee[$fieldName] = $feeAmount;
			}
			else
			{
				$feeValues  = explode("\r\n", $field->row->fee_values);
				$values     = explode("\r\n", $field->row->values);
				$values     = array_map('trim', $values);
				$valueIndex = array_search(trim((string) $field->value), $values);

				if ($valueIndex !== false && isset($feeValues[$valueIndex]))
				{
					$fieldsFee[$fieldName] = floatval($feeValues[$valueIndex]);
				}
				else
				{
					$fieldsFee[$fieldName] = 0;
				}
			}
		}

		return $fieldsFee;
	}

	/**
	 *  Calculate Fee Parts using for invoice
	 *
	 * @return array
	 */
	public function calculateFeeParts()
	{
		if (!count($this->fields))
		{
			return [];
		}

		$totalFeeAmount = 0;
		$fees           = [];
		$this->buildFieldsDependency();
		$fieldsFee = $this->calculateFieldsFee();

		foreach ($this->fields as $field)
		{
			if (!$field->visible)
			{
				continue;
			}

			if (!$field->row->fee_field)
			{
				continue;
			}

			if (strtolower($field->type) == 'text' || $field->row->fee_formula)
			{
				// Maybe we need to check fee formula
				if (!$field->row->fee_formula)
				{
					continue;
				}

				$formula = $field->row->fee_formula;
				$formula = str_replace('[FIELD_VALUE]', $field->value, $formula);

				if (count($fieldsFee))
				{
					foreach ($fieldsFee as $fieldName => $fieldFee)
					{
						$fieldName = strtoupper($fieldName);
						$formula   = str_replace('[' . $fieldName . ']', $fieldFee, $formula);
					}
				}

				$feeValue = 0;

				if ($formula)
				{
					@eval('$feeValue = ' . $formula . ';');
					$fees[$field->name]['title']     = $field->title;
					$fees[$field->name]['value']     = $field->value;
					$fees[$field->name]['fee_value'] = $feeValue;
				}
			}
			else
			{
				$feeValue  = 0;
				$feeValues = explode("\r\n", $field->row->fee_values);
				$values    = explode("\r\n", $field->row->values);
				$values    = array_map('trim', $values);

				if (is_array($field->value))
				{
					$fieldValues = $field->value;
				}
				elseif ($field->value)
				{
					$fieldValues   = [];
					$fieldValues[] = $field->value;
				}
				else
				{
					$fieldValues = [];
				}

				foreach ($fieldValues as $fieldValue)
				{
					$fieldValue      = trim($fieldValue);
					$fieldValueIndex = array_search($fieldValue, $values);

					if ($fieldValueIndex !== false && isset($feeValues[$fieldValueIndex]))
					{
						$feeValue += (float) $feeValues[$fieldValueIndex];
					}
				}

				if ($feeValue > 0)
				{
					$fees[$field->name]['title']     = $field->title;
					$fees[$field->name]['value']     = $field->value;
					$fees[$field->name]['fee_value'] = $feeValue;
					$totalFeeAmount                  += $feeValue;
				}
			}
		}

		$fees['total_fee_amount'] = $totalFeeAmount;

		return $fees;
	}

	/**
	 * Set validator language
	 *
	 * @param   string  $lang
	 */
	public function setValidatorLanguage($lang)
	{
		$lang = strtolower($lang);

		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries/vendor/valitron/lang/' . $lang . '.php'))
		{
			$this->lang = $lang;

			return;
		}

		$parts = explode('-', $lang);

		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_osmembership/libraries/vendor/valitron/lang/' . $parts[0] . '.php'))
		{
			$this->lang = $parts[0];

			return;
		}

		// Use default language if the passed language does not exist
		$this->lang = 'en';
	}

	/**
	 * Validate form data
	 */
	public function validate()
	{
		$errors          = [];
		$validationRules = [];
		$labels          = [];
		$data            = [];
		$fields          = $this->getFields();

		/* @var MPFFormField $field */
		foreach ($fields as $field)
		{
			if (!$field->visible)
			{
				continue;
			}

			// Ignore State, Heading, Message validation since these field types don't need to have data
			$fieldType = strtolower($field->type);

			if (in_array($fieldType, ['heading', 'message']))
			{
				continue;
			}

			$data[$field->name]   = $field->value;
			$labels[$field->name] = $field->title;

			$fieldRules = [];

			// Required rule
			if ($field->required && $fieldType != 'state')
			{
				$fieldRules[] = 'required';
			}

			// Custom rules
			if ($field->row->server_validation_rules)
			{
				$rules = explode('|', $field->row->server_validation_rules);

				foreach ($rules as $rule)
				{
					$parts    = explode(':', $rule);
					$ruleName = $parts[0];

					if (count($parts) > 1)
					{
						$params = explode(',', $parts[1]);
						$params = array_map('trim', $params);

						// The
						if (in_array($ruleName, ['in', 'notIn']))
						{
							$fieldRules[] = [$ruleName, $params];
						}
						else
						{
							$fieldRules[] = array_merge([$ruleName], $params);
						}
					}
					else
					{
						$fieldRules[] = $ruleName;
					}
				}
			}

			if (count($fieldRules))
			{
				$validationRules[$field->name] = $fieldRules;
			}
		}

		// Load custom validators if exist
		if (file_exists(JPATH_ROOT . '/components/com_osmembership/helper/validator.php'))
		{
			require_once JPATH_ROOT . '/components/com_osmembership/helper/validator.php';
		}

		// Set validation language
		if (empty($this->lang))
		{
			$this->setValidatorLanguage(Factory::getApplication()->getLanguage()->getTag());
		}

		Validator::lang($this->lang);

		// Create validator object
		$v = new Validator($data);
		$v->mapFieldsRules($validationRules);
		$v->labels($labels);

		// Perform validation and return error message
		if (!$v->validate())
		{
			foreach ($v->errors() as $fieldName => $errorMessages)
			{
				$field = $fields[$fieldName];

				// If the field has a custom error message, use it
				if (!empty($field->row->validation_error_message))
				{
					$errors[] = str_ireplace('[FIELD_NAME]', $field->title, $field->row->validation_error_message);
				}
				else
				{
					$errors = array_merge($errors, $errorMessages);
				}
			}
		}

		return $errors;
	}
}
