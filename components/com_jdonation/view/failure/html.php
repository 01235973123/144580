<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
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

class DonationViewFailureHtml extends OSFViewHtml
{
	/**
	 * Indicate that this view doesn't have a model, so controller don't need to create it.
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * Method to display the view
	 *
	 */
	function display()
	{
		$this->reason = isset($_SESSION['reason']) ? $_SESSION['reason'] : '';
		if (empty($this->reason))
		{
			$this->reason = Factory::getApplication()->getSession()->get('omnipay_payment_error_reason');
		}
		$this->link   = Route::_('index.php?option=com_jdonation&view=donation&Itemid=' . $this->Itemid);;

		parent::display();
	}
}
