<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

namespace Joomla\Plugin\Task\MembershipPro\Extension;

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

defined('_JEXEC') or die;

final class MembershipPro extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;
	use DatabaseAwareTrait;

	/**
	 * @var string[]
	 *
	 * @since 4.1.0
	 */
	protected const TASKS_MAP = [
		'membershippro.deleteOldInvoicesPDF'                 => [
			'langConstPrefix' => 'PLG_TASK_MEMBERSHIPPRO_TASK_DELETE_OLD_INVOICES_PDF',
			'method'          => 'deleteOldInvoicesPDF',
		],
		'membershippro.deleteMemberCardsPDF'                 => [
			'langConstPrefix' => 'PLG_TASK_MEMBERSHIPPRO_TASK_DELETE_OLD_MEMBERS_CARD_PDF',
			'method'          => 'deleteOldMemberCardsPDF',
		],
		'membershippro.deleteOldSubscriptions'               => [
			'langConstPrefix' => 'PLG_TASK_MEMBERSHIPPRO_TASK_DELETE_OLD_SUBSCRIPTIONS',
			'form'            => 'delete_old_subscriptions',
			'method'          => 'deleteOldSubscriptions',
		],
		'membershippro.deleteIncompletePaymentSubscriptions' => [
			'langConstPrefix' => 'PLG_TASK_MEMBERSHIPPRO_TASK_DELETE_INCOMPLETE_PAYMENT_SUBSCRIPTIONS',
			'form'            => 'delete_incomplete_payments_subscriptions',
			'method'          => 'deleteIncompletePaymentSubscriptions',
		],
		'membershippro.cleanEmailLogs'                       => [
			'langConstPrefix' => 'PLG_TASK_MEMBERSHIPPRO_TASK_CLEAN_EMAILS_LOG',
			'form'            => 'clean_emails_log',
			'method'          => 'cleanEmailsLog',
		],
		'membershippro.offlinePaymentReminder'               => [
			'langConstPrefix' => 'PLG_TASK_MEMBERSHIPPRO_TASK_OFFLINE_PAYMENT_REMINDER',
			'form'            => 'offline_payment_reminder',
			'method'          => 'sendOfflinePaymentReminder',
		],
		'membershippro.sendOfflineRecurringInvoice'          => [
			'langConstPrefix' => 'PLG_TASK_MEMBERSHIPPRO_TASK_SEND_OFFLINE_RECURRING_INVOICE',
			'form'            => 'offline_recurring_invoice',
			'method'          => 'sendOfflineRecurringInvoice',
		],
		'membershippro.sendICPSNotification'                 => [
			'langConstPrefix' => 'PLG_TASK_MEMBERSHIPPRO_TASK_SEND_ICPS_NOTIFICATION',
			'form'            => 'icps_notification',
			'method'          => 'sendICPSNotification',
		],
	];

	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since 4.1.0
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
	 * @since 4.1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor.
	 *
	 * @param   DispatcherInterface  $dispatcher     The dispatcher
	 * @param   array                $config         An optional associative array of configuration settings
	 * @param   string               $rootDirectory  The root directory to look for images
	 *
	 * @since   4.2.0
	 */
	public function __construct(DispatcherInterface $dispatcher, array $config)
	{
		parent::__construct($dispatcher, $config);
	}

	/**
	 * Delete old invoices PDF to save storage spaces
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 */
	protected function deleteOldInvoicesPDF(ExecuteTaskEvent $event): int
	{
		$path = JPATH_ROOT . '/media/com_osmembership/invoices';

		$files = Folder::files($path, '\.pdf$', false, true);

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
	protected function deleteOldMemberCardsPDF(ExecuteTaskEvent $event): int
	{
		$path = JPATH_ROOT . '/media/com_osmembership/membercards';

		$files = Folder::files($path, '\.pdf$', false, true);

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
	protected function deleteOldSubscriptions(ExecuteTaskEvent $event): int
	{
		$params = new Registry($event->getArgument('params'));
		$delay  = (int) $params->get('delay', 10) ?: 10;

		$delayInDays = $delay * 365;

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('id')
			->from('#__osmembership_subscribers')
			->where('TIMESTAMPDIFF(DAY, created_date, UTC_DATE()) >= ' . $delayInDays)
			->order('id');
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (count($ids))
		{
			$this->deleteSubscriptions($ids);
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
	protected function deleteIncompletePaymentSubscriptions(ExecuteTaskEvent $event): int
	{
		$params = new Registry($event->getArgument('params'));
		$delay  = (int) $params->get('delay', 20) ?: 20;

		$db = $this->getDatabase();

		$query = $db->getQuery(true)
			->select('id')
			->from('#__osmembership_subscribers')
			->where('published = 0')
			->where('payment_method NOT LIKE "os_offline%"')
			->where('DATEDIFF(UTC_DATE(), created_date) >= ' . $delay)
			->order('id');
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (count($ids))
		{
			$this->deleteSubscriptions($ids);
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
		$delay  = (int) $params->get('delay', 30) ?: 30;

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->delete('#__osmembership_emails')
			->where('DATEDIFF(UTC_DATE(), sent_at) >= ' . $delay);
		$db->setQuery($query)
			->execute();

		return TaskStatus::OK;
	}

	/**
	 * Send reminder to subscriptions use offline payment to remind them to complete payment for these subscriptions
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 */
	protected function sendOfflinePaymentReminder(ExecuteTaskEvent $event): int
	{
		$params              = new Registry($event->getArgument('params'));
		$bccEmails           = $params->get('bcc_emails', '');
		$numberDays          = (int) $params->get('number_days') ?: 7;
		$numberSubscriptions = (int) $params->get('number_subscribers') ?: 15;

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('published = 0')
			->where('payment_method LIKE "os_offline%"')
			->where('offline_payment_reminder_email_sent = 0')
			->where('DATEDIFF(UTC_DATE(), created_date) >= ' . $numberDays);
		$db->setQuery($query, 0, $numberSubscriptions);
		$rows = $db->loadObjectList();

		if (count($rows) > 0)
		{
			// Require library + register autoloader
			require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

			$ids = [];

			foreach ($rows as $row)
			{
				$ids[] = $row->id;
			}

			\OSMembershipHelper::callOverridableHelperMethod(
				'Mail',
				'sendOfflinePaymentReminderEmails',
				[$rows, $bccEmails]
			);

			$query->clear()
				->update('#__osmembership_subscribers')
				->set('offline_payment_reminder_email_sent = 1')
				->whereIn('id', $ids);
			$db->setQuery($query)
				->execute();
		}

		return TaskStatus::OK;
	}

	/**
	 * Renew and send invoice for recurring subscription uses offline payment to ask users to pay for it
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 */
	protected function sendOfflineRecurringInvoice(ExecuteTaskEvent $event): int
	{
		$params            = new Registry($event->getArgument('params'));
		$numberSubEachTime = (int) $params->get('number_subscribers', 10) ?: 10;
		$numberDays        = (int) $params->get('number_days', 10) ?: 10;
		$published         = (int) $params->get('published', 0);

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('a.*')
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__osmembership_plans AS b  ON a.plan_id = b.id')
			->where('a.published = 1')
			->where('group_admin_id = 0')
			->where('a.offline_recurring_email_sent = 0')
			->where('b.recurring_subscription = 1')
			->where('recurring_subscription_cancelled = 0')
			->where('a.payment_method LIKE "os_offline%"')
			->where('DATEDIFF(a.to_date, NOW()) >= 0')
			->where('DATEDIFF(a.to_date, NOW()) <= ' . $numberDays);
		$db->setQuery($query, 0, $numberSubEachTime);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			$rows = [];
		}

		if ($rows === [])
		{
			return TaskStatus::OK;
		}

		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		/* @var \OSMembershipModelApi $model */
		$model  = \MPFModel::getTempInstance('Api', 'OSMembershipModel');
		$config = \OSMembershipHelper::getConfig();

		foreach ($rows as $row)
		{
			// Check to see whether the user has renewed before
			$query->clear()
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('plan_id = ' . $row->plan_id)
				->where('published = 1')
				->where('id > ' . $row->id)
				->where('((user_id > 0 AND user_id = ' . (int) $row->user_id . ') OR email="' . $row->email . '")');
			$db->setQuery($query);
			$total = (int) $db->loadResult();

			if (!$total)
			{
				$data    = ['published' => $published];
				$sParams = new Registry($row->params);

				if ($sParams->get('regular_amount') > 0)
				{
					$data = array_merge($data, [
						'amount'                 => $sParams->get('regular_amount'),
						'discount_amount'        => $sParams->get('regular_discount_amount'),
						'tax_amount'             => $sParams->get('regular_tax_amount'),
						'payment_processing_fee' => $sParams->get('regular_payment_processing_fee'),
						'gross_amount'           => $sParams->get('regular_gross_amount'),
					]);
				}

				$renewedSubscription = $model->renew($row->id, $data, false);
				\OSMembershipHelperMail::sendOfflineRecurringEmail($renewedSubscription, $config);
			}

			$query->clear()
				->update('#__osmembership_subscribers')
				->set('offline_recurring_email_sent = 1')
				->where('id = ' . $row->id);
			$db->setQuery($query)
				->execute();
		}

		return TaskStatus::OK;
	}

	/**
	 * Send notification email to admin to inform them about incomplete payment subscriptions
	 *
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 */
	protected function sendICPSNotification(ExecuteTaskEvent $event): int
	{
		$params = new Registry($event->getArgument('params'));

		// Only send notification to subscriptions within the last 48 hours
		$db    = $this->getDatabase();
		$now   = $db->quote(Factory::getDate('now', $this->getApplication()->get('offset'))->toSql(true));
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('published = 0')
			->where('icps_notified = 0')
			->where('payment_method NOT LIKE "os_offline%"')
			->where("TIMESTAMPDIFF(HOUR, created_date, $now) <= 48")
			->where("TIMESTAMPDIFF(MINUTE, created_date, $now) >= 20")
			->order('id');
		$db->setQuery($query, 0, 10);
		$rows = $db->loadObjectList();

		$subscriptions = [];
		$ids           = [];

		foreach ($rows as $row)
		{
			// Special case, without user_id and email, no way to check if he is registered again ir not
			if (!$row->user_id && !$row->email)
			{
				$subscriptions[] = $row;
				continue;
			}

			// Check to see if he has paid for the subscription in a different record
			$query->clear()
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('plan_id = ' . (int) $row->plan_id)
				->where('id > ' . $row->id)
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
				$subscriptions[] = $row;
				$ids[]           = $row->id;
			}
		}

		if (count($subscriptions) > 0)
		{
			// Require library + register autoloader
			require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

			\OSMembershipHelper::callOverridableHelperMethod(
				'Mail',
				'sendIncompletePaymentSubscriptionsEmails',
				[$subscriptions, $params]
			);

			// Mark the notification as sent
			$query->clear()
				->update('#__osmembership_subscribers')
				->set('icps_notified = 1')
				->whereIn('id', $ids);
			$db->setQuery($query)
				->execute();
		}

		return TaskStatus::OK;
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
	 * Delete subscriptions from given IDs
	 *
	 * @param   array  $ids
	 *
	 * @return void
	 */
	private function deleteSubscriptions(array $ids): void
	{
		// Require library + register autoloader
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

		\JLoader::register(
			'OSMembershipModelSubscription',
			JPATH_ADMINISTRATOR . '/components/com_osmembership/model/subscription.php'
		);

		/* @var \OSMembershipModelSubscription $model */
		$model = \MPFModel::getTempInstance('Subscription', 'OSMembershipModel');

		$model->delete($ids);
	}
}