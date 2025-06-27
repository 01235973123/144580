<?php
/**
 * Form Class for handling custom fields
 * 
 * @package		OSF
 * @subpackage  Form  
 */
class OSFForm
{

	/**
	 * The array hold list of custom fields
	 * 
	 * @var array
	 */
	protected $fields;
	/**
	 * Constructor 
	 * 
	 * @param array $fields
	 */
	public function __construct($fields, $config = array())
	{
		foreach ($fields as $field)
		{
			$class = 'OSFFormField' . ucfirst($field->fieldtype);
			if (class_exists($class))
			{
				$this->fields[$field->name] = new $class($field, $field->default_values);
			}
			else
			{
				throw new RuntimeException('The field type ' . $field->fieldType . ' is not supported');
			}
		}
	}

	/**
	 * Get fields of form
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}
	/**
	 * Get the field object from name
	 * @param string $name
	 * @return OSFFormField
	 */
	public function getField($name)
	{
		return $this->fields[$name];	
	}
	/**
	 *
	 * Bind data into form fields
	 *
	 * @param array $data
	 * @param bool $useDefault
	 */
	public function bind($data, $useDefault = false)
	{
		foreach ($this->fields as $field)
		{
			if (isset($data[$field->name]))
			{
				$field->setValue($data[$field->name]);
			}
			else
			{
				if ($useDefault)
				{
					$field->setValue($field->default_values);
				}
				else
				{
					$field->setValue(null);
				}
			}
		}
		return $this;
	}

    /**
     * Prepare form fields before being displayed. We need to calculate to see what fields are shown, what fields are hided
     *
     * @param int $campaignId
     */
    public function prepareFormField($campaignId = 0)
    {
        foreach($this->fields as $field)
        {
            if ($field->campaignId != $campaignId && $field->campaignId != 0)
            {
                $field->setVisibility(false);
            }
        }
    }

	/**
	 * Method to get form rendered string 
	 * 
	 * @return string
	 */
	public function render($tableLess = true)
	{
		ob_start();
		foreach ($this->fields as $field)
		{
			echo $field->getControlGroup($tableLess);
		}
		return ob_get_clean();	
	}

    /**
     * Display form fields and it's value
     *
     * @param bool $tableLess
     * @return string
     */
    public function getOutput($tableLess = true, $bootstrapHelper = null)
    {
        ob_start();
        foreach ($this->fields as $field)
        {
            echo $field->getOutput($tableLess , $bootstrapHelper);
        }
        return ob_get_clean();
    }
}