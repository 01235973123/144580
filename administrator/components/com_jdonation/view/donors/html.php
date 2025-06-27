<?php

/**
 * @version        5.6.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ();

use Joomla\CMS\Factory;

use Joomla\CMS\HTML\HTMLHelper;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class DonationViewDonorsHtml extends OSFViewList
{
	protected function prepareView()
	{
		parent::prepareView();
		$config = DonationHelper::getConfig();
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		if ($config->use_campaign)
		{	
			$query->select('id, title')
				->from('#__jd_campaigns')
				->order('title');
			$db->setQuery($query);
			$options                            = array();
			$options []                         = HTMLHelper::_('select.option', '', Text::_('JD_SELECT_CAMPAIGN'), 'id', 'title');
			$options                            = array_merge($options, $db->loadObjectList());
			$this->lists ['filter_campaign_id'] = HTMLHelper::_('select.genericlist', $options, 'filter_campaign_id', ' onchange="submit();" class="input-large form-select ilarge"', 'id', 'title', $this->state->filter_campaign_id);
			$query->clear();
		}
		$current_year							= date("Y",time());
		$options								= array();
		$options[]								= HTMLHelper::_('select.option', '', Text::_('JD_SELECT_YEAR'), 'value', 'text');
		for($i=$current_year; $i >= $current_year - 5;$i--){
			$options[]							= HTMLHelper::_('select.option',$i,$i);
		}
		$this->lists['filter_year']				= HTMLHelper::_('select.genericlist', $options, 'filter_year', ' onchange="submit();" class="input-medium form-select ilarge" ', 'value', 'text', $this->state->filter_year);
		

		if(DonationHelper::isMultipleCurrencies())
		{
			$active_currencies = $config->active_currencies;
			$active_currencies_array = explode(",",$active_currencies);
			$optionArr = array();
			$optionArr[] = HTMLHelper::_('select.option','',Text::_('JD_CURRENCY'));
			foreach($active_currencies_array as $currency){
				$optionArr[] = HTMLHelper::_('select.option',$currency,$currency);
			}
			if((!in_array($config->currency,$active_currencies_array)) && ($config->currency != "")){
				$optionArr[] = HTMLHelper::_('select.option',$config->currency,$config->currency);
			}
			$this->lists['currencies'] = HTMLHelper::_('select.genericlist',$optionArr,'filter_currency','onchange="submit();" class="input-medium form-select ilarge"','value','text',$this->state->filter_currency);
		}

		if($config->show_pending_records == 1)
		{
			$optionArr = array();
			$optionArr[] = HTMLHelper::_('select.option','',Text::_('JD_PAID_STATUS'));
			$optionArr[] = HTMLHelper::_('select.option','1',Text::_('JD_UNPAID'));
			$optionArr[] = HTMLHelper::_('select.option','2',Text::_('JD_PAID'));
			$this->lists['paid_status'] = HTMLHelper::_('select.genericlist',$optionArr,'filter_paid_status','class="input-medium form-select ilarge"  onchange="submit();" ','value','text',$this->state->filter_paid_status);
		}
		if($config->enable_hide_donor)
		{
			$optionArr = array();
			$optionArr[] = HTMLHelper::_('select.option','',Text::_('JD_SHOW_ANONYMOUS_DONATION'));
			$optionArr[] = HTMLHelper::_('select.option','1',Text::_('JNO'));
			$optionArr[] = HTMLHelper::_('select.option','2',Text::_('JYES'));
			$this->lists['anonymous'] = HTMLHelper::_('select.genericlist',$optionArr,'filter_hide','class="input-medium form-select ilarge"  onchange="submit();" ','value','text',$this->state->filter_hide);
		}
		if($config->enable_gift_aid)
		{
			$optionArr = array();
			$optionArr[] = HTMLHelper::_('select.option','',Text::_('JD_SHOW_GIFT_AID_DONATION'));
			$optionArr[] = HTMLHelper::_('select.option','1',Text::_('JNO'));
			$optionArr[] = HTMLHelper::_('select.option','2',Text::_('JYES'));
			$this->lists['gift_aid'] = HTMLHelper::_('select.genericlist',$optionArr,'gift_aid','class="input-medium form-select ilarge"  onchange="submit();" ','value','text',$this->state->gift_aid);
		}
		if(DonationHelper::isAvailablePayments())
		{
			$db->setQuery("Select name as value, title as text from #__jd_payment_plugins order by ordering");
			$payments = $db->loadObjectList();
			$optionArr = array();
			$optionArr[] = HTMLHelper::_('select.option','',Text::_('JD_ALL_PAYMENT_METHODS'));
			$optionArr = array_merge($optionArr, $payments);
			$this->lists['payments'] = HTMLHelper::_('select.genericlist',$optionArr,'filter_payment','onchange="submit();" class="input-medium form-select ilarge"','value','text',$this->state->filter_payment);
		}
		if((int) $this->state->filter_campaign_id > 0 || $this->state->filter_paid_status > 0 || $this->state->currency != "" || $this->state->filter_year > 0 || $this->state->start_date != "" || $this->state->end_date != "" || $this->state->filter_hide > 0)
		{
			$this->showFilterForm = "js-stools-container-filters-visible";
		}
		else
		{
			$this->showFilterForm = "";
		}
		$this->bootstrapHelper  = new DonationHelperBootstrap($config->twitter_bootstrap_version);
		$this->config = $config;
	}
	/**
	 * Method to add toolbar buttons
	 * 
	 */
	protected function addToolbar()
	{
		if(DonationHelper::isJoomla4())
		{
			$toolbar = Toolbar::getInstance('toolbar');
			$helperClass = $this->classPrefix . 'Helper';
			if (is_callable($helperClass . '::getActions'))
			{
				$canDo = call_user_func(array($helperClass, 'getActions'), $this->name, $this->state);
			}
			else
			{
				$canDo = call_user_func(array('OSFHelper', 'getActions'), $this->option, $this->name, $this->state);
			}
			ToolBarHelper::title(Text::_(strtoupper($this->languagePrefix . '_MANAGE_' . $this->name)), 'link ' . $this->name);
			if ($canDo->get('core.create'))
			{
				ToolBarHelper::addNew('add', 'JTOOLBAR_NEW');
			}

			if (( $canDo->get('core.edit') || $canDo->get('core.delete') || $canDo->get('core.edit.state')) && isset($this->items[0]))
			{
				$dropdown = $toolbar->dropdownButton('status-group')
					->text('JTOOLBAR_CHANGE_STATUS')
					->toggleSplit(false)
					->icon('icon-ellipsis-h')
					->buttonClass('btn btn-action')
					->listCheck(true);

				$childBar = $dropdown->getChildToolbar();
			
				if ($canDo->get('core.edit') && isset($this->items[0]))
				{
					$childBar->edit('edit', 'JTOOLBAR_EDIT')->listCheck(true);
				}
				if ($canDo->get('core.delete') && isset($this->items[0]))
				{
					$childBar->delete('delete')->message(Text::_($this->languagePrefix . '_DELETE_CONFIRM'))->listCheck(true);
				}
				if ($canDo->get('core.edit.state'))
				{
					if (isset($this->items[0]->published) || isset($this->items[0]->state))
					{
						$childBar->publish('publish')->listCheck(true);
						$childBar->unpublish('unpublish')->listCheck(true);							
					}
				}
				$childBar->standardButton('donor.resendEmail')
						->text('JD_RESEND_EMAIL')
						->icon('icon-mail')
						->task('donor.resendEmail')
						->listCheck(true);
				$childBar->standardButton('donor.request_payment')
						->text('JD_REQUEST_PAYMENT')
						->icon('icon-mail')
						->task('donor.request_payment')
						->listCheck(true);
			}

			
		}
		else
		{
			$helperClass = $this->classPrefix . 'Helper';
			if (is_callable($helperClass . '::getActions'))
			{
				$canDo = call_user_func(array($helperClass, 'getActions'), $this->name, $this->state);
			}
			else
			{
				$canDo = call_user_func(array('OSFHelper', 'getActions'), $this->option, $this->name, $this->state);
			}
			ToolBarHelper::title(Text::_(strtoupper($this->languagePrefix . '_MANAGE_' . $this->name)), 'link ' . $this->name);
			if ($canDo->get('core.create'))
			{
				ToolBarHelper::addNew('add', 'JTOOLBAR_NEW');
			}
			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				ToolBarHelper::editList('edit', 'JTOOLBAR_EDIT');
			}
			if ($canDo->get('core.delete') && isset($this->items[0]))
			{
				ToolBarHelper::deleteList(Text::_($this->languagePrefix . '_DELETE_CONFIRM'), 'delete');
			}
			ToolbarHelper::custom('donor.resendEmail', 'envelope', 'envelope', 'JD_RESEND_EMAIL', true);
			ToolbarHelper::custom('donor.request_payment', 'envelope', 'envelope', 'JD_REQUEST_PAYMENT', true);
			if ($canDo->get('core.edit.state'))
			{
				if (isset($this->items[0]->published) || isset($this->items[0]->state))
				{
					ToolbarHelper::publish('publish', 'JTOOLBAR_PUBLISH', true);
					ToolbarHelper::unpublish('unpublish', 'JTOOLBAR_UNPUBLISH', true);								
				}
			}
		}
	}
}
