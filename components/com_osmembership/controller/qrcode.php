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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

class OSMembershipControllerQrcode extends MPFController
{
	public function check_subscription_status()
	{
		$user = $this->app->getIdentity();

		if (!$user->authorise('membershippro.subscriptions', 'com_osmembership'))
		{
			$response = [
				'success' => false,
				'message' => Text::_('OSM_CHECK_MEMBERSHIP_STATUS_NO_PERMISSION'),
			];

			$this->sendJsonResponse($response);

			return;
		}

		$subscriptionCode = $this->input->getString('value');

		if (!$subscriptionCode)
		{
			$response = [
				'success' => false,
				'message' => Text::_('OSM_CHECK_MEMBERSHIP_STATUS_NO_SUBSCRIPTION_CODE_PROVIDED'),
			];

			$this->sendJsonResponse($response);

			return;
		}

		// Check subscription record
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('subscription_code = ' . $db->quote($subscriptionCode));
		$db->setQuery($query);
		$row = $db->loadObject();

		if (!$row)
		{
			$response = [
				'success' => false,
				'message' => Text::_('OSM_CHECK_MEMBERSHIP_STATUS_NO_SUBSCRIPTION_FOUND'),
			];

			$this->sendJsonResponse($response);

			return;
		}

		$success = false;

		switch ($row->plan_subscription_status)
		{
			case 0:
				$message = Text::_('OSM_CHECK_MEMBERSHIP_STATUS_PENDING');
				break;
			case 1:
				$message = Text::_('OSM_CHECK_MEMBERSHIP_STATUS_ACTIVE');
				$success = true;
				break;
			case 2:
				$message = Text::_('OSM_CHECK_MEMBERSHIP_STATUS_EXPIRED');
				break;
			case 4:
				$message = Text::_('OSM_CHECK_MEMBERSHIP_STATUS_CANCELLED_PENDING');
				break;
			case  5:
				$message = Text::_('OSM_CHECK_MEMBERSHIP_STATUS_CANCELLED_REFUNDED');
				break;
			default:
				$message = Text::_('OSM_CHECK_MEMBERSHIP_STATUS_UNKNOWN');
				break;
		}

		// Replace the tags
		$config                = OSMembershipHelper::getConfig();
		$replaces              = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]
		);
		$replaces['from_date'] = HTMLHelper::_('date', $row->plan_subscription_from_date, $config->date_format);
		$replaces['to_date']   = HTMLHelper::_('date', $row->plan_subscription_to_date, $config->date_format);

		$message = OSMembershipHelper::replaceUpperCaseTags($message, $replaces);

		$response = [
			'success' => $success,
			'message' => $message,
		];

		// Log checkin action
		$checkinLog                = new stdClass();
		$checkinLog->subscriber_id = $row->id;
		$checkinLog->checkin_date  = Factory::getDate()->toSql();
		$checkinLog->success       = (int) $success;

		$db->insertObject('#__osmembership_checkinlogs', $checkinLog, 'id');

		$this->sendJsonResponse($response);
	}

	/**
	 * Send json response
	 *
	 * @param   array  $response
	 */
	protected function sendJsonResponse($response)
	{
		echo json_encode($response);

		$this->app->close();
	}
}
