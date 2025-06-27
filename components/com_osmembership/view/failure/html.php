<?php

/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2022 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class OSMembershipViewFailureHtml extends MPFViewHtml
{
	/**
	 * Flag to mark that this view does not have an associated model
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * The payment failure reason
	 *
	 * @var string
	 */
	protected $reason;

	/**
	 * Display the view
	 *
	 * @return void
	 */
	public function display()
	{
		$reason = Factory::getApplication()->getSession()->get('omnipay_payment_error_reason');

		if (!$reason)
		{
			$reason = $this->input->getString('failReason', '');
		}

		$this->reason = $reason;

		$this->setLayout('default');

		parent::display();
	}
}
