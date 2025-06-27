<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use mediaburst\ClockworkSMS\Clockwork;

class plgMembershipProSMSClockwork extends CMSPlugin
{
	public function onMembershipProSendingSMSReminder(Event $event)
	{
		[$rows] = array_values($event->getArguments());

		require_once JPATH_ROOT . '/plugins/membershipprosms/clockwork/clockwork/vendor/autoload.php';

		$apiKey = $this->params->get('api_key');

		if (!$apiKey)
		{
			return;
		}

		$client = new Clockwork($apiKey);

		foreach ($rows as $row)
		{
			$message = ['to' => $this->sanitize($row->phone), 'message' => $row->sms_message];

			try
			{
				$result = $client->send($message);

				if (!$result['success'])
				{
					OSMembershipHelper::logData(
						__DIR__ . '/clockwork_error.txt',
						['id' => $row->id, 'phone' => $row->phone, 'error' => $result['error_message']]
					);
				}
			}
			catch (Exception $e)
			{
				OSMembershipHelper::logData(
					__DIR__ . '/clockwork_error.txt',
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
