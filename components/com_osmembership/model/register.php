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
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;
use OSSolution\MembershipPro\Admin\Event\Subscription\AfterStoreSubscription;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipActive;

class OSMembershipModelRegister extends MPFModel
{
	use OSMembershipModelSubscriptiontrait;
	use OSMembershipModelValidationtrait;

	/**
	 * Process Subscription
	 *
	 * @param   array     $data
	 * @param   MPFInput  $input
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function processSubscription($data, $input)
	{
		$session = Factory::getApplication()->getSession();
		$config  = OSMembershipHelper::getConfig();
		$user    = Factory::getApplication()->getIdentity();
		$userId  = $user->id;

		$rowPlan = OSMembershipHelperDatabase::getPlan((int) $data['plan_id']);

		// Get subscription related custom fields and build the form object
		[$rowFields, $formFields] = $this->getFields($rowPlan->id, true, null, $data['act'], 'register');

		// Filter form data
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

		/* @var $row OSMembershipTableSubscriber */
		$row = $this->getTable('Subscriber');

		// Create user account
		if (!$userId && $config->registration_integration)
		{
			if ($config->create_account_when_membership_active !== '1')
			{
				$userId = OSMembershipHelper::saveRegistration($data);
			}
			else
			{
				//Encrypt the password and store into  #__osmembership_subscribers table and create the account layout
				$data['user_password'] = OSMembershipHelper::encrypt($data['password1']);
			}

			// Store password into session and use it for auto login
			$session->set('mp_subscriber_password', $data['password1']);
		}

		// Uploading avatar
		$avatar = $input->files->get('profile_avatar');

		if ($avatar && $avatar['name'])
		{
			$this->uploadAvatar($avatar, $row);
		}

		// Store IP Address of subscriber
		if ($config->get('collect_ip_address', 1))
		{
			$data['ip_address'] = IpHelper::getIp();
		}
		else
		{
			$data['ip_address'] = '';
		}

		$data['subscription_code'] = OSMembershipHelper::getUniqueCodeForField(
			'subscription_code',
			'#__osmembership_subscribers'
		);
		$data['transaction_id']    = OSMembershipHelper::getUniqueCodeForField(
			'transaction_id',
			'#__osmembership_subscribers'
		);

		$row->bind($data);

		// Set subscription data which is not available from request
		$row->agree_privacy_policy = 1;

		if ($config->show_subscribe_newsletter_checkbox)
		{
			$row->subscribe_newsletter = empty($data['subscribe_to_newsletter']) ? 0 : 1;
		}
		else
		{
			$row->subscribe_newsletter = 1;
		}

		$row->id           = 0;
		$row->plan_id      = (int) $row->plan_id;
		$row->user_id      = (int) $userId;
		$row->created_date = Factory::getDate()->toSql();
		$row->language     = Factory::getApplication()->getLanguage()->getTag();
		$row->published    = 0;

		// Store user language
		if ($row->user_id)
		{
			$user = User::getInstance($row->user_id);
			$user->setParam('language', $row->language);
			$user->save();
		}

		// Disable free trial if he subscribed before
		OSMembershipHelper::callOverridableHelperMethod('Subscription', 'disableFreeTrialForPlan', [$rowPlan]);

		// Calculate and store subscription fees
		$fees = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'calculateSubscriptionFee',
			[$rowPlan, $form, $data, $config, $row->payment_method]
		);

		// Calculate and set subscription duration
		$this->calculateSubscriptionDuration($row, $rowPlan, $rowFields, $data, $fees);

		// Store payment amounts data for subscription
		$this->setSubscriptionAmounts($row, $fees, (bool) $rowPlan->recurring_subscription);

		// Store data for vies_registered field
		if (isset($fees['vies_registered']))
		{
			$row->vies_registered = $fees['vies_registered'];
		}
		else
		{
			$row->vies_registered = 0;
		}

		$couponCode = $input->getString('coupon_code');

		if ($couponCode && $fees['coupon_valid'])
		{
			if (!empty($fees['coupon_id']))
			{
				$row->coupon_id = $fees['coupon_id'];
			}
			else
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select('id')
					->from('#__osmembership_coupons')
					->where('code = ' . $db->quote($couponCode));
				$db->setQuery($query);
				$row->coupon_id = (int) $db->loadResult();
			}
		}

		// Mark subscription as free trial if needed to make it easier for payment processing
		if ($rowPlan->recurring_subscription && $fees['trial_duration'] > 0 && $fees['trial_gross_amount'] == 0)
		{
			$row->is_free_trial = 1;
		}
		else
		{
			$row->is_free_trial = 0;
		}

		$params = new Registry($row->params);
		$params->set('user_agent', $input->server->get('HTTP_USER_AGENT', '', 'string'));
		$params->set('user_ip', $input->server->get('REMOTE_ADDR', '', 'string'));

		$row->params = $params->toString();

		$row->store();

		//Store custom fields data
		$form->storeFormData($row->id, $data);

		// Trigger onAfterStoreSubscription event
		PluginHelper::importPlugin('osmembership');
		$app = Factory::getApplication();

		$event = new AfterStoreSubscription(['row' => $row]);

		$app->triggerEvent($event->getName(), $event);

		// Synchronize data from this subscription record to other subscriptions
		OSMembershipHelperSubscription::synchronizeProfileData($row, $rowFields);

		/* Accept privacy consent to avoid Joomla require users to accept it again */
		if (PluginHelper::isEnabled(
				'system',
				'privacyconsent'
			) && $row->user_id > 0 && $config->show_privacy_policy_checkbox)
		{
			OSMembershipHelperSubscription::acceptPrivacyConsent($row);
		}

		// Store subscription code into session so that we won't have to pass it in URL, support Paypal auto return
		$session->set('mp_subscription_id', $row->id);

		// Prepare payment amounts and pass it to payment plugin for payment processing
		$data['amount'] = $row->gross_amount;

		if ($rowPlan->recurring_subscription)
		{
			$data['regular_price']       = $fees['regular_gross_amount'];
			$data['trial_amount']        = $fees['trial_gross_amount'];
			$data['trial_duration']      = $fees['trial_duration'];
			$data['trial_duration_unit'] = $fees['trial_duration_unit'];
		}
		else
		{
			$data['regular_price']       = 0;
			$data['trial_amount']        = 0;
			$data['trial_duration']      = 0;
			$data['trial_duration_unit'] = '';
		}

		if (isset($data['x_card_num']))
		{
			$data['x_card_num'] = preg_replace('/\s+/', '', $data['x_card_num']);
		}

		if ($data['amount'] > 0 || ($rowPlan->recurring_subscription && $data['regular_price'] > 0))
		{
			$this->processPayment($row, $rowPlan, $data);
		}
		else
		{
			$this->completeFreeSubscription($row, $rowPlan);

			$app->redirect(
				Route::_(
					OSMembershipHelperRoute::getViewRoute(
						'complete',
						$input->getInt('Itemid', 0)
					) . '&subscription_code=' . $row->subscription_code,
					false
				)
			);
		}
	}

	/**
	 * Method to cancel a recurring subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function cancelSubscription($row)
	{
		$method = OSMembershipHelper::loadPaymentMethod($row->payment_method);

		/* @var os_authnet $method */

		$ret = false;

		if (method_exists($method, 'cancelSubscription'))
		{
			$ret = $method->cancelSubscription($row);
		}

		if ($ret)
		{
			OSMembershipHelperSubscription::cancelRecurringSubscription($row->id);
		}

		return $ret;
	}

	/**
	 * Verify payment
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function paymentConfirm($paymentMethod)
	{
		/* @var os_paypal $method */
		$method = OSMembershipHelper::loadPaymentMethod($paymentMethod);

		try
		{
			$method->verifyPayment();
		}
		catch (MPFPaymentException $e)
		{
			$this->handlePaymentError($e);
		}
	}

	/**
	 * Verify recurring payment
	 *
	 * @param $paymentMethod
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function recurringPaymentConfirm($paymentMethod)
	{
		/* @var os_paypal $method */
		$method = OSMembershipHelper::loadPaymentMethod($paymentMethod);

		try
		{
			$method->verifyRecurringPayment();
		}
		catch (MPFPaymentException $e)
		{
			$this->handlePaymentError($e);
		}
	}

	/**
	 * Calculate and set subscription duration
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 * @param   array                        $rowFields
	 * @param   array                        $data
	 * @param   array                        $fees
	 *
	 * @throws Exception
	 */
	protected function calculateSubscriptionDuration($row, $rowPlan, $rowFields, $data, $fees)
	{
		// Special case for monthly recurring plan with fixed payment date
		if ($rowPlan->payment_day > 0 && $rowPlan->subscription_length == 1 && $rowPlan->subscription_length_unit == 'M')
		{
			$now            = Factory::getDate();
			$row->from_date = $now->toSql();

			if ($fees['trial_duration'] > 0)
			{
				$now->modify('+' . $fees['trial_duration'] . ' days');
			}
			else
			{
				$dateIntervalSpec = 'P' . $rowPlan->subscription_length . $rowPlan->subscription_length_unit;
				$now->add(new DateInterval($dateIntervalSpec));
			}

			$row->to_date = $now->toSql();
		}
		else
		{
			// Calculate and set subscription start date, end date
			$fromDate = $this->calculateSubscriptionFromDate($row, $rowPlan, $data);

			// Calculate subscription end date
			$this->calculateSubscriptionEndDate($row, $rowPlan, $fromDate, $rowFields, $data);
		}
	}

	/**
	 * Finish subscription process for free subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 */
	protected function completeFreeSubscription($row, $rowPlan)
	{
		$config = OSMembershipHelper::getConfig();

		$row->published = 1;

		if ($rowPlan->price == 0
			&& !$rowPlan->free_plan_subscription_status)
		{
			$row->published = 0;
		}

		$row->store();

		if ($row->act == 'upgrade' && $row->published == 1)
		{
			OSMembershipHelperSubscription::processUpgradeMembership($row);
		}

		if ($row->published == 1)
		{
			if (OSMembershipHelperSubscription::needToTriggerActiveEvent($row))
			{
				PluginHelper::importPlugin('osmembership');

				$event = new MembershipActive(['row' => $row]);

				Factory::getApplication()->triggerEvent($event->getName(), $event);
			}
			else
			{
				$row->active_event_triggered = 0;
				$row->store();
			}
		}

		OSMembershipHelper::sendEmails($row, $config);
	}

	/**
	 * Form form some basic validation to make sure the data is valid
	 *
	 * @param   MPFInput  $input
	 *
	 * @return array
	 */
	public function validate($input)
	{
		$data              = $input->post->getData();
		$db                = $this->getDbo();
		$query             = $db->getQuery(true);
		$config            = OSMembershipHelper::getConfig();
		$rowFields         = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'getProfileFields',
			[(int) $data['plan_id'], true, null, $input->getCmd('act'), 'register']
		);
		$userId            = Factory::getApplication()->getIdentity()->id;
		$filterInput       = InputFilter::getInstance();
		$createUserAccount = $config->registration_integration && !$userId;
		$errors            = [];

		$paymentMethod = $data['payment_method'] ?? '';

		// Validate username and password
		if ($createUserAccount)
		{
			$username = $data['username'] ?? '';

			$errors = array_merge($errors, $this->validateUsername($username));

			if (!$config->auto_generate_password)
			{
				$password = $data['password1'] ?? '';
				$errors   = array_merge($errors, $this->validatePassword($password));
			}
		}

		// Validate email
		$email  = $data['email'] ?? '';
		$errors = array_merge($errors, $this->validateEmail($email, $createUserAccount));

		// Validate avatar
		$avatar = $input->files->get('profile_avatar');

		if ($avatar && $avatar['name'])
		{
			$avatarErrors = $this->validateAvatar($avatar);

			if (count($avatarErrors))
			{
				$errors = array_merge($errors, $avatarErrors);
			}
		}

		// Validate name
		$name = trim($data['first_name'] . ' ' . $data['last_name']);

		if ($filterInput->clean($name, 'TRIM') == '')
		{
			$errors[] = Text::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_YOUR_NAME');
		}

		// Validate form fields
		$form = new MPFForm($rowFields);
		$form->setData($data)->bindData();
		$form->buildFieldsDependency(false);
		$form->handleFieldsDependOnPaymentMethod($paymentMethod);

		// If there is error message, use it
		if ($formErrors = $form->validate())
		{
			$errors = array_merge($errors, $formErrors);
		}

		$plan = OSMembershipHelperDatabase::getPlan((int) $data['plan_id']);

		if ($subscriptionStartDateErrors = $this->validateUserSelectedSubscriptionStartDate($plan, $data))
		{
			$errors = array_merge($errors, $subscriptionStartDateErrors);
		}

		// If user has an active recurring subscription of this plan, he won't be allowed to subscribe to this plan anymore
		if ($plan->recurring_subscription && $userId)
		{
			$query->clear()
				->select('*')
				->from('#__osmembership_subscribers')
				->where('plan_id = ' . $plan->id)
				->where('user_id = ' . $userId)
				->where('published = 1')
				->order('id DESC');
			$db->setQuery($query);
			$rowSubscriptions = $db->loadObjectList();

			$canSubscribe = true;

			foreach ($rowSubscriptions as $rowSubscription)
			{
				if ($rowSubscription->subscription_id && !str_contains($rowSubscription->payment_method, 'os_offline'))
				{
					$canSubscribe = false;
					break;
				}
			}

			if (!$canSubscribe)
			{
				$errors[] = Text::_('OSM_HAVE_ACTIVE_RECURRING_SUBSCRIPTION_OF_THIS_PLAN_ALREADY');
			}
		}

		// Validate privacy policy
		if ($config->show_privacy_policy_checkbox && empty($data['agree_privacy_policy']))
		{
			$errors[] = Text::_('OSM_AGREE_PRIVACY_POLICY_ERROR');
		}

		// Validate renew subscription using offline payment multiple times
		if ($plan->price > 0 && $userId > 0 && str_contains($paymentMethod, 'os_offline'))
		{
			$query->clear()
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . (int) $userId)
				->where('plan_id = ' . (int) $data['plan_id'])
				->where('published = 0')
				->where('payment_method LIKE "os_offline%"')
				->where('to_date > ' . $db->quote(Factory::getDate()->toSql()));
			$db->setQuery($query);
			$total = $db->loadResult();

			if ($total)
			{
				// This user has an offline payment renewal has not paid yet, disable renewal using offline payment again
				$errors[] = Text::_('OSM_HAD_OFFLINE_PAYMENT_RENEWAL_ALREADY');
			}
		}

		if ($plan->require_coupon)
		{
			if (empty($data['coupon_code']))
			{
				$errors[] = Text::_('OSM_REQUIRE_VALID_COUPON');
			}
			else
			{
				// Make sure the provided coupon is valid for the plan
				$fees   = [];
				$coupon = OSMembershipHelper::callOverridableHelperMethod(
					'Subscription',
					'getSubscriptionCoupon',
					[$plan, $data, &$fees]
				);

				if (!$coupon)
				{
					$errors[] = Text::_('OSM_REQUIRE_VALID_COUPON');
				}
			}
		}

		// While renewing membership for a group membership plan, make sure number selected members is greater than current nummbers member in the group
		if ($userId > 0 && $input->getCmd(
				'act'
			) === 'renew' && $plan->number_members_field && !$plan->number_group_members)
		{
			// Get name of field
			$query->clear()
				->select('name')
				->from('#__osmembership_fields')
				->where('id = ' . $plan->number_members_field);
			$db->setQuery($query);
			$fieldName = $db->loadResult();

			$numberGroupMembersOnRenew = $input->getInt($fieldName, 0);

			// Get total number members of group
			$query->clear()
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('group_admin_id = ' . $userId)
				->where('plan_id = ' . $plan->id);
			$db->setQuery($query);
			$totalGroupMembers = (int) $db->loadResult();

			if ($numberGroupMembersOnRenew < $totalGroupMembers)
			{
				$errorMessage = Text::_('OSM_NUMBER_GROUP_MEMBERS_VALIDATION_ERROR');
				$errorMessage = str_replace('[CURRENT_NUMBER_MEMBERS]', $totalGroupMembers, $errorMessage);
				$errorMessage = str_replace('[SELECTED_NUMBER_MEMBERS]', $numberGroupMembersOnRenew, $errorMessage);
				$errors[]     = $errorMessage;
			}
		}

		// Validate EU VAT Number
		if ($config->block_subscription_if_eu_vat_number_is_invalid
			&& $config->eu_vat_number_field
			&& !empty($data[$config->eu_vat_number_field]))
		{
			$vatNumber = $data[$config->eu_vat_number_field];

			// If users doesn't enter the country code into the VAT Number, append the code
			$firstTwoCharacters = substr($vatNumber, 0, 2);

			$country     = $data['country'] ?? $config->default_country;
			$countryCode = OSMembershipHelper::getCountryCode($country);

			if ($countryCode == 'GR')
			{
				$countryCode = 'EL';
			}

			if (strtoupper($firstTwoCharacters) != $countryCode)
			{
				$vatNumber = $countryCode . $vatNumber;
			}

			if (!OSMembershipHelperEuvat::validateEUVATNumber($vatNumber))
			{
				$errors[] = Text::_('OSM_INVALID_EU_VAT_NUMBER_ENTERED');
			}
		}

		return $errors;
	}

	/**
	 * Method to set fees data for the subscription record
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   array                        $fees
	 * @param   bool                         $isRecurringSubscription
	 *
	 * return void
	 */
	protected function setSubscriptionAmounts($row, $fees, $isRecurringSubscription)
	{
		$row->setup_fee = $fees['setup_fee'];
		$row->tax_rate  = $fees['tax_rate'];

		if ($isRecurringSubscription)
		{
			if ($fees['trial_duration'] > 0)
			{
				$row->amount                 = $fees['trial_amount'] - $row->setup_fee;
				$row->discount_amount        = $fees['trial_discount_amount'];
				$row->tax_amount             = $fees['trial_tax_amount'];
				$row->payment_processing_fee = $fees['trial_payment_processing_fee'];
				$row->gross_amount           = $fees['trial_gross_amount'];
			}
			else
			{
				$row->amount                 = $fees['regular_amount'];
				$row->discount_amount        = $fees['regular_discount_amount'];
				$row->tax_amount             = $fees['regular_tax_amount'];
				$row->payment_processing_fee = $fees['regular_payment_processing_fee'];
				$row->gross_amount           = $fees['regular_gross_amount'];
			}
		}
		else
		{
			$row->amount                 = $fees['amount'];
			$row->discount_amount        = $fees['discount_amount'];
			$row->tax_amount             = $fees['tax_amount'];
			$row->payment_processing_fee = $fees['payment_processing_fee'];
			$row->gross_amount           = $fees['gross_amount'];
		}

		// Store regular payment amount for recurring subscriptions
		if ($isRecurringSubscription)
		{
			$params = new Registry($row->params);
			$params->set('regular_amount', $fees['regular_amount']);
			$params->set('regular_discount_amount', $fees['regular_discount_amount']);
			$params->set('regular_tax_amount', $fees['regular_tax_amount']);
			$params->set('regular_payment_processing_fee', $fees['regular_payment_processing_fee']);
			$params->set('regular_gross_amount', $fees['regular_gross_amount']);
			$row->params = $params->toString();

			// In case the coupon discount is 100%, we treat this as lifetime membership
			if ($fees['regular_gross_amount'] == 0)
			{
				$row->to_date = '2099-12-31 23:59:59';
			}
		}

		$params = new Registry($row->params);
		$params->set('fields_fee_values', $fees['fields_fee_values'] ?? []);
		$row->params = $params->toString();
	}

	/**
	 * Update and store coupon usage
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   array                        $fees
	 * @param   string                       $couponCode
	 *
	 * @return void
	 */
	protected function updateAndStoreCouponUsage($row, $fees, $couponCode)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// We need this check to make it backward compatible with existing override
		if (!empty($fees['coupon_id']))
		{
			$couponId = (int) $fees['coupon_id'];
		}
		else
		{
			$query->select('id')
				->from('#__osmembership_coupons')
				->where('code = ' . $db->quote($couponCode));
			$db->setQuery($query);
			$couponId = (int) $db->loadResult();
		}

		$query->clear()
			->update('#__osmembership_coupons')
			->set('used = used + 1')
			->where('id = ' . $couponId);
		$db->setQuery($query);
		$db->execute();

		$row->coupon_id = $couponId;
	}

	/**
	 * Process payment for subscripotion via selected payment gateway
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $rowPlan
	 * @param   array                        $data
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function processPayment($row, $rowPlan, $data)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		switch ($row->act)
		{
			case 'renew':
				$itemName = Text::_('OSM_PAYMENT_FOR_RENEW_SUBSCRIPTION');
				$itemName = str_replace('[PLAN_TITLE]', $rowPlan->title, $itemName);
				break;
			case 'upgrade':
				$itemName = Text::_('OSM_PAYMENT_FOR_UPGRADE_SUBSCRIPTION');
				$itemName = str_replace('[PLAN_TITLE]', $rowPlan->title, $itemName);

				//Get from Plan Title
				$query->select('a.title')
					->from('#__osmembership_plans AS a')
					->innerJoin('#__osmembership_upgraderules AS b ON a.id = b.from_plan_id')
					->where('b.id = ' . $row->upgrade_option_id);
				$db->setQuery($query);
				$fromPlanTitle = $db->loadResult();
				$itemName      = str_replace('[FROM_PLAN_TITLE]', $fromPlanTitle, $itemName);
				break;
			default:
				$itemName = Text::_('OSM_PAYMENT_FOR_SUBSCRIPTION');
				$itemName = str_replace('[PLAN_TITLE]', $rowPlan->title, $itemName);
				break;
		}

		$config = OSMembershipHelper::getConfig();

		// Build tags
		$replaces = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$itemName = OSMembershipHelper::replaceUpperCaseTags($itemName, $replaces);

		$data['item_name'] = $itemName;

		$paymentMethod = $data['payment_method'];
		$paymentClass  = OSMembershipHelper::loadPaymentMethod($paymentMethod);

		// Convert payment amount to USD if the currency is not supported by payment gateway
		$currency = $rowPlan->currency ?: $config->currency_code;

		if (method_exists($paymentClass, 'getSupportedCurrencies'))
		{
			$currencies = $paymentClass->getSupportedCurrencies();

			if (!in_array($currency, $currencies))
			{
				if ($data['amount'] > 0)
				{
					$data['amount'] = OSMembershipHelper::convertAmountToUSD($data['amount'], $currency);
				}

				if ($data['regular_price'] > 0)
				{
					$data['regular_price'] = OSMembershipHelper::convertAmountToUSD($data['regular_price'], $currency);
				}

				if ($data['trial_amount'] > 0)
				{
					$data['trial_amount'] = OSMembershipHelper::convertAmountToUSD($data['trial_amount'], $currency);
				}

				$currency = 'USD';
			}
		}

		$data['currency'] = $currency;

		if (!empty($data['x_card_num']) && empty($data['card_type']))
		{
			$data['card_type'] = OSMembershipHelperCreditcard::getCardType($data['x_card_num']);
		}

		$country         = empty($data['country']) ? $config->default_country : $data['country'];
		$data['country'] = OSMembershipHelper::getCountryCode($country);

		// Round payment amount before passing to payment gateway
		if ($currency == 'JPY')
		{
			$precision = 0;
		}
		else
		{
			$precision = 2;
		}

		if ($data['amount'] > 0)
		{
			$data['amount'] = round($data['amount'], $precision);
		}

		if ($data['regular_price'] > 0)
		{
			$data['regular_price'] = round($data['regular_price'], $precision);
		}

		if ($data['trial_amount'] > 0)
		{
			$data['trial_amount'] = round($data['trial_amount'], $precision);
		}

		// Store payment currency and payment amount for future validation
		$row->payment_currency = $currency;

		if ($rowPlan->recurring_subscription)
		{
			$row->trial_payment_amount = $data['trial_amount'];
			$row->payment_amount       = $data['regular_price'];
		}
		else
		{
			$row->payment_amount = $data['amount'];
		}

		$row->store();

		try
		{
			if ($rowPlan->recurring_subscription && method_exists($paymentClass, 'processRecurringPayment'))
			{
				$paymentClass->processRecurringPayment($row, $data);
			}
			else
			{
				$paymentClass->processPayment($row, $data);
			}
		}
		catch (MPFPaymentException $e)
		{
			$this->handlePaymentError($e);
		}
	}

	/**
	 * Handle payment processing error
	 *
	 * @param   MPFPaymentException  $e
	 *
	 * @return void
	 */
	protected function handlePaymentError(MPFPaymentException $e)
	{
		$app    = Factory::getApplication();
		$Itemid = $app->getInput()->getInt('Itemid', 0);
		$app->getSession()->set('omnipay_payment_error_reason', $e->getMessage());
		$app->redirect(Route::_('index.php?option=com_osmembership&view=failure&Itemid=' . $Itemid, false));
	}
}
