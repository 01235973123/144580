<?php
/**
 * Form Field class for the Joomla OSF.
 * Supports a textarea inut.
 *
 * @package     Joomla.OSF
 * @subpackage  Form
 */
class OSFFormFieldTextarea extends OSFFormField
{

	protected $type = 'Textarea';

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
		if ($row->rows)
		{
			$this->attributes['rows'] = $row->rows;
		}
		if ($row->cols)
		{
			$this->attributes['cols'] = $row->cols;
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
		return '<textarea name="' . $this->name . '" id="' . $this->name . '"' . $attributes . $this->extraAttributes . ' >' .
			 htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
	}

    public function getInputSimple($bootstrapHelper = null,$field, $controlGroupAttributes)
    {
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
			$attributes = str_replace('class="','class="jd_width_100_percentage '.$campaignClass. ' '.$formControlClass. ' ',$attributes);
		}
		else
		{
			$attributes = 'class="'.$campaignClass.' '.$formControlClass.'"';
		}
		if($this->description != "")
		{
			$desc = '<p class="jd-field-description">'.$this->description.'</p>';
		}

		$return  = ($config->display_field_description == 'above_field') ? $desc : '';

        $return .= '<textarea placeholder="'.$this->attributes['placeholder'].'" name="' . $this->name . '" id="' . $this->name . '"' . $attributes . $this->extraAttributes . ' '.$controlGroupAttributes.' >' .
            htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';

		if($config->display_field_description == "under_field")
		{
			$return .= $desc;
		}

		return $return;
    }
}