<?php

/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2023 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;

class DonationControllerEmail extends DonationController
{
	public function delete_all()
	{
		Factory::getDbo()->truncateTable('#__jd_emails');

		$this->setRedirect('index.php?option=com_jdonation&view=emails');
	}
}
