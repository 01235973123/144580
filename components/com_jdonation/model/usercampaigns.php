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
use Joomla\CMS\Factory;

class DonationModelUsercampaigns extends OSFModelList
{
    function __construct($config = array())
    {
        $config['table']            = '#__jd_campaigns';
        parent::__construct($config);
    }

    protected function buildQueryColumns($query)
    {
        $query->select('tbl.*, DATEDIFF(end_date, CURDATE()) AS days_left, SUM(b.amount) as total_donated, COUNT(b.id) As number_donors');

        if (!Factory::getApplication()->isClient('administrator'))
        {
            $fieldSuffix = DonationHelper::getFieldSuffix();
            if ($fieldSuffix)
            {
                DonationHelper::getMultilingualFields($query, array('tbl.title', 'tbl.description'), $fieldSuffix);
            }
        }

        return $this;
    }

    protected function buildQueryJoins($query)
    {
        $query->leftJoin('#__jd_donors AS b ON (tbl.id = b.campaign_id AND b.published = 1)');
        return $this;
    }

    protected function buildQueryWhere($query)
    {
        $query->where('tbl.user_id = '. (int) Factory::getApplication()->getIdentity()->id);
        parent::buildQueryWhere($query);
        return $this;
    }

    protected function buildQueryGroup($query)
    {
        $query->group('tbl.id');
        return $this;
    }

    function getTopDonors()
    {
        $db = $this->getDbo();
        $db->setQuery(
            'SELECT b.first_name, b.last_name, b.amount , c.title as campaign_title ' .
            'FROM #__jd_donors AS b ' .
            'LEFT JOIN #__jd_campaigns AS c ON b.campaign_id = c.id ' .
            'WHERE c.user_id = '. (int) Factory::getApplication()->getIdentity()->id  .
            ' AND b.published = 1 ' .
            'ORDER BY b.amount DESC'
        );
        try
        {
            $topDonors = $db->loadObjectList();
            return $topDonors ? $topDonors : array();
        }
        catch (RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return array();
        }
    }
}
