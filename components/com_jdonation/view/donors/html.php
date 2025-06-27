<?php

/**
 * @version        5.6.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
class DonationViewDonorsHtml extends OSFViewList
{
	protected function prepareView()
	{
		$db						= Factory::getContainer()->get('db');
        $this->config           = DonationHelper::getConfig();
		$params					= Factory::getApplication()->getParams();
		$campaignId				= (int) $params->get('cid', 0);
		if($campaignId == 0)
		{
			$campaignId			= Factory::getApplication()->input->getInt('campaign_id',0);
		}
		$orderBy				= $params->get('order_by', 'created_date');
		$orderDirection			= $params->get('order_direction', 'DESC');
        $owncampaigns			= $params->get('owncampaigns',0);
		$exclude_campaign_ids	= $params->get('exclude_campaign_ids','');
        if($owncampaigns == 1){
            if (!Factory::getApplication()->getIdentity()->id)
            {
                $returnUrl		= Route::_('index.php?option=com_jdonation&view=donors&Itemid=' . $this->Itemid);
                $url			= Route::_('index.php?option=com_users&view=login&return=' . base64_encode($returnUrl), false);
				Factory::getApplication()->enqueueMessage(Text::_('JD_PLEASE_LOGIN'));
                Factory::getApplication()->redirect($url);
            }
        }

		$menus                      = Factory::getApplication()->getMenu();
		$menu                       = $menus->getActive();
		$params                     = new Registry() ;
		if (is_object($menu))
		{
	        $params                 = new Registry() ;
	        //$params->loadString($menu->getParams());
			$params 				= $menu->getParams();
			//$show_page_heading		= $params->get('show_page_heading','1');
			$page_heading			= $params->get('page_heading','');
		}
		if($page_heading == '')
		{
			$page_heading = Text::_('JD_DONOR_LIST');
		}

		//Campaigns selection dropdown
		$options = array() ;
		$options[] = HTMLHelper::_('select.option', '' , Text::_('JD_SELECT_CAMPAIGN') , 'id', 'title') ;
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__jd_campaigns')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);
		$rowCampaigns = $db->loadObjectList();
		$options = array_merge($options, $rowCampaigns) ;
		$this->lists['campaign_id'] = HTMLHelper::_('select.genericlist', $options, 'campaign_id', ' class="input-large form-select" onchange="javascript:document.adminForm.submit();" ', 'id', 'title', $campaignId) ;

        $this->model->set('filter_campaign_id',$campaignId);
        $this->model->set('filter_state','P');
        $this->model->set('filter_own_campaign',$owncampaigns);
        $this->model->set('filter_order',$orderBy);
        $this->model->set('filter_order_Dir',$orderDirection);
		$this->model->set('filter_exclude_campaign_ids',$exclude_campaign_ids);
		//$this->model->set('limitstart',20);
        $this->bootstrapHelper      = new DonationHelperBootstrap($this->config->twitter_bootstrap_version);
		$this->page_heading			= $page_heading;

		$ajax = Factory::getApplication()->input->getInt('ajax',0);
		$this->pagination = $this->model->getPagination();
		$total = $this->pagination->total;
		$limit = $this->model->get('limit');
		$totalPages = ceil($total / $limit);
		if($ajax == 1)
		{
			$page  = Factory::getApplication()->input->getInt('page',0);
			$startlimit = $limit*($page-1);
			$this->model->set('limitstart',$startlimit);
			//$this->model->set('start',$startlimit);
			$this->setLayout('default_items');
		}

		$this->totalPages = $totalPages;
		parent::prepareView();
	}
} 
