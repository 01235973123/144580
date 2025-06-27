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
class OSFFormFieldCountries extends OSFFormFieldList
{

	/**
	 * The form field type.
	 *
	 * @var    string	 
	 */
	public $type = 'Countries';

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 */
	protected function getOptions()
	{
		try
		{
			$db = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('name AS `value`, name AS `text`')
				->from('#__jd_countries')
                ->order('name');
			$db->setQuery($query);
			$options = array();
			$options[] = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_COUNTRY'));
			$options = array_merge($options, $db->loadObjectList());
		}
		catch (Exception $e)
		{
			$options = array();
		}
		return $options;
	}

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 */
	protected function getOptionsSimple($field)
	{
		try
		{
			$db = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('name AS `value`, name AS `text`')
				->from('#__jd_countries')
                ->order('name');
			$db->setQuery($query);
			$options = array();
			$options[] = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_COUNTRY'));
			$options = array_merge($options, $db->loadObjectList());
		}
		catch (Exception $e)
		{
			$options = array();
		}
		return $options;
	}
}
