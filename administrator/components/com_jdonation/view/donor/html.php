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
defined('_JEXEC') or die ();
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class DonationViewDonorHtml extends OSFViewItem
{
	protected function prepareView()
	{
		parent::prepareView();
		$config = DonationHelper::getConfig();
		$db     = Factory::getContainer()->get('db');
		$query  = $db->getQuery(true);
		//Build campaign selection
		$query->select('id, title')
			->from('#__jd_campaigns')
			->order('title');
		$db->setQuery($query);
		$options                     = array();
		$options []                  = HTMLHelper::_('select.option', 0, Text::_('JD_SELECT_CAMPAIGN'), 'id', 'title');
		$options                     = array_merge($options, $db->loadObjectList());
		$this->lists ['campaign_id'] = HTMLHelper::_('select.genericlist', $options, 'campaign_id', 'class="input-large form-select"', 'id', 'title', $this->item->campaign_id);
		$this->lists ['hide_me']     = HTMLHelper::_('select.booleanlist', 'hide_me', '', $this->item->hide_me);

		//Payment methods dropdown
		$query->clear();
		$query->select('name, title')
			->from('#__jd_payment_plugins')
			->where('published = 1')
			->order('title');
		$db->setQuery($query);
		$options                        = array();
		$options[]                      = HTMLHelper::_('select.option', '', Text::_('JD_PAYMENT_METHOD'), 'name', 'title');
		$options                        = array_merge($options, $db->loadObjectList());
		$this->lists ['payment_method'] = HTMLHelper::_('select.genericlist', $options, 'payment_method', 'class="input-large form-select"', 'name', 'title', $this->item->payment_method);
		//Build the form object
		$rowFields = DonationHelper::getFormFields($this->item->language);
		$form      = new OSFForm($rowFields);
		if ($this->item->id)
		{
			$data       = DonationHelper::getDonationData($this->item, $rowFields, true, false);
			$useDefault = false;
		}
		else
		{
			$useDefault = true;
			$data       = array();
		}
		if (!isset($data['country']))
		{
			$data['country'] = $config->default_country;
		}

		$active_currency = $config->currency;
		$add_currency = 0;
		if($this->item->currency_code == ""){
			$this->item->currency_code = $active_currency;
			$add_currency = 1;
		}

		if(DonationHelper::isMultipleCurrencies()){
			$active_currencies = $config->active_currencies;
			$active_currencies_array = explode(",",$active_currencies);
			$optionArr = array();
			$optionArr[] = HTMLHelper::_('select.option','',Text::_('JD_CURRENCY'));
			foreach($active_currencies_array as $currency){
				$optionArr[] = HTMLHelper::_('select.option',$currency,$currency);
			}
			if($add_currency == 1){
				$optionArr[] = HTMLHelper::_('select.option',$active_currency,$active_currency);
			}

			if($this->item->currency_converted != ''){
			    $currency = $this->item->currency_converted;
            }else{
			    $currency = $this->item->currency_code;
            }

			$this->lists['currencies'] = HTMLHelper::_('select.genericlist',$optionArr,'currency_code','class="input-medium form-select"','value','text',$currency);
		}
		$this->bootstrapHelper  = new DonationHelperBootstrap($config->twitter_bootstrap_version);
		$form->bind($data, $useDefault);
		$this->form   = $form;
		$this->config = $config;
	}
}
