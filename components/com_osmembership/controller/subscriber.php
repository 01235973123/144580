<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use OSSolution\MembershipPro\Admin\Event\Subscriptions\AfterImportSubscriptions;

JLoader::register(
	'OSMembershipControllerSubscription',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/controller/subscription.php'
);
JLoader::register(
	'OSMembershipModelOverrideSubscriptions',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/model/override/subscriptions.php'
);
JLoader::register(
	'OSMembershipModelSubscriptions',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/model/subscriptions.php'
);
JLoader::register(
	'OSMembershipModelSubscription',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/model/subscription.php'
);
JLoader::register(
	'OSMembershipController',
	JPATH_ADMINISTRATOR . '/components/com_osmembership/controller/controller.php'
);

class OSMembershipControllerSubscriber extends OSMembershipControllerSubscription
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
			'index.php?option=com_osmembership&view=subscribers&Itemid=' . OSMembershipHelperRoute::findView(
				'subscribers',
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

		$url .= '&Itemid=' . $this->input->getInt('Itemid', OSMembershipHelperRoute::findView('subscribers'));

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
		if (!$this->app->getIdentity()->authorise('membershippro.subscriptions', 'com_osmembership'))
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
		if (!$this->app->getIdentity()->authorise('membershippro.subscriptions', 'com_osmembership'))
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
		if (!$this->app->getIdentity()->authorise('membershippro.subscriptions', 'com_osmembership'))
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
		if (!$this->app->getIdentity()->authorise('membershippro.subscriptions', 'com_osmembership'))
		{
			return false;
		}

		return parent::allowEditState($id);
	}

	/**
	 *
	 */
	public function cancel()
	{
		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Import Subscribers from CSV
	 */
	public function import_subscriptions()
	{
		$user = $this->app->getIdentity();

		if (!$user->authorise('membershippro.subscriptions', 'com_osmembership'))
		{
			throw new Exception(403, Text::_('You do not have permission to import subscriptions'));
		}

		$inputFile = $this->input->files->get('input_file');
		$fileName  = $inputFile ['name'];
		$fileExt   = strtolower(OSMembershipHelper::getFileExt($fileName));

		if (!in_array($fileExt, ['csv', 'xls', 'xlsx']))
		{
			$this->setRedirect(
				$this->getViewListUrl(),
				Text::_('Invalid File Type. Only CSV, XLS and XLS file types are supported')
			);

			return;
		}

		JLoader::register(
			'OSMembershipModelImport',
			JPATH_ADMINISTRATOR . '/components/com_osmembership/model/import.php'
		);

		/* @var OSMembershipModelImport $model */
		$model = $this->getModel('import');

		try
		{
			$ids = $model->store($inputFile['tmp_name'], $inputFile['name']);

			if (is_int($ids))
			{
				$numberSubscribers = $ids;
			}
			else
			{
				$numberSubscribers = count($ids);
			}

			$event = new AfterImportSubscriptions([
				'ids' => $ids,
			]);

			$this->getApplication()->triggerEvent($event->getName(), $event);
			$this->setRedirect(
				$this->getViewListUrl(),
				Text::sprintf('OSM_NUMBER_SUBSCRIBERS_IMPORTED', $numberSubscribers)
			);
		}
		catch (Exception $e)
		{
			$this->setRedirect($this->getViewListUrl());
			$this->setMessage($e->getMessage(), 'error');
		}
	}
}
