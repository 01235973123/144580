<?php

use Joomla\CMS\Factory;
/**
 * Supports an custom SQL select list
 *
 * @package     Joomla.OSF
 * @subpackage  Form
 */
class OSFFormFieldSQL extends OSFFormFieldList
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'SQL';
			
	/**
	 * The query.
	 *
	 * @var    string	 
	 */
	protected $query;
	
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
		$this->query = $row->values;
	}

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		try
		{
			$db = Factory::getContainer()->get('db');
			$db->setQuery($this->query);
			$options = $db->loadObjectlist();
		}
		catch (Exception $e)
		{
			$options = array();
		}
		
		return $options;
	}

	protected function getOptionsSimple()
	{
		try
		{
			$db = Factory::getContainer()->get('db');
			$db->setQuery($this->query);
			$options = $db->loadObjectList();
			//print_r($options);die();
		}
		catch (Exception $e)
		{
			//echo $e->getMessage();die();
			$options = array();
		}
		
		return $options;
	}
}
