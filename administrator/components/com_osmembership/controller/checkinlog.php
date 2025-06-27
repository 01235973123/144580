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

class OSMembershipControllerCheckinlog extends OSMembershipController
{
	use MPFControllerDownload;

	/**
	 * Export checkin logs
	 *
	 * @return void
	 */
	public function export()
	{
		set_time_limit(0);

		$config = OSMembershipHelper::getConfig();

		/* @var OSMembershipModelCheckinlogs $model */
		$model = $this->getModel('Checkinlogs');
		$model->set('limitstart', 0)
			->set('limit', 0);

		$rows = $model->getData();

		if (count($rows) === 0)
		{
			$this->setMessage(Text::_('There are no subscription records to export'));
			$this->setRedirect($this->getViewListUrl());

			return;
		}

		$fields = [
			'username',
			'name',
			'plan_title',
			'checkin_date',
			'success',
			'id',
		];

		$headers = [
			Text::_('OSM_USERNAME'),
			Text::_('OSM_NAME'),
			Text::_('OSM_PLAN'),
			Text::_('OSM_CHECKIN_DATE'),
			Text::_('OSM_SUCCESS'),
			Text::_('OSM_ID'),
		];

		foreach ($rows as $row)
		{
			if ((int) $row->checkin_date)
			{
				$row->checkin_data = HTMLHelper::_('date', $row->checkin_date, $config->date_format . ' H:i:s');
			}

			if ($row->success)
			{
				$row->success = Text::_('JYES');
			}
			else
			{
				$row->success = Text::_('JNO');
			}
		}

		$filePath = OSMembershipHelper::callOverridableHelperMethod(
			'Data',
			'excelExport',
			[$fields, $rows, 'checkinlogs', $headers]
		);

		if ($filePath)
		{
			$this->processDownloadFile($filePath);
		}
	}
}