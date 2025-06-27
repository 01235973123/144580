<?php

/**
 * Form Field class for the Joomla OSF.
 * Supports a message form field
 *
 * @package     Joomla.OSF
 * @subpackage  Form
 */
class OSFFormFieldMessage extends OSFFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 */
	protected $type = 'Message';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 */
	public function getInput($bootstrapHelper = null)
	{
		$controlGroupAttributes = 'id="field_' . $this->name . '"';
		if (!$this->visible)
		{
			$controlGroupAttributes .= ' style="display:none;" ';
		}
		$controlGroupClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-group') : 'control-group';
		if($this->visible)
		{
			return '<div class="'.$controlGroupClass.' eb-message campaign_' . $this->campaignId . '" ' . $controlGroupAttributes . '>' . $this->description . '</div>';
		}
		else
		{
			return '';
		}
	}


    public function getInputSimple($bootstrapHelper = null,$field, $controlGroupAttributes, $campaignId = 0)
    {
        $controlGroupAttributes = 'id="field_' . $this->name . '"';
        if (!$this->visible)
        {
            $controlGroupAttributes .= ' style="display:none;" ';
        }
        $controlGroupClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-group') : 'control-group';
		if($this->visible)
		{
			return '<div class="'.$controlGroupClass.' eb-message campaign_' . $this->campaignId . '" ' . $controlGroupAttributes . '>' . $this->description . '</div>';
		}
		else
		{
			return '';
		}
    }	
}