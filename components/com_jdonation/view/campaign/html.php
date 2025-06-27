<?php

/**
 * @version        5.7.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

class DonationViewCampaignHtml extends OSFViewItem
{
    function prepareView()
    {
        parent::prepareView();
        $app                        = Factory::getApplication();
		$db							= Factory::getContainer()->get('db');
        $this->config               = DonationHelper::getConfig();
        if (!Factory::getApplication()->getIdentity()->id)
        {
            // Allow users to login
            $return                 = base64_encode(Uri::getInstance()->toString());
            Factory::getApplication()->redirect('index.php?option=com_users&view=login&return=' . $return);
        }
        $id = $this->model->getState()->id;
        if (!$id)
        {
            //new campaign
            $ret                    = Factory::getApplication()->getIdentity()->authorise('core.create', 'com_jdonation');
			$ret1                   = Factory::getApplication()->getIdentity()->authorise('managecampaigns', 'com_jdonation');
            if(! $ret && ! $ret1)
            {
                $app->enqueueMessage(Text::_('JD_NOT_ALLOWED_ACTION'));
                $url = Route::_('index.php?option=com_jdonation&Itemid=' . $this->input->getInt('Itemid'));
                $app->redirect($url);
            }
        }
        else
        {
            //edit campaign
            $ret                    = Factory::getApplication()->getIdentity()->authorise('core.edit.own', 'com_jdonation');
            //only allow edit campaign when user has permission and is owner of campaign

            if(! $ret && DonationHelper::getCampaignOwner($id) != Factory::getApplication()->getIdentity()->id)
            {
                $app->enqueueMessage(Text::_('JD_NOT_ALLOWED_ACTION'));
                $url = Route::_('index.php?option=com_jdonation&Itemid=' . $this->input->getInt('Itemid'));
                $app->redirect($url);
            }
        }

        $document = Factory::getApplication()->getDocument();
        if($this->item->id > 0)
        {
            $headerText = str_replace('CAMPAIGN_TITLE', $this->item->title, Text::_('JD_EDIT_CAMPAIGN'));
            $document->setTitle($headerText);
        }
        else
        {
            $headerText = Text::_('JD_ADD_CAMPAIGN');
            $document->setTitle($headerText);
        }

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



        $this->bootstrapHelper      = new DonationHelperBootstrap($this->config->twitter_bootstrap_version);
        $options                      = [];
        $options[]                    = HTMLHelper::_('select.option', 0, Text::_('JD_BOTH'));
        $options[]                    = HTMLHelper::_('select.option', 1, Text::_('JD_ONE_TIME_ONLY'));
        $options[]                    = HTMLHelper::_('select.option', 2, Text::_('JD_RECURRING_ONLY'));
        $this->lists['donation_type'] = HTMLHelper::_('select.genericlist', $options, 'donation_type', 'class="input-large form-select"', 'value', 'text', $this->item->donation_type);


		$options                      = [];
		$options[]                    = HTMLHelper::_('select.option', 1, Text::_('JYES'));
        $options[]                    = HTMLHelper::_('select.option', 0, Text::_('JNO'));
		$this->lists['published']	  = HTMLHelper::_('select.genericlist', $options, 'published', 'class="input-large form-select"', 'value', 'text', $this->item->published);

		$options   = [];
        $options[] = HTMLHelper::_('select.option', 0, Text::_('JD_SELECT_CATEGORY'), 'id', 'title');

        $query = $db->getQuery(true);
        $query->clear()
            ->select('id, title')
            ->from('#__jd_categories')
            ->where('published=1');
        $db->setQuery($query);
        $this->lists['category_id'] = HTMLHelper::_('select.genericlist', array_merge($options, $db->loadObjectList()), 'category_id', ' class="form-select inputbox"', 'id', 'title', $this->item->category_id);
    }	
}
