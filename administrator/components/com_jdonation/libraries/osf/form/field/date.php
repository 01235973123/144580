<?php

use Joomla\CMS\HTML\HTMLHelper;
class OSFFormFieldDate extends OSFFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 *	 
	 */
	protected $type = 'Date';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *	 
	 */
	public function getInput($bootstrapHelper = null)
	{
		$attributes = $this->buildAttributes();
		return HTMLHelper::_('calendar', $this->value, $this->name, $this->name, '%Y-%m-%d',".$attributes.");
	}

    public function getInputSimple($bootstrapHelper = null,$field, $controlGroupAttributes, $campaignId = 0)
    {
		$config = DonationHelper::getConfig();
        $attributes = $this->buildAttributes();
		if($campaignId > 0)
		{
			$campaignClass = 'campaign_'.$campaignId. ' ';
		}
		$temp = array();
		if($attributes != "")
		{
			$attributes = str_replace("input-large","",$attributes);
			$attributes = str_replace('class="','class="jd_width_100_percentage '.$campaignClass,$attributes);
		}
		else
		{
			$attributes = 'class="'.$campaignClass.'"';
			
		}
		$temp = array('class' => 'jd_width_100_percentage '.$campaignClass.'', 'placeholder'=> $field->title);

		if($this->description != "")
		{
			$desc = '<p class="jd-field-description">'.$this->description.'</p>';
		}

		$return  = ($config->display_field_description == 'above_field') ? $desc : '';

        $return .= HTMLHelper::_('calendar', $this->value, $this->name, $this->name, '%Y-%m-%d',$temp);

		if($config->display_field_description == "under_field")
		{
			$return .= $desc;
		}

		return $return;
    }
}
