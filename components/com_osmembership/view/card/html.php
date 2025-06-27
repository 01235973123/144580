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

class OSMembershipViewCardHtml extends MPFViewHtml
{
	/**
	 * The subscription record
	 *
	 * @var stdClass
	 */
	protected $subscription;

	/**
	 * Flag to mark this view does not have an associate model
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * Bootstrap Helper
	 *
	 * @var OSMembershipHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * Contains select lists
	 *
	 * @var array
	 */
	protected $lists;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * The associated payment method
	 *
	 * @var null
	 */
	protected $method = null;

	/**
	 * Display the view
	 *
	 * @throws Exception
	 */
	protected function prepareView()
	{
		// Add necessary javascript files
		OSMembershipHelper::addLangLinkForAjax();

		OSMembershipHelperJquery::loadjQuery();

		$config         = OSMembershipHelper::getConfig();
		$subscriptionId = $this->input->getString('subscription_id');
		$subscription   = OSMembershipHelperSubscription::getSubscription($subscriptionId);

		if (!$subscription)
		{
			throw new Exception(Text::sprintf('Subscription ID %s not found', $subscriptionId));
		}

		if ($subscription->payment_method)
		{
			$this->method = OSMembershipHelper::loadPaymentMethod($subscription->payment_method);
		}

		if ($this->method === null)
		{
			throw new Exception(Text::sprintf('No payment method associated for subscription %s', $subscriptionId));
		}

		// Payment Methods parameters
		$currentYear        = date('Y');
		$expMonth           = $this->input->post->getInt('exp_month', date('n'));
		$expYear            = $this->input->post->getInt('exp_year', $currentYear);
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

		$wa = Factory::getApplication()
			->getDocument()
			->getWebAssetManager()
			->registerAndUseScript(
				'com_osmembership.paymentmethods',
				'media/com_osmembership/assets/js/paymentmethods.min.js'
			);

		$customJSFile = JPATH_ROOT . '/media/com_osmembership/assets/js/custom.js';

		if (file_exists($customJSFile) && filesize($customJSFile) > 0)
		{
			$wa->registerAndUseScript('com_osmembership.custom', 'media/com_osmembership/assets/js/custom.js');
		}

		$this->bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
		$this->lists           = $lists;
		$this->subscription    = $subscription;
		$this->config          = $config;
	}
}
