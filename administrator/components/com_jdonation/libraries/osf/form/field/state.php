<?php

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
/**
 * Supports a custom field which display list of countries
 *
 * @package     Joomla.OSF
 * @subpackage  Form
 */
class OSFFormFieldState extends OSFFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'State';
	/**
	 * ID of the country used to build the zone
	 * 
	 * @var int
	 */
	protected $countryId = null;
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
	}

	/**
	 * Set ID of the country used to generate the zones list
	 * @param int $countryId
	 */
	public function setCountryId($countryId)
	{
		$this->countryId = (int) $countryId;
	}
	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 */
	public function getOptions()
	{
		$db = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('state_2_code AS `value`, state_name AS `text`')
		->from('#__jd_states')
		->where('country_id=' . (int)$this->countryId)
		->order('state_name');
		$db->setQuery($query);
		$options = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_STATE'));
		$options = array_merge($options, $db->loadObjectList());
		return $options;
	}

	public function getOptionsSimple($field)
	{
		$db = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('state_2_code AS `value`, state_name AS `text`')
		->from('#__jd_states')
		->where('country_id=' . (int)$this->countryId)
		->order('state_name');
		$db->setQuery($query);
		$options = array();
		$options[] = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_STATE'));
		$options = array_merge($options, $db->loadObjectList());
		return $options;
	}
}
