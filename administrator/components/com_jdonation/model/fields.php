<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class DonationModelFields extends OSFModelList
{

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->state->insert('filter_campaign_id', 'int', 0);
		$this->state->insert('field_type', 'string', '');
		$this->state->insert('published', 'int', -1);
		$this->state->insert('is_core_field', 'int', -1);
		$this->state->insert('require_status', 'int', -1);
	}

	protected function buildQueryColumns($query)
	{
		$query->select('tbl.* , vl.title AS access_level');
		return $this;
	}

	protected function buildQueryJoins($query)
	{
		$query->leftJoin('#__viewlevels AS vl ON vl.id = tbl.access');

		return $this;
	}

	protected function buildQueryWhere($query)
	{
		parent::buildQueryWhere($query);

		if ($this->state->campaign_id)
		{
			$query->where('tbl.campaign_id=' . $this->state->filter_campaign_id);
		}

		if ($this->state->field_type != "")
		{
			$query->where('tbl.fieldtype=' . $this->db->quote($this->state->field_type));
		}

		if ((int)$this->state->published >= 0)
		{
			if((int)$this->state->published == 1)
			{
				$query->where('tbl.published=0');
			}
			else
			{
				$query->where('tbl.published=1');
			}
			
		}

		if ((int)$this->state->is_core_field >= 0)
		{
			//$query->where('tbl.is_core=' . (int)$this->state->is_core_field);

			if((int)$this->state->is_core_field == 1)
			{
				$query->where('tbl.is_core=0');
			}
			else
			{
				$query->where('tbl.is_core=1');
			}
		}

		if ((int)$this->state->require_status >= 0)
		{
			//$query->where('tbl.required=' . (int)$this->state->require_status);
			if((int)$this->state->require_status == 1)
			{
				$query->where('tbl.required=0');
			}
			else
			{
				$query->where('tbl.required=1');
			}
		}

		return $this;
	}
}