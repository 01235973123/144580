<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;

class DonationModelDashboard extends OSFModel
{
    /**
     * Get dashboard statistics summary
     */
    public function getStatistics($startDate, $endDate)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Total donations amount trong khoảng date
        $query->select('SUM(amount) AS total_amount')
            ->from($db->quoteName('#__jd_donors'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('payment_date') . ' >= ' . $db->quote($startDate))
            ->where($db->quoteName('payment_date') . ' <= ' . $db->quote($endDate));
        $db->setQuery($query);
        $totalAmount = $db->loadResult() ?: 0;

        // Total number of donors trong khoảng date
        $query->clear()
            ->select('COUNT(DISTINCT id) AS total_donors')
            ->from($db->quoteName('#__jd_donors'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('payment_date') . ' >= ' . $db->quote($startDate))
            ->where($db->quoteName('payment_date') . ' <= ' . $db->quote($endDate));
        $db->setQuery($query);
        $totalDonors = $db->loadResult() ?: 0;

        // Total number of campaigns (không lọc theo ngày)
        $query->clear()
            ->select('COUNT(id) AS total_campaigns')
            ->from($db->quoteName('#__jd_campaigns'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);
        $totalCampaigns = $db->loadResult() ?: 0;

        // Average donation amount
        $avgDonation = ($totalDonors > 0) ? $totalAmount / $totalDonors : 0;

        return [
            'total_amount' => $totalAmount,
            'total_donors' => $totalDonors,
            'total_campaigns' => $totalCampaigns,
            'avg_donation' => $avgDonation
        ];
    }

    
    /**
     * Get donation timeline data for charts
     */
    public function getDonationTimeline($period = 'monthly', $limit = 12)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $dateFormat = '%Y-%m';
        $groupByFormat = 'DATE_FORMAT(payment_date, "%Y-%m")';
        $labelFormat = '%b %Y';
        
        switch ($period) {
            case 'daily':
                $dateFormat = '%Y-%m-%d';
                $groupByFormat = 'DATE_FORMAT(payment_date, "%Y-%m-%d")';
                $labelFormat = '%d %b';
                $limit = 30;
                break;
            case 'weekly':
                $dateFormat = '%x-%v';
                $groupByFormat = 'CONCAT(YEAR(payment_date), "-", WEEK(payment_date))';
                $labelFormat = 'Week %v, %Y';
                $limit = 12;
                break;
            case 'yearly':
                $dateFormat = '%Y';
                $groupByFormat = 'YEAR(payment_date)';
                $labelFormat = '%Y';
                $limit = 5;
                break;
        }
        
        $query->select($groupByFormat . ' AS date_group')
              ->select('SUM(amount) AS total')
              ->select('COUNT(id) AS count')
              ->from($db->quoteName('#__jd_donors'))
              ->where($db->quoteName('published') . ' = 1')
              ->group($groupByFormat)
              ->order('date_group DESC')
              ->setLimit($limit);
        
        $db->setQuery($query);
        $results = $db->loadObjectList() ?: [];
        
        // Reverse to get chronological order
        return array_reverse($results);
    }
    
    /**
     * Get top performing campaigns
     */
    public function getTopCampaigns($limit = 5, $startDate, $endDate)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('c.id, c.title, c.goal, SUM(d.amount) AS donated_amount, c.end_date')
            ->select('COUNT(d.id) AS donors_count')
            ->from($db->quoteName('#__jd_campaigns', 'c'))
            ->leftJoin(
                $db->quoteName('#__jd_donors', 'd') . ' ON ' .
                $db->quoteName('d.campaign_id') . ' = ' . $db->quoteName('c.id') .
                ' AND ' . $db->quoteName('d.published') . ' = 1' .
                ' AND ' . $db->quoteName('d.payment_date') . ' >= ' . $db->quote($startDate) .
                ' AND ' . $db->quoteName('d.payment_date') . ' <= ' . $db->quote($endDate)
            )
            ->where($db->quoteName('c.published') . ' = 1')
            ->group($db->quoteName('c.id'))
            ->order($db->quoteName('donated_amount') . ' DESC')
            ->setLimit($limit);

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    
    /**
     * Get campaigns ending soon
     */
    public function getEndingSoonCampaigns($limit = 5)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $now = Factory::getDate()->toSql();
        
        $query->select('id, title, goal, donated_amount, end_date')
              ->from($db->quoteName('#__jd_campaigns'))
              ->where($db->quoteName('published') . ' = 1')
              ->where($db->quoteName('end_date') . ' > ' . $db->quote($now))
              ->order($db->quoteName('end_date') . ' ASC')
              ->setLimit($limit);
        
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }
    
    /**
     * Get geographic distribution of donors
     */
    public function getDonorLocations($limit = 10, $startDate, $endDate)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('country, COUNT(id) as count, SUM(amount) as total_amount')
            ->from($db->quoteName('#__jd_donors'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('country') . ' != ' . $db->quote(''))
            ->where($db->quoteName('payment_date') . ' >= ' . $db->quote($startDate))
            ->where($db->quoteName('payment_date') . ' <= ' . $db->quote($endDate))
            ->group($db->quoteName('country'))
            ->order('count DESC')
            ->setLimit($limit);

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    
    /**
     * Get recent donations
     */
    public function getRecentDonations($limit = 10, $startDate, $endDate)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('d.id, d.first_name, d.last_name, d.amount, d.payment_date, d.payment_method')
            ->select('c.title AS campaign_title')
            ->from($db->quoteName('#__jd_donors', 'd'))
            ->leftJoin(
                $db->quoteName('#__jd_campaigns', 'c') . ' ON ' . 
                $db->quoteName('c.id') . ' = ' . $db->quoteName('d.campaign_id')
            )
            ->where($db->quoteName('d.published') . ' = 1')
            ->where($db->quoteName('d.payment_date') . ' >= ' . $db->quote($startDate))
            ->where($db->quoteName('d.payment_date') . ' <= ' . $db->quote($endDate))
            ->order($db->quoteName('d.payment_date') . ' DESC')
            ->setLimit($limit);

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    
    /**
     * Get campaign distribution data for charts
     */
    public function getCampaignDistribution($startDate, $endDate)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('c.id, c.title, SUM(d.amount) AS total_amount, COUNT(d.id) AS donors_count')
            ->from($db->quoteName('#__jd_campaigns', 'c'))
            ->leftJoin(
                $db->quoteName('#__jd_donors', 'd') . ' ON ' . 
                $db->quoteName('d.campaign_id') . ' = ' . $db->quoteName('c.id') .
                ' AND ' . $db->quoteName('d.published') . ' = 1' .
                ' AND ' . $db->quoteName('d.payment_date') . ' >= ' . $db->quote($startDate) .
                ' AND ' . $db->quoteName('d.payment_date') . ' <= ' . $db->quote($endDate)
            )
            ->where($db->quoteName('c.published') . ' = 1')
            ->group($db->quoteName('c.id'));

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    
    /**
     * Check if there is any data in the system
     */
    public function hasData()
    {
        $db = $this->getDbo();
        
        // Check if there are any campaigns
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__jd_campaigns'));
        $db->setQuery($query);
        $campaignCount = (int) $db->loadResult();
        
        // Check if there are any donors
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__jd_donors'));
        $db->setQuery($query);
        $donorCount = (int) $db->loadResult();
        
        return ($campaignCount > 0 || $donorCount > 0);
    }
}
