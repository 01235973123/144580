<?php

class OSFFormFieldHeading extends OSFFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 */
	public $type = 'Heading';

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

		$desc = '<h3 class="eb-heading campaign_' . $this->campaignId . '" ' . $controlGroupAttributes . '>' . $this->title . '</h3>';
		if($this->description != "")
		{
			$desc .= '<p class="jd-field-description">'.$this->description.'</p>';
		}
		return $desc;
	}

    public function getInputSimple($bootstrapHelper = null)
    {
        $controlGroupAttributes = 'id="field_' . $this->name . '"';
        if (!$this->visible)
        {
            $controlGroupAttributes .= ' style="display:none;" ';
        }

        $desc = '<h3 class="eb-heading campaign_' . $this->campaignId . '" ' . $controlGroupAttributes . '>' . $this->title . '</h3>';
		if($this->description != "")
		{
			$desc .= '<p class="jd-field-description">'.$this->description.'</p>';
		}
		return $desc;
    }
	/**
	 * Get control group used to display on form
	 *
	 * @see OSFFormField::getControlGroup()
	 */
	public function getControlGroup($tableLess = true, $bootstrapHelper = null, $field = null)
	{
		return $this->getInput();
	}

	/**
	 * Get output used for displaying on email and the detail page
	 *
	 * @see OSFFormField::getOutput()
	 */
	public function getOutput($tableLess = true, $bootstrapHelper = null)
	{
		if ($tableLess)
		{
			return $this->getInput();
		}
		else
		{
			return '<tr>' . '<td class="eb-heading" colspan="2">' . $this->title . '</td></tr>';
		}
	}
}