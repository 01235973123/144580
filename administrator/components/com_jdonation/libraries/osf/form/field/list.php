<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for the Joomla OSF.
 * Supports a generic list of options.
 *
 * @package     Joomla.OSF
 * @subpackage  Form
 */
class OSFFormFieldList extends OSFFormField
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
	 * @param   JTable  $row  the table object store form field definitions
	 * @param	mixed	$value the initial value of the form field
	 *
	 */
	public function __construct($row, $value)
	{
		parent::__construct($row, $value);	
			
		if ($row->multiple)
		{
			$this->attributes['multiple'] = true;
			$this->multiple = 1;			
		}				
		if (is_array($row->values))
		{
			$this->options = $row->values;
		}
		elseif (strpos($row->values, "\r\n") !== FALSE)
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
	public function getInput($bootstrapHelper = null)
	{
		if($bootstrapHelper == null)
		{
			$formControlClass	= 'form-control';
		}
		else
		{
			$formControlClass	= $bootstrapHelper->getClassMapping('form-control');
		}
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))			
		{
			$formControlClass .= " form-select ";
		}
		// Get the field options.
		$options = (array) $this->getOptions();
		$attributes = $this->buildAttributes();
		if($campaignId > 0)
		{
			$campaignClass = 'campaign_'.$campaignId.' ';
		}
		if($attributes != "")
		{
			$attributes = str_replace('class="','class="'.$campaignClass. ' '.$formControlClass. ' ',$attributes);
		}
		else
		{
			$attributes = 'class="'.$campaignClass.' '.$formControlClass. ' "';
		}
		$attributes .= ' aria-label="'.$this->title.'" ';
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
				$selectedOptions = array($this->value);
			}
		}
		else
		{
			$selectedOptions = $this->value;
		}
		return HTMLHelper::_('select.genericlist', $options, $this->name . ($this->multiple ? '[]' : ''), trim($attributes . $this->extraAttributes), 'value', 'text', $selectedOptions);
	}

    public function getInputSimple($bootstrapHelper = null,$field, $controlGroupAttributes, $campaignId = 0)
    {
		$config				= DonationHelper::getConfig();
        // Get the field options.
		$formControlClass	= $bootstrapHelper->getClassMapping('form-control');
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))			
		{
			$formControlClass .= " form-select ";
		}
        $options			= (array) $this->getOptionsSimple($field);
		$attributes			= $this->buildAttributes();
		if($campaignId > 0)
		{
			$campaignClass = 'campaign_'.$campaignId.' ';
		}
		if($attributes != "")
		{
			$attributes = str_replace("input-large","",$attributes);
			$attributes = str_replace('class="','class="jd_width_100_percentage '.$campaignClass. ' '.$formControlClass .' ',$attributes);
		}
		else
		{
			$attributes = 'class="jd_width_100_percentage '.$campaignClass.' '.$formControlClass. ' "';
		}
		$attributes .= ' aria-label="'.$this->title.'" ';
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
                $selectedOptions = array($this->value);
            }
        }
        else
        {
            $selectedOptions = $this->value;
        }
		if($this->description != "")
		{
			$desc = '<p class="jd-field-description" '. trim( $this->extraAttributes. ' '.$controlGroupAttributes).'>'.$this->description.'</p>';
		}

		$return  = ($config->display_field_description == 'above_field') ? $desc : '';

        $return .= HTMLHelper::_('select.genericlist', $options, $this->name . ($this->multiple ? '[]' : ''), trim($attributes . $this->extraAttributes. ' '.$controlGroupAttributes),
            'value', 'text', $selectedOptions);

		if($config->display_field_description == "under_field")
		{
			$return .= $desc ;
		}

		return $return;
    }
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 */
	protected function getOptions()
	{
		$options = [];	
		$options[] = HTMLHelper::_('select.option', '', Text::_('JD_PLEASE_SELECT'));
		foreach ($this->options as $option)
		{
			$options[] = HTMLHelper::_('select.option', trim($option), $option);
		}
		return $options;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 */
	protected function getOptionsSimple($field)
	{
		$options = [];	
		$options[] = HTMLHelper::_('select.option', '', $field->title);
		foreach ($this->options as $option)
		{
			$options[] = HTMLHelper::_('select.option', trim($option), $option);
		}
		return $options;
	}
}
