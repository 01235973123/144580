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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;
use OSSolution\MembershipPro\Admin\Event\Subscription\AfterStoreSubscription;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipActive;

class OSMembershipModelApi extends MPFModel
{
	use OSMembershipModelSubscriptiontrait;
	use OSMembershipModelValidationtrait;

	/**
	 * Method to add/update a subscription
	 *
	 * @param   array  $data
	 *
	 * @return mixed The created/ updated subscription on success. In case there is errors, array containing
	 *               errors will be returned
	 *
	 * @throws Exception
	 */
	public function store($data)
	{
		$errors = $this->validate($data);

		if (count($errors))
		{
			return $errors;
		}

		$config = OSMembershipHelper::getConfig();
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);

		/* @var OSMembershipTableSubscriber $row */
		$row   = $this->getTable('Subscriber');
		$isNew = true;

		if (!empty($data['id']))
		{
			$row->load((int) $data['id']);
			$isNew = false;
		}

		// First, create user account if needed
		if (empty($data['user_id']) && !empty($data['username']))
		{
			$data['user_id'] = $this->createUserAccount($data);
		}

		// Bind data to subscription record from data array
		$row->bind($data, ['id']);

		// Set some basic subscritpion data if not provided in the input array
		$row->plan_id = (int) $row->plan_id;
		$row->user_id = (int) $row->user_id;

		if (!$row->created_date)
		{
			$row->created_date = Factory::getDate()->toSql();
		}

		$row->agree_privacy_policy = 1;

		if (array_key_exists('subscribe_newsletter', $data))
		{
			$row->subscribe_newsletter = $data['subscribe_newsletter'];
		}
		else
		{
			$row->subscribe_newsletter = 1;
		}

		if (!$row->transaction_id)
		{
			$row->transaction_id = strtoupper(UserHelper::genRandomPassword(16));
		}

		if (!$row->language)
		{
			$row->language = Factory::getApplication()->getLanguage()->getTag();
		}

		if (array_key_exists('published', $data))
		{
			$row->published = $data['published'];
		}
		else
		{
			$row->published = 1;

			if (!$row->payment_date || $row->payment_date == $db->getNullDate())
			{
				$row->payment_date = Factory::getDate()->toSql();
			}
		}

		// Check to see if this is a new subscription or a subscription renewal
		if ($isNew && $row->user_id > 0)
		{
			$query->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $row->user_id)
				->where('plan_id = ' . $row->plan_id)
				->where('(published >= 1 OR payment_method LIKE "os_offline%")');
			$db->setQuery($query);
			$total = $db->loadResult();

			if ($total > 0)
			{
				$row->act = 'renew';
			}
			else
			{
				$row->act = 'subscribe';
			}
		}
		elseif ($isNew && empty($row->act))
		{
			$row->act = 'subscribe';
		}

		$rowPlan = OSMembershipHelperDatabase::getPlan($row->plan_id);

		// Get subscription related custom fields and build the form object
		[$rowFields, $formFields] = $this->getFields($row->plan_id, true, null, $row->act);

		$form = new MPFForm($formFields);
		$form->setData($data)
			->bindData(true);

		// Set data for renew_option_id
		if ($row->act == 'renew' && !$row->renew_option_id)
		{
			$row->renew_option_id = OSM_DEFAULT_RENEW_OPTION_ID;
		}

		// Calculate and set subscription start date if not set
		if (!$row->from_date)
		{
			$fromDate = $this->calculateSubscriptionFromDate($row, $rowPlan, $data);
		}
		else
		{
			$fromDate = Factory::getDate($row->from_date, 'UCT');
		}

		// Calculate and set subscription end date if not set
		if (!$row->to_date)
		{
			// Calculate subscription end date
			$this->calculateSubscriptionEndDate($row, $rowPlan, $fromDate, $rowFields, $data);
		}

		// Calculate subscription fees if not set
		if (!array_key_exists('amount', $data) || $row->amount === '')
		{
			// Calculate and store subscription fees
			$fees = OSMembershipHelper::callOverridableHelperMethod(
				'Helper',
				'calculateSubscriptionFee',
				[$rowPlan, $form, $data, $config, $row->payment_method]
			);

			if ($rowPlan->recurring_subscription)
			{
				// Set the fee here
				$row->amount          = $fees['regular_amount'];
				$row->discount_amount = $fees['regular_discount_amount'];
				$row->tax_amount      = $fees['regular_tax_amount'];

				if (isset($fees['regular_payment_processing_fee']))
				{
					$row->payment_processing_fee = $fees['regular_payment_processing_fee'];
				}

				$row->gross_amount = $fees['regular_gross_amount'];
				$row->tax_rate     = $fees['tax_rate'];
			}
			else
			{
				// Set the fee here
				$row->amount                 = $fees['amount'];
				$row->discount_amount        = $fees['discount_amount'];
				$row->tax_amount             = $fees['tax_amount'];
				$row->payment_processing_fee = $fees['payment_processing_fee'];
				$row->gross_amount           = $fees['gross_amount'];
				$row->tax_rate               = $fees['tax_rate'];
			}

			$params = new Registry($row->params);
			$params->set('fields_fee_values', $fees['fields_fee_values'] ?? []);
			$row->params = $params->toString();
		}

		// Store the subscription
		$row->store();

		// In case update existing subscription, we will need to delete existing custom fields data
		if (!$isNew)
		{
			$query->clear()
				->delete('#__osmembership_field_value')
				->where('subscriber_id = ' . (int) $row->id);
			$db->setQuery($query)
				->execute();
		}

		//Store custom fields data
		$form->storeFormData($row->id, $data);

		// Trigger onAfterStoreSubscription event
		PluginHelper::importPlugin('osmembership');
		$app = Factory::getApplication();

		if ($isNew)
		{
			$event = new AfterStoreSubscription(['row' => $row]);
			$app->triggerEvent($event->getName(), $event);
		}

		// Synchronize data from this subscription record to other subscriptions
		OSMembershipHelperSubscription::synchronizeProfileData($row, $rowFields);

		if ($row->published == 1)
		{
			$event = new MembershipActive(['row' => $row]);

			$app->triggerEvent($event->getName(), $event);
		}

		return $row;
	}

	/**
	 * Renew a subscription
	 *
	 * @param   int    $id
	 * @param   array  $data  Data which will be used to override the data from original subscription
	 * @param   bool   $sendEmail
	 *
	 * @return OSMembershipTableSubscriber
	 *
	 * @throws Exception
	 */
	public function renew($id, $data = [], $sendEmail = true)
	{
		$config = OSMembershipHelper::getConfig();

		/* @var OSMembershipTableSubscriber $row */
		$row = $this->getTable('Subscriber');
		$row->load($id);

		// Build the data for the new subscription
		$data                        = array_merge($this->getSubscriptionData($id), $data);
		$data['plan_id']             = $row->plan_id;
		$data['user_id']             = $row->user_id;
		$data['language']            = $row->language;
		$data['subscription_id']     = $row->subscription_id;
		$data['gateway_customer_id'] = $row->gateway_customer_id;
		$data['act']                 = 'renew';
		$data['tax_rate']            = $row->tax_rate;

		if (!array_key_exists('renew_option_id', $data))
		{
			$data['renew_option_id'] = OSM_DEFAULT_RENEW_OPTION_ID;
		}

		if (!array_key_exists('published', $data))
		{
			$data['published'] = 1;
		}

		if (!array_key_exists('payment_method', $data))
		{
			$data['payment_method'] = $row->payment_method;
		}

		if ($data['published'] == 1)
		{
			$data['payment_date'] = Factory::getDate()->toSql();
		}

		if (!array_key_exists('subscription_code', $data))
		{
			$data['subscription_code'] = OSMembershipHelper::getUniqueCodeForField(
				'subscription_code',
				'#__osmembership_subscribers'
			);
		}

		// Store the subscription record
		$renewedSubscription = $this->store($data);

		// Send notification email to inform admin and users about this subscription
		if ($sendEmail)
		{
			OSMembershipHelperMail::sendEmails($renewedSubscription, $config);
		}

		return $renewedSubscription;
	}

	/**
	 * Method to renew a recurring subscription
	 *
	 * @param   int     $id
	 * @param   string  $subscriptionId
	 * @param   string  $transactionId
	 *
	 * @return mixed OSMembershipTableSubscriber on success, false other wise
	 *
	 * @throws Exception
	 */
	public function renewRecurringSubscription($id, $subscriptionId, $transactionId)
	{
		/* @var OSMembershipTableSubscriber $row */
		$row = $this->getTable('Subscriber');
		$row->load($id);
		$rowPlan = OSMembershipHelperDatabase::getPlan((int) $row->plan_id);

		// Increase payment_made of the subscription and store it into database
		$row->payment_made = $row->payment_made + 1;
		$row->store();

		// Update payment_made data for all records belong to this subscription
		if ($row->subscription_id)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->update('#__osmembership_subscribers')
				->set('payment_made = ' . $row->payment_made)
				->where('subscription_id = ' . $db->quote($row->subscription_id));
			$db->setQuery($query)
				->execute();
		}

		if (!$this->needToExtendSubscription($row, $rowPlan))
		{
			return false;
		}

		// Get subscription fees for
		$params = new Registry($row->params);

		if ($params->get('regular_amount') > 0)
		{
			$data = [
				'amount'                 => $params->get('regular_amount'),
				'discount_amount'        => $params->get('regular_discount_amount'),
				'tax_amount'             => $params->get('regular_tax_amount'),
				'payment_processing_fee' => $params->get('regular_payment_processing_fee'),
				'gross_amount'           => $params->get('regular_gross_amount'),
			];
		}
		else
		{
			$data = [
				'amount'                 => $row->amount,
				'discount_amount'        => $row->discount_amount,
				'tax_amount'             => $row->tax_amount,
				'payment_processing_fee' => $row->payment_processing_fee,
				'gross_amount'           => $row->gross_amount,
			];
		}

		$data['subscription_id']   = $subscriptionId;
		$data['transaction_id']    = $transactionId;
		$data['payment_made']      = $row->payment_made;
		$data['subscription_code'] = OSMembershipHelper::getUniqueCodeForField(
			'subscription_code',
			'#__osmembership_subscribers'
		);

		return $this->renew($id, $data);
	}

	/**
	 * Method to check if this's a valid recurring subscription renewal
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 *
	 * @return bool
	 */
	protected function needToExtendSubscription($row, $rowPlan)
	{
		// If this is a free trial or number payment is greater than 1, we know that it's a valid recurring renewal
		if (($rowPlan->trial_duration && $rowPlan->trial_amount == 0)
			|| $row->is_free_trial
			|| $row->payment_made > 1)
		{
			return true;
		}

		// False back case, if today date is 2 days more than the created date of the record, we know that it's a valid recurring renewal
		$todayDate   = Factory::getDate();
		$createdDate = Factory::getDate($row->created_date);

		$dateDiff = $createdDate->diff($todayDate);

		// If original date and and today date is greater than or = 2 days, then we are sure that this is a renewal
		if ($dateDiff->days >= 2)
		{
			return true;
		}

		// Not a valid recurring renewal
		return false;
	}

	/**
	 * Method to get data related to the subscription
	 *
	 * @param   int  $id
	 *
	 * @return mixed  array containing subscription data on success, false otherwise
	 */
	public function getSubscriptionData($id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$data  = [];

		/* @var OSMembershipTableSubscriber $row */
		$row = $this->getTable('Subscriber');

		if (!$row->load($id))
		{
			return false;
		}

		$rowFields = OSMembershipHelper::getProfileFields(
			$row->plan_id,
			true,
			$row->language,
			$row->act,
			null,
			$row->user_id
		);

		$query->select('a.name, b.field_value')
			->from('#__osmembership_fields AS a')
			->innerJoin('#__osmembership_field_value AS b ON a.id = b.field_id')
			->where('b.subscriber_id = ' . $id);
		$db->setQuery($query);
		$fieldValues = $db->loadObjectList('name');

		foreach ($rowFields as $rowField)
		{
			if ($rowField->is_core)
			{
				$data[$rowField->name] = $row->{$rowField->name};
			}
			elseif (isset($fieldValues[$rowField->name]))
			{
				$data[$rowField->name] = $fieldValues[$rowField->name]->field_value;
			}
		}

		return $data;
	}

	/**
	 * Basic subscription validation before storing in into database
	 *
	 * @param   array  $data
	 *
	 * @return array List of errors. In case no errors found, an empty array will be returned
	 */
	protected function validate(&$data)
	{
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);
		$errors = [];

		// If subscription id is provided, make sure it's an existing subscription
		if (!empty($data['id']))
		{
			$query->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('id = ' . (int) $data['id']);
			$db->setQuery($query);
			$total = $db->loadResult();

			if (!$total)
			{
				$errors[] = Text::sprintf('OSM_INVALID_SUBSCRIPTION_ID', $data['id']);
			}
		}

		if (empty($data['plan_id']))
		{
			$errors[] = Text::_('OSM_NEED_PLAN_FOR_SUBSCRIPTION');
		}
		else
		{
			$data['plan_id'] = (int) $data['plan_id'];

			$plan = OSMembershipHelperDatabase::getPlan($data['plan_id']);

			if (!$plan)
			{
				$errors[] = Text::sprintf('OSM_PLAN_DOES_NOT_EXIST', $data['plan_id']);
			}
		}

		if (!empty($data['user_id']))
		{
			$data['user_id'] = (int) $data['user_id'];
			$query->clear()
				->select('COUNT(*)')
				->from('#__users')
				->where('id = ' . $data['user_id']);
			$db->setQuery($query);
			$total = $db->loadResult();

			if (!$total)
			{
				$errors[] = Text::sprintf('OSM_USER_DOES_NOT_EXIST', $data['user_id']);
			}
		}
		else
		{
			// Check to see whether username is provided.  If yes, validate and make sure that username is valid
			$username = $data['username'] ?? '';
			$password = $data['password'] ?? '';

			if ($username)
			{
				$errors = array_merge($errors, $this->validateUsername($username));

				// If password is provided, validate password, otherwise, generate a random password
				if ($password)
				{
					$errors = array_merge($errors, $this->validatePassword($password));
				}
				else
				{
					$data['password'] = UserHelper::genRandomPassword();
				}
			}
		}

		return $errors;
	}
}
