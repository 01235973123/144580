<?php
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
/**
 * @version        5.4.5
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2018 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class DonationViewSearchHtml extends OSFViewList
{
	protected function prepareView()
	{
		parent::prepareView();
		$filter_search              = $this->input->getString('filter_search','');
		if($filter_search)
        {
            $model                  = $this->getModel();
            $model->set('filter_state', $filter_search);
        }
		$document                   = Factory::getApplication()->getDocument();
		$menus                      = Factory::getApplication()->getMenu();
		$menu                       = $menus->getActive();
		$params                     = new Registry() ;
		if (is_object($menu))
		{
	        $params                 = new Registry() ;
	        //$params->loadString($menu->getParams());
			$params 				= $menu->getParams();
			$meta_description       = $params->get('menu-meta_description','');
            if($meta_description != "")
            {
				$document->setMetaData( "description", $meta_description); 
            }
		}
		$this->config               = DonationHelper::getConfig();
        $this->bootstrapHelper      = new DonationHelperBootstrap($this->config->twitter_bootstrap_version);
        $this->nullDate             = Factory::getContainer()->get('db')->getNullDate();
	}
}
