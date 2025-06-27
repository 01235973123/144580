<?php

/**
 * @version        5.6.0
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

class DonationViewHistoryHtml extends OSFViewHtml
{
	/**
	 * @var Indicate that this view doesn't have a model, so controller don't need to create it.
	 */
	public $hasModel = false;

	/**
	 * Method to display the view
	 *
	 */
	public function display()
	{
		$user = Factory::getApplication()->getIdentity();
		$campaign_id = Factory::getApplication()->input->getInt('campaign_id', 0);
		if (!$user->id)
		{
			//Redirect user, ask them for login
			$return = base64_encode(Route::_('index.php?option=com_jdonation&view=history&Itemid=' . $this->Itemid, false));
			Factory::getApplication()->enqueueMessage(Text::_('JD_LOGIN_TO_ACCESS_HISTORY'));
			Factory::getApplication()->redirect('index.php?option=com_users&view=login&return=' . $return);
		}
        $menus                  = Factory::getApplication()->getMenu();
        $menu                   = $menus->getActive();
        if (is_object($menu))
        {
			$params				= Factory::getApplication()->getParams();
            $page_heading = $params->get('page_heading','');
        }
        if($page_heading == "")
        {
            $page_heading = Text::_('JD_DONATION_HISTORY');
        }
		$model = OSFModel::getInstance('Donors', 'DonationModel', array('ignore_request' => true, 'table_prefix' => '#__jd_'));
		$model->filter_order('created_date')
			->filter_order_Dir('DESC')
			->filter_user_id($user->id);
		if($campaign_id > 0)
		{
			$model->filter_campaign_id($campaign_id);
		}
		$this->state      = $model->getState();

		$start			  = Factory::getApplication()->input->getInt('start', 0);
		if($start > 0)
		{
			$this->state->limitstart  = $start;
		}
		else
		{
			$start		  = $this->state->limitstart;
		}

		$this->start	  = $start;
		$limit			  = Factory::getApplication()->input->getInt('limit', 0);
		if($limit > 0)
		{
			$this->state->limit = $limit;
		}
		$this->items      = $model->getData();
		$this->pagination = $model->getPagination();
		$this->config     = DonationHelper::getConfig();
		$this->page_heading = $page_heading;
		$this->bootstrapHelper = new DonationHelperBootstrap($this->config->twitter_bootstrap_version);
		parent::display();
	}
}
