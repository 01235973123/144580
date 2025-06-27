<?php
/**
 * Form Field class for the Joomla OSF.
 * Supports a text input.
 *
 * @package     Joomla.OSF
 * @subpackage  Form
 */
class OSFFormFieldText extends OSFFormField
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
	 * @param   JTable  $row  the table object store form field definitions
	 * @param	mixed	$value the initial value of the form field
	 *
	 */
	public function __construct($row, $value = null)
	{
		parent::__construct($row, $value);
		if ($row->place_holder)
		{
			$this->attributes['placeholder'] = $row->place_holder;
		}
		else
		{
			$this->attributes['placeholder'] = $row->title;
		}
		if ($row->max_length)
		{
			$this->attributes['maxlength'] = $row->max_length;
		}
		if ($row->size)
		{
			$this->attributes['size'] = $row->size;
		}
		if ($row->input_mask)
		{
			$this->attributes['data-input-mask'] = $row->input_mask;
		}
		
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 */
	public function getInput($bootstrapHelper = null)
	{
		$attributes = $this->buildAttributes();
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))			
		{
			$attributes = str_replace("input-large", "input-large form-control", $attributes);
			$attributes = str_replace("input-small", "input-small form-control", $attributes);
			$attributes = str_replace("input-medium", "input-medium form-control", $attributes);
			$attributes = str_replace("input-mini", "input-mini form-control", $attributes);
		}
		return '<input type="text" name="' . $this->name . '" id="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .
			 '" ' . $attributes . $this->extraAttributes . ' />';
	}

	public function getInputSimple($bootstrapHelper = null,$field, $controlGroupAttributes, $campaignId = 0)
	{
		if($field->default_values)
		{
			$this->value = $field->default_values;
		}
		$config = DonationHelper::getConfig();
		$formControlClass	= $bootstrapHelper->getClassMapping('form-control');
        $attributes = $this->buildAttributes();
		if($campaignId > 0)
		{
			$campaignClass = 'campaign_'.$campaignId. ' ';
		}
		if($attributes != "")
		{
			$attributes = str_replace("input-large","",$attributes);
			$attributes = str_replace('class="','class="form-control jd_width_100_percentage '.$campaignClass. ' '.$formControlClass . ' ',$attributes);
		}
		else
		{
			$attributes = 'class="form-control '.$campaignClass.' '.$formControlClass.'"';
		}
		if($this->description != "")
		{
			$desc = '<p class="jd-field-description">'.$this->description.'</p>';
		}

		$return  = ($config->display_field_description == 'above_field') ? $desc : '';

        $return .='<input type="text" name="' . $this->name . '" id="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .
            '" ' . $attributes . $this->extraAttributes . ' placeholder="'.$field->title.'" '.$controlGroupAttributes.'/>';

		if($config->display_field_description == "underfield")
		{
			$return .= $desc;
		}

		return $return;
    }
}