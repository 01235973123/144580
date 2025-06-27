<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

class os_authnet_arb
{
	/**
	 * Auth merchant ID
	 *
	 * @var string
	 */
    var $login    = null;
    /**
     * Auth transaction key 
     *
     * @var string
     */
    var $transkey = null;
    /**
     * Test or live mode
     *
     * @var boolean
     */
    var $mode     = true;
	/**
	 * Params which will be passed to authorize.net
	 *
	 * @var string
	 */
    var $params  = array();
    /**
     * Success or not
     *
     * @var boolean
     */
    var $success = false;
    /**
     * Error or not
     *
     * @var boolean
     */
    var $error   = true;	
    var $xml;
    var $response;
    var $resultCode;
    var $code;
    var $text;
    var $subscrId;
	/**
	 * Constructor function
	 *
	 * @param object $config
	 */
    function __construct($params)
    {
      	$this->mode = $params->get('authnet_mode');        
        $this->login = $params->get('x_login');
        $this->transkey = $params->get('x_tran_key');
        if ($this->mode)
        {
        	$this->url = "https://api.authorize.net/xml/v1/request.api";        	              
        }
        else
        {
            $this->url = "https://apitest.authorize.net/xml/v1/request.api";    
        }    	                                       
        $this->params['startDate']        = date("Y-m-d");
        $this->params['totalOccurrences'] = 9999;
        $this->params['trialOccurrences'] = 0;
        $this->params['trialAmount']      = 0.00;
    }
	/**
	 * Process payment
	 *
	 * @param int $retries Number of retries if error appear
	 */
    function process($retries = 3)
    {
        $count = 0;
        while ($count < $retries)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $this->response = curl_exec($ch);            
            $this->parseResults();
            if ($this->resultCode === "Ok")
            {
                $this->success = true;
                $this->error   = false;
                break;
            }
            else
            {
                $this->success = false;
                $this->error   = true;
                break;
            }
            $count++;
        }
        curl_close($ch);
    }
	/**
	 * Perform a recurring payment subscription
	 *
	 */
    function createAccount()
    {
        $this->xml = "<?xml version='1.0' encoding='utf-8'?>
          <ARBCreateSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
              <merchantAuthentication>
                  <name>" . $this->login . "</name>
                  <transactionKey>" . $this->transkey . "</transactionKey>
              </merchantAuthentication>
              <refId>" . $this->params['refID'] ."</refId>
              <subscription>
                  <name>". $this->params['subscrName'] ."</name>
                  <paymentSchedule>
                      <interval>
                          <length>". $this->params['interval_length'] ."</length>
                          <unit>". $this->params['interval_unit'] ."</unit>
                      </interval>
                      <startDate>" . $this->params['startDate'] . "</startDate>
                      <totalOccurrences>". $this->params['totalOccurrences'] .
                      "</totalOccurrences>
                      <trialOccurrences>". $this->params['trialOccurrences'] .
                      "</trialOccurrences>
                  </paymentSchedule>
                  <amount>". $this->params['amount'] ."</amount>
                  <trialAmount>" . $this->params['trialAmount'] . "</trialAmount>
                  <payment>
                      <creditCard>
                          <cardNumber>" . $this->params['cardNumber'] . "</cardNumber>
                          <expirationDate>" . $this->params['expirationDate'] .
                          "</expirationDate>
                      </creditCard>
                  </payment>
				  <customer>
					  <email>". $this->params['email'] . "</email>		
				  </customer>
                  <billTo>
                      <firstName>". $this->params['firstName'] . "</firstName>
                      <lastName>" . $this->params['lastName'] . "</lastName>
                      <address>" . $this->params['address'] . "</address>
                      <city>" . $this->params['city'] . "</city>
                      <state>" . $this->params['state'] . "</state>
                      <zip>" . $this->params['zip'] . "</zip>
                  </billTo>
              </subscription>
          </ARBCreateSubscriptionRequest>";
        $this->process();
    }
	/**
	 * Set paramter
	 *
	 * @param string $field
	 * @param string $value
	 */
    function setParameter($field = "", $value = null)
    {
        $field = (is_string($field)) ? trim($field) : $field;
        $value = (is_string($value)) ? trim($value) : $value;
        $this->params[$field] = $value;
    }
	/**
	 * Parse the xml to get the necessary information
	 *
	 */
    function parseResults()
    {
	    $this->resultCode = self::substring_between($this->response, '<resultCode>', '</resultCode>');
	    $this->code = self::substring_between($this->response, '<code>', '</code>');
	    $this->text = self::substring_between($this->response, '<text>', '</text>');
	    $this->subscrId = self::substring_between($this->response, '<subscriptionId>', '</subscriptionId>');
    }


	public static function substring_between($haystack,$start,$end)
	{
		if (strpos($haystack,$start) === false || strpos($haystack,$end) === false)
		{
			return false;
		}
		else
		{
			$start_position = strpos($haystack,$start)+strlen($start);
			$end_position = strpos($haystack,$end);
			return substr($haystack,$start_position,$end_position-$start_position);
		}
	}

    function getSubscriberID()
    {
        return $this->subscrId;
    }

    function isSuccessful()
    {
        return $this->success;
    }

    function isError()
    {
        return $this->error;
    }
    /**
     * Processs payment 
     *
     * @param string $data
     * @return unknown
     */        
    function processRecurringPayment($row, $data)
    {
		$input = Factory::getApplication()->input;
		if($row->campaign_id > 0)
		{
			$db     = Factory::getDbo();
			$query  = $db->getQuery(true);
			$query->select('*')
				  ->from('#__jd_campaigns')
				  ->where('id='.$row->campaign_id);
			$db->setQuery($query);
			$campaign = $db->loadObject();
			if ($campaign->authorize_api_login && $campaign->authorize_transaction_key)
			{
				$this->login	= $campaign->authorize_api_login;
				$this->transkey = $campaign->authorize_transaction_key;
			}
		}
    	$app    = Factory::getApplication() ;
    	$Itemid       = $input->getInt('Itemid'); 
    	switch ($row->r_frequency)
        {
			case 'd':
				$length = 1 ;
				$unit = 'days';
				break ;
			case 'w' :
				$length = 7 ;
				$unit = 'days';
				break ;
			case 'm' :
				$length = 1 ;
				$unit = 'months' ;
				break ;
			case 'q' :
				$length = 3 ;
				$unit = 'months' ;
				break ;
			case 's' :
				$length = 6 ;
				$unit = 'months' ;
				break ;
			case 'a' :
				$length = 12 ;
				$unit = 'months';
				break ;					
		}    	
		$this->setParameter('refID', 'refID', $row->id . '-' . HTMLHelper::_('date', 'now', 'Y-m-d'));
		$this->setParameter('subscrName', $row->first_name . ' ' . $row->last_name);
		$this->setParameter('interval_length', $length);
		$this->setParameter('interval_unit', $unit);
		$this->setParameter('expirationDate', str_pad($data['exp_month'], 2, '0', STR_PAD_LEFT).'/'.substr($data['exp_year'],2 ,2 ));
		$this->setParameter('cardNumber', $data['x_card_num']);
		$this->setParameter('firstName', $row->first_name);
		$this->setParameter('lastName', $row->last_name );
		$this->setParameter('address', $row->address);
		$this->setParameter('city', $row->city);
		$this->setParameter('state', $row->state);
		$this->setParameter('zip', $row->zip);
		$this->setParameter('email', $row->email);
		$this->setParameter('amount', $data['gateway_amount']);
    	if (isset($data['r_times']) && $data['r_times'] > 2)
        {
			$totalOccurences = $data['r_times'] ;					
		}
        else
        {
			$totalOccurences = 9999 ;
		}
		$this->setParameter('totalOccurrences', $totalOccurences);		    
    	$this->createAccount();
    	if($this->success)
        {
    		$config					= DonationHelper::getConfig() ;
			$row->transaction_id	= $this->getSubscriberID();
			$row->subscr_id			= $this->getSubscriberID() ;
   			$row->payment_date		= date('Y-m-d H:i:s');
   			$row->published			= 1;
   			$row->store();		
   			
			PluginHelper::importPlugin('jdonation');
			Factory::getApplication()->triggerEvent('onAfterPaymentSuccess', array($row));
			DonationHelper::sendEmails($row, $config);
	        $app->redirect(Route::_(DonationHelperRoute::getDonationCompleteRoute($row->id, $row->campaign_id, $Itemid), false));
        	return true;
        }
        else
        {
        	$_SESSION['reason']		= $this->text ;
			Factory::getSession()->set('omnipay_payment_error_reason', $this->text);
	        $app->redirect(Route::_(DonationHelperRoute::getDonationFailureRoute($row->id, $row->campaign_id, $Itemid), false));
        	return false;
        }       
    } 
	/**
	 * Verify recurring payment
	 *
	 */
	function verifyRecurringPayment()
    {
    	$db = Factory::getDbo() ;
		$input = Factory::getApplication()->input;
    	$config = DonationHelper::getConfig() ;
    	$subscriptionId = $input->getString('x_subscription_id', '-1') ;
    	if ($subscriptionId != -1)
        {
    		$responseCode = $input->getString('x_response_code', '') ;
    		if ($responseCode == 1)
            {
    			$paymentNumber = $input->getInt('x_subscription_paynum', 0) ;
	    			if ($paymentNumber > 1)
                    {
                        $amount = $input->getString('x_amount', 0) ;
                        $sql = 'SELECT id FROM #__jd_donors WHERE subscr_id="'.$subscriptionId.'"';
                        $db->setQuery($sql) ;
                        $id = $db->loadResult();
                        if ($id)
                        {
                            $row =  Table::getInstance('jdonation', 'Table');
                            $row->load($id);
                            $row->amount = $row->amount + $amount ;
                            $row->payment_made = $paymentNumber ;
                            $row->store() ;
                            $input->set('receive_amount', $amount) ;
                            DonationHelper::sendRecurringEmail($row, $config);
                        }
    			}    			
    		}
    	}
    }
}
?>
