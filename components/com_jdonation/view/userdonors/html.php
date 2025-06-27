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
use Joomla\CMS\HTML\HTMLHelper;

class DonationViewUserdonorsHtml extends OSFViewList
{
	protected function prepareView()
	{
		parent::prepareView();
		$document                   = Factory::getApplication()->getDocument();
		$db							= Factory::getContainer()->get('db');
		$query						= $db->getQuery(true);
		$document->setTitle(Text::_('JD_DONORS'));
		$this->config               = DonationHelper::getConfig();
        if (!Factory::getApplication()->getIdentity()->get('id'))
        {
            // Allow users to login
            $return = base64_encode(Uri::getInstance()->toString());
            Factory::getApplication()->redirect('index.php?option=com_users&view=login&return=' . $return);
        }
		
        $this->campaigns            = DonationHelper::getUserCampaigns();
        if(count($this->campaigns) == 0)
        {
            Factory::getApplication()->enqueueMessage(Text::_('JD_NO_DONORS_FOUND'));
            Factory::getApplication()->redirect(Route::_('index.php?option=com_jdonation'));
        }
		
		$id                         = Factory::getApplication()->input->getString('id',0);
		if($id > 0 && !DonationHelper::getCampaignOwner($id))
		{
			Factory::getApplication()->enqueueMessage(Text::_('JD_NO_PERMISSION'));
            Factory::getApplication()->redirect(Uri::root());
		}
        
        $this->state                = $this->model->getState();
        $this->state->set('id', $id);
		$query						= $db->getQuery(true);
		$query->select('id')->from('#__jd_campaigns')->where('user_id='.Factory::getApplication()->getIdentity()->id)
			->where('published = 1');
		$db->setQuery($query);
		$campaigns					= $db->loadColumn();
		if(count($campaigns))
		{
			$this->state->set('campaign_ids', implode(",", $campaigns));
		}
        $this->Itemid               = Factory::getApplication()->input->getInt('Itemid', 0);
        $this->items                = $this->model->getData();
        $this->pagination           = $this->model->getPagination();
        $this->campaign_id          = $id;

		if ($id > 0)
		{
			$query->clear();
			$query->select('*')
				->from('#__jd_campaigns')
				->where('id='.$id);				
			if ($fieldSuffix)
			{
				$fields = array(
					'title',
					'description',
					'amounts_explanation',
					'donation_form_msg',
					'thanks_message'
				);
				DonationHelper::getMultilingualFields($query, $fields, $fieldSuffix);
			}	
			$db->setQuery($query);
			$campaign = $db->loadObject();
			
			$this->campaign = $campaign;

			$document->setTitle(Text::_('JD_DONATION_STATISTIC').' ['.$campaign->title.']');

		}

        $this->bootstrapHelper      = new DonationHelperBootstrap($this->config->twitter_bootstrap_version);
	}
}
