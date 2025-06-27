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
defined('_JEXEC') or die ();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class DonationViewCampaignHtml extends OSFViewItem
{
	protected function prepareView()
	{
        if (version_compare(JVERSION, '3.0', 'ge'))
        {
            //JHtml::_('formbehavior.chosen', 'select');
        }
		parent::prepareView();
		DonationHelper::addScript(Uri::root() . 'components/com_jdonation/assets/js/colorpicker/jscolor.js');
		$db = Factory::getContainer()->get('db');

		$nullDate = Factory::getContainer()->get('db')->getNullDate();
		if ($this->item->start_date != "" && $this->item->start_date != $nullDate)
		{
			$this->item->start_date = HTMLHelper::_('date', $this->item->start_date, 'Y-m-d', null);
		}
		if ($this->item->end_date != "" && $this->item->end_date != $nullDate)
		{
			$this->item->end_date = HTMLHelper::_('date', $this->item->end_date, 'Y-m-d', null);
		}
        $config                 = DonationHelper::getConfig();
		if($this->item->id == 0)
		{
		    $this->item->active_dedicate = $config->activate_tributes;
        }
		//$this->lists['enable_recurring'] = JHtml::_ ( 'select.booleanlist', 'enable_recurring', ' ', $this->item->enable_recurring );
        $options   = [];
        $options[] = HTMLHelper::_('select.option', '', Text::_('JD_ALL_PAYMENT_METHODS'), 'id', 'title');

        $query = $db->getQuery(true);
        $query->clear()
            ->select('id, title')
            ->from('#__jd_payment_plugins')
            ->where('published=1');
        $db->setQuery($query);
        $this->lists['payment_methods'] = HTMLHelper::_('select.genericlist', array_merge($options, $db->loadObjectList()), 'payment_methods[]', ' class="imedium form-control" multiple="multiple" ', 'id', 'title', explode(',', $this->item->payment_plugins));

        $optionArr = [];
        $optionArr[] = HTMLHelper::_('select.option', -1 , Text::_('JD_INHERIT_FROM_GLOBAL_CONFIGURATION'));
        $optionArr[] = HTMLHelper::_('select.option', 0, Text::_('JNO'));
        $optionArr[] = HTMLHelper::_('select.option', 1, Text::_('JYES'));
        $this->lists['show_campaign'] = HTMLHelper::_('select.genericlist', $optionArr, 'show_campaign', 'class="input-large form-select"','value','text', $this->item->show_campaign);

		$options                      = [];
		$options[]                    = HTMLHelper::_('select.option', 0, Text::_('JD_BOTH'));
		$options[]                    = HTMLHelper::_('select.option', 1, Text::_('JD_ONE_TIME_ONLY'));
		$options[]                    = HTMLHelper::_('select.option', 2, Text::_('JD_RECURRING_ONLY'));
		$this->lists['donation_type'] = HTMLHelper::_('select.genericlist', $options, 'donation_type', 'class="form-select w-100"', 'value', 'text', $this->item->donation_type);

		$options                      = [];
		$options[]                    = HTMLHelper::_('select.option', 0, Text::_('JD_INHERIT_FROM_GLOBAL_CONFIGURATION'));
		$options[]                    = HTMLHelper::_('select.option', 1, Text::_('JNO'));
		$options[]                    = HTMLHelper::_('select.option', 2, Text::_('JYES'));
		$this->lists['display_amount_textbox'] = HTMLHelper::_('select.genericlist', $options, 'display_amount_textbox', 'class="input-medium form-select ilarge"', 'value', 'text', (int)$this->item->display_amount_textbox);

		$options                      = [];
		$options[]                    = HTMLHelper::_('select.option', 0, Text::_('JNO'));
		$options[]                    = HTMLHelper::_('select.option', 1, Text::_('JYES'));
		$this->lists['show_amounts'] = HTMLHelper::_('select.genericlist', $options, 'show_amounts', 'class="input-medium form-select ilarge"', 'value', 'text', (int)$this->item->show_amounts);

		$this->config                 = $config;


		//if($config->activate_campaign_currency)
		//{
		$active_currencies_arr	      = [];
		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', 0, Text::_('JD_SELECT_CURRENCY_FOR_THIS_CAMPAIGN'));
		if($config->active_currencies != "")
		{
			$active_currencies = $config->active_currencies;
			$active_currencies_arr = explode(",", $active_currencies);
			if(count($active_currencies_arr))
			{
				foreach($active_currencies_arr as $currency)
				{
					$options[]    = HTMLHelper::_('select.option', $currency, $currency);
				}
			}
		}
	
		$this->lists['currency']   = HTMLHelper::_('select.genericlist', $options, 'currency', 'class="input-medium form-select imedium"', 'value', 'text', isset($this->item->currency) ? $this->item->currency: $config->currency);

		$this->count_active_currencies_arr = count($active_currencies_arr);
		//}

        $options    = [];
        $options [] = HTMLHelper::_('select.option', 'd', Text::_('JD_DAILY'));
        $options [] = HTMLHelper::_('select.option', 'w', Text::_('JD_WEEKLY'));
        $options [] = HTMLHelper::_('select.option', 'b', Text::_('JD_BI_WEEKLY'));
        $options [] = HTMLHelper::_('select.option', 'm', Text::_('JD_MONTHLY'));
        $options [] = HTMLHelper::_('select.option', 'q', Text::_('JD_QUARTERLY'));
        $options [] = HTMLHelper::_('select.option', 's', Text::_('JD_SEMI_ANNUALLY'));
        $options [] = HTMLHelper::_('select.option', 'a', Text::_('JD_ANNUALLY'));

        $selecteds   = [];
        $frequencies = explode(',', $this->item->recurring_frequencies);
        for ($i = 0, $n = count($frequencies); $i < $n; $i++)
        {
            $selecteds [] = HTMLHelper::_('select.option', $frequencies [$i], $frequencies [$i]);
        }
        $this->lists ['recurring_frequencies'] = HTMLHelper::_('select.genericlist', $options, 'recurring_frequencies[]', ' class="input-large imedium" size="5" multiple="multiple" ', 'value', 'text', $selecteds);

		$options   = [];
        $options[] = HTMLHelper::_('select.option', 0, Text::_('JD_SELECT_CATEGORY'), 'id', 'title');

        $query = $db->getQuery(true);
        $query->clear()
            ->select('id, title')
            ->from('#__jd_categories')
            ->where('published=1');
        $db->setQuery($query);
        $this->lists['category_id'] = HTMLHelper::_('select.genericlist', array_merge($options, $db->loadObjectList()), 'category_id', ' class="form-select inputbox"', 'id', 'title', $this->item->category_id);

		$this->bootstrapHelper		= new DonationHelperBootstrap($config->twitter_bootstrap_version);
	}
}
