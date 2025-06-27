<?php
/**
 * @version        5.4
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Table\Table;

class DonationTableDonor extends Table
{

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 *
	 */
	function __construct($db)
	{
		parent::__construct('#__jd_donors', 'id', $db);
	}

    /**
     * Sanitize data before storing into database
     *
     * @return bool|void
     */
    public function check()
    {
        $this->user_id          = (int)$this->user_id;
        $this->receive_user_id  = (int)$this->receive_user_id;
        $this->amount           = (float)$this->amount;
        if (!$this->created_date) {
            $this->created_date = $this->getDbo()->getNullDate();
        }

        if (!$this->payment_date) {
            $this->payment_date = $this->getDbo()->getNullDate();
        }

        if (!$this->mollie_recurring_start_date) {
            $this->mollie_recurring_start_date = $this->getDbo()->getNullDate();
        }
        return parent::check();
    }
}
