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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use OSSolution\MembershipPro\Admin\Event\Export\BeforeXLSXExport;
use OSSolution\MembershipPro\Admin\Event\Subscriptions\AfterImportSubscriptions;
use OSSolution\MembershipPro\Admin\Event\Subscriptions\BeforeExportSubscriptions;
use OSSolution\MembershipPro\Admin\Event\Subscriptions\SubscriptionsExport;

/**
 * OSMembership Plugin controller
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipControllerSubscription extends OSMembershipController
{
	use MPFControllerDownload;

	/**
	 * Cancel recurring subscription
	 *
	 * @throws Exception
	 */
	public function cancel_subscription()
	{
		$id = $this->input->post->getInt('id', 0);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_subscribers')
			->where('id = ' . $id);
		$db->setQuery($query);
		$rowSubscription = $db->loadObject();

		if ($rowSubscription && OSMembershipHelper::canCancelSubscription($rowSubscription))
		{
			JLoader::register(
				'OSMembershipModelRegister',
				JPATH_ROOT . '/components/com_osmembership/model/register.php'
			);

			/**@var OSMembershipModelRegister $model * */
			$model = $this->getModel('Register');
			$ret   = $model->cancelSubscription($rowSubscription);

			if ($ret)
			{
				$this->setRedirect(
					'index.php?option=com_osmembership&view=subscription&id=' . $rowSubscription->id,
					Text::_('OSM_SUBSCRIPTION_CANCELLED')
				);
			}
			else
			{
				// Redirect back to profile page, the payment plugin should enqueue the reason of failed cancellation so that it could be displayed to end user
				$this->setRedirect('index.php?option=com_osmembership&view=subscription&id=' . $rowSubscription->id);
			}
		}
		else
		{
			throw new InvalidArgumentException(Text::_('OSM_INVALID_RECURRING_SUBSCRIPTION'), 404);
		}
	}

	/**
	 * Resend confirmation email to registrants in case they didn't receive it
	 */
	public function resend_email()
	{
		$cid = $this->input->get('cid', [], 'array');
		$cid = ArrayHelper::toInteger($cid);

		/* @var OSMembershipModelSubscription $model */
		$model = $this->getModel();

		foreach ($cid as $id)
		{
			$model->resendEmail($id);
		}

		$this->setRedirect($this->getViewListUrl(), Text::_('OSM_EMAIL_SUCCESSFULLY_RESENT'));
	}

	/**
	 * Send batch mail to subscriptions
	 */
	public function batch_mail()
	{
		if ($this->app->isClient('site'))
		{
			$this->csrfProtection();
		}

		$this->checkAccessPermission('subscriptions');

		/* @var OSMembershipModelSubscription $model */
		$model = $this->getModel();

		try
		{
			$model->batchMail($this->input);
			$this->setMessage(Text::_('OSM_BATCH_MAIL_SUCCESS'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Send batch mail to subscriptions
	 */
	public function batch_subscriptions()
	{
		if ($this->app->isClient('site'))
		{
			$this->csrfProtection();
		}

		$this->checkAccessPermission('subscriptions');

		/* @var OSMembershipModelSubscription $model */
		$model = $this->getModel();

		try
		{
			$model->batchSubscriptions($this->input);
			$this->setMessage(Text::_('OSM_BATCH_SUBSCRIPTIONS_SUCCESS'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Send batch SMS to selected subscriptions
	 *
	 * @return void
	 */
	public function batch_sms()
	{
		$cid     = $this->input->get('cid', [], 'array');
		$cid     = ArrayHelper::toInteger($cid);
		$message = $this->getInput()->getString('sms_message');

		/* @var OSMembershipModelSubscription $model */
		$model = $this->getModel();

		try
		{
			$model->batchSMS($cid, $message);

			$this->setMessage(Text::_('OSM_BATCH_SMS_SUCCESS'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Renew subscription for given user
	 */
	public function renew()
	{
		if ($this->app->isClient('site'))
		{
			$this->csrfProtection();
		}

		$this->checkAccessPermission('subscriptions');

		$cid = $this->input->get('cid', [], 'array');
		$cid = ArrayHelper::toInteger($cid);

		/* @var OSMembershipModelSubscription $model */
		$model = $this->getModel('subscription');

		foreach ($cid as $id)
		{
			$model->renew($id);
		}

		$this->setRedirect($this->getViewListUrl(), Text::_('OSM_SUBSCRIPTIONS_RENEWED'));
	}

	/**
	 * Import Subscribers from CSV
	 */
	public function import()
	{
		if ($this->app->isClient('site'))
		{
			throw new Exception('You are not allowed to perform this action', 403);
		}

		$this->checkAccessPermission('subscriptions');

		$inputFile = $this->input->files->get('input_file');
		$fileName  = $inputFile ['name'];
		$fileExt   = strtolower(OSMembershipHelper::getFileExt($fileName));

		if (!in_array($fileExt, ['csv', 'xls', 'xlsx']))
		{
			$this->setRedirect(
				'index.php?option=com_osmembership&view=import',
				Text::_('Invalid File Type. Only CSV, XLS and XLS file types are supported')
			);

			return;
		}

		/* @var OSMembershipModelImport $model */
		$model = $this->getModel('import');

		try
		{
			$ids = $model->store($inputFile['tmp_name'], $inputFile['name']);

			// Backward compatible
			if (is_int($ids))
			{
				$numberSubscribers = $ids;
			}
			else
			{
				$numberSubscribers = count($ids);
			}

			$event = new AfterImportSubscriptions([
				'ids' => $ids,
			]);

			$this->getApplication()->triggerEvent($event->getName(), $event);

			$this->setRedirect(
				$this->getViewListUrl(),
				Text::sprintf('OSM_NUMBER_SUBSCRIBERS_IMPORTED', $numberSubscribers)
			);
		}
		catch (Exception $e)
		{
			$this->setRedirect('index.php?option=com_osmembership&view=import');
			$this->setMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Import Subscribers from Joomla cores
	 */
	public function import_from_joomla()
	{
		if ($this->app->isClient('site'))
		{
			throw new Exception('You are not allowed to perform this action', 403);
		}

		$planId = $this->input->getInt('to_plan_id', 0);
		$start  = $this->input->getInt('start', 0);
		$limit  = $this->input->getInt('limit', 0);

		if (empty($planId))
		{
			throw new Exception('Plan not found', 404);
		}

		/* @var OSMembershipModelImport $model */
		$model = $this->getModel('import');

		try
		{
			$numberSubscribers = $model->importFromJoomla($planId, $start, $limit);
			$this->setRedirect(
				$this->getViewListUrl(),
				Text::sprintf('OSM_NUMBER_SUBSCRIBERS_IMPORTED', $numberSubscribers)
			);
		}
		catch (Exception $e)
		{
			$this->setRedirect('index.php?option=com_osmembership&view=import');
			$this->setMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Export subscriptions into a CSV file
	 */
	public function export()
	{
		$this->checkAccessPermission('subscriptions');

		set_time_limit(0);

		$config = OSMembershipHelper::getConfig();

		/* @var OSMembershipModelSubscriptions $model */

		if ($this->app->isClient('site'))
		{
			$model = $this->getModel('subscribers');
			$model->setParams($this->app->getParams());
		}
		else
		{
			$model = $this->getModel('subscriptions');
		}

		$model->set('limitstart', 0)
			->set('limit', 0);

		if ($config->include_group_members_in_export)
		{
			$model->setIncludeGroupMembers(true);
		}

		if ($config->export_exclude_status)
		{
			$model->setExcludeStatus(explode(',', $config->export_exclude_status));
		}

		$cid = $this->input->getString('cid', '');

		// This is needed in case there are filter fields on subscriptions management
		if (is_string($cid))
		{
			$cid = explode(',', $cid);
		}

		if (count($cid))
		{
			$model->set('filter_subscription_ids', $cid);
		}

		// Give a chance for plugin to handle export subscriptions itself
		PluginHelper::importPlugin('osmembership');

		$event = new BeforeExportSubscriptions(['model' => $model]);

		$results = $this->getApplication()->triggerEvent($event->getName(), $event);

		if (count($results) && $filename = $results[0])
		{
			// There is a plugin handles export, it returns the filename, so we just process download the file
			$this->processDownloadFile($filename);

			return;
		}

		// OK, no plugin handles export, we do the work ourself
		$rows = $model->getData();

		$numberSubscriptions = count($rows);

		if ($numberSubscriptions == 0)
		{
			$this->setMessage(Text::_('There are no subscription records to export'));
			$this->setRedirect($this->getViewListUrl());

			return;
		}

		$planId = (int) $model->get('plan_id');

		$event = new SubscriptionsExport([
			'planId'              => $planId,
			'numberSubscriptions' => $numberSubscriptions,
		]);

		$this->getApplication()->triggerEvent($event->getName(), $event);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('id, name, is_core')
			->from('#__osmembership_fields')
			->where('published = 1')
			->where('hide_on_export = 0')
			->order('ordering');

		if ($planId > 0)
		{
			$negPlanId = -1 * $planId;
			$query->where(
				'(plan_id = 0 OR id IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $planId . ' OR (plan_id < 0 AND plan_id != ' . $negPlanId . ')))'
			);
		}

		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		$fieldIds = [];
		foreach ($rowFields as $rowField)
		{
			if ($rowField->is_core)
			{
				continue;
			}

			$fieldIds[] = $rowField->id;
		}

		$fieldValues = $model->getFieldsData($fieldIds);

		$exportFields = [
			'id',
			'category',
			'plan',
			'user_id',
			'username',
		];

		$fields = [];

		foreach ($exportFields as $exportField)
		{
			if ($config->get('export_' . $exportField, 1))
			{
				$fields[] = $exportField;
			}
		}

		$i = 0;

		foreach ($rowFields as $rowField)
		{
			$fields[] = $rowField->name;

			if ($rowField->is_core)
			{
				unset($rowFields[$i]);
			}

			$i++;
		}

		$exportFields = [
			'created_date',
			'payment_date',
			'from_date',
			'to_date',
			'published',
			'amount',
			'tax_amount',
			'discount_amount',
			'gross_amount',
			'payment_method',
			'transaction_id',
			'membership_id',
			'subscription_type',
		];

		foreach ($exportFields as $exportField)
		{
			if ($config->get('export_' . $exportField, 1))
			{
				$fields[] = $exportField;
			}
		}

		if ($config->export_subscribe_to_newsletter)
		{
			$fields[] = 'subscribe_newsletter';
		}

		if ($config->activate_invoice_feature && $config->get('export_invoice_number', 1))
		{
			$fields[] = 'invoice_number';
		}

		if ($config->enable_coupon && $config->get('export_coupon', 1))
		{
			$fields[] = 'coupon_code';
		}

		$this->prepareExportData($rows, $rowFields, $fieldValues, $fields);

		if ($exportTemplateId = $this->input->getInt('export_template', 0))
		{
			$exportTemplate = $model->getExportTemplate($exportTemplateId);

			if ($exportTemplate->fields)
			{
				$templateFields = json_decode($exportTemplate->fields, true);
				$newFields      = [];

				foreach ($templateFields as $templateField)
				{
					if (in_array($templateField, $fields))
					{
						$newFields[] = $templateField;
					}
				}

				$fields = $newFields;
			}
		}

		$filePath = OSMembershipHelper::callOverridableHelperMethod(
			'Data',
			'excelExport',
			[$fields, $rows, 'subscriptions_list']
		);

		if ($filePath)
		{
			$this->processDownloadFile($filePath);
		}
	}

	/**
	 * Export registrants into a PDF file
	 */
	public function export_pdf()
	{
		if ($this->app->isClient('site'))
		{
			$this->csrfProtection();
		}

		$this->checkAccessPermission('subscriptions');

		set_time_limit(0);

		$config = OSMembershipHelper::getConfig();

		/* @var OSMembershipModelSubscriptions $model */
		if ($this->app->isClient('site'))
		{
			$model = $this->getModel('subscribers');

			// Set menu item params
			$model->setParams($this->app->getParams());
		}
		else
		{
			$model = $this->getModel('subscriptions');
		}

		$model->set('limitstart', 0)
			->set('limit', 0);

		if ($config->include_group_members_in_export)
		{
			$model->setIncludeGroupMembers(true);
		}

		if ($config->export_exclude_status)
		{
			$model->setExcludeStatus(explode(',', $config->export_exclude_status));
		}

		$cid = $this->input->get('cid', [], 'array');

		if (count($cid))
		{
			$model->set('filter_subscription_ids', $cid);
		}

		$rows = $model->getData();

		$numberSubscriptions = count($rows);

		if ($numberSubscriptions == 0)
		{
			$this->setMessage(Text::_('There are no subscription records to export'));
			$this->setRedirect($this->getViewListUrl());

			return;
		}

		$planId = (int) $model->get('plan_id');

		$event = new SubscriptionsExport([
			'planId'              => $planId,
			'numberSubscriptions' => $numberSubscriptions,
		]);

		$this->getApplication()->triggerEvent($event->getName(), $event);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('id, name, is_core')
			->from('#__osmembership_fields')
			->where('published = 1')
			->where('hide_on_export = 0')
			->order('ordering');

		if ($planId > 0)
		{
			$negPlanId = -1 * $planId;
			$query->where(
				'(plan_id = 0 OR id IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $planId . ' OR (plan_id < 0 AND plan_id != ' . $negPlanId . ')))'
			);
		}

		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		$fieldIds = [];

		foreach ($rowFields as $rowField)
		{
			if ($rowField->is_core)
			{
				continue;
			}

			$fieldIds[] = $rowField->id;
		}

		$fieldValues = $model->getFieldsData($fieldIds);

		$fields = [
			'id',
			'plan',
			'user_id',
			'username',
		];

		$i = 0;

		foreach ($rowFields as $rowField)
		{
			$fields[] = $rowField->name;

			if ($rowField->is_core)
			{
				unset($rowFields[$i]);
			}

			$i++;
		}

		$fields = array_merge($fields, [
			'created_date',
			'payment_date',
			'from_date',
			'to_date',
			'published',
			'amount',
			'tax_amount',
			'discount_amount',
			'gross_amount',
			'payment_method',
			'transaction_id',
			'membership_id',
		]);

		if ($config->activate_invoice_feature)
		{
			$fields[] = 'invoice_number';
		}

		if ($config->enable_coupon)
		{
			$fields[] = 'coupon_code';
		}

		$this->prepareExportData($rows, $rowFields, $fieldValues, $fields);

		$filePath = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'generateSubscriptionsPDF',
			[$rows, $fields]
		);

		$this->processDownloadFile($filePath);
	}

	/**
	 * Export PDF invoices
	 */
	public function export_invoices()
	{
		ini_set('memory_limit', '-1');
		set_time_limit(0);

		if ($this->app->isClient('site'))
		{
			$this->csrfProtection();
		}

		$this->checkAccessPermission('subscriptions');

		set_time_limit(0);

		$config = OSMembershipHelper::getConfig();

		/* @var OSMembershipModelSubscriptions $model */

		if ($this->app->isClient('site'))
		{
			$model = $this->getModel('subscribers');
		}
		else
		{
			$model = $this->getModel('subscriptions');
		}

		$model->set('limitstart', 0)
			->set('limit', 0);

		if ($config->include_group_members_in_export)
		{
			$model->setIncludeGroupMembers(true);
		}

		if ($config->export_exclude_status)
		{
			$model->setExcludeStatus(explode(',', $config->export_exclude_status));
		}

		$cid = $this->input->get('cid', [], 'array');

		if (count($cid))
		{
			$model->set('filter_subscription_ids', $cid);
		}

		// Only export subscriptions with real invoices
		$model->getQuery()->where('tbl.invoice_number > 0');

		$rows = $model->getData();

		$numberSubscriptions = count($rows);

		if ($numberSubscriptions == 0)
		{
			$this->setMessage(Text::_('There are no subscription records to export'));
			$this->setRedirect($this->getViewListUrl());

			return;
		}

		$filePath = OSMembershipHelper::callOverridableHelperMethod('Helper', 'generateSubscriptionsInvoices', [$rows]);

		$this->processDownloadFile($filePath);
	}

	/**
	 * Method to export expired subscribers in the whole system
	 *
	 * @return void
	 */
	public function export_expired_subscribers()
	{
		$this->checkAccessPermission('subscriptions');

		set_time_limit(0);

		/* @var OSMembershipModelSubscriptions $model */
		$model = $this->getModel('subscriptions');

		$rows = $model->getExpiredSubscribers();

		if (count($rows) == 0)
		{
			$this->setMessage(Text::_('There are no expired subscribers to export'));
			$this->setRedirect($this->getViewListUrl());

			return;
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('id, name, is_core')
			->from('#__osmembership_fields')
			->where('published = 1')
			->where('plan_id = 0')
			->where('hide_on_export = 0')
			->order('ordering');
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		$fieldIds = [];

		foreach ($rowFields as $rowField)
		{
			if ($rowField->is_core)
			{
				continue;
			}

			$fieldIds[] = $rowField->id;
		}

		$userIds = [];

		foreach ($rows as $row)
		{
			$userIds[] = $row->user_id;
		}

		// Get latest expired plan
		$query->clear()
			->select('a.title, b.user_id, b.to_date')
			->from('#__osmembership_plans AS a')
			->innerJoin('#__osmembership_subscribers AS b On a.id = b.plan_id')
			->whereIn('b.user_id', $userIds)
			->where('b.published = 2')
			->order('b.to_date');
		$db->setQuery($query);
		$userPlans = $db->loadObjectList('user_id');

		$fieldValues = $model->getFieldsData($fieldIds);

		$fields = [
			'username',
			'plan',
		];

		$i = 0;

		foreach ($rowFields as $rowField)
		{
			$fields[] = $rowField->name;

			if ($rowField->is_core)
			{
				unset($rowFields[$i]);
			}

			$i++;
		}

		$fields[] = 'created_date';
		$fields[] = 'to_date';
		$fields[] = 'membership_id';

		$dateFields = ['created_date', 'to_date'];

		foreach ($rows as $row)
		{
			$row->plan    = $userPlans[$row->user_id]->title;
			$row->to_date = $userPlans[$row->user_id]->to_date;

			foreach ($dateFields as $dateField)
			{
				if ((int) $row->{$dateField})
				{
					$row->{$dateField} = HTMLHelper::_('date', $row->{$dateField}, 'Y-m-d');
				}
				else
				{
					$row->{$dateField} = '';
				}
			}

			foreach ($rowFields as $rowField)
			{
				if (!$rowField->is_core)
				{
					$fieldValue             = $fieldValues[$row->id][$rowField->id] ?? '';
					$row->{$rowField->name} = $fieldValue;
				}
			}
		}

		// Give a chance for plugin to handle export data itself
		PluginHelper::importPlugin('osmembership');

		$event = new BeforeXLSXExport([
			'rows'     => $rows,
			'fields'   => $fields,
			'filename' => 'expired_subscribers_list.xlsx',
		]);

		$results = $this->getApplication()->triggerEvent($event->getName(), $event);

		if (count($results) && $filename = $results[0])
		{
			// There is a plugin handles export, it return the filename, so we just process download the file
			$this->processDownloadFile($filename);

			return;
		}

		$filePath = OSMembershipHelper::callOverridableHelperMethod(
			'Data',
			'excelExport',
			[$fields, $rows, 'expired_subscribers_list']
		);

		if ($filePath)
		{
			$this->processDownloadFile($filePath);
		}
	}

	/**
	 * Generate CSV Template use to import subscribers into the system
	 */
	public function csv_import_template()
	{
		$this->checkAccessPermission('subscriptions');

		$planId = $this->input->getInt('plan_id', 0);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('name')
			->from('#__osmembership_fields')
			->whereNotIn('fieldtype', ['Heading', 'Message'], ParameterType::STRING)
			->where('published = 1')
			->order('ordering');

		if ($planId > 0)
		{
			$negPlanId = -1 * $planId;
			$query->where(
				'(plan_id = 0 OR id IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $planId . ' OR plan_id < 0))'
			)
				->where(
					'id NOT IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $negPlanId . ')'
				);
		}

		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		$fields = [
			'plan',
			'username',
			'password',
		];

		foreach ($rowFields as $rowField)
		{
			$fields[] = $rowField->name;
		}

		$fields[] = 'created_date';
		$fields[] = 'payment_date';
		$fields[] = 'from_date';
		$fields[] = 'to_date';
		$fields[] = 'published';
		$fields[] = 'amount';
		$fields[] = 'tax_amount';
		$fields[] = 'discount_amount';
		$fields[] = 'gross_amount';
		$fields[] = 'payment_method';
		$fields[] = 'transaction_id';
		$fields[] = 'membership_id';

		$row           = new stdClass();
		$row->plan     = '6 Months Membership';
		$row->username = 'tuanpn';
		$row->password = 'tuanpn';

		foreach ($rowFields as $rowField)
		{
			if ($rowField->name == 'first_name')
			{
				$row->{$rowField->name} = 'Tuan';
			}
			elseif ($rowField->name == 'last_name')
			{
				$row->{$rowField->name} = 'Pham Ngoc';
			}
			elseif ($rowField->name == 'email')
			{
				$row->{$rowField->name} = 'tuanpn@joomdonation.com';
			}
			else
			{
				$row->{$rowField->name} = 'sample_data_for_' . $rowField->name;
			}
		}

		$todayDate = Factory::getDate();

		$row->payment_date = $row->from_date = $row->created_date = $todayDate->format('Y-m-d');

		$todayDate->modify('+6 months');

		$row->to_date         = $todayDate->format('Y-m-d');
		$row->published       = 1;
		$row->amount          = 100;
		$row->tax_amount      = 10;
		$row->discount_amount = 0;
		$row->gross_amount    = 110;
		$row->payment_method  = 'os_paypal';
		$row->transaction_id  = 'TR4756RUI78465';
		$row->membership_id   = 1001;

		// Give a chance for plugin to handle export data itself
		PluginHelper::importPlugin('osmembership');

		$event = new BeforeXLSXExport([
			'rows'     => [$row],
			'fields'   => $fields,
			'filename' => 'subscriptions_import_template.xlsx',
		]);

		$results = $this->getApplication()->triggerEvent($event->getName(), $event);

		if (count($results) && $filename = $results[0])
		{
			// There is a plugin handles export, it returns the filename, so we just process download the file
			$this->processDownloadFile($filename);

			return;
		}

		$filePath = OSMembershipHelper::callOverridableHelperMethod(
			'Data',
			'excelExport',
			[$fields, [$row], 'subscriptions_import_template']
		);

		if ($filePath)
		{
			$this->processDownloadFile($filePath);
		}
	}

	/**
	 * Disable reminders for selected subscription records
	 */
	public function disable_reminders()
	{
		if ($this->app->isClient('site'))
		{
			$this->csrfProtection();
		}

		$this->checkAccessPermission('subscriptions');

		$cid = $this->input->post->get('cid', [], 'array');

		if (count($cid))
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->update('#__osmembership_subscribers')
				->set('first_reminder_sent = 1')
				->set('second_reminder_sent = 1')
				->set('third_reminder_sent = 1')
				->whereIn('id', $cid);

			$subscribeTableFields = array_keys($db->getTableColumns('#__osmembership_subscribers'));

			$extraReminderSentFields = [
				'fourth_reminder_sent',
				'fifth_reminder_sent',
				'sixth_reminder_sent',
			];

			foreach ($extraReminderSentFields as $extraField)
			{
				if (in_array($extraField, $subscribeTableFields))
				{
					$query->set($extraField . ' = 1');
				}
			}

			$db->setQuery($query)
				->execute();
		}

		$this->setRedirect(
			$this->getViewListUrl(),
			Text::_('OSM_REMINDER_EMAILS_DISABLED_FOR_SELECTED_SUBSCRIPTIONS')
		);
	}

	/**
	 * Enable reminders for selected subscription records
	 */
	public function enable_reminders(): void
	{
		if ($this->app->isClient('site'))
		{
			$this->csrfProtection();
		}

		$this->checkAccessPermission('subscriptions');

		$cid = $this->input->post->get('cid', [], 'array');

		if (count($cid))
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->update('#__osmembership_subscribers')
				->set('first_reminder_sent = 0')
				->set('second_reminder_sent = 0')
				->set('third_reminder_sent = 0')
				->set('first_reminder_sent_at = NULL')
				->set('second_reminder_sent_at = NULL')
				->set('third_reminder_sent_at = NULL')
				->whereIn('id', $cid);

			$subscribeTableFields = array_keys($db->getTableColumns('#__osmembership_subscribers'));

			$extraReminderSentFields = [
				'fourth_reminder_sent',
				'fifth_reminder_sent',
				'sixth_reminder_sent',
			];

			foreach ($extraReminderSentFields as $extraField)
			{
				if (in_array($extraField, $subscribeTableFields))
				{
					$query->set($extraField . ' = 1')
						->set($extraField . '_at = NULL');
				}
			}

			$db->setQuery($query)
				->execute();
		}

		$this->setRedirect(
			$this->getViewListUrl(),
			Text::_('OSM_REMINDER_EMAILS_DISABLED_FOR_SELECTED_SUBSCRIPTIONS')
		);
	}

	/**
	 * Cancel recurring subscription
	 *
	 * @throws Exception
	 */
	public function refund()
	{
		$id = $this->input->post->getInt('id', 0);

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_subscribers')
			->where('id = ' . $id);
		$db->setQuery($query);
		$rowSubscription = $db->loadObject();

		if (OSMembershipHelper::canRefundSubscription($rowSubscription))
		{
			/**@var OSMembershipModelSubscription $model * */
			$model = $this->getModel('Subscription');

			try
			{
				$model->refund($rowSubscription);

				$this->setRedirect(
					'index.php?option=com_osmembership&view=subscription&id=' . $rowSubscription->id,
					Text::_('OSM_SUBSCRIPTION_REFUNDED')
				);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'error');
				$this->setRedirect(
					'index.php?option=com_osmembership&view=subscription&id=' . $rowSubscription->id,
					$e->getMessage(),
					'error'
				);
			}
		}
		else
		{
			throw new InvalidArgumentException(Text::_('OSM_CANNOT_PROCESS__REFUND'));
		}
	}

	/**
	 * Send payment request to selected subscriptions
	 *
	 * @return void
	 */
	public function request_payment()
	{
		if (!$this->app->getIdentity()->authorise('membershippro.subscriptions', 'com_osmembership'))
		{
			throw new Exception('You do not have permission to request payment', 403);
		}

		$cid = $this->input->get('cid', [], 'array');
		$cid = ArrayHelper::toInteger($cid);

		/* @var OSMembershipModelSubscription $model */
		$model = $this->getModel();

		try
		{
			foreach ($cid as $id)
			{
				$model->sendPaymentRequestEmail($id);
			}

			$this->setMessage(Text::_('OSM_REQUEST_PAYMENT_EMAIL_SENT_SUCCESSFULLY'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Download member card
	 */
	public function download_member_card()
	{
		if (!$this->app->getIdentity()->authorise('membershippro.subscriptions', 'com_osmembership'))
		{
			throw new Exception('You do not have permission to request payment', 403);
		}

		$config = OSMembershipHelper::getConfig();

		if (!$config->activate_member_card_feature)
		{
			throw new Exception(
				'This feature is not enabled. If you are administrator and want to use it, go to Membership Pro -> Configuration to enable this feature',
				403
			);
		}

		$id = $this->input->getInt('id');

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('a.*, b.username')
			->from('#__osmembership_subscribers AS a')
			->leftJoin('#__users AS b ON a.user_id = b.id')
			->where('a.id = ' . $id);
		$db->setQuery($query);
		$item = $db->loadObject();

		if (!$item)
		{
			$this->setRedirect($this->getViewListUrl(), Text::_('Invalid Subscription Record'));

			return;
		}

		// Generate member card and save it
		$path = OSMembershipHelperSubscription::generatePlanMemberCard($item, $config);

		$this->processDownloadFile($path, $item->username . '_' . $item->plan_id . '.pdf');
	}

	/**
	 * Get sales chart data used for reloading chart
	 */
	public function get_sales_chart_data()
	{
		$planId = $this->input->getInt('plan_id');

		$sales = OSMembershipModelSubscriptions::getLast12MonthSales($planId);

		$data = [
			'labels'             => $sales['labels'],
			'sales'              => $sales['income'],
			'subscriptionsCount' => $sales['count'],
		];

		echo json_encode($data);

		$this->app->close();
	}

	/**
	 * Get daily sales data
	 *
	 * @return void
	 */
	public function get_daily_sales_chart_data()
	{
		$planId   = $this->input->getInt('plan_id');
		$fromDate = $this->input->getString('from_date', '');
		$toDate   = $this->input->getString('to_date', '');

		/* @var OSMembershipModelSubscriptions $model */
		$model = MPFModel::getTempInstance('Subscriptions', 'OSMembershipModel')
			->set('plan_id', $planId)
			->set('filter_from_date', $fromDate)
			->set('filter_to_date', $toDate);

		$sales = $model->getDailySalesStatistic();

		$data = [
			'labels'             => $sales['labels'],
			'sales'              => $sales['income'],
			'subscriptionsCount' => $sales['count'],
		];

		echo json_encode($data);

		$this->app->close();
	}

	/**
	 * Prepare data for each subscription record before it is being exported
	 *
	 * @param   array  $rows
	 * @param   array  $rowFields
	 * @param   array  $fieldValues
	 * @param   array  $fields
	 *
	 * @return void
	 */
	protected function prepareExportData($rows, $rowFields, $fieldValues, &$fields): void
	{
		$config = OSMembershipHelper::getConfig();

		$dateFields = ['created_date', 'payment_date', 'from_date', 'to_date'];

		$subscriptionTypeMapping = [
			'subscribe' => Text::_('OSM_NEW_SUBSCRIPTION'),
			'renew'     => Text::_('OSM_SUBSCRIPTION_RENEWAL'),
			'upgrade'   => Text::_('OSM_SUBSCRIPTION_UPGRADE'),
		];

		foreach ($rows as $row)
		{
			foreach ($rowFields as $rowField)
			{
				if (!$rowField->is_core)
				{
					$fieldValue             = $fieldValues[$row->id][$rowField->id] ?? '';
					$row->{$rowField->name} = $fieldValue;
				}
			}

			if ($config->activate_invoice_feature && $row->invoice_number > 0)
			{
				$row->invoice_number = OSMembershipHelper::formatInvoiceNumber($row, $config);
			}
			else
			{
				$row->invoice_number = '';
			}

			$row->plan     = $row->plan_title;
			$row->category = $row->category_title;

			foreach ($dateFields as $dateField)
			{
				if ((int) $row->{$dateField})
				{
					$row->{$dateField} = HTMLHelper::_('date', $row->{$dateField}, $config->date_format);
				}
				else
				{
					$row->{$dateField} = '';
				}
			}

			$row->subscription_type = $subscriptionTypeMapping[$row->act] ?? Text::_('OSM_NEW_SUBSCRIPTION');
		}
	}
}
