<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Database\DatabaseQuery;

class OSMembershipModelDownloadids extends MPFModelList
{
	protected $clearJoin = false;

	/**
	 * Constructor
	 *
	 * @param   array  $config
	 */
	public function __construct($config = [])
	{
		$config['search_fields'] = ['tbl.download_id', 'tbl.domain', 'u.username'];

		parent::__construct($config);

		$this->state->setDefault('filter_order_Dir', 'DESC');
	}

	/**
	 * Override buildQueryColumns to get required fields
	 *
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryColumns(DatabaseQuery $query)
	{
		$query->select('u.username');

		return parent::buildQueryColumns($query);
	}

	/**
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryJoins(DatabaseQuery $query)
	{
		$query->leftJoin('#__users AS u ON tbl.user_id = u.id');

		return parent::buildQueryJoins($query);
	}

	/**
	 * @param   DatabaseQuery  $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(DatabaseQuery $query)
	{
		if ($this->state->filter_state == 'P')
		{
			$query->where('tbl.published = 1');
		}
		elseif ($this->state->filter_state == 'U')
		{
			$query->where('tbl.published = 0');
		}

		return parent::buildQueryWhere($query);
	}
}
