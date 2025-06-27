<?php

/**
 * @version        5.5.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die ();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class DonationViewCategoryHtml extends OSFViewItem
{
	protected function prepareView()
	{
		parent::prepareView();
		
		$db = Factory::getContainer()->get('db');

		$this->bootstrapHelper		= new DonationHelperBootstrap($config->twitter_bootstrap_version);
	}
}
