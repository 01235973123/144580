<?php
/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\Factory;

class DonationModelDonors extends OSFModelList
{

	/**
	 * Constructor
	 *
	 * @since 3.6
	 */
	function __construct($config = array())
	{
		$config['search_fields'] = array('tbl.first_name', 'tbl.last_name', 'tbl.email', 'tbl.transaction_id');

		parent::__construct($config);

		$this->state->insert('filter_campaign_id', 'int', 0)
            ->insert('filter_own_campaign','int',0)
			->insert('filter_receive_user_id', 'int', 0)
			->insert('filter_user_id', 'int', 0)
			->insert('filter_campaign_ids', 'string', '')
			->insert('filter_amount','int',0)
			->insert('filter_year','int',0)
			->insert('filter_paid_status', 'int', 0)
			->insert('filter_min_amount', 'float', 0)
			->insert('filter_max_amount', 'float', 0)
			->insert('filter_currency','string','')
			->insert('filter_exclude_campaign_ids','string','')
			->insert('filter_payment','string','')
			->insert('start_date','string','')
			->insert('end_date','string','')
			->insert('filter_search','string','')
			->insert('filter_hide','int',0)
			->insert('gift_aid','int',0);

		$this->state->setDefault('filter_order', 'tbl.created_date')->setDefault('filter_order_Dir', 'DESC');
	}

	protected function buildQueryColumns($query)
	{
		$query->select('tbl.*, cp.title, b.username');

		return $this;
	}

	protected function buildQueryJoins($query)
	{
		$query->leftJoin('#__jd_campaigns AS cp ON tbl.campaign_id = cp.id')
			->leftJoin('#__users AS b ON tbl.user_id = b.id');

		return $this;
	}

	protected function buildQueryGroup($query)
    {
        $query->group('tbl.id');
        return $this;
    }

	protected function buildQueryWhere($query)
	{
		parent::buildQueryWhere($query);

		$config = DonationHelper::getConfig();
		if (!@$config->show_pending_records)
		{
			$query->where('(tbl.published=1 OR tbl.payment_method = "os_offline")');
		}
		if ($this->state->filter_campaign_id)
		{
			$query->where('tbl.campaign_id=' . $this->state->filter_campaign_id);
		}

		if ($this->state->filter_own_campaign == 1) {
            $user = Factory::getApplication()->getIdentity();
            $query->where('tbl.campaign_id in (Select id from #__jd_campaigns where user_id = '.$user->id.')');
        }

		if ($this->state->filter_user_id)
		{
			$query->where(' tbl.user_id = ' . $this->state->filter_user_id);
		}

		if ($this->state->filter_campaign_ids)
		{
			$query->where('tbl.campaign_id IN (' . $this->state->filter_campaign_ids . ')');
		}

		if($this->state->filter_amount == 1){
			$query->where('tbl.amount > 0');
		}

		if($this->state->filter_min_amount > 0)
		{
			$query->where('tbl.amount >= '. $this->state->filter_min_amount);
		}

		if($this->state->filter_max_amount > 0)
		{
			$query->where('tbl.amount <= '. $this->state->filter_max_amount);
		}

		if($this->state->filter_year > 0){
			$query->where('tbl.created_date like "'.$this->state->filter_year.'%"');
		}

		if($this->state->filter_paid_status > 0)
		{
			if($this->state->filter_paid_status == 1)
			{
				$query->where('tbl.published = "0"');
			}
			elseif($this->state->filter_paid_status == 2)
			{
				$query->where('tbl.published = "1"');
			}
		}

		if($this->state->filter_hide > 0)
		{
			//$query->where('tbl.hide_me = "'.$this->state->filter_hide.'"');

			if($this->state->filter_hide == 1)
			{
				$query->where('tbl.hide_me = "0"');
			}
			elseif($this->state->filter_hide == 2)
			{
				$query->where('tbl.hide_me = "1"');
			}
		}

		if($this->state->gift_aid > 0)
		{
			if($this->state->gift_aid == 1)
			{
				$query->where('tbl.gift_aid = "0"');
			}
			elseif($this->state->gift_aid == 2)
			{
				$query->where('tbl.gift_aid = "1"');
			}
		}

		if($this->state->filter_search != "")
		{
			$query->where('(tbl.first_name like "%'.$this->state->filter_search.'%" or tbl.last_name like "%'.$this->state->filter_search.'%" or tbl.email like "%'.$this->state->filter_search.'%" or tbl.phone like "%'.$this->state->filter_search.'%" or tbl.address like "%'.$this->state->filter_search.'%" or tbl.city like "%'.$this->state->filter_search.'%" or tbl.country like "%'.$this->state->filter_search.'%" or tbl.state like "%'.$this->state->filter_search.'%" or tbl.transaction_id like "%'.$this->state->filter_search.'%")');
		}

		if($this->state->filter_payment != ""){
			$query->where('tbl.payment_method = "'.$this->state->filter_payment.'"');
		}

		if($this->state->start_date != ""){
			$query->where('tbl.created_date >= "'.$this->state->start_date.'"');
		}

		if($this->state->end_date != ""){
			$query->where('tbl.created_date <= "'.$this->state->end_date.'"');
		}

		if($this->state->filter_currency != ''){
			if($this->state->filter_currency == $config->currency){
				$query->where(' (tbl.currency_code = "" or tbl.currency_code = "'.$this->state->filter_currency.'")');
			}else{
				$query->where('tbl.currency_code = "'.$this->state->filter_currency.'"');
			}
		}
		
		if(trim($this->state->filter_exclude_campaign_ids) != ''){
			$filter_exclude_campaign_ids = trim($this->state->filter_exclude_campaign_ids);
			$query->where('tbl.campaign_id not in ('.$filter_exclude_campaign_ids.')');
		}	

		return $this;
	}

    public static function getMonthlyReport($time_period = 'current_month', $campaign_id = 0, $payment_method = '')
    {
        $config = Factory::getConfig();
        $db     = Factory::getContainer()->get('db');
        $query  = $db->getQuery(true);

        $donationConfig = DonationHelper::getConfig();
        $query->select('*')
            ->from('#__jd_donors');

        switch ($time_period)
        {
            case 'this_week':
                $date   = Factory::getDate('now', $config->get('offset'));
                $monday = clone $date->modify( 'Monday this week');
                $monday->setTime(0, 0, 0);
                $monday->setTimezone(new DateTimeZone('UCT'));
                $fromDate = $monday->toSql(true);
                $sunday   = clone $date->modify('Sunday this week');
                $sunday->setTime(23, 59, 59);
                $sunday->setTimezone(new DateTimeZone('UCT'));
                $toDate = $sunday->toSql(true);
            break;
            case 'current_month':
                $date = Factory::getDate('now', $config->get('offset'));
                $date->setDate($date->year, $date->month, 1);
                $date->setTime(0, 0, 0);
                $date->setTimezone(new DateTimeZone('UCT'));
                $fromDate = $date->toSql(true);
                $date     = Factory::getDate('now', $config->get('offset'));
                $date->setDate($date->year, $date->month, $date->daysinmonth);
                $date->setTime(23, 59, 59);
                $date->setTimezone(new DateTimeZone('UCT'));
                $toDate = $date->toSql(true);
            break;
            case 'last_month':
                $date = Factory::getDate('first day of last month', $config->get('offset'));
                $date->setTime(0, 0, 0);
                $date->setTimezone(new DateTimeZone('UCT'));
                $fromDate = $date->toSql(true);
                $date     = Factory::getDate('last day of last month', $config->get('offset'));
                $date->setTime(23, 59, 59);
                $date->setTimezone(new DateTimeZone('UCT'));
                $toDate = $date->toSql(true);
            break;
            case 'this_year':
                $date = Factory::getDate('now', $config->get('offset'));
                $date->setDate($date->year, 1, 1);
                $date->setTime(0, 0, 0);
                $date->setTimezone(new DateTimeZone('UCT'));
                $fromDate = $date->toSql(true);
                $date     = Factory::getDate('now', $config->get('offset'));
                $date->setDate($date->year, 12, 31);
                $date->setTime(23, 59, 59);
                $date->setTimezone(new DateTimeZone('UCT'));
                $toDate = $date->toSql(true);
            break;
            case 'last_year':
                $date = Factory::getDate('now', $config->get('offset'));
                $date->setDate($date->year - 1, 1, 1);
                $date->setTime(0, 0, 0);
                $date->setTimezone(new DateTimeZone('UCT'));
                $fromDate = $date->toSql(true);
                $date     = Factory::getDate('now', $config->get('offset'));
                $date->setDate($date->year - 1, 12, 31);
                $date->setTime(23, 59, 59);
                $date->setTimezone(new DateTimeZone('UCT'));
                $toDate = $date->toSql(true);
                break;
        }
        $query->clear('where');
        if($payment_method == "")
        {
            $query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))');
        }
        else
        {
            $query->where('published = 1 and payment_method like "'.$payment_method.'"');
        }

        $query->where('created_date >= ' . $db->quote($fromDate))
            ->where('created_date <=' . $db->quote($toDate));
        if($campaign_id > 0)
        {
            $query->where('campaign_id = '.$campaign_id);
        }
        $query->order('created_date');
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        return $rows;
    }
	/**
	 * Get statistic data
	 *
	 * @return array
	 */
	public static function getStatisticsData()
	{
		$data   = array();
		$config = Factory::getConfig();
		$db     = Factory::getContainer()->get('db');
		$query  = $db->getQuery(true);

		$donationConfig = DonationHelper::getConfig();

		$query->select('COUNT(id) AS total_donations, SUM(amount) AS total_amount')
			->from('#__jd_donors');

		if(DonationHelper::isMultipleCurrencies())
		{
			$active_currencies = $donationConfig->active_currencies;
			$active_currencies_array = explode(",",$active_currencies);	
			if((!in_array($donationConfig->currency,$active_currencies_array)) && ($donationConfig->currency != ""))
			{
				$active_currencies_array[count($active_currencies_array)] = $donationConfig->currency;
			}
		}

		// Today
		if(DonationHelper::isMultipleCurrencies())
		{
			$date = Factory::getDate('now', $config->get('offset'));
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('now', $config->get('offset'));
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);

			foreach ($active_currencies_array as $currency)
			{
				$query->clear('where');
				$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
					->where('created_date >= ' . $db->quote($fromDate))
					->where('created_date <=' . $db->quote($toDate));
				if($currency == $donationConfig->currency)
				{
					$query->where('(currency_code = "'.$currency.'" or currency_code = "")');
				}
				else
				{
					$query->where('currency_code = "'.$currency.'"');
				}
				$db->setQuery($query);
				$row = $db->loadObject();

				$data['today'][$currency] = array(
					'total_donations' => (int) $row->total_donations,
					'total_amount'    => floatval($row->total_amount)
				);
			}
		}
		else
		{
			$date = Factory::getDate('now', $config->get('offset'));
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('now', $config->get('offset'));
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);

			$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
				->where('created_date >= ' . $db->quote($fromDate))
				->where('created_date <=' . $db->quote($toDate));
			$db->setQuery($query);
			$row = $db->loadObject();

			$data['today'] = array(
				'total_donations' => (int) $row->total_donations,
				'total_amount'    => floatval($row->total_amount)
			);
		}

		// Yesterday
		if(DonationHelper::isMultipleCurrencies())
		{
			$date = Factory::getDate('now', $config->get('offset'));
			$date->modify('-1 day');
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('now', $config->get('offset'));
			$date->modify('-1 day');
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);
			foreach ($active_currencies_array as $currency)
			{
				$query->clear('where');
				$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
					->where('created_date >= ' . $db->quote($fromDate))
					->where('created_date <=' . $db->quote($toDate));
				if($currency == $donationConfig->currency)
				{
					$query->where('(currency_code = "'.$currency.'" or currency_code = "")');
				}
				else
				{
					$query->where('currency_code = "'.$currency.'"');
				}
				$db->setQuery($query);
				$row = $db->loadObject();

				$data['yesterday'][$currency] = array(
					'total_donations' => (int) $row->total_donations,
					'total_amount'    => floatval($row->total_amount)
				);
			}
		}
		else
		{
			$date = Factory::getDate('now', $config->get('offset'));
			$date->modify('-1 day');
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('now', $config->get('offset'));
			$date->modify('-1 day');
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);
			$query->clear('where');
			$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
				->where('created_date >= ' . $db->quote($fromDate))
				->where('created_date <=' . $db->quote($toDate));
			$db->setQuery($query);
			$row = $db->loadObject();

			$data['yesterday'] = array(
				'total_donations' => (int) $row->total_donations,
				'total_amount'    => floatval($row->total_amount)
			);
		}

		// This week
		if(DonationHelper::isMultipleCurrencies())
		{
			$date   = Factory::getDate('now', $config->get('offset'));
			$monday = clone $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
			$monday->setTime(0, 0, 0);
			$monday->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $monday->toSql(true);
			$sunday   = clone $date->modify('Sunday this week');
			$sunday->setTime(23, 59, 59);
			$sunday->setTimezone(new DateTimeZone('UCT'));
			$toDate = $sunday->toSql(true);

			foreach ($active_currencies_array as $currency)
			{
				$query->clear('where');
				$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
					->where('created_date >= ' . $db->quote($fromDate))
					->where('created_date <=' . $db->quote($toDate));
				if($currency == $donationConfig->currency)
				{
					$query->where('(currency_code = "'.$currency.'" or currency_code = "")');
				}
				else
				{
					$query->where('currency_code = "'.$currency.'"');
				}
				$db->setQuery($query);
				$row = $db->loadObject();

				$data['this_week'][$currency] = array(
					'total_donations' => (int) $row->total_donations,
					'total_amount'    => floatval($row->total_amount)
				);
			}
		}
		else
		{
			$date   = Factory::getDate('now', $config->get('offset'));
			$monday = clone $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
			$monday->setTime(0, 0, 0);
			$monday->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $monday->toSql(true);
			$sunday   = clone $date->modify('Sunday this week');
			$sunday->setTime(23, 59, 59);
			$sunday->setTimezone(new DateTimeZone('UCT'));
			$toDate = $sunday->toSql(true);

			$query->clear('where');

			$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
				->where('created_date >= ' . $db->quote($fromDate))
				->where('created_date <=' . $db->quote($toDate));
			$db->setQuery($query);
			$row = $db->loadObject();

			$data['this_week'] = array(
				'total_donations' => (int) $row->total_donations,
				'total_amount'    => floatval($row->total_amount)
			);
		}

		// Last week, re-use data from this week
		if(DonationHelper::isMultipleCurrencies())
		{
			$monday->modify('-7 day');
			$sunday->modify('-7 day');
			$fromDate = $monday->toSql(true);
			$toDate   = $sunday->toSql(true);
			foreach ($active_currencies_array as $currency)
			{
				$query->clear('where');
				$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
					->where('created_date >= ' . $db->quote($fromDate))
					->where('created_date <=' . $db->quote($toDate));
				if($currency == $donationConfig->currency)
				{
					$query->where('(currency_code = "'.$currency.'" or currency_code = "")');
				}
				else
                {
					$query->where('currency_code = "'.$currency.'"');
				}
				$db->setQuery($query);
				$row = $db->loadObject();

				$data['last_week'][$currency] = array(
					'total_donations' => (int) $row->total_donations,
					'total_amount'    => floatval($row->total_amount)
				);
			}
		}
		else
        {
			$monday->modify('-7 day');
			$sunday->modify('-7 day');
			$fromDate = $monday->toSql(true);
			$toDate   = $sunday->toSql(true);

			$query->clear('where');
			$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
				->where('created_date >= ' . $db->quote($fromDate))
				->where('created_date <=' . $db->quote($toDate));
			$db->setQuery($query);
			$row = $db->loadObject();

			$data['last_week'] = array(
				'total_donations' => (int) $row->total_donations,
				'total_amount'    => floatval($row->total_amount)
			);
		}

		// This month
		if(DonationHelper::isMultipleCurrencies())
		{
			$date = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year, $date->month, 1);
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year, $date->month, $date->daysinmonth);
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);
			foreach ($active_currencies_array as $currency)
			{
				$query->clear('where');
				$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
					->where('created_date >= ' . $db->quote($fromDate))
					->where('created_date <=' . $db->quote($toDate));
				if($currency == $donationConfig->currency)
				{
					$query->where('(currency_code = "'.$currency.'" or currency_code = "")');
				}
				else
				{
					$query->where('currency_code = "'.$currency.'"');
				}
				$db->setQuery($query);
				$row = $db->loadObject();

				$data['this_month'][$currency] = array(
					'total_donations' => (int) $row->total_donations,
					'total_amount'    => floatval($row->total_amount)
				);
			}
		}
		else
		{
			$date = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year, $date->month, 1);
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year, $date->month, $date->daysinmonth);
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);

			$query->clear('where');
			$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
				->where('created_date >= ' . $db->quote($fromDate))
				->where('created_date <=' . $db->quote($toDate));
			$db->setQuery($query);
			$row = $db->loadObject();

			$data['this_month'] = array(
				'total_donations' => (int) $row->total_donations,
				'total_amount'    => floatval($row->total_amount)
			);
		}

		// Last month
		if(DonationHelper::isMultipleCurrencies())
		{
			$date = Factory::getDate('first day of last month', $config->get('offset'));
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('last day of last month', $config->get('offset'));
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);
			foreach ($active_currencies_array as $currency)
			{
				$query->clear('where');
				$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
					->where('created_date >= ' . $db->quote($fromDate))
					->where('created_date <=' . $db->quote($toDate));
				if($currency == $donationConfig->currency)
				{
					$query->where('(currency_code = "'.$currency.'" or currency_code = "")');
				}
				else
				{
					$query->where('currency_code = "'.$currency.'"');
				}
				$db->setQuery($query);
				$row = $db->loadObject();

				$data['last_month'][$currency] = array(
					'total_donations' => (int) $row->total_donations,
					'total_amount'    => floatval($row->total_amount)
				);
			}
		}
		else
		{
			$date = Factory::getDate('first day of last month', $config->get('offset'));
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('last day of last month', $config->get('offset'));
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);

			$query->clear('where');
			$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
				->where('created_date >= ' . $db->quote($fromDate))
				->where('created_date <=' . $db->quote($toDate));
			$db->setQuery($query);
			$row = $db->loadObject();

			$data['last_month'] = array(
				'total_donations' => (int) $row->total_donations,
				'total_amount'    => floatval($row->total_amount)
			);
		}

		// This year
		if(DonationHelper::isMultipleCurrencies())
		{
			$date = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year, 1, 1);
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year, 12, 31);
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);
			foreach ($active_currencies_array as $currency)
			{
				$query->clear('where');
				$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
					->where('created_date >= ' . $db->quote($fromDate))
					->where('created_date <=' . $db->quote($toDate));
				if($currency == $donationConfig->currency)
				{
					$query->where('(currency_code = "'.$currency.'" or currency_code = "")');
				}
				else
                {
					$query->where('currency_code = "'.$currency.'"');
				}
				$db->setQuery($query);
				$row = $db->loadObject();

				$data['this_year'][$currency] = array(
					'total_donations' => (int) $row->total_donations,
					'total_amount'    => floatval($row->total_amount)
				);
			}
		}
		else
		{
			$date = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year, 1, 1);
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year, 12, 31);
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);

			$query->clear('where');
			$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
				->where('created_date >= ' . $db->quote($fromDate))
				->where('created_date <=' . $db->quote($toDate));
			$db->setQuery($query);
			$row = $db->loadObject();

			$data['this_year'] = array(
				'total_donations' => (int) $row->total_donations,
				'total_amount'    => floatval($row->total_amount)
			);

		}

		// Last year
		if(DonationHelper::isMultipleCurrencies())
		{
			$date = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year - 1, 1, 1);
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year - 1, 12, 31);
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);
			foreach ($active_currencies_array as $currency)
			{
				$query->clear('where');
				$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
					->where('created_date >= ' . $db->quote($fromDate))
					->where('created_date <=' . $db->quote($toDate));
				if($currency == $donationConfig->currency)
				{
					$query->where('(currency_code = "'.$currency.'" or currency_code = "")');
				}
				else
				{
					$query->where('currency_code = "'.$currency.'"');
				}
				$db->setQuery($query);
				$row = $db->loadObject();

				$data['last_year'][$currency] = array(
					'total_donations' => (int) $row->total_donations,
					'total_amount'    => floatval($row->total_amount)
				);
			}
		}
		else
        {
			$date = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year - 1, 1, 1);
			$date->setTime(0, 0, 0);
			$date->setTimezone(new DateTimeZone('UCT'));
			$fromDate = $date->toSql(true);
			$date     = Factory::getDate('now', $config->get('offset'));
			$date->setDate($date->year - 1, 12, 31);
			$date->setTime(23, 59, 59);
			$date->setTimezone(new DateTimeZone('UCT'));
			$toDate = $date->toSql(true);

			$query->clear('where');
			$query->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
				->where('created_date >= ' . $db->quote($fromDate))
				->where('created_date <=' . $db->quote($toDate));
			$db->setQuery($query);
			$row = $db->loadObject();

			$data['last_year'] = array(
				'total_donations' => (int) $row->total_donations,
				'total_amount'    => floatval($row->total_amount)
			);
		}

		// Total registration
		if(DonationHelper::isMultipleCurrencies())
		{
			$query->clear();
			$query->select('COUNT(id) AS total_donations, SUM(amount) AS total_amount')
				->from('#__jd_donors')
				->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))');
				if($currency == $donationConfig->currency)
				{
					$query->where('(currency_code = "'.$currency.'" or currency_code = "")');
				}
				else
				{
					$query->where('currency_code = "'.$currency.'"');
				}
			$db->setQuery($query);
			$row = $db->loadObject();

			$data['total_donations'][$currency] = array(
				'total_donations' => (int) $row->total_donations,
				'total_amount'    => floatval($row->total_amount)
			);
		}
		else
		{
			$query->clear();
			$query->select('COUNT(id) AS total_donations, SUM(amount) AS total_amount')
				->from('#__jd_donors')
				->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))');
			$db->setQuery($query);
			$row = $db->loadObject();

			$data['total_donations'] = array(
				'total_donations' => (int) $row->total_donations,
				'total_amount'    => floatval($row->total_amount)
			);
		}

		return $data;
	}

	static function returnDonorsBasedOnCountry()
    {
        $db = Factory::getContainer()->get('db');
        $db->setQuery("SELECT SUM(amount) as donated_amount, count(id) as number_donated, country FROM `#__jd_donors` WHERe published = '1' group by country order by donated_amount desc");
        $rows = $db->loadObjectList();
        return $rows;
    }
}
