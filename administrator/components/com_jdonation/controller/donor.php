<?php

/**
 * @version        5.6.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Application\Web\WebClient;

class DonationControllerDonor extends DonationController
{
	public function home()
	{
		$this->setRedirect('index.php?option=com_jdonation&view=dashboard');
	}

	public function export()
	{
		if (!$this->app->isClient('administrator'))
		{
			//Check permission
			$user          = Factory::getApplication()->getIdentity();
			$receiveUserId = $this->input->getInt('filter_receive_user_id');
			if (!($user->authorise('core.admin', 'com_jdonation') || ($receiveUserId > 0 && $user->id == $receiveUserId)))
			{
				$app = Factory::getApplication();
				$app->enqueueMessage(Text::_('JD_YOUR_ARE_NOT_ALLOW_TO_EXPORT_DONORS'), 'error');
				$app->redirect('index.php');

				return false;
			}
		}
		
		require_once JPATH_ROOT . '/components/com_jdonation/helper/data.php';
		$config = DonationHelper::getConfig();
		$model  = $this->getModel('donors', array('remember_states' => true));
		$rows   = $model->limitstart(0)
			->limit(0)
			->filter_order('tbl.created_date')
			->filter_order_Dir('ASC')
			->getData();
		if (count($rows))
		{
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__jd_fields')
				->where('published=1')
				->order('ordering');
			$db->setQuery($query);
			$rowFields   = $db->loadObjectList();
			$fieldValues = array();
			$donorIds    = array();
			if (count($rowFields))
			{
				foreach ($rows as $row)
				{
					$donorIds[] = $row->id;
				}
				$query->clear();
				$query->select('donor_id, field_id, field_value')
					->from('#__jd_field_value')
					->where('donor_id IN (' . implode(',', $donorIds) . ')');
				$db->setQuery($query);
				$rowFieldValues = $db->loadObjectList();
				for ($i = 0, $n = count($rowFieldValues); $i < $n; $i++)
				{
					$rowFieldValue                                                   = $rowFieldValues[$i];
					$fieldValues[$rowFieldValue->donor_id][$rowFieldValue->field_id] = $rowFieldValue->field_value;
				}
			}
			DonationHelperData::csvExport($rows, $config, $rowFields, $fieldValues);
		}
		else
		{
			$this->app->enqueueMessage(Text::_('JD_THERE_ARE_NO_DONOR_RECORDS_TO_EXPORT'));
			$this->app->redirect('index.php?option=com_jdonation');
		}
	}

	public function exportpdf()
    {
        if (!$this->app->isClient('administrator'))
        {
            //Check permission
            $user          = Factory::getApplication()->getIdentity();
            $receiveUserId = $this->input->getInt('filter_receive_user_id');
            if (!($user->authorise('core.admin', 'com_jdonation') || ($receiveUserId > 0 && $user->id == $receiveUserId)))
            {
                $app = Factory::getApplication();
                $app->enqueueMessage(Text::_('JD_YOUR_ARE_NOT_ALLOW_TO_EXPORT_DONORS'), 'error');
                $app->redirect('index.php');

                return false;
            }
        }

        require_once JPATH_ROOT . '/components/com_jdonation/helper/data.php';
        $config = DonationHelper::getConfig();
        $model  = $this->getModel('donors', array('remember_states' => true));
        $rows   = $model->limitstart(0)
            ->limit(0)
            ->filter_order('tbl.created_date')
            ->filter_order_Dir('ASC')
            ->getData();
        if (count($rows))
        {
            $db    = Factory::getContainer()->get('db');
            $query = $db->getQuery(true);
            $query->select('*')
                ->from('#__jd_fields')
                ->where('published=1')
                ->order('ordering');
            $db->setQuery($query);
            $rowFields   = $db->loadObjectList();
            $fieldValues = array();
            $donorIds    = array();
            if (count($rowFields))
            {
                foreach ($rows as $row)
                {
                    $donorIds[] = $row->id;
                }
                $query->clear();
                $query->select('donor_id, field_id, field_value')
                    ->from('#__jd_field_value')
                    ->where('donor_id IN (' . implode(',', $donorIds) . ')');
                $db->setQuery($query);
                $rowFieldValues = $db->loadObjectList();
                for ($i = 0, $n = count($rowFieldValues); $i < $n; $i++)
                {
                    $rowFieldValue                                                   = $rowFieldValues[$i];
                    $fieldValues[$rowFieldValue->donor_id][$rowFieldValue->field_id] = $rowFieldValue->field_value;
                }
            }
            DonationHelperData::pdfExport($rows, $config, $rowFields, $fieldValues);
            $this->processDownloadFile(JPATH_ROOT . '/media/com_jdonation/donors.pdf');
        }
        else
        {
            $this->app->enqueueMessage(Text::_('JD_THERE_ARE_NO_DONOR_RECORDS_TO_EXPORT'));
            $this->app->redirect('index.php?option=com_jdonation');
        }
    }

	public function exportpdfrevenue()
	{
		require_once JPATH_ROOT . '/components/com_jdonation/helper/data.php';
		$config = Factory::getConfig();
		$date = Factory::getDate('now', $config->get('offset'));
		$date->setDate($date->year, 1, 1);
		$date->setTime(0, 0, 0);
		$date->setTimezone(new DateTimeZone('UCT'));
		$fromDate = $date->toSql(true);
		$date     = Factory::getDate('now', $config->get('offset'));
		$date->setDate($date->year, 12, 31);
		$date->setTime(23, 59, 59);
		$date->setTimezone(new DateTimeZone('UCT'));
		$toDate = $date->toSql(true);

        $config = DonationHelper::getConfig();
		$db    = Factory::getContainer()->get('db');

		$db->setQuery("Select * from #__jd_campaigns where published = '1'");
		$campaigns = $db->loadObjectList();
		if(count($campaigns) > 0)
		{
			foreach($campaigns as $campaign)
			{
				$model  = $this->getModel('donors', array('remember_states' => true));
				$rows   = $model->limitstart(0)
					->limit(0)
					->filter_campaign_id($campaign->id)
					->start_date($fromDate)
					->end_date($toDate)
					->filter_order('tbl.created_date')
					->filter_order_Dir('ASC')
					->getData();
				if (count($rows))
				{
					$total = 0;
					foreach($rows as $row)
					{
						$total += $row->amount;
					}
					$campaign->total_donation = count($rows);
					$campaign->total_donated = $total;
				}
			}
			DonationHelperData::pdfExportRevenue($campaigns, $config);
            $this->processDownloadFile(JPATH_ROOT . '/media/com_jdonation/donation-report-'.date('Y-m-d').'.pdf');
		}
		else
        {
            $this->app->enqueueMessage(Text::_('JD_THERE_ARE_NO_RECORDS_TO_EXPORT'));
            $this->app->redirect('index.php?option=com_jdonation');
        }
	}

	function exportrevenue()
	{
		$db = Factory::getContainer()->get('db');
		require_once JPATH_ROOT . '/components/com_jdonation/helper/data.php';
		$config = Factory::getConfig();
		$monthLabelArray = [Text::_('JANUARY_SHORT'),Text::_('FEBRUARY_SHORT'),Text::_('MARCH_SHORT'),Text::_('APRIL_SHORT'),Text::_('MAY_SHORT'),Text::_('JUNE_SHORT'),Text::_('JULY_SHORT'),Text::_('AUGUST_SHORT'),Text::_('SEPTEMBER_SHORT'),Text::_('OCTOBER_SHORT'),Text::_('NOVEMBER_SHORT'),Text::_('DECEMBER_SHORT')];

		$input = Factory::getApplication()->input;
		$from_month = $input->getString('from_month', '');
		$from_year  = $input->getString('from_year', '');
		$to_month	= $input->getString('to_month', '' );
		$to_year	= $input->getString('to_year','');

		$year_distance = $to_year - $from_year;
		
		$month_distance = ($year_distance-1)*12 + (12 - $from_month) + $to_month + 1;

		$j = $from_month;
		$current_year = $from_year;
		if($month_distance > 0)
		{
			$data = [];
			for($i = 1; $i <= $month_distance ; $i++)
			{
				if($j >= 12)
				{
					$j = 0;
					$current_year++;
				}
				//echo $monthLabelArray[$j-1]." - ".$current_year;
				//echo "<BR />";
				

				$tmp = new stdClass();
				$date = Factory::getDate($current_year.'-'.$j.'-1', $config->get('offset'));
				$date->setTime(0, 0, 0);
				$date->setTimezone(new DateTimeZone('UCT'));
				$fromDate = $date->toSql(true);
			
				$date->setDate($current_year, $j, $date->daysinmonth);
				$date->setTime(0, 0, 0);
				$date->setTimezone(new DateTimeZone('UCT'));
				$toDate = $date->toSql(true);

				//echo $fromDate ." - ".$toDate;
				//echo "<BR />";

				$db->setQuery("Select count(id) as total, sum(amount) as donated from #__jd_donors where published = '1' and created_date >= '$fromDate' and created_date <= '$toDate'");
				$row = $db->loadObject();
				$tmp->month = $monthLabelArray[$j-1]." - ".$current_year;
				$tmp->total = (int)$row->total;
				$tmp->total_donated = (float)$row->donated;
				
				$data[] = $tmp;

				$j++;
			}
			DonationHelperData::csvExportRevenue($data, $config);
		}
		else
		{
			$this->app->enqueueMessage(Text::_('JD_THERE_ARE_NO_RECORDS_TO_EXPORT'));
            $this->app->redirect('index.php?option=com_jdonation&view=report');
		}
	}

	/**
	 * Generate CSV Template use to import subscribers into the system
	 */
	public function csv_import_template()
	{
		$db     = Factory::getContainer()->get('db');
		$query  = $db->getQuery(true);
		$query->select('name')
			->from('#__jd_fields')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		$results_arr   = array();
		$results_arr[] = 'campaign_id';
		foreach ($rowFields as $rowField)
		{
			$results_arr[] = $rowField->name;
		}
		$results_arr[] = 'created_date';
		$results_arr[] = 'amount';
		$results_arr[] = 'published';
		$results_arr[] = 'payment_method';
		$results_arr[] = 'transaction_id';

		$csv_output = "\"" . implode("\",\"", $results_arr) . "\"";

		$results_arr   = array();
		$results_arr[] = '1';
		foreach ($rowFields as $rowField)
		{
			if ($rowField->name == 'first_name')
			{
				$results_arr[] = 'Tuan';
			}
			elseif ($rowField->name == 'last_name')
			{
				$results_arr[] = 'Pham Ngoc';
			}
			elseif ($rowField->name == 'email')
			{
				$results_arr[] = 'tuanpn@joomdonation.com';
			}
			else
			{
				$results_arr[] = 'sample_data_for_'.$rowField->name;
			}
		}
		$results_arr[] = '2016-1-24';
		$results_arr[] = '100';
		$results_arr[] = '1';
		$results_arr[] = 'os_offline';
		$results_arr[] = 'TR4756RUI78465';

		$csv_output .= "\n\"" . implode("\",\"", $results_arr) . "\"";

		$csv_output .= "\n";
		$browser   = Factory::getApplication()->client->browser;
		$mime_type = ($browser == WebClient::IE || $browser == WebClient::OPERA) ? 'application/octetstream' : 'application/octet-stream';
		$filename  = "sample_donors_csv";
		header('Content-Encoding: UTF-8');
		header('Content-Type: ' . $mime_type . ' ;charset=UTF-8');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		if ($browser == WebClient::IE)
		{
			header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		else
		{
			header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
			header('Pragma: no-cache');
		}
		print $csv_output;
		exit();
	}

	/**
	 * Import Subscribers from CSV
	 */
	public function import()
	{
		$model             = $this->getModel('import');
		$numberDonors = $model->store($this->input);
		if ($numberDonors === false)
		{
			$this->app->enqueueMessage(Text::_('JD_ERROR_IMPORT_DONORS'));
			$this->app->redirect('index.php?option=com_jdonation&view=import');
		}
		else
		{
			$this->app->enqueueMessage(Text::sprintf('JD_NUMNER_DONORS_IMPORTED', $numberDonors));
			$this->app->redirect('index.php?option=com_jdonation&view=donors');
		}
	}

    /**
     * Resend Email
     */
	public function resendEmail()
    {
        $cid = $this->input->get('cid', array(), 'array');
        $cid = ArrayHelper::toInteger($cid);

        $model = $this->getModel();
        $ret   = true;

        foreach ($cid as $id)
        {
            $ret = $model->resendEmail($id);
        }

        if ($ret)
        {
            $this->setMessage(Text::_('JD_EMAIL_SUCCESSFULLY_RESENT'));
        }
        else
        {
            $this->setMessage(Text::_('JD_COULD_NOT_RESEND_EMAIL'), 'notice');
        }

        $this->setRedirect('index.php?option=com_jdonation&view=donors');
    }

	/**
     * Cancel recurring subscription
     *
     * @throws Exception
     */
	
	/*
    public function cancelrecurringdonation()
    {
        $id             = $this->input->getInt('id', 0);
        $Itemid         = $this->input->getInt('Itemid', 0);

        $db             = Factory::getContainer()->get('db');
        $query          = $db->getQuery(true);
        $query->select('*')
            ->from('#__jd_donors')
            ->where('id = ' . $db->quote($id));
        $db->setQuery($query);
        $row            = $db->loadObject();

        if ($row && DonationHelper::canCancelRecurringDonation($row))
        {
            $model = $this->getModel('Donor');
            $ret   = $model->cancelRecurringDonation($row);

            if ($ret)
            {
				$this->app->enqueueMessage(Text::_('JD_RECURRING_DONATION_HAS_BEEN_CANCELLED'));
                Factory::getSession()->set('donor_id', $row->id);
                $this->app->redirect('index.php?option=com_jdonation&view=donor&id='.$id);
            }
            else
            {
                // Redirect back to profile page, the payment plugin should enque the reason of failed cancellation so that it could be displayed to end user
                $this->app->redirect('index.php?option=com_jdonation&view=donor&id='.$id);
            }
        }
        else
        {
            // Redirect back to user profile page
            $this->app->enqueueMessage(Text::_('JD_INVALID_DONATION_RECORD'));
            $this->app->redirect('index.php?option=com_jdonation&view=donor&id='.$id);
        }
    }
	*/

	public function request_payment()
	{
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

			$this->setMessage(Text::_('JD_REQUEST_PAYMENT_EMAIL_SENT_SUCCESSFULLY'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect($this->getViewListUrl());
	}

	public function cancelrecurringdonation()
	{
		$id             = $this->input->getInt('id', 0);
       

        $db             = Factory::getContainer()->get('db');
        $query          = $db->getQuery(true);
        $query->select('*')
            ->from('#__jd_donors')
            ->where('id = ' . $db->quote($id));
        $db->setQuery($query);
        $row            = $db->loadObject();

        if ($row && DonationHelper::canCancelRecurringDonation($row))
        {
            /**@var OSMembershipModelRegister $model * */
            $model = $this->getModel();
            $ret   = $model->cancelRecurringDonation($row);

            if ($ret)
            {
				$this->setMessage(Text::_('JD_RECURRING_DONATION_HAS_BEEN_CANCELLED'));
                $this->setRedirect('index.php?option=com_jdonation&view=donors');
            }
            else
            {
                // Redirect back to profile page, the payment plugin should enque the reason of failed cancellation so that it could be displayed to end user
				$this->setMessage(Text::_('JD_DONATION_RECORD_CANNOT_BE_CANCELLED'));
                $this->setRedirect('index.php?option=com_jdonation&view=donors');
            }
        }
        else
        {
            // Redirect back to user profile page
			$this->setMessage(Text::_('JD_INVALID_DONATION_RECORD'));
            $this->setRedirect('index.php?option=com_jdonation&view=donors');
        }
	}
} 
