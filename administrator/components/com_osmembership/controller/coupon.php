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
use Joomla\CMS\Plugin\PluginHelper;
use OSSolution\MembershipPro\Admin\Event\Export\BeforeXLSXExport;

class OSMembershipControllerCoupon extends OSMembershipController
{
	use MPFControllerDownload;

	/**
	 * Method to import coupon codes from a csv file
	 */
	public function import()
	{
		$this->checkAccessPermission('coupons');

		$inputFile = $this->input->files->get('input_file');
		$fileName  = $inputFile ['name'];
		$fileExt   = strtolower(OSMembershipHelper::getFileExt($fileName));

		if (!in_array($fileExt, ['csv', 'xls', 'xlsx']))
		{
			$this->setRedirect(
				'index.php?option=com_osmembership&view=coupon&layout=import',
				Text::_('Invalid File Type. Only CSV, XLS and XLS file types are supported')
			);

			return;
		}

		/* @var OSMembershipModelCoupon $model */
		$model = $this->getModel('Coupon');

		try
		{
			$numberImportedCoupons = $model->import($inputFile['tmp_name'], $inputFile['name']);
			$this->setRedirect(
				'index.php?option=com_osmembership&view=coupons',
				Text::sprintf('OSM_NUMBER_COUPONS_IMPORTED', $numberImportedCoupons)
			);
		}
		catch (Exception $e)
		{
			$this->setRedirect('index.php?option=com_osmembership&view=coupon&layout=import');
			$this->setMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Export Coupons into a CSV file
	 */
	public function export()
	{
		$this->checkAccessPermission('coupons');

		set_time_limit(0);

		/* @var OSMembershipModelCoupons $model */
		$model = $this->getModel('coupons');
		$model->set('limitstart', 0)
			->set('limit', 0);
		$rows = $model->getData();

		if (count($rows) == 0)
		{
			$this->setMessage(Text::_('There are no coupon records to export'));
			$this->setRedirect('index.php?option=com_osmembership&view=coupons');

			return;
		}

		$fields = [
			'id',
			'plan',
			'code',
			'coupon_type',
			'subscription_type',
			'discount',
			'times',
			'used',
			'max_usage_per_user',
			'valid_from',
			'valid_to',
			'note',
			'published',
		];

		foreach ($rows as $row)
		{
			if ((int) $row->valid_from)
			{
				$row->valid_from = HTMLHelper::_('date', $row->valid_from, 'Y-m-d', null);
			}
			else
			{
				$row->valid_from = '';
			}

			if ((int) $row->valid_to)
			{
				$row->valid_to = HTMLHelper::_('date', $row->valid_to, 'Y-m-d', null);
			}
			else
			{
				$row->valid_to = '';
			}
		}

		// Give a chance for plugin to handle export data itself
		PluginHelper::importPlugin('osmembership');

		$event = new BeforeXLSXExport([
			'rows'     => $rows,
			'fields'   => $fields,
			'filename' => 'coupons_list.xlsx',
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
			[$fields, $rows, 'coupons_list']
		);

		if ($filePath)
		{
			$this->processDownloadFile($filePath);
		}
	}

	/**
	 * Batch coupon generation
	 */
	public function batch()
	{
		$this->checkAccessPermission('coupons');

		/* @var OSMembershipModelCoupon $model */
		$model = $this->getModel('Coupon');
		$model->batch($this->input);

		$this->setRedirect(
			'index.php?option=com_osmembership&view=coupons',
			Text::_('OSM_COUPONS_SUCCESSFULLY_GENERATED')
		);
	}
}
