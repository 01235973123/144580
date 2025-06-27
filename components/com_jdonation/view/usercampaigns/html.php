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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

class DonationViewUsercampaignsHtml extends OSFViewList
{
	protected function prepareView()
	{
		parent::prepareView();
		$document                   = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JD_MY_CAMPAIGNS'));
		$this->config               = DonationHelper::getConfig();
        if (!Factory::getApplication()->getIdentity()->id)
        {
            // Allow users to login
            $return = base64_encode(Uri::getInstance()->toString());
            Factory::getApplication()->redirect('index.php?option=com_users&view=login&return=' . $return);
        }
		 $ret                    = Factory::getApplication()->getIdentity()->authorise('managecampaigns', 'com_jdonation');
		if(! $ret)
		{
			Factory::getApplication()->enqueueMessage(Text::_('JD_NOT_ALLOWED_ACTION'));
			$url = Route::_('index.php?option=com_jdonation&Itemid=' . $this->input->getInt('Itemid'));
			Factory::getApplication()->redirect($url);
		}
        $this->Itemid               = Factory::getApplication()->input->getInt('Itemid', 0);
        $this->items                = $this->model->getData();
        $this->pagination           = $this->model->getPagination();
		$this->topDonors		    = $this->model->getTopDonors();	
        $this->bootstrapHelper      = new DonationHelperBootstrap($this->config->twitter_bootstrap_version);
	}
}
