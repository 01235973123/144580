<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class OSMembershipViewSubscriptionHtml extends MPFViewItem
{
	/**
	 * The date picker format
	 *
	 * @var string
	 */
	protected $datePickerFormat;

	/**
	 * The component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * The form object
	 *
	 * @var MPFForm
	 */
	protected $form;

	/**
	 * Determine whether we can cancel the recurring subscription
	 *
	 * @var bool
	 */
	protected $canCancelSubscription = false;

	/**
	 * Determine whether we can refund the subscription
	 *
	 * @var bool
	 */
	protected $canRefundSubscription = false;

	/**
	 * The selected state
	 *
	 * @var string
	 */
	protected $selectedState = '';

	/**
	 * Prepare view data
	 *
	 * @throws Exception
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$db     = $this->model->getDbo();
		$query  = $db->getQuery(true);
		$item   = $this->item;
		$lists  = &$this->lists;
		$config = OSMembershipHelper::getConfig();

		if ($item->id == 0)
		{
			$item->plan_id = $this->input->getInt('plan_id', 0);
		}

		$query->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		$options          = [];
		$options[]        = HTMLHelper::_('select.option', '', Text::_('OSM_SELECT_PLAN'), 'id', 'title');
		$options          = array_merge($options, $db->loadObjectList());
		$lists['plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'plan_id',
			' class="form-select validate[required]" ',
			'id',
			'title',
			$item->plan_id
		);
		$lists['plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
			$lists['plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_ONE_PLAN')
		);

		//Subscription status
		$options            = [];
		$options[]          = HTMLHelper::_('select.option', -1, Text::_('OSM_ALL'));
		$options[]          = HTMLHelper::_('select.option', 0, Text::_('OSM_PENDING'));
		$options[]          = HTMLHelper::_('select.option', 1, Text::_('OSM_ACTIVE'));
		$options[]          = HTMLHelper::_('select.option', 2, Text::_('OSM_EXPIRED'));
		$options[]          = HTMLHelper::_('select.option', 3, Text::_('OSM_CANCELLED_PENDING'));
		$options[]          = HTMLHelper::_('select.option', 4, Text::_('OSM_CANCELLED_REFUNDED'));
		$lists['published'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'published',
			' class="form-select" ',
			'value',
			'text',
			$item->published
		);

		//Get list of payment methods
		$query->clear()
			->select('name, title')
			->from('#__osmembership_plugins')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		$options                 = [];
		$options[]               = HTMLHelper::_('select.option', '', Text::_('OSM_PAYMENT_METHOD'), 'name', 'title');
		$options                 = array_merge($options, $db->loadObjectList());
		$lists['payment_method'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'payment_method',
			' class="form-select" ',
			'name',
			'title',
			$item->payment_method
		);

		$rowFields = OSMembershipHelper::getProfileFields($item->plan_id, true, $item->language);

		// Disable readonly for adding/editing subscription
		foreach ($rowFields as $rowField)
		{
			$rowField->readonly = 0;
		}

		$data = [];

		if ($item->id)
		{
			$data       = OSMembershipHelper::getProfileData($item, $item->plan_id, $rowFields);
			$setDefault = false;
		}
		else
		{
			$setDefault = true;
		}

		if (!isset($data['country']) || !$data['country'])
		{
			$data['country'] = $config->default_country;
		}

		$form = new MPFForm($rowFields);
		$form->setData($data)->bindData($setDefault);
		$form->buildFieldsDependency();
		$form->handleFieldsDependOnPaymentMethod($item->payment_method);

		$fields = $form->getFields();

		if (isset($fields['state']))
		{
			$this->selectedState = $fields['state']->value;
		}

		//Custom fields processing goes here
		if ($item->plan_id)
		{
			$plan                         = OSMembershipHelperDatabase::getPlan($item->plan_id);
			$item->lifetime_membership    = (int) $plan->lifetime_membership;
			$item->recurring_subscription = (int) $plan->recurring_subscription;
		}
		else
		{
			$item->lifetime_membership = 0;
		}

		// Convert dates from UTC to user timezone
		if ($item->id)
		{
			$item->created_date = HTMLHelper::_('date', $item->created_date, 'Y-m-d H:i:s');
			$item->from_date    = HTMLHelper::_('date', $item->from_date, 'Y-m-d H:i:s');
			$item->to_date      = HTMLHelper::_('date', $item->to_date, 'Y-m-d H:i:s');

			if ((int) $item->payment_date)
			{
				$item->payment_date = HTMLHelper::_('date', $item->payment_date, 'Y-m-d H:i:s');
			}
		}

		OSMembershipHelper::addLangLinkForAjax();

		// Support cancel recurring subscription from backend if the payment gateway support it
		if ($item->id > 0 && $item->payment_method)
		{
			try
			{
				$method = OSMembershipHelper::loadPaymentMethod($item->payment_method);

				if ($item->subscription_id
					&& !$item->recurring_subscription_cancelled
					&& $method && method_exists($method, 'supportCancelRecurringSubscription')
					&& $method->supportCancelRecurringSubscription())
				{
					$this->canCancelSubscription = true;
				}

				if (OSMembershipHelper::canRefundSubscription($item))
				{
					$this->canRefundSubscription = true;
				}
			}
			catch (Exception $e)
			{
				// Payment method doesn't exist for some reasons, ignore it
			}
		}

		$this->datePickerFormat = $config->get('date_field_format', '%Y-%m-%d');
		$this->config           = $config;
		$this->form             = $form;
	}
}
