<?php
/**
 * @version        5.7.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class DonationModelDonationdetails extends OSFModelAdmin
{

    public function __construct(array $config = array())
    {
        $config['table']            = '#__jd_donors';
        parent::__construct($config);
    }

    /**
     * Override loadData method to calculate number_created_days
     */
    public function loadData()
    {
        $db         = $this->getDbo();
        $query      = $db->getQuery(true);
        $query->select('tbl.*, b.title as campaign_title')
            ->from('#__jd_donors as tbl')
            ->join('left','#__jd_campaigns as b on b.id = tbl.campaign_id')
            ->where('tbl.id = ' . (int) $this->state->id);
        $db->setQuery($query);
        $this->data = $db->loadObject();
    }
}
