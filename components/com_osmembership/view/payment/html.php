<?php
/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2012 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class OSMembershipViewPaymentHtml extends MPFViewHtml
{
	use OSMembershipViewRegister;

	/**
	 * Bootstrap Helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * The message displayed above form
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Field suffix use to get data from right language
	 *
	 * @var string
	 */
	protected $fieldSuffix;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * The form
	 *
	 * @var MPFForm
	 */
	protected $form;

	/**
	 * The plan which the subscription belongs to
	 *
	 * @var stdClass
	 */
	protected $plan;

	/**
	 * The subscription record to process payment
	 *
	 * @var stdClass
	 */
	protected $row;

	/**
	 * Contains select lists used on the form
	 *
	 * @var array
	 */
	protected $lists;

	/**
	 * The available payment methods
	 *
	 * @var array
	 */
	protected $methods;

	/**
	 * The current selected payment method
	 *
	 * @var string
	 */
	protected $paymentMethod;

	/**
	 * The currency symbol
	 *
	 * @var string
	 */
	protected $currencySymbol;

	/**
	 * Flag to determine if Stripe payment plugin is used
	 *
	 * @var bool
	 */
	protected $hasStripe = false;

	/**
	 * Flag to determine if Squarecard payment plugin is used
	 *
	 * @var bool
	 */
	protected $hasSquareCard = false;

	/**
	 * Flag to determine
	 *
	 * @var bool
	 */
	protected $useIconForPaymentMethods = false;

	/**
	 * Display interface to user
	 */
	public function display()
	{
		if ($this->getLayout() == 'complete')
		{
			$this->displayPaymentComplete();

			return;
		}

		$user   = Factory::getApplication()->getIdentity();
		$config = OSMembershipHelper::getConfig();

		// Load view assets
		$this->loadAssets();

		$row = $this->model->getData();

		if (empty($row))
		{
			echo Text::_('OSM_INVALID_SUBSCRIPTION_RECORD');

			return;
		}

		if ($row->published == 1)
		{
			echo Text::_('OSM_SUBSCRIPTION_WAS_PAID_ALREADY');

			return;
		}

		$plan = OSMembershipHelperDatabase::getPlan($row->plan_id);
		$this->setBaseFormData($row, $plan, $this->input);

		$this->loadCaptcha($config, $user);

		$this->setLayout('default');

		parent::display();
	}

	/**
	 * Method to calculate and set base form data
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   OSMembershipTablePlan        $plan
	 * @param   MPFInput                     $input
	 */
	protected function setBaseFormData($row, $plan, $input)
	{
		$app       = Factory::getApplication();
		$config    = OSMembershipHelper::getConfig();
		$user      = $app->getIdentity();
		$userId    = $user->id;
		$rowFields = OSMembershipHelper::getProfileFields($plan->id, true, null, null, 'payment');

		$captchaInvalid = $this->input->getInt('captcha_invalid', 0);

		if ($captchaInvalid)
		{
			$data = $this->input->post->getData();
		}
		else
		{
			$data = [];

			// IN case there is no data, get it from URL (get for example)
			if (empty($data))
			{
				$data = $this->input->getData();
			}
		}

		if ($userId && !isset($data['first_name']))
		{
			//Load the name from Joomla default name
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

		//Get data
		$form = new MPFForm($rowFields);

		if ($captchaInvalid)
		{
			$useDefault = false;
		}
		else
		{
			$useDefault = true;
		}

		$form->setData($data)
			->bindData($useDefault);

		$defaultPaymentMethod = OSMembershipHelperPayments::getDefautPaymentMethod($plan->payment_methods, false, true);
		$paymentMethod        = $input->post->get('payment_method', $defaultPaymentMethod, 'cmd');

		if (!$paymentMethod)
		{
			$paymentMethod = $defaultPaymentMethod;
		}

		$methods = OSMembershipHelperPayments::getPaymentMethods(false, $plan->payment_methods, true);

		if (count($methods) == 0)
		{
			$app->enqueueMessage(Text::_('OSM_NEED_TO_PUBLISH_PLUGIN'));
			$app->redirect(Uri::root());
		}

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
		$lists['exp_year']  = HTMLHelper::_(
			'select.integerlist',
			$currentYear,
			$currentYear + 10,
			1,
			'exp_year',
			'class="id="exp_year" input-medium form-select"',
			$expYear
		);

		if (empty($paymentMethod))
		{
			$paymentMethod = $methods[0]->getName();
		}

		foreach ($methods as $method)
		{
			$paymentMethodName = $method->getName();

			if ($method->iconUri)
			{
				$this->useIconForPaymentMethods = true;
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

		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
		$this->message         = OSMembershipHelper::getMessages();
		$this->fieldSuffix     = OSMembershipHelper::getFieldSuffix();
		$this->config          = $config;
		$this->plan            = $plan;
		$this->form            = $form;
		$this->row             = $row;
		$this->lists           = $lists;
		$this->methods         = $methods;
		$this->paymentMethod   = $paymentMethod;
		$this->currencySymbol  = $plan->currency_symbol ?: $config->currency_symbol;
	}

	/**
	 * Display payment complete page
	 */
	protected function displayPaymentComplete()
	{
		$config      = OSMembershipHelper::getConfig();
		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if ($fieldSuffix && OSMembershipHelper::isValidMessage(
				$message->{'subscription_payment_thanks_message' . $fieldSuffix}
			))
		{
			$thankMessage = $message->{'subscription_payment_thanks_message' . $fieldSuffix};
		}
		else
		{
			$thankMessage = $message->subscription_payment_thanks_message;
		}

		$id = (int) Factory::getApplication()->getSession()->get('mp_subscription_id', 0);

		if (!$id && $subscriptionCode = $this->input->getString('subscription_code'))
		{
			$db    = $this->model->getDbo();
			$query = $db->getQuery(true)
				->select('id')
				->from('#__osmembership_subscribers')
				->where('subscription_code = ' . $db->quote($subscriptionCode));
			$db->setQuery($query);
			$id = (int) $db->loadResult();
		}

		$row = $this->model->getTable('Subscriber');

		if (!$row->load($id))
		{
			echo Text::_('Invalid Subscription Record');

			return;
		}

		$replaces = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$thankMessage = OSMembershipHelper::replaceCaseInsensitiveTags($thankMessage, $replaces);

		$this->message = $thankMessage;

		parent::display();
	}
}
