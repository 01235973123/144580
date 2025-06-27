<?php

/**
 * @version        5.7.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


class DonationControllerCampaign extends OSFController
{

	/**
	 * Display information
	 */
	public function display($cachable = false, array $urlparams = array())
	{
		$config   = DonationHelper::getConfig();
		$document = Factory::getApplication()->getDocument();
		DonationHelper::addStylesheet(Uri::root() . 'media/com_jdonation/assets/css/style.css', 'text/css', null, null);
		if (file_exists(JPATH_ROOT . '/media/com_jdonation/assets/css/custom.css') && filesize(JPATH_ROOT . '/media/com_jdonation/assets/css/custom.css') > 0)
		{
			DonationHelper::addStylesheet(Uri::root() . 'media/com_jdonation/assets/css/custom.css', 'text/css', null, null);
		}
		if ($config->load_twitter_bootstrap)
		{
			DonationHelper::loadBootstrap(false);
		}
		DonationHelper::loadJQuery();
		DonationHelper::addScript( DonationHelper::getSiteUrl() . '/media/com_jdonation/assets/js/noconflict.js');

		//Clear the donation form data on donaton complete or cancel
		$viewName = $this->input->get('view', $this->defaultView, 'string');
		if ($viewName == 'complete' || $viewName == 'cancel')
		{
			$this->app->setUserState('com_jdonation.formdata', null);
		}

		parent::display($cachable, $urlparams);
	}

    /**
     * Save campaign
     * @throws Exception
     */
	public function save()
    {
        $config         = DonationHelper::getConfig();
        $id             = $this->input->getInt('id',0);
        if($id == 0)
        {
            if($config->auto_approval_campaign)
            {
                $msg    = Text::_('JD_CAMPAIGN_SAVED');
            }
            else
            {
                $msg    = Text::_('JD_CAMPAIGN_SAVED').". ".Text::_('JD_APPROVAL_LATER');
            }

            $isNew      = true;
        }
        else
        {
            $msg        = Text::_('JD_CAMPAIGN_SAVED');

            $isNew      = false;
        }
        $model          = $this->getModel();
        $model->store($this->input);

        if($isNew)
        {
			DonationHelper::sendNewCampaign($this->input->get('id'));
        }
        $manageItemId   = DonationHelperRoute::findView('usercampaigns', 0);
        if ($manageItemId)
        {
            $url        = Route::_('index.php?option=com_jdonation&view=usercampaigns&Itemid='.$manageItemId);
        }
        else
        {
            $url        = Route::_('index.php?option=com_jdonation&view=usercampaigns&Itemid='.$this->input->getInt('Itemid'));
        }
        Factory::getApplication()->enqueueMessage($msg);
        Factory::getApplication()->redirect($url);
    }

    public function canceledit()
    {
        $manageItemId = DonationHelperRoute::findView('usercampaigns', 0);
        if ($manageItemId)
        {
            $url = Route::_('index.php?option=com_jdonation&view=usercampaigns&Itemid='.$manageItemId);
        }
        else
        {
            $url = Route::_('index.php?option=com_jdonation&view=usercampaigns&Itemid='.$this->input->getInt('Itemid'));
        }
        Factory::getApplication()->redirect($url);
    }

    function delete()
    {
        $app        = Factory::getApplication();
        $user       = Factory::getApplication()->getIdentity();
        if((int) $user->id == 0)
        {
            $app->enqueueMessage(Text::_('JD_NOT_ALLOWED_ACTION'));
            $app->redirect('index.php');
        }
        $id         = Factory::getApplication()->input->getInt('campaign_id');
        if((int) $id == 0)
        {
            $app->enqueueMessage(Text::_('JD_CAMPAIGN_IS_NOT_EXISTS'));
            $app->redirect('index.php');
        }
		$ret                    = Factory::getApplication()->getIdentity()->authorise('managecampaigns', 'com_jdonation');
		if(! $ret)
		{
			$app->enqueueMessage(Text::_('JD_NOT_ALLOWED_ACTION'));
			$url = Route::_('index.php');
			$app->redirect($url);
		}
        if($user->id  != DonationHelper::getCampaignOwner($id))
        {
            $app->enqueueMessage(Text::_('JD_NOT_ALLOWED_ACTION'));
            $app->redirect('index.php');
        }
        if(!$user->authorise('core.delete','com_jdonation'))
        {
            $app->enqueueMessage(Text::_('JD_NOT_ALLOWED_ACTION'));
            $app->redirect('index.php');
        }
        $db         = Factory::getContainer()->get('db');
        $db->setQuery("Delete from #__jd_campaigns where id = '$id'");
        $db->execute();
        $app->enqueueMessage(Text::_('JD_CAMPAIGN_HAS_BEEN_REMOVED'));
        $app->redirect(Jroute::_('index.php?option=com_jdonation&view=usercampaigns&Itemid='.$app->input->getInt('Itemid',0)));
    }
}
