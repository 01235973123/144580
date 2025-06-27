<?php

/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\Factory;

class DonationModelCampaigns extends OSFModelList
{

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->state->insert('cids', 'string', '');
		$this->state->insert('category_id', 'int', 0);
	}

	protected function buildQueryColumns($query)
	{
		$query->select('tbl.* , vl.title AS access_level, DATEDIFF(end_date, CURDATE()) AS days_left, SUM(b.amount) as total_donated, COUNT(b.id) As number_donors');

		if (!Factory::getApplication()->isClient('administrator'))
		{
			$fieldSuffix = DonationHelper::getFieldSuffix();
			if ($fieldSuffix)
			{
				DonationHelper::getMultilingualFields($query, array('tbl.title', 'tbl.short_description' , 'tbl.description'), $fieldSuffix);
			}
		}

		return $this;
	}

	protected function buildQueryJoins($query)
	{
		$query->leftJoin('#__jd_donors AS b ON (tbl.id = b.campaign_id AND b.published = 1)')
			  ->leftJoin('#__viewlevels AS vl ON vl.id = tbl.access');

		return $this;
	}

	protected function buildQueryWhere($query)
	{
		$user       = Factory::getApplication()->getIdentity();

		//For front-end, we only show published campaign
		$app = Factory::getApplication();
		if (!$app->isClient('administrator'))
		{
			$this->state->filter_state = 'P';
			$campaignIds               = trim($app->getParams()->get('campaign_ids'));
			$exCampaignIds             = trim($app->getParams()->get('exclude_campaign_ids'));
			if ($campaignIds)
			{
				$query->where('tbl.id IN (' . $campaignIds . ')');
			}

			if ($exCampaignIds)
			{
				$query->where('tbl.id NOT IN (' . $exCampaignIds . ')');
			}

			$ownerIds					= trim($app->getParams()->get('owner_ids'));
			if($ownerIds)
			{
				$query->where('tbl.user_id IN ('.$ownerIds.')');
			}

			$query->where('tbl.private_campaign = 0');

			$query->where('tbl.access in (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
			
		}

		parent::buildQueryWhere($query);

		if ($this->state->cids)
		{
			$query->where('tbl.id IN (' . $this->state->cids . ')');
		}
		
		if($this->state->category_id )
		{
			$query->where('tbl.category_id = "'.$this->state->category_id.'"');
		}

		return $this;
	}

	protected function buildQueryGroup($query)
	{
		$query->group('tbl.id');

		return $this;
	}
}
