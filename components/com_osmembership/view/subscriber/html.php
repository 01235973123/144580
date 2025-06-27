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
use Joomla\Utilities\ArrayHelper;

/**
 * HTML View class for Membership Pro component
 *
 * @static
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipViewSubscriberHtml extends MPFViewItem
{
	/**
	 * The buttons which will be hidden
	 *
	 * @var array
	 */
	protected $hideButtons = ['save2new', 'save2copy'];

	/**
	 * The subscription form object
	 *
	 * @var MPFForm
	 */
	protected $form;

	/**
	 * The component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * The format used for date picker
	 *
	 * @var string
	 */
	protected $datePickerFormat;

	/**
	 * Name of views which could be used to get menu item parameters for the current view
	 *
	 * @var array
	 */
	protected $paramsViews = ['subscribers'];

	/**
	 * Store the selected state
	 *
	 * @var string
	 */
	protected $selectedState = '';

	/**
	 * Prepare view data before displaying
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$user        = Factory::getApplication()->getIdentity();
		$db          = $this->model->getDbo();
		$query       = $db->getQuery(true);
		$item        = $this->item;
		$lists       = &$this->lists;
		$config      = OSMembershipHelper::getConfig();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if (!$item->id)
		{
			$item->plan_id = $this->input->getInt('plan_id', 0);
		}

		$query->select('id')
			->select($db->quoteName('title' . $fieldSuffix, 'title'))
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('ordering');

		// If user does not have super-user permisson, only allow them to manage subscriptions from the plans which they created
		if (!$user->authorise('core.admin', 'com_osmembership'))
		{
			$query->where('subscriptions_manage_user_id IN (0, ' . $user->id . ')');
		}

		if ($this->params->get('plan_ids'))
		{
			$query->whereIn('id', ArrayHelper::toInteger($this->params->get('plan_ids')));
		}

		if ($this->params->get('exclude_plan_ids'))
		{
			$query->whereNotIn('id', ArrayHelper::toInteger($this->params->get('exclude_plan_ids')));
		}

		$db->setQuery($query);
		$options          = [];
		$options[]        = HTMLHelper::_('select.option', '', Text::_('OSM_SELECT_PLAN'), 'id', 'title');
		$options          = array_merge($options, $db->loadObjectList());
		$lists['plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'plan_id',
			'class="form-select validate[required]"',
			'id',
			'title',
			$item->plan_id
		);

		//Subscription status
		$options            = [];
		$options[]          = HTMLHelper::_('select.option', 0, Text::_('OSM_PENDING'));
		$options[]          = HTMLHelper::_('select.option', 1, Text::_('OSM_ACTIVE'));
		$options[]          = HTMLHelper::_('select.option', 2, Text::_('OSM_EXPIRED'));
		$options[]          = HTMLHelper::_('select.option', 3, Text::_('OSM_CANCELLED_PENDING'));
		$options[]          = HTMLHelper::_('select.option', 4, Text::_('OSM_CANCELLED_REFUNDED'));
		$lists['published'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'published',
			'class="form-select"',
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
			'class="form-select"',
			'name',
			'title',
			$item->payment_method
		);

		if ($config->get('enable_select_show_hide_members_list'))
		{
			$options   = [];
			$options[] = HTMLHelper::_('select.option', 1, Text::_('JYES'));
			$options[] = HTMLHelper::_('select.option', 0, Text::_('JNO'));

			$lists['show_on_members_list'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'show_on_members_list',
				'',
				'value',
				'text',
				$item->show_on_members_list
			);
		}

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
			$query->clear()
				->select('lifetime_membership')
				->from('#__osmembership_plans')
				->where('id = ' . (int) $item->plan_id);
			$db->setQuery($query);
			$item->lifetime_membership = (int) $db->loadResult();
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
		}

		OSMembershipHelper::addLangLinkForAjax($item->language);

		$this->config           = $config;
		$this->form             = $form;
		$this->datePickerFormat = $config->get('date_field_format', '%Y-%m-%d');

		$this->addToolbar();
	}

	/**
	 * Add Toolbar
	 */
	protected function addToolbar()
	{
		parent::addToolbar();
	}
}
