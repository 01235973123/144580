<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die ();
use Joomla\CMS\Factory;

class DonationViewCampaignsHtml extends OSFViewList
{
	protected function prepareView()
	{
		parent::prepareView();
		$user					= Factory::getApplication()->getIdentity();
		$this->dateFormat		= DonationHelper::getConfigValue('date_format');
		$this->nullDate			= Factory::getContainer()->get('db')->getNullDate();
		$this->bootstrapHelper  = new DonationHelperBootstrap($config->twitter_bootstrap_version);
		$this->config    		= DonationHelper::getConfig();
		
		$filters = [];

		
		$this->lists['filter_category_id'] = DonationHelperHtml::getCategoryListDropdown(
			'category_id',
			$this->state->category_id,
			'class="form-select" onchange="submit();" style="width:300px"',
			null,
			$filters
		);

        // We don't need toolbar in the modal window.
		/*
        if (version_compare(JVERSION, '3.0', 'ge') && version_compare(JVERSION, '4.0', 'lt')) {
            if ($this->getLayout() !== 'modal')
            {
                DonationHelper::addSideBarmenus('campaigns');
                $this->sidebar = JHtmlSidebar::render();
            }
        }
		*/
	}
}
