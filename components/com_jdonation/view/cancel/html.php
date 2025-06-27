<?php
/**
 * @version        4.2
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

class DonationViewCancelHtml extends OSFViewHtml
{
	/**
	 * Indicate that this view doesn't have a model, so controller don't need to create it.
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * Display the view
	 */
	function display()
	{
		$config      = DonationHelper::getConfig();
		$fieldSuffix = DonationHelper::getFieldSuffix();
		if ($fieldSuffix)
		{
			DonationHelper::getMultilingualConfigData($config, $fieldSuffix, array('cancel_message'));
		}

		$this->message = $config->cancel_message;

		parent::display();
	}
}