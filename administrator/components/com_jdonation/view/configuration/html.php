<?php
/**
 * @version        5.4.9
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die ();
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\Filesystem\Folder;
use DrewM\MailChimp\MailChimp;

class DonationViewConfigurationHtml extends OSFViewHtml
{
    public static function showCheckboxfield($name, $value ,$option1='',$option2='')
    {
        if($option1 == ""){
            $option1 = Text::_('JNO');
        }
        if($option2 == ""){
            $option2 = Text::_('JYES');
        }

        HTMLHelper::_('jquery.framework');
        $field = FormHelper::loadFieldType('Radio');

        $element = new SimpleXMLElement('<field />');
        $element->addAttribute('name', $name);

        if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
        {
            $element->addAttribute('layout', 'joomla.form.field.radio.switcher');
        }
        else
        {
            $element->addAttribute('class', 'radio btn-group btn-group-yesno');
        }

        $element->addAttribute('default', '0');

        $node = $element->addChild('option', $option1);
        $node->addAttribute('value', '0');

        $node = $element->addChild('option', $option2);
        $node->addAttribute('value', '1');

        $field->setup($element, $value);

        return $field->input;
    }

	public function display()
	{
        if (!DonationHelper::isJoomla4()) 
		{
			HTMLHelper::_('formbehavior.chosen', 'select');
		}
		$db         = Factory::getContainer()->get('db');
		$query      = $db->getQuery(true);
		$config     = $this->model->getData();
		$options    = [];
		$options [] = HTMLHelper::_('select.option', 0, Text::_('No integration'));
		if (file_exists(JPATH_ROOT . '/components/com_comprofiler/comprofiler.php'))
		{
			$options[] = HTMLHelper::_('select.option', 1, Text::_('Community Builder'));
		}
		if (file_exists(JPATH_ROOT . '/components/com_community/community.php'))
		{
			$options[] = HTMLHelper::_('select.option', 2, Text::_('JomSocial'));
		}
		if (PluginHelper::isEnabled('user', 'profile'))
		{
			$options[] = HTMLHelper::_('select.option', 3, Text::_('Joomla Profile'));
		}
        if (file_exists(JPATH_ROOT . '/components/com_easysocial/easysocial.php'))
        {
            $options[] = HTMLHelper::_('select.option', 4, Text::_('Easy Social'));
        }
        if (file_exists(JPATH_ROOT . '/components/com_jsn/jsn.php'))
        {
            $options[] = HTMLHelper::_('select.option', 5, Text::_('Easy Profile'));
        }

		$lists ['cb_integration']                    = HTMLHelper::_('select.genericlist', $options, 'cb_integration', 'class="form-select input-large ilarge"', 'value', 'text', (int)$config->cb_integration);
		//$lists ['registration_integration']          = self::showCheckboxfield('registration_integration',$config->registration_integration);

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 4, Text::_('JD_VERSION_4'));
		$options[] = HTMLHelper::_('select.option', 5, Text::_('JD_VERSION_5'));
		$options[] = HTMLHelper::_('select.option', 6, Text::_('JD_UIKIT3'));

		$lists['twitter_bootstrap_version'] = HTMLHelper::_('select.genericlist', $options, 'twitter_bootstrap_version', 'class="form-select input-large ilarge"', 'value', 'text', $config->twitter_bootstrap_version ? $config->twitter_bootstrap_version : 2);

		$options = [];
		$options[] = HTMLHelper::_('select.option','0',Text::_('JNO'));
		$options[] = HTMLHelper::_('select.option','1',Text::_('JYES'));
		$options[] = HTMLHelper::_('select.option','2',Text::_('JYES').' - '.Text::_('JOPTION_OPTIONAL'));
		$lists ['registration_integration']                    = HTMLHelper::_('select.genericlist', $options, 'registration_integration', 'class="form-select input-large ilarge"', 'value', 'text', (int)$config->registration_integration);

		$query->clear();
		$query->select('*')->from('#__extensions')->where('`type`= "plugin"')->where('`element`= "jdcaptcha"')->where('`folder`= "captcha"')->where('enabled = "1"');
		$db->setQuery($query);
		$jdcaptcha									 = $db->loadObject();
		$lists['jdcaptcha']							 = $jdcaptcha;
		if($jdcaptcha->extension_id > 0)
		{
			$lists ['use_jd_captcha']                = self::showCheckboxfield('use_jd_captcha',(int)$config->use_jd_captcha);
		}

		$lists ['show_login_box']                    = self::showCheckboxfield('show_login_box',(int)$config->show_login_box);
		$lists ['enable_gift_aid']                   = self::showCheckboxfield('enable_gift_aid',(int)$config->enable_gift_aid);
		$lists ['use_campaign']                      = self::showCheckboxfield('use_campaign',(int)$config->use_campaign);
		$lists ['enable_recurring']                  = self::showCheckboxfield('enable_recurring',(int)$config->enable_recurring);
        $lists ['enable_cancel_recurring']           = self::showCheckboxfield('enable_cancel_recurring',(int)$config->enable_cancel_recurring);
		$lists ['use_https']                         = self::showCheckboxfield('use_https',(int)$config->use_https);
		$lists ['enable_hide_donor']                 = self::showCheckboxfield('enable_hide_donor',(int)$config->enable_hide_donor);
		$lists ['activate_donation_receipt_feature'] = self::showCheckboxfield('activate_donation_receipt_feature',(int)$config->activate_donation_receipt_feature);// JHtml::_('select.booleanlist', 'activate_donation_receipt_feature', '', $config->activate_donation_receipt_feature);
		$lists ['send_receipt_via_email']            = self::showCheckboxfield('send_receipt_via_email',(int)$config->send_receipt_via_email); //JHtml::_('select.booleanlist', 'send_receipt_via_email', '', $config->send_receipt_via_email);
        $lists ['generated_invoice_for_paid_donation_only'] = self::showCheckboxfield('generated_invoice_for_paid_donation_only',(int)$config->generated_invoice_for_paid_donation_only);
		$lists ['invoice_readonly']					 = self::showCheckboxfield('invoice_readonly',(int)$config->invoice_readonly);
        $lists ['reset_invoice_number']              = self::showCheckboxfield('reset_invoice_number',(int)$config->reset_invoice_number);
		$lists ['send_receipt_to_admin']             = self::showCheckboxfield('send_receipt_to_admin',(int)$config->send_receipt_to_admin); 
		$lists ['show_r_times']                      = self::showCheckboxfield('show_r_times',(int)$config->show_r_times); //JHtml::_('select.booleanlist', 'show_r_times', '', $config->show_r_times);
		$lists ['create_account_when_donation_active']                      = self::showCheckboxfield('create_account_when_donation_active',(int)$config->create_account_when_donation_active); 
		$lists ['pay_payment_gateway_fee']           = self::showCheckboxfield('pay_payment_gateway_fee',(int)$config->pay_payment_gateway_fee); //JHtml::_('select.booleanlist', 'pay_payment_gateway_fee', '', $config->pay_payment_gateway_fee);
		$lists ['currency_selection']                = self::showCheckboxfield('currency_selection',(int)$config->currency_selection); 
		//JHtml::_('select.booleanlist', 'currency_selection', '', $config->currency_selection);

		$lists ['activate_campaign_currency']                 = self::showCheckboxfield('activate_campaign_currency',(int)$config->activate_campaign_currency);

		$lists ['show_pending_records']              = self::showCheckboxfield('show_pending_records',(int)$config->show_pending_records); //JHtml::_('select.booleanlist', 'show_pending_records', '', $config->show_pending_records);
		$lists ['currency_space']					 = self::showCheckboxfield('currency_space',(int)$config->currency_space); 

		$lists ['send_attachment_to_admin_email']    = self::showCheckboxfield('send_attachment_to_admin_email',(int)$config->send_attachment_to_admin_email); //JHtml::_('select.booleanlist', 'send_attachment_to_admin_email', '', $config->send_attachment_to_admin_email);
        $lists ['show_privacy']                      = self::showCheckboxfield('show_privacy',(int)$config->show_privacy);
        $lists ['store_ip_address']                  = self::showCheckboxfield('store_ip_address',(int)$config->store_ip_address);

		$lists ['activate_form_floating']            = self::showCheckboxfield('activate_form_floating',(int)$config->activate_form_floating);

		$lists['social_sharing']                     = self::showCheckboxfield('social_sharing',(int)$config->social_sharing);
		$lists['social_sharing_type']                = self::showCheckboxfield('social_sharing_type',(int)$config->social_sharing_type,Text::_('JD_ADDTHIS'),Text::_('JD_NATIVE'));

        $lists ['activate_tributes']                 = self::showCheckboxfield('activate_tributes',(int)$config->activate_tributes);
		$lists ['add_honoree_in_csv']                = self::showCheckboxfield('add_honoree_in_csv',(int)$config->add_honoree_in_csv);
        $lists ['send_email_to_honoree']             = self::showCheckboxfield('send_email_to_honoree',(int)$config->send_email_to_honoree);

        $lists ['export_donors']                     = self::showCheckboxfield('export_donors',(int)$config->export_donors);
		$lists['layout_type']						 = self::showCheckboxfield('layout_type',(int)$config->layout_type,Text::_('JD_BRIGHT_LAYOUT'),Text::_('JD_DARK_LAYOUT'));

        $optionArr									 = [];
		$optionArr[]							     = HTMLHelper::_('select.option', 0, Text::_('JD_DEFAULT_LAYOUT'));
		$optionArr[]							     = HTMLHelper::_('select.option', 1, Text::_('JD_SIMPLE_DONATION_LAYOUT'));
		$optionArr[]							     = HTMLHelper::_('select.option', 2, Text::_('JD_MULTI_STEPS_LAYOUT'));

		$lists['default_layout']                     = HTMLHelper::_('select.genericlist',$optionArr, 'default_layout','class="form-select"','value','text',(int)$config->default_layout);
        $lists['auto_approval_campaign']             = self::showCheckboxfield('auto_approval_campaign',(int)$config->auto_approval_campaign);

        $lists['show_brackets']                      = self::showCheckboxfield('show_brackets',(int)$config->show_brackets);

		$lists['show_update_available_message_in_dashboard']                      = self::showCheckboxfield('show_update_available_message_in_dashboard',(int)$config->show_update_available_message_in_dashboard);
		

		$currencies = require_once JPATH_ROOT . '/components/com_jdonation/helper/currencies.php';
		$options    = [];
		$options[]  = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_CURRENCY'));
		foreach ($currencies as $code => $title)
		{
			$options[] = HTMLHelper::_('select.option', $code, $title);
		}
		$lists['currency']           = HTMLHelper::_('select.genericlist', $options, 'currency', 'class="form-select input-large ilarge"', 'value', 'text', isset($config->currency) ? $config->currency : 'USD');

		array_shift($options);
		$lists ['active_currencies'] = HTMLHelper::_('select.genericlist', $options, 'active_currencies[]', 'class="form-select ilarge" multiple="true"', 'value', 'text', explode(',', $config->active_currencies));

		$options    = [];
		$options [] = HTMLHelper::_('select.option', 'd', Text::_('JD_DAILY'));
		$options [] = HTMLHelper::_('select.option', 'w', Text::_('JD_WEEKLY'));
		$options [] = HTMLHelper::_('select.option', 'b', Text::_('JD_BI_WEEKLY'));
		$options [] = HTMLHelper::_('select.option', 'm', Text::_('JD_MONTHLY'));
		$options [] = HTMLHelper::_('select.option', 'q', Text::_('JD_QUARTERLY'));
		$options [] = HTMLHelper::_('select.option', 's', Text::_('JD_SEMI_ANNUALLY'));
		$options [] = HTMLHelper::_('select.option', 'a', Text::_('JD_ANNUALLY'));

		$selecteds   = [];
		$frequencies = explode(',', $config->recurring_frequencies);
		for ($i = 0, $n = count($frequencies); $i < $n; $i++)
		{
			$selecteds [] = HTMLHelper::_('select.option', $frequencies [$i], $frequencies [$i]);
		}
		$lists ['recurring_frequencies'] = HTMLHelper::_('select.genericlist', $options, 'recurring_frequencies[]', ' class="inputbox ilarge form-select" size="5" multiple="multiple" ', 'value', 'text', $selecteds);
		$lists ['amount_by_campaign']    = self::showCheckboxfield('amount_by_campaign',$config->amount_by_campaign);

		// Format of the pre-defined amount
		$options                  = [];
		$options []               = HTMLHelper::_('select.option', 0, 'Select Format');
		$options []               = HTMLHelper::_('select.option', 1, Text::_('Radio list'));
		$options []               = HTMLHelper::_('select.option', 2, Text::_('Dropdown'));
		$lists ['amounts_format'] = self::showCheckboxfield('amounts_format',(int)$config->amounts_format); //JHtml::_('select.genericlist', $options, 'amounts_format', ' class="inputbox" ', 'value', 'text', $config->amounts_format);

		$sql = 'SELECT id, title FROM #__content';
		$db->setQuery($sql);
		$rows                  = $db->loadObjectList();
		$options               = [];
		$options []            = HTMLHelper::_('select.option', 0, Text::_('Select article'), 'id', 'title');
		$options               = array_merge($options, $rows);
		$lists ['article_id']  = HTMLHelper::_('select.genericlist', $options, 'article_id', ' class="form-select input-large ilarge" ', 'id', 'title', $config->article_id);
		$lists ['active_term'] = self::showCheckboxfield('accept_term',(int)$config->accept_term);//JHtml::_('select.booleanlist', 'accept_term', '', $config->accept_term);

		$lists ['field_campaign'] = self::showCheckboxfield('field_campaign',(int)$config->field_campaign);//JHtml::_('select.booleanlist', 'field_campaign', '', $config->field_campaign);

		$lists ['enable_captcha'] = self::showCheckboxfield('enable_captcha',(int)$config->enable_captcha); //JHtml::_('select.booleanlist', 'enable_captcha', '', $config->enable_captcha);
        $lists ['enable_captcha_with_public_user'] = self::showCheckboxfield('enable_captcha_with_public_user',(int)$config->enable_captcha_with_public_user);

		$lists ['convert_currency_before_donation'] = self::showCheckboxfield('convert_currency_before_donation',(int)$config->convert_currency_before_donation);

        $options                          = [];
		$options []                       = HTMLHelper::_('select.option', 0, Text::_('Dropdown'));
		$options []                       = HTMLHelper::_('select.option', 1, Text::_('Radio list'));
		$lists ['amounts_format']         = HTMLHelper::_('select.genericlist', $options, 'amounts_format', ' class="form-select input-large ilarge" ', 'value', 'text', $config->amounts_format);
		$lists ['display_amount_textbox'] = self::showCheckboxfield('display_amount_textbox',$config->display_amount_textbox); //JHtml::_('select.booleanlist', 'display_amount_textbox', '', $config->display_amount_textbox);

		$lists ['populate_from_previous_donation'] = self::showCheckboxfield('populate_from_previous_donation',(int)$config->populate_from_previous_donation); 

		// Get list of country
		$sql = 'SELECT name AS value, name AS text FROM #__jd_countries WHERE published=1';
		$db->setQuery($sql);
		$rowCountries           = $db->loadObjectList();
		$options                = [];
		$options []             = HTMLHelper::_('select.option', '', Text::_('Select default country'));
		$options                = array_merge($options, $rowCountries);
		$lists ['country_list'] = HTMLHelper::_('select.genericlist', $options, 'default_country', 'class="form-select input-large ilarge"', 'value', 'text', $config->default_country);

		$options                          = [];
		$options []                       = HTMLHelper::_('select.option', 0, Text::_('Site administrator'));
		$options []                       = HTMLHelper::_('select.option', 1, Text::_('Member. Merchant account will be get from CB profile'));
		$options []                       = HTMLHelper::_('select.option', 2, Text::_('Member. Merchant account will be get from JomSocial profile'));
		$lists ['payment_to']             = self::showCheckboxfield('payment_to',$config->payment_to);//JHtml::_('select.genericlist', $options, 'payment_to', ' class="inputbox" ', 'value', 'text', $config->payment_to);
		$lists ['load_twitter_bootstrap'] = self::showCheckboxfield('load_twitter_bootstrap',(int)$config->load_twitter_bootstrap);//JHtml::_('select.booleanlist', 'load_twitter_bootstrap', '', isset ($config->load_twitter_bootstrap) ? $config->load_twitter_bootstrap : 1);
		$lists ['load_jquery']            = self::showCheckboxfield('load_jquery',(int)$config->load_jquery);//JHtml::_('select.booleanlist', 'load_jquery', '', isset ($config->load_jquery) ? $config->load_jquery : 1);
		$lists ['show_campaign_progress'] = self::showCheckboxfield('show_campaign_progress',(int)$config->show_campaign_progress);
		$lists ['show_campaign_picture']  = self::showCheckboxfield('show_campaign_picture',(int)$config->show_campaign_picture);
        $lists ['endable_donation_with_expired_campaigns'] = self::showCheckboxfield('endable_donation_with_expired_campaigns',(int)$config->endable_donation_with_expired_campaigns);
        $lists ['endable_donation_with_goal_achieved_campaigns'] = self::showCheckboxfield('endable_donation_with_goal_achieved_campaigns',(int)$config->endable_donation_with_goal_achieved_campaigns);
        $lists ['show_newsletter_subscription'] = self::showCheckboxfield('show_newsletter_subscription',(int)$config->show_newsletter_subscription);
        $lists ['show_campaign']       = self::showCheckboxfield('show_campaign',(int)$config->show_campaign);
		
		$lists ['simple_language']     = self::showCheckboxfield('simple_language',(int)$config->simple_language);

        $lists ['include_payment_fee'] = self::showCheckboxfield('include_payment_fee',(int)$config->include_payment_fee);

		$lists ['qr_code']			   = self::showCheckboxfield('qr_code',(int)$config->qr_code);

		$lists ['log_emails']          = self::showCheckboxfield('log_emails',(int)$config->log_emails);
		$lists ['activate_campaign_sharing']          = self::showCheckboxfield('activate_campaign_sharing',(int)$config->activate_campaign_sharing);

		$options                     = [];
		$options []                  = HTMLHelper::_('select.option', '', Text::_('Select position'));
		$options []                  = HTMLHelper::_('select.option', 0, Text::_('Before amount'));
		$options []                  = HTMLHelper::_('select.option', 1, Text::_('After amount'));
		$lists ['currency_position'] = HTMLHelper::_('select.genericlist', $options, 'currency_position', ' class="form-select input-large ilarge"', 'value', 'text', (int)$config->currency_position);

		if(DonationHelper::isMailchimpPluginEnabled())
		{
            require_once JPATH_ROOT . '/plugins/jdonation/mailchimp/api/MailChimp.php';

            $query->clear();
            $query->select('*')->from('#__extensions')->where('`type`= "plugin"')->where('`element`= "mailchimp"')->where('`folder`= "jdonation"')->where('enabled = "1"');
            $db->setQuery($query);
            $plugin     = $db->loadObject();
            $params     = new Registry($plugin->params);
            //$params->loadString($plugin->params);
            
			try
			{
				$mailchimp = new MailChimp($params->get('api_key', ''));
			}
			catch (Exception $e)
			{
				echo $e->getMessage();

				//die();
			}

			$mlists		= $mailchimp->get('lists', ['count' => 1000]);
            //$mlists     = $mailchimp->call('lists/list');
            if($config->mailchimp_list_ids != '')
			{
                $listIds = explode(',', $config->mailchimp_list_ids);
            }
			else 
			{
                $listIds = explode(',', $params->get('default_list_ids', ''));
            }

            $options    = [];
            $mlists     = (array)$mlists['lists'];
            if (count($mlists))
            {
                foreach ($mlists as $list)
                {
                    $options[] = HTMLHelper::_('select.option', $list['id'], $list['name']);
                }
            }
            $lists['mailchimp_list'] = HTMLHelper::_('select.genericlist', $options, 'mailchimp_list_ids[]', 'class="form-select" multiple="multiple" size="10"', 'value', 'text', $listIds);
        }

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'not_showing', Text::_('JD_NOT_SHOWING'));
		$options[] = HTMLHelper::_('select.option', 'under_field', Text::_('JD_UNDER_FIELD'));
		$options[] = HTMLHelper::_('select.option', 'above_field', Text::_('JD_ABOVE_FIELD'));

		$lists['display_field_description'] = HTMLHelper::_('select.genericlist', $options, 'display_field_description', 'class="input-large ilarge form-select"', 'value', 'text', $config->display_field_description);

		$fontsPath = JPATH_ROOT . '/components/com_jdonation/tcpdf/fonts/';
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_FONT'));
		$options[] = HTMLHelper::_('select.option', 'courier', Text::_('Courier'));
		$options[] = HTMLHelper::_('select.option', 'helvetica', Text::_('Helvetica'));
		$options[] = HTMLHelper::_('select.option', 'symbol', Text::_('Symbol'));
		$options[] = HTMLHelper::_('select.option', 'times', Text::_('Times New Roman'));
		$options[] = HTMLHelper::_('select.option', 'zapfdingbats', Text::_('Zapf Dingbats'));

		$additionalFonts = [
			'aealarabiya',
			'aefurat',
			'dejavusans',
			'dejavuserif',
			'freemono',
			'freesans',
			'freeserif',
			'hysmyeongjostdmedium',
			'kozgopromedium',
			'kozminproregular',
			'msungstdlight',
			'opensans',
			'cid0jp',
			'DroidSansFallback',
			'PFBeauSansProthin',
			'PFBeauSansPro',
			'roboto',
			'consolateelfb',
			'ubuntu',
			'tantular',
			'anonymouspro',
		];

		foreach ($additionalFonts as $fontName)
		{
			if (file_exists($fontsPath . $fontName . '.php'))
			{
				$options[] = HTMLHelper::_('select.option', $fontName, ucfirst($fontName));
			}
		}

		// Support True Type Font
		$trueTypeFonts = Folder::files($fontsPath, '.ttf');

		foreach ($trueTypeFonts as $trueTypeFont)
		{
			$options[] = HTMLHelper::_('select.option', $trueTypeFont, $trueTypeFont);
		}

		$lists['pdf_font'] = HTMLHelper::_('select.genericlist', $options, 'pdf_font', ' class="input-large form-select"', 'value', 'text', empty($config->pdf_font) ? 'times' : $config->pdf_font);

		if($config->dedicate_type == '')
		{
			$dedicateTypeArray = array('1','2','3','4');
		}
		else
		{
			$dedicateTypeArray = explode(",", $config->dedicate_type);
		}

		$optionArr = [];
		$optionArr[] = HTMLHelper::_('select.option','1', Text::_('JD_IN_HONOR_OF'));
		$optionArr[] = HTMLHelper::_('select.option','2', Text::_('JD_IN_MEMORY_OF'));
		$optionArr[] = HTMLHelper::_('select.option','3', Text::_('JD_IN_DEDICATE_TO'));
		$optionArr[] = HTMLHelper::_('select.option','4', Text::_('JD_IN_REMEMBRANCE_OF'));
		$lists['dedicate_type'] = HTMLHelper::_('select.genericlist', $optionArr, 'dedicate_type[]','class="form-select ilarge" multiple','value','text',$dedicateTypeArray);

		$optionArr = [];
		$optionArr[] = HTMLHelper::_('select.option', '', Text::_('JD_SHOW_PAYMENT_METHODS'));
		$optionArr[] = HTMLHelper::_('select.option', '0', Text::_('JD_SHOW_PAYMENT_LOGO'));
		$optionArr[] = HTMLHelper::_('select.option', '1', Text::_('JD_SHOW_PAYMENT_TITLE'));
		$optionArr[] = HTMLHelper::_('select.option', '2', Text::_('JD_SHOW_PAYMENT_LOGO_AND_TITLE'));
		$lists['show_payment_method'] = HTMLHelper::_('select.genericlist', $optionArr, 'show_payment_method','class="input-large ilarge form-select"','value','text',(int)$config->show_payment_method);

		$this->lists  = $lists;
		$this->config = $config;

		DonationHelperHtml::renderSubmenu('configuration');
		$this->bootstrapHelper		= new DonationHelperBootstrap($config->twitter_bootstrap_version);
		parent::display();
	}
}
