<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\File;

class DonationModelImport extends OSFModel
{
	/**
	 * @param $input
	 *
	 * @return int
	 * @throws Exception
	 */
	public function store($input)
	{
		$db        = Factory::getContainer()->get('db');
		$query     = $db->getQuery(true);
		$donors    = $this->getDonorsFromCSVFile($input);
		$todayDate = Factory::getDate()->toSql();

		if (count($donors))
		{
			$imported = 0;
			foreach ($donors as $donor)
			{
				//print_r($donor);die();
				if (empty($donor['email']) && empty($donor['Email']))
				{
					continue;
				}

				if (!empty($donor['"first_name"']))
				{
					$donor['first_name'] = $donor['"first_name"'];
				}

				if (!empty($donor['Last Name']))
				{
					$donor['last_name'] = $donor['Last Name'];
				}

				if($donor['campaign'] != "")
				{
					$campaign = $donor['campaign'];
				}
				elseif($donor['Campaign'] != "")
				{
					$campaign = $donor['Campaign'];
				}

				if ($campaign != "")
				{
					$query->clear()
						->select('id')
						->from('#__jd_campaigns')
						->where('title = ' . $db->quote($campaign));
					$db->setQuery($query);
					$donor['campaign_id'] = (int) $db->loadResult();
				}

				$row = DonationTableDonor($db); //Table::getInstance('donor', 'DonationTable');
				if (!empty($donor['created_date']) && strtotime($donor['created_date']) !== false)
				{
					$donor ['created_date'] = Factory::getDate($donor ['created_date'])->toSql();
				}
				else
				{
					$donor ['created_date'] = $todayDate;
				}

				if (!empty($donor['donation_date']) && strtotime($donor['donation_date']) !== false)
				{
					$donor ['created_date'] = Factory::getDate($donor ['donation_date'])->toSql();
				}
				else
				{
					$donor ['created_date'] = $todayDate;
				}

				if (!empty($donor['email']))
				{
					$query->clear()
						->select('id')
						->from('#__users')
						->where('email = ' . $db->quote($donor['email']));
					$db->setQuery($query);
					$donor['user_id'] = (int) $db->loadResult();
				}
				elseif (!empty($donor['Email']))
				{
					$query->clear()
						->select('id')
						->from('#__users')
						->where('email = ' . $db->quote($donor['email']));
					$db->setQuery($query);
					$donor['user_id'] = (int) $db->loadResult();
					$donor['email'] = $donor['Email'];
				}

				if (!empty($donor['Address']))
				{
					$donor['address'] = $donor['Address'];
				}

				if (!empty($donor['City']))
				{
					$donor['city'] = $donor['City'];
				}

				if (!empty($donor['State']))
				{
					$donor['state'] = $donor['State'];
				}

				if (!empty($donor['Zip']))
				{
					$donor['zip'] = $donor['Zip'];
				}

				if (!empty($donor['Country']))
				{
					$donor['country'] = $donor['Country'];
				}

				if (!empty($donor['Phone']))
				{
					$donor['phone'] = $donor['Phone'];
				}

				if (!empty($donor['Comment']))
				{
					$donor['comment'] = $donor['Comment'];
				}

				if (!empty($donor['Donation type']))
				{
					$donor['donation_type'] = $donor['Donation type'];
				}
				if($donor['donation_type'] == "One time")
				{
					$donor['donation_type'] = "I";
				}
				elseif($donor['donation_type'] == "Recurring")
				{
					$donor['donation_type'] = "R";
				}
				
				if (!empty($donor['donation_amount']))
				{
					$donor['amount'] = $donor['donation_amount'];
				}
				$donor['amount'] = (float) $donor['amount'];
				if (!empty($donor['status']))
				{
					if($donor['status'] == "Paid")
					{
						$donor['published'] = "1";
					}
					elseif($donor['status'] == "Unpaid")
					{
						$donor['published'] = "0";
					}
				}

				if (!empty($donor['Transaction ID']))
				{
					$donor['transaction_id'] = $donor['Transaction ID'];
				}

				if (!empty($donor['payment_method']))
				{
					$db->setQuery("Select name from #__jd_payment_plugins where title = ".$db->quote($donor['payment_method']));
					$payment_method = $db->loadResult();
					$donor['payment_method'] = $payment_method;
				}

				if (!empty($donor['anonymous_donation']))
				{
					if($donor['anonymous_donation'] == "No")
					{
						$donor['hide_me'] = "1";
					}
					elseif($donor['anonymous_donation'] == "Yes")
					{
						$donor['hide_me'] = "0";
					}
				}


				$donor['mollie_recurring_start_date'] = "0000-00-00 00:00:00";

				
				
				$row->bind($donor);

				if (!$row->store()) 
				{
					//JError::raiseError(500, $row->getError() );
					throw new Exception($row->getError(), 500);
				}

				$imported++;
			}
		}

		return $imported;
	}

	/**
	 * Get subscribers data from csv file
	 *
	 * @param $input
	 *
	 * @return array
	 */
	protected function getDonorsFromCSVFile($input)
	{
		$keys        = array();
		$donors      = array();
		$donor       = array();
		$allowedExts = array('csv');
		$csvFile     = $input->files->get('csv_donors');
		$csvFileName = $csvFile['tmp_name'];
		$fileName    = $csvFile['name'];
		$fileExt     = strtolower(File::getExt($fileName));
		if (in_array($fileExt, $allowedExts))
		{
			$line = 0;
			$fp   = fopen($csvFileName, 'r');
			$i = 0;
			while (($cells = fgetcsv($fp, 0, ",")) !== false)
			{
				if ($line == 0 && isset($cells[0])) {
					$cells[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cells[0]);
				}
				$i++;
				//print_r($cells);
				//echo "<BR />";
				if ($line == 0)
				{
					foreach ($cells as $key)
					{
						$key = str_replace('"','',$key);
						$key = str_replace(' ','_',$key);
						$key = strtolower(trim($key));
						$key = mb_convert_encoding($key, 'UTF-8', 'UTF-8');
						$keys[] = $key;
					}
					$line++;
				}
				else
				{
					$i = 0;
					foreach ($cells as $cell)
					{
						$donor[$keys[$i]] = $cell;
						$i++;
					}
					$donors[] = $donor;
				}
			}
			//die();
			fclose($fp);
		}

		return $donors;
	}
}
