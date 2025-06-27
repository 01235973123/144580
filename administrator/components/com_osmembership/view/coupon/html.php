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

class OSMembershipViewCouponHtml extends MPFViewItem
{
	/**
	 * Subscription records use the coupon
	 *
	 * @var array
	 */
	protected $subscriptions;

	/**
	 * The date picker format
	 *
	 * @var string
	 */
	protected $datePickerFormat;

	/**
	 * The database null data
	 *
	 * @var string
	 */
	protected $nullDate;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	protected function prepareView()
	{
		parent::prepareView();

		$config = OSMembershipHelper::getConfig();

		$db    = $this->model->getDbo();
		$query = $db->getQuery(true);

		// Assignment
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_ALL_PLANS'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('OSM_ALL_SELECTED_PLANS'));
		$options[] = HTMLHelper::_('select.option', -1, Text::_('OSM_ALL_EXCEPT_SELECTED_PLANS'));

		$this->lists['assignment'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'assignment',
			'class="form-select"',
			'value',
			'text',
			$this->item->assignment
		);

		if ($this->item->id)
		{
			$query->select('plan_id')
				->from('#__osmembership_coupon_plans')
				->where('coupon_id = ' . $this->item->id);
			$db->setQuery($query);
			$planIds = array_map('abs', $db->loadColumn());
		}
		else
		{
			$planIds = [];
		}

		$query->clear()
			->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		$this->lists['plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$db->loadObjectList(),
			'plan_id[]',
			' class="chosen" multiple="multiple" ',
			'id',
			'title',
			$planIds
		);

		$options                    = [];
		$options[]                  = HTMLHelper::_('select.option', 0, Text::_('%'));
		$options[]                  = HTMLHelper::_('select.option', 1, $config->currency_symbol);
		$this->lists['coupon_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'coupon_type',
			' class="form-select input-small d-inline-block" ',
			'value',
			'text',
			$this->item->coupon_type
		);

		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', 0, Text::_('OSM_ALL_PAYMENTS'));
		$options[]                = HTMLHelper::_('select.option', 1, Text::_('OSM_ONLY_FIRST_PAYMENT'));
		$this->lists['apply_for'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'apply_for',
			'class="form-select"',
			'value',
			'text',
			$this->item->apply_for
		);

		$options                          = [];
		$options[]                        = HTMLHelper::_('select.option', '', Text::_('All'));
		$options[]                        = HTMLHelper::_(
			'select.option',
			'subscribe',
			Text::_('OSM_NEW_SUBSCRIPTION')
		);
		$options[]                        = HTMLHelper::_(
			'select.option',
			'renew',
			Text::_('OSM_SUBSCRIPTION_RENEWAL')
		);
		$options[]                        = HTMLHelper::_(
			'select.option',
			'upgrade',
			Text::_('OSM_SUBSCRIPTION_UPGRADE')
		);
		$this->lists['subscription_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'subscription_type',
			'class="form-select"',
			'value',
			'text',
			$this->item->subscription_type
		);

		$this->subscriptions = $this->model->getSubscriptions();

		$this->datePickerFormat = $config->get('date_field_format', '%Y-%m-%d');
		$this->nullDate         = $db->getNullDate();
		$this->config           = $config;

		$dateFields = ['valid_from', 'valid_to'];

		foreach ($dateFields as $dateField)
		{
			if (!(int) $this->item->{$dateField})
			{
				$this->item->{$dateField} = '';
			}
		}

		$this->lists['plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
			$this->lists['plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_SOME_PLANS')
		);
	}

	/**
	 * Override addToolbar method, only add toolbar for default layout
	 */
	protected function addToolbar()
	{
		if ($this->getLayout() == 'default')
		{
			parent::addToolbar();
		}
	}
}
