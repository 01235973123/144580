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

class DonationModelUserdonors extends OSFModelList
{
    function __construct($config = array())
    {
        $config['table']            = '#__jd_donors';
        parent::__construct($config);
        $this->state->insert('id', 'int', 0)
			->insert('start_date','string','')
			->insert('end_date','string','')
			->insert('campaign_ids','string','');
    }

    protected function buildQueryColumns($query)
    {
        $query->select('tbl.*, b.title as campaign_title');
        return $this;
    }

    protected function buildQueryJoins($query)
    {
        $query->leftJoin('#__jd_campaigns AS b ON tbl.campaign_id = b.id');
        return $this;
    }

    protected function buildQueryWhere($query)
    {
        $usercampaigns = DonationHelper::getUserCampaigns();
        if(count($usercampaigns) > 0)
        {
            $query->where('tbl.campaign_id in (' . implode(",", $usercampaigns) . ') and tbl.published = 1');
        }
        else
        {
            //return empty donors list
            $query->where('1=2');
        }
        if($this->state->id > 0)
        {
            $query->where('tbl.campaign_id = "'.$this->state->id.'" and tbl.published = 1');
        }
		if($this->state->campaign_ids != "")
        {
            $query->where('tbl.campaign_id in ('.$this->state->campaign_ids.')');
        }
		if($this->state->start_date != ""){
			$query->where('tbl.created_date >= "'.$this->state->start_date.'"');
		}

		if($this->state->end_date != ""){
			$query->where('tbl.created_date <= "'.$this->state->end_date.'"');
		}
        parent::buildQueryWhere($query);

        //echo $query->__toString();die();
        return $this;
    }

    protected function buildQueryOrder($query)
    {
        $query->order('tbl.created_date desc');
        return $this;
    }
}
