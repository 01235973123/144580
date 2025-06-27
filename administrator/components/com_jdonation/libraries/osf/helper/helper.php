<?php
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
/**
 * @package     OSF
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2009 - 2023 Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();

/**
 * Provide basic static methods which usually be used on component development
 *
 * @package		OSF
 * @subpackage	Helper
 * @since		1.0
 */
class OSFHelper
{
	/**
	 * Get actions can be performed by the current user on the view of a component
	 * 
	 * @param string $option	Name of the component is being dispatched
	 * @param string $viewName	Name of the view is being displayed
	 * @param OSFModelState $state State of model associated with the view 	 
	 * 
	 * @return JObject	Actions which can be performed by the current user 	
	 */
	public static function getActions($option, $viewName, $state)
	{
		$result = new CMSObject();
		$user = Factory::getApplication()->getIdentity();
		$actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete');
				
		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $option));
		}
		
		return $result;
	}
	
	/**
	 * Add sub-menus which allow users to access to the other views in the component
	 * @param string $option	Name of the component being dispatched
	 * @param string $viewName	Name of the view currently displayed
	 */
		
	public static function addSubMenus($option, $viewName)
	{
		$db = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$baseLink = 'index.php?option='.$option;
		$currentViewLink = 'index.php?option='.$option.'&view='.$viewName;
		$query->select('title, link')
			->from('#__menu')
			->where('link LIKE '.$db->quote($baseLink.'%'))
			->where('parent_id != 1')
			->where('client_id = 1')
			->order('id');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row)
		{
			JSubMenuHelper::addEntry(Text::_($row->title), $row->link, $row->link == $currentViewLink);
		}
	}		
}
