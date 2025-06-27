<?php
/**
 * @version        5.5.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Captcha\Captcha;

class DonationViewDonationHtml extends OSFViewHtml
{
	function display()
	{
		require_once JPATH_ROOT.'/components/com_jdonation/helper/integrations.php';
		require_once JPATH_ROOT.'/components/com_jdonation/helper/route.php';
		$document               = Factory::getApplication()->getDocument();
		$db						= Factory::getContainer()->get('db');
        $config                 = DonationHelper::getConfig();
		$bootstrapHelper		= new DonationHelperBootstrap($config->twitter_bootstrap_version);
		$campaignId             = $this->input->getInt('campaign_id', 0);
		$menus                  = Factory::getApplication()->getMenu();
		$menu                   = $menus->getActive();
		$option					= Factory::getApplication()->input->getString('option','');
		$view					= Factory::getApplication()->input->getString('view','');
		if($option == "com_content" && $view == "article")
		{
			$article_id			= Factory::getApplication()->input->getInt('id','');
			$db->setQuery("Select * from #__content where id = '$article_id'");
			$article			= $db->loadObject();
			if ($article->metadesc)
			{
				$document->setMetaData( "description", $article->metadesc); 
			}

			if ($article->metakey)
			{
				$document->setMetadata('keywords', $article->metakey);
			}
			$layout             = $config->default_layout;
			if($layout == 0)
			{
				$layout         = 'default';
			}
			elseif($layout == 1)
			{
				$layout         = 'simpleflow_layout';
			}
			elseif($layout == 2)
			{
				$layout 		= 'smart';
			}
		}
		elseif (is_object($menu))
		{
			$this->Itemid		= $menu->id;
			//$item = $menu->getItem($this->Itemid);
        
			if ($menu && $menu->component != 'com_jdonation')
			{
				$this->Itemid	= DonationHelperRoute::findItem();
			}
			//echo $this->Itemid;
	        $params             = new Registry() ;
	        //$params->loadString($menu->getParams());
			$params = $menu->getParams();
			if($campaignId == 0)
			{
				$meta_description   = $params->get('menu-meta_description','');
				if($meta_description != "")
				{
					$document->setMetaData( "description", $meta_description); 
				}
			}
            $layout             = $params->get('layout','');
			if($layout == '')
			{
                $layout         = $config->default_layout;
                if($layout == 0)
                {
                    $layout     = 'default';
                }
                elseif($layout == 1)
				{
					$layout         = 'simpleflow_layout';
				}
				elseif($layout == 2)
				{
					$layout 		= 'smart';
				}
			}
			else
            {
                if(!in_array($layout,array('default','simpleflow_layout','smart')))
                {
                    $layout     = $config->default_layout;
                    if($layout == 0)
                    {
                        $layout = 'default';
                    }
                    elseif($layout == 1)
					{
						$layout         = 'simpleflow_layout';
					}
					elseif($layout == 2)
					{
						$layout 		= 'smart';
					}
                }
            }
		}
		else
		{
		    $layout             = $config->default_layout;
            if($layout == 0)
            {
                $layout         = 'default';
            }
            elseif($layout == 1)
			{
				$layout         = 'simpleflow_layout';
			}
			elseif($layout == 2)
			{
				$layout 		= 'smart';
			}
        }

		//$layout = 'smart';

        if($layout != "")
        {
		    $this->setLayout($layout);
        }
		else
		{
			$this->setLayout('default');
		}
		$allowUserRegistration  = ComponentHelper::getParams('com_users')->get('allowUserRegistration');
		$db                     = Factory::getContainer()->get('db');
		$query                  = $db->getQuery(true);
		$user                   = Factory::getApplication()->getIdentity();
		$userId                 = $user->get('id');
        $input                  = $this->input;
		$rowFields              = DonationHelper::getFormFields();
		$captchaInvalid         = $input->getInt('captcha_invalid', 0);
		$fieldSuffix            = DonationHelper::getFieldSuffix();
		if ($fieldSuffix)
		{
			DonationHelper::getMultilingualConfigData($config, $fieldSuffix);
		}
		if ($captchaInvalid)
		{
			$data = $input->post->getData();
		}
		else
		{
			//Keep the data
			$app = Factory::getApplication();
			$data = $app->getUserState('com_jdonation.formdata', null);
			if ($data)
			{
				$data = unserialize($data);
			}
			else
			{
				$data = DonationHelper::getFormData($rowFields, $userId, $config, false, true);
			}
			
			// IN case there is no data, get it from URL (get for example)
			if (empty($data))
			{
				$data = $input->getData();
			}
		}
		if ($userId && !isset($data['first_name']))
		{
			//Load the name from Joomla default name
			$name = $user->name;
			if ($name)
			{
				$pos = strpos($name, ' ');
				if ($pos !== false)
				{
					$data['first_name'] = substr($name, 0, $pos);
					$data['last_name'] = substr($name, $pos + 1);
				}
				else
				{
					$data['first_name'] = $name;
					$data['last_name'] = '';
				}
			}
		}
		if ($userId && !isset($data['email']))
		{
			$data['email'] = $user->email;
		}
		if (!isset($data['country']) || !$data['country'])
		{
			$data['country'] = $config->default_country;
		}
		//Get data
		$form = new OSFForm($rowFields);
		if ($captchaInvalid)
		{
			$useDefault = false;
		}
		else
		{
			$useDefault = true;
		}
		$form->bind($data, $useDefault);
		$form->prepareFormField($this->input->getInt('campaign_id', 0));
		$showCampaignInformation = false;
		if ($campaignId > 0)
		{
			$query->clear();
			$query->select('*')
				->from('#__jd_campaigns')
				->where('id='.$campaignId);				
			if ($fieldSuffix)
			{
				if((int)$config->simple_language == 0)
				{
					$fields = array(
						'title',
						'description',
						'amounts_explanation',
						'donation_form_msg',
						'thanks_message'
					);
				}
				else
				{
					$fields = array(
						'title',
						'description'
					);
				}
				DonationHelper::getMultilingualFields($query, $fields, $fieldSuffix);
			}	
			$db->setQuery($query);
			$campaign = $db->loadObject();
			if($campaign->published == 0)
			{
				Factory::getApplication()->enqueueMessage(Text::_('JD_CAMPAIGN_UNAVAILABLE'));
				Factory::getApplication()->redirect(Uri::root());
			}
            if($campaign->show_campaign == -1 && $config->show_campaign == 1)
            {
                $showCampaignInformation = true;
            }
            elseif($campaign->show_campaign == 1)
            {
                $showCampaignInformation = true;
            }
            $campaign->showCampaignInformation = $showCampaignInformation;
			$this->campaign = $campaign;

			if($option == "com_jdonation")
			{
				if($campaign->use_parameter == 1 && is_object($menu))
				{
					$params = new Registry();
					$params->loadString($menu->getParams());

					if ($params->get('menu-meta_description')) 
					{
						$document->setDescription($params->get('menu-meta_description'));
					}

					if ($params->get('menu-meta_keywords')) 
					{
						$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
					}

					$page_title = $params->get('page_title', '');
					if ($page_title != "") 
					{
						$title = $page_title;
					} 
					elseif ($campaign->title != "") 
					{
						$title = $campaign->title;
					}
					$document->setTitle($title);
				}
				else
				{
					$document->setDescription($campaign->meta_description);
					$document->setMetadata('keywords',$campaign->meta_keywords);
					if($campaign->browser_page_title != "")
					{
						$title = $campaign->browser_page_title;
					}
					else
					{
						$title = $campaign->title;
					}
					$document->setTitle($title);
				}
			}
			
			$pathway = Factory::getApplication()->getPathway();
			$pathway->addItem($campaign->title, '');
			
			if($campaign->min_donation_amount > 0)
			{
				$this->minDonationAmount = (int) $campaign->min_donation_amount;
			}
			else
			{
				$this->minDonationAmount = (int) $config->minimum_donation_amount;
			}
			if($campaign->max_donation_amount > 0)
			{
				$this->maxDonationAmount = (int) $campaign->max_donation_amount;
			}
			else
			{
				$this->maxDonationAmount = (int) $config->maximum_donation_amount;
			}
		}
		
		$paymentMethod = $input->getString('payment_method', os_jdpayments::getDefautPaymentMethod($campaignId));
		$amount = $input->getFloat('amount', 0);
		if ($amount == 0)
		{
			$amount = '';
		}
		$rdAmount = $input->getFloat('rd_amount', 0);

		if(($amount == 0 || $amount == '') && $rdAmount > 0)
		{
			$amount = $rdAmount;
		}

		if($rdAmount > 0)
		{
			$paymentFeeAmount = 0;
			$paymentFeePercent = 0;
			if($paymentMethod != "")
			{
				$query->clear();
				$query->select('params')
						->from('#__jd_payment_plugins')
						->where('name=' . $db->quote($paymentMethod))
						->where('published = 1');
				$db->setQuery($query);
				$plugin = $db->loadObject();

				$params = new Registry($plugin->params);

				$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
				$paymentFeePercent = (float) $params->get('payment_fee_percent');
			}

			$pay_payment_gateway_fee = 1;

			if (($paymentFeeAmount != 0 || $paymentFeePercent != 0) && $pay_payment_gateway_fee == 1)
			{
				$payment_plugin_fee		= round($paymentFeeAmount + $rdAmount * $paymentFeePercent / 100, 2);
				$donated_amount			= round($rdAmount + $payment_plugin_fee, 2);
			}
			//no payment fee on Offline payment
			elseif ($config->convenience_fee && $pay_payment_gateway_fee == 1 && $paymentMethod != "os_offline")
			{
				$donated_amount			= round($rdAmount * (1 + $config->convenience_fee / 100), 2);
			}
			else
			{
				$donated_amount			= $rdAmount;
			}
		}

		if ($campaignId > 0)
		{
			if ($campaign->donation_type == 1)
			{
				$donationType = 'onetime';
			}
			elseif($campaign->donation_type == 2)
			{
				$donationType = 'recurring';
			}
			else
			{
				$donationType = $input->get('donation_type', 'onetime', 'string');
			}

			if($campaign->display_amount_textbox == 1)
			{
				$config->display_amount_textbox = 0;
			}
			elseif($campaign->display_amount_textbox == 2)
			{
				$config->display_amount_textbox == 1;
			}
		}
		else
		{
			$donationType = $input->get('donation_type', 'onetime', 'string');
		}
		//Campaigns selection dropdown
		$options = [] ;
		$options[] = HTMLHelper::_('select.option', '' , Text::_('JD_SELECT_CAMPAIGN') , 'id', 'title') ;
		$query->clear();
		$query->select('*')
			->from('#__jd_campaigns')
			->where('published = 1')
			->where('private_campaign = 0')
			//->where('(start_date = '.$db->quote($nullDate).' OR DATE(start_date) <= CURDATE())')
			//->where('(end_date = '.$db->quote($nullDate).' OR DATE(end_date) >= CURDATE())')
			->order('ordering');

		if ($fieldSuffix)
		{
			if((int)$config->simple_language == 0)
			{
				$fields = array(
					'title',
					'description',
					'amounts_explanation',
					'donation_form_msg',
					'thanks_message'
				);
			}
			else
			{
				$fields = array(
					'title',
					'description'
				);
			}
			DonationHelper::getMultilingualFields($query, $fields, $fieldSuffix);
		}
		$db->setQuery($query);
		$rowCampaigns = $db->loadObjectList();
		$options = array_merge($options, $rowCampaigns) ;
		$lists['campaign_id'] = HTMLHelper::_('select.genericlist', $options, 'campaign_id', ' class="input-large form-select validate[required]" onchange="processChangeCampaign();" ', 'id', 'title', $campaignId) ;
		if ($config->enable_recurring)
		{
			$options = [];
			$options[] = HTMLHelper::_('select.option', '',Text::_('JD_SELECT')) ;
			if (empty($config->recurring_frequencies))
			{
				$config->recurring_frequencies = 'd,w,m,q,s,a';
			}
			$recurringFrequencies = explode(',', $config->recurring_frequencies);
			if (in_array('d', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'd',Text::_('JD_DAILY'));
			}
			if (in_array('w', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'w',Text::_('JD_WEEKLY'));
			}
			if (in_array('b', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'b',Text::_('JD_BI_WEEKLY'));
			}
			if (in_array('m', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'm',Text::_('JD_MONTHLY'));
			}
			if (in_array('q', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'q',Text::_('JD_QUARTERLY'));
			}
			if (in_array('s', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 's',Text::_('JD_SEMI_ANNUALLY'));
			}
			if (in_array('a', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'a',Text::_('JD_ANNUALLY'));
			}
			$lists['r_frequency'] = HTMLHelper::_('select.genericlist', $options, 'r_frequency', ' class="'.$bootstrapHelper->getClassMapping('input-medium').' form-select validate[required]" ', 'value', 'text', $input->get('r_frequency', '')) ;

			$options = [];
			if (empty($config->recurring_frequencies))
			{
				$config->recurring_frequencies = 'd,w,m,q,s,a';
			}
			$recurringFrequencies = explode(',', $config->recurring_frequencies);

			if (in_array('d', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'd',Text::_('JD_DAILY'));
			}
			if (in_array('w', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'w',Text::_('JD_WEEKLY'));
			}
			if (in_array('b', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'b',Text::_('JD_BI_WEEKLY'));
			}
			if (in_array('m', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'm',Text::_('JD_MONTHLY'));
			}
			if (in_array('q', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'q',Text::_('JD_QUARTERLY'));
			}
			if (in_array('s', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 's',Text::_('JD_SEMI_ANNUALLY'));
			}
			if (in_array('a', $recurringFrequencies))
			{
				$options[] = HTMLHelper::_('select.option', 'a',Text::_('JD_ANNUALLY'));
			}
			$lists['r_frequency1'] = HTMLHelper::_('select.genericlist', $options, 'r_frequency', ' class="'.$bootstrapHelper->getClassMapping('input-medium').' form-select validate[required]" ', 'value', 'text', $input->get('r_frequency', '')) ;
			$this->recurringFrequencies = (array)$recurringFrequencies;
		}
		$this->recurring_require = 0;
		if($campaignId > 0)
        {
           if(($campaign->donation_type == 0 || $campaign->donation_type == 2) && $campaign->recurring_frequencies != "")
           {
               $options = [];
               $options[] = HTMLHelper::_('select.option', '',Text::_('JD_SELECT')) ;

               $recurringFrequencies = explode(',', $campaign->recurring_frequencies);
               if (in_array('d', $recurringFrequencies))
               {
                   $options[] = HTMLHelper::_('select.option', 'd',Text::_('JD_DAILY'));
               }
               if (in_array('w', $recurringFrequencies))
               {
                   $options[] = HTMLHelper::_('select.option', 'w',Text::_('JD_WEEKLY'));
               }
               if (in_array('b', $recurringFrequencies))
               {
                   $options[] = HTMLHelper::_('select.option', 'b',Text::_('JD_BI_WEEKLY'));
               }
               if (in_array('m', $recurringFrequencies))
               {
                   $options[] = HTMLHelper::_('select.option', 'm',Text::_('JD_MONTHLY'));
               }
               if (in_array('q', $recurringFrequencies))
               {
                   $options[] = HTMLHelper::_('select.option', 'q',Text::_('JD_QUARTERLY'));
               }
               if (in_array('s', $recurringFrequencies))
               {
                   $options[] = HTMLHelper::_('select.option', 's',Text::_('JD_SEMI_ANNUALLY'));
               }
               if (in_array('a', $recurringFrequencies))
               {
                   $options[] = HTMLHelper::_('select.option', 'a',Text::_('JD_ANNUALLY'));
               }
               $lists['r_frequency'] = HTMLHelper::_('select.genericlist', $options, 'r_frequency', ' class="'.$bootstrapHelper->getClassMapping('input-medium').' form-select validate[required]" ', 'value', 'text', $input->get('r_frequency', '')) ;
			   if($campaign->donation_type == 2)
			   {
				   $additionalCss = "validate[required]";
				   $this->recurring_require = 1;
			   }
				
			   $lists['r_frequency1'] = HTMLHelper::_('select.genericlist', $options, 'r_frequency', ' class="'.$bootstrapHelper->getClassMapping('input-medium').' form-select '.$additionalCss.'" ', 'value', 'text', $input->get('r_frequency', '')) ;
               $this->recurringFrequencies = (array) $recurringFrequencies;
           }
        }
		$options = [];
		$options[] = HTMLHelper::_('select.option', 'onetime', Text::_('JD_ONETIME')) ;
		$options[] = HTMLHelper::_('select.option', 'recurring', Text::_('JD_RECURRING')) ;

		$lists['donation_type'] = HTMLHelper::_('select.radiolist', $options, 'donation_type', ' class="form-select" onclick="changeDonationType();" ', 'value', 'text', $donationType);

		if ($config->amount_by_campaign)
		{
			//For campaigns which doesn't have it own pre-defined amount and pre-defined amount explanation, we just use data from Configuration
			for ($i = 0 , $n = count($rowCampaigns) ; $i < $n ; $i++)
			{
				$rowCampaign = $rowCampaigns[$i] ;
				if (!$rowCampaign->amounts)
				{
					$rowCampaign->amounts = $config->donation_amounts;
				}
				if (!$rowCampaign->amounts_explanation)
				{
					$rowCampaign->amounts_explanation = $config->donation_amounts_explanation;
				}
				if($rowCampaign->show_amounts == 0)
				{
					$rowCampaign->amounts = '';
				}
			}

			if ($campaignId > 0 && $campaign->amounts && $campaign->show_amounts == 1)
			{
				$config->donation_amounts = $campaign->amounts ;
				$config->donation_amounts_explanation = $campaign->amounts_explanation ;
			}
			else
			{
				$config->donation_amounts = '';
			}
		}

		$options   =  [] ;
		$options[] = HTMLHelper::_('select.option', 'Visa', 'Visa') ;
		$options[] = HTMLHelper::_('select.option', 'MasterCard', 'MasterCard') ;
		$options[] = HTMLHelper::_('select.option', 'Discover', 'Discover') ;
		$options[] = HTMLHelper::_('select.option', 'Amex', 'American Express') ;
		$lists['card_type'] = HTMLHelper::_('select.genericlist', $options, 'card_type', ' class="input-medium form-select" ', 'value', 'text') ;
		//Expiration month, expiration year
		$lists['exp_month'] = HTMLHelper::_('select.integerlist', 1, 12, 1, 'exp_month', ' class="input-small form-select" ', $input->getInt('exp_month', date('m')), '%02d') ;
		$currentYear = date('Y');
		$lists['exp_year'] = HTMLHelper::_('select.integerlist', $currentYear, $currentYear + 10, 1, 'exp_year', 'class="input-small form-select"', $input->getInt('exp_year', date('Y')));
		//Support disable recurring for each campaign
		$recurringString = "var recurrings = new Array();\n";
		for ($i = 0 , $n = count($rowCampaigns) ; $i < $n ; $i++)
		{
			$row = $rowCampaigns[$i] ;
			$recurringString.= " recurrings[$row->id] = ".(int)$row->donation_type.";\n" ;
		}

		//Allow donors to choose to pay for payment gateway fee
		if ($config->pay_payment_gateway_fee)
		{
			$lists['pay_payment_gateway_fee'] = HTMLHelper::_('select.booleanlist', 'pay_payment_gateway_fee', ' class="inputbox" ', $input->getInt('pay_payment_gateway_fee', 1)) ;
		}
		//Allow users choose to pay for paypal currencies

		if ($config->currency_selection)
		{
			$query->clear();
			$query->select('currency_code,  currency_name')
				->from('#__jd_currencies');
			if ($config->active_currencies)
			{
				$activeCurrencies = explode(',', $config->active_currencies) ;
				if(count($activeCurrencies) == 1)
				{
					$config->currency_selection = false;
				}
				$query->where('currency_code IN ("'.implode('","', $activeCurrencies).'")');
			}
			$db->setQuery($query) ;
			$options = [];
			$options[] = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_CURRENCY'), 'currency_code', 'currency_name') ;
			$options = array_merge($options, $db->loadObjectList()) ;
			$lists['currency_code'] =  HTMLHelper::_('select.genericlist', $options, 'currency_code', ' class="input-large form-select" style="width:300px;" onChange="updateSummary();"', 'currency_code', 'currency_name', $input->get('currency_code', '', 'none')) ;
		}
		$methods   = os_jdpayments::getPaymentMethods($campaignId);
		$method    = os_jdpayments::getPaymentMethod($paymentMethod, $campaignId) ;

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'Visa', 'Visa') ;
		$options[] = HTMLHelper::_('select.option', 'MasterCard', 'MasterCard') ;
		$options[] = HTMLHelper::_('select.option', 'Discover', 'Discover') ;
		$options[] = HTMLHelper::_('select.option', 'Amex', 'American Express') ;
		$lists['card_type'] = HTMLHelper::_('select.genericlist', $options, 'card_type', ' class="inputbox" ', 'value', 'text') ;

		if (DonationHelper::isPaymentMethodEnabled('os_echeck'))
		{
			$options  = [];
			$options[] = HTMLHelper::_('select.option', 'CHECKING', Text::_('JD_BANK_TYPE_CHECKING')) ;
			$options[] = HTMLHelper::_('select.option', 'BUSINESSCHECKING', Text::_('JD_BANK_TYPE_BUSINESSCHECKING')) ;
			$options[] = HTMLHelper::_('select.option', 'SAVINGS', Text::_('JD_BANK_TYPE_SAVINGS')) ;
			$lists['x_bank_acct_type'] = HTMLHelper::_('select.genericlist', $options, 'x_bank_acct_type', ' class="inputbox" ', 'value', 'text', $input->get('x_bank_acct_type', '', 'none')) ;
		}

		if ($campaignId > 0 && !$captchaInvalid)
		{
			$this->showCampaignSelection = false;
		}
		else
		{
			$this->showCampaignSelection = true;
		}

		//Captcha integration
		$showCaptcha = 0;
		if ($config->enable_captcha)
		{
			if($config->use_jd_captcha == 1)
			{
				$captchaPlugin = 'jdcaptcha';
			}
			else
			{
				$captchaPlugin = Factory::getConfig()->get('captcha');
			}
			if (!$captchaPlugin)
			{
				// Hardcode to recaptcha, reduce support request
				$captchaPlugin = 'recaptcha';
			}

			if ($captchaPlugin)
			{
				$showCaptcha        = 1;

				$plugin				= PluginHelper::getPlugin('captcha', $captchaPlugin);

				if ($plugin)
				{
					$this->showCaptcha = true;
					$this->captcha = Captcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required');
					
				}
				else
				{
					Factory::getApplication()->enqueueMessage(Text::_('JD_CAPTCHA_NOT_ACTIVATED_IN_YOUR_SITE'), 'error');
				}

				$this->captchaPlugin = $captchaPlugin;
			}
		}

		$contentPlugin = $this->input->getInt('content_plugin', 0);
		if ($contentPlugin)
		{
			$donationPageUrl        = base64_encode(Uri::getInstance()->toString());
		}
		else
		{
			$donationPageUrl        = '';
		}

		$this->show_dedicate        = 0;
		if($config->activate_tributes)
		{
            if($campaignId > 0)
            {
                if($campaign->activate_dedicate == 1)
                {
                    $this->show_dedicate = 1;
                }
            }
			else
			{
                $this->show_dedicate = 1;
            }
        }
		$this->form                 = $form;
		//Assign these parameters
		if((int) $this->Itemid == 0)
		{
			$this->Itemid			= DonationHelperRoute::findItem();
		}
		$this->userId               = $userId;
		$this->paymentMethod        = $paymentMethod;
		$this->lists                = $lists;
		$this->config               = $config;
        $this->bootstrapHelper      = $bootstrapHelper;
		$this->amount               = $amount;
		$this->rdAmount             = $rdAmount;
		$this->donationType         = $donationType;
		$this->campaignId           = $campaignId;
		$this->recurringString      = $recurringString;
		$this->methods              = (array)$methods;
		$this->method               = $method;
		$this->rowCampaigns         = $rowCampaigns;
		$this->captchaInvalid       = $captchaInvalid;
		$this->showCaptcha          = $showCaptcha;
		$this->donationPageUrl      = $donationPageUrl;
		$this->allowUserRegistration = $allowUserRegistration;
		$this->campaign				= $campaign;
		$this->donationType			= $donationType;
		$this->option				= $option;
		$this->contentPlugin		= $contentPlugin;
		$this->donated_amount		= $donated_amount;
		
		parent::display();
	}
}
