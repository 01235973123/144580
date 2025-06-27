<?php
/**
 * Abstract Form Field class for the EshopRAD framework
 *
 * @package     Joomla.EshopRAD
 * @subpackage  Form
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

abstract class EshopRADFormField
{

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
	 * Title of the form field
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Description of the form field
	 * @var string
	 */
	protected $description;

	/**
	 * The current value of the form field.
	 *
	 * @var    mixed
	 */
	protected $value;
	/**
	 * The form field is required or not
	 *
	 * @var int
	 */
	protected $required;
	/**
	 * Any other extra attributes of the custom fields
	 *
	 * @var string
	 */
	protected $extraAttributes;

	/**
	 * The html attributes of the field
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * The input for the form field.
	 *
	 * @var    string
	 */
	protected $input;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Table  $row    the table object store form field definitions
	 * @param   mixed  $value  the initial value of the form field
	 *
	 */
	public function __construct($row, $value = null)
	{
		$this->name            = $row->name;
		$this->title           = Text::_($row->title);
		$this->description     = $row->description;
		$this->required        = $row->required;
		$this->extraAttributes = $row->extra_attributes;
		$this->value           = $value;
		$cssClasses            = [];
		if ($row->css_class)
		{
			$this->attributes['class'] = $row->css_class;
		}
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'type':
			case 'name':
			case 'title':
			case 'description':
			case 'value':
			case 'extraAttributes':
			case 'required':
				return $this->{$name};
			case 'input':
				// If the input hasn't yet been generated, generate it.
				if (empty($this->input))
				{
					$this->input = $this->getInput();
				}

				return $this->input;
		}

		return null;
	}

	/**
	 * Simple method to set the value for the form field
	 *
	 * @param   mixed  $value  Value to set
	 *
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 *
	 * Simple method to get the value for the form field
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 */
	abstract protected function getInput();

	/**
	 * Method to get a control group with label and input.
	 *
	 * @return  string  A string containing the html for the control goup
	 *
	 */
	public function getControlGroup($tableLess = true)
	{
		$bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
		if ($this->type == 'hidden')
		{
			return $this->getInput();
		}
		else
		{
			if ($tableLess)
			{
				return '<div class="' . $bootstrapHelper->getClassMapping('control-group') . '">
							<label class="' . $bootstrapHelper->getClassMapping(
						'control-label'
					) . '" for="' . $this->name . '">' . ($this->required ? '<span class="required">*</span>' : '')
					. $this->title . '
							</label>
						<div class="' . $bootstrapHelper->getClassMapping('controls') . ' docs-input-sizes">
							' . $this->getInput() . '
						</div>
					</div>';
			}
			else
			{
				return '<tr>
							<td class="key">' . ($this->required ? '<span class="required">*</span>' : '')
					. $this->title . '
							</td>
						<td>
							' . $this->getInput() . '
						</td>
					</tr>';
			}
		}
	}

	/**
	 * Get output of the field using for sending email and display on the registration complete page
	 *
	 * @param   bool  $tableless
	 *
	 * @return string
	 */
	public function getOutput($tableLess = true)
	{
		if (is_string($this->value) && is_array(json_decode($this->value)))
		{
			$fieldValue = implode(', ', json_decode($this->value));
		}
		else
		{
			$fieldValue = $this->value;
		}
		if ($tableLess)
		{
			return '<div class="control-group"><div class="control-label">' . $this->title . '</div>' . '<div class="controls">' . $fieldValue .
				'</div>' . '</div>';
		}
		else
		{
			return '<tr><td class="title_cell">' . $this->title . '</td>' . '<td class="field_cell">' . $fieldValue . '</td>' . '</tr>';
		}
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

		return count($html) > 0 ? ' ' . implode(' ', $html) : '';
	}
}
