<?php
/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

class DonationModelDonor extends OSFModelAdmin
{

	/**
	 * Method to store a donor
	 *
	 * @access    public
	 * @return    boolean    True on success
	 * @since     3.6
	 */
	function store($input, $ignore = array())
	{
		$row           = $this->getTable();
		$currency      = DonationHelper::getConfigValue('currency');
		$id            = $input->getInt('id', 0);
		$published     = 0;
		$paymentMethod = $input->get('payment_method', 'os_offline');

		$donationType = $input->get('donation_type','I');
		if ($paymentMethod != 'os_offline')
		{
			// Need to set published = 1 so that the record will be showed
			//$input->set('published', 1);
		}
		$donationType  = isset($donationType) ? $donationType : 'I';
		if ($donationType == 'I' || $paymentMethod == 'os_offline')
		{
		    $input->set('donation_type', 'I');
		}
		else
		{
		    $input->set('donation_type', 'R');
		}
		
		if (!$id)
		{
			// Generate Random Transaction ID for the record
			jimport('joomla.user.helper');
			$input->set('transaction_id', strtoupper(UserHelper::genRandomPassword()));
			$isNew = true;
		}
		else
		{
			$row->load($id);
			$published = $row->published;
			$isNew     = false;
		}
		
		if (!$row->campaign_id)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('id, title')
				->from('#__jd_campaigns')
				->where('published = 1')
				->order('id DESC');
			$db->setQuery($query, 0, 1);
			$campaign = $db->loadObject();

			if ($campaign)
			{
				$row->campaign_id = $campaign->id;
			}
		}

		//amount
        $currency_code          = $input->get('currency_code',$currency);
        $amount                 = $input->getFloat('amount');//$row->amount;
        $converted_amount       = $amount;
		//$currency_code          = $row->currency_code;
		if($currency_code != "" && $currency_code != $currency && DonationHelper::isMultipleCurrencies())
		{
		    $converted_amount   = DonationHelper::convertAmountToDefaultCurrency($amount,$currency_code);
        }
        $row->amount_converted  = (float)$amount;
		$row->amount            = (float)$converted_amount;
		$row->currency_code     = $currency;
		$row->payment_fee		= $input->get('payment_fee',0);
		$row->payment_fee		= (float) $row->payment_fee;
		$input->set('payment_fee', $row->payment_fee);
		$input->set('amount_converted',$amount);
        $input->set('amount',$converted_amount);
        $input->set('currency_code',$currency);
        $input->set('currency_converted',$currency_code);
		$row->show_dedicate		= $input->get('show_dedicate',0);
		$row->r_times			= (int) $row->r_times;
		$input->set('r_times',$row->r_times);
		$input->set('show_dedicate', $row->show_dedicate);
		$row->dedicate_type		= (string) $row->dedicate_type;
		$row->dedicate_name		= (string) $row->dedicate_name;
		$row->dedicate_email	= (string) $row->dedicate_email;
		$row->category_id		= (string) $row->category_id;
		$row->params			= "";

		$input->set('dedicate_type',$row->dedicate_type);
		$input->set('dedicate_name',$row->dedicate_name);
		$input->set('dedicate_email',$row->dedicate_email);
		$input->set('category_id',$row->category_id);
		$input->set('params',$row->params);
		
		parent::store($input, $ignore);

		// Store custom fields data
		DonationHelper::storeFormData($input->getInt('id'), $input->getData());

		// Reload the data for the saved record
		$id = $input->getInt('id', 0);
		$row->load($id);

		// In case adding new registration record, we will send email notification to donor and admin
		$send_email = 0;
		if ($isNew)
		{
			$config = DonationHelper::getConfig();
			DonationHelper::sendEmails($row, $config);
			$send_email = 1;
		}

		// Trigger onAfterPaymentSuccess event if needed
		if (!$published && $row->published)
		{
			PluginHelper::importPlugin('jdonation');
			//$dispatcher = JDispatcher::getInstance();
			$result = Factory::getApplication()->triggerEvent('onAfterPaymentSuccess', array($row));

			//send Offline payment received
			if($send_email == 0)
			{
				self::sendOfflineReceived($row);
			}
		}
	}

	protected function initData()
	{
		parent::initData();
		$date						= Factory::getDate('now', Factory::getConfig()->get('offset'));
		$this->data->created_date   = $date->format("Y-m-d");
		$this->data->payment_date   = $date->format("Y-m-d");
		$this->data->payment_method = 'os_offline';
		$this->data->published      = 1;
	}

	/**
	 * Publish donors
	 *
	 * @param array $cid
	 * @param int   $state
	 *
	 * @return bool
	 */
	function publish($cid, $state = 1)
	{
		$config= DonationHelper::getConfig();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// In case submitting the record, we will trigger onAfterPaymentSuccess event
		if (($state == 1) && count($cid))
		{
			PluginHelper::importPlugin('jdonation');
			//$dispatcher = JDispatcher::getInstance();
			$row        = $this->getTable();
			foreach ($cid as $donorId)
			{
				$row->load($donorId);
				if (!$row->published)
				{
					// Trigger event
					Factory::getApplication()->triggerEvent('onAfterPaymentSuccess', array($row));
				}

				self::sendOfflineReceived($row);
			}
		}

		$query->update('#__jd_donors')
			->set('published = ' . (int) $state)
			->where('id IN (' . implode(',', $cid) . ')');
		if ($state == 0)
		{
			$query->where('payment_method = "os_offline"');
		}
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	public static function sendOfflineReceived($row)
	{
		$db     = Factory::getContainer()->get('db');
		$query  = $db->getQuery(true);
		$config= DonationHelper::getConfig();
		if($row->payment_method == 'os_offline')
		{
			//check and send email: Offline payment received
			$mailer      = Factory::getMailer();
			$fieldSuffix = DonationHelper::getFieldSuffix($row->language);
			if ($fieldSuffix)
			{
				$configFields = array(
					'user_email_subject',
					'user_email_body_offline_received'
				);
				DonationHelper::getMultilingualConfigData($config, $fieldSuffix, $configFields);
			}

			$siteName = Factory::getConfig()->get('sitename');
			$replaces = DonationHelper::buildReplaceTags($row, $config, true);
			// Override email message
			if ($row->campaign_id)
			{
				$query->select('*')
					->from('#__jd_campaigns')
					->where('id = ' . (int) $row->campaign_id);
				$db->setQuery($query);

				if ($fieldSuffix)
				{
					$campaignFields = array(
						'title',
						'user_email_subject',
						'user_email_body_offline_received'
					);
					DonationHelper::getMultilingualFields($query, $campaignFields, $fieldSuffix);
				}

				$rowCampaign = $db->loadObject();
				if ($rowCampaign->notification_emails)
				{
					$config->notification_emails = $rowCampaign->notification_emails;
				}
				if($rowCampaign->user_email_body_offline_received)
				{
					$config->user_email_body_offline_received = $rowCampaign->user_email_body_offline_received;
				}
			}

			if ($row->campaign_id && $rowCampaign->from_name != '')
			{
				$fromName = $rowCampaign->from_name;
			}
			elseif ($config->from_name)
			{
				$fromName = $config->from_name;
			}
			else
			{
				$fromName = Factory::getConfig()->get('fromname');
			}

			if ($row->campaign_id && $rowCampaign->from_email != '')
			{
				$fromEmail = $rowCampaign->from_email;
			}
			elseif ($config->from_email)
			{
				$fromEmail = $config->from_email;
			}
			else
			{
				$fromEmail = Factory::getConfig()->get('mailfrom');
			}

			//Notification email send to user
			$subject	= $config->user_email_subject;
			$body		= $config->user_email_body_offline_received;
			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$body    = str_replace("[$key]", $value, $body);
				$subject = str_replace("[$key]", $value, $subject);
			}
			$body        = DonationHelper::convertImgTags($body);
			$attachments = [];
			if ($config->activate_donation_receipt_feature && $config->send_receipt_via_email)
			{
				if (!$row->invoice_number)
				{
					$row->invoice_number = DonationHelper::getInvoiceNumber($row);
					$row->store();
				}
				$invoiceNumber = DonationHelper::formatInvoiceNumber($row->invoice_number, $config, $row);
				DonationHelper::generateInvoicePDF($row);
				$attachments[] = JPATH_ROOT . '/media/com_jdonation/receipts/' .$invoiceNumber . '.pdf';
			}
			if($subject != "" && $body != "")
			{
				$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1, null, null, $attachments);
				//log Emails
				DonationHelper::logEmails(array($row->email), $subject, $body, 1, 'confirmation');
			}
		}
	}

    /**
     * This function is used to resend emails to donors. But the emails will only be sent to paid donors
     * @param $id
     * @return bool
     */
    public function resendEmail($id)
    {
        $row = $this->getTable();
        $row->load($id);

        // Load the default frontend language
        $lang = Factory::getApplication()->getLanguage();
        $tag  = $row->language;

        if (!$tag || $tag == '*')
        {
            $tag = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
        }

        $lang->load('com_jdonation', JPATH_ROOT, $tag);

        $config = DonationHelper::getConfig();

        if ($row->published == 1)
        {
            DonationHelper::sendEmails($row, $config);
            return true;
        }
        else
        {
            return false;
        }
    }

	public function cancelRecurringDonation($row)
    {
        $method = DonationHelper::loadPaymentMethod($row->payment_method);

        /* @var os_authnet $method */

        $ret = false;

        if (method_exists($method, 'cancelDonation'))
        {
            $ret = $method->cancelDonation($row);
        }


        if ($ret)
        {
            DonationHelper::cancelRecurringDonation($row->id);
        }

        return $ret;
    }

	public function sendPaymentRequestEmail($id)
	{

		$row = $this->getTable();
		$row->load($id);

		if ($row->published != 0)
		{
			// We don't send request payment email to paid registration
			throw new Exception(Text::_('JD_PAYMENT_REQUEST_PENDING_REQUEST'));
		}

		if ($row->amount == 0)
		{
			throw new Exception(Text::_('JD_PAYMENT_REQUEST_NO_PAYMENT_AMOUNT'));
		}

		$config = DonationHelper::getConfig();
		DonationHelper::sendRequestPaymentEmail($row, $config);
	}

	
}
