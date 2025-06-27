<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;

trait EventbookingControllerRegistrantCheckin
{
	/**
	 * Checkin registrant from given ID
	 *
	 * @throws Exception
	 */
	public function checkin()
	{
		$config = EventbookingHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$id    = $this->input->getInt('id');

		$query->select('a.*, b.created_by AS event_created_by, b.title AS event_title')
			->from('#__eb_registrants AS a')
			->leftJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.id = ' . $id);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();

		if (!$rowRegistrant)
		{
			throw new Exception('Invalid Registration Record:' . $id, 404);
		}

		if (EventbookingHelperAcl::canManageRegistrant($rowRegistrant))
		{
			/* @var EventbookingModelRegistrant $model */
			$model       = $this->getModel();
			$result      = $model->checkinRegistrant($id, false, (bool) $config->get('validate_checkin_date', 1));
			$messageType = null;
			$message     = '';

			switch ($result)
			{
				case 0:
					$message     = Text::_('EB_INVALID_REGISTRATION_RECORD');
					$messageType = 'error';
					break;
				case 1:
					$message     = Text::_('EB_REGISTRANT_ALREADY_CHECKED_IN');
					$messageType = 'error';
					break;
				case 2:
					$message = Text::_('EB_CHECKED_IN_SUCCESSFULLY');
					break;
				case 3:
					$message = Text::_('EB_CHECKED_IN_FAIL_REGISTRATION_CANCELLED');
					break;
				case 4:
					$message = Text::_('EB_CHECKED_IN_REGISTRATION_PENDING');
					break;
				case 5:
					$message     = Text::_('EB_CHECKED_IN_PAST_EVENT');
					$messageType = 'error';
					break;
				case 6:
					$message     = Text::_('EB_CHECKED_IN_FUTURE_EVENT');
					$messageType = 'error';
					break;
			}

			$replaces = EventbookingHelperRegistration::getRegistrationReplaces(
				$rowRegistrant,
				null,
				$this->app->getIdentity()->id
			);

			$message = EventbookingHelper::replaceCaseInsensitiveTags($message, $replaces);

			$this->setRedirect($this->getViewListUrl(), $message, $messageType);
		}
		else
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}
	}

	/*
	 * Check in a registrant
	 */
	public function check_in_webapp()
	{
		Session::checkToken('get');

		if ($this->app->getIdentity()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$id = $this->input->getInt('id');

			/* @var EventbookingModelRegistrant $model */
			$model = $this->getModel();

			try
			{
				$model->checkinRegistrant($id, true);
				$this->setMessage(Text::_('EB_CHECKIN_SUCCESSFULLY'));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

			$this->setRedirect($this->getViewListUrl());
		}
		else
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}
	}

	/**
	 * Reset check in for a registrant
	 *
	 * @throws Exception
	 */
	public function reset_check_in()
	{
		Session::checkToken('get');

		if ($this->app->getIdentity()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$id = $this->input->getInt('id');

			/* @var EventbookingModelRegistrant $model */
			$model = $this->getModel();

			try
			{
				$model->resetCheckin($id);
				$this->setMessage(Text::_('EB_RESET_CHECKIN_SUCCESSFULLY'));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

			$this->setRedirect($this->getViewListUrl());
		}
		else
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}
	}

	/**
	 * Method to checkin multiple registrants
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function checkin_multiple_registrants()
	{
		Session::checkToken();

		if (!$this->app->getIdentity()->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}

		$cid = $this->input->get('cid', [], 'array');

		$cid = ArrayHelper::toInteger($cid);

		if (count($cid))
		{
			/* @var EventbookingModelRegistrant $model */
			$model = $this->getModel();

			// First check to see if there is someone already checked in
			$db    = $model->getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from('#__eb_registrants')
				->where('checked_in = 1')
				->whereIn('id', $cid);
			$db->setQuery($query);
			$rowRegistrant = $db->loadObject();

			if ($rowRegistrant)
			{
				$message = Text::_('EB_REGISTRANT_ALREADY_CHECKED_IN');

				$replaces = [
					'FIRST_NAME'         => $rowRegistrant->first_name,
					'LAST_NAME'          => $rowRegistrant->last_name,
					'EVENT_TITLE'        => $rowRegistrant->event_title,
					'REGISTRANT_ID'      => $rowRegistrant->id,
					'NUMBER_REGISTRANTS' => $rowRegistrant->number_registrants,
				];

				$message = EventbookingHelper::replaceCaseInsensitiveTags($message, $replaces);

				$this->setMessage($message, 'error');
			}
			else
			{
				try
				{
					$model->batchCheckin($cid);
					$this->setMessage(Text::_('EB_CHECKIN_REGISTRANTS_SUCCESSFULLY'));
				}
				catch (Exception $e)
				{
					$this->setMessage($e->getMessage(), 'error');
				}
			}
		}

		$this->setRedirect($this->getViewListUrl());
	}
}