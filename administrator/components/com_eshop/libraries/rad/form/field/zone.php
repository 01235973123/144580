<?php
/**
 * Supports a custom field which display list of countries
 *
 * @package     Joomla.EshopRAD
 * @subpackage  Form
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class EshopRADFormFieldZone extends EshopRADFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'Zone';
	/**
	 * ID of the country used to build the zone
	 *
	 * @var int
	 */
	protected $countryId = null;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Table  $row    the table object store form field definitions
	 * @param   mixed  $value  the initial value of the form field
	 *
	 */
	public function __construct($row, $value)
	{
		parent::__construct($row, $value);
	}

	/**
	 * Set ID of the country used to generate the zones list
	 *
	 * @param   int  $countryId
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
	protected function getOptions()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id AS `value`, zone_name AS `text`')
			->from('#__eshop_zones')
			->where('country_id = ' . (int) $this->countryId)
			->where('published = 1')
			->order('zone_name');
		$db->setQuery($query);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('ESHOP_PLEASE_SELECT'));
		$options   = array_merge($options, $db->loadObjectList());

		return $options;
	}
}
