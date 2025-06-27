<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

trait EventbookingControllerRegistrantExport
{
	/**
	 * Export registrants data into a csv file
	 */
	public function export()
	{
		$this->checkExportPermission();

		set_time_limit(0);
		$config = EventbookingHelper::getConfig();

		[$rows, $fields, $headers] = $this->getExportData(
			$config->get('export_registrants_order', 'tbl.id'),
			$config->get('export_registrants_order_dir', 'asc')
		);

		if (count($rows) == 0)
		{
			echo Text::_('There are no registrants to export');

			return;
		}

		if ($exportTemplateId = $this->input->getInt('export_template', 0))
		{
			/* @var EventbookingModelRegistrants $model */
			$model = $this->getModel('registrants');

			$exportTemplate = $model->getExportTemplate($exportTemplateId);

			if ($exportTemplate->fields)
			{
				$templateFields = json_decode($exportTemplate->fields, true);

				[$fields, $headers] = $this->getFieldsAndHeadersFromExportTemplates($templateFields, $fields, $headers);
			}
		}

		PluginHelper::importPlugin('eventbooking');

		// Give plugin a chance to process export data
		$results = $this->app->triggerEvent(
			'onBeforeExportDataToXLSX',
			[$rows, &$fields, &$headers, 'registrants_list.xlsx']
		);

		if (count($results) && $filename = $results[0])
		{
			// There is a plugin handles export, it returns the filename, so we just process download the file
			$this->processDownloadFile($filename);

			return;
		}

		$filePath = EventbookingHelper::callOverridableHelperMethod(
			'Data',
			'excelExport',
			[$fields, $rows, 'registrants_list', $headers]
		);

		if ($filePath)
		{
			$this->processDownloadFile($filePath);
		}
	}

	/**
	 * Export registrants data into a csv file
	 */
	public function export_pdf()
	{
		$this->checkExportPermission();

		set_time_limit(0);

		[$rows, $fields, $headers] = $this->getExportData();

		if (count($rows) == 0)
		{
			echo Text::_('There are no registrants to export');

			return;
		}

		$filePath = EventbookingHelper::callOverridableHelperMethod(
			'Helper',
			'generateRegistrantsPDF',
			[$rows, $fields, $headers]
		);

		$this->processDownloadFile($filePath);
	}

	/**
	 * Export invoices
	 *
	 * @return void
	 */
	public function export_invoices()
	{
		$this->checkExportPermission();

		set_time_limit(0);

		[$rows, $fields, $headers] = $this->getExportData(
			'tbl.invoice_number',
			'ASC',
			['tbl.invoice_number > 0'],
			false
		);

		if (count($rows) == 0)
		{
			echo Text::_('There are no registrants to export');

			return;
		}

		$filePath = EventbookingHelper::callOverridableHelperMethod('Helper', 'generateRegistrantsInvoices', [$rows]);

		$this->processDownloadFile($filePath);
	}

	/**
	 * Check to see if user has permission to export registrants, if not redirect them to homepage
	 * with 403 error
	 *
	 * @return void
	 */
	protected function checkExportPermission(): void
	{
		$eventId = $this->input->getInt('event_id', $this->input->getInt('filter_event_id'));

		if (!EventbookingHelperAcl::canExportRegistrants($eventId))
		{
			$this->app->enqueueMessage(Text::_('EB_NOT_ALLOWED_TO_EXPORT'), 'error');
			$this->app->redirect(Uri::root(), 403);
		}
	}

	/**
	 * @param   string|null  $filterOrder
	 * @param   string|null  $filterOrderDir
	 * @param   bool         $preprocessData
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function getExportData(
		?string $filterOrder = null,
		?string $filterOrderDir = null,
		$wheres = [],
		$preprocessData = true
	): array {
		$eventId        = $this->input->getInt('event_id', $this->input->getInt('filter_event_id'));
		$filterOrder    ??= $this->input->getString('filter_order', 'tbl.id');
		$filterOrderDir ??= $this->input->getString('filter_order_Dir', 'ASC');

		$config = EventbookingHelper::getConfig();
		$model  = $this->getModel('registrants');

		// Fake config data so that registrants model get correct data for export
		if (isset($config->export_group_billing_records))
		{
			$config->set('include_group_billing_in_registrants', $config->export_group_billing_records);
		}

		if (isset($config->export_group_member_records))
		{
			$config->set('include_group_members_in_registrants', $config->export_group_member_records);
		}

		/* @var EventbookingModelRegistrants $model */
		$model->setState('filter_event_id', $eventId)
			->setState('limitstart', 0)
			->setState('limit', 0)
			->setState('filter_order', $filterOrder)
			->setState('filter_order_Dir', $filterOrderDir);

		$cid = $this->input->get('cid', [], 'raw');

		if ($config->export_exclude_statuses)
		{
			$model->setExcludeStatus(explode(',', $config->export_exclude_statuses));
		}

		if (!is_array($cid))
		{
			$cid = explode(',', $cid);
		}

		$cid = array_filter(ArrayHelper::toInteger($cid));

		$model->setRegistrantIds($cid);

		if (count($wheres))
		{
			foreach ($wheres as $where)
			{
				$model->getQuery()->where($where);
			}
		}

		$rows = $model->getData();

		// Early return if we only need to get registrants data
		if (!$preprocessData)
		{
			return [$rows, [], []];
		}

		$fields  = [];
		$headers = [];

		if (count($rows))
		{
			$rowFields = EventbookingHelperRegistration::getAllEventFields($eventId);
			$fieldIds  = [];

			foreach ($rowFields as $rowField)
			{
				$fieldIds[] = $rowField->id;
			}

			$fieldValues = $model->getFieldsData($fieldIds);

			[$fields, $headers] = EventbookingHelper::callOverridableHelperMethod(
				'Data',
				'prepareRegistrantsExportData',
				[$rows, $config, $rowFields, $fieldValues, $eventId]
			);
		}

		return [$rows, $fields, $headers];
	}
}