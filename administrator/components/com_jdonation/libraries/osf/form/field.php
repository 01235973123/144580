<?php



use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;


/**
 * Abstract Form Field class for the OSF framework
 *
 * @package     Joomla.OSF
 * @subpackage  Form
 */
 
 
abstract class OSFFormField
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
	 * Description of the form field
	 * @var string
	 */
	protected $place_holder;

    /**
     * Default value for the field
     *
     * @var string
     */
    protected $defaultValues;
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
     * This field is visible or hidden on the form
     *
     * @var bool
     */
    protected $visible = true;

    /**
     * ID of the campaign which the custom field is assigned to
     *
     * @var int
     */
    protected $campaignId = 0;
	
	/**
	 * The html attributes of the field
	 * 
	 * @var array
	 */
	protected $attributes = array();
	
	/**
	 * The input for the form field.
	 *
	 * @var    string	
	 */
	protected $input;

	protected $container_size;

	protected $container_class;

	protected $default_values;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JTable  $row  the table object store form field definitions
	 * @param	mixed	$value the initial value of the form field
	 *
	 */
	public function __construct($row, $value = null)
	{
		$this->name				= $row->name;
		$this->title			= Text::_($row->title);
		$this->description		= $row->description;
		$this->required			= $row->required;
		$this->place_holder		= $row->place_holder;
		$this->extraAttributes	= $row->extra_attributes;		
		$this->value			= $value;
        $this->default_values	= $row->default_values;
        $this->campaignId		= (int) $row->campaign_id;
		$this->container_size	= $row->container_size;
		$this->container_class	= $row->container_class;
        $cssClasses				= array();
        if ($row->css_class)
        {
            $cssClasses[] = $row->css_class;
        }
        if ($row->validation_rules)
        {
            $cssClasses[] = $row->validation_rules;
        }
        if (count($cssClasses))
        {
            $this->attributes['class'] = implode(' ', $cssClasses);
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
            case 'default_values':
            case 'campaignId':
				return $this->{$name};
				break;
			case 'input':
				// If the input hasn't yet been generated, generate it.
				if (empty($this->input))
				{
					$this->input = $this->getInput();
				}
				return $this->input;
				break;
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
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *	 
	 */
	abstract protected function getInput($bootstrapHelper = null);

	/**
	 * Method to get a control group with label and input.
	 *
	 * @return  string  A string containing the html for the control goup
	 *
	 */
	public function getControlGroup($tableLess = true, $bootstrapHelper = null, $field = null)
	{
		if (!empty($this->description))
		{
			HTMLHelper::_('bootstrap.tooltip');
			Factory::getApplication()->getDocument()->addStyleDeclaration(".hasTip{display:block !important}");
			$useTooltip = true;
			$class = 'hasTooltip hasTip';
		}
		if ($this->type == 'hidden')
		{
			return $this->getInput();
		}
		else
		{
			require_once JPATH_ROOT .'/components/com_jdonation/helper/helper.php';
			require_once JPATH_ROOT .'/components/com_jdonation/helper/bootstrap.php';
			$config                 = DonationHelper::getConfig();
			$bootstrapHelper		= new DonationHelperBootstrap($config->twitter_bootstrap_version);
			$controlGroupClass      = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-group') : 'control-group';
			$controlLabelClass      = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-label') : 'control-label';
			$controlsClass          = $bootstrapHelper ? $bootstrapHelper->getClassMapping('controls') : 'controls';
            $controlGroupAttributes = 'id="field_'.$this->name.'"';
            if(!$this->visible)
            {
                $controlGroupAttributes .= ' style="display:none !important;" ';
            }
			$classes = array($controlGroupClass);
			if ($field->container_size)
			{
				$classes[] = $bootstrapHelper ? $bootstrapHelper->getClassMapping($field->container_size) : $field->container_size;
			}

			if ($field->container_class)
			{
				$classes[] = $field->container_class;
			}

			if ($tableLess)
            {
				if($field->name == "state")
				{
					$field_state_id = "field_state_select";
				}
				else
				{
					$field_state_id = "";
				}
				return '<div class="' . implode(' ', $classes) . ' campaign_' .$this->campaignId. '" '.$controlGroupAttributes.'>
							<label class="'.$controlLabelClass.' '.$class.'" for="'.$this->name.'" title="'.HTMLHelper::tooltipText(trim($this->title, ':'), $this->description, 0) .'">'
											. $this->title .($this->required ? '<span class="required">*</span>' : '') . '
							</label>
						<div class="' . $controlsClass . '" id="'.$field_state_id.'">
							' . $this->getInput($bootstrapHelper). '
						</div>
					</div>';
			}
			else 
			{
			    return $this->getInputSimple($bootstrapHelper, $this, $controlGroupAttributes, $this->campaignId);
			}				
		}
	}

	public function getFieldLabel()
	{
		return $this->title;
	}

	public function getFieldInput($bootstrapHelper = null)
	{
		$this->getInput($bootstrapHelper);
	}

    public function getControlGroupSimple($tableLess = true, $bootstrapHelper = null,$field)
    {
        if ($this->type == 'hidden')
        {
            return $this->getInput();
        }
        else
        {
            $controlGroupClass      = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-group') : 'control-group';
            $controlLabelClass      = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-label') : 'control-label';
            $controlsClass          = $bootstrapHelper ? $bootstrapHelper->getClassMapping('controls') : 'controls';
            $controlGroupAttributes = 'id="field_'.$this->name.'"';
            if(!$this->visible)
            {
                $controlGroupAttributes .= ' style="display:none !important;" ';
            }
            $classes = array($controlGroupClass);
			if ($field->container_class)
			{
				$classes[] = $field->container_class;
			}
            if ($tableLess)
            {
				if($field->name == "state")
				{
					$field_state_id = "field_state_select";
				}
				else
				{
					$field_state_id = "";
				}
                return '<div class="' . implode(' ', $classes) . ' campaign_' .$this->campaignId.'" '.$controlGroupAttributes.'>
							<label class="'.$controlLabelClass.'" for="'.$this->name.'" title="'.$this->description.'">'
                    . $this->title .($this->required ? '<span class="required">*</span>' : '') . '
							</label>
						<div class="' . $controlsClass . '" id="'.$field_state_id.'">
							' . $this->getInput($bootstrapHelper). '
						</div>
					</div>';
            }
            else
            {
                return $this->getInputSimple($bootstrapHelper,$field,$controlGroupAttributes, $this->campaignId);
            }
        }
    }

	/**
	 * Get output of the field using for sending email and display on the registration complete page
	 * @param bool $tableless
	 * @return string
	 */
	public function getOutput($tableLess = true, $bootstrapHelper = null)
	{
        if(!$this->visible)
        {
            return;
        }
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
			$controlGroupClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-group') : 'control-group';
			$controlLabelClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-label') : 'control-label';
			$controlsClass     = $bootstrapHelper ? $bootstrapHelper->getClassMapping('controls') : 'controls';

			if($fieldValue != '')
			{
                return '<div class="' . $controlGroupClass . '">' . '<div class="' . $controlLabelClass . '">' . $this->title . '</div>' . '<div class="' . $controlsClass . '">' . $fieldValue .
                    '</div>' . '</div>';
            }
		}
		else
		{
			return '<tr>' . '<td class="title_cell">' . $this->title . '</td>' . '<td class="field_cell">' . $fieldValue . '</td>' . '</tr>';
		}
	}

    /**
     * Add attribute to the form field
     * @param string $name
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    /**
     * Get data of the given attribute
     * @param string $name
     * @return string
     */
    public function getAttribute($name)
    {
        return $this->attributes[$name];
    }

    /**
     * Set visibility status for the field on form
     *
     * @param $visible
     */
    public function setVisibility($visible)
    {
        $this->visible = $visible;
    }
	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @param  array  $attributes
	 * @return string
	 */
	public function buildAttributes()
	{
		$html = array();
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
