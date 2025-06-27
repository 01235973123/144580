<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\Table\Table;

class DonationTableCampaign extends Table
{

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 *
	 */
	function __construct($db)
	{
		parent::__construct('#__jd_campaigns', 'id', $db);
	}

    /**
     * Sanitize data before storing into database
     *
     * @return bool|void
     */
    public function check()
    {
        $this->user_id          = (int)$this->user_id;
        $this->goal             = (int)$this->goal;
		if(!$this->campaign_photo){
			$this->campaign_photo   = '';
		}
        if (!$this->start_date) {
            $this->start_date = $this->getDbo()->getNullDate();
        }

        if (!$this->end_date) {
            $this->end_date = $this->getDbo()->getNullDate();
        }

        return parent::check();
    }
}
