<?php
/**
 * Form Field class for the Joomla EshopRAD.
 * Supports a generic list of options.
 *
 * @package     Joomla.EshopRAD
 * @subpackage  Form
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class EshopRADFormFieldList extends EshopRADFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'List';
	/**
	 * This is multiple select?
	 * @var int
	 */
	protected $multiple = 0;
	/**
	 * Options in the form field
	 * @var array
	 */
	protected $options = [];

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

		if ($row->multiple)
		{
			$this->attributes['multiple'] = true;
			$this->multiple               = 1;
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
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 */
	protected function getInput()
	{
		// Get the field options.
		$options    = (array) $this->getOptions();
		$attributes = $this->buildAttributes();
		if ($this->multiple)
		{
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
		}
		else
		{
			$selectedOptions = $this->value;
		}

		return HTMLHelper::_(
			'select.genericlist',
			$options,
			$this->name . ($this->multiple ? '[]' : ''),
			trim($attributes . $this->extraAttributes),
			'value',
			'text',
			$selectedOptions
		);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 */
	protected function getOptions()
	{
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('ESHOP_PLEASE_SELECT'));
		foreach ($this->options as $option)
		{
			$options[] = HTMLHelper::_('select.option', trim($option), $option);
		}

		return $options;
	}
}
