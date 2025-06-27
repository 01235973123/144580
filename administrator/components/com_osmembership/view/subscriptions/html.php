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
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\Utilities\ArrayHelper;

class OSMembershipViewSubscriptionsHtml extends MPFViewList
{
	/**
	 * Subscriptions custom field data
	 *
	 * @var array
	 */
	protected $fieldsData;

	/**
	 * The date picker format
	 *
	 * @var string
	 */
	protected $datePickerFormat;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Custom fields
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * The filter dropdowns
	 *
	 * @var array<string, mixed>
	 */
	protected $filters;

	/**
	 * The flag to mark if we should show last name column
	 * @var bool
	 */
	protected $showLastName;

	/**
	 * The export templates
	 *
	 * @var array
	 */
	protected $exportTemplates = [];

	/**
	 * The payment plugin
	 *
	 * @var array
	 */
	protected $paymentPlugins = [];

	/**
	 * Prepare view data
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$db    = $this->model->getDbo();
		$query = $db->getQuery(true)
			->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('ordering');

		if ($this->state->filter_category_id > 0)
		{
			$query->where('category_id = ' . $this->state->filter_category_id);
		}

		$db->setQuery($query);

		$options                = [];
		$options[]              = HTMLHelper::_('select.option', 0, Text::_('OSM_SELECT_PLAN'), 'id', 'title');
		$options                = array_merge($options, $db->loadObjectList());
		$this->lists['plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'plan_id',
			'class="form-select" onchange="submit();" ',
			'id',
			'title',
			$this->state->plan_id
		);
		$this->lists['plan_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
			$this->lists['plan_id'],
			Text::_('OSM_TYPE_OR_SELECT_ONE_PLAN')
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'H', Text::_('OSM_HOURS'));
		$options[] = HTMLHelper::_('select.option', 'D', Text::_('OSM_DAYS'));
		$options[] = HTMLHelper::_('select.option', 'W', Text::_('OSM_WEEKS'));
		$options[] = HTMLHelper::_('select.option', 'M', Text::_('OSM_MONTHS'));
		$options[] = HTMLHelper::_('select.option', 'Y', Text::_('OSM_YEARS'));

		$this->lists['extend_subscription_duration_unit'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'extend_subscription_duration_unit',
			'class="form-select input-medium d-inline-block"',
			'value',
			'text',
			'D'
		);

		$query->clear()
			->select('id, title')
			->from('#__osmembership_categories')
			->where('published = 1')
			->order('title');
		$db->setQuery($query);

		$categories = $db->loadObjectList();

		if (count($categories) > 0)
		{
			$options                           = [];
			$options[]                         = HTMLHelper::_(
				'select.option',
				0,
				Text::_('OSM_SELECT_CATEGORY'),
				'id',
				'title'
			);
			$options                           = array_merge($options, $categories);
			$this->lists['filter_category_id'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'filter_category_id',
				'class="form-select" onchange="submit();" ',
				'id',
				'title',
				$this->state->filter_category_id
			);
			$this->lists['filter_category_id'] = OSMembershipHelperHtml::getChoicesJsSelect(
				$this->lists['filter_category_id'],
				Text::_('OSM_TYPE_OR_SELECT_ONE_CATEGORY')
			);
		}

		$options                          = [];
		$options[]                        = HTMLHelper::_('select.option', 0, Text::_('OSM_ALL_SUBSCRIPTIONS'));
		$options[]                        = HTMLHelper::_('select.option', 1, Text::_('OSM_NEW_SUBSCRIPTION'));
		$options[]                        = HTMLHelper::_('select.option', 2, Text::_('OSM_SUBSCRIPTION_RENEWAL'));
		$options[]                        = HTMLHelper::_('select.option', 3, Text::_('OSM_SUBSCRIPTION_UPGRADE'));
		$this->lists['subscription_type'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'subscription_type',
			' class="form-select input-medium" onchange="submit();" ',
			'value',
			'text',
			$this->state->subscription_type
		);

		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', -1, Text::_('OSM_ALL'));
		$options[]                = HTMLHelper::_('select.option', 0, Text::_('OSM_PENDING'));
		$options[]                = HTMLHelper::_('select.option', 1, Text::_('OSM_ACTIVE'));
		$options[]                = HTMLHelper::_('select.option', 2, Text::_('OSM_EXPIRED'));
		$options[]                = HTMLHelper::_('select.option', 3, Text::_('OSM_CANCELLED_PENDING'));
		$options[]                = HTMLHelper::_('select.option', 4, Text::_('OSM_CANCELLED_REFUNDED'));
		$this->lists['published'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'published',
			' class="form-select input-medium" onchange="submit();" ',
			'value',
			'text',
			$this->state->published
		);

		$options                          = [];
		$options[]                        = HTMLHelper::_(
			'select.option',
			'tbl.created_date',
			Text::_('OSM_CREATED_DATE')
		);
		$options[]                        = HTMLHelper::_('select.option', 'tbl.from_date', Text::_('OSM_START_DATE'));
		$options[]                        = HTMLHelper::_('select.option', 'tbl.to_date', Text::_('OSM_END_DATE'));
		$this->lists['filter_date_field'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_date_field',
			' class="form-select input-medium" ',
			'value',
			'text',
			$this->state->filter_date_field
		);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_DURATION'));
		$options[] = HTMLHelper::_('select.option', 'today', Text::_('OSM_TODAY'));
		$options[] = HTMLHelper::_('select.option', 'yesterday', Text::_('OSM_YESTERDAY'));
		$options[] = HTMLHelper::_('select.option', 'this_week', Text::_('OSM_THIS_WEEK'));
		$options[] = HTMLHelper::_('select.option', 'last_week', Text::_('OSM_LAST_WEEK'));
		$options[] = HTMLHelper::_('select.option', 'this_month', Text::_('OSM_THIS_MONTH'));
		$options[] = HTMLHelper::_('select.option', 'last_month', Text::_('OSM_LAST_MONTH'));
		$options[] = HTMLHelper::_('select.option', 'this_year', Text::_('OSM_THIS_YEAR'));
		$options[] = HTMLHelper::_('select.option', 'last_year', Text::_('OSM_LAST_YEAR'));
		$options[] = HTMLHelper::_('select.option', 'last_7_days', Text::_('OSM_LAST_7_DAYS'));
		$options[] = HTMLHelper::_('select.option', 'last_30_days', Text::_('OSM_LAST_30_DAYS'));

		$this->lists['filter_subscription_duration'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'filter_subscription_duration',
			' class="form-select input-medium" onchange="submit()" ',
			'value',
			'text',
			$this->state->filter_subscription_duration
		);

		$rowFields = OSMembershipHelper::getCustomFieldsForPlans($this->state->plan_id);

		$fields       = [];
		$filters      = [];
		$showLastName = false;

		$filterFieldsValues = $this->state->get('filter_fields', []);

		foreach ($rowFields as $rowField)
		{
			if ($rowField->name == 'last_name')
			{
				$showLastName = true;
			}

			if ($rowField->filterable)
			{
				if ($rowField->name == 'country')
				{
					$query->clear()
						->select('DISTINCT country')
						->from('#__osmembership_subscribers')
						->where('LENGTH(country) > 0');
					$db->setQuery($query);
					$fieldOptions = $db->loadColumn();
				}
				elseif ($rowField->name == 'state')
				{
					$query->clear()
						->select('DISTINCT state')
						->from('#__osmembership_subscribers')
						->where('LENGTH(state) > 0');
					$db->setQuery($query);
					$fieldOptions = $db->loadColumn();
				}
				else
				{
					$fieldOptions = explode("\r\n", $rowField->values);
				}

				$options   = [];
				$options[] = HTMLHelper::_('select.option', '', $rowField->title);

				foreach ($fieldOptions as $option)
				{
					$options[] = HTMLHelper::_('select.option', $option, $option);
				}

				$filters['field_' . $rowField->id] = HTMLHelper::_(
					'select.genericlist',
					$options,
					'filter_fields[field_' . $rowField->id . ']',
					' class="form-select input-medium" onchange="submit();" ',
					'value',
					'text',
					ArrayHelper::getValue($filterFieldsValues, 'field_' . $rowField->id)
				);
			}

			if ($rowField->show_on_subscriptions != 1 || in_array($rowField->name, ['first_name', 'last_name']))
			{
				continue;
			}

			$fields[$rowField->id] = $rowField;
		}

		if (count($fields))
		{
			$this->fieldsData = $this->model->getFieldsData(array_keys($fields));
		}

		// We do not need to call model to get data because this was done in Add toolbar process already
		if (count($this->exportTemplates))
		{
			$options   = [];
			$options[] = HTMLHelper::_('select.option', 0, Text::_('OSM_DEFAULT'), 'id', 'title');
			$options   = array_merge($options, $this->exportTemplates);

			$this->lists['export_template'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'export_template',
				'class="form-select"',
				'id',
				'title',
				0
			);
		}

		$query->clear()
			->select('*')
			->from('#__osmembership_mmtemplates')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (count($rows))
		{
			$options   = [];
			$options[] = HTMLHelper::_('select.option', '0', Text::_('OSM_SELECT'), 'id', 'title');
			$options   = array_merge($options, $rows);

			$this->lists['mm_template_id'] = HTMLHelper::_(
				'select.genericlist',
				$options,
				'mm_template_id',
				'class="form-select"',
				'id',
				'title',
				0
			);
		}

		$query->clear()
			->select('name, title')
			->from('#__osmembership_plugins');
		$db->setQuery($query);
		$this->paymentPlugins = $db->loadObjectList('name');

		$config                 = OSMembershipHelper::getConfig();
		$this->datePickerFormat = $config->get('date_field_format', '%Y-%m-%d');
		$this->config           = $config;
		$this->fields           = $fields;
		$this->filters          = $filters;
		$this->showLastName     = $showLastName;
	}

	/**
	 * Custom Toolbar buttons
	 *
	 * @return void
	 */
	protected function addCustomToolbarButtons()
	{
		$this->exportTemplates = $this->model->getExportTemplates();

		$config  = OSMembershipHelper::getConfig();
		$toolbar = Toolbar::getInstance('toolbar');

		/* @var DropdownButton $dropdown */
		$actionDropdown = $toolbar->dropdownButton('actions-group', 'JTOOLBAR_CHANGE_STATUS')
			->toggleSplit(false)
			->icon('icon-ellipsis-h')
			->buttonClass('btn btn-action');

		/* @var Toolbar $childBar */
		$childBar = $actionDropdown->getChildToolbar();

		$childBar->standardButton('renew', 'OSM_RENEW_SUBSCRIPTION', 'renew')
			->icon('icon-plus')
			->listCheck(true);

		$childBar->standardButton('request_payment', 'OSM_REQUEST_PAYMENT', 'request_payment')
			->icon('icon-mail')
			->listCheck(true);

		$childBar->standardButton('resend_email', 'OSM_RESEND_EMAIL', 'resend_email')
			->icon('icon-mail')
			->listCheck(true);

		$childBar->standardButton('enable_reminders', 'OSM_ENABLE_REMINDERS', 'enable_reminders')
			->icon('icon-check')
			->listCheck(true);

		$childBar->standardButton('disable_reminders', 'OSM_DISABLE_REMINDERS', 'disable_reminders')
			->icon('icon-delete')
			->listCheck(true);

		// Batch subscriptions
		$childBar->popupButton('batch')
			->text('OSM_BATCH_SUBSCRIPTIONS')
			->selector('collapseModal_Subscriptions')
			->listCheck(true);

		// Mass Mail
		$layout = new FileLayout('joomla.toolbar.batch');
		$dhtml  = $layout->render(['title' => Text::_('OSM_MASS_MAIL')]);
		$childBar->customHtml($dhtml, 'mass_mail')
			->listCheck(true);

		// Batch SMS
		if (PluginHelper::isEnabled('system', 'membershippro'))
		{
			$childBar->popupButton('batch_sms', 'OSM_BATCH_SMS')
				->selector('collapseModal_Sms')
				->listCheck(true);
		}

		/* @var DropdownButton $dropdown */
		$dropdown = $toolbar->dropdownButton('status-group')
			->text('OSM_EXPORT')
			->toggleSplit(false)
			->icon('icon-ellipsis-h')
			->buttonClass('btn btn-action');

		$childBar = $dropdown->getChildToolbar();

		if (count($this->exportTemplates))
		{
			$childBar->popupButton('batch')
				->text('OSM_EXPORT_EXCEL')
				->selector('collapseModal_Export_Template');
		}
		else
		{
			$childBar->standardButton('export')
				->text('OSM_EXPORT_EXCEL')
				->icon('icon-download')
				->task('export');
		}

		$childBar->standardButton('export_pdf')
			->text('OSM_EXPORT_PDF')
			->icon('icon-download')
			->task('export_pdf');

		if ($config->activate_invoice_feature)
		{
			$childBar->standardButton('export_invoices')
				->text('OSM_EXPORT_INVOICES')
				->icon('icon-download')
				->task('export_invoices');
		}
	}
}
