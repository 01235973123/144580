<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

namespace Joomla\Plugin\Task\EventBooking\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

final class EventBooking extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;
	use DatabaseAwareTrait;

	/**
	 * @var string[]
	 *
	 * @since 5.0.7
	 */
	protected const TASKS_MAP = [
		'eventbooking.deleteOldInvoicesPDF'                  => [
			'langConstPrefix' => 'PLG_TASK_EVENTBOOKING_TASK_DELETE_OLD_INVOICES_PDF',
			'method'          => 'deleteInvoicesPDF',
		],
		'eventbooking.deleteTicketsPDF'                      => [
			'langConstPrefix' => 'PLG_TASK_EVENTBOOKING_TASK_DELETE_OLD_TICKETS_PDF',
			'method'          => 'deleteTicketsPDF',
		],
		'eventbooking.deleteOldCertificates'                 => [
			'langConstPrefix' => 'PLG_TASK_EVENTBOOKING_TASK_DELETE_OLD_CERTIFICATES',
			'method'          => 'deleteCertificatesPDF',
		],
		'eventbooking.deleteOldQRCodes'                      => [
			'langConstPrefix' => 'PLG_TASK_EVENTBOOKING_TASK_DELETE_OLD_QRCODES',
			'method'          => 'deleteOldQRCodes',
		],
		'eventbooking.deleteIncompeletePaymentRegistrations' => [
			'langConstPrefix' => 'PLG_TASK_EVENTBOOKING_TASK_DELETE_INCOMPLETE_PAYMENT_REGISTRATIONS',
			'form'            => 'delete_incomplete_payment_registrations',
			'method'          => 'deleteIncompletePaymentRegistrations',
		],
		'eventbooking.cleanEmailsLog'                        => [
			'langConstPrefix' => 'PLG_TASK_EVENTBOOKING_TASK_CLEAN_EMAILS_LOG',
			'form'            => 'clean_emails_log',
			'method'          => 'cleanEmailsLog',
		],
		'eventbooking.sendDepositPaymentReminderEmails'      => [
			'langConstPrefix' => 'PLG_TASK_EVENTBOOKING_TASK_SEND_DPR_EMAILS',
			'form'            => 'send_deposit_payment_reminder_emails',
			'method'          => 'sendDepositPaymentReminderEmails',
		],
		'eventbooking.offlinePaymentHandle'                  => [
			'langConstPrefix' => 'PLG_TASK_EVENTBOOKING_TASK_OFFLINE_PAYMENT_HANDLE',
			'form'            => 'offline_payment_handle',
			'method'          => 'handleOfflinePayment',
		],
		'eventbooking.emailRegistrantsList'                  => [
			'langConstPrefix' => 'PLG_TASK_EVENTBOOKING_TASK_EMAIL_REGISTRANTS_LIST',
			'form'            => 'email_registrants_list',
			'method'          => 'emailRegistrantsList',
		],
		'eventbooking.icprnotify'                            => [
			'langConstPrefix' => 'PLG_TASK_EVENTBOOKING_TASK_ICPR_NOTIFY',
			'form'            => 'icpr_notify',
			'method'          => 'notifyInCompletePaymentRegistration',
		],
	];

	/**
	 * Constructor.
	 *
	 * @param   DispatcherInterface  $dispatcher     The dispatcher
	 * @param   array                $config         An optional associative array of configuration settings
	 * @param   string               $rootDirectory  The root directory to look for images
	 *
	 */
	public function __construct(DispatcherInterface $dispatcher, array $config)
	{
		parent::__construct($dispatcher, $config);
	}

	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since 5.0.7
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'standardRoutineHandler',
			'onContentPrepareForm' => 'enhanceTaskItemForm',
		];
	}

	/**
	 * @var boolean
	 *
	 * @since 5.0.7
	 */
	protected $autoloadLanguage = true;

	/**
	 * Delete old invoices PDF to save storage spaces
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 */
	protected function deleteInvoicesPDF(ExecuteTaskEvent $event): int
	{
		$invoicesPath = JPATH_ROOT . '/media/com_eventbooking/invoices';

		$files = Folder::files($invoicesPath, '\.pdf$', false, true);

		foreach ($files as $file)
		{
			if ($this->isFileCreatedMoreThanOneDay($file))
			{
				File::delete($file);
			}
		}

		return TaskStatus::OK;
	}

	/**
	 * Delete old invoices PDF to save storage spaces
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 */
	protected function deleteTicketsPDF(ExecuteTaskEvent $event): int
	{
		$ticketsPath = JPATH_ROOT . '/media/com_eventbooking/tickets';

		$files = Folder::files($ticketsPath, '\.pdf$', false, true);

		foreach ($files as $file)
		{
			if ($this->isFileCreatedMoreThanOneDay($file))
			{
				File::delete($file);
			}
		}

		return TaskStatus::OK;
	}

	/**
	 * Delete old membercards PDF to save storage spaces
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 */
	protected function deleteCertificatesPDF(ExecuteTaskEvent $event): int
	{
		$certificatesPath = JPATH_ROOT . '/media/com_eventbooking/certificates';

		$files = Folder::files($certificatesPath, '\.pdf$', false, true);

		foreach ($files as $file)
		{
			if ($this->isFileCreatedMoreThanOneDay($file))
			{
				File::delete($file);
			}
		}

		return TaskStatus::OK;
	}

	/**
	 * Delete old membercards PDF to save storage spaces
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 */
	protected function deleteOldQRCodes(ExecuteTaskEvent $event): int
	{
		$qrCodesPath = JPATH_ROOT . '/media/com_eventbooking/qrcodes';

		$files = Folder::files($qrCodesPath, '\.png$|\.jpg$|\.jpeg$', false, true);

		foreach ($files as $file)
		{
			if ($this->isFileCreatedMoreThanOneDay($file))
			{
				File::delete($file);
			}
		}

		return TaskStatus::OK;
	}


	/**
	 * Delete old subscription records which are older than certain number of days in database
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 */
	protected function deleteIncompletePaymentRegistrations(ExecuteTaskEvent $event): int
	{
		$params = new Registry($event->getArgument('params'));
		$delay  = (int) $params->get('delay', 10) ?: 10;

		$db = $this->getDatabase();

		$query = $db->getQuery(true)
			->select('id')
			->from('#__eb_registrants')
			->where('published = 0')
			->where('group_id = 0')
			->where('payment_method NOT LIKE "os_offline%"')
			->where('DATEDIFF(UTC_DATE(), register_date) >= ' . $delay)
			->order('id');
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (count($ids))
		{
			// Require library + register autoloader
			require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

			\JLoader::register(
				'EventbookingModelRegistrant',
				JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/registrant.php'
			);

			/* @var  \EventbookingModelRegistrant $model */
			$model = \RADModel::getTempInstance('Registrant', 'EventbookingModel');
			$model->deleteRegistrationData($ids);
		}

		return TaskStatus::OK;
	}

	/**
	 * Clean email logs which are older than pre-configured number of days
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 */
	protected function cleanEmailsLog(ExecuteTaskEvent $event): int
	{
		$params = new Registry($event->getArgument('params'));

		$delay = (int) $params->get('delay', 10) ?: 10;
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->delete('#__eb_emails')
			->where('DATEDIFF(UTC_DATE(), sent_at) >= ' . $delay);
		$db->setQuery($query)
			->execute();

		return TaskStatus::OK;
	}

	/**
	 * Send deposit payment reminder emails to registrants
	 *
	 * @param   ExecuteTaskEvent  $event
	 *
	 * @return int
	 */
	protected function sendDepositPaymentReminderEmails(ExecuteTaskEvent $event): int
	{
		$params                  = new Registry($event->getArgument('params'));
		$bccEmail                = $params->get('bcc_email', '');
		$numberDays              = (int) $params->get('number_days', 7) ?: 7;
		$numberEmailSendEachTime = (int) $params->get('number_registrants', 15) ?: 15;

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		\EventbookingHelper::callOverridableHelperMethod(
			'Mail',
			'sendDepositReminder',
			[$numberDays, $numberEmailSendEachTime, $bccEmail]
		);

		return TaskStatus::OK;
	}

	/**
	 * Send deposit payment reminder emails to registrants
	 *
	 * @param   ExecuteTaskEvent  $event
	 *
	 * @return int
	 */
	protected function handleOfflinePayment(ExecuteTaskEvent $event): int
	{
		$params = new Registry($event->getArgument('params'));

		$numberDaysToSendReminder = (int) $params->get('number_days_to_send_reminders', 7);
		$numberDaysToCancel       = (int) $params->get('number_days_to_cancel', 0);
		$numberRegistrants        = (int) $params->get('number_registrants', 15);
		$baseOn                   = (int) $params->get('base_on', 0);
		$eventIds                 = $params->get('event_ids', []);

		// No need to send reminder or cancel offline payment registration, don't process further
		if ($numberDaysToSendReminder === 0 && $numberDaysToCancel === 0)
		{
			return TaskStatus::OK;
		}

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

		if ($numberDaysToSendReminder > 0)
		{
			\EventbookingHelper::callOverridableHelperMethod(
				'mail',
				'sendOfflinePaymentReminder',
				[$numberDaysToSendReminder, $numberRegistrants, $params]
			);
		}

		if ($numberDaysToCancel > 0)
		{
			$this->cancelRegistrations($numberDaysToCancel, $params);
		}

		return TaskStatus::OK;
	}

	/**
	 * Send deposit payment reminder emails to registrants
	 *
	 * @param   ExecuteTaskEvent  $event
	 *
	 * @return int
	 */
	protected function emailRegistrantsList(ExecuteTaskEvent $event): int
	{
		$params = new Registry($event->getArgument('params'));

		$db    = $this->getDatabase();
		$now   = $db->quote(Factory::getDate('now', $this->getApplication()->get('offset'))->toSql(true));
		$query = $db->getQuery(true)
			->select('*')
			->from('#__eb_events')
			->where('published = 1')
			->where('registrants_emailed = 0')
			->where('event_date >= ' . $now)
			->order('event_date');

		$timeToSend     = (int) $params->get('time_to_send', 1) ?: 1;
		$timeToSendUnit = $params->get('time_to_send_unit', 'd');

		if ($timeToSendUnit === 'd')
		{
			$query->where("DATEDIFF(event_date, $now) <= " . $timeToSend);
		}
		else
		{
			$query->where("TIMESTAMPDIFF(HOUR, $now, event_date) <= " . $timeToSend);
		}

		$db->setQuery($query, 0, 1);
		$row = $db->loadObject();

		if ($row)
		{
			// Require library + register autoloader
			require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

			\JLoader::register(
				'EventbookingModelEvent',
				JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/event.php'
			);

			/* @var  \EventbookingModelEvent $model */
			$model = \RADModel::getTempInstance('Event', 'EventbookingModel');
			$model->sendRegistrantsList($row->id);

			// Mark that registrants list was sent for the event
			$query->clear()
				->update('#__eb_events')
				->set('registrants_emailed = 1')
				->where('id = ' . $row->id);
			$db->setQuery($query)
				->execute();
		}

		return TaskStatus::OK;
	}

	/**
	 * Send deposit payment reminder emails to registrants
	 *
	 * @param   ExecuteTaskEvent  $event
	 *
	 * @return int
	 */
	protected function notifyInCompletePaymentRegistration(ExecuteTaskEvent $event): int
	{
		$params = new Registry($event->getArgument('params'));

		// Only send notification to registrations within the last 48 hours
		$db    = $this->getDatabase();
		$now   = $db->quote(Factory::getDate('now', $this->getApplication()->get('offset'))->toSql(true));
		$query = $db->getQuery(true)
			->select('*')
			->from('#__eb_registrants')
			->where('published = 0')
			->where('group_id = 0')
			->where('icpr_notified = 0')
			->where('payment_method NOT LIKE "os_offline%"')
			->where("TIMESTAMPDIFF(HOUR, register_date, $now) <= 48")
			->order('id');
		$db->setQuery($query, 0, 10);
		$rows = $db->loadObjectList();

		$registrants = [];
		$ids         = [];

		foreach ($rows as $row)
		{
			// Special case, without user_id and email, no way to check if he is registered again ir not
			if (!$row->user_id && !$row->email)
			{
				$registrants[] = $row;

				continue;
			}

			// Check to see if he has paid for the event
			$query->clear()
				->select('COUNT(*)')
				->from('#__eb_registrants')
				->where('event_id = ' . $row->event_id)
				->where('id != ' . $row->id)
				->where('(published = 1 OR payment_method LIKE "os_offline%")');

			if ($row->user_id)
			{
				$query->where('user_id = ' . $row->user_id);
			}
			else
			{
				$query->where('email = ' . $db->quote($row->email));
			}

			$db->setQuery($query);
			$total = $db->loadResult();

			if (!$total)
			{
				$registrants[] = $row;
				$ids[]         = $row->id;
			}
		}

		if (count($registrants) > 0)
		{
			// Require library + register autoloader
			require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

			\EventbookingHelper::callOverridableHelperMethod(
				'Mail',
				'sendIncompletePaymentRegistrationsEmails',
				[$registrants, $params]
			);

			// Mark the notification as sent
			$query->clear()
				->update('#__eb_registrants')
				->set('icpr_notified = 1')
				->whereIn('id', $ids);
			$db->setQuery($query)
				->execute();
		}

		return TaskStatus::OK;
	}

	/**
	 * Cancel registrations if no payment for offline payment received
	 *
	 * @param   int       $numberDaysToCancel
	 * @param   Registry  $params
	 *
	 * @return void
	 */
	private function cancelRegistrations(int $numberDaysToCancel, Registry $params): void
	{
		$db = $this->getDatabase();

		$query = $db->getQuery(true)
			->select('a.id')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.published = 0')
			->where('a.group_id = 0')
			->where('a.payment_method LIKE "os_offline%"')
			->order('a.register_date');

		$baseOn = $params->get('base_on', 0);

		if ($baseOn == 0)
		{
			$query->where('DATEDIFF(NOW(), a.register_date) >= ' . $numberDaysToCancel)
				->where('(DATEDIFF(b.event_date, NOW()) > 0 OR DATEDIFF(b.cut_off_date, NOW()) > 0)');
		}
		else
		{
			$query->where('DATEDIFF(b.event_date, NOW()) <= ' . $numberDaysToCancel)
				->where('DATEDIFF(b.event_date, a.register_date) > ' . $numberDaysToCancel)
				->where('DATEDIFF(b.event_date, NOW()) >= 0');
		}

		$eventIds = array_filter(ArrayHelper::toInteger($params->get('event_ids', [])));

		if (count($eventIds))
		{
			$query->whereIn('a.event_id', $eventIds);
		}

		$db->setQuery($query);

		try
		{
			$ids = $db->loadColumn();
		}
		catch (\Exception $e)
		{
			$ids = [];
		}

		if (count($ids))
		{
			/* @var \EventbookingModelRegistrant $model */
			$model = \RADModel::getTempInstance('Registrant', 'EventbookingModel');
			$model->cancelRegistrations($ids);
		}
	}

	/**
	 * Method to check if the file was created more than one day ago
	 *
	 * @param   string  $file  Path to the file
	 *
	 * @return bool
	 */
	private function isFileCreatedMoreThanOneDay($file): bool
	{
		$fileCreatedTime = filemtime($file);

		if ($fileCreatedTime === false)
		{
			return false;
		}

		$timeDifference = time() - $fileCreatedTime;

		return $timeDifference > 24 * 60 * 60;
	}

	/**
	 * Override registerListeners method to only register listeners if needed
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_eventbooking'))
		{
			return;
		}

		parent::registerListeners();
	}
}