<?php
/**
 * @version        5.6.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Http\HttpFactory;

class DonationController extends OSFController
{

	/**
	 * Display information
	 */
	public function display($cachable = false, array $urlparams = array())
	{
		global $loadStyle;
		$config   = DonationHelper::getConfig();
		$document = Factory::getApplication()->getDocument();

		DonationHelper::loadMedia();

		if (file_exists(JPATH_ROOT . '/media/com_jdonation/assets/css/custom.css') && filesize(JPATH_ROOT . '/media/com_jdonation/assets/css/custom.css') > 0)
		{
			$document->addStylesheet(Uri::base(true) . '/media/com_jdonation/assets/css/custom.css', 'text/css', null, null);
		}
		if ($config->load_twitter_bootstrap)
		{
			DonationHelper::loadBootstrap(false);
		}
		DonationHelper::loadJQuery();
		HTMLHelper::_('script', DonationHelper::getSiteUrl() . '/media/com_jdonation/assets/js/noconflict.js', false, false);

		//Clear the donation form data on donaton complete or cancel
		$viewName = $this->input->get('view', $this->defaultView, 'string');
		if ($viewName == 'complete' || $viewName == 'cancel')
		{
			$this->app->setUserState('com_jdonation.formdata', null);
			
		}

		parent::display($cachable, $urlparams);
	}

	/**
	 * Download donation receipt
	 *
	 */
	public function download_receipt()
	{
		DonationHelper::noindex();
		$user = Factory::getApplication()->getIdentity();
		$f	 = $this->input->getInt('f', 0);
		if (!$user && $f == 0)
		{
			return;
		}
		$id  = $this->input->getInt('id');
		$row = Table::getInstance('Donor', 'DonationTable');
		$row->load($id);

		if (!Factory::getApplication()->isClient('administrator') && $row->user_id != $user->id && $f == 0)
		{
			return;
		}

		//Validation is OK, we can now process download the receipt
		DonationHelper::downloadInvoice($id);
	}

	/**
	 * Download a file uploaded by donor
	 */
	public function download_file()
	{

		$filePath = 'media/com_jdonation/files';
		$fileName = $this->input->get('file_name', '', 'none');
		if (file_exists(JPATH_ROOT . '/' . $filePath . '/' . $fileName))
		{
			while (@ob_end_clean()) ;
			DonationHelper::processDownload(JPATH_ROOT . '/' . $filePath . '/' . $fileName, $fileName);
			exit();
		}
		else
		{
			$this->app->enqueueMessage(Text::_('JD_FILE_NOT_EXIST'));
			$this->app->redirect('index.php?option=com_jdonation');
		}
	}

	/**
	 * Validate username which users entered on order form
	 *
	 */
	public function validate_username()
	{
		$db         = Factory::getContainer()->get('db');
		$query      = $db->getQuery(true);
		$username   = $this->input->get('fieldValue', '', 'string');
		$validateId = $this->input->get('fieldId', '', 'string');
		$query->select('COUNT(*)')
			->from('#__users')
			->where('username="' . $username . '"');
		$db->setQuery($query);
		$total        = $db->loadResult();
		$arrayToJs    = array();
		$arrayToJs[0] = $validateId;
		if ($total)
		{
			$arrayToJs[1] = false;
		}
		else
		{
			$arrayToJs[1] = true;
		}
		echo json_encode($arrayToJs);
		Factory::getApplication()->close();
	}

	/**
	 * Validate email which users entered on order form to make sure it is valid
	 */
	public function validate_email()
	{
		$db         = Factory::getContainer()->get('db');
		$query      = $db->getQuery(true);
		$email      = $this->input->get('fieldValue', '', 'string');
		$validateId = $this->input->get('fieldId', '', 'string');
		$query->select('COUNT(*)')
			->from('#__users')
			->where('email="' . $email . '"');
		$db->setQuery($query);
		$total        = $db->loadResult();
		$arrayToJs    = array();
		$arrayToJs[0] = $validateId;
		if (!$total)
		{
			$arrayToJs[1] = true;
		}
		else
		{
			$arrayToJs[1] = false;
		}
		echo json_encode($arrayToJs);
		Factory::getApplication()->close();
	}

	/**
	 * Get list of states for the selected country, using in AJAX request
	 */
	public function get_states()
	{
		$countryName = $this->input->get('country_name', '', 'string');
		$stateName   = $this->input->get('state_name', '', 'string');
		if (!$countryName)
		{
			$countryName = DonationHelper::getConfigValue('default_country');
		}
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->clear();
		$query->select('required')
			->from('#__jd_fields')
			->where('name=' . $db->quote('state'));
		$db->setQuery($query);
		$required = $db->loadResult();
		($required) ? $class = 'validate[required]' : $class = '';

		$query->clear();
		$query->select('country_id')
			->from('#__jd_countries')
			->where('name=' . $db->quote($countryName));
		$db->setQuery($query);
		$countryId = $db->loadResult();
		//get state
		$query->clear();
		$query->select('state_name AS value, state_name AS text')
			->from('#__jd_states')
			->where('country_id=' . (int) $countryId);;
		$db->setQuery($query);
		$states  = $db->loadObjectList();
		$options = array();
		if (count($states))
		{
			$options[] = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_STATE'));
			$options   = array_merge($options, $states);
		}
		else
		{
			$options[] = HTMLHelper::_('select.option', 'N/A', Text::_('JD_NA'));
		}
		echo HTMLHelper::_('select.genericlist', $options, 'state', ' class="input-large form-select form-control' . $class . '" id="state" ', 'value', 'text',
			$stateName);

		Factory::getApplication()->close();
	}

	/**
	 * Redirect donor to donation form
	 */
	public function donation_form()
	{
		$config        = DonationHelper::getConfig();
		$campaignId    = $this->input->getInt('campaign_id', 0);
		$Itemid        = $this->input->getInt('Item_id');
		$amount        = $this->input->getFloat('amount', 0);
		$rdAmount      = $this->input->getFloat('rd_amount', 0);
		$paymentMethod = $this->input->getCmd('payment_method', '');

		$url = DonationHelperRoute::getDonationFormRoute($campaignId, $Itemid);
		if ($amount > 0)
		{
			$url .= '&amount=' . $amount;
		}

		if ($rdAmount > 0)
		{
			$url .= '&rd_amount=' . $rdAmount;
		}

		if ($paymentMethod)
		{
			$url .= '&payment_method=' . $paymentMethod;
		}

		$this->setRedirect(Route::_($url, false, (int) $config->use_https));
	}

	static function convertCurrency(){
		$jinput				= Factory::getApplication()->input;
		$cur_from			= $jinput->getString('cur_from');
		$cur_to				= $jinput->getString('cur_to');
		$http				= HttpFactory::getHttp();
        $url				= 'http://free.currencyconverterapi.com/api/v5/convert?q='.$cur_from.'_'.$cur_to.'&compact=y';
        $response			= $http->get($url);
		if ($response->code == 200)
        {
            $data = $response->body;
            $returnArr = json_decode($data);
            $converted = $returnArr->{$cur_from.'_'.$cur_to}->val;
        }
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
            ->filter_order('tbl.payment_date')
            ->filter_order_Dir('ASC')
			->filter_paid_status(2)
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
            $this->app->redirect('index.php?option=com_jdonation&view=donors');
        }
    }

    /***
     * Get search parameters from search module and performing redirect
     */
    public function search()
    {
        $search			= $this->input->getString('filter_search', '');
        $Itemid			= $this->input->getInt('Itemid', 0);
        $url = 'index.php?option=com_jdonation&view=search';
        if ($search)
        {
            $url        .= '&filter_search=' . $search;
        }
        $url            .= '&Itemid=' . $Itemid;
        $this->app->redirect(Route::_($url, false, 0));
    }

	public function populateUserData()
	{
		$input			= Factory::getApplication()->input;
		$userId			= $input->getInt('user_id', 0);
		$data			= array();
		if ($userId > 0)
		{
			$user		= Factory::getUser($userId);
			$name		= $user->name;
			$nameArr    = explode(" ", $name);
			if(count($nameArr) > 2)
			{
				$data['first_name']	= $nameArr[0];
				$last_name = "";
				for($i=1; $i< count($nameArr); $i++)
				{
					$last_name .= $nameArr[$i]. " ";
				}
				$last_name = substr($last_name, 0, strlen($last_name) - 1);
				$data['last_name']	= $last_name;
			}
			else
			{
				$data['first_name']	= $name;
				$data['last_name']	= "";
			}
			$email		= $user->email;
			
			$data['email']	= $user->email;
		}
		echo json_encode($data);
		Factory::getApplication()->close();
	}

	public function share_campaign()
	{
		//echo "1";
		$input			= Factory::getApplication()->input;
		$user			= Factory::getApplication()->getIdentity();
		if($user->id > 0)
		{
			$myname		= $user->name;
			$myemail	= $user->email;
		}
		else
		{
			$myname		= $input->getString('myname','');
			$myemail	= $input->getString('myemail','');
		}

		$friendname		= $input->getString('friendname','');
		$friendemail	= $input->getString('friendemail','');
		$campaign_url	= $input->getString('campaign_url','');
		$campaign_url	= base64_decode($campaign_url);
		$campaign_url   = "<a href='$campaign_url'>$campaign_url</a>";

		$config			= DonationHelper::getConfig();
		$shareSbj		= $config->share_campaign_sbj;
		$shareBody		= $config->share_campaign_body;

		$id				= $input->getInt('id');
		$db				= Factory::getContainer()->get('db');
		$query              = $db->getQuery(true);
		$query->select('*')->from('#__jd_campaigns')->where('id = '.$id);
		$db->setQuery($query);
		$campaign		= $db->loadObject();

		$shareBody      = str_replace("[MY_NAME]", $myname, $shareBody);
		$shareBody      = str_replace("[MY_EMAIL]", $myemail, $shareBody);
		$shareBody      = str_replace("[FRIEND_NAME]", $friendname, $shareBody);
		$shareBody      = str_replace("[FRIEND_EMAIL]", $friendemail, $shareBody);
		$shareBody      = str_replace("[CAMPAIGN_NAME]", $campaign->title, $shareBody);
		$shareBody      = str_replace("[CAMPAIGN_GOAL]", number_format($campaign->goal, 2) , $shareBody);
		$shareBody      = str_replace("[CAMPAIGN_LINK]", $campaign_url, $shareBody);
		if($config->qr_code)
		{
			if(!file_exists(JPATH_ROOT . '/media/com_jdonation/qrcodes/'.$campaign->id.'.png'))
			{
				DonationHelper::generateQrCode($campaign->id);
			}
			$qr_code    = "<img src='".Uri::root()."media/com_jdonation/qrcodes/".$campaign->id.".png' />";
			$shareBody  = str_replace("[CAMPAIGN_QR]", $qr_code, $shareBody);
		}
		else
		{
			$shareBody  = str_replace("[CAMPAIGN_QR]", '', $shareBody);
		}
		
		if ($row->campaign_id && $campaign->from_name != '')
        {
            $fromName	= $campaign->from_name;
        }
        elseif ($config->from_name)
        {
            $fromName	= $config->from_name;
        }
        else
        {
            $fromName	= Factory::getConfig()->get('fromname');
        }

        if ($row->campaign_id && $campaign->from_email != '')
        {
            $fromEmail = $campaign->from_email;
        }
        elseif ($config->from_email)
        {
            $fromEmail = $config->from_email;
        }
        else
        {
            $fromEmail = Factory::getConfig()->get('mailfrom');
        }

		$mailer			= Factory::getMailer();

		if($shareSbj != "" && $shareBody != "" && filter_var($friendemail, FILTER_VALIDATE_EMAIL))
		{
			$mailer->sendMail($fromEmail, $fromName, $friendemail, $shareSbj, $shareBody, 1);
			static::logEmails(array($friendemail), $shareSbj, $shareBody, 1, 'sharecampaign');

			?>
			<div style="width:100%;text-align:center;padding:20px;font-weight:500;">
				<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#0d6efd" class="bi bi-check2-circle" viewBox="0 0 16 16">
				  <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/>
				  <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/>
				</svg>
				</br>
				<p class="jd-message"><?php echo Text::_('JD_THANKYOU_SHARING'); ?></p>
			</div>
			<?php
		}
		else
		{
			?>
			<div style="width:100%;text-align:center;padding:20px;font-weight:500;">
				<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="red" class="bi bi-exclamation-circle" viewBox="0 0 16 16">
				  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
				  <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
				</svg>
				</br>
				<p class="jd-message"><?php echo Text::_('JD_SHARING_NOT_COMPLETE'); ?></p>
			</div>
			<?php
		}
		Factory::getApplication()->close();
	}
}
