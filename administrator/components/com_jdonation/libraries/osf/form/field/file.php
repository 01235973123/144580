<?php

use Joomla\CMS\Language\Text;

class OSFFormFieldFile extends OSFFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 *	 
	 */
	protected  $type = 'File';
	
	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JTable  $row  the table object store form field definitions
	 * @param	mixed	$value the initial value of the form field
	 *
	 */
	public function __construct($row, $value = null, $fieldSuffix = null)
	{
		parent::__construct($row, $value, $fieldSuffix);				
		if ($row->size)
		{
			$this->attributes['size'] = $row->size;
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
		if($attributes != "")
		{
			$attributes = str_replace('class="','class="form-control ', $attributes);
		}
		else
		{
			$attributes = ' class="form-control"';
		}

		if ($this->value && file_exists(JPATH_ROOT.'/media/com_jdonation/files/'.$this->value))
		{
			return '<input type="file" name="' . $this->name . '" id="' . $this->name . '" value=""' . $attributes. $this->extraAttributes. ' /><div class="clearfix"></div>'.Text::_('JD_CURRENT_FILE').' <strong>'.$this->value.'</strong> <a href="index.php?option=com_jdonation&task=download_file&file_name='.$this->value.'">'.Text::_('JD_DOWNLOAD').'</a><input type="hidden" name="current_' . $this->name . '" id="current_' . $this->name . '" value="'.$this->value.'"/>';
		}
		else
		{
			return '<input type="file" name="' . $this->name . '" id="' . $this->name . '" value=""' . $attributes. $this->extraAttributes. ' />';
		}
	}

    public function getInputSimple($bootstrapHelper = null)
    {
		$config			= DonationHelper::getConfig();
        $attributes		= $this->buildAttributes();
		if($attributes != "")
		{
			$attributes = str_replace('class="','class="form-control ', $attributes);
		}
		else
		{
			$attributes = ' class="form-control"';
		}
		if($this->description != "")
		{
			$desc = '<p class="jd-field-description">'.$this->description.'</p>';
		}
		$return  = ($config->display_field_description == 'above_field') ? $desc : '';

        if ($this->value && file_exists(JPATH_ROOT.'/media/com_jdonation/files/'.$this->value))
        {
            $return .= '<input type="file" name="' . $this->name . '" id="' . $this->name . '" value=""' . $attributes. $this->extraAttributes. ' /><div class="clearfix"></div>'.Text::_('JD_CURRENT_FILE').' <strong>'.$this->value.'</strong> <a href="index.php?option=com_jdonation&task=download_file&file_name='.$this->value.'">'.Text::_('JD_DOWNLOAD').'</a><input type="hidden" name="current_' . $this->name . '" id="current_' . $this->name . '" value="'.$this->value.'"/>';
        }
        else
        {
            $return .= '<input type="file" name="' . $this->name . '" id="' . $this->name . '" value=""' . $attributes. $this->extraAttributes. ' />';
			//$return .= DonationHelperHtml::getMediaInput('', $this->name, null);
        }

		if($config->display_field_description == "under_field")
		{
			$return .= $desc;
		}

		return $return;
    }
}
