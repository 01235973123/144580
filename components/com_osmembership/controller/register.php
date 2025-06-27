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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;

class OSMembershipControllerRegister extends OSMembershipController
{
	use OSMembershipControllerCaptcha;

	/**
	 * Initialize data for renewing membership
	 */
	public function process_renew_membership()
	{
		$renewOptionId = $this->input->getString('renew_option_id', 0);

		if (!$renewOptionId)
		{
			$this->app->enqueueMessage(Text::_('OSM_INVALID_RENEW_MEMBERSHIP_OPTION'));
			$this->app->redirect(Uri::root(), 404);
		}

		if (str_contains($renewOptionId, '|'))
		{
			$renewOptionArray = explode('|', $renewOptionId);
			$this->input->set('id', (int) $renewOptionArray[0]);
			$this->input->set('renew_option_id', (int) $renewOptionArray[1]);
		}
		else
		{
			$this->input->set('id', (int) $renewOptionId);
			$this->input->set('renew_option_id', OSM_DEFAULT_RENEW_OPTION_ID);
		}

		$this->input->set('view', 'register');
		$this->input->set('layout', 'default');
		$this->display();
	}

	/**
	 * Initialize data for upgrading membership
	 */
	public function process_upgrade_membership()
	{
		$upgradeOptionId = $this->input->getInt('upgrade_option_id', 0);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('to_plan_id')
			->from('#__osmembership_upgraderules')
			->where('id=' . $upgradeOptionId);
		$db->setQuery($query);
		$upgradeRule = $db->loadObject();

		if ($upgradeRule)
		{
			//Set Plan ID
			$this->input->set('id', $upgradeRule->to_plan_id);
			$this->input->set('view', 'register');
			$this->input->set('layout', 'default');
			$this->display();
		}
		else
		{
			$this->app->enqueueMessage(Text::_('OSM_INVALID_UPGRADE_MEMBERSHIP_OPTION'));
			$this->app->redirect(Uri::root(), 404);
		}
	}

	/**
	 * Process subscription
	 *
	 * @throws Exception
	 */
	public function process_subscription()
	{
		$this->csrfProtection();

		$this->antiSpam();

		$config = OSMembershipHelper::getConfig();

		$input = $this->input;

		if (!empty($config->use_email_as_username) && !$this->app->getIdentity()->id)
		{
			$input->post->set('username', $input->post->getString('email'));
		}

		if (!$input->post->has('first_name') && !$input->post->has('last_name'))
		{
			$user = $this->app->getIdentity();

			if ($user->id)
			{
				/* @var DatabaseDriver $db */
				$db    = Factory::getContainer()->get('db');
				$query = $db->getQuery(true)
					->select('*')
					->from('#__osmembership_subscribers')
					->where('user_id = ' . $user->id)
					->where('(published IN (1,2) OR payment_method LIKE "os_offline%")')
					->order('id DESC');
				$db->setQuery($query);
				$rowSubscriber = $db->loadObject();
			}

			if (empty($rowSubscriber))
			{
				$input->post->set('first_name', $input->post->getString('email'));
			}
			else
			{
				$input->post->set('first_name', $rowSubscriber->first_name);
				$input->post->set('last_name', $rowSubscriber->last_name);
			}
		}

		$planId = $input->post->getInt('plan_id', 0);

		$plan = OSMembershipHelperDatabase::getPlan($planId);

		// Generate password automatically if configured
		if ($config->auto_generate_password)
		{
			$password = UserHelper::genRandomPassword($config->get('auto_generate_password_length', 8));
			$input->post->set('password1', $password);
			$input->post->set('password2', $password);
		}

		// Validate captcha
		$errorMessage = '';

		if (!$this->validateCaptcha($input, $errorMessage))
		{
			$this->app->enqueueMessage($errorMessage, 'warning');
			$input->set('view', 'register');
			$input->set('layout', 'default');
			$input->set('id', $input->getInt('plan_id', 0));
			$input->set('validation_error', 1);
			$this->display();

			return;
		}

		// Validate user input

		/**@var OSMembershipModelRegister $model * */
		$model  = $this->getModel();
		$errors = $model->validate($input);

		if (!$plan)
		{
			$errors[] = 'Invalid Plan Id';
		}

		if (count($errors))
		{
			// Enqueue the error messages
			foreach ($errors as $error)
			{
				$this->app->enqueueMessage($error, 'error');
			}

			$input->set('view', 'register');
			$input->set('layout', 'default');
			$input->set('id', $input->getInt('plan_id', 0));
			$input->set('validation_error', 1);
			$this->display();

			return;
		}

		// OK, data validation success, process the subscription

		try
		{
			$data = $input->post->getData();
			$model->processSubscription($data, $input);
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$input->set('view', 'register');
			$input->set('layout', 'default');
			$input->set('id', $input->getInt('plan_id', 0));
			$input->set('validation_error', 1);
			$this->display();

			return;
		}
	}

	/**
	 * Verify the payment and further process. Called by payment gateway when a payment completed
	 */
	public function payment_confirm()
	{
		/**@var OSMembershipModelRegister $model * */
		$model         = $this->getModel();
		$paymentMethod = $this->input->getString('payment_method');
		$model->paymentConfirm($paymentMethod);
	}

	/**
	 * Verify the payment and further process. Called by payment gateway when a recurring payment happened
	 */
	public function recurring_payment_confirm()
	{
		/**@var OSMembershipModelRegister $model * */
		$model         = $this->getModel();
		$paymentMethod = $this->input->getString('payment_method');
		$model->recurringPaymentConfirm($paymentMethod);
	}

	/**
	 * Cancel recurring subscription
	 *
	 * @throws Exception
	 */
	public function process_cancel_subscription()
	{
		$this->csrfProtection();
		$subscriptionId = $this->input->post->get('subscription_id', '', 'none');
		$Itemid         = $this->input->getInt('Itemid', 0);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_subscribers')
			->where('subscription_id = ' . $db->quote($subscriptionId));
		$db->setQuery($query);
		$rowSubscription = $db->loadObject();

		if ($rowSubscription && OSMembershipHelper::canCancelSubscription($rowSubscription))
		{
			/**@var OSMembershipModelRegister $model * */
			$model = $this->getModel('Register');
			$ret   = $model->cancelSubscription($rowSubscription);

			if ($ret)
			{
				$this->app->getSession()->set('mp_subscription_id', $rowSubscription->id);
				$this->app->redirect('index.php?option=com_osmembership&view=subscriptioncancel&Itemid=' . $Itemid);
			}
			else
			{
				// Redirect back to profile page, the payment plugin should enque the reason of failed cancellation so that it could be displayed to end user
				$this->app->redirect('index.php?option=com_osmembership&view=profile&Itemid=' . $Itemid);
			}
		}
		else
		{
			// Redirect back to user profile page
			$this->app->enqueueMessage(Text::_('OSM_INVALID_SUBSCRIPTION'));
			$this->app->redirect('index.php?option=com_osmembership&view=profile&Itemid=' . $Itemid, 404);
		}
	}

	/**
	 * Re-calculate subscription fee when subscribers choose a fee option on subscription form
	 *
	 * Called by ajax request. After calculation, the system will update the fee displayed on end users on subscription sign up form
	 */
	public function calculate_subscription_fee()
	{
		/* @var DatabaseDriver $db */
		$db     = Factory::getContainer()->get('db');
		$query  = $db->getQuery(true);
		$config = OSMembershipHelper::getConfig();

		$planId        = $this->input->getInt('plan_id', 0);
		$paymentMethod = $this->input->get('payment_method', '', 'none');
		$query->select('*')
			->from('#__osmembership_plans')
			->where('id=' . $planId);
		$db->setQuery($query);
		$rowPlan   = $db->loadObject();
		$rowFields = OSMembershipHelper::getProfileFields($planId);
		$data      = $this->input->getData();
		$form      = new MPFForm($rowFields);
		$form->setData($data)->bindData(false);
		$form->handleFieldsDependOnPaymentMethod($paymentMethod);

		$fees = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'calculateSubscriptionFee',
			[$rowPlan, $form, $data, $config, $paymentMethod]
		);

		$amountFields = [
			'setup_fee',
			'amount',
			'discount_amount',
			'tax_amount',
			'payment_processing_fee',
			'gross_amount',
			'trial_amount',
			'trial_discount_amount',
			'trial_tax_amount',
			'trial_payment_processing_fee',
			'trial_gross_amount',
			'regular_amount',
			'regular_discount_amount',
			'regular_tax_amount',
			'regular_payment_processing_fee',
			'regular_gross_amount',
		];

		foreach ($amountFields as $field)
		{
			if (isset($fees[$field]))
			{
				$fees[$field]                = round($fees[$field], 2);
				$fees[$field . '_formatted'] = OSMembershipHelper::formatAmount($fees[$field], $config);
			}
		}

		echo json_encode($fees);

		$this->app->close();
	}

	/**
	 * Get list of states for the selected country, using in AJAX request
	 */
	public function get_states()
	{
		$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
		$countryName     = $this->input->get('country_name', '', 'string');
		$fieldName       = $this->input->get('field_name', 'state', 'string');
		$stateName       = $this->input->get('state_name', '', 'string');

		if (!$countryName)
		{
			$config      = OSMembershipHelper::getConfig();
			$countryName = $config->default_country;
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_fields')
			->where('name = ' . $db->quote('state'));
		$db->setQuery($query);
		$row = $db->loadObject();

		$cssClasses = [];

		if ($row->css_class)
		{
			$cssClasses[] = $row->css_class;
		}

		if ($row->validation_rules)
		{
			$cssClasses[] = $row->validation_rules;
		}

		if ($bootstrapHelper->getFrameworkClass('uk-select'))
		{
			$cssClasses[] = $bootstrapHelper->getFrameworkClass('uk-select');
		}
		elseif ($bootstrapHelper->getFrameworkClass('form-select'))
		{
			$cssClasses[] = 'form-select';
		}
		elseif ($bootstrapHelper->getClassMapping('form-control'))
		{
			$cssClasses[] = 'form-control';
		}

		$query->clear()
			->select('country_id')
			->from('#__osmembership_countries')
			->where('name = ' . $db->quote($countryName));
		$db->setQuery($query);
		$countryId = $db->loadResult();

		//get state
		$query->clear()
			->select('state_2_code AS value, state_name AS text')
			->from('#__osmembership_states')
			->where('country_id=' . (int) $countryId)
			->where('published = 1')
			->order('state_name');
		$db->setQuery($query);
		$states  = $db->loadObjectList();
		$options = [];

		if (count($states))
		{
			$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_SELECT_STATE'));
			$options   = array_merge($options, $states);
		}
		else
		{
			$options[] = HTMLHelper::_('select.option', 'N/A', Text::_('OSM_NA'));
		}

		if (count($cssClasses))
		{
			$attributes = 'id="' . $fieldName . '" class="' . implode(' ', $cssClasses) . '"';
		}
		else
		{
			$attributes = 'id="' . $fieldName . '"';
		}

		echo HTMLHelper::_('select.genericlist', $options, $fieldName, $attributes, 'value', 'text', $stateName);

		$this->app->close();
	}

	/**
	 * Get depend fields status to show/hide custom fields based on selected options
	 */
	public function get_depend_fields_status()
	{
		$input = $this->input;

		/* @var DatabaseDriver $db */
		$db      = Factory::getContainer()->get('db');
		$fieldId = $this->input->get('field_id', 'int');

		$hiddenFields = [];

		//Get list of depend fields
		$allFieldIds = OSMembershipHelper::getAllDependencyFields($fieldId);

		//Get list of depend fields
		$languageSuffix = OSMembershipHelper::getFieldSuffix();
		$query          = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_fields')
			->whereIn('id', $allFieldIds)
			->where('published = 1')
			->order('ordering');

		if ($languageSuffix)
		{
			$query->select($db->quoteName('depend_on_options' . $languageSuffix, 'depend_on_options'));
		}

		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		$masterFields = [];
		$fieldsAssoc  = [];

		foreach ($rowFields as $rowField)
		{
			if ($rowField->depend_on_field_id)
			{
				$masterFields[] = $rowField->depend_on_field_id;
			}

			$fieldsAssoc[$rowField->id] = $rowField;
		}

		$masterFields = array_unique($masterFields);

		if (count($masterFields))
		{
			foreach ($rowFields as $rowField)
			{
				if ($rowField->depend_on_field_id && isset($fieldsAssoc[$rowField->depend_on_field_id]))
				{
					// If master field is hided, then children field should be hided, too
					if (in_array($rowField->depend_on_field_id, $hiddenFields))
					{
						$hiddenFields[] = $rowField->id;
					}
					else
					{
						$fieldName = $fieldsAssoc[$rowField->depend_on_field_id]->name;

						$masterFieldValues = $input->get($fieldName, '', 'none');

						if (is_array($masterFieldValues))
						{
							$selectedOptions = $masterFieldValues;
						}
						else
						{
							$selectedOptions = [$masterFieldValues];
						}

						if (is_string($rowField->depend_on_options) && is_array(
								json_decode($rowField->depend_on_options)
							))
						{
							$dependOnOptions = json_decode($rowField->depend_on_options);
						}
						else
						{
							$dependOnOptions = explode(',', $rowField->depend_on_options);
						}

						if (!count(array_intersect($selectedOptions, $dependOnOptions)))
						{
							$hiddenFields[] = $rowField->id;
						}
					}
				}
			}
		}

		$showFields = [];
		$hideFields = [];

		foreach ($rowFields as $rowField)
		{
			if (in_array($rowField->id, $hiddenFields))
			{
				$hideFields[] = 'field_' . $rowField->name;
			}
			else
			{
				$showFields[] = 'field_' . $rowField->name;
			}
		}

		echo json_encode(['show_fields' => implode(',', $showFields), 'hide_fields' => implode(',', $hideFields)]);

		$this->app->close();
	}

	/**
	 * Method to add some checks to prevent spams
	 *
	 */
	protected function antiSpam()
	{
		$config = OSMembershipHelper::getConfig();

		$honeypotFieldName = $config->get('honeypot_fieldname', 'osm_my_own_website_name');

		if ($this->input->getString($honeypotFieldName))
		{
			throw new Exception(Text::_('OSM_HONEYPOT_SPAM_DETECTED'), 403);
		}

		if ((int) $config->minimum_form_time > 0)
		{
			$startTime = $this->input->getInt(OSMembershipHelper::getHashedFieldName(), 0);

			if ((time() - $startTime) < (int) $config->minimum_form_time)
			{
				throw new Exception(Text::_('OSM_FORM_SUBMIT_TOO_FAST'), 403);
			}
		}

		if ((int) $config->maximum_submits_per_session)
		{
			$session = $this->app->getSession();

			$numberSubmissions = (int) $session->get('osm_number_submissions', 0) + 1;

			if ($numberSubmissions > (int) $config->maximum_submits_per_session)
			{
				throw new Exception(Text::_('OSM_EXCEEDED_NUMBER_FORM_SUBMISSIONS'), 403);
			}
			$session->set('osm_number_submissions', $numberSubmissions);
		}
	}
}
