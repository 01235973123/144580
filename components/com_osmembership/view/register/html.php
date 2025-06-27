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
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use OSSolution\MembershipPro\Admin\Event\Profile\GetProfileData;

class OSMembershipViewRegisterHtml extends MPFViewHtml
{
	use OSMembershipViewRegister;

	/**
	 * The flag to mark if we should show payment fee
	 *
	 * @var bool
	 */
	protected $showPaymentFee;

	/**
	 * Browser page title
	 *
	 * @var string
	 */
	protected $pageTitle;

	/**
	 * ID of the user who is subscribing for the plan
	 *
	 * @var int
	 */
	protected $userId;

	/**
	 * The current selected payment method
	 *
	 * @var string
	 */
	protected $paymentMethod;

	/**
	 * List of select list
	 *
	 * @var array
	 */
	protected $lists;

	/**
	 * Component config data
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * The subscribing plan
	 *
	 * @var stdClass
	 */
	protected $plan;

	/**
	 * Available payment methods
	 *
	 * @var array
	 */
	protected $methods;

	/**
	 * Action: renew, upgrade or subscribe
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * Renew Option ID
	 *
	 * @var int
	 */
	protected $renewOptionId;

	/**
	 * Upgrade Option ID
	 *
	 * @var int
	 */
	protected $upgradeOptionId;

	/**
	 * Form object
	 *
	 * @var MPFForm
	 */
	protected $form;

	/**
	 * Contains detailed fees data for the subscription
	 *
	 * @var array
	 */
	protected $fees;

	/**
	 * Flag to mark if the system has different tax rate for each country
	 *
	 * @var int
	 */
	protected $countryBaseTax;

	/**
	 * The tax rate used for subscription
	 *
	 * @var float
	 */
	protected $taxRate;

	/**
	 * Flag to mark if the system has different tax rate for each state
	 *
	 * @var int
	 */
	protected $taxStateCountries;

	/**
	 * The country 2 code of the selected country
	 *
	 * @var string
	 */
	protected $countryCode;

	/**
	 * Bootstrap helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * The uploaded avatar
	 *
	 * @var string
	 */
	protected $avatar;

	/**
	 * Flag to determine of Stripe payment plugin is used
	 *
	 * @var bool
	 */
	protected $hasStripe = false;

	/**
	 * Flag to determine of Squarecard payment plugin is used
	 *
	 * @var bool
	 */
	protected $hasSquareCard = false;

	/**
	 * Flag to determine if icon is used to display payment methods
	 *
	 * @var bool
	 */
	protected $useIconForPaymentMethods;

	/**
	 * The message displayed at the top of subscription form
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * The currency symbol
	 *
	 * @var string
	 */
	protected $currencySymbol;

	/**
	 * Flag to determine if form grid is enabled for this plan
	 *
	 * @var bool
	 */
	protected $enableFormGrid = false;

	/**
	 * Name of views which could be used to get menu item parameters for the current view
	 *
	 * @var array
	 */
	protected $paramsViews = ['categories', 'plans', 'plan', 'register'];

	/**
	 * The checkout tracking code
	 *
	 * @var string
	 */
	protected $checkoutTrackingCode = '';

	/**
	 * Extra login buttons
	 *
	 * @var array
	 */
	protected $extraLoginButtons = [];

	/**
	 * Display subscription page
	 *
	 * @throws Exception
	 */
	public function display()
	{
		$app    = Factory::getApplication();
		$user   = $app->getIdentity();
		$config = OSMembershipHelper::getConfig();
		$input  = $this->input;
		$db     = $this->model->getDbo();
		$query  = $db->getQuery(true);

		// Load assets
		$this->loadAssets();

		$renewOptionId   = $input->getInt('renew_option_id', 0);
		$upgradeOptionId = $input->getInt('upgrade_option_id', 0);
		$planId          = $input->getInt('id', 0);
		$userId          = $user->id;
		$fieldSuffix     = OSMembershipHelper::getFieldSuffix();

		$plan = OSMembershipHelperDatabase::getPlan($planId);

		if (!$this->checkSubscriptionParameters($plan, $config))
		{
			return;
		}

		// Disable free trial for recurring plan if subscribed before
		OSMembershipHelper::callOverridableHelperMethod('Subscription', 'disableFreeTrialForPlan', [$plan]);

		if ($renewOptionId)
		{
			$action = 'renew';
		}
		elseif ($upgradeOptionId)
		{
			$action = 'upgrade';
		}
		else
		{
			$action = 'subscribe';

			// Check exclusive plans requirement
			$exclusivePlanIds  = OSMembershipHelperSubscription::getExclusivePlanIds();
			$subscribedPlanIds = OSMembershipHelperSubscription::getSubscribedPlans();

			if (in_array($plan->id, $exclusivePlanIds) && !in_array($plan->id, $subscribedPlanIds))
			{
				if ($config->exclusive_plans == 1)
				{
					$msg = Text::_('OSM_EXCLUSIVE_PLAN_SYSTEM');
				}
				else
				{
					$msg = Text::_('OSM_EXCLUSIVE_PLAN_CATEGORY');
				}

				$app->enqueueMessage($msg, 'warning');
				$app->redirect(Uri::root());
			}

			// Check to see whether the user signed up for this plan before or not, if he signed up before, we treat this as renewal
			if ($userId)
			{
				$query->clear()
					->select('COUNT(*)')
					->from('#__osmembership_subscribers')
					->where('user_id = ' . $userId)
					->where('plan_id = ' . $plan->id)
					->where('published IN (1, 2)');
				$db->setQuery($query);

				$total = (int) $db->loadResult();

				if ($total)
				{
					$query->clear()
						->select('id')
						->from('#__osmembership_renewrates')
						->where('plan_id = ' . $plan->id);
					$db->setQuery($query);
					$renewOptions = $db->loadObjectList();

					$renewMembershipMenuId = OSMembershipHelperRoute::findView('renewmembership', 0);
					$profileMenuId         = OSMembershipHelperRoute::findView('profile', 0);

					if (count($renewOptions) > 1)
					{
						$app->enqueueMessage(Text::_('OSM_CHOOSE_RENEW_OPTION'));

						if ($renewMembershipMenuId)
						{
							$app->redirect(Route::_('index.php?Itemid=' . $renewMembershipMenuId));
						}
						elseif ($profileMenuId)
						{
							$app->redirect(Route::_('index.php?Itemid=' . $profileMenuId));
						}
						else
						{
							$app->redirect(
								Route::_(
									'index.php?option=com_osmembership&view=renewmembership&Itemid=' . $this->Itemid
								)
							);
						}
					}
					else
					{
						$action = 'renew';
						// If there is only one renew option, assume that users will renew of that option

						if (count($renewOptions) == 1)
						{
							$data['renew_option_id'] = $renewOptions[0]->id;
						}
						else
						{
							$data['renew_option_id'] = OSM_DEFAULT_RENEW_OPTION_ID;
						}

						$renewOptionId = $data['renew_option_id'];
					}
				}
			}
		}

		$defaultPaymentMethod = OSMembershipHelperPayments::getDefautPaymentMethod(
			$plan->payment_methods,
			$plan->recurring_subscription
		);
		$paymentMethod        = $input->post->get('payment_method', $defaultPaymentMethod, 'cmd');

		if (!$paymentMethod)
		{
			$paymentMethod = $defaultPaymentMethod;
		}

		$methods = OSMembershipHelperPayments::getPaymentMethods($plan->recurring_subscription, $plan->payment_methods);

		if (count($methods) == 0)
		{
			$app->enqueueMessage(Text::_('OSM_NEED_TO_PUBLISH_PLUGIN'));
			$app->redirect(Uri::root());
		}

		$rowFields = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'getProfileFields',
			[$planId, true, null, $action, 'register']
		);

		$params = new Registry($plan->params ?? '{}');

		$numberFieldsPerRow = (int) $params->get('number_fields_per_row', $config->get('number_fields_per_row', ''));

		if ($numberFieldsPerRow > 0)
		{
			$containerSize = [
				2 => 'osm-one-half',
				3 => 'osm-one-third',
				4 => 'osm-one-quarter',
			];

			$fieldContainerSize = $containerSize[$numberFieldsPerRow];

			foreach ($rowFields as $rowField)
			{
				$rowField->container_size = $fieldContainerSize;
			}

			$this->enableFormGrid = true;
		}
		else
		{
			foreach ($rowFields as $rowField)
			{
				if ($rowField->container_size > 0)
				{
					$this->enableFormGrid = true;
					break;
				}
			}
		}

		if ($params->get('subscription_form_layout'))
		{
			$config->set('subscription_form_layout', $params->get('subscription_form_layout'));
		}

		if ($params->get('form_format'))
		{
			$config->set('form_format', $params->get('form_format'));
		}

		OSMembershipHelper::passFieldPaymentMethodDataToJS($rowFields);

		$data = $this->getFormData($input, $user, $planId, $rowFields, $config);
		$form = new MPFForm($rowFields);
		$form->setData($data)->bindData(true);
		$form->prepareFormFields('calculateSubscriptionFee();');
		$form->handleFieldsDependOnPaymentMethod($paymentMethod);

		$data['renew_option_id']   = $renewOptionId;
		$data['upgrade_option_id'] = $upgradeOptionId;
		$data['act']               = $action;

		$fees = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'calculateSubscriptionFee',
			[$plan, $form, $data, $config, $paymentMethod]
		);

		if ($plan->recurring_subscription)
		{
			$amount = $fees['regular_gross_amount'];
		}
		else
		{
			$amount = $fees['amount'];
		}

		$this->getFormMessage($plan, $action, $renewOptionId, $upgradeOptionId, $amount, $config, $fieldSuffix);

		$this->loadCaptcha($config, $user);

		// Set document meta data
		$active = Factory::getApplication()->getMenu()->getActive();

		$formLayout = $config->get('subscription_form_layout', 'default');

		if ($active && (isset($active->query['option'], $active->query['view'])
				&& $active->query['option'] == 'com_osmembership'
				&& $active->query['view'] == 'register'))
		{
			if (empty($active->query['layout']))
			{
				$formLayout = 'default';
			}
			else
			{
				$formLayout = $active->query['layout'];
			}
		}

		// Check to see whether we need to show coupon on subscription form or not
		if ($config->enable_coupon && !OSMembershipHelperSubscription::isCouponAvailableForPlan($planId))
		{
			// No coupon for this plan, so we just disable coupon
			$config->enable_coupon = 0;
		}

		if ($userId)
		{
			$query->clear()
				->select('avatar')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $userId);
			$db->setQuery($query);
			$avatar = $db->loadResult();
		}
		else
		{
			$avatar = '';
		}

		// Payment Methods parameters
		$currentYear        = date('Y');
		$expMonth           = $input->post->getInt('exp_month', date('n'));
		$expYear            = $input->post->getInt('exp_year', $currentYear);
		$lists['exp_month'] = HTMLHelper::_(
			'select.integerlist',
			1,
			12,
			1,
			'exp_month',
			'id="exp_month" class="input-medium form-select"',
			$expMonth,
			'%02d'
		);

		$lists['exp_year'] = HTMLHelper::_(
			'select.integerlist',
			$currentYear,
			$currentYear + 10,
			1,
			'exp_year',
			'id="exp_year" class="input-medium form-select"',
			$expYear
		);

		// Check to see if there is payment processing fee or not
		$showPaymentFee = false;
		$useIcon        = false;

		foreach ($methods as $method)
		{
			$paymentMethodName = $method->getName();

			if ($method->paymentFee)
			{
				$showPaymentFee = true;
			}

			if ($method->iconUri)
			{
				$useIcon = true;
			}

			if (str_contains($paymentMethodName, 'os_stripe'))
			{
				$this->hasStripe = true;
			}
			elseif (str_contains($paymentMethodName, 'os_squarecard'))
			{
				$this->hasSquareCard = true;
			}
		}

		if ($config->get('enable_select_show_hide_members_list')
			&& $config->get(
				'enable_select_show_hide_members_list_on_signup',
				$config->get('enable_select_show_hide_members_list')
			))
		{
			$existingSubscriptionsCount = 0;

			if ($userId > 0)
			{
				$query->clear()
					->select('COUNT(*)')
					->from('#__osmembership_subscribers')
					->where('user_id = ' . $userId)
					->where('(published IN (1, 2) OR (published = 0 AND payment_method LIKE "os_offline%"))');
				$db->setQuery($query);
				$existingSubscriptionsCount = $db->loadResult();
			}

			if ($existingSubscriptionsCount == 0)
			{
				$options   = [];
				$options[] = HTMLHelper::_('select.option', 1, Text::_('JYES'));
				$options[] = HTMLHelper::_('select.option', 0, Text::_('JNO'));

				$lists['show_on_members_list'] = HTMLHelper::_(
					'select.genericlist',
					$options,
					'show_on_members_list',
					'class="form-select"',
					'value',
					'text',
					1
				);
			}
		}

		$trackingCode = $config->checkout_tracking_code;

		if ($trackingCode)
		{
			$replaces['action']     = $action;
			$replaces['plan_id']    = $plan->id;
			$replaces['plan_title'] = $plan->title;

			if ($plan->recurring_subscription)
			{
				$replaces['amount']                 = $fees['regular_amount'];
				$replaces['discount_amount']        = $fees['regular_discount_amount'];
				$replaces['tax_amount']             = $fees['regular_tax_amount'];
				$replaces['gross_amount']           = $fees['regular_gross_amount'];
				$replaces['payment_processing_fee'] = $fees['regular_payment_processing_fee'];
			}
			else
			{
				$replaces['amount']                 = $fees['amount'];
				$replaces['discount_amount']        = $fees['discount_amount'];
				$replaces['tax_amount']             = $fees['tax_amount'];
				$replaces['gross_amount']           = $fees['gross_amount'];
				$replaces['payment_processing_fee'] = $fees['payment_processing_fee'];
			}

			$trackingCode = OSMembershipHelper::replaceUpperCaseTags($trackingCode, $replaces);
		}

		$this->params->def('page_title', $this->pageTitle);

		// Set form layout
		$this->setLayout($formLayout ?: 'default');

		$this->showPaymentFee = $showPaymentFee;

		if ($config->show_extra_login_buttons)
		{
			$this->extraLoginButtons = AuthenticationHelper::getLoginButtons('osm_login_form');
		}

		// Assign variables to template
		$this->userId            = $userId;
		$this->paymentMethod     = $paymentMethod;
		$this->lists             = $lists;
		$this->config            = $config;
		$this->plan              = $plan;
		$this->methods           = $methods;
		$this->action            = $action;
		$this->renewOptionId     = $renewOptionId;
		$this->upgradeOptionId   = $upgradeOptionId;
		$this->form              = $form;
		$this->fees              = $fees;
		$this->countryBaseTax    = (int) OSMembershipHelper::isCountryBaseTax();
		$this->taxRate           = OSMembershipHelper::calculateMaxTaxRate($planId, '', '', 2, false);
		$this->taxStateCountries = OSMembershipHelper::getTaxStateCountries();
		$this->countryCode       = OSMembershipHelper::getCountryCode($data['country'] ?? '');
		$this->bootstrapHelper   = OSMembershipHelperBootstrap::getInstance();
		$this->avatar            = $avatar;

		$this->useIconForPaymentMethods = $useIcon;
		$this->checkoutTrackingCode     = $trackingCode;

		$this->prepareDocument();

		parent::display();
	}

	/**
	 * Make sure user is allowed to access to this subscription form
	 *
	 * @param $plan
	 * @param $config
	 *
	 * @return bool
	 */
	protected function checkSubscriptionParameters($plan, $config)
	{
		// Check to see whether this is a valid form or not
		if (!$plan->id)
		{
			$this->displayOrRedirect(Text::_('OSM_INVALID_MEMBERSHIP_PLAN'));

			return false;
		}

		if (!$plan || $plan->published == 0)
		{
			$this->displayOrRedirect(Text::_('OSM_CANNOT_ACCESS_UNPUBLISHED_PLAN'));

			return false;
		}

		$user = Factory::getApplication()->getIdentity();

		if (!in_array($plan->access, $user->getAuthorisedViewLevels()))
		{
			if ($user->guest)
			{
				// Redirect users to login page
				$this->requestLogin();
			}
			else
			{
				$this->displayOrRedirect(Text::_('OSM_NOT_ALLOWED_PLAN'));

				return false;
			}
		}

		if (!in_array($plan->subscribe_access, $user->getAuthorisedViewLevels()))
		{
			if ($user->guest)
			{
				// Redirect users to login page
				$this->requestLogin();
			}
			else
			{
				$this->displayOrRedirect(Text::_('OSM_NOT_ALLOWED_PLAN'));

				return false;
			}
		}

		// Check if user can subscribe to the plan
		if (!OSMembershipHelper::canSubscribe($plan))
		{
			$loginRedirectUrl = OSMembershipHelper::getLoginRedirectUrl();

			if ($loginRedirectUrl)
			{
				$this->displayOrRedirect('', Route::_($loginRedirectUrl));

				return false;
			}
			elseif ($config->number_days_before_renewal)
			{
				// Redirect to membership profile page
				$profileItemId = OSMembershipHelperRoute::findView('profile', $this->Itemid);
				$redirectUrl   = Route::_('index.php?option=com_osmembership&view=profile&Itemid=' . $profileItemId);
				$this->displayOrRedirect(
					Text::sprintf('OSM_COULD_NOT_RENEWAL', $config->number_days_before_renewal),
					$redirectUrl
				);

				return false;
			}
			$this->displayOrRedirect(Text::_('OSM_YOU_ARE_NOT_ALLOWED_TO_SIGNUP'), false);

			return false;
		}

		// All conditions passed, return true
		return true;
	}

	/**
	 * Get data using for subscription form
	 *
	 * @param   MPFInput   $input
	 * @param   User       $user
	 * @param   int        $planId
	 * @param   array      $rowFields
	 * @param   MPFConfig  $config
	 *
	 * @return array
	 */
	protected function getFormData($input, $user, $planId, $rowFields, $config)
	{
		$userId = $user->id;

		if ($input->getInt('validation_error', 0))
		{
			$data = $input->getData();
		}
		else
		{
			$data = [];

			if ($userId)
			{
				// First, use plugin to get data
				$mappings = [];

				foreach ($rowFields as $rowField)
				{
					if ($rowField->field_mapping)
					{
						$mappings[$rowField->name] = $rowField->field_mapping;
					}
				}

				PluginHelper::importPlugin('osmembership');

				$event = new GetProfileData(['userId' => $userId, 'mappings' => $mappings]);

				$results = Factory::getApplication()->triggerEvent($event->getName(), $event);

				if (count($results))
				{
					foreach ($results as $res)
					{
						if (is_array($res) && count($res))
						{
							$data = $res;
							break;
						}
					}
				}

				// If data is not found from plugin, get it directly from user profile
				if (!count($data) && PluginHelper::isEnabled('osmembership', 'userprofile'))
				{
					$synchronizer = new MPFSynchronizerJoomla();
					$mappings     = [];

					foreach ($rowFields as $rowField)
					{
						if ($rowField->profile_field_mapping)
						{
							$mappings[$rowField->name] = $rowField->profile_field_mapping;
						}
					}

					$data = $synchronizer->getData($userId, $mappings);

					// Convert from state name to start 2 code
					if (!empty($data['country']) && !empty($data['state']) && strlen($data['state']) > 2)
					{
						$data['state'] = OSMembershipHelper::getStateCode($data['country'], $data['state']);
					}
				}

				// If still nothing found, get it from latest subscription
				if (!count($data))
				{
					// Check to see if this user has subscription of this plan, if Yes, use it, otherwise, use
					// data from profile record
					$db    = $this->model->getDbo();
					$query = $db->getQuery(true)
						->select('*')
						->from('#__osmembership_subscribers')
						->where('user_id = ' . $userId)
						->where('plan_id = ' . $planId)
						->where('(published >= 1 OR payment_method LIKE "os_offline%")')
						->order('id DESC');
					$db->setQuery($query);
					$rowProfile = $db->loadObject();

					if (!$rowProfile)
					{
						$query->clear('where')
							->where('user_id = ' . $userId)
							->where('is_profile = 1');
						$db->setQuery($query);
						$rowProfile = $db->loadObject();
					}

					if ($rowProfile)
					{
						$data = OSMembershipHelper::getProfileData($rowProfile, $planId, $rowFields);
					}
				}
			}
			else
			{
				$data = $input->getData();
			}
		}

		if ($userId && !isset($data['first_name']))
		{
			// Load the name from Joomla default name
			$name = $user->name;

			if ($name)
			{
				$pos = strpos($name, ' ');

				if ($pos !== false)
				{
					$data['first_name'] = substr($name, 0, $pos);
					$data['last_name']  = substr($name, $pos + 1);
				}
				else
				{
					$data['first_name'] = $name;
					$data['last_name']  = '';
				}
			}
		}

		if ($userId && !isset($data['email']))
		{
			$data['email'] = $user->email;
		}

		if (!isset($data['country']) || !$data['country'])
		{
			$data['country'] = $config->default_country;
		}

		// Handle Populate Data From Previous Subscription from custom field settings
		foreach ($rowFields as $rowField)
		{
			if (!$rowField->populate_from_previous_subscription)
			{
				unset($data[$rowField->name]);
			}
		}

		$data += $input->get->getData();

		return $data;
	}

	/**
	 * Get subscription form message
	 *
	 * @param $plan
	 * @param $action
	 * @param $renewOptionId
	 * @param $upgradeOptionId
	 * @param $amount
	 * @param $config
	 * @param $fieldSuffix
	 *
	 * @return string
	 */
	protected function getFormMessage($plan, $action, $renewOptionId, $upgradeOptionId, $amount, $config, $fieldSuffix)
	{
		$db      = $this->model->getDbo();
		$query   = $db->getQuery(true);
		$message = OSMembershipHelper::getMessages();

		if ($plan->category_id > 0)
		{
			$category = OSMembershipHelperDatabase::getCategory($plan->category_id);

			OSMembershipHelper::setPlanMessagesDataFromCategory($plan, $category, [
				'subscription_form_message',
			]);
		}

		if ($plan->currency_symbol)
		{
			$symbol = $plan->currency_symbol;
		}
		elseif ($plan->currency)
		{
			$symbol = $plan->currency;
		}
		else
		{
			$symbol = $config->currency_symbol;
		}

		$replaces = [];

		if ($action == 'renew')
		{
			$this->pageTitle = Text::_('OSM_RENEW_SUBSCRIPTION_PAGE_TITLE');

			if ($fieldSuffix && OSMembershipHelper::isValidMessage(
					$message->{'subscription_renew_form_msg' . $fieldSuffix}
				))
			{
				$formMessage = $message->{'subscription_renew_form_msg' . $fieldSuffix};
			}
			elseif (OSMembershipHelper::isValidMessage($plan->subscription_renew_form_msg))
			{
				$formMessage = $plan->subscription_renew_form_msg;
			}
			elseif (!empty($category) && OSMembershipHelper::isValidMessage($category->subscription_renew_form_msg))
			{
				$formMessage = $category->subscription_renew_form_msg;
			}
			else
			{
				$formMessage = $message->subscription_renew_form_msg;
			}

			if ($renewOptionId == OSM_DEFAULT_RENEW_OPTION_ID)
			{
				$renewOptionFrequency = $plan->subscription_length_unit;
				$renewOptionLength    = $plan->subscription_length;
			}
			else
			{
				$query->select('*')
					->from('#__osmembership_renewrates')
					->where('id = ' . $renewOptionId);
				$db->setQuery($query);
				$renewOption          = $db->loadObject();
				$renewOptionFrequency = $renewOption->renew_option_length_unit;
				$renewOptionLength    = $renewOption->renew_option_length;
			}

			$renewOptionDuration = OSMembershipHelperSubscription::getDurationText(
				$renewOptionLength,
				$renewOptionFrequency
			);

			$replaces['[NUMBER_DAYS] days'] = $renewOptionDuration;
			$replaces['RENEW_OPTION']       = $renewOptionDuration;
			$replaces['PLAN_TITLE']         = $plan->title;
			$replaces['AMOUNT']             = OSMembershipHelper::formatCurrency($amount, $config, $symbol);
		}
		elseif ($action == 'upgrade')
		{
			$this->pageTitle = Text::_('OSM_UPGRADE_SUBSCRIPTION_PAGE_TITLE');

			if ($fieldSuffix && OSMembershipHelper::isValidMessage(
					$message->{'subscription_upgrade_form_msg' . $fieldSuffix}
				))
			{
				$formMessage = $message->{'subscription_upgrade_form_msg' . $fieldSuffix};
			}
			elseif (!empty($category) && OSMembershipHelper::isValidMessage($category->subscription_upgrade_form_msg))
			{
				$formMessage = $category->subscription_upgrade_form_msg;
			}
			else
			{
				$formMessage = $message->subscription_upgrade_form_msg;
			}

			$query->select($db->quoteName('b.title' . $fieldSuffix))
				->from('#__osmembership_upgraderules AS a')
				->innerJoin('#__osmembership_plans AS b ON a.from_plan_id = b.id')
				->where('a.id = ' . $upgradeOptionId);
			$db->setQuery($query);
			$fromPlan = $db->loadResult();

			$replaces['PLAN_TITLE']      = $plan->title;
			$replaces['AMOUNT']          = OSMembershipHelper::formatCurrency($amount, $config, $symbol);
			$replaces['FROM_PLAN_TITLE'] = $fromPlan;
		}
		else
		{
			$this->pageTitle = Text::_('OSM_NEW_SUBSCRIPTION_PAGE_TITLE');

			if (OSMembershipHelper::isValidMessage(
					$plan->{'subscription_form_message' . $fieldSuffix}
				) || OSMembershipHelper::isValidMessage(
					$plan->subscription_form_message
				))
			{
				if ($fieldSuffix && OSMembershipHelper::isValidMessage(
						$plan->{'subscription_form_message' . $fieldSuffix}
					))
				{
					$formMessage = $plan->{'subscription_form_message' . $fieldSuffix};
				}
				else
				{
					$formMessage = $plan->subscription_form_message;
				}
			}
			elseif ($fieldSuffix && OSMembershipHelper::isValidMessage(
					$message->{'subscription_form_msg' . $fieldSuffix}
				))
			{
				$formMessage = $message->{'subscription_form_msg' . $fieldSuffix};
			}
			else
			{
				$formMessage = $message->subscription_form_msg;
			}

			$replaces['PLAN_TITLE'] = $plan->title;
			$replaces['AMOUNT']     = OSMembershipHelper::formatCurrency($amount, $config, $symbol);
		}

		if ($plan->lifetime_membership)
		{
			$planDuration = Text::_('OSM_LIFETIME');
		}
		else
		{
			$planDuration = OSMembershipHelperSubscription::getDurationText(
				$plan->subscription_length,
				$plan->subscription_length_unit
			);
		}

		$replaces['PLAN_DURATION'] = $planDuration;

		if ($plan->category_id)
		{
			$category                   = OSMembershipHelperDatabase::getCategory($plan->category_id);
			$replaces['CATEGORY_TITLE'] = $category->title;
		}
		else
		{
			$replaces['CATEGORY_TITLE'] = '';
		}

		$replaces['plan_short_description'] = $plan->short_description;
		$replaces['plan_description']       = $plan->description;

		$formMessage     = OSMembershipHelper::replaceUpperCaseTags($formMessage, $replaces);
		$this->pageTitle = OSMembershipHelper::replaceUpperCaseTags($this->pageTitle, $replaces);

		$this->message        = $formMessage;
		$this->currencySymbol = $symbol;

		return $formMessage;
	}

	/**
	 * Method to display a message or perform redirection, depends on whether this is a HMVC call or not
	 *
	 * @param   string  $message
	 * @param   string  $url
	 * @param   string  $messageType
	 */
	protected function displayOrRedirect($message = '', $url = '', $messageType = 'message')
	{
		if ($this->input->get('hmvc_call'))
		{
			echo $message;
		}
		else
		{
			if (!$url)
			{
				$url = Uri::root();
			}

			$app = Factory::getApplication();

			if ($message)
			{
				$app->enqueueMessage($message, $messageType);
			}

			$app->redirect($url);
		}
	}

	/**
	 * Set document meta-data and handle breadcumb if required
	 *
	 * @throws Exception
	 */
	protected function prepareDocument()
	{
		if ($this->input->getInt('hmvc_call'))
		{
			return;
		}

		$active = Factory::getApplication()->getMenu()->getActive();

		if (!$active)
		{
			return;
		}

		$this->setDocumentMetadata($this->params);
		$this->handleBreadcrumb($active);
	}

	/**
	 * Add breadcrumb items
	 *
	 * @param   MenuItem  $active
	 */
	protected function handleBreadcrumb($active)
	{
		if (!isset($active->query['view']))
		{
			return;
		}

		$pathway = Factory::getApplication()->getPathway();

		// Add link to plans list
		if ($active->query['view'] === 'categories' && $this->plan->category_id > 0)
		{
			$category = OSMembershipHelperDatabase::getCategory($this->plan->category_id);

			if ($category)
			{
				$pathway->addItem(
					$category->title,
					Route::_(OSMembershipHelperRoute::getCategoryRoute($category->id, $this->Itemid))
				);
			}
		}

		// Add link to plan details
		if (in_array($active->query['view'], ['categories', 'plans']))
		{
			$planMenuId = OSMembershipHelperRoute::getPlanMenuId(
				$this->plan->id,
				$this->plan->category_id,
				$this->Itemid
			);
			$pathway->addItem(
				$this->plan->title,
				Route::_(
					'index.php?option=com_osmembership&view=plan&catid=' . $this->plan->category_id . '&id=' . $this->plan->id . '&Itemid=' . $planMenuId
				)
			);
		}

		// Add last item to the breadcrumb
		if (in_array($active->query['view'], ['categories', 'plans', 'plan']))
		{
			switch ($this->action)
			{
				case 'renew':
					$pathway->addItem(Text::_('OSM_RENEW_SUBSCRIPTION_BREADCRUMB'));
					break;
				case 'upgrade':
					$pathway->addItem(Text::_('OSM_UPGRADE_SUBSCRIPTION_BREADCRUMB'));
					break;
				default:
					$pathway->addItem(Text::_('OSM_NEW_SUBSCRIPTION_BREADCRUMB'));
					break;
			}
		}
	}
}
