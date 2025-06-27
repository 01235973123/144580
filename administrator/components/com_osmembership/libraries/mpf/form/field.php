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
 * Abstract Form Field class for the MPF framework
 *
 * @package     Joomla.MPF
 * @subpackage  Form
 */
abstract class MPFFormField
{
	/**
	 * Id of the field object in the database
	 *
	 * @var int
	 */
	protected $id;
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type;

	/**
	 * The name (and id) for the form field.
	 *
	 * @var    string
	 */
	protected $name;

	/**
	 * It is a core field or not
	 *
	 * @var int
	 */
	protected $is_core;

	/**
	 * The title for the form field.
	 *
	 * @var    string
	 */
	protected $title;

	/**
	 * The description text for the form field. Usually used in tooltips.
	 *
	 * @var    string
	 */
	protected $description;

	/**
	 * This field is visible or hidden on the form
	 *
	 * @var bool
	 */
	protected $visible = true;
	/**
	 * The indication whether a field is required or not
	 *
	 * @var    bool|int
	 */
	protected $required;

	/**
	 * The value of the form field.
	 *
	 * @var    mixed
	 */
	protected $value;

	/**
	 * The html attributes of the field
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * The extra attributes of the field. You can enter any HTML attributes you want into that field
	 *
	 * @var string
	 */
	protected $extraAttributes;

	/**
	 * The label for the form field.
	 *
	 * @var    string
	 */
	protected $label;

	/**
	 * The input for the form field.
	 *
	 * @var    string
	 */
	protected $input;

	/**
	 * The row object used to store field definition
	 *
	 * @var object
	 */
	protected $row;

	/**
	 * This field is a master field or not
	 *
	 * @var bool
	 */
	protected $isMasterField = false;

	/**
	 * This field is used in fee calculation or not
	 *
	 * @var bool
	 */
	protected $feeCalculation;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   OSMembershipTableField  $row  the table object store form field attribute
	 * @param   mixed                   $value
	 * @param   string                  $fieldSuffix
	 *
	 * @return static
	 *
	 * @throws  RuntimeException
	 */
	public static function create($row, $value = null, $fieldSuffix = null)
	{
		$class = 'MPFFormField' . ucfirst($row->fieldtype);

		if (class_exists($class))
		{
			return new $class($row, $value, $fieldSuffix);
		}

		throw new RuntimeException('The field type ' . $row->fieldType . ' is not supported');
	}

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   OSMembershipTableField  $row  the table object store form field attribute
	 * @param   mixed                   $value
	 * @param   string                  $fieldSuffix
	 */
	public function __construct($row, $value = null, $fieldSuffix = null)
	{
		$this->row             = $row;
		$this->id              = $row->id;
		$this->name            = $row->name . $fieldSuffix;
		$this->is_core         = $row->is_core;
		$this->title           = $row->title;
		$this->description     = $row->description;
		$this->value           = $value;
		$this->required        = $row->required;
		$this->extraAttributes = $row->extra;
		$cssClasses            = [];

		if ($row->css_class)
		{
			$cssClasses[] = $row->css_class;
		}

		if ($row->validation_rules)
		{
			$cssClasses[] = $row->validation_rules;
		}

		if ($row->input_size)
		{
			$cssClasses[] = $row->input_size;
		}

		if (count($cssClasses))
		{
			$this->attributes['class'] = implode(' ', $cssClasses);
		}

		if ($row->validation_error_message)
		{
			$this->attributes['data-errormessage'] = $row->validation_error_message;
		}
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'id':
			case 'type':
			case 'name':
			case 'title':
			case 'description':
			case 'is_core':
			case 'visible':
			case 'required':
			case 'value':
			case 'row':
				return $this->{$name};
			case 'fee_formula':
			case 'fee_field':
			case 'default_values':
			case 'depend_on_field_id':
			case 'depend_on_options':
				return $this->row->{$name};
			case 'input':
				// If the input hasn't yet been generated, generate it.
				if (empty($this->input))
				{
					$this->input = $this->getInput();
				}

				return $this->input;
			case 'label':
				// If the label hasn't yet been generated, generate it.
				if (empty($this->label))
				{
					$this->label = $this->getLabel();
				}

				return $this->label;
		}

		return;
	}

	/**
	 * Magic method to allow setting value of required property of the field
	 *
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value)
	{
		if ($name == 'required')
		{
			$this->{$name} = $value;
		}
	}

	/**
	 * Simple method to set the value
	 *
	 * @param   mixed  $value  Value to set
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Add attribute to the form field
	 *
	 * @param   string  $name
	 */
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
	}

	/**
	 * Get data of the given attribute
	 *
	 * @param   string  $name
	 *
	 * @return string
	 */
	public function getAttribute($name)
	{
		if (isset($this->attributes[$name]))
		{
			return $this->attributes[$name];
		}

		return;
	}

	/**
	 * Method to add new class to the field
	 *
	 * @param   string  $class
	 *
	 * @return void
	 */
	public function addClass($class)
	{
		$classes = $this->getAttribute('class');
		$this->setAttribute('class', $classes ? $classes . ' ' . $class : $class);
	}

	/**
	 * @param   bool  $feeCalculation
	 */
	public function setFeeCalculation($feeCalculation)
	{
		$this->feeCalculation = $feeCalculation;
	}

	/***
	 * Mark a field as master field
	 *
	 * @param $isMasterField
	 */
	public function setMasterField($isMasterField)
	{
		$this->isMasterField = $isMasterField;
	}

	/**
	 * Set visibility status for the field on form
	 *
	 * @param $visible
	 */
	public function setVisibility($visible)
	{
		$this->visible = $visible;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	abstract protected function getInput($bootstrapHelper = null);

	/**
	 * Method to get the field title.
	 *
	 * @return  string  The field title.
	 */
	protected function getTitle()
	{
		return $this->title;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 */
	protected function getLabel()
	{
		$data = [
			'name'        => $this->name,
			'title'       => $this->title,
			'description' => $this->description,
			'row'         => $this->row,
		];

		return OSMembershipHelperHtml::loadCommonLayout('fieldlayout/label.php', $data);
	}

	/**
	 * Method to get a control group with label and input.
	 *
	 * @param   OSMembershipHelperBootstrap  $bootstrapHelper
	 * @param   bool                         $enableGrid
	 *
	 * @return  string  A string containing the html for the control goup
	 */
	public function getControlGroup($bootstrapHelper = null, $enableGrid = false)
	{
		if ($this->type == 'Hidden')
		{
			return $this->getInput();
		}
		$controlGroupAttributes = 'id="field_' . $this->name . '" ';

		if (!$this->visible)
		{
			$controlGroupAttributes .= ' style="display:none;" ';
		}

		$classes   = [];
		$classes[] = 'field-type-' . strtolower($this->row->fieldtype);

		if ($this->feeCalculation)
		{
			$classes[] = 'payment-calculation';
		}

		if ($this->isMasterField)
		{
			$classes[] = 'master-field';
		}

		if ($enableGrid && $this->row->container_size)
		{
			$classes[] = $bootstrapHelper ? $bootstrapHelper->getClassMapping($this->row->container_size) : $this->row->container_size;
		}

		if ($this->row->container_class)
		{
			$classes[] = $this->row->container_class;
		}

		$class = implode(' ', $classes);

		if (!empty($classes))
		{
			$class = ' ' . implode(' ', $classes);
		}

		$data = [
			'name'                   => $this->name,
			'description'            => $this->description,
			'class'                  => $class,
			'controlGroupAttributes' => $controlGroupAttributes,
			'label'                  => $this->getLabel(),
			'input'                  => $this->getInput($bootstrapHelper),
			'bootstrapHelper'        => $bootstrapHelper,
			'row'                    => $this->row,
		];

		return OSMembershipHelperHtml::loadCommonLayout('fieldlayout/controlgroup.php', $data);
	}

	/**
	 * Get output of the field using for sending email and display on the registration complete page
	 *
	 * @param   bool                         $tableLess
	 *
	 * @param   OSMembershipHelperBootstrap  $bootstrapHelper
	 *
	 * @return string
	 */
	public function getOutput($tableLess = true, $bootstrapHelper = null)
	{
		$fieldValue = $this->getDisplayValue();

		$data = [
			'name'        => $this->name,
			'title'       => $this->title,
			'description' => $this->description,
			'row'         => $this->row,
			'fieldValue'  => $fieldValue,
			'rawValue'    => $this->getRawDisplayValue(),
		];

		if ($tableLess)
		{
			$controlGroupAttributes = 'id="field_' . $this->name . '" ';

			if (!$this->visible)
			{
				$controlGroupAttributes .= ' style="display:none;" ';
			}

			$data['bootstrapHelper']        = $bootstrapHelper;
			$data['controlGroupAttributes'] = $controlGroupAttributes;

			return OSMembershipHelperHtml::loadCommonLayout('fieldlayout/fieldoutput.php', $data);
		}

		return OSMembershipHelperHtml::loadCommonLayout('fieldlayout/fieldoutput_table.php', $data);
	}

	/**
	 * Get display value for custom field
	 *
	 * @return string
	 */
	public function getDisplayValue()
	{
		$fieldValue = $this->getRawDisplayValue();

		$data = [
			'row'        => $this->row,
			'fieldValue' => $fieldValue,
			'rawValue'   => $this->value,
		];

		return OSMembershipHelperHtml::loadCommonLayout('fieldlayout/fieldvalue.php', $data);
	}

	/**
	 * Get raw display value
	 *
	 * @return mixed|string
	 */
	protected function getRawDisplayValue()
	{
		if (is_string($this->value) && is_array(json_decode($this->value)))
		{
			return implode(', ', json_decode($this->value));
		}

		return $this->value;
	}

	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @param   array  $attributes
	 *
	 * @return string
	 */
	public function buildAttributes()
	{
		$html = [];

		foreach ((array) $this->attributes as $key => $value)
		{
			if (is_bool($value))
			{
				$html[] = " $key ";
			}
			else
			{
				$html[] = $key . '="' . htmlentities($value, ENT_QUOTES, 'UTF-8', false) . '"';
			}
		}

		if ($this->extraAttributes)
		{
			$html[] = $this->extraAttributes;
		}

		return count($html) > 0 ? ' ' . implode(' ', $html) : '';
	}
}
