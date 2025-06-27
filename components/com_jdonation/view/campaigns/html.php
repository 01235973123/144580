<?php


/**
 * @version        5.4.5
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

class DonationViewCampaignsHtml extends OSFViewList
{
	protected function prepareView()
	{
		parent::prepareView();
		$this->show_category		= false;
		$filter_search              = $this->input->getString('filter_search','');
		if($filter_search)
        {
            $model                  = $this->getModel();
            $model->set('filter_state', $filter_search);
        }
		$document                   = Factory::getApplication()->getDocument();
		$this->category_id			= $this->input->getInt('category_id',0);
		$menus                      = Factory::getApplication()->getMenu();
		$menu                       = $menus->getActive();
		$params                     = new Registry() ;
		$show_category_description  = 1;
		if (is_object($menu))
		{
	        $params                 = new Registry() ;
			$params = $menu->getParams();
			$meta_description       = $params->get('menu-meta_description','');
            if($meta_description != "")
            {
				$document->setMetaData( "description", $meta_description); 
            }
            $layout                 = $params->get('campaignlayout','default');
            $ncolumns               = $params->get('ncolumns',2);
            $this->setLayout($layout);

			$show_category_description = $params->get('show_category_description',1);

			if($this->category_id > 0 && $params->get('show_category', 1))
			{
				$this->show_category = true;
				$db					= Factory::getContainer()->get('db');

				$fieldSuffix            = DonationHelper::getFieldSuffix();
				if ($fieldSuffix)
				{
					DonationHelper::getMultilingualConfigData($config, $fieldSuffix);
				}
				$query				= $db->getQuery(true);
				$query->select('*')
					->from('#__jd_categories')
					->where('id='.$this->category_id)
					->where('published = 1');
				if ($fieldSuffix)
				{
					$fields = array(
						'title',
						'description'
					);
					DonationHelper::getMultilingualFields($query, $fields, $fieldSuffix);
				}
				$db->setQuery($query);
				$this->category			 = $db->loadObject();
			}

		}
		$this->config               = DonationHelper::getConfig();
        $this->bootstrapHelper      = new DonationHelperBootstrap($this->config->twitter_bootstrap_version);
        $this->nullDate             = Factory::getContainer()->get('db')->getNullDate();
        $this->ncolumns             = $ncolumns;
		$this->show_category_description = $show_category_description;
	}
}
