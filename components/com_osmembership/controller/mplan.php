<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

JLoader::register(
	'OSMembershipController',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/controller/controller.php'
);
JLoader::register(
	'OSMembershipControllerPlan',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/controller/plan.php'
);

class OSMembershipControllerMplan extends OSMembershipControllerPlan
{
	use OSMembershipControllerDisplay;

	/**
	 * Get url of the page which display list of records
	 *
	 * @return string
	 */
	protected function getViewListUrl()
	{
		return Route::_(
			'index.php?option=com_osmembership&view=mplans&Itemid=' . OSMembershipHelperRoute::findView(
				'mplans',
				$this->input->getInt('Itemid')
			),
			false
		);
	}

	/**
	 * Get url of the page which allow adding/editing a record
	 *
	 * @param   int  $recordId
	 *
	 * @return string
	 */
	protected function getViewItemUrl($recordId = null)
	{
		$url = 'index.php?option=' . $this->option . '&view=' . $this->viewItem;

		if ($recordId)
		{
			$url .= '&id=' . $recordId;
		}

		$url .= '&Itemid=' . $this->input->getInt('Itemid', OSMembershipHelperRoute::findView('mplans'));

		return $url;
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  bool
	 */
	protected function allowAdd($data = [])
	{
		if (!$this->app->getIdentity()->authorise('membershippro.plans', 'com_osmembership'))
		{
			return false;
		}

		return parent::allowAdd($data);
	}

	/**
	 * Method to check if you can edit a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 * @param   string  $key  The name of the key for the primary key; default is id.
	 *
	 * @return  bool
	 */
	protected function allowEdit($data = [], $key = 'id')
	{
		if (!$this->app->getIdentity()->authorise('membershippro.plans', 'com_osmembership'))
		{
			return false;
		}

		return parent::allowEdit($data, $key);
	}

	/**
	 * Method to check whether the current user is allowed to delete a record
	 *
	 * @param   int  $id  Record ID
	 *
	 * @return  bool  True if allowed to delete the record. Defaults to the permission for the component.
	 */
	protected function allowDelete($id)
	{
		// Users need to have plans management permission to delete plans
		if (!$this->app->getIdentity()->authorise('membershippro.plans', 'com_osmembership'))
		{
			return false;
		}

		return parent::allowDelete($id);
	}

	/**
	 * Method to check whether the current user can change status (publish, unpublish of a record)
	 *
	 * @param   int  $id  Id of the record
	 *
	 * @return  bool  True if allowed to change the state of the record. Defaults to the permission for the component.
	 */
	protected function allowEditState($id)
	{
		// Users need to have plans management permission to delete plans
		if (!$this->app->getIdentity()->authorise('membershippro.plans', 'com_osmembership'))
		{
			return false;
		}

		return parent::allowEditState($id);
	}
}
