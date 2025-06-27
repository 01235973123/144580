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

class DonationViewCategoriesHtml extends OSFViewList
{
	protected function prepareView()
	{
		parent::prepareView();
		$this->dateFormat		= DonationHelper::getConfigValue('date_format');
		$this->nullDate			= Factory::getDbo()->getNullDate();
		$this->bootstrapHelper  = new DonationHelperBootstrap($config->twitter_bootstrap_version);
        // We don't need toolbar in the modal window.
	}
}
