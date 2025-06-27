<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Clickatell\Rest;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;

class plgMembershipProSMSClickatell extends CMSPlugin
{
	public function onMembershipProSendingSMSReminder(Event $event)
	{
		[$rows] = array_values($event->getArguments());

		require_once JPATH_ROOT . '/plugins/membershipprosms/clickatell/clickatell/vendor/autoload.php';

		$apiToken = $this->params->get('api_token');

		if (!$apiToken)
		{
			return;
		}

		$clickatell = new Rest($apiToken);

		foreach ($rows as $row)
		{
			try
			{
				$result = $clickatell->sendMessage(['to' => [$this->sanitize($row->phone)], 'content' => $row->sms_message]);

				if ($result['error'])
				{
					OSMembershipHelper::logData(
						__DIR__ . '/clickatell_error.txt',
						['id' => $row->id, 'phone' => $row->phone, 'error' => $result['error'], 'errorDescription' => $result['errorDescription']]
					);
				}
			}
			catch (Exception $e)
			{
				OSMembershipHelper::logData(
					__DIR__ . '/clickatell_error.txt',
					['id' => $row->id, 'phone' => $row->phone, 'error' => $e->getMessage()]
				);
			}
		}

		// Return true to tell the system that SMS were successfully sent so that it could update sms sending status for registrants
		return true;
	}

	/**
	 * Helper method used to sanitize phone numbers.
	 *
	 * @param   string  $phone  The phone number to sanitize.
	 *
	 * @return    string    The cleansed number.
	 */
	protected function sanitize($phone)
	{
		$phone = trim(str_replace(' ', '', $phone));

		if (substr($phone, 0, 1) != '+')
		{
			if (substr($phone, 0, 2) == '00')
			{
				$phone = '+' . substr($phone, 2);
			}
			else
			{
				$phone = $this->params->get('prefix') . $phone;
			}
		}

		return $phone;
	}
}
