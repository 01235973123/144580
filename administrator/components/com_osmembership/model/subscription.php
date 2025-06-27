<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use OSSolution\MembershipPro\Admin\Event\SMS\SendingSMSReminder;
use OSSolution\MembershipPro\Admin\Event\Subscription\AfterStoreSubscription;
use OSSolution\MembershipPro\Admin\Event\Subscription\AfterSubscriptionUpdate;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipActive;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipExpire;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipUpdate;
use OSSolution\MembershipPro\Admin\Event\Subscription\SubscriptionAfterSave;
use OSSolution\MembershipPro\Admin\Event\Subscriptions\SubscriptionsAfterDelete;

class OSMembershipModelSubscription extends MPFModelAdmin
{
	use OSMembershipModelSubscriptiontrait;

	/**
	 * Allow subscription model to trigger event
	 *
	 * @var bool
	 */
	protected $triggerEvents = true;

	/**
	 * Instantiate the model.
	 *
	 * @param   array  $config  configuration data for the model
	 */
	public function __construct($config = [])
	{
		$config['table'] = '#__osmembership_subscribers';

		parent::__construct($config);
	}

	/**
	 * Method to store a subscription record
	 *
	 * @param   MPFInput  $input
	 * @param   array     $ignore
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function store($input, $ignore = [])
	{
		$app    = Factory::getApplication();
		$db     = $this->getDbo();
		$config = OSMembershipHelper::getConfig();

		/* @var OSMembershipTableSubscriber $row */
		$row   = $this->getTable('Subscriber');
		$isNew = true;

		// Convert datetime fields value to format expected by database
		$this->convertDateTimeFields($input, ['created_date', 'from_date', 'to_date', 'payment_date']);

		$data = $input->getData();

		// Generate password automatically if configured
		if ($config->auto_generate_password && empty($data['password']))
		{
			$password         = UserHelper::genRandomPassword($config->get('auto_generate_password_length', 8));
			$data['password'] = $password;
		}

		// Create new user account for the subscription
		if (!$data['id'] && !$data['user_id'] && $data['username'] && $data['password'] && $data['email'])
		{
			// Set user language so that Joomla sends new notification email in right language
			$data['params']['language'] = $app->getLanguage()->getTag();

			$this->createUserAccountForSubscription($row, $data);
		}

		$planOrUserChanged            = false;
		$beforeUpdateSubscriptionData = [];
		$planFields                   = [];

		if ($data['id'])
		{
			$isNew = false;
			$row->load($data['id']);

			if ($input->exists('delete_avatar'))
			{
				$this->deleteUserAvatar($row);
			}

			$published = $row->published;

			$planFields                   = OSMembershipHelper::getProfileFields($row->plan_id, true);
			$beforeUpdateSubscriptionData = OSMembershipHelper::getProfileData($row, $row->plan_id, $planFields);

			if ($row->plan_id != $data['plan_id'] || $row->user_id != $data['user_id'])
			{
				$planOrUserChanged = true;

				// Since plan change, we need to trigger onMembershipExpire for the current subscription
				$event = new MembershipExpire(['row' => $row]);
				$app->triggerEvent($event->getName(), $event);
			}
		}
		else
		{
			$published = 0; //Default is pending
		}

		// Reset send reminder information on save2copy
		if ($isNew && $input->getCmd('task') == 'save2copy')
		{
			$nullDate = $db->getNullDate();

			$row->first_reminder_sent    = $row->second_reminder_sent = $row->third_reminder_sent = 0;
			$row->first_reminder_sent_at = $row->second_reminder_sent_at = $row->third_reminder_sent_at = $nullDate;

			// Extra reminders
			$extraReminderSentFields = [
				'fourth_reminder_sent',
				'fifth_reminder_sent',
				'sixth_reminder_sent',
			];

			foreach ($extraReminderSentFields as $extraField)
			{
				if (property_exists($row, $extraField))
				{
					$row->{$extraField} = 0;
				}

				$sentAtField = $extraField . '_at';

				if (property_exists($row, $sentAtField))
				{
					$row->{$sentAtField} = $nullDate;
				}
			}
		}

		$rowPlan = OSMembershipHelperDatabase::getPlan((int) $data['plan_id']);

		[$rowFields, $formFields] = $this->getFields((int) $data['plan_id']);

		// Filter data
		$data = $this->filterFormData($rowFields, $data);

		$form = new MPFForm($formFields);
		$form->setData($data)
			->bindData()
			->buildFieldsDependency();

		foreach ($form->getFields() as $field)
		{
			if (!$field->visible)
			{
				unset($data[$field->name]);
			}
		}

		// Avatar
		$avatar = $input->files->get('profile_avatar');

		if ($avatar && $avatar['name'])
		{
			$this->uploadAvatar($avatar, $row);
		}

		$row->bind($data);

		if (!$row->check())
		{
			throw new Exception($row->getError());
		}

		$row->user_id = (int) $row->user_id;
		$row->plan_id = (int) $row->plan_id;

		// Set subscription act data (subscribe or renew)
		if ($isNew && $row->user_id)
		{
			$this->setSubscriptionAction($row);
		}

		// Calculate from date, to date for new subscription record in case admin leave it empty
		$this->setSubscriptionDuration($row, $rowPlan, $rowFields, $data, $isNew);

		// In case data for amount field empty, mean users don't enter it, we will calculate subscription fee automatically
		if ($isNew && (!array_key_exists('amount', $data) || strlen($data['amount']) == 0) && $rowPlan)
		{
			$this->setSubscriptionFee($row, $rowPlan, $form, $data);
		}

		// Store recurring payment amount
		if (!$isNew && $config->enable_editing_recurring_payment_amounts)
		{
			$this->setRecurringSubscriptionFee($row, $data);
		}

		// Generate unique transaction_id if not provided
		if (!$row->transaction_id)
		{
			$row->transaction_id = $this->getUniqueTransactionId();
		}

		if (!$row->subscription_code)
		{
			$row->subscription_code = OSMembershipHelper::getUniqueCodeForField(
				'subscription_code',
				'#__osmembership_subscribers'
			);
		}

		if (!array_key_exists('gross_amount', $data))
		{
			$row->gross_amount = $row->amount - $row->discount_amount + $row->tax_amount + $row->payment_processing_fee;
		}

		if ($isNew)
		{
			if ($config->show_subscribe_newsletter_checkbox)
			{
				$row->subscribe_newsletter = empty($data['subscribe_to_newsletter']) ? 0 : 1;
			}
			else
			{
				$row->subscribe_newsletter = 1;
			}
		}

		// Set payment_amount to be the same with gross_amount
		if ($published != 1 && $row->published == 1 && $row->payment_amount == 0)
		{
			$row->payment_amount = $row->gross_amount;
		}

		if (!$row->store())
		{
			throw new Exception($row->getError());
		}

		$form->storeFormData($row->id, $data);

		if ($config->get('enable_select_show_hide_members_list') && isset($data['show_on_members_list']))
		{
			$this->updateShowOnMembersList($row);
		}

		if ($isNew)
		{
			$event = new AfterStoreSubscription(['row' => $row]);

			$app->triggerEvent($event->getName(), $event);
		}

		if ($planOrUserChanged && $row->published == 1)
		{
			$event = new MembershipActive(['row' => $row]);

			$app->triggerEvent($event->getName(), $event);
		}

		if ($published != 1 && $row->published == 1)
		{
			/**
			 * Recalculate subscription from date and subscription to date when offline subscription is approved to
			 * avoid users loose some days in their subscription
			 */
			if (str_starts_with($row->payment_method ?? '', 'os_offline')
				&& $published == 0
				&& !$isNew
				&& !(int) $rowPlan->expired_date)
			{
				$this->reCalculateSubscriptionDuration($row);
			}

			if (!(int) $row->payment_date)
			{
				$row->payment_date = Factory::getDate()->toSql();
			}

			//Membership active, trigger plugin
			if (OSMembershipHelperSubscription::needToTriggerActiveEvent($row))
			{
				$event = new MembershipActive(['row' => $row]);

				$app->triggerEvent($event->getName(), $event);
			}
			else
			{
				$row->active_event_triggered = 0;
				$row->store();
			}

			// Upgrade membership
			if ($row->act == 'upgrade' && $published == 0)
			{
				OSMembershipHelperSubscription::processUpgradeMembership($row);
			}

			if (!$isNew && $published == 0)
			{
				OSMembershipHelper::sendMembershipApprovedEmail($row);
			}
		}
		elseif ($published == 1)
		{
			if ($row->published != 1)
			{
				$event = new MembershipExpire(['row' => $row]);

				$app->triggerEvent($event->getName(), $event);
			}
		}

		// Send notification about new subscription
		if ($isNew && $this->needToSendEmailWhenSubscriptionAdded($row))
		{
			OSMembershipHelper::sendEmails($row, $config);
		}

		$data['id'] = $row->id;
		$input->set('id', $row->id);

		if (!$isNew)
		{
			// Check and update email of user if the email is changed in subscription record
			$this->updateUserEmail($row);

			$event = new MembershipUpdate(['row' => $row]);

			$app->triggerEvent($event->getName(), $event);

			$afterUpdateSubscriptionData = OSMembershipHelper::getProfileData($row, $row->plan_id, $planFields);

			$event = new AfterSubscriptionUpdate([
				'row'     => $row,
				'oldData' => $beforeUpdateSubscriptionData,
				'newData' => $afterUpdateSubscriptionData,
			]);

			$app->triggerEvent($event->getName(), $event);

			// Temp solution for update user groups when subscription record is updated
			if ($row->user_id)
			{
				$user = Factory::getUser($row->user_id);

				$this->updateUserGroups($user, $rowFields, $beforeUpdateSubscriptionData, $afterUpdateSubscriptionData);
			}
		}

		// Synchronize data from this subscription record to other subscriptions
		OSMembershipHelperSubscription::synchronizeProfileData($row, $rowFields);

		$event = new SubscriptionAfterSave([
			'context' => $this->context,
			'row'     => $row,
			'data'    => $data,
			'isNew'   => $isNew,
		]);

		$app->triggerEvent($event->getName(), $event);

		return true;
	}


	/**
	 * Delete custom fields data related to selected subscribers, trigger event before actual delete the data
	 *
	 * @param   array  $cid
	 */
	protected function beforeDelete($cid)
	{
		if (count($cid))
		{
			// Trigger onMembershipExpire event before subscriptions being deleted

			$app = Factory::getApplication();

			/* @var OSMembershipTableSubscriber $row */
			$row = $this->getTable('Subscriber');

			foreach ($cid as $id)
			{
				$row->load($id);

				$event = new MembershipExpire(['row' => $row]);

				$app->triggerEvent($event->getName(), $event);
			}
		}
	}

	/**
	 * Delete subscription custom fields data
	 *
	 * @param   array  $cid
	 */
	protected function afterDelete($cid)
	{
		if (!count($cid))
		{
			return;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__osmembership_field_value')
			->whereIn('subscriber_id', $cid);
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array  $pks    A list of the primary keys to change.
	 * @param   int    $value  The value of the published state.
	 *
	 * @throws Exception
	 */
	public function publish($pks, $value = 1)
	{
		$app = Factory::getApplication();
		$pks = (array) $pks;

		$this->beforePublish($pks, $value);

		// Change state of the records
		foreach ($pks as $pk)
		{
			/* @var OSMembershipTableSubscriber $row */
			$row     = $this->getTable();
			$trigger = false;

			if (!$row->load($pk))
			{
				throw new Exception('Invalid Subscription Record: ' . $pk);
			}

			$published = $row->published;

			if ($value == 1 && $row->published == 0)
			{
				$trigger = true;

				if ($row->payment_amount == 0)
				{
					$row->payment_amount = $row->gross_amount;
				}

				$rowPlan = OSMembershipHelperDatabase::getPlan($row->plan_id);

				if (str_starts_with($row->payment_method ?? '', 'os_offline')
					&& !(int) $rowPlan->expired_date)
				{
					$this->reCalculateSubscriptionDuration($row);
				}
			}

			$row->published = $value;

			if ($value == 1 && !(int) $row->payment_date)
			{
				$row->payment_date = Factory::getDate()->toSql();
			}

			$row->store();

			if ($trigger)
			{
				// Upgrade membership
				if ($row->act == 'upgrade')
				{
					OSMembershipHelperSubscription::processUpgradeMembership($row);
				}

				if (OSMembershipHelperSubscription::needToTriggerActiveEvent($row))
				{
					$event = new MembershipActive(['row' => $row]);

					$app->triggerEvent($event->getName(), $event);
				}
				else
				{
					$row->active_event_triggered = 0;
					$row->store();
				}

				OSMembershipHelper::sendMembershipApprovedEmail($row);
			}
			elseif ($published == 1 && $value == 0)
			{
				// Trigger onMembershipExpire event when un-publish subscription record

				$event = new MembershipExpire(['row' => $row]);

				$app->triggerEvent($event->getName(), $event);
			}
		}

		$app->triggerEvent($this->eventChangeState, [$this->context, $pks, $value]);

		$this->afterPublish($pks, $value);

		// Clear the component's cache
		$this->cleanCache();
	}

	/**
	 * Renew subscription for a given subscriber
	 *
	 * @param $id
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function renew($id)
	{
		$model = new OSMembershipModelApi();

		/* @var OSMembershipTableSubscriber $row */
		$row = $this->getTable('Subscriber');
		$row->load($id);

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from('#__osmembership_renewrates')
			->where('plan_id = ' . $row->plan_id);
		$db->setQuery($query);
		$renewOptions = $db->loadObjectList();

		$data = [];

		if (count($renewOptions) == 1)
		{
			$data['renew_option_id'] = $renewOptions[0]->id;
		}

		$model->renew($id, $data);

		return true;
	}

	/**
	 * Send batch emails to selected subscriptions by quangnv
	 *
	 * @param   MPFInput  $input
	 *
	 * @throws Exception
	 */
	public function batchMail($input)
	{
		$cid          = $input->get('cid', [], 'array');
		$emailSubject = $input->getString('subject');
		$emailMessage = $input->get('message', '', 'raw');
		$replyToEmail = $input->getString('reply_to_email');
		$bccEmail     = $input->getString('bcc_email');

		if (empty($cid))
		{
			throw new Exception('Please select subscriptions to send mass mail');
		}

		if (empty($emailSubject))
		{
			throw new Exception('Please enter subject of the email');
		}

		if (empty($emailMessage))
		{
			throw new Exception('Please enter message of the email');
		}

		$attachment     = $input->files->get('attachment', null, 'raw');
		$attachmentFile = null;
		$fileName       = null;

		if ($attachment['name'])
		{
			$allowedExtensions = OSMembershipHelper::getAllowedFileTypes();
			$fileName          = File::makeSafe($attachment['name']);
			$fileExt           = OSMembershipHelper::getFileExt($fileName);

			if (in_array(strtolower($fileExt), $allowedExtensions))
			{
				$attachmentFile = $attachment['tmp_name'];
			}
			else
			{
				throw new Exception(Text::sprintf('Attachment file type %s is not allowed', $fileExt));
			}
		}

		// Get list of subscriptions records
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('a.*, b.title, u.username')
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__osmembership_plans AS b ON a.plan_id = b.id')
			->leftJoin('#__users AS u ON a.user_id = u.id')
			->whereIn('a.id', $cid);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		OSMembershipHelperMail::sendMassMails(
			$rows,
			$emailSubject,
			$emailMessage,
			$replyToEmail,
			$bccEmail,
			$attachmentFile,
			$fileName,
			$input
		);
	}

	/**
	 * @param   MPFInput  $input
	 *
	 * @throws Exception
	 */
	public function batchSubscriptions($input)
	{
		$app          = Factory::getApplication();
		$cid          = array_filter(ArrayHelper::toInteger($input->post->get('cid', [], 'array')));
		$duration     = $input->getInt('extend_subscription_duration', 0);
		$durationUnit = $input->getString('extend_subscription_duration_unit', 'D');

		if (empty($cid))
		{
			throw new Exception('Please select subscriptions for the batch action');
		}

		if ($duration <= 0)
		{
			throw new Exception('Extend duration must be greater than 0');
		}

		if ($durationUnit == '')
		{
			throw new Exception('Please choose a valid extend duration unit');
		}

		PluginHelper::importPlugin('osmembership');

		$dateIntervalSpec = OSMembershipHelperSubscription::getDateIntervalString($duration, $durationUnit);
		$dateInterval     = new DateInterval($dateIntervalSpec);
		$now              = Factory::getDate();

		foreach ($cid as $id)
		{
			$triggerActiveEvent = false;
			$row                = $this->getTable();
			$row->load($id);
			$toDate = Factory::getDate($row->to_date);
			$toDate->add($dateInterval);
			$row->to_date = $toDate->toSql();

			if ($row->published == 2 && $toDate > $now)
			{
				$row->published     = 1;
				$triggerActiveEvent = true;
			}

			$row->store();

			if ($triggerActiveEvent)
			{
				$event = new MembershipActive(['row' => $row]);

				$app->triggerEvent($event->getName(), $event);
			}
		}
	}

	/**
	 * @param $cid
	 * @param $message
	 *
	 * @throws Exception
	 */
	public function batchSMS($cid, $message)
	{
		if (empty($cid))
		{
			throw new Exception('Please select subscriptions to send SMS');
		}

		if (empty($message))
		{
			throw new Exception('Please enter SMS Message');
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('a.*, b.title AS plan_title, c.username')
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__osmembership_plans AS b ON a.plan_id = b.id')
			->leftJoin('#__users AS c  ON a.user_id = c.id')
			->whereIn('a.id', $cid);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			if (!$row->phone)
			{
				continue;
			}

			$smsMessage = $message;

			$replaces = OSMembershipHelper::buildSMSTags($row);

			$smsMessage = OSMembershipHelper::replaceCaseInsensitiveTags($smsMessage, $replaces);

			$row->sms_message = $smsMessage;
		}

		PluginHelper::importPlugin('membershipprosms');

		$event = new SendingSMSReminder(['rows' => $rows]);

		Factory::getApplication()->triggerEvent($event->getName(), $event);
	}

	/**
	 * Get JTable object for the model
	 *
	 * @param   string  $name
	 *
	 * @return Table
	 */
	public function getTable($name = 'Subscriber')
	{
		return parent::getTable($name);
	}

	/**
	 * Resend confirmation email to subscriber
	 *
	 * @param   int  $id
	 *
	 * @return void
	 */
	public function resendEmail($id)
	{
		/* @var OSMembershipTableSubscriber $row */
		$row = $this->getTable();
		$row->load($id);

		// Load the default frontend language
		$tag = $row->language;

		if (!$tag || $tag == '*')
		{
			$tag = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		Factory::getApplication()->getLanguage()->load('com_osmembership', JPATH_ROOT, $tag);

		$config = OSMembershipHelper::getConfig();

		OSMembershipHelperMail::sendEmails($row, $config);
	}

	/**
	 * Recalculate subscription from_date and to_date for offline payment subscription when the subscription is approved
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return void
	 */
	protected function reCalculateSubscriptionDuration($row)
	{
		$createdDate = Factory::getDate($row->created_date);
		$fromDate    = Factory::getDate($row->from_date);
		$toDate      = Factory::getDate($row->to_date);
		$todayDate   = Factory::getDate('now');
		$diff        = $createdDate->diff($todayDate);
		$fromDate->add($diff);
		$toDate->add($diff);
		$row->from_date = $fromDate->toSql();
		$row->to_date   = $toDate->toSql();
		$row->store();
	}

	/**
	 * Override delete method to trigger onSubscriptionsAfterDelete for action logs
	 *
	 * @param   array  $cid
	 *
	 * @throws Exception
	 */
	public function delete($cid = [])
	{
		parent::delete($cid);

		$event = new SubscriptionsAfterDelete(['context' => $this->context, 'pks' => $cid]);

		Factory::getApplication()->triggerEvent($event->getName(), $event);
	}

	/**
	 * Resend confirmation email to registrant
	 *
	 * @param $id
	 *
	 * @return bool True if email is successfully delivered
	 * @throws Exception
	 */
	public function sendPaymentRequestEmail($id)
	{
		/* @var OSMembershipTableSubscriber $row */
		$row = $this->getTable();
		$row->load($id);

		if ($row->published != 0)
		{
			// We don't send request payment email to paid registration
			throw new Exception(Text::_('OSM_PAYMENT_REQUEST_PENDING_REQUEST'));
		}

		if ($row->gross_amount == 0)
		{
			throw new Exception(Text::_('OSM_PAYMENT_REQUEST_NO_PAYMENT_AMOUNT'));
		}

		$config = OSMembershipHelper::getConfig();

		OSMembershipHelper::callOverridableHelperMethod('Mail', 'sendRequestPaymentEmail', [$row, $config]);
	}

	/**
	 * Method create user account for subscription base on input data
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   array                        $data
	 *
	 * @throws Exception
	 */
	protected function createUserAccountForSubscription($row, &$data)
	{
		$data['user_id'] = $this->createUserAccount($data);
		$params          = ComponentHelper::getParams('com_users');

		// Store username and password so that we can send password via email
		if (Factory::getApplication()->isClient('site') && $params->get('sendpassword'))
		{
			// Store username and password so that it can be sent via email
			$row->username      = $data['username'];
			$row->user_password = OSMembershipHelperCryptor::encrypt($data['password']);
		}
	}

	/**
	 * Method to calculate and set created_date, from_date, to_date for the subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 * @param   array                        $rowFields
	 * @param   array                        $data
	 */
	protected function setSubscriptionDuration($row, $rowPlan, $rowFields, $data, $isNew)
	{
		if ($rowPlan->lifetime_membership == 1 && $data['to_date'] == '')
		{
			$row->to_date = '2099-12-31 00:00:00';
		}

		if ($isNew && $rowPlan)
		{
			if (!$row->created_date)
			{
				$row->created_date = Factory::getDate()->toSql();
			}

			if (!$row->from_date)
			{
				$date = $this->calculateSubscriptionFromDate($row, $rowPlan, $data);
			}

			if (!$row->to_date)
			{
				if (empty($date))
				{
					$date = Factory::getDate($row->from_date);
				}

				$this->calculateSubscriptionEndDate($row, $rowPlan, $date, $rowFields, $data);
			}
		}
		else
		{
			// When editing, we should convert the data back to UTC
			$offset = Factory::getApplication()->getIdentity()->getParam(
				'timezone',
				Factory::getApplication()->get('offset')
			);

			// Return a MySQL formatted datetime string in UTC.
			$row->created_date = Factory::getDate($row->created_date, $offset)->toSql();
			$row->from_date    = Factory::getDate($row->from_date, $offset)->toSql();

			if (!$rowPlan->lifetime_membership)
			{
				$row->to_date = Factory::getDate($row->to_date, $offset)->toSql();
			}

			if ((int) $row->payment_date && isset($data['payment_date']))
			{
				$row->payment_date = Factory::getDate($row->payment_date, $offset)->toSql();
			}
		}
	}

	/**
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 * @param   MPFForm                      $form
	 * @param   array                        $data
	 */
	protected function setSubscriptionFee($row, $rowPlan, $form, $data)
	{
		$config = OSMembershipHelper::getConfig();
		$form->setData($data)->bindData(true);
		$data['act'] = 'subscribe';

		$fees = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'calculateSubscriptionFee',
			[$rowPlan, $form, $data, $config, $data['payment_method']]
		);

		// Set the fee here
		$row->setup_fee              = $fees['setup_fee'];
		$row->amount                 = $rowPlan->recurring_subscription ? $fees['regular_amount'] : $fees['amount'];
		$row->discount_amount        = $rowPlan->recurring_subscription ? $fees['regular_discount_amount'] : $fees['discount_amount'];
		$row->tax_amount             = $rowPlan->recurring_subscription ? $fees['regular_tax_amount'] : $fees['tax_amount'];
		$row->payment_processing_fee = $rowPlan->recurring_subscription ? $fees['regular_payment_processing_fee'] : $fees['payment_processing_fee'];
		$row->gross_amount           = $rowPlan->recurring_subscription ? $fees['regular_gross_amount'] : $fees['gross_amount'];
		$row->tax_rate               = $fees['tax_rate'];

		// Store fee values for each custom fee fields if available
		$params = new Registry($row->params);
		$params->set('fields_fee_values', $fees['fields_fee_values'] ?? []);
		$row->params = $params->toString();
	}

	/**
	 * Store recurring subscription fee
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   array                        $data
	 */
	protected function setRecurringSubscriptionFee($row, $data)
	{
		$params                      = new Registry($row->params);
		$regularAmount               = $data['regular_amount'] ?? 0;
		$regularDiscountAmount       = $data['regular_discount_amount'] ?? 0;
		$regularTaxAmount            = $data['regular_tax_amount'] ?? 0;
		$regularPaymentProcessingFee = $data['regular_payment_processing_fee'] ?? 0;
		$regularGrossAmount          = $data['regular_gross_amount'] ?? 0;

		// Calculate gross amount base
		if ($regularAmount > 0 && empty($regularGrossAmount))
		{
			$regularGrossAmount = $regularAmount - $regularDiscountAmount + $regularTaxAmount + $regularPaymentProcessingFee;
		}

		$params->set('regular_amount', $regularAmount);
		$params->set('regular_discount_amount', $regularDiscountAmount);
		$params->set('regular_tax_amount', $regularTaxAmount);
		$params->set('payment_processing_fee', $regularPaymentProcessingFee);
		$params->set('regular_gross_amount', $regularGrossAmount);

		$row->params = $params->toString();
	}

	/**
	 * Method to calculate and set act data for subscription record
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	protected function setSubscriptionAction($row)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $row->user_id)
			->where('plan_id = ' . $row->plan_id)
			->where('(published >= 1 OR payment_method LIKE "os_offline%")');
		$db->setQuery($query);
		$total = $db->loadResult();

		if ($total > 0)
		{
			$row->act             = 'renew';
			$row->renew_option_id = OSM_DEFAULT_RENEW_OPTION_ID;
		}
		else
		{
			$row->act = 'subscribe';
		}
	}

	/**
	 * Update email of user account if the email is changed
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return void
	 */
	protected function updateUserEmail($row)
	{
		if (!$row->user_id)
		{
			return;
		}

		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__users')
			->where('id != ' . $row->user_id)
			->where('email = ' . $db->quote($row->email));
		$db->setQuery($query);
		$total = $db->loadResult();

		// No user uses the email, update this email to his user account
		if ($total == 0)
		{
			$query->clear()
				->update('#__users')
				->set('email = ' . $db->quote($row->email))
				->where('id = ' . $row->user_id);
			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Convert datetime fields data to the format which can be stored into database
	 *
	 * @param   MPFInput  $input
	 * @param   array     $fields
	 */
	protected function convertDateTimeFields($input, $fields = [])
	{
		$config         = OSMembershipHelper::getConfig();
		$dateTimeFormat = str_replace('%', '', $config->get('date_field_format', '%Y-%m-%d')) . ' H:i:s';

		foreach ($fields as $field)
		{
			$dateValue = $input->getString($field);

			if (!$dateValue)
			{
				continue;
			}

			try
			{
				$date = DateTime::createFromFormat($dateTimeFormat, $dateValue);

				if ($date !== false)
				{
					$input->set($field, $date->format('Y-m-d H:i:s'));
				}
			}
			catch (Exception $e)
			{
				// Do nothing
			}
		}
	}

	/**
	 * Method to check if we need to send notification email when subscription is added from backend
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return true
	 */
	protected function needToSendEmailWhenSubscriptionAdded($row): bool
	{
		return true;
	}
}
