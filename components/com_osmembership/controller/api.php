<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

class OSMembershipControllerApi extends MPFController
{
	/**
	 * Method to add a subscription record to the system
	 *
	 * @throws Exception
	 */
	public function add()
	{
		// Validate the request
		$this->validateAPIRequest();

		$data = $this->input->getData();

		// Make sure id is not provided on a add request
		if (isset($data['id']))
		{
			unset($data['id']);
		}

		/* @var OSMembershipModelApi $model */
		$model  = $this->getModel('Api', ['ignore_request' => true]);
		$result = $model->store($data);

		// There are something wrong with the provided data
		if (is_array($result) && count($result))
		{
			$success = false;
			$data    = $result;
		}
		else
		{
			$success = true;
			$data    = $model->getSubscriptionData($result->id);

			// Return ID of the subscription record
			$data['id'] = $result->id;
		}

		$this->sendResponse($success, $data);
	}

	/**
	 * API Method to update an existing subscription
	 *
	 * @throws Exception
	 */
	public function update()
	{
		// Validate the request
		$this->validateAPIRequest();

		$data = $this->input->getData();

		/* @var OSMembershipModelApi $model */
		$model  = $this->getModel('Api', ['ignore_request' => true]);
		$result = $model->store($data);

		// There are something wrong with the provided data
		if (is_array($result) && count($result))
		{
			$success = false;
			$data    = $result;
		}
		else
		{
			$success = true;
			$data    = $model->getSubscriptionData($result->id);
		}

		$this->sendResponse($success, $data);
	}

	/**
	 * Method to get data of a subscription
	 *
	 * @throws Exception
	 */
	public function get()
	{
		// Validate the request
		$this->validateAPIRequest();

		// Get ID of the subscription from API and call API to get the details
		$id = $this->input->getInt('id');

		/* @var OSMembershipModelApi $model */
		$model = $this->getModel('Api', ['ignore_request' => true]);
		$data  = $model->getSubscriptionData($id);

		if ($data === false)
		{
			$success = false;
			$data    = [];
			$data[]  = Text::sprintf('OSM_INVALID_SUBSCRIPTION_ID', $id);
		}
		else
		{
			$success = true;
		}

		$this->sendResponse($success, $data);
	}

	/**
	 * Method to get id of active subscription of given users
	 *
	 * @throws Exception
	 */
	public function get_active_plan_ids()
	{
		$this->validateAPIRequest();

		$userId = $this->input->getInt('user_id');

		if (!$userId)
		{
			$userId = $this->getUserIdFromOtherDataInRequest();
		}

		$activePlans = OSMembershipHelperSubscription::getActivePlanIdsForUser($userId);

		$this->sendResponse(true, $activePlans);
	}

	public function get_user_active_subscriptions()
	{
		$this->validateAPIRequest();

		$userId = $this->input->getInt('user_id');

		if (!$userId)
		{
			$userId = $this->getUserIdFromOtherDataInRequest();
		}

		$rows = OSMembershipHelperSubscription::getUserActiveSubscriptions($userId);

		$this->sendResponse(true, $rows);
	}

	/**
	 * Method to get user_id from other information passed in the request
	 *
	 * @return int
	 */
	protected function getUserIdFromOtherDataInRequest(): int
	{
		$username   = $this->input->getString('username');
		$downloadId = $this->input->getString('download_id');

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		if ($username)
		{
			$query->select('id')
				->from('#__users')
				->where('username = ' . $db->quote($username));
			$db->setQuery($query);

			return (int) $db->loadResult();
		}

		if ($downloadId)
		{
			$query->select('user_id')
				->from('#__osmembership_downloadids')
				->where('download_id = ' . $db->quote($downloadId))
				->where('published = 1');
			$db->setQuery($query);

			return (int) $db->loadResult();
		}

		return 0;
	}

	/**
	 * Basic API validation, should be called before each request
	 *
	 * @throws Exception
	 */
	protected function validateAPIRequest()
	{
		$config = OSMembershipHelper::getConfig();

		// Check and make sure API is enabled
		if (!$config->enable_api)
		{
			throw new Exception(Text::_('API is not enabled on this site'));
		}

		// Check API Key
		$apiKey = $this->input->getString('api_key');

		if ($apiKey !== $config->api_key)
		{
			throw new Exception(sprintf('The provided API Key %s is invalid', $apiKey));
		}
	}

	/**
	 * Send json response to the API call
	 *
	 * @param   bool   $success
	 * @param   array  $data
	 */
	protected function sendResponse($success, $data)
	{
		$response['success'] = $success;

		if ($success)
		{
			$response['data'] = $data;
		}
		else
		{
			$response['errors'] = $data;
		}

		echo json_encode($response);

		$this->app->close();
	}
}
